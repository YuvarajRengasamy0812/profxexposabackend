<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Models\EmailTemplate;
use App\Services\MailService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use DB;

class EmailsController extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function emails()
    {
        $templates = EmailTemplate::where('is_active',1)->get();

        return view("admin.marketings.emails", compact('templates'));
    }

    public function bulkEmailSend(Request $request)
    {
        $emails = $request->emails;
        $templateId = $request->template;

        $templateData = DB::table('email_templates')
        ->where('id', $templateId)
        ->first();

        if (empty($emails)) {
            return response()->json([
                'status' => 0,
                'message' => 'No emails found'
            ]);
        }

        try {
                $this->mailService->sendBulkEmail($emails, 'Promotional email', 'emails.marketings.dynamic-template',    ['html' => $templateData->template]);
                
                $datalogs = [
                    'action' => 'Bulk Email Send',
                    'client_type' => 'Registered Clients',
                    'total_email_sent'  => count($emails),
                    'sent_emails'  => array_combine(range(1, count($emails)), $emails),
                    'timestamp'   => now(),
                ];

                

            return response()->json([
                'status' => 1,
                'count' => count($emails)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function importEmails(Request $request)
    {
        $request->validate([
            'email_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $data = Excel::toArray([], $request->file('email_file'));

        $emails = [];

        foreach ($data[0] as $row) {
            if (!empty($row[0]) && filter_var($row[0], FILTER_VALIDATE_EMAIL)) {
                $emails[] = $row[0];
            }
        }

        return response()->json([
            'emails' => $emails
        ]);
    }

    public function bulkEmailImportSend(Request $request)
    {
        $emails = $request->emails;
        $templateId = $request->import_template;

        $templateData = DB::table('email_templates')
        ->where('id', $templateId)
        ->first();
        
        if (empty($emails)) {
            return response()->json([
                'status' => 0,
                'message' => 'No emails found'
            ]);
        }

        if (empty($templateData)) {
            return response()->json([
                'status' => 0,
                'message' => 'No template found'
            ]);
        }

        try {
            $this->mailService->sendBulkEmail($emails, 'Promotional email', 'emails.marketings.dynamic-template',    ['html' => $templateData->template]);

            $datalogs = [
                'action' => 'Bulk Email Send',
                'client_type' => 'Leads',
                'total_email_sent'  => count($emails),
                'sent_emails'  => $emails,
                'timestamp'   => now(),
            ];

            

            return response()->json([
                'status' => 1,
                'count' => count($emails)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }
}
