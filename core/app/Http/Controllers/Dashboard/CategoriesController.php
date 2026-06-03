<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Section;
use App\Models\Topic;
use App\Models\TopicCategory;
use App\Models\WebmasterSection;
use Auth;
use File;
use Helper;
use Illuminate\Http\Request;
use Redirect;

class CategoriesController extends Controller
{
    private $uploadPath = "uploads/sections/";

    public function __construct()
    {
        $this->middleware('auth');

    }

    public function index($webmasterId)
    {
        // Check Permissions
        $data_sections_arr = explode(",", Auth::user()->permissionsGroup->data_sections);
        if (!in_array($webmasterId, $data_sections_arr)) {
            return Redirect::to(route('NoPermission'))->send();
        }
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //Webmaster Section Details
        $WebmasterSection = WebmasterSection::find($webmasterId);

        if (@Auth::user()->permissionsGroup->view_status) {
            $Sections = Section::where('created_by', '=', Auth::user()->id)->where('webmaster_id', '=',
                $webmasterId)->where('father_id', '0');
        } else {
            $Sections = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=', '0');
        }
        $search_word = \request()->input("q");
        if ($search_word != "") {
            $Sections = $Sections->where(function ($query) use ($search_word) {
                $query->where('title_' . Helper::currentLanguage()->code, 'like', '%' . $search_word . '%')
                    ->orwhere('seo_title_' . Helper::currentLanguage()->code, 'like', '%' . $search_word . '%');
            });
        }

        $Sections = $Sections->orderby('row_no', 'asc')->paginate(config('smartend.backend_pagination'));

        return view("dashboard.categories.list", compact("Sections", "GeneralWebmasterSections", "WebmasterSection"));
    }

    public function create($webmasterId)
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //Webmaster Section Details
        $WebmasterSection = WebmasterSection::find($webmasterId);

        $fatherSections = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=',
            '0')->orderby('row_no', 'asc')->get();

        return view("dashboard.categories.create",
            compact("GeneralWebmasterSections", "WebmasterSection", "fatherSections"));
    }

    public function store(Request $request, $webmasterId)
    {
        //
        $this->validate($request, [
            'photo' => 'image'
        ]);


        $next_nor_no = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=',
            $request->father_id)->max('row_no');
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
        // End of Upload Files

        $Section = new Section;
        $Section->row_no = $next_nor_no;

        foreach (Helper::languagesList() as $ActiveLanguage) {
            if ($ActiveLanguage->box_status) {
                $Section->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});

                // meta info
                $Section->{"seo_title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                $Section->{"seo_url_slug_" . $ActiveLanguage->code} = Helper::URLSlug(strip_tags($request->{"title_" . $ActiveLanguage->code}), "category", 0);

            }
        }
        $Section->icon = $request->icon;
        if ($fileFinalName != "") {
            $Section->photo = $fileFinalName;
        }
        $Section->webmaster_id = $webmasterId;
        $Section->father_id = $request->father_id;
        $Section->popup_id = $request->popup_id;
        $Section->visits = 0;
        $Section->status = 1;
        $Section->created_by = Auth::user()->id;

        $Section->save();

        return redirect()->action('Dashboard\CategoriesController@index', $webmasterId)->with('doneMessage',
            __('backend.addDone'));
    }

    public function clone($webmasterId, $id)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->add_status) {
            return redirect()->route('NoPermission');
        }
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            if (@Auth::user()->permissionsGroup->view_status) {
                $Category = Section::where('created_by', '=', Auth::user()->id)->find($id);
            } else {
                $Category = Section::find($id);
            }
            if (!empty($Category)) {
                $NewCategory = $Category->replicate();

                $next_nor_no = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=', $Category->father_id)->max('row_no');
                if ($next_nor_no < 1) {
                    $next_nor_no = 1;
                } else {
                    $next_nor_no++;
                }

                $NewCategory->row_no = $next_nor_no;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $NewCategory->{"title_" . $ActiveLanguage->code} = $Category->{"title_" . $ActiveLanguage->code} . " - Copy";
                        // meta info
                        if ($Category->{"seo_title_" . $ActiveLanguage->code} != "") {
                            $NewCategory->{"seo_title_" . $ActiveLanguage->code} = $Category->{"seo_title_" . $ActiveLanguage->code} . " - Copy";
                        }
                        $NewCategory->{"seo_url_slug_" . $ActiveLanguage->code} = Helper::URLSlug($Category->{"title_" . $ActiveLanguage->code}, "topic", 0);
                    }
                }
                $NewCategory->visits = 0;
                $NewCategory->created_by = Auth::user()->id;
                $NewCategory->updated_by = null;
                $NewCategory->save();

                if (@Auth::user()->permissionsGroup->edit_status) {
                    return redirect()->action('Dashboard\CategoriesController@edit', [$webmasterId, $NewCategory->id])->with('doneMessage',
                        __('backend.addDone'));
                } else {
                    return redirect()->action('Dashboard\CategoriesController@index', $webmasterId)->with('doneMessage',
                        __('backend.addDone'));
                }
            }
        }
    }

    public function edit($webmasterId, $id)
    {
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        if (@Auth::user()->permissionsGroup->view_status) {
            $Sections = Section::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Sections = Section::find($id);
        }
        if (!empty($Sections)) {
            //Section Sections Details
            $WebmasterSection = WebmasterSection::find($Sections->webmaster_id);

            $fatherSections = Section::where('webmaster_id', '=', $webmasterId)->where('id', '!=',
                $id)->where('father_id', '0')->orderby('row_no', 'asc')->get();

            return view("dashboard.categories.edit",
                compact("Sections", "GeneralWebmasterSections", "WebmasterSection", "fatherSections"));
        } else {
            return redirect()->action('Dashboard\CategoriesController@index', $webmasterId);
        }
    }

    public function update(Request $request, $webmasterId, $id)
    {
        //
        $Section = Section::find($id);
        if (!empty($Section)) {


            $this->validate($request, [
                'photo' => 'image'
            ]);


            // Start of Upload Files
            $formFileName = "photo";
            $fileFinalName = "";
            if ($request->$formFileName != "") {
                // Delete a Section photo
                if ($Section->photo != "") {
                    File::delete($this->uploadPath . $Section->photo);
                }

                $fileFinalName = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $fileFinalName);

                // resize & optimize
                Helper::imageResize($path . $fileFinalName);
                Helper::imageOptimize($path . $fileFinalName);
            }
            // End of Upload Files
            foreach (Helper::languagesList() as $ActiveLanguage) {
                if ($ActiveLanguage->box_status) {
                    $Section->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                }
            }
            $Section->icon = $request->icon;
            if ($request->photo_delete == 1) {
                // Delete photo
                if ($Section->photo != "") {
                    File::delete($this->uploadPath . $Section->photo);
                }

                $Section->photo = "";
            }

            if ($fileFinalName != "") {
                $Section->photo = $fileFinalName;
            }
            $Section->father_id = $request->father_id;
            $Section->popup_id = $request->popup_id;
            $Section->status = $request->status;
            $Section->updated_by = Auth::user()->id;
            $Section->save();
            return redirect()->action('Dashboard\CategoriesController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'));
        } else {
            return redirect()->action('Dashboard\CategoriesController@index', $webmasterId);
        }
    }

    public function seo(Request $request, $webmasterId, $id)
    {
        //
        $Section = Section::find($id);
        if (!empty($Section)) {
            foreach (Helper::languagesList() as $ActiveLanguage) {
                if ($ActiveLanguage->box_status) {
                    $Section->{"seo_title_" . $ActiveLanguage->code} = strip_tags($request->{"seo_title_" . $ActiveLanguage->code});
                    $Section->{"seo_description_" . $ActiveLanguage->code} = strip_tags($request->{"seo_description_" . $ActiveLanguage->code});
                    $Section->{"seo_keywords_" . $ActiveLanguage->code} = strip_tags($request->{"seo_keywords_" . $ActiveLanguage->code});
                    $Section->{"seo_url_slug_" . $ActiveLanguage->code} = Helper::URLSlug(strip_tags($request->{"seo_url_slug_" . $ActiveLanguage->code}), "category", $id);
                }
            }
            $Section->updated_by = Auth::user()->id;
            $Section->save();
            return redirect()->action('Dashboard\CategoriesController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'seo');
        } else {
            return redirect()->action('Dashboard\CategoriesController@index', $webmasterId);
        }
    }

    public function destroy($webmasterId, $id = 0)
    {
        //
        if (@Auth::user()->permissionsGroup->view_status) {
            $Sections = Section::where('created_by', '=', Auth::user()->id)->find($id);
        } else {
            $Section = Section::find($id);
        }

        if (!empty($Section)) {
            // Delete a Section photo
            if ($Section->photo != "") {
                File::delete($this->uploadPath . $Section->photo);
            }
            TopicCategory::where('section_id', $Section->id)->delete();
            Section::where('father_id', $Section->id)->delete();
            $Section->delete();
            return redirect()->action('Dashboard\CategoriesController@index', $webmasterId)->with('doneMessage',
                __('backend.deleteDone'));
        } else {
            return redirect()->action('Dashboard\CategoriesController@index', $webmasterId);
        }
    }

    public function updateAll(Request $request, $webmasterId)
    {
        //
        if ($request->action == "order") {
            foreach ($request->row_ids as $rowId) {
                $Section = Section::find($rowId);
                if (!empty($Section)) {
                    $row_no_val = "row_no_" . $rowId;
                    $Section->row_no = $request->$row_no_val;
                    $Section->save();
                }
            }

        } else {
            if ($request->ids != "") {
                if ($request->action == "activate") {
                    Section::wherein('id', $request->ids)
                        ->update(['status' => 1]);

                } elseif ($request->action == "block") {
                    Section::wherein('id', $request->ids)
                        ->update(['status' => 0]);

                } elseif ($request->action == "delete") {
                    // Delete Sections photo
                    $Sections = Section::wherein('id', $request->ids)->get();
                    foreach ($Sections as $Section) {
                        if ($Section->photo != "") {
                            File::delete($this->uploadPath . $Section->photo);
                        }
                    }
                    TopicCategory::wherein('section_id', $request->ids)->delete();
                    Section::wherein('father_id', $request->ids)->delete();
                    Section::wherein('id', $request->ids)
                        ->delete();

                }
            }
        }
        return redirect()->action('Dashboard\CategoriesController@index', $webmasterId)->with('doneMessage',
            __('backend.saveDone'));
    }


}
