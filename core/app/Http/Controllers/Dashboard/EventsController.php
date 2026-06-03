<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Coupon;
use App\Http\Requests;
use App\Models\WebmasterSection;
use Auth;
use File;
use Helper;
use Illuminate\Http\Request;

class EventsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        \Session()->forget('_Loader_Events');
    }

    public function index()
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //List of Events
        if (@Auth::user()->permissionsGroup->view_status) {
            $Events = Event::where('created_by', '=', Auth::user()->id)->orderby('start_date', 'asc')->get();
        } else {
            $Events = Event::orderby('start_date', 'asc')->get();
        }
        $DefaultDate = date('Y-m-d');
        $EStatus = "";

        return view("dashboard.events.list", compact("GeneralWebmasterSections", "Events", "DefaultDate", "EStatus"));
    }

    public function create()
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //List of Events
        if (@Auth::user()->permissionsGroup->view_status) {
            $Events = Event::where('created_by', '=', Auth::user()->id)->orderby('start_date', 'asc')->get();
        } else {
            $Events = Event::orderby('start_date', 'asc')->get();
        }
        $DefaultDate = date('Y-m-d');
        $EStatus = "new";

        return view("dashboard.events.list", compact("GeneralWebmasterSections", "Events", "DefaultDate", "EStatus"));
    }

    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'type' => 'required',
            'title' => 'required'
        ]);

        $Event = new Event;
        $Event->user_id = Auth::user()->id;
        $Event->created_by = Auth::user()->id;
        $Event->type = $request->type;
        $Event->title = strip_tags($request->title);
        $Event->details = strip_tags($request->details);
        if ($request->type == 3) {
            // Task
            $Event->start_date = Helper::dateForDB($request->date_start);
            $Event->end_date = Helper::dateForDB($request->date_end);
        } elseif ($request->type == 2) {
            // Event
            $Event->start_date = Helper::dateForDB($request->time_start, 1);
            $Event->end_date = Helper::dateForDB($request->time_end, 1);
        } elseif ($request->type == 1) {
            // Meeting
            $Event->start_date = Helper::dateForDB($request->date_at, 1);
            $Event->end_date = Helper::dateForDB($request->date_at, 1);
        } else {
            // Note
            $Event->start_date = Helper::dateForDB($request->date);
            $Event->end_date = Helper::dateForDB($request->date);
        }
        $Event->save();

        return redirect()->action('Dashboard\EventsController@index')->with('doneMessage', __('backend.addDone'));
    }

    public function edit($id)
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //List of Events
        if (@Auth::user()->permissionsGroup->view_status) {
            $Events = Event::where('created_by', '=', Auth::user()->id)->orderby('start_date', 'asc')->get();
            $EditEvent = Event::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Events = Event::orderby('start_date', 'asc')->get();
            $EditEvent = Event::find($id);
        }

        if (!empty($EditEvent)) {
            $DefaultDate = $EditEvent->start_date;
            $EStatus = "edit";
            return view("dashboard.events.list",
                compact("GeneralWebmasterSections", "Events", "EditEvent", "DefaultDate", "EStatus"));
        } else {
            return redirect()->action('Dashboard\EventsController@index');
        }
    }

    public function update(Request $request, $id)
    {
        //
        $Event = Event::find($id);
        if (!empty($Event)) {

            $this->validate($request, [
                'type' => 'required',
                'title' => 'required'
            ]);

            $Event->type = $request->type;
            $Event->title = strip_tags($request->title);
            $Event->details = strip_tags($request->details);
            if ($request->type == 3) {
                // Task
                $Event->start_date = Helper::dateForDB($request->date_start);
                $Event->end_date = Helper::dateForDB($request->date_end);
            } elseif ($request->type == 2) {
                // Event
                $Event->start_date = Helper::dateForDB($request->time_start, 1);
                $Event->end_date = Helper::dateForDB($request->time_end, 1);
            } elseif ($request->type == 1) {
                // Meeting
                $Event->start_date = Helper::dateForDB($request->date_at, 1);
                $Event->end_date = Helper::dateForDB($request->date_at, 1);
            } else {
                // Note
                $Event->start_date = Helper::dateForDB($request->date);
                $Event->end_date = Helper::dateForDB($request->date);
            }
            $Event->updated_by = Auth::user()->id;
            $Event->save();
            return redirect()->action('Dashboard\EventsController@index', $id)->with('doneMessage', __('backend.saveDone'));
        } else {
            return redirect()->action('Dashboard\EventsController@index');
        }
    }

    public function destroy($id)
    {
        //
        if (@Auth::user()->permissionsGroup->view_status) {
            $Event = Event::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Event = Event::find($id);
        }
        if (!empty($Event)) {
            $Event->delete();
            return redirect()->action('Dashboard\EventsController@index')->with('doneMessage', __('backend.deleteDone'));
        } else {
            return redirect()->action('Dashboard\EventsController@index');
        }
    }

    public function updateAll()
    {
        //
        Event::where('user_id', "=", Auth::user()->id)->delete();
        return redirect()->action('Dashboard\EventsController@index')->with('doneMessage', __('backend.saveDone'));
    }

    public function extend(Request $request, $id)
    {
        //
        $Event = Event::find($id);
        if (!empty($Event)) {
            if ($Event->type == 0 && $request->ended_on != "") {
                $Event->type = 3;
            }
            if ($request->ended_on != "") {
                $Event->end_date = date("Y-m-d", strtotime($request->ended_on));
            } else {
                if ($Event->end_date != "") {
                    $dif = strtotime($request->started_on) - strtotime($Event->start_date);
                    $n = strtotime($Event->end_date) + $dif;
                    $Event->end_date = date("Y-m-d", $n);
                }
            }
            if ($request->started_on != "") {
                $Event->start_date = date("Y-m-d H:i:s", strtotime($request->started_on));
            }
            $Event->updated_by = Auth::user()->id;
            $Event->save();
        }
    }
    
    
public function couponlist()
{

       $GeneralWebmasterSections = WebmasterSection::where('status', 1)
        ->orderby('row_no', 'asc')
        ->get();
    $coupons = Coupon::orderBy('created_at', 'desc')->get();
    return view('dashboard.referalcode.listrefer', compact('GeneralWebmasterSections', 'coupons'));
}


    // Store new coupon
public function couponadd(Request $request)
{
    $request->validate([
        'coupon_name' => 'required|string|max:255',
        'percentage'  => 'required|integer|min:1|max:100',
        'coupon_code' => 'required|string|max:255|unique:coupon_codes,coupon_code', // <-- unique validation
        'status'      => 'required|boolean',
    ]);

    // If validation passes, create coupon
    Coupon::create($request->only('coupon_name', 'percentage', 'coupon_code', 'status'));

    return back()->with('success', 'Coupon created successfully!');
}


    // Get coupon for edit (AJAX)
public function couponedit($id)
{
    $GeneralWebmasterSections = WebmasterSection::where('status',1)->orderby('row_no','asc')->get();
    $coupons = Coupon::orderBy('created_at','desc')->get();
    $EditCoupon = Coupon::findOrFail($id); // Fetch the coupon to edit
    $EStatus = "edit"; // Flag to open modal
    return view('dashboard.referalcode.listrefer', compact('GeneralWebmasterSections','coupons','EditCoupon','EStatus'));
}


// AJAX: update coupon
public function couponupdate(Request $request, $id)
{
    // Find coupon or fail
    $coupon = Coupon::findOrFail($id);

    // Validate only the fields that can be changed
    $request->validate([
        'coupon_name' => 'required|string|max:255',
        'percentage'  => 'required|integer|min:1|max:100',
        'status'      => 'required|in:0,1',
    ]);

    try {
        // Update only allowed fields
        $coupon->update([
            'coupon_name' => $request->coupon_name,
            'percentage'  => $request->percentage,
            'status'      => $request->status,
        ]);

        return redirect()->route('couponlist')->with('success','Coupon updated successfully!');
    } catch(\Exception $e) {
        return back()->with('error','Failed to update coupon: '.$e->getMessage());
    }
}


// Delete coupon
public function coupondelete($id)
{
    $coupon = Coupon::findOrFail($id);
    $coupon->delete();

    return redirect()->route('couponlist')->with('success','Coupon deleted successfully!');
}


    public function checkCoupon(Request $request)
    {
        $coupon = Coupon::where('coupon_code', $request->coupon_code)
                        ->where('status', 1)
                        ->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid coupon code!'
            ]);
        }

        return response()->json([
            'valid' => true,
            'percentage' => $coupon->percentage
        ]);
    }


}
