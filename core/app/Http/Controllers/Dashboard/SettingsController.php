<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Setting;
use App\Models\WebmasterSection;
use Auth;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Redirect;
use Helper;

class SettingsController extends Controller
{
    // Define Default Settings ID
    private $uploadPath = "uploads/settings/";

    public function __construct()
    {
        $this->middleware('auth');

        // Check Permissions
        if (!@Auth::user()->permissionsGroup->settings_status || !Helper::GeneralWebmasterSettings("settings_status")) {
            return Redirect::to(route('NoPermission'))->send();
        }

        \Session()->forget('_Loader_Web_Settings');

    }

    public function edit()
    {
        //

        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        $id = 1;
        $Setting = $this->getOrCreateSettings($id);
        if (!empty($Setting)) {
            return view("dashboard.settings.settings", compact("Setting", "GeneralWebmasterSections"));

        } else {
            return redirect()->route('adminHome');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id = 1 for default settings
     * @return \Illuminate\Http\Response
     */
    public function updateSiteInfo(Request $request)
    {
        //
        $id = 1;
        $Setting = $this->getOrCreateSettings($id);
        if (!empty($Setting)) {

            $this->validate($request, [
                'style_logo_en' => 'image',
                'style_logo_ar' => 'image',
                'style_fav' => 'image',
                'style_apple' => 'image',
                'style_bg_image' => 'image',
                'style_footer_bg' => 'image',
            ]);
            foreach (Helper::languagesList() as $ActiveLanguage) {

                // Start of Upload Files
                $formFileName = "style_logo_" . $ActiveLanguage->code;
                $fileFinalName = "";
                if ($request->$formFileName != "") {
                    $this->validate($request, [
                        $formFileName => 'image'
                    ]);

                    $fileFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $request->file($formFileName)->move($path, $fileFinalName);
                }

                //save file name
                if ($fileFinalName != "") {
                    // Delete a banner file
                    if ($Setting->{"style_logo_" . $ActiveLanguage->code} != "" && $Setting->{"style_logo_" . $ActiveLanguage->code} != "nologo.png") {
                        File::delete($this->uploadPath . $Setting->{"style_logo_" . $ActiveLanguage->code});
                    }

                    $Setting->{"style_logo_" . $ActiveLanguage->code} = $fileFinalName;
                }

                $Setting->{"site_title_" . $ActiveLanguage->code} = strip_tags($request->{"site_title_" . $ActiveLanguage->code});
                $Setting->{"site_desc_" . $ActiveLanguage->code} = strip_tags($request->{"site_desc_" . $ActiveLanguage->code});
                $Setting->{"site_keywords_" . $ActiveLanguage->code} = strip_tags($request->{"site_keywords_" . $ActiveLanguage->code});
                $Setting->{"contact_t1_" . $ActiveLanguage->code} = strip_tags($request->{"contact_t1_" . $ActiveLanguage->code});
                $Setting->{"contact_t7_" . $ActiveLanguage->code} = strip_tags($request->{"contact_t7_" . $ActiveLanguage->code});
            }
            $Setting->site_webmails = $request->site_webmails;
            $Setting->notify_messages_status = $request->notify_messages_status;
            $Setting->notify_comments_status = $request->notify_comments_status;
            $Setting->notify_orders_status = $request->notify_orders_status;
            $Setting->notify_table_status = $request->notify_table_status;
            $Setting->notify_private_status = $request->notify_private_status;
            $Setting->site_url = $request->site_url;


            $formFileName2 = "style_fav";
            $fileFinalName2 = "";
            if ($request->$formFileName2 != "") {
                // Delete a style_fav photo
                if ($Setting->style_fav != "" && $Setting->style_fav != "nofav.png") {
                    File::delete($this->uploadPath . $Setting->style_fav);
                }

                $fileFinalName2 = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName2)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName2)->move($path, $fileFinalName2);
            }


            $formFileName3 = "style_apple";
            $fileFinalName3 = "";
            if ($request->$formFileName3 != "") {
                // Delete a style_apple photo
                if ($Setting->style_apple != "" && $Setting->style_apple != "nofav.png") {
                    File::delete($this->uploadPath . $Setting->style_apple);
                }

                $fileFinalName3 = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName3)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName3)->move($path, $fileFinalName3);
            }


            $formFileName4 = "style_bg_image";
            $fileFinalName4 = "";
            if ($request->$formFileName4 != "") {
                // Delete a style_bg_image photo
                if ($Setting->style_bg_image != "") {
                    File::delete($this->uploadPath . $Setting->style_bg_image);
                }

                $fileFinalName4 = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName4)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName4)->move($path, $fileFinalName4);
            }


            $formFileName5 = "style_footer_bg";
            $fileFinalName5 = "";
            if ($request->$formFileName5 != "") {
                // Delete a style_footer_bg photo
                if ($Setting->style_footer_bg != "" && $Setting->style_footer_bg != "footer-bg.webp") {
                    File::delete($this->uploadPath . $Setting->style_footer_bg);
                }

                $fileFinalName5 = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName5)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName5)->move($path, $fileFinalName5);
            }

            // End of Upload Files
            if ($fileFinalName2 != "") {
                $Setting->style_fav = $fileFinalName2;
            }
            if ($fileFinalName3 != "") {
                $Setting->style_apple = $fileFinalName3;
            }

            $Setting->style_color1 = $request->style_color1;
            $Setting->style_color2 = $request->style_color2;
            $Setting->style_color3 = $request->style_color3;
            $Setting->style_color4 = $request->style_color4;
            $Setting->style_type = ($request->style_type) ? 1 : 0;
            $Setting->style_change = ($request->style_change) ? 1 : 0;
            $Setting->style_bg_type = $request->style_bg_type;
            $Setting->style_bg_pattern = $request->style_bg_pattern;
            $Setting->style_bg_color = $request->style_bg_color;
            if ($fileFinalName4 != "") {
                $Setting->style_bg_image = $fileFinalName4;
            }
            $Setting->style_subscribe = $request->style_subscribe;
            $Setting->style_footer = $request->style_footer;
            $Setting->style_header = $request->style_header;
            if ($request->photo_delete == 1) {
                // Delete style_footer_bg
                if ($Setting->style_footer_bg != "" && $Setting->style_footer_bg != "footer-bg.webp") {
                    File::delete($this->uploadPath . $Setting->style_footer_bg);
                }

                $Setting->style_footer_bg = "";
            }

            if ($fileFinalName5 != "") {
                $Setting->style_footer_bg = $fileFinalName5;
            }
            $Setting->style_preload = $request->style_preload;
            $Setting->css = $request->css_code;
            $Setting->js = $request->js_code;
            $Setting->body = $request->body_code;

            $Setting->social_link1 = $request->social_link1;
            $Setting->social_link2 = $request->social_link2;
            $Setting->social_link3 = $request->social_link3;
            $Setting->social_link4 = $request->social_link4;
            $Setting->social_link5 = $request->social_link5;
            $Setting->social_link6 = $request->social_link6;
            $Setting->social_link7 = $request->social_link7;
            $Setting->social_link8 = $request->social_link8;
            $Setting->social_link9 = $request->social_link9;
            $Setting->social_link10 = $request->social_link10;

            $Setting->contact_t3 = $request->contact_t3;
            $Setting->contact_t4 = $request->contact_t4;
            $Setting->contact_t5 = $request->contact_t5;
            $Setting->contact_t6 = $request->contact_t6;

            $Setting->site_status = $request->site_status;
            $Setting->close_msg = $request->close_msg;


            $Setting->updated_by = Auth::user()->id;

            $Setting->save();
            return redirect()->action('Dashboard\SettingsController@edit')
                ->with('doneMessage', __('backend.saveDone'))
                ->with('active_tab', $request->active_tab);
        } else {
            return redirect()->route('adminHome');
        }
    }

    private function getOrCreateSettings($id)
    {
        $Setting = Setting::find($id);
        if (!empty($Setting)) {
            return $Setting;
        }

        $columns = array_flip(Schema::getColumnListing('settings'));
        $defaults = [
            'id' => $id,
            'site_title_en' => config('app.name', 'Site Title'),
            'site_desc_en' => 'Website description',
            'site_keywords_en' => 'website',
            'site_webmails' => 'info@sitename.com',
            'notify_messages_status' => 1,
            'notify_comments_status' => 1,
            'notify_orders_status' => 1,
            'notify_table_status' => 1,
            'notify_private_status' => 1,
            'site_url' => config('app.url', url('/')),
            'site_status' => 1,
            'close_msg' => "<div class='text-center'><h1>Website Under Maintenance</h1></div>",
            'social_link1' => '#',
            'social_link2' => '#',
            'social_link3' => '#',
            'social_link4' => '#',
            'social_link5' => '#',
            'social_link6' => '#',
            'social_link7' => '#',
            'social_link8' => '#',
            'social_link9' => '#',
            'social_link10' => '#',
            'contact_t1_en' => 'Building, Street name, City, Country',
            'contact_t3' => '+(xxx) 0xxxxxxx',
            'contact_t4' => '+(xxx) 0xxxxxxx',
            'contact_t5' => '+(xxx) 0xxxxxxx',
            'contact_t6' => 'info@sitename.com',
            'contact_t7_en' => 'Sunday to Thursday 08:00 AM to 05:00 PM',
            'style_color1' => '#0cbaa4',
            'style_color2' => '#2e3e4e',
            'style_color3' => '#edf3f2',
            'style_color4' => '#dfeae8',
            'style_type' => 0,
            'style_change' => 1,
            'style_bg_type' => 0,
            'style_subscribe' => 1,
            'style_footer' => 1,
            'style_header' => 1,
            'style_footer_bg' => 'footer-bg.webp',
            'style_preload' => 1,
            'created_by' => Auth::id() ?: 1,
            'updated_by' => Auth::id() ?: 1,
        ];

        $Setting = new Setting();
        foreach ($defaults as $column => $value) {
            if (isset($columns[$column])) {
                $Setting->$column = $value;
            }
        }
        $Setting->save();

        return $Setting->fresh();
    }

}
