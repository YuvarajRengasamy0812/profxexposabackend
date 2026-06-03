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
use Symfony\Component\HttpFoundation\StreamedResponse;

class FloorplanController extends Controller
{
    private $uploadPath = "uploads/settings/";
    protected $mailService;




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
            $query->where('boothtitle', 'like', '%' . $request->location . '%');
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
        'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:10028',
        'company_url' => 'nullable|url|max:255',
        'status' => 'required|string|in:pending,approved,rejected',
    ]);

    // ✅ Find floorplan
    $floorplan = Floorplan::findOrFail($id);

    // ✅ Update basic fields
    $floorplan->status = $request->status;
    $floorplan->approval_message = $request->message;
    $floorplan->approved_by = $request->profile_name ?? auth()->user()->name ?? 'Admin';
    $floorplan->company_url = $request->company_url;

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

    // ✅ Save floorplan
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

        // if ($request->filled('boothtitle')) {
        //     $query->where('boothtitle', 'like', '%' . $request->location . '%');
        // }



        // 📄 Pagination
        $profxusers = $query
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends($request->query());

        // 📊 Stats
        $stats = (object) [
            'total' => DB::table('users_registers')->count(),
        ];

        return view('dashboard.profxusers.listusers', compact('GeneralWebmasterSections', 'profxusers', 'stats'));
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

        // if ($request->filled('boothtitle')) {
        //     $query->where('boothtitle', 'like', '%' . $request->location . '%');
        // }



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



