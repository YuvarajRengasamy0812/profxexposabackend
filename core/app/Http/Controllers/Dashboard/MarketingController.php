<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketingController extends Controller
{
    protected MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function index()
    {
        $campaigns = MarketingCampaign::with('template')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $stats = [
            'total'     => MarketingCampaign::count(),
            'sent'      => MarketingCampaign::where('status', 'sent')->count(),
            'scheduled' => MarketingCampaign::where('status', 'scheduled')->count(),
            'failed'    => MarketingCampaign::where('status', 'failed')->count(),
        ];

        return view('admin.marketings.campaigns.index', compact('campaigns', 'stats'));
    }

    public function create()
    {
        $templates = EmailTemplate::where('is_active', 1)->get();
        return view('admin.marketings.campaigns.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_name'  => 'required|string|max:255',
            'template_id'    => 'required|integer|exists:email_templates,id',
            'recipient_type' => 'required|in:all_clients,specific_clients,external',
            'send_mode'      => 'nullable|in:instant,schedule',
            'scheduled_at'   => 'nullable|date|after:now',
            'client_ids'     => 'nullable|array',
            'client_ids.*'   => 'integer',
        ]);

        $template  = EmailTemplate::findOrFail($request->template_id);
        $sendMode  = $request->input('send_mode', 'instant');
        $schedAt   = ($sendMode === 'schedule') ? $request->scheduled_at : null;

        // Build email + name map
        $emails  = [];
        $nameMap = [];

        if ($request->recipient_type === 'all_clients') {
            $clients = DB::table('users_registers')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->select('email', DB::raw('full_name as fullname'))
                ->get();
            foreach ($clients as $c) {
                $emails[]           = $c->email;
                $nameMap[$c->email] = $c->fullname;
            }
        } elseif ($request->recipient_type === 'specific_clients') {
            $ids = $request->input('client_ids', []);
            if (empty($ids)) {
                return response()->json(['status' => 0, 'message' => 'No clients selected']);
            }
            $clients = DB::table('users_registers')
                ->whereIn('id', $ids)
                ->select('email', DB::raw('full_name as fullname'))
                ->get();
            foreach ($clients as $c) {
                $emails[]           = $c->email;
                $nameMap[$c->email] = $c->fullname;
            }
        } elseif ($request->recipient_type === 'external') {
            $raw = trim($request->input('external_emails', ''));
            if (empty($raw)) {
                return response()->json(['status' => 0, 'message' => 'No external emails provided']);
            }
            foreach ($this->parseEmails($raw) as $email) {
                $emails[]          = $email;
                $nameMap[$email]   = '';
            }
        }

        $emails = $this->cleanEmails($emails);

        if (empty($emails)) {
            return response()->json(['status' => 0, 'message' => 'No valid emails found']);
        }

        // If scheduling — save and return without sending
        if ($sendMode === 'schedule') {
            $campaign = MarketingCampaign::create([
                'campaign_name'    => $request->campaign_name,
                'template_id'      => $template->id,
                'recipient_type'   => $request->recipient_type,
                'total_recipients' => count($emails),
                'sent_count'       => 0,
                'failed_count'     => 0,
                'status'           => 'scheduled',
                'scheduled_at'     => $schedAt,
                'created_by'       => $this->campaignCreator(),
            ]);

            // Store recipients as pending; sendNow() marks them sent or failed.
            $rows = [];
            foreach ($emails as $email) {
                $rows[] = [
                    'campaign_id' => $campaign->id,
                    'email'       => $email,
                    'name'        => $nameMap[$email] ?? '',
                    'status'      => 'pending',
                    'sent_at'     => null,
                ];
            }
            DB::table('marketing_campaign_recipients')->insert($rows);

            return response()->json(['status' => 1, 'campaign_id' => $campaign->id, 'count' => count($emails)]);
        }

        // Send instantly
        return $this->dispatchCampaign(
            $request->campaign_name,
            $template,
            $request->recipient_type,
            $emails,
            $nameMap
        );
    }

    public function sendNow(Request $request)
    {
        $request->validate(['campaign_id' => 'required|integer']);

        $campaign = MarketingCampaign::with('template')->findOrFail($request->campaign_id);

        if ($campaign->status !== 'scheduled') {
            return response()->json(['status' => 0, 'message' => 'Campaign is not in scheduled state']);
        }

        // Collect recipients saved from scheduling step
        $recipientRows = DB::table('marketing_campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->get();

        $emails  = $recipientRows->pluck('email')->toArray();
        $nameMap = $recipientRows->pluck('name', 'email')->toArray();
        $emails  = $this->cleanEmails($emails);

        if (empty($emails)) {
            return response()->json(['status' => 0, 'message' => 'No recipients found']);
        }

        try {
            if (!$campaign->template) {
                throw new \RuntimeException('Email template not found for this campaign');
            }

            $campaign->update(['status' => 'sending']);

            $this->sendBulkCampaignEmail(
                $emails,
                $campaign->campaign_name,
                $campaign->template->template
            );

            $now = now();
            DB::table('marketing_campaign_recipients')
                ->where('campaign_id', $campaign->id)
                ->update(['status' => 'sent', 'sent_at' => $now]);

            $campaign->update([
                'sent_count'   => count($emails),
                'failed_count' => 0,
                'status'       => 'sent',
            ]);


            return response()->json(['status' => 1, 'count' => count($emails)]);

        } catch (\Exception $e) {
            DB::table('marketing_campaign_recipients')
                ->where('campaign_id', $campaign->id)
                ->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'sent_at'       => null,
                ]);

            $campaign->update([
                'sent_count'   => 0,
                'failed_count' => count($emails),
                'status'       => 'failed',
            ]);

            return response()->json([
                'status'      => 0,
                'message'     => $e->getMessage(),
                'campaign_id' => $campaign->id,
                'count'       => count($emails),
            ]);
        }
    }

    public function show(int $id)
    {
        $campaign   = MarketingCampaign::with('template')->findOrFail($id);
        $recipients = MarketingCampaignRecipient::where('campaign_id', $id)
            ->orderBy('id', 'desc')
            ->paginate(30);

        return view('admin.marketings.campaigns.show', compact('campaign', 'recipients'));
    }

    public function history()
    {
        $campaigns = MarketingCampaign::with('template')
            ->orderBy('id', 'desc')
            ->paginate(25);

        return view('admin.marketings.history', compact('campaigns'));
    }

    public function getClients(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = DB::table('users_registers')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->select('id', 'email', DB::raw('full_name as fullname'));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('full_name')->limit(200)->get();

        return response()->json(['data' => $clients]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function dispatchCampaign(
        string $name,
        EmailTemplate $template,
        string $recipientType,
        array $emails,
        array $nameMap
    ) {
        $campaign = MarketingCampaign::create([
            'campaign_name'    => $name,
            'template_id'      => $template->id,
            'recipient_type'   => $recipientType,
            'total_recipients' => count($emails),
            'sent_count'       => 0,
            'failed_count'     => 0,
            'status'           => 'sending',
            'created_by'       => $this->campaignCreator(),
        ]);

        try {
            $this->sendBulkCampaignEmail(
                $emails,
                $name,
                $template->template
            );

            $now  = now();
            $rows = [];
            foreach ($emails as $email) {
                $rows[] = [
                    'campaign_id' => $campaign->id,
                    'email'       => $email,
                    'name'        => $nameMap[$email] ?? '',
                    'status'      => 'sent',
                    'sent_at'     => $now,
                ];
            }
            DB::table('marketing_campaign_recipients')->insert($rows);

            $campaign->update([
                'sent_count'   => count($emails),
                'failed_count' => 0,
                'status'       => 'sent',
            ]);

          

            return response()->json(['status' => 1, 'campaign_id' => $campaign->id, 'count' => count($emails)]);

        } catch (\Exception $e) {
            $rows = [];
            foreach ($emails as $email) {
                $rows[] = [
                    'campaign_id'   => $campaign->id,
                    'email'         => $email,
                    'name'          => $nameMap[$email] ?? '',
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'sent_at'       => null,
                ];
            }
            DB::table('marketing_campaign_recipients')->insert($rows);

            $campaign->update([
                'sent_count'   => 0,
                'failed_count' => count($emails),
                'status'       => 'failed',
            ]);

            return response()->json(['status' => 0, 'message' => $e->getMessage()]);
        }
    }

    private function parseEmails(string $raw): array
    {
        $emails = [];
        foreach (preg_split('/[\r\n,]+/', $raw) as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            preg_match('/<([^>]+)>/', $line, $m);
            $email = strtolower(trim($m[1] ?? $line));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
        return array_values(array_unique($emails));
    }

    private function cleanEmails(array $emails): array
    {
        $valid = [];

        foreach ($emails as $email) {
            $email = strtolower(trim((string) $email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid[] = $email;
            }
        }

        return array_values(array_unique($valid));
    }

    private function campaignCreator(): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return $user->name ?: ($user->email ?: (string) $user->id);
    }

    private function sendBulkCampaignEmail(array $emails, string $subject, string $html): void
    {
        $result = $this->mailService->sendBulkEmail(
            $emails,
            $subject,
            'emails.marketings.dynamic-template',
            ['html' => $html]
        );

        if (is_array($result) && !empty($result['error'])) {
            throw new \RuntimeException($result['message'] ?? 'Bulk email failed');
        }

        Log::info('Marketing campaign email sent', [
            'subject' => $subject,
            'count'   => count($emails),
        ]);
    }
}
