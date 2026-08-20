<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\WebmasterSection;
use App\Models\User;
use Auth;
use File;
use Illuminate\Http\Request;
use Redirect;
use Helper;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\Floorplan;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class FloorplanController extends Controller
{
    private $uploadPath = "uploads/settings/";
    protected $mailService;


 public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function bookingList(Request $request)
    {

        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $query = DB::table('bookings');

        // 🔍 Search filters
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('game')) {
            $query->where('game', 'like', '%' . $request->game . '%');
        }

        // 📤 Export CSV
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportCsv($query);
        }

        // 📄 Pagination
        $bookings = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        // 📊 Stats
        $stats = (object) [
            'total' => DB::table('bookings')->count(),
        ];

        return view('dashboard.booking.listbooking', compact('GeneralWebmasterSections', 'bookings', 'stats'));
    }

    // 👁️ View Single Booking
    public function bookingView($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $booking = DB::table('bookings')->where('id', $id)->first();

        abort_if(!$booking, 404);

        return view('dashboard.booking.viewbooking', compact('GeneralWebmasterSections', 'booking'));
    }



    // membership

    public function floorplanList(Request $request)
    {

        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $query = DB::table('floorplans');

        // 🔍 Search filters
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

       if ($request->filled('boothtitle')) {
            $query->where('boothtitle', 'like', '%' . $request->boothtitle . '%');
        }



        // 📄 Pagination
        $floorplans = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());
            
    if (!empty($floorplans->file)) {
        $floorplans->file = url('uploads/topics/' . $floorplans->file);
    } else {
        $floorplans->file = null;
    }

        // 📊 Stats
        $stats = (object) [
            'total' => DB::table('floorplans')->count(),
        ];

        return view('dashboard.floorplan.listfloorplan', compact('GeneralWebmasterSections', 'floorplans', 'stats'));
    }

public function floorplansView($id)
{
    $GeneralWebmasterSections = WebmasterSection::where('status', 1)
        ->orderBy('row_no', 'asc')
        ->get();

    // ✅ Get single floorplan
    $floorplan = DB::table('floorplans')->where('id', $id)->first();

    // ❌ If not found
    abort_if(!$floorplan, 404);

    // ✅ Convert file name to full URL
    if (!empty($floorplan->file)) {
        $floorplan->file = url('uploads/topics/' . $floorplan->file);
    } else {
        $floorplan->file = null;
    }

    return view(
        'dashboard.floorplan.viewfloorplan',
        compact('GeneralWebmasterSections', 'floorplan')
    );
}


public function approve(Request $request, $id)
{
    // ✅ Validate input
     $request->validate([
        'message' => 'nullable|string|max:1000',
        'profile_name' => 'nullable|string|max:255',
        'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp|max:10028',
        'company_url' => 'nullable|url|max:255',
        'booth_design' => 'nullable|string|max:255',
        'booth_design_image' => 'nullable|file|mimes:jpg,jpeg,png,gif,svg,webp,pdf,mp4,mov,avi,webm,mkv|max:10240',
        'status' => 'required|string|in:pending,approved,rejected',
    ]);

    // ✅ Find floorplan
    $floorplan = Floorplan::findOrFail($id);

    // ✅ Update basic fields
    $floorplan->status = $request->status;
    $floorplan->approval_message = $request->message;
        $floorplan->approved_by = $request->profile_name ?? auth()->user()->name ?? 'Admin';
    if ($request->filled('company_url')) {
        $floorplan->company_url = $request->company_url;
    }
    if ($request->has('booth_design')) {
        $floorplan->booth_design = $request->booth_design;
    }
    // $floorplan->approved_by = $request->profile_name ?? auth()->user()->name ?? 'Admin';
    // $floorplan->company_url = $request->company_url;

    // ✅ Upload company logo (if exists)
    if ($request->hasFile('company_logo')) {

        $file = $request->file('company_logo');
        $fileFinalName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();

        $path = $this->uploadPath; // example: public/uploads/topics/

        $file->move($path, $fileFinalName);

        // resize & optimize
        Helper::imageResize($path . $fileFinalName);
        Helper::imageOptimize($path . $fileFinalName);

        // ✅ SAVE filename in DB
        $floorplan->company_logo = $fileFinalName;
    }

  if ($request->hasFile('booth_design_image')) {
        $floorplan->booth_design_image = $this->storeSettingsImage($request->file('booth_design_image'));
    }

    // Save floorplan
    $floorplan->save();

    // ✅ Flash messages
    if ($request->status === 'approved') {
        return redirect()->back()->with('success', 'Successfully verified payments');
    } elseif ($request->status === 'rejected') {
        return redirect()->back()->with('error', 'Rejected the Payment');
    } else {
        return redirect()->back()->with('info', 'Floorplan status updated');
    }
}

    private function storeSettingsImage($file)
    {
        $fileFinalName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $path = $this->uploadPath;
        $file->move($path, $fileFinalName);

        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }

        return $fileFinalName;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'referal_code' => 'nullable|string|max:255',
            'boothno' => 'nullable|string|max:255',
            'boothtitle' => 'nullable|string|max:255',
            'boothsize' => 'nullable|string|max:255',
            'boothammount' => 'nullable|numeric',
            'paymenttype' => 'nullable|string|max:255',
            'networktype' => 'nullable|string|max:255',
            'company_profile_name' => 'nullable|string|max:255',
            'company_details' => 'nullable|string|max:5000',
            'company_url' => 'nullable|url|max:255',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp|max:10028',
            'booth_design' => 'nullable|string|max:255',
            'booth_design_image' => 'nullable|file|mimes:jpg,jpeg,png,gif,svg,webp,pdf,mp4,mov,avi,webm,mkv|max:10240',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'approval_message' => 'nullable|string|max:1000',
        ]);

        $floorplan = Floorplan::findOrFail($id);

        foreach ([
            'name', 'email', 'phone', 'company', 'referal_code', 'boothno',
            'boothtitle', 'boothsize', 'boothammount', 'paymenttype', 'networktype',
            'company_profile_name', 'company_details', 'company_url', 'booth_design',
            'status', 'approval_message'
        ] as $field) {
            if ($request->has($field)) {
                $floorplan->{$field} = $request->input($field);
            }
        }

        if ($request->hasFile('company_logo')) {
            $floorplan->company_logo = $this->storeSettingsImage($request->file('company_logo'));
        }

        if ($request->hasFile('booth_design_image')) {
            $floorplan->booth_design_image = $this->storeSettingsImage($request->file('booth_design_image'));
        }

        $floorplan->save();

        return redirect()->back()->with('success', 'Floorplan updated successfully');
    }

    public function destroy($id)
    {
        $floorplan = Floorplan::findOrFail($id);

        foreach (['company_logo', 'booth_design_image'] as $field) {
            if (!empty($floorplan->{$field}) && File::exists($this->uploadPath . $floorplan->{$field})) {
                File::delete($this->uploadPath . $floorplan->{$field});
            }
        }

        if (!empty($floorplan->file) && File::exists('uploads/topics/' . $floorplan->file)) {
            File::delete('uploads/topics/' . $floorplan->file);
        }

        $floorplan->delete();

        return redirect()->back()->with('success', 'Floorplan deleted successfully');
    }

    public function profxusers(Request $request)
    {

        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $query = $this->profxUsersQuery($request);

        // Pagination
        $profxusers = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());
            
             $userTypes = DB::table('users_registers')
            ->whereNotNull('user_type')
            ->where('user_type', '!=', '')
            ->distinct()
            ->orderBy('user_type')
            ->pluck('user_type');

        // Stats
        $stats = (object) [
            'total' => DB::table('users_registers')->count(),
        ];

        return view('dashboard.profxusers.listusers', compact('GeneralWebmasterSections', 'profxusers', 'stats', 'userTypes'));
    }

    public function profxusersExport(Request $request, string $format)
    {
        $format = strtolower($format);
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        $rows = $this->profxUsersQuery($request)
            ->orderBy('created_at', 'DESC')
            ->get();

        $headings = [
            'ID',
            'Name',
            'Role',
            'Email',
            'Referral Code',
            'Referral Link',
            'Company Name',
            'Phone',
            'Country',
            'Created At',
            'Updated At',
        ];

        $data = $rows->map(function ($user) {
            return [
                $user->id,
                $user->full_name,
                $user->user_type,
                $user->email,
                $user->referral_code,
                $user->referral_link,
                $user->company_name,
                $user->phone,
                $user->nationality,
                $user->created_at,
                $user->updated_at,
            ];
        });

        return $this->downloadUserExport($format, 'profx-users', 'PROFX Users', $headings, $data);
    }

    public function storeReferralAccount(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users_registers,email',
            'phone' => 'required|string|max:50',
            'nationality' => 'required|string|max:100',
            'user_type' => 'required|string|max:100',
            'referral_code' => 'nullable|string|max:50|unique:users_registers,referral_code',
            'password' => 'nullable|string|min:6|max:100',
        ]);

        $now = now();
        $password = $request->filled('password') ? $request->password : 'profx' . random_int(100000, 999999);
        $referralCode = strtoupper(trim((string) $request->input('referral_code', '')));

        $userId = DB::table('users_registers')->insertGetId([
            'full_name' => $request->full_name,
            'email' => strtolower(trim($request->email)),
            'company_name' => $request->input('company_name', $request->user_type),
            'phone' => $request->phone,
            'user_type' => $request->user_type,
            'nationality' => $request->nationality,
            'password' => Hash::make($password),
            'special_requirements' => null,
            'sponsor_package' => null,
            'products_services' => null,
            'referral_code' => null,
            'referral_link' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($referralCode === '') {
            $referralCode = $this->createReferralCodeForRegisteredUser($userId);
        }

        $referralLink = $this->createReferralLinkForCode($referralCode);

        DB::table('users_registers')
            ->where('id', $userId)
            ->update([
                'referral_code' => $referralCode,
                'referral_link' => $referralLink,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('profxusers')
            ->with('profxSwalSuccess', 'Referral account created. Code: ' . $referralCode);
    }
    public function sendRegistrationEmails(Request $request)
    {
        $templateKey = $request->input('email_template', 'registration_ticket');
        $sendScope = $request->input('send_scope', 'selected');
        $selectedUserIds = $request->input('user_ids', []);

        $allowedTemplates = [
            'registration_ticket' => [
                'label' => 'Registration Ticket',
                'subject' => 'Ticket - PROFX Expo Africa',
                'view' => 'emails.registration',
            ],
            'community_groups' => [
                'label' => 'Telegram & WhatsApp Community',
                'subject' => 'Join PROFX Expo Africa 2026 Official Communities',
                'view' => 'emails.community-groups',
            ],
            'expo_countdown' => [
                'label' => 'Expo Day',
                'subject' => 'TODAY IS THE DAY! - PROFX Expo Africa 2026',
                'view' => 'emails.expo-countdown',
            ],
        ];

        if (!array_key_exists($templateKey, $allowedTemplates)) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalError', 'Please select a valid email template.');
        }

        if (!in_array($sendScope, ['selected', 'all', 'league_users'], true)) {
            $sendScope = 'selected';
        }

        if ($sendScope === 'selected' && (!is_array($selectedUserIds) || empty($selectedUserIds))) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalError', 'Please select at least one user.');
        }

        if ($sendScope === 'league_users') {
            $usersQuery = DB::table('booking_leagues')
                ->select(
                    'id',
                    'name as full_name',
                    'email',
                    'company as company_name',
                    'phone',
                    'country as nationality',
                    'role as user_type',
                    'own_referral_code as referral_code',
                    'own_referral_link as referral_link',
                    'created_at'
                )
                ->whereNotNull('email')
                ->where('email', '!=', '');
        } else {
            $usersQuery = DB::table('users_registers')
                ->whereNotNull('email')
                ->where('email', '!=', '');

            if ($sendScope === 'selected') {
                $usersQuery->whereIn('id', $selectedUserIds);
            }
        }

        $users = $usersQuery->orderBy('created_at', 'DESC')->get();

        if ($users->isEmpty()) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalError', 'No users with valid email addresses found.');
        }

        $template = $allowedTemplates[$templateKey];
        $sent = 0;
        $failed = [];

        foreach ($users as $user) {
            $mailData = [
                'user' => $user,
                'title' => $template['subject'],
                'details' => "Hi {$user->full_name},<br><br>Thank you for registering for PROFX Expo Africa 2026.<br>You can now login with your email.<br><br>Regards,<br>PROFX Team",
                'logo' => 'https://profxexpo.com/africa/assets/images/logo/profx-white.png?v=20260722-082125',
                'ticket_header' => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792010449837.png',
                'ticket_footer' => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792011237810.png',
                'downloadTicketUrl' => route('ticket.download', $user->id),
                'heroImage' => 'https://profxexpo.com/africa/adminpanel/assets/dashboard/images/email/telgram.png',
                'countdownHeroImage' => 'https://profxexpo.com/africa/adminpanel/assets/dashboard/images/email/profx-expo-day.jpeg',
                'eventUrl' => 'https://profxexpo.com/africa',
                'telegramUrl' => 'https://t.me/profxexpoafrica',
                'whatsappUrl' => 'https://chat.whatsapp.com/Er7vSpSmGoK68nFbGrxAQk',
            ];

            try {
                $result = $this->mailService->sendEmail(
                    $user->email,
                    $template['subject'],
                    $template['view'],
                    $mailData
                );

                if (is_array($result) && !empty($result['error'])) {
                    $failed[] = $user->email;
                    continue;
                }

                $sent++;
            } catch (\Exception $e) {
                $failed[] = $user->email;

                Log::error('Admin user email failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'template' => $templateKey,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($sendScope === 'all') {
            $scopeText = 'all users';
        } elseif ($sendScope === 'league_users') {
            $scopeText = 'league users';
        } else {
            $scopeText = 'selected users';
        }

        if (!empty($failed)) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalWarning', "{$sent} {$template['label']} email(s) sent to {$scopeText}. Failed: " . implode(', ', $failed));
        }

        return redirect()
            ->route('profxusers')
            ->with('profxSwalSuccess', "{$sent} {$template['label']} email(s) sent successfully to {$scopeText}.");
    }
    
      public function profxusersView($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $profxusers = DB::table('users_registers')->where('id', $id)->first();

        abort_if(!$profxusers, 404);

        $referralLeagueUsers = DB::table('booking_leagues')
            ->where(function ($query) use ($profxusers) {
                $query->where('referred_by_user_id', $profxusers->id);

                if (!empty($profxusers->referral_code)) {
                    $query->orWhere('referral_code', $profxusers->referral_code);
                }
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('dashboard.profxusers.viewusers', compact('GeneralWebmasterSections', 'profxusers', 'referralLeagueUsers'));
    }
    
    
            public function leagueusers(Request $request)
    {

        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $query = $this->leagueUsersQuery($request);

        // Pagination
        $leagueusers = $query
            ->orderBy('bl.created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        // Stats
        $stats = (object) [
            'total' => DB::table('booking_leagues')->count(),
        ];

        return view('dashboard.leagueusers.listusers', compact('GeneralWebmasterSections', 'leagueusers', 'stats'));
    }

    public function leagueusersExport(Request $request, string $format)
    {
        $format = strtolower($format);
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);

        $rows = $this->leagueUsersQuery($request)
            ->orderBy('bl.created_at', 'DESC')
            ->get();

        $headings = [
            'ID',
            'Name',
            'Role',
            'Email',
            'Company',
            'Phone',
            'Country',
            'Referral User',
            'Used Referral Code',
            'Own Referral Code',
            'Own Referral Link',
            'Created At',
            'Updated At',
        ];

        $data = $rows->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->role,
                $user->email,
                $user->company,
                $user->phone,
                $user->country,
                $user->referral_user_name,
                $user->referral_code,
                $user->own_referral_code,
                $user->own_referral_link,
                $user->created_at,
                $user->updated_at,
            ];
        });

        return $this->downloadUserExport($format, 'league-users', 'League Users', $headings, $data);
    }
      public function leagueusersView($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $leagueusers = DB::table('booking_leagues as bl')
            ->leftJoin('users_registers as ur', 'bl.referred_by_user_id', '=', 'ur.id')
            ->leftJoin('booking_leagues as rbl', 'bl.referred_by_league_id', '=', 'rbl.id')
            ->select('bl.*', DB::raw('COALESCE(' . DB::getTablePrefix() . 'ur.full_name, ' . DB::getTablePrefix() . 'rbl.name, ' . DB::getTablePrefix() . 'bl.referrer_name) as referral_user_name'))
            ->where('bl.id', $id)
            ->first();

        abort_if(!$leagueusers, 404);

        $referralLeagueUsers = DB::table('booking_leagues')
            ->where(function ($query) use ($leagueusers) {
                $query->where('referred_by_league_id', $leagueusers->id);

                if (!empty($leagueusers->own_referral_code)) {
                    $query->orWhere('referral_code', $leagueusers->own_referral_code);
                }
            })
            ->where('id', '!=', $leagueusers->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('dashboard.leagueusers.viewusers', compact('GeneralWebmasterSections', 'leagueusers', 'referralLeagueUsers'));
    }

    private function createReferralCodeForRegisteredUser(int $userId): string
    {
        return 'PFX' . str_pad((string) $userId, 6, '0', STR_PAD_LEFT);
    }

    private function createReferralLinkForCode(string $referralCode): string
    {
        return 'https://profxexpo.com/africa/LeagueEnroll?ref=' . urlencode($referralCode);
    }


    private function profxUsersQuery(Request $request)
    {
        $query = DB::table('users_registers');

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        return $query;
    }

    private function leagueUsersQuery(Request $request)
    {
        $query = DB::table('booking_leagues as bl')
            ->leftJoin('users_registers as ur', 'bl.referred_by_user_id', '=', 'ur.id')
            ->leftJoin('booking_leagues as rbl', 'bl.referred_by_league_id', '=', 'rbl.id')
            ->select('bl.*', DB::raw('COALESCE(' . DB::getTablePrefix() . 'ur.full_name, ' . DB::getTablePrefix() . 'rbl.name, ' . DB::getTablePrefix() . 'bl.referrer_name) as referral_user_name'));

        if ($request->filled('email')) {
            $query->where('bl.email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('name')) {
            $query->where('bl.name', 'like', '%' . $request->name . '%');
        }

        return $query;
    }

    private function downloadUserExport(string $format, string $filePrefix, string $title, array $headings, Collection $data)
    {
        $fileName = $filePrefix . '-' . now()->format('Y-m-d-His');

        if ($format === 'excel') {
            $export = new class($headings, $data) implements FromCollection, WithHeadings, ShouldAutoSize {
                private array $headings;
                private Collection $data;

                public function __construct(array $headings, Collection $data)
                {
                    $this->headings = $headings;
                    $this->data = $data;
                }

                public function headings(): array
                {
                    return $this->headings;
                }

                public function collection(): Collection
                {
                    return $this->data;
                }
            };

            return Excel::download($export, $fileName . '.xlsx');
        }

        $pdf = Pdf::loadView('dashboard.exports.users-table', [
            'title' => $title,
            'headings' => $headings,
            'rows' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName . '.pdf');
    }
}
