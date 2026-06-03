<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Http\Requests;
use App\Models\Menu;
use App\Models\Popup;
use App\Models\WebmasterBanner;
use App\Models\WebmasterSection;
use Auth;
use File;
use Helper;
use Illuminate\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Redirect;

class PopupController extends Controller
{

    private $uploadPath = "uploads/banners/";

    // Define Default Variables

    public function __construct()
    {
        $this->middleware('auth');

        // Check Permissions
        if (!@Auth::user()->permissionsGroup->popups_status || !Helper::GeneralWebmasterSettings("popups_status")) {
            return Redirect::to(route('NoPermission'))->send();
        }
    }

    public function index()
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        if (@Auth::user()->permissionsGroup->view_status) {
            $Popups = Popup::where('created_by', '=', Auth::user()->id)->orderby('row_no', 'asc')->paginate(config('smartend.backend_pagination'));
        } else {
            $Popups = Popup::orderby('row_no', 'asc')->paginate(config('smartend.backend_pagination'));
        }
        if (count($Popups) == 0) {
            // check lang cols after update
            foreach (Helper::languagesList() as $ActiveLanguage) {
                try {
                    $code = $ActiveLanguage->code;
                    // alter table
                    Schema::table('popups', function (Blueprint $table) use ($code) {
                        $table->string('title_' . $code)->nullable();
                        $table->string('details_' . $code)->nullable();
                    });
                } catch (\Exception $e) {
                }
            }
        }
        return view("dashboard.popups.list", compact("Popups", "GeneralWebmasterSections"));
    }

    public function create()
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->add_status) {
            return Redirect::to(route('NoPermission'))->send();
        }
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END
        return view("dashboard.popups.create", compact("GeneralWebmasterSections"));
    }

    public function store(Request $request)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->add_status) {
            return Redirect::to(route('NoPermission'))->send();
        }

        // validate fields
        $validate_inputs = [
            'photo' => 'image',
        ];
        $this->validate($request, $validate_inputs);

        $next_nor_no = Popup::max('row_no');
        if ($next_nor_no < 1) {
            $next_nor_no = 1;
        } else {
            $next_nor_no++;
        }

        // Start of Upload Files
        $formFileName = "photo";
        $fileFinalName = "";
        if ($request->$formFileName != "") {
            $fileFinalName = time() . rand(1111,
                    9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
            $path = $this->uploadPath;
            $request->file($formFileName)->move($path, $fileFinalName);

            // resize & optimize
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }

        $Popup = new Popup;
        $Popup->row_no = $next_nor_no;
        foreach (Helper::languagesList() as $ActiveLanguage) {
            if ($ActiveLanguage->box_status) {
                $Popup->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                $Popup->{"details_" . $ActiveLanguage->code} = $request->{"details_" . $ActiveLanguage->code};
            }
        }

        if ($fileFinalName != "") {
            $Popup->photo = $fileFinalName;
        }
        $Popup->form_id = $request->form_id;
        $Popup->show_in = ($request->show_in > 0) ? $request->show_in : 0;

        $PopupSettings = [];
        $PopupSettings["background_color"] = $request->background_color;
        $PopupSettings["period"] = $request->period;
        $PopupSettings["closable"] = $request->closable;
        $PopupSettings["delay"] = $request->delay;
        $PopupSettings["width"] = $request->width;
        $PopupSettings["height"] = $request->height;
        $PopupSettings["backdrop_opacity"] = $request->backdrop_opacity;
        $PopupSettings["photo_position"] = $request->photo_position;

        $Popup->settings = json_encode($PopupSettings);
        $Popup->code = $request->code;
        $Popup->status = 1;
        $Popup->created_by = Auth::user()->id;
        $Popup->save();

        return redirect()->action('Dashboard\PopupController@index')->with('doneMessage', __('backend.addDone'));
    }

    public function edit($id)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->edit_status) {
            return Redirect::to(route('NoPermission'))->send();
        }
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        if (@Auth::user()->permissionsGroup->view_status) {
            $Popup = Popup::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Popup = Popup::find($id);
        }
        if (!empty($Popup)) {
            return view("dashboard.popups.edit", compact("Popup", "GeneralWebmasterSections"));
        } else {
            return redirect()->action('Dashboard\PopupController@index');
        }
    }

    public function update(Request $request)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->add_status) {
            return Redirect::to(route('NoPermission'))->send();
        }

        // validate fields
        $validate_inputs = [
            'popup_id' => 'required',
            'photo' => 'image',
        ];
        $this->validate($request, $validate_inputs);

        $Popup = Popup::find($request->popup_id);
        if (!empty($Popup)) {
            foreach (Helper::languagesList() as $ActiveLanguage) {
                if ($ActiveLanguage->box_status) {
                    $Popup->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                    $Popup->{"details_" . $ActiveLanguage->code} = $request->{"details_" . $ActiveLanguage->code};
                }
            }


            // Start of Upload Files
            $formFileName = "photo";
            $fileFinalName = "";
            if ($request->$formFileName != "") {
                // Delete a Topic photo
                if ($Popup->$formFileName != "" && $Popup->$formFileName != "default.png") {
                    File::delete($this->uploadPath . $Popup->$formFileName);
                }

                $fileFinalName = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $fileFinalName);

                // resize & optimize
                Helper::imageResize($path . $fileFinalName);
                Helper::imageOptimize($path . $fileFinalName);
            }

            if ($request->photo_delete == 1) {
                // Delete photo_file
                if ($Popup->photo != "" && $Popup->photo_file != "default.png") {
                    File::delete($this->uploadPath . $Popup->photo);
                }

                $Popup->photo = "";
            }

            if ($fileFinalName != "") {
                $Popup->photo = $fileFinalName;
            }

            $Popup->form_id = $request->form_id;
            $Popup->show_in = ($request->show_in > 0) ? $request->show_in : 0;

            $PopupSettings = [];
            $PopupSettings["background_color"] = $request->background_color;
            $PopupSettings["period"] = $request->period;
            $PopupSettings["closable"] = $request->closable;
            $PopupSettings["delay"] = $request->delay;
            $PopupSettings["width"] = $request->width;
            $PopupSettings["height"] = $request->height;
            $PopupSettings["backdrop_opacity"] = $request->backdrop_opacity;
            $PopupSettings["photo_position"] = $request->photo_position;

            $Popup->settings = json_encode($PopupSettings);

            $Popup->code = $request->code;
            $Popup->status = $request->status;
            $Popup->updated_by = Auth::user()->id;
            $Popup->save();
            return redirect()->action('Dashboard\PopupController@edit', $Popup->id)->with('doneMessage', __('backend.saveDone'));
        }
        return redirect()->action('Dashboard\PopupController@index')->with('errorMessage', __('backend.error'));

    }

    public function destroy($id = 0)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->delete_status) {
            return Redirect::to(route('NoPermission'))->send();
        }
        //
        if (@Auth::user()->permissionsGroup->view_status) {
            $Popup = Popup::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Popup = Popup::find($id);
        }
        if (!empty($Popup)) {
            // Delete a Topic photo
            if ($Popup->photo != "" && $Popup->photo != "default.png") {
                File::delete($this->uploadPath . $Popup->photo);
            }
            $Popup->delete();
            return redirect()->action('Dashboard\PopupController@index')->with('doneMessage', __('backend.deleteDone'));
        } else {
            return redirect()->action('Dashboard\PopupController@index');
        }
    }

    public function updateAll(Request $request)
    {
        //
        if ($request->action == "order") {
            foreach ($request->row_ids as $rowId) {
                $Popup = Popup::find($rowId);
                if (!empty($Popup)) {
                    $row_no_val = "row_no_" . $rowId;
                    $Popup->row_no = $request->$row_no_val;
                    $Popup->save();
                }
            }

        } else {
            if ($request->ids != "") {
                if ($request->action == "activate") {
                    Popup::wherein('id', $request->ids)
                        ->update(['status' => 1]);

                } elseif ($request->action == "block") {
                    Popup::wherein('id', $request->ids)
                        ->update(['status' => 0]);

                } elseif ($request->action == "delete") {
                    // Check Permissions
                    if (!@Auth::user()->permissionsGroup->delete_status) {
                        return Redirect::to(route('NoPermission'))->send();
                    }
                    // Delete Topics photo
                    $Popups = Popup::wherein('id', $request->ids)->get();
                    foreach ($Popups as $Popup) {
                        if ($Popup->photo != "" && $Popup->photo != "default.png") {
                            File::delete($this->uploadPath . $Popup->photo);
                        }
                    }

                    Popup::wherein('id', $request->ids)
                        ->delete();

                }
            }
        }
        return redirect()->action('Dashboard\PopupController@index')->with('doneMessage', __('backend.saveDone'));
    }
}
