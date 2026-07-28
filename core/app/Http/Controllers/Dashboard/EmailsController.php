<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EmailsController extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function emails()
    {
        $templates = EmailTemplate::where('is_active', 1)->orderBy('name')->get();

        return view('admin.marketings.emails', compact('templates'));
    }

    public function bulkEmailSend(Request $request)
    {
        $emails = $this->cleanEmails((array) $request->input('emails', []));
        $templateId = $request->input('template');

        $templateData = DB::table('email_templates')
            ->where('id', $templateId)
            ->where('is_active', 1)
            ->first();

        if (empty($templateData)) {
            return response()->json([
                'status' => 0,
                'message' => 'No active template found',
            ]);
        }

        if (empty($emails)) {
            return response()->json([
                'status' => 0,
                'message' => 'No valid emails found',
            ]);
        }

        try {
            $result = $this->mailService->sendBulkEmail($emails, 'Promotional email', 'emails.marketings.dynamic-template', ['html' => $templateData->template]);

            if (is_array($result) && !empty($result['error'])) {
                return response()->json([
                    'status' => 0,
                    'message' => $result['message'] ?? 'Bulk email failed',
                    'failed' => $result['failed'] ?? [],
                ]);
            }

            return response()->json([
                'status' => 1,
                'count' => count($emails),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function importEmails(Request $request)
    {
        $request->validate([
            'email_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $data = Excel::toArray([], $request->file('email_file'));
        $emails = [];

        foreach (($data[0] ?? []) as $row) {
            if (!empty($row[0]) && filter_var($row[0], FILTER_VALIDATE_EMAIL)) {
                $emails[] = strtolower(trim($row[0]));
            }
        }

        return response()->json([
            'emails' => array_values(array_unique($emails)),
        ]);
    }

    public function bulkEmailImportSend(Request $request)
    {
        $emails = $this->cleanEmails((array) $request->input('emails', []));
        $templateId = $request->input('import_template');

        $templateData = DB::table('email_templates')
            ->where('id', $templateId)
            ->where('is_active', 1)
            ->first();

        if (empty($templateData)) {
            return response()->json([
                'status' => 0,
                'message' => 'No active template found',
            ]);
        }

        if (empty($emails)) {
            return response()->json([
                'status' => 0,
                'message' => 'No valid emails found',
            ]);
        }

        try {
            $result = $this->mailService->sendBulkEmail($emails, 'Promotional email', 'emails.marketings.dynamic-template', ['html' => $templateData->template]);

            if (is_array($result) && !empty($result['error'])) {
                return response()->json([
                    'status' => 0,
                    'message' => $result['message'] ?? 'Bulk email failed',
                    'failed' => $result['failed'] ?? [],
                ]);
            }

            return response()->json([
                'status' => 1,
                'count' => count($emails),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage(),
            ]);
        }
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
}
