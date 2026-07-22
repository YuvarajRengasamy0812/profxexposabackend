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

use App\Models\Booking;
use App\Models\Floorplan;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $query = DB::table('users_registers');

        // 🔍 Search filters
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }



        // 📄 Pagination
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


        // 📊 Stats
        $stats = (object) [
            'total' => DB::table('users_registers')->count(),
        ];

        return view('dashboard.profxusers.listusers', compact('GeneralWebmasterSections', 'profxusers', 'stats', 'userTypes'));
    }
    public function sendRegistrationEmails(Request $request)
    {
        $selectedUserIds = $request->input('user_ids', []);

        if (!is_array($selectedUserIds) || empty($selectedUserIds)) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalError', 'Please select at least one user.');
        }

        $users = DB::table('users_registers')
            ->whereIn('id', $selectedUserIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($users->isEmpty()) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalError', 'Please select users with valid email addresses.');
        }

        $sent = 0;
        $failed = [];


        foreach ($users as $user) {
            $mailData = [
                'user' => $user,
                'title' => 'Welcome to PROFX Expo Africa 2026',
                'details' => "Hi {$user->full_name},<br><br>Thank you for registering for PROFX Expo Africa 2026.<br>You can now login with your email.<br><br>Regards,<br>PROFX Team",
                'logo' => 'https://profxexpo.com/africa/assets/images/logo/profx-white.png?v=20260722-082125',
                'ticket_header' => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792010449837.png',
                'ticket_footer' => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792011237810.png',
                'downloadTicketUrl' => route('ticket.download', $user->id),
            ];

            try {
                $result = $this->mailService->sendEmail(
                    $user->email,
                    'Ticket - PROFX Expo Africa',
                    'emails.registration',
                    $mailData
                );

                if (is_array($result) && !empty($result['error'])) {
                    $failed[] = $user->email;
                    continue;
                }

                $sent++;
            } catch (\Exception $e) {
                $failed[] = $user->email;

                Log::error('Admin registration email failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($failed)) {
            return redirect()
                ->route('profxusers')
                ->with('profxSwalWarning', "{$sent} email(s) sent. Failed: " . implode(', ', $failed));
        }

        return redirect()
            ->route('profxusers')
            ->with('profxSwalSuccess', "{$sent} registration email(s) sent successfully.");
    }
    
      public function profxusersView($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $profxusers = DB::table('users_registers')->where('id', $id)->first();

        abort_if(!$profxusers, 404);

        return view('dashboard.profxusers.viewusers', compact('GeneralWebmasterSections', 'profxusers'));
    }
    
    
            public function leagueusers(Request $request)
    {

        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $query = DB::table('booking_leagues');

        // 🔍 Search filters
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }




        // 📄 Pagination
        $leagueusers = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        // 📊 Stats
        $stats = (object) [
            'total' => DB::table('booking_leagues')->count(),
        ];

        return view('dashboard.leagueusers.listusers', compact('GeneralWebmasterSections', 'leagueusers', 'stats'));
    }
    
      public function leagueusersView($id)
    {
        $GeneralWebmasterSections = WebmasterSection::where('status', 1)
            ->orderby('row_no', 'asc')
            ->get();

        $exporttitle = 'All';
        $leagueusers = DB::table('booking_leagues')->where('id', $id)->first();

        abort_if(!$leagueusers, 404);

        return view('dashboard.leagueusers.viewusers', compact('GeneralWebmasterSections', 'leagueusers'));
    }

}









