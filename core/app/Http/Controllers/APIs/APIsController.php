<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Mail\NotificationEmail;
use App\Models\AttachFile;
use App\Models\Banner;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Language;
use App\Models\BookingLeague;
use App\Models\Map;
use App\Models\Menu;
use App\Models\Photo;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Topic;
use App\Models\TopicCategory;
use App\Models\TopicField;
use App\Models\Webmail;
use App\Models\WebmasterSection;
use App\Models\WebmasterSetting;
use App\Models\Ticket;
use App\Models\TicketUser;
use App\Models\Floorplan;
use App\Models\UserRegister;
use App\Models\Exhibitors;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\ClientSpeaker;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Mail;
use Illuminate\Support\Facades\DB;
use App\Services\MailService;
use Milon\Barcode\DNS2D; // make sure you installed milon/barcode via composer
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class APIsController extends Controller
{
    private function createReferralCodeForUser(UserRegister $user): string
    {
        return 'PFX' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
    }

    private function createReferralLink(string $referralCode, ?string $frontendUrl = null): string
    {
        $baseUrl = $frontendUrl ?: 'https://profxexpo.com/africa/LeagueEnroll';
        $separator = str_contains($baseUrl, '?') ? '&' : '?';

        return $baseUrl . $separator . 'ref=' . urlencode($referralCode);
    }

    
     protected $uploadPath = 'uploads/topics/';
    public function __construct()
    {
        // Check API Status
        if (!Helper::GeneralWebmasterSettings("api_status")) {
            // API disabled
            exit();
        }
        // Helper::SaveVisitorInfo(url()->current());
        if (!request()->is('api/*')) {
    Helper::SaveVisitorInfo(url()->current());
}
        
    }

    public function api()
    {
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
<meta charset=\"utf-8\">
<title>API v1 | Restful Web Services</title>
<body>
<br>
<div>
Restful Web Services: <br>
---------------------------------------- <br>
{ GET }     /api/v1/website/status <br>
{ GET }     /api/v1/website/info <br>
{ GET }     /api/v1/website/contacts <br>
{ GET }     /api/v1/website/style <br>
{ GET }     /api/v1/website/social <br>
{ GET }     /api/v1/website/settings <br>
{ GET }     /api/v1/menu/ <br>
{ GET }     /api/v1/banners/ <br>
{ GET }     /api/v1/section/ <br>
{ GET }     /api/v1/categories/ <br>
{ GET }     /api/v1/topics/ <br>
{ GET }     /api/v1/category/ <br>
{ GET }     /api/v1/topic/ <br>
{ GET }     /api/v1/topic/fields/ <br>
{ GET }     /api/v1/topic/photos/ <br>
{ GET }     /api/v1/topic/photo/ <br>
{ GET }     /api/v1/topic/maps/ <br>
{ GET }     /api/v1/topic/map/ <br>
{ GET }     /api/v1/topic/files/ <br>
{ GET }     /api/v1/topic/file/ <br>
{ GET }     /api/v1/topic/comments/ <br>
{ GET }     /api/v1/topic/comment/ <br>
{ GET }     /api/v1/topic/related/ <br>
{ GET }     /api/v1/user/ <br>
{ POST }   /api/v1/subscribe <br>
{ POST }   /api/v1/comment <br>
{ POST }   /api/v1/order <br>
{ POST }   /api/v1/contact <br>
---------------------------------------- <br>
For more details check <a href='http://smartfordesign.net/smartend/documentation/api.html' target='_blank'><strong>API documentation</strong></a>
</div>
</body>
</html>
        ";
        exit();
    }


public function BookingLeague(Request $request)
{
    // ? Validate request data
    $validated = $request->validate([
        'name'    => 'required|string|max:255',
        'email'   => 'required|email|max:255',
        'phone'   => 'required|string|max:20',
        'country' => 'required|string',
        'company'    => 'required|string|max:100',
        'role'    => 'required|string|max:100',
        'referral_code' => 'nullable|string|max:50',
        'api_key' => 'required|string',
    ]);

    // ? API Key check
    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json([
            'code' => -1,
            'msg'  => 'Authentication failed'
        ], 401);
    }

    $referralCode = strtoupper(trim($request->input('referral_code', '')));
    $referrer = null;

    if ($referralCode !== '') {
        $referrer = UserRegister::where('referral_code', $referralCode)->first();

        if (!$referrer) {
            return response()->json([
                'code' => -1,
                'msg'  => 'Invalid referral code'
            ], 422);
        }
    }

    // ? Save booking
    $booking = BookingLeague::create([
        'name'    => $validated['name'],
        'email'   => $validated['email'],
        'phone'   => $validated['phone'],
        'country' => $validated['country'],
        'company' => $validated['company'],
        'role'    => $validated['role'],
        'referral_code' => $referralCode ?: null,
        'referred_by_user_id' => $referrer?->id,
        'referrer_name' => $referrer?->full_name,
    ]);

    // ? Success response
    return response()->json([
        'code' => 1,
        'msg'  => 'Registration successful',
        'data' => [
            'booking_id' => $booking->id
        ]
    ], 201);
}

    public function website_status()
    {
        // Get Site Settings
        $Setting = Setting::find(1);
        // Response Details
        $msg = "";
        if ($Setting->site_status == 0) {
            $msg = nl2br($Setting->close_msg);
        }
        $response_details = [
            'status' => $Setting->site_status,
            'close_msg' => $msg
        ];
        // Response MSG
        $response = [
            'msg' => 'Website Status details',
            'details' => $response_details
        ];
        return response()->json($response, 200);
    }

    public function website_info($lang = '')
    {
        // Get Site Settings
        $Setting = Setting::find(1);

        // By Language
        $lang = $this->getLanguage($lang);
        $site_title_var = "site_title_$lang";
        $site_desc_var = "site_desc_$lang";
        $site_keywords_var = "site_keywords_$lang";

        // Response Details
        $response_details = [
            'site_url' => $Setting->site_url,
            'site_title' => $Setting->$site_title_var,
            'site_desc' => $Setting->$site_desc_var,
            'site_keywords' => $Setting->$site_keywords_var,
            'site_webmails' => $Setting->site_webmails
        ];
        // Response MSG
        $response = [
            'msg' => 'Main information about the Website',
            'details' => $response_details
        ];
        return response()->json($response, 200);
    }

    public function getLanguage($lang)
    {
        // List of active languages for API
        $Language = Language::where("status", true)->where("code", $lang)->first();

        if ($lang == "" || empty($Language)) {
            $lang = config('smartend.default_language');
        }
        return $lang;
    }

    public function website_contacts($lang = '')
    {
        // Get Site Settings
        $Setting = Setting::find(1);

        // By Language
        $lang = $this->getLanguage($lang);
        $address_var = "contact_t1_$lang";
        $working_time_var = "contact_t7_$lang";

        // Response Details
        $response_details = [
            'address' => $Setting->$address_var,
            'phone' => $Setting->contact_t3,
            'fax' => $Setting->contact_t4,
            'mobile' => $Setting->contact_t5,
            'email' => $Setting->contact_t6,
            'working_time' => $Setting->$working_time_var
        ];
        // Response MSG
        $response = [
            'msg' => 'List of Contacts Details',
            'details' => $response_details
        ];
        return response()->json($response, 200);
    }

    public function website_style($lang = '')
    {
        // Get Site Settings
        $Setting = Setting::find(1);

        // By Language
        $lang = $this->getLanguage($lang);
        $style_logo_var = "style_logo_$lang";

        // Response Details
        $response_details = [
            'logo' => ($Setting->$style_logo_var != "") ? url("") . "/uploads/settings/" . $Setting->$style_logo_var : null,
            'fav_icon' => ($Setting->style_fav != "") ? url("") . "/uploads/settings/" . $Setting->style_fav : null,
            'apple_icon' => ($Setting->style_apple != "") ? url("") . "/uploads/settings/" . $Setting->style_apple : null,
            'style_color_1' => $Setting->style_color1,
            'style_color_2' => $Setting->style_color2,
            'layout_mode' => $Setting->style_type,
            'bg_type' => $Setting->style_bg_type,
            'bg_pattern' => ($Setting->style_bg_pattern != "") ? url("") . "/uploads/pattern/" . $Setting->style_bg_pattern : null,
            'bg_color' => $Setting->style_bg_color,
            'bg_image' => ($Setting->style_bg_image != "") ? url("") . "/uploads/settings/" . $Setting->style_bg_image : null,
            'footer_style' => $Setting->style_footer,
            'footer_bg' => ($Setting->style_footer_bg != "") ? url("") . "/uploads/settings/" . $Setting->style_footer_bg : null,
            'newsletter_subscribe_status' => $Setting->style_subscribe,
            'preload_status' => $Setting->style_preload
        ];
        // Response MSG
        $response = [
            'msg' => 'List of Style Settings',
            'details' => $response_details
        ];
        return response()->json($response, 200);
    }

    public function website_social()
    {
        // Get Site Settings
        $Setting = Setting::find(1);

        // Response Details
        $response_details = [
            'facebook' => $Setting->social_link1,
            'twitter' => $Setting->social_link2,
            'google' => $Setting->social_link3,
            'linkedin' => $Setting->social_link4,
            'youtube' => $Setting->social_link5,
            'instagram' => $Setting->social_link6,
            'pinterest' => $Setting->social_link7,
            'tumblr' => $Setting->social_link8,
            'flickr' => $Setting->social_link9,
            'whatsapp' => $Setting->social_link10,
        ];
        // Response MSG
        $response = [
            'msg' => 'List of Social Networks Links',
            'details' => $response_details
        ];
        return response()->json($response, 200);
    }

    public function website_settings()
    {
        // Get Site Settings
        $WebmasterSetting = WebmasterSetting::find(1);

        // Response Details
        $response_details = [
            'new_comments_status' => $WebmasterSetting->new_comments_status,
            'allow_register_status' => $WebmasterSetting->register_status,
            'register_permission_group' => $WebmasterSetting->permission_group,
            'contact_text_page_id' => $WebmasterSetting->contact_page_id,
            'header_menu_id' => $WebmasterSetting->header_menu_id,
            'footer_menu_id' => $WebmasterSetting->footer_menu_id,
            'latest_news_section_id' => $WebmasterSetting->latest_news_section_id,
            'newsletter_contacts_group' => $WebmasterSetting->newsletter_contacts_group,
            'home_content1_section_id' => $WebmasterSetting->home_content1_section_id,
            'home_content2_section_id' => $WebmasterSetting->home_content2_section_id,
            'home_content3_section_id' => $WebmasterSetting->home_content3_section_id,
            'home_banners_section_id' => $WebmasterSetting->home_banners_section_id,
            'home_text_banners_section_id' => $WebmasterSetting->home_text_banners_section_id,
            'side_banners_section_id' => $WebmasterSetting->side_banners_section_id,
            'languages' => Helper::languagesList()
        ];
        // Response MSG
        $response = [
            'msg' => 'General Website Settings',
            'details' => $response_details
        ];
        return response()->json($response, 200);
    }

    public function menu($menu_id, $lang = '')
    {
        if ($menu_id > 0) {
            // Get menu details
            $Menu = Menu::where('father_id', $menu_id)->where('status', 1)->orderby('row_no', 'asc')->get();
            if (count($Menu) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";

                // Response Details
                $response_details = [];
                foreach ($Menu as $MenuLink) {
                    $SubMenu = Menu::where('father_id', $MenuLink->id)->where('status', 1)->orderby('row_no', 'asc')->get();
                    $sub_response_details = [];
                    if (count($SubMenu) > 0) {
                        foreach ($SubMenu as $SubMenuLink) {
                            $m_link = "";
                            if ($SubMenuLink->type == 3 || $SubMenuLink->type == 2) {
                                $m_link = $SubMenuLink->webmasterSection->name;
                            } elseif ($SubMenuLink->type == 1) {
                                $m_link = $MenuLink->link;
                            }
                            $sub_response_details[] = [
                                'id' => $SubMenuLink->id,
                                'title' => $SubMenuLink->$title_var,
                                'section_id' => $SubMenuLink->cat_id,
                                'href' => $SubMenuLink->link_en
                            ];
                        }
                    }

                    $m_link = "";
                    $sub_count = count($SubMenu);
                    if ($MenuLink->type == 3) {
                        // Section with drop list
                        $m_link = $MenuLink->webmasterSection->name;
                        $sub_count = count($MenuLink->webmasterSection->sections);
                        foreach ($MenuLink->webmasterSection->sections as $SubSection) {
                            $sub_response_details[] = [
                                'id' => $SubSection->id,
                                'title' => $SubSection->$title_var,
                                'section_id' => $MenuLink->cat_id,
                                'href' =>$SubSection->link_en
                            ];
                        }
                    } elseif ($MenuLink->type == 2) {
                        // Section Link
                        $m_link = $MenuLink->webmasterSection->name;
                    } elseif ($MenuLink->type == 1) {
                        $m_link = $MenuLink->link_en;
                    }

                   
                    $response_details[] = [
                        'id' => $MenuLink->id,
                        'title' => $MenuLink->$title_var,
                        'section_id' => $MenuLink->cat_id,
                        'href' => $m_link,
                        'sub_links_count' => $sub_count,
                        'sub_links' => $sub_response_details
                    ];
                    // sub links

                }
                // Response MSG
                $response = [
                    'msg' => 'List of Menu Links',
                    'links_count' => count($Menu),
                    'links' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function banners($group_id, $lang = '')
    {
        if ($group_id > 0) {
            // Get banners
            $Banners = Banner::where('section_id', $group_id)->where('status', 1)->orderby('row_no', 'asc')->get();
            if (count($Banners) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $details_var = "details_$lang";
                $file_var = "file_$lang";

                // Response Details
                $response_details = [];
                $type = "";
                foreach ($Banners as $Banner) {
                    $type = $Banner->webmasterBanner->type;
                    $response_details[] = [
                        'id' => $Banner->id,
                        'title' => $Banner->$title_var,
                        'details' => nl2br($Banner->$details_var),
                        'file' => ($Banner->$file_var != "") ? url("") . "/uploads/banners/" . $Banner->$file_var : null,
                        'video_type' => $Banner->video_type,
                        'youtube_link' => $Banner->youtube_link,
                        'link_url' => $Banner->link_url,
                        'icon' => $Banner->icon
                    ];
                }
                // Response MSG
                $response = [
                    'msg' => 'List of Banners',
                    'type' => $type,
                    'banners_count' => count($Banners),
                    'banners' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function section($section_id, $lang = '')
    {
        if ($section_id > 0) {
            // Get categories
            $WebmasterSections = WebmasterSection::where('id', $section_id)->where('status', 1)->get();
            if (count($WebmasterSections) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $section_title = "";
                $type = "";
                $sections_status = "";
                $title_var2 = "title_" . config('smartend.default_language');

                // Response Details
                $response_details = [];
                foreach ($WebmasterSections as $WebmasterSection) {
                    if ($WebmasterSection->$title_var != "") {
                        $section_title = $WebmasterSection->$title_var;
                    } else {
                        $section_title = $WebmasterSection->$title_var2;
                    }
                    $type = $WebmasterSection->type;
                    $sections_status = $WebmasterSection->sections_status;
                }
                // Response MSG
                $response = [
                    'msg' => 'Website Section Details',
                    'section_id' => $section_id,
                    'title' => $section_title,
                    'href' => "/" . $WebmasterSection->name,
                    'type' => $type,
                    'categories_status' => $sections_status
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function categories($section_id, $lang = '')
    {
        if ($section_id > 0) {
            $WebmasterSection = WebmasterSection::find($section_id);
            if (!empty($WebmasterSection)) {
                // if private redirect back to home
                if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                    // Empty MSG
                    $response = [
                        'msg' => 'There is no data'
                    ];
                    return response()->json($response, 404);
                }
            }

            // Get categories
            $Sections = Section::where('webmaster_id', $section_id)->where('father_id', '0')->where('status', 1)->orderby('row_no', 'asc')->get();
            if (count($Sections) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $type = "";
                $section_title = "";

                // Response Details
                $response_details = [];
                foreach ($Sections as $Section) {
                    $type = $Section->webmasterSection->type;
                    if ($Section->webmasterSection->$title_var != "") {
                        $section_title = $Section->webmasterSection->$title_var;
                    } else {
                        $section_title = $Section->webmasterSection->$title_var2;
                    }

                    $SubSections = Section::where('webmaster_id', $section_id)->where('father_id', $Section->id)->where('status', 1)->orderby('row_no', 'asc')->get();
                    $sub_response_details = [];
                    foreach ($SubSections as $SubSection) {
                        if ($SubSection->$title_var != "") {
                            $SubCat_title = $SubSection->$title_var;
                        } else {
                            $SubCat_title = $SubSection->$title_var2;
                        }
                        $sub_response_details[] = [
                            'id' => $SubSection->id,
                            'title' => $SubCat_title,
                            'icon' => $SubSection->icon,
                            'photo' => ($SubSection->photo != "") ? url("") . "/uploads/sections/" . $SubSection->photo : null,
                            'href' => "topics/cat/" . $SubSection->id,
                        ];
                    }
                    if ($Section->$title_var != "") {
                        $cat_title = $Section->$title_var;
                    } else {
                        $cat_title = $Section->$title_var2;
                    }
                    $response_details[] = [
                        'id' => $Section->id,
                        'title' => $cat_title,
                        'icon' => $Section->icon,
                        'photo' => ($Section->photo != "") ? url("") . "/uploads/sections/" . $Section->photo : null,
                        'href' => "topics/cat/" . $Section->id,
                        'sub_categories_count' => count($SubSections),
                        'sub_categories' => $sub_response_details
                    ];

                }
                // Response MSG
                $response = [
                    'msg' => 'List of Categories',
                    'section_id' => $section_id,
                    'section_title' => $section_title,
                    'type' => $type,
                    'categories_count' => count($Sections),
                    'categories' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topics($section_id, $page_number = 1, $topics_count = 0, $lang = '')
    {
        if ($section_id > 0) {
            $WebmasterSection = WebmasterSection::find($section_id);
            if (!empty($WebmasterSection)) {
                // if private redirect back to home
                if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                    // Empty MSG
                    $response = [
                        'msg' => 'There is no data'
                    ];
                    return response()->json($response, 404);
                }
            }

            if ($page_number < 1) {
                $page_number = 1;
            }
            Paginator::currentPageResolver(function () use ($page_number) {
                return $page_number;
            });

            // Get topics
            $Topics = Topic::where([['webmaster_id', '=', $section_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['webmaster_id', '=', $section_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc');

            if ($topics_count > 0) {
                $Topics = $Topics->paginate($topics_count);
            } else {
                $Topics = $Topics->get();
            }

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $details_var = "details_$lang";
                $details_var2 = "details_" . config('smartend.default_language');
                $type = "";
                $section_title = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {
                    $type = $Topic->webmasterSection->type;
                    if ($Topic->webmasterSection->$title_var != "") {
                        $section_title = $Topic->webmasterSection->$title_var;
                    } else {
                        $section_title = $Topic->webmasterSection->$title_var2;
                    }


                    $Joined_categories = [];
                    foreach ($Topic->categories as $category) {
                        if ($category->section->$title_var != "") {
                            $Cat_title = $category->section->$title_var;
                        } else {
                            $Cat_title = $category->section->$title_var2;
                        }
                        $Joined_categories[] = [
                            'id' => $category->id,
                            'title' => $Cat_title,
                            'icon' => $category->section->icon,
                            'photo' => ($category->section->photo != "") ? url("") . "/uploads/sections/" . $category->section->photo : null,
                            'href' => "topics/cat/" . $category->id
                        ];
                    }

                    // additional fields
                    $Additional_fields = [];
                    foreach ($Topic->webmasterSection->customFields->where("in_listing", true) as $customField) {
                        if ($customField->in_page) {

                            $cf_saved_val = "";
                            $cf_saved_val_array = array();
                            if (count($Topic->fields) > 0) {
                                foreach ($Topic->fields as $t_field) {
                                    if ($t_field->field_id == $customField->id) {
                                        if ($customField->type == 7) {
                                            // if multi check
                                            $cf_saved_val_array = explode(", ", $t_field->field_value);
                                            $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                            $cf_details_var2 = "details_" . config('smartend.default_language');
                                            if ($customField->$cf_details_var != "") {
                                                $cf_details = $customField->$cf_details_var;
                                            } else {
                                                $cf_details = $customField->$cf_details_var2;
                                            }
                                            $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                            $line_num = 1;
                                            foreach ($cf_details_lines as $cf_details_line) {
                                                if (in_array($line_num, $cf_saved_val_array)) {
                                                    $cf_saved_val .= $cf_details_line . ", ";
                                                }
                                                $line_num++;
                                            }
                                            $cf_saved_val = substr($cf_saved_val, 0, -2);
                                        } else {
                                            $cf_saved_val = $t_field->field_value;
                                        }
                                    }
                                }
                            }

                            if (($cf_saved_val != "" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == "$lang")) {
                                $Additional_fields[] = [
                                    'type' => $customField->type,
                                    'title' => $customField->$title_var,
                                    'value' => $cf_saved_val,
                                ];
                            }
                        }
                    }

                    $video_file = $Topic->video_file;
                    if ($Topic->video_type == 0) {
                        $video_file = ($Topic->video_file != "") ? url("") . "/uploads/topics/" . $Topic->video_file : "";
                    }
                    if ($Topic->$title_var != "") {
                        $Topic_title = $Topic->$title_var;
                    } else {
                        $Topic_title = $Topic->$title_var2;
                    }
                    if ($Topic->$details_var != "") {
                        $Topic_details = $Topic->$details_var;
                    } else {
                        $Topic_details = $Topic->$details_var2;
                    }
                    $response_details[] = [
                        'id' => $Topic->id,
                        'title' => $Topic_title,
                        'details' => $Topic_details,
                        'date' => $Topic->date,
                        'video_type' => $Topic->video_type,
                        'video_file' => $video_file,
                        'photo_file' => ($Topic->photo_file != "") ? url("") . "/uploads/topics/" . $Topic->photo_file : null,
                        'attach_file' => ($Topic->attach_file != "") ? url("") . "/uploads/topics/" . $Topic->attach_file : null,
                        'audio_file' => ($Topic->audio_file != "") ? url("") . "/uploads/topics/" . $Topic->audio_file : null,
                        'icon' => $Topic->icon,
                        'visits' => $Topic->visits,
                        'href' => "topic/" . $Topic->id,
                        'fields_count' => count($Additional_fields),
                        'fields' => $Additional_fields,
                        'Joined_categories_count' => count($Topic->categories),
                        'Joined_categories' => $Joined_categories,
                        'user' => [
                            'id' => $Topic->user->id,
                            'name' => $Topic->user->name,
                            'href' => "user/" . $Topic->user->id . "/topics",
                        ]

                    ];

                }
                // Response MSG
                $response = [
                    'msg' => 'List of Topics',
                    'section_id' => $section_id,
                    'section_title' => $section_title,
                    'type' => $type,
                    'topics_count' => count($Topics),
                    'topics' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function category($cat_id, $page_number = 1, $topics_count = 0, $lang = '')
    {
        if ($cat_id > 0) {
            if ($page_number < 1) {
                $page_number = 1;
            }
            Paginator::currentPageResolver(function () use ($page_number) {
                return $page_number;
            });

            $category_topics = array();
            $TopicCategories = TopicCategory::where('section_id', $cat_id)->get();
            foreach ($TopicCategories as $category) {
                $category_topics[] = $category->topic_id;
            }


            $Topics = Topic::where(function ($q) {
                $q->where([['status', 1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['status', 1], ['expire_date', null]]);
            })->whereIn('id', $category_topics)->orderby('row_no', config('smartend.frontend_topics_order'))->orderby('id', config('smartend.frontend_topics_order'));

            if ($topics_count > 0) {
                $Topics = $Topics->paginate($topics_count);
            } else {
                $Topics = $Topics->get();
            }

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $details_var = "details_$lang";
                $details_var2 = "details_" . config('smartend.default_language');
                $cat_title = "";

                $CurrentCategory = Section::find($cat_id);
                if (!empty($CurrentCategory)) {
                    $cat_title = $CurrentCategory->$title_var;
                }

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {
                    $Joined_categories = [];
                    foreach ($Topic->categories as $category) {
                        if ($category->section->$title_var != "") {
                            $Cat_title = $category->section->$title_var;
                        } else {
                            $Cat_title = $category->section->$title_var2;
                        }
                        $Joined_categories[] = [
                            'id' => $category->id,
                            'title' => $Cat_title,
                            'icon' => $category->section->icon,
                            'photo' => ($category->section->photo != "") ? url("") . "/uploads/sections/" . $category->section->photo : null,
                            'href' => "topics/cat/" . $category->id
                        ];
                    }

                    // additional fields
                    $Additional_fields = [];
                    foreach ($Topic->webmasterSection->customFields->where("in_listing", true) as $customField) {
                        if ($customField->in_page) {

                            $cf_saved_val = "";
                            $cf_saved_val_array = array();
                            if (count($Topic->fields) > 0) {
                                foreach ($Topic->fields as $t_field) {
                                    if ($t_field->field_id == $customField->id) {
                                        if ($customField->type == 7) {
                                            // if multi check
                                            $cf_saved_val_array = explode(", ", $t_field->field_value);
                                            $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                            $cf_details_var2 = "details_" . config('smartend.default_language');
                                            if ($customField->$cf_details_var != "") {
                                                $cf_details = $customField->$cf_details_var;
                                            } else {
                                                $cf_details = $customField->$cf_details_var2;
                                            }
                                            $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                            $line_num = 1;
                                            foreach ($cf_details_lines as $cf_details_line) {
                                                if (in_array($line_num, $cf_saved_val_array)) {
                                                    $cf_saved_val .= $cf_details_line . ", ";
                                                }
                                                $line_num++;
                                            }
                                            $cf_saved_val = substr($cf_saved_val, 0, -2);
                                        } else {
                                            $cf_saved_val = $t_field->field_value;
                                        }
                                    }
                                }
                            }

                            if (($cf_saved_val != "" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == "$lang")) {
                                $Additional_fields[] = [
                                    'type' => $customField->type,
                                    'title' => $customField->$title_var,
                                    'value' => $cf_saved_val,
                                ];
                            }
                        }
                    }

                    $video_file = $Topic->video_file;
                    if ($Topic->video_type == 0) {
                        $video_file = ($Topic->video_file != "") ? url("") . "/uploads/topics/" . $Topic->video_file : "";
                    }
                    if ($Topic->$title_var != "") {
                        $Topic_title = $Topic->$title_var;
                    } else {
                        $Topic_title = $Topic->$title_var2;
                    }
                    if ($Topic->$details_var != "") {
                        $Topic_details = $Topic->$details_var;
                    } else {
                        $Topic_details = $Topic->$details_var2;
                    }
                    $response_details[] = [
                        'id' => $Topic->id,
                        'title' => $Topic_title,
                        'details' => $Topic_details,
                        'date' => $Topic->date,
                        'video_type' => $Topic->video_type,
                        'video_file' => $video_file,
                        'photo_file' => ($Topic->photo_file != "") ? url("") . "/uploads/topics/" . $Topic->photo_file : null,
                        'audio_file' => ($Topic->audio_file != "") ? url("") . "/uploads/topics/" . $Topic->audio_file : null,
                        'icon' => $Topic->icon,
                        'visits' => $Topic->visits,
                        'href' => "topic/" . $Topic->id,
                        'fields_count' => count($Additional_fields),
                        'fields' => $Additional_fields,
                        'Joined_categories_count' => count($Topic->categories),
                        'Joined_categories' => $Joined_categories,
                        'user' => [
                            'id' => $Topic->user->id,
                            'name' => $Topic->user->name,
                            'href' => "user/" . $Topic->user->id . "/topics",
                        ]

                    ];

                }
                // Response MSG
                $response = [
                    'msg' => 'List of Topics',
                    'cat_id' => $cat_id,
                    'cat_title' => $cat_title,
                    'topics_count' => count($Topics),
                    'topics' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topic($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $details_var = "details_$lang";
                $details_var2 = "details_" . config('smartend.default_language');

                $type = "";
                $section_id = "";
                $section_title = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    $type = $Topic->webmasterSection->type;
                    $section_id = $Topic->webmasterSection->id;
                    if ($Topic->webmasterSection->$title_var != "") {
                        $section_title = $Topic->webmasterSection->$title_var;
                    } else {
                        $section_title = $Topic->webmasterSection->$title_var2;
                    }

                    // additional fields
                    $Additional_fields = [];
                    foreach ($Topic->webmasterSection->customFields->where("in_page", true) as $customField) {
                        if ($customField->in_page) {
                            $cf_saved_val = "";
                            $cf_saved_val_array = array();
                            if (count($Topic->fields) > 0) {
                                foreach ($Topic->fields as $t_field) {
                                    if ($t_field->field_id == $customField->id) {
                                        if ($customField->type == 7) {
                                            // if multi check
                                            $cf_saved_val_array = explode(", ", $t_field->field_value);
                                            $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                            $cf_details_var2 = "details_" . config('smartend.default_language');
                                            if ($customField->$cf_details_var != "") {
                                                $cf_details = $customField->$cf_details_var;
                                            } else {
                                                $cf_details = $customField->$cf_details_var2;
                                            }
                                            $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                            $line_num = 1;
                                            foreach ($cf_details_lines as $cf_details_line) {
                                                if (in_array($line_num, $cf_saved_val_array)) {
                                                    $cf_saved_val .= $cf_details_line . ", ";
                                                }
                                                $line_num++;
                                            }
                                            $cf_saved_val = substr($cf_saved_val, 0, -2);

                                        } else {
                                            $cf_saved_val = $t_field->field_value;
                                        }
                                    }
                                }
                            }

                            if (($cf_saved_val != "" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == "$lang")) {
                                $Additional_fields[] = [
                                    'type' => $customField->type,
                                    'title' => $customField->$title_var,
                                    'value' => $cf_saved_val,
                                ];
                            }
                        }
                    }

                    // categories
                    $Joined_categories = [];
                    foreach ($Topic->categories as $category) {
                        if ($category->section->$title_var != "") {
                            $Cat_title = $category->section->$title_var;
                        } else {
                            $Cat_title = $category->section->$title_var2;
                        }
                        $Joined_categories[] = [
                            'id' => $category->id,
                            'title' => $Cat_title,
                            'icon' => $category->section->icon,
                            'photo' => ($category->section->photo != "") ? url("") . "/uploads/sections/" . $category->section->photo : null,
                            'href' => "topics/cat/" . $category->id
                        ];
                    }
                    // photos
                    $Photos = [];
                    foreach ($Topic->photos as $photo) {
                        $Photos[] = [
                            'id' => $photo->id,
                            'title' => $photo->title,
                            'url' => ($photo->file != "") ? url("") . "/uploads/topics/" . $photo->file : null,
                            'href' => "/topic/photo/" . $photo->id
                        ];
                    }
                    // maps
                    $Maps = [];
                    foreach ($Topic->maps as $map) {

                        if ($map->$title_var != "") {
                            $map_title = $map->$title_var;
                        } else {
                            $map_title = $map->$title_var2;
                        }
                        if ($map->$details_var != "") {
                            $map_details = $map->$details_var;
                        } else {
                            $map_details = $map->$details_var2;
                        }

                        $Maps[] = [
                            'id' => $map->id,
                            'longitude' => $map->longitude,
                            'latitude' => $map->latitude,
                            'title' => $map_title,
                            'details' => $map_details,
                            'href' => "/topic/map/" . $map->id
                        ];
                    }
                    // attach files
                    $Attach_files = [];
                    foreach ($Topic->attachFiles as $attachFile) {
                        if ($attachFile->$title_var != "") {
                            $attachFile_title = $attachFile->$title_var;
                        } else {
                            $attachFile_title = $attachFile->$title_var2;
                        }
                        $Attach_files[] = [
                            'id' => $attachFile->id,
                            'title' => $attachFile_title,
                            'url' => ($attachFile->file != "") ? url("") . "/uploads/topics/" . $attachFile->file : null,
                            'href' => "/topic/file/" . $attachFile->id
                        ];
                    }
                    // comments
                    $Comments = [];
                    foreach ($Topic->approvedComments as $comment) {
                        $Comments[] = [
                            'id' => $comment->id,
                            'name' => $comment->name,
                            'email' => $comment->email,
                            'date' => $comment->date,
                            'comment' => nl2br($comment->comment),
                            'href' => "/topic/comment/" . $comment->id
                        ];
                    }
                    // related topics
                    $Related_topics = [];
                    foreach ($Topic->relatedTopics as $relatedTopic) {
                        if ($relatedTopic->topic->$title_var != "") {
                            $relatedTopic_title = $relatedTopic->topic->$title_var;
                        } else {
                            $relatedTopic_title = $relatedTopic->topic->$title_var2;
                        }
                        $Related_topics[] = [
                            'id' => $relatedTopic->topic->id,
                            'title' => $relatedTopic_title,
                            'date' => $relatedTopic->topic->date,
                            'href' => "topic/" . $relatedTopic->topic->id,
                            'photo_file' => ($relatedTopic->topic->photo_file != "") ? url("") . "/uploads/topics/" . $relatedTopic->topic->photo_file : null
                        ];
                    }

                    $video_file = $Topic->video_file;
                    if ($Topic->video_type == 0) {
                        $video_file = ($Topic->video_file != "") ? url("") . "/uploads/topics/" . $Topic->video_file : "";
                    }

                    if ($Topic->$title_var != "") {
                        $Topic_title = $Topic->$title_var;
                    } else {
                        $Topic_title = $Topic->$title_var2;
                    }
                    if ($Topic->$details_var != "") {
                        $Topic_details = $Topic->$details_var;
                    } else {
                        $Topic_details = $Topic->$details_var2;
                    }

                    $response_details[] = [
                        'id' => $Topic->id,
                        'title' => $Topic_title,
                        'details' => $Topic_details,
                        'date' => $Topic->date,
                        'video_type' => $Topic->video_type,
                        'video_file' => $video_file,
                        'photo_file' => ($Topic->photo_file != "") ? url("") . "/uploads/topics/" . $Topic->photo_file : null,
                        'audio_file' => ($Topic->audio_file != "") ? url("") . "/uploads/topics/" . $Topic->audio_file : null,
                        'icon' => $Topic->icon,
                        'visits' => $Topic->visits,
                        'href' => "topic/" . $Topic->id,
                        'fields_count' => count($Additional_fields),
                        'fields' => $Additional_fields,
                        'Joined_categories_count' => count($Joined_categories),
                        'Joined_categories' => $Joined_categories,
                        'photos_count' => count($Photos),
                        'photos' => $Photos,
                        'attach_files_count' => count($Attach_files),
                        'attach_files' => $Attach_files,
                        'maps_count' => count($Maps),
                        'maps' => $Maps,
                        'comments_count' => count($Comments),
                        'comments' => $Comments,
                        'related_topics_count' => count($Related_topics),
                        'related_topics' => $Related_topics,
                        'user' => [
                            'id' => $Topic->user->id,
                            'name' => $Topic->user->name,
                            'href' => "user/" . $Topic->user->id . "/topics",
                        ]

                    ];

                }
                // Response MSG
                $response = [
                    'msg' => 'Details of topic',
                    'section_id' => $section_id,
                    'section_title' => $section_title,
                    'type' => $type,
                    'topic' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topic_photos($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $topic_title = "";
                $photo_file = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    if ($Topic->$title_var != "") {
                        $topic_title = $Topic->$title_var;
                    } else {
                        $topic_title = $Topic->$title_var2;
                    }
                    $photo_file = $Topic->photo_file;

                    // photos
                    $response_details = [];
                    foreach ($Topic->photos as $photo) {
                        $response_details[] = [
                            'id' => $photo->id,
                            'title' => $photo->title,
                            'url' => ($photo->file != "") ? url("") . "/uploads/topics/" . $photo->file : null,
                            'href' => "/topic/photo/" . $photo->id
                        ];
                    }

                }
                // Response MSG
                $response = [
                    'msg' => 'Photos of topic',
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                    'topic_link' => "topic/" . $topic_id,
                    'topic_photo' => ($photo_file != "") ? url("") . "/uploads/topics/" . $photo_file : null,
                    'photos_count' => count($response_details),
                    'photos' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topic_photo($photo_id, $lang = '')
    {
        if ($photo_id > 0) {

            // Get Photo details
            $Photo = Photo::find($photo_id);

            if (!empty($Photo)) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $topic_title = "";
                $photo_file = "";

                $response_details[] = [
                    'id' => $Photo->id,
                    'title' => $Photo->title,
                    'url' => ($Photo->file != "") ? url("") . "/uploads/topics/" . $Photo->file : null
                ];

                // Response MSG
                $response = [
                    'msg' => 'Photo details',
                    'topic_id' => $Photo->topic_id,
                    'photo' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topic_maps($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $details_var = "details_$lang";
                $details_var2 = "details_" . config('smartend.default_language');
                $topic_title = "";
                $photo_file = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    if ($Topic->$title_var != "") {
                        $topic_title = $Topic->$title_var;
                    } else {
                        $topic_title = $Topic->$title_var2;
                    }
                    $photo_file = $Topic->photo_file;

                    // maps
                    $response_details = [];
                    foreach ($Topic->maps as $map) {

                        if ($map->$title_var != "") {
                            $map_title = $map->$title_var;
                        } else {
                            $map_title = $map->$title_var2;
                        }
                        if ($map->$details_var != "") {
                            $map_details = $map->$details_var;
                        } else {
                            $map_details = $map->$details_var2;
                        }

                        $response_details[] = [
                            'id' => $map->id,
                            'longitude' => $map->longitude,
                            'latitude' => $map->latitude,
                            'title' => $map_title,
                            'details' => $map_details,
                            'href' => "/topic/map/" . $map->id
                        ];
                    }

                }
                // Response MSG
                $response = [
                    'msg' => 'Maps of topic',
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                    'topic_link' => "topic/" . $topic_id,
                    'topic_photo' => ($photo_file != "") ? url("") . "/uploads/topics/" . $photo_file : null,
                    'maps_count' => count($response_details),
                    'maps' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topic_map($map_id, $lang = '')
    {
        if ($map_id > 0) {

            // Get map details
            $Map = Map::find($map_id);

            if (!empty($Map)) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $details_var = "details_$lang";
                $details_var2 = "details_" . config('smartend.default_language');

                if ($map->$title_var != "") {
                    $map_title = $map->$title_var;
                } else {
                    $map_title = $map->$title_var2;
                }
                if ($map->$details_var != "") {
                    $map_details = $map->$details_var;
                } else {
                    $map_details = $map->$details_var2;
                }

                $response_details[] = [
                    'id' => $Map->id,
                    'longitude' => $Map->longitude,
                    'latitude' => $Map->latitude,
                    'title' => $map_title,
                    'details' => $map_details
                ];

                // Response MSG
                $response = [
                    'msg' => 'Map details',
                    'topic_id' => $Map->topic_id,
                    'map' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

    public function topic_files($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $topic_title = "";
                $photo_file = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    if ($Topic->$title_var != "") {
                        $topic_title = $Topic->$title_var;
                    } else {
                        $topic_title = $Topic->$title_var2;
                    }
                    $photo_file = $Topic->photo_file;

                    // attach files
                    $response_details = [];
                    foreach ($Topic->attachFiles as $attachFile) {
                        if ($attachFile->$title_var != "") {
                            $attachFile_title = $attachFile->$title_var;
                        } else {
                            $attachFile_title = $attachFile->$title_var2;
                        }
                        $response_details[] = [
                            'id' => $attachFile->id,
                            'title' => $attachFile_title,
                            'url' => ($attachFile->file != "") ? url("") . "/uploads/topics/" . $attachFile->file : null,
                            'href' => "/topic/file/" . $attachFile->id
                        ];
                    }

                }

                // Response MSG
                $response = [
                    'msg' => 'Attach files of topic',
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                    'topic_link' => "topic/" . $topic_id,
                    'topic_photo' => ($photo_file != "") ? url("") . "/uploads/topics/" . $photo_file : null,
                    'files_count' => count($response_details),
                    'files' => $response_details
                ];

                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];

            return response()->json($response, 404);
        }
    }

    public function topic_file($file_id, $lang = '')
    {
        if ($file_id > 0) {

            // Get topic details
            $AttachFile = AttachFile::find($file_id);

            if (!empty($AttachFile)) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                if ($AttachFile->$title_var != "") {
                    $attachFile_title = $AttachFile->$title_var;
                } else {
                    $attachFile_title = $AttachFile->$title_var2;
                }
                $response_details[] = [
                    'id' => $AttachFile->id,
                    'title' => $attachFile_title,
                    'url' => ($AttachFile->file != "") ? url("") . "/uploads/topics/" . $AttachFile->file : null
                ];

                // Response MSG
                $response = [
                    'msg' => 'Attach file details',
                    'topic_id' => $AttachFile->topic_id,
                    'file' => $response_details
                ];

                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];

            return response()->json($response, 404);
        }
    }

    public function topic_comments($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $topic_title = "";
                $photo_file = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    if ($Topic->$title_var != "") {
                        $topic_title = $Topic->$title_var;
                    } else {
                        $topic_title = $Topic->$title_var2;
                    }
                    $photo_file = $Topic->photo_file;

                    // comments
                    $response_details = [];
                    foreach ($Topic->approvedComments as $comment) {
                        $response_details[] = [
                            'id' => $comment->id,
                            'name' => $comment->name,
                            'email' => $comment->email,
                            'date' => $comment->date,
                            'comment' => nl2br($comment->comment),
                            'href' => "/topic/comment/" . $comment->id
                        ];
                    }

                }
                // Response MSG
                $response = [
                    'msg' => 'Comments of topic',
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                    'topic_link' => "topic/" . $topic_id,
                    'topic_photo' => ($photo_file != "") ? url("") . "/uploads/topics/" . $photo_file : null,
                    'comments_count' => count($response_details),
                    'comments' => $response_details
                ];
                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];

            return response()->json($response, 404);
        }
    }

    public function topic_comment($comment_id, $lang = '')
    {
        if ($comment_id > 0) {

            // Get topic details
            $Comment = Comment::find($comment_id);

            if (!empty($Comment)) {
                $response_details[] = [
                    'id' => $Comment->id,
                    'name' => $Comment->name,
                    'email' => $Comment->email,
                    'date' => $Comment->date,
                    'comment' => nl2br($Comment->comment)
                ];
                // Response MSG
                $response = [
                    'msg' => 'Comment details',
                    'topic_id' => $Comment->topic_id,
                    'comment' => $response_details
                ];

                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];

            return response()->json($response, 404);
        }
    }

    public function topic_related($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $topic_title = "";
                $photo_file = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    if ($Topic->$title_var != "") {
                        $topic_title = $Topic->$title_var;
                    } else {
                        $topic_title = $Topic->$title_var2;
                    }
                    $photo_file = $Topic->photo_file;

                    // related topics
                    $response_details = [];
                    foreach ($Topic->relatedTopics as $relatedTopic) {
                        if ($relatedTopic->topic->$title_var != "") {
                            $relatedTopic_title = $relatedTopic->topic->$title_var;
                        } else {
                            $relatedTopic_title = $relatedTopic->topic->$title_var2;
                        }
                        $response_details[] = [
                            'id' => $relatedTopic->topic->id,
                            'title' => $relatedTopic_title,
                            'date' => $relatedTopic->topic->date,
                            'href' => "topic/" . $relatedTopic->topic->id,
                            'photo_file' => ($relatedTopic->topic->photo_file != "") ? url("") . "/uploads/topics/" . $relatedTopic->topic->photo_file : null,
                        ];
                    }

                }
                // Response MSG
                $response = [
                    'msg' => 'Related topics of topic',
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                    'topic_link' => "topic/" . $topic_id,
                    'topic_photo' => ($photo_file != "") ? url("") . "/uploads/topics/" . $photo_file : null,
                    'related_topics_count' => count($response_details),
                    'related_topics' => $response_details
                ];

                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];

            return response()->json($response, 404);
        }
    }

    public function topic_fields($topic_id, $lang = '')
    {
        if ($topic_id > 0) {

            // Get topic details
            $Topics = Topic::where([['id', '=', $topic_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['id', '=', $topic_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc')->get();

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);
                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $topic_title = "";
                $photo_file = "";

                // Response Details
                $response_details = [];
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            // Empty MSG
                            $response = [
                                'msg' => 'There is no data'
                            ];
                            return response()->json($response, 404);
                        }
                    }

                    if ($Topic->$title_var != "") {
                        $topic_title = $Topic->$title_var;
                    } else {
                        $topic_title = $Topic->$title_var2;
                    }
                    $photo_file = $Topic->photo_file;

                    // additional fields
                    $response_details = [];
                    foreach ($Topic->webmasterSection->customFields->where("in_page", true) as $customField) {
                        if ($customField->in_page) {
                            $cf_saved_val = "";
                            $cf_saved_val_array = array();
                            if (count($Topic->fields) > 0) {
                                foreach ($Topic->fields as $t_field) {
                                    if ($t_field->field_id == $customField->id) {
                                        if ($customField->type == 7) {
                                            // if multi check
                                            $cf_saved_val_array = explode(", ", $t_field->field_value);
                                            $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                            $cf_details_var2 = "details_" . config('smartend.default_language');
                                            if ($customField->$cf_details_var != "") {
                                                $cf_details = $customField->$cf_details_var;
                                            } else {
                                                $cf_details = $customField->$cf_details_var2;
                                            }
                                            $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                            $line_num = 1;
                                            foreach ($cf_details_lines as $cf_details_line) {
                                                if (in_array($line_num, $cf_saved_val_array)) {
                                                    $cf_saved_val .= $cf_details_line . ", ";
                                                }
                                                $line_num++;
                                            }
                                            $cf_saved_val = substr($cf_saved_val, 0, -2);
                                        } else {
                                            $cf_saved_val = $t_field->field_value;
                                        }
                                    }
                                }
                            }

                            if (($cf_saved_val != "" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == "$lang")) {
                                $response_details[] = [
                                    'type' => $customField->type,
                                    'title' => $customField->$title_var,
                                    'value' => $cf_saved_val,
                                ];
                            }
                        }
                    }

                }
                // Response MSG
                $response = [
                    'msg' => 'Additional Fields of topic',
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                    'topic_link' => "topic/" . $topic_id,
                    'topic_photo' => ($photo_file != "") ? url("") . "/uploads/topics/" . $photo_file : null,
                    'fields_count' => count($response_details),
                    'fields' => $response_details
                ];

                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];

            return response()->json($response, 404);
        }
    }

    public function user_topics($user_id, $page_number = 1, $topics_count = 0, $lang = '')
    {
        if ($user_id > 0) {
            if ($page_number < 1) {
                $page_number = 1;
            }
            Paginator::currentPageResolver(function () use ($page_number) {
                return $page_number;
            });

            // Get topics
            $Topics = Topic::where([['created_by', '=', $user_id], ['status',
                1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orWhere([['created_by', '=', $user_id], ['status', 1], ['expire_date', null]])->orderby('row_no', 'asc');

            if ($topics_count > 0) {
                $Topics = $Topics->paginate($topics_count);
            } else {
                $Topics = $Topics->get();
            }

            if (count($Topics) > 0) {
                // By Language
                $lang = $this->getLanguage($lang);

                $title_var = "title_$lang";
                $title_var2 = "title_" . config('smartend.default_language');
                $details_var = "details_$lang";
                $details_var2 = "details_" . config('smartend.default_language');
                $user_name = "";

                // Response Details
                $response_details = [];
                $ic = 0;
                foreach ($Topics as $Topic) {

                    $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);
                    if (!empty($WebmasterSection)) {
                        // if private redirect back to home
                        if ($WebmasterSection->type == 4 || $WebmasterSection->type == 7) {
                            continue;
                        }
                    }
                    $ic++;
                    $type = $Topic->webmasterSection->type;
                    $section_name = $Topic->webmasterSection->name;
                    $section_id = $Topic->webmasterSection->id;
                    $user_name = $Topic->user->name;


                    $Joined_categories = [];
                    foreach ($Topic->categories as $category) {

                        if ($category->section->$title_var != "") {
                            $category_title = $category->section->$title_var;
                        } else {
                            $category_title = $category->section->$title_var2;
                        }

                        $Joined_categories[] = [
                            'id' => $category->id,
                            'title' => $category_title,
                            'icon' => $category->section->icon,
                            'photo' => ($category->section->photo != "") ? url("") . "/uploads/sections/" . $category->section->photo : null,
                            'href' => "topics/cat/" . $category->id
                        ];
                    }

                    // additional fields
                    $Additional_fields = [];
                    foreach ($Topic->webmasterSection->customFields->where("in_listing", true) as $customField) {
                        if ($customField->in_page) {

                            $cf_saved_val = "";
                            $cf_saved_val_array = array();
                            if (count($Topic->fields) > 0) {
                                foreach ($Topic->fields as $t_field) {
                                    if ($t_field->field_id == $customField->id) {
                                        if ($customField->type == 7) {
                                            // if multi check
                                            $cf_saved_val_array = explode(", ", $t_field->field_value);
                                            $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                            $cf_details_var2 = "details_" . config('smartend.default_language');
                                            if ($customField->$cf_details_var != "") {
                                                $cf_details = $customField->$cf_details_var;
                                            } else {
                                                $cf_details = $customField->$cf_details_var2;
                                            }
                                            $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                            $line_num = 1;
                                            foreach ($cf_details_lines as $cf_details_line) {
                                                if (in_array($line_num, $cf_saved_val_array)) {
                                                    $cf_saved_val .= $cf_details_line . ", ";
                                                }
                                                $line_num++;
                                            }
                                            $cf_saved_val = substr($cf_saved_val, 0, -2);
                                        } else {
                                            $cf_saved_val = $t_field->field_value;
                                        }
                                    }
                                }
                            }

                            if (($cf_saved_val != "" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == "$lang")) {

                                if ($customField->$title_var != "") {
                                    $customField_title = $customField->$title_var;
                                } else {
                                    $customField_title = $customField->$title_var2;
                                }

                                $Additional_fields[] = [
                                    'type' => $customField->type,
                                    'title' => $customField_title,
                                    'value' => $cf_saved_val,
                                ];
                            }
                        }
                    }

                    $video_file = $Topic->video_file;
                    if ($Topic->video_type == 0) {
                        $video_file = ($Topic->video_file != "") ? url("") . "/uploads/topics/" . $Topic->video_file : "";
                    }

                    if ($Topic->$title_var != "") {
                        $Topic_title = $Topic->$title_var;
                    } else {
                        $Topic_title = $Topic->$title_var2;
                    }
                    if ($Topic->$details_var != "") {
                        $Topic_details = $Topic->$details_var;
                    } else {
                        $Topic_details = $Topic->$details_var2;
                    }

                    $response_details[] = [
                        'id' => $Topic->id,
                        'title' => $Topic_title,
                        'details' => $Topic_details,
                        'date' => $Topic->date,
                        'video_type' => $Topic->video_type,
                        'video_file' => $video_file,
                        'photo_file' => ($Topic->photo_file != "") ? url("") . "/uploads/topics/" . $Topic->photo_file : null,
                        'audio_file' => ($Topic->audio_file != "") ? url("") . "/uploads/topics/" . $Topic->audio_file : null,
                        'icon' => $Topic->icon,
                        'visits' => $Topic->visits,
                        'href' => "topic/" . $Topic->id,
                        'fields_count' => count($Additional_fields),
                        'fields' => $Additional_fields,
                        'Joined_categories_count' => count($Topic->categories),
                        'Joined_categories' => $Joined_categories,
                        'section_id' => $section_id,
                        'section_name' => $section_name,
                        'section_type' => $type,

                    ];

                }
                // Response MSG
                $response = [
                    'msg' => 'List of Topics for user',
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'topics_count' => $ic,
                    'topics' => $response_details
                ];

                return response()->json($response, 200);
            } else {
                // Empty MSG
                $response = [
                    'msg' => 'There is no data'
                ];

                return response()->json($response, 200);
            }
        } else {
            // Empty MSG
            $response = [
                'msg' => 'There is no data'
            ];
            return response()->json($response, 404);
        }
    }

 public function ContactPageSubmit(Request $request)
{
    // Validate input
    $request->validate([
        'api_key' => 'required',
        'contact_name' => 'required',
        'contact_email' => 'required|email',
        'contact_subject' => 'required',
        'contact_message' => 'required'
    ]);

    // Check API key
    if ($request->api_key != Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json([
            'code' => -1,
            'msg' => 'Authentication failed'
        ], 401);
    }

    // SITE SETTINGS
    $WebsiteSettings = Setting::find(1);
    $site_title_var = "site_title_" . @Helper::currentLanguage()->code;
    $site_email = $WebsiteSettings->site_webmails;
    $site_title = $WebsiteSettings->$site_title_var ?? "Website";

    // Save message to database
    $Webmail = new Webmail();
    $Webmail->cat_id = 0;
    $Webmail->group_id = null;
    $Webmail->title = $request->contact_subject;
    $Webmail->details = $request->contact_message;
    $Webmail->date = now();
    $Webmail->from_email = $request->contact_email;
    $Webmail->from_name = $request->contact_name;
    $Webmail->from_phone = $request->contact_phone;
    $Webmail->to_email = $site_email;
    $Webmail->to_name = $site_title;
    $Webmail->status = 0;
    $Webmail->flag = 0;
    $Webmail->save();

    // TRY sending notification email, but do NOT break API if it fails
    try {
        if (@Helper::GeneralSiteSettings('notify_messages_status')) {
            $recipient = explode(",", str_replace(" ", "", $site_email));
            $message_details = __('frontend.name') . ": " . $request->contact_name . "<hr>" .
                               __('frontend.phone') . ": " . $request->contact_phone . "<hr>" .
                               __('frontend.email') . ": " . $request->contact_email . "<hr>" .
                               __('frontend.message') . ":<br>" . nl2br($request->contact_message);

            Mail::to($recipient)->send(new NotificationEmail([
                "title" => $request->contact_subject,
                "details" => $message_details,
                "from_email" => $request->contact_email,
                "from_name" => $request->contact_name
            ]));
        }
    } catch (\Exception $e) {
        // Log the error but do not fail the request
        \Log::error('ContactPageSubmit Mail Error: '.$e->getMessage());
    }

    // Always return success JSON
    return response()->json([
        'code' => 1,
        'msg' => 'Message sent successfully'
    ], 200);
}


    public function subscribeSubmit(Request $request)
    {

        $this->validate($request, [
            'api_key' => 'required',
            'subscribe_name' => 'required',
            'subscribe_email' => 'required|email'
        ]);
        // check api_key
        if ($request->api_key == Helper::GeneralWebmasterSettings("api_key")) {
            // General Webmaster Settings
            $WebmasterSettings = WebmasterSetting::find(1);

            $Contacts = Contact::where('email', $request->subscribe_email)->get();
            if (count($Contacts) > 0) {
                // response MSG
                $response = [
                    'code' => '2',
                    'msg' => 'You are already subscribed'
                ];
                return response()->json($response, 200);
            } else {
                $subscribe_names = explode(' ', $request->subscribe_name, 2);

                $Contact = new Contact;
                $Contact->group_id = $WebmasterSettings->newsletter_contacts_group;
                $Contact->first_name = @$subscribe_names[0];
                $Contact->last_name = @$subscribe_names[1];
                $Contact->email = $request->subscribe_email;
                $Contact->status = 1;
                $Contact->save();


                // response MSG
                $response = [
                    'code' => '1',
                    'msg' => 'You have subscribed successfully'
                ];
                return response()->json($response, 201);
            }
        } else {
            // Empty MSG
            $response = [
                'code' => '-1',
                'msg' => 'Authentication failed'
            ];
            return response()->json($response, 500);
        }
    }

    public function commentSubmit(Request $request)
    {

        $this->validate($request, [
            'api_key' => 'required',
            'topic_id' => 'required',
            'comment_name' => 'required',
            'comment_email' => 'required|email',
            'comment_message' => 'required'
        ]);

        // check api_key
        if ($request->api_key == Helper::GeneralWebmasterSettings("api_key")) {
            // General Webmaster Settings
            $WebmasterSettings = WebmasterSetting::find(1);

            $next_nor_no = Comment::where('topic_id', '=', $request->topic_id)->max('row_no');
            if ($next_nor_no < 1) {
                $next_nor_no = 1;
            } else {
                $next_nor_no++;
            }

            $Comment = new Comment;
            $Comment->row_no = $next_nor_no;
            $Comment->name = $request->comment_name;
            $Comment->email = $request->comment_email;
            $Comment->comment = $request->comment_message;
            $Comment->topic_id = $request->topic_id;;
            $Comment->date = date("Y-m-d H:i:s");
            $Comment->status = $WebmasterSettings->new_comments_status;
            $Comment->save();

            // Site Details
            $WebsiteSettings = Setting::find(1);
            $site_title_var = "site_title_" . @Helper::currentLanguage()->code;
            $site_email = $WebsiteSettings->site_webmails;
            $site_url = $WebsiteSettings->site_url;
            $site_title = $WebsiteSettings->$site_title_var;

            // Topic details
            $Topic = Topic::where('status', 1)->find($request->topic_id);
            if (!empty($Topic)) {
                $tpc_title_var = "title_" . @Helper::currentLanguage()->code;
                $tpc_title = $WebsiteSettings->$tpc_title_var;

                // SEND Notification Email
                if (@Helper::GeneralSiteSettings('notify_comments_status')) {
                    $recipient = explode(",", str_replace(" ", "", $site_email));
                    $message_details = __('frontend.name') . ": " . $request->comment_name . "<hr>" . __('frontend.email') . ": " . $request->comment_email . "<hr>" . __('frontend.comment') . ":<br>" . nl2br($request->comment_message);

                    Mail::to($recipient)->send(new NotificationEmail(
                        [
                            "title" => "Comment: " . $tpc_title,
                            "details" => $message_details,
                            "from_email" => $request->comment_email,
                            "from_name" => $request->comment_name
                        ]
                    ));
                }

                // response MSG
                $response = [
                    'code' => '1',
                    'msg' => 'Your Comment Sent successfully'
                ];
                return response()->json($response, 201);
            } else {
                // response MSG
                $response = [
                    'code' => '0',
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 404);
            }
        } else {
            // Empty MSG
            $response = [
                'code' => '-1',
                'msg' => 'Authentication failed'
            ];
            return response()->json($response, 500);
        }
    }

    public function orderSubmit(Request $request)
    {

        $this->validate($request, [
            'api_key' => 'required',
            'topic_id' => 'required',
            'order_name' => 'required',
            'order_phone' => 'required',
            'order_email' => 'required|email'
        ]);

        // check api_key
        if ($request->api_key == Helper::GeneralWebmasterSettings("api_key")) {
            $WebsiteSettings = Setting::find(1);
            $site_title_var = "site_title_" . @Helper::currentLanguage()->code;
            $site_email = $WebsiteSettings->site_webmails;
            $site_url = $WebsiteSettings->site_url;
            $site_title = $WebsiteSettings->$site_title_var;

            $Topic = Topic::where('status', 1)->find($request->topic_id);
            if (!empty($Topic)) {
                $tpc_title_var = "title_" . @Helper::currentLanguage()->code;
                $tpc_title = $WebsiteSettings->$tpc_title_var;

                $Webmail = new Webmail;
                $Webmail->cat_id = 0;
                $Webmail->group_id = null;
                $Webmail->contact_id = null;
                $Webmail->father_id = null;
                $Webmail->title = "ORDER " . ", " . $Topic->$tpc_title_var;
                $Webmail->details = $request->order_message;
                $Webmail->date = date("Y-m-d H:i:s");
                $Webmail->from_email = $request->order_email;
                $Webmail->from_name = $request->order_name;
                $Webmail->from_phone = $request->order_phone;
                $Webmail->to_email = $WebsiteSettings->site_webmails;
                $Webmail->to_name = $WebsiteSettings->$site_title_var;
                $Webmail->status = 0;
                $Webmail->flag = 0;
                $Webmail->save();


                // SEND Notification Email
                if (@Helper::GeneralSiteSettings('notify_orders_status')) {
                    $recipient = explode(",", str_replace(" ", "", $site_email));
                    $message_details = __('frontend.name') . ": " . $request->order_name . "<hr>" . __('frontend.phone') . ": " . $request->order_phone . "<hr>" . __('frontend.email') . ": " . $request->order_email . "<hr>" . __('frontend.notes') . ":<br>" . nl2br($request->order_message);

                    Mail::to($recipient)->send(new NotificationEmail(
                        [
                            "title" => "Order: " . $tpc_title,
                            "details" => $message_details,
                            "from_email" => $request->order_email,
                            "from_name" => $request->order_name
                        ]
                    ));
                }

                // response MSG
                $response = [
                    'code' => '1',
                    'msg' => 'Your Order Sent successfully'
                ];
                return response()->json($response, 201);
            } else {
                // response MSG
                $response = [
                    'code' => '0',
                    'msg' => 'There is no data'
                ];
                return response()->json($response, 404);
            }
        } else {
            // Empty MSG
            $response = [
                'code' => '-1',
                'msg' => 'Authentication failed'
            ];
            return response()->json($response, 500);
        }

    }
    
     public function gallery()
{
    $lang = Helper::currentLanguage()->code;

    // Get webmaster section
    $webmasterSection = WebmasterSection::where('title_en', 'gallery')
        ->where('status', 1)
        ->first();

    if (!$webmasterSection) {
        return response()->json([
            'success' => false,
            'message' => 'Gallery section not found'
        ], 404);
    }

    $webmasterId = $webmasterSection->id;

    // Get category list
    $categories = Section::where('webmaster_id', $webmasterId)
        ->where('status', 1)
        ->orderBy('row_no', 'asc')
        ->get();

    // Get topic IDs
    $topicIds = Topic::where('webmaster_id', $webmasterId)
        ->where('status', 1)
        ->pluck('id')
        ->toArray();

    if (empty($topicIds)) {
        return response()->json([
            'success' => true,
            'items' => [],
            'message' => 'No gallery items found'
        ]);
    }

    // Get photos
    $photos = Photo::whereIn('topic_id', $topicIds)
        ->orderBy('row_no', 'asc')
        ->get();

    if ($photos->isEmpty()) {
        return response()->json([
            'success' => true,
            'items' => [],
            'message' => 'No photos found'
        ]);
    }

    // Topic-category mapping
    $topicCategories = TopicCategory::whereIn('topic_id', $topicIds)
        ->get()
        ->groupBy('topic_id')
        ->map(function ($items) {
            return $items->pluck('section_id')->toArray();
        });

    // Category names
    $categoryNames = [];
    foreach ($categories as $cat) {
        $categoryNames[$cat->id] = $cat->{'title_' . $lang} ?? $cat->title_en;
    }

    // Build gallery items
    $galleryItems = [];

    foreach ($photos as $photo) {
        $categoryIds = $topicCategories->get($photo->topic_id, []);
        $category = "all";

        if (!empty($categoryIds)) {
            $firstCat = $categoryIds[0];
            $category = $categoryNames[$firstCat] ?? "all";
        }

        if ($photo->file) {
            $galleryItems[] = [
                'image' => url('uploads/topics/' . $photo->file),
                'category' => $category,
                'alt' => $photo->title ?? $photo->file
            ];
        }
    }

    // Apply category filter
    $filter = request()->get('category', 'all');

    if ($filter !== "all") {
        $galleryItems = array_values(array_filter($galleryItems, function ($item) use ($filter) {
            return $item['category'] === $filter;
        }));
    }

    // Final JSON return
    return response()->json([
        'success' => true,
        'items' => $galleryItems,
        'count' => count($galleryItems),
        'categoryFilter' => $filter
    ]);
}


public function blog()
{
    try {

        $lang = Helper::currentLanguage()->code;

        // 1) Get blog section
        $webmasterSection = DB::table('webmaster_sections')
            ->where('title_en', 'blog')
            ->where('status', 1)
            ->first();

        if (!$webmasterSection) {
            return response()->json([
                'success' => false,
                'message' => 'Blog section not found',
            ], 404);
        }

        $webmasterId = $webmasterSection->id;

        // 2) Get blogs
        $blogs = DB::table('topics')
            ->where('webmaster_id', $webmasterId)
            ->where('status', 1)
            ->orderBy('row_no', 'asc')
            ->get();

        if ($blogs->isEmpty()) {
            return response()->json([
                'success' => true,
                'items' => [],
                'message' => 'No blogs found',
            ]);
        }

        $topicIds = $blogs->pluck('id')->toArray();

        // 3) Photos
        $photos = DB::table('photos')
            ->whereIn('topic_id', $topicIds)
            ->orderBy('row_no', 'asc')
            ->get()
            ->groupBy('topic_id');

        // 4) Field definitions
        $fieldDefinitions = DB::table('webmaster_section_fields')
            ->where('webmaster_id', $webmasterId)
            ->get();

        $fieldsAssoc = [];
        foreach ($fieldDefinitions as $f) {
            $fieldsAssoc[$f->id] = $f;
        }

        // 5) Field values
        $fieldValues = DB::table('topic_fields')
            ->whereIn('topic_id', $topicIds)
            ->get()
            ->groupBy('topic_id');

        $blogList = [];

        foreach ($blogs as $blog) {

            // Photos processing
            $blogPhotos = collect($photos->get($blog->id, []))->map(function ($p) {
                return [
                    'image' => url('uploads/topics/' . $p->file),
                    'alt'   => $p->title ?? $p->file,
                ];
            })->toArray();

            // Custom fields processing
            $customFields = [];

            if (isset($fieldValues[$blog->id])) {
                foreach ($fieldValues[$blog->id] as $fv) {

                    $fieldId = $fv->field_id;

                    if (isset($fieldsAssoc[$fieldId])) {
                        $field = $fieldsAssoc[$fieldId];

                        $customFields[] = [
                            'field_id' => $fieldId,
                            'label'    => $field->{"title_$lang"} ?? $field->title_en,
                            'value'    => $fv->field_value,   // << FIXED HERE
                        ];
                    }
                }
            }

            $blogList[] = [
                'id'         => $blog->id,
                'title'      => $blog->{"title_$lang"} ?? $blog->title_en,
                'details'    => $blog->{"details_$lang"} ?? $blog->details_en,
               'image'      => $blog->photo_file 
                    ? url('uploads/topics/' . $blog->photo_file)
                    : null,
                'date' => $blog->date,
                'created_at' => $blog->created_at,
                'updated_at' => $blog->updated_at,
                'photos'     => $blogPhotos,
                'custom_fields' => $customFields,
            ];
        }

        return response()->json([
            'success' => true,
            'count'   => count($blogList),
            'items'   => $blogList,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server Error',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


// public function registerSubmit(Request $request)
// {
//     $this->validate($request, [
//         'api_key' => 'required',
//         'full_name' => 'required',
//         'email' => 'required',
//         'company_name' => 'required',
//         'phone' => 'required',
//         'user_type' => 'required',
//         'nationality' => 'required',
//         'password' => 'required|min:6',
//         'password_confirmation' => 'required|same:password',
//         'frontend_url' => 'nullable|string|max:500'
//     ]);

//     if ($request->api_key == Helper::GeneralWebmasterSettings("api_key")) {

//         // ? Save user
//         $user = new UserRegister();
//         $user->full_name = $request->full_name;
//         $user->email = $request->email;
//         $user->company_name = $request->company_name;
//         $user->phone = $request->phone;
//         $user->user_type = $request->user_type;
//         $user->nationality = $request->nationality;
//         $user->password = \Hash::make($request->password);
//         $user->special_requirements = $request->special_requirements;
//         $user->sponsor_package = $request->sponsor_package;
//         $user->products_services = $request->products_services;
//         $user->save();


//         return response()->json([
//             'code' => '1',
//             'msg' => 'Registration successful'
//         ], 201);

//     } else {
//         return response()->json([
//             'code' => '-1',
//             'msg' => 'Authentication failed'
//         ], 500);
//     }
// }


public function downloadTicket(UserRegister $user)
{
    // Generate Barcode for PDF
    $generator = new \Milon\Barcode\DNS1D();
    $barcodePNG = $generator->getBarcodePNG($user->email, 'C128');
    $barcodeBase64 = 'data:image/png;base64,' . $barcodePNG;

    $pdf = Pdf::loadView('emails.ticket-pdf', [
        'user' => $user,
        'barcodeBase64' => $barcodeBase64,
        'ticket_header' => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792010449837.png',
        'ticket_footer' => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792011237810.png',
    ]);

    return $pdf->download("ticket-{$user->id}.pdf");
}


public function registerSubmit(Request $request)
{
    // ? Validation
    $this->validate($request, [
        'api_key' => 'required',
        'full_name' => 'required',
        'email' => 'required|email',
        'company_name' => 'required',
        'phone' => 'required',
        'user_type' => 'required',
        'nationality' => 'required',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password',
        'frontend_url' => 'nullable|string|max:500'
    ]);

    // ?? API KEY CHECK
    if ($request->api_key != Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json([
            'code' => '-1',
            'msg' => 'Authentication failed'
        ], 500);
    }

    // ? Save user
    $user = new UserRegister();
    $user->full_name = $request->full_name;
    $user->email = $request->email;
    $user->company_name = $request->company_name;
    $user->phone = $request->phone;
    $user->user_type = $request->user_type;
    $user->nationality = $request->nationality;
    $user->password = \Hash::make($request->password);
    $user->special_requirements = $request->special_requirements;
    $user->sponsor_package = $request->sponsor_package;
    $user->products_services = $request->products_services;
    $user->save();

    if (empty($user->referral_code)) {
        $user->referral_code = $this->createReferralCodeForUser($user);
        $user->referral_link = $this->createReferralLink($user->referral_code, $request->frontend_url);
        $user->save();
    }

    // QR code generation
try {
    $generator = new DNS2D();
    // Generate QR code using email as unique content
    $qrPNG = $generator->getBarcodePNG($user->email, 'QRCODE'); 
    $barcodeBase64 = 'data:image/png;base64,' . $qrPNG;

    // Download link via route
    $downloadTicketUrl = route('ticket.download', $user->id);

} catch (\Exception $e) {
    \Log::error('QR code generation failed: ' . $e->getMessage());
    $barcodeBase64 = null;
    $downloadTicketUrl = null;
}


    // ? Send registration email via Brevo API
    try {
        $mailService = new MailService();

        $mailData = [
            'title'             => 'Welcome to PROFX Expo Africa 2026',
            'details'           => "Hi {$user->full_name},<br><br>Thank you for registering for PROFX Expo Africa 2026.<br>You can now login with your email.<br><br>Regards,<br>PROFX Team",
            'logo'              => 'https://profxexpo.com/africa/adminpanel/uploads/settings/17791964093936.png',
            'ticket_header'     => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792010449837.png',
            'ticket_footer'     => 'https://profxexpo.com/africa/adminpanel/uploads/topics/17792011237810.png',
            'barcodeBase64'     => $barcodeBase64,
            'downloadTicketUrl' => $downloadTicketUrl,
        ];

        $result = $mailService->sendEmail(
            $user->email,
            'Ticket - PROFX Expo Africa',
            'emails.registration', // Blade template
            $mailData
        );

        \Log::info('Brevo Mail Response', $result);

        if (isset($result['error'])) {
            \Log::error('Failed to send registration email: ' . $result['message']);
        }

    } catch (\Exception $e) {
        \Log::error('Exception sending registration email: ' . $e->getMessage());
    }

    // Send register completed email via Brevo API
    try {
        $completedMailService = new MailService();

        $completedMailData = [
            'title' => 'Register Completed - PROFX Expo Africa',
            'user' => $user,
            'logo' => 'https://profxexpo.com/africa/adminpanel/uploads/settings/17791964093936.png',
            'heroImage' => 'https://profxexpo.com/africa/adminpanel/assets/dashboard/images/email/1.png',
            'loginUrl' => 'https://profxsummit.com/login',
            'loginEmail' => $user->email,
            'loginPassword' => $request->password,
        ];

        $completedResult = $completedMailService->sendEmail(
            $user->email,
            'Register Completed - PROFX Expo Africa',
            'emails.register-completed',
            $completedMailData
        );

        \Log::info('Brevo Register Completed Mail Response', $completedResult);

        if (isset($completedResult['error'])) {
            \Log::error('Failed to send register completed email: ' . $completedResult['message']);
        }
    } catch (\Exception $e) {
        \Log::error('Exception sending register completed email: ' . $e->getMessage());
    }

    // ? Response
    return response()->json([
        'code' => '1',
        'msg'  => 'Registration successful',
        'data' => [
            'user_id' => $user->id,
            'referral_code' => $user->referral_code,
            'referral_link' => $user->referral_link,
        ]
    ], 201);
}


 public function loginSubmit(Request $request)
    {
        // ? Validation
        $this->validate($request, [
            'api_key' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // ?? API KEY CHECK (BODY)
        if ($request->api_key == Helper::GeneralWebmasterSettings("api_key")) {

            // ? Check user
            $user = UserRegister::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'code' => '0',
                    'msg' => 'User not found'
                ], 404);
            }

            // ? Password check
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'code' => '0',
                    'msg' => 'Invalid password'
                ], 401);
            }

            // ? Login success
            return response()->json([
                'code' => '1',
                'msg' => 'Login successful',
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                     'company_name'=>$user->company_name,
                      'phone' => $user->phone,
                    'nationality'=>$user->nationality,
                    'special_requirements'=>$user->special_requirements,
                    'sponsor_package'=>$user->sponsor_package,
                    'products_services'=>$user->products_services,
                    'profile_photo'=>$user->profile_photo ?? null,
                    'profile_photo_url'=>!empty($user->profile_photo) ? url('uploads/settings/' . $user->profile_photo) : null,
                    'referral_code'=>$user->referral_code ?? null,
                    'referral_link'=>$user->referral_link ?? null
                ]
            ], 200);

        } else {
            return response()->json([
                'code' => '-1',
                'msg' => 'Authentication failed'
            ], 500);
        }
    }


public function updateClientProfile(Request $request)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'user_id' => 'required',
        'full_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'company_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:50',
        'nationality' => 'nullable|string|max:255',
        'user_type' => 'nullable|string|max:255',
        'sponsor_package' => 'nullable|string|max:255',
        'products_services' => 'nullable|string|max:1000',
        'special_requirements' => 'nullable|string|max:5000',
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $user = UserRegister::find($validated['user_id']);

    if (!$user) {
        return response()->json(['code' => -1, 'msg' => 'User not found'], 404);
    }

    $oldEmail = $user->email;

    $emailExists = UserRegister::where('email', $validated['email'])
        ->where('id', '!=', $user->id)
        ->exists();

    if ($emailExists) {
        return response()->json(['code' => -1, 'msg' => 'Email already exists'], 422);
    }

    $user->full_name = $request->full_name;
    $user->email = $request->email;
    $user->company_name = $request->company_name;
    $user->phone = $request->phone;
    $user->nationality = $request->nationality;
    $user->user_type = $request->user_type;
    $user->sponsor_package = $request->sponsor_package;
    $user->products_services = $request->products_services;
    $user->special_requirements = $request->special_requirements;

    if ($request->hasFile('profile_photo')) {
        $file = $request->file('profile_photo');
        $fileFinalName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/settings/';
        $file->move($path, $fileFinalName);

        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }

        $user->profile_photo = $fileFinalName;
    }

    $user->save();

    if ($oldEmail !== $user->email) {
        Floorplan::where('email', $oldEmail)->update(['email' => $user->email]);
        ClientSpeaker::where('email', $oldEmail)->update(['email' => $user->email]);
    }

    return response()->json([
        'code' => 1,
        'msg' => 'Profile updated successfully',
        'data' => [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'company_name' => $user->company_name,
            'phone' => $user->phone,
            'nationality' => $user->nationality,
            'special_requirements' => $user->special_requirements,
            'sponsor_package' => $user->sponsor_package,
            'products_services' => $user->products_services,
            'profile_photo' => $user->profile_photo,
            'profile_photo_url' => !empty($user->profile_photo) ? url('uploads/settings/' . $user->profile_photo) : null,
            'referral_code' => $user->referral_code ?? null,
            'referral_link' => $user->referral_link ?? null,
        ],
    ], 200);
}

public function updateClientPassword(Request $request)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'user_id' => 'required',
        'current_password' => 'required|string',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $user = UserRegister::find($validated['user_id']);

    if (!$user) {
        return response()->json(['code' => -1, 'msg' => 'User not found'], 404);
    }

    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json(['code' => -1, 'msg' => 'Current password is incorrect'], 422);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json([
        'code' => 1,
        'msg' => 'Password updated successfully',
    ], 200);
}
public function TicketPage(Request $request)
{
    // Validate request
    $this->validate($request, [
        'api_key' => 'required',
        'ticket_type' => 'required|string|max:100',
        'user_id' => 'required',
        'tickets' => 'required|array|min:1|max:5',
        'tickets.*.name' => 'required|string|max:100',
        'tickets.*.email' => 'required|email',
        'tickets.*.phone' => 'required|string|max:20',
        'tickets.*.id' => 'required|string|max:50',
        'tickets.*.id_name' => 'required|string|max:20',
        'tickets.*.id_number' => 'required|string|max:50',
        'payment_type' => 'required|string|max:50',
        'amount' => 'required|numeric',
       
    ]);

    // Check API Key
    if ($request->api_key != Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json([
            'code' => '-1',
            'msg' => 'Authentication failed'
        ], 401);
    }

    // Save Ticket
    $ticket = Ticket::create([
        'ticket_type' => $request->ticket_type,
        'user_id'=> $request->user_id,
        'refer_code'  =>$request->refer_code, // generate a random 8-char code
    'refer_count' => $request->refer_count
    ]);

    // Save Users
    foreach ($request->tickets as $user) {
        TicketUser::create([
            'ticket_id' => $ticket->id,
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'user_id' => $user['id'],
            'id_name' => $user['id_name'],
            'id_number' => $user['id_number']
        ]);
    }

  if ($request->hasFile('payment_image')) {
    $filename = $request->file('payment_image')->store('payments', 'public');
    // convert to full URL like your bg_image example
    $payment_image_path = ($filename != "") ? url("") . "/storage/" . $filename : null;
} else {
    return response()->json([
        'code' => '-2',
        'msg' => 'Payment image is required'
    ], 422);
}


    // Save Payment
    $payment = Payment::create([
        'ticket_id' => $ticket->id,
        'payment_type' => $request->payment_type,
        'amount' => $request->amount,
        'payment_image' => $payment_image_path
    ]);

    // Response
    return response()->json([
        'code' => '1',
        'msg' => 'Ticket submitted successfully',
        'ticket' => $ticket,
        'users' => $ticket->users,
        'payment' => $payment
    ], 200);
}

public function TicketList(Request $request)
{
    // ? API key check
    $apiKey = $request->query('api_key');
    $correctApiKey = Helper::GeneralWebmasterSettings("api_key");

    if (!$apiKey || $apiKey != $correctApiKey) {
        return response()->json([
            'msg' => 'Authentication failed',
            'details' => [
                'tickets' => [],
                'general_webmaster_sections' => [],
               
            ]
        ], 401);
    }

    // Fetch all tickets with users & payment
    $tickets = Ticket::with(['users', 'payment'])
        ->orderBy('id', 'asc') // or 'id' if row_no does not exist
        ->get();

    // Fetch General Webmaster Sections
    $generalWebmasterSections = WebmasterSection::where('status', 1)
        ->orderBy('row_no', 'asc')
        ->get();


 

    // Response
    return response()->json([
        'msg' => 'All tickets fetched successfully',
        'details' => [
            'tickets' => $tickets,
            'general_webmaster_sections' => $generalWebmasterSections,
           
        ]
    ], 200);
}

public function BookingPageSubmit(Request $request)
{
    // ? Validate request data
    $validated = $request->validate([
        'name'    => 'required|string|max:255',
        'email'   => 'required|email|max:255',
        'phone'   => 'required|string|max:20',
        'address' => 'required|string',
        'role'    => 'required|string|max:100',
        'referral_code' => 'nullable|string|max:50',
        'api_key' => 'required|string',
    ]);

    // ? API Key check
    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json([
            'code' => -1,
            'msg'  => 'Authentication failed'
        ], 401);
    }

    // ? Save booking
    $booking = Booking::create([
        'name'    => $validated['name'],
        'email'   => $validated['email'],
        'phone'   => $validated['phone'],
        'address' => $validated['address'],
        'role'    => $validated['role'],
    ]);

    // ? Success response
    return response()->json([
        'code' => 1,
        'msg'  => 'Registration successful',
        'data' => [
            'booking_id' => $booking->id
        ]
    ], 201);
}


public function exhibitorsSubmit(Request $request)
{
    // ? Validation (same style as subscribe)
    $this->validate($request, [
        'api_key' => 'required',
        'full_name' => 'required',
        'email' => 'required',
        'company_name' => 'required',
        'phone' => 'required',
        'user_type' => 'required',
        'nationality' => 'required',
        'password' => 'required|min:6',
        'password_confirmation' => 'required|same:password',
        'frontend_url' => 'nullable|string|max:500'
    ]);

    // ?? API KEY CHECK (BODY la irundhu)
    if ($request->api_key == Helper::GeneralWebmasterSettings("api_key")) {

        // ? Save user
        $user = new Exhibitors();
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->company_name = $request->company_name;
        $user->phone = $request->phone;
        $user->user_type = $request->user_type;
        $user->nationality = $request->nationality;
        $user->password = \Hash::make($request->password);
        $user->special_requirements = $request->special_requirements;
        $user->sponsor_package = $request->sponsor_package;
    $user->products_services = $request->products_services;
    $user->save();


        // ? Response
    return response()->json([
        'code' => '1',
        'msg'  => 'Registration successful',
        'data' => [
            'user_id' => $user->id,
        ]
    ], 201);

    } else {
        // ? API KEY FAILED
        return response()->json([
            'code' => '-1',
            'msg' => 'Authentication failed'
        ], 500);
    }
}

public function FloorplanSubmit(Request $request)
{
    // ? Validate request
    $validated = $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|max:255',
        'phone'        => 'required|string|max:20',
        'company'      => 'nullable|string|max:255',
        'referal_code' => 'nullable|string|max:100',
        'boothno'      => 'nullable|string|max:50',
        'boothtitle'   => 'nullable|string|max:255',
        'boothsize'    => 'nullable|string|max:100',
        'boothammount' => 'nullable|numeric',
        'paymenttype'  => 'nullable|string|max:100',
        'networktype'  => 'nullable|string|max:100',
        'file'         => 'required|file|mimes:jpg,jpeg,png,pdf|max:10048',
        'api_key'      => 'required|string',
    ]);

    // ? API key check
    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json([
            'code' => -1,
            'msg'  => 'Authentication failed'
        ], 401);
    }

    // ? remove api_key before DB save
    unset($validated['api_key']);

    $filePath = null;

    // ? File upload
    if ($request->hasFile('file')) {

        $file = $request->file('file');
        $fileFinalName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();

        $path = $this->uploadPath; // example: uploads/topics/
        $file->move($path, $fileFinalName);

        // ? Resize only for images
        if (in_array($file->getClientOriginalExtension(), ['jpg','jpeg','png'])) {
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }

        // ? Save filename into validated data
        $validated['file'] = $fileFinalName;
        $filePath = url($path . $fileFinalName);
    }

    // ? Save to DB
    $floorplan = Floorplan::create($validated);

    return response()->json([
        'code' => 1,
        'msg'  => 'Floorplan saved successfully',
        'data' => [
            'floorplan_id' => $floorplan->id,
            'file' => $filePath
        ]
    ], 201);
}


public function floorplanList(Request $request)
{
    $GeneralWebmasterSections = WebmasterSection::where('status', 1)
        ->orderBy('row_no', 'asc')
        ->get();

    $query = DB::table('floorplans');

    // ?? Search filters
    if ($request->filled('email')) {
        $query->where('email', 'like', '%' . $request->email . '%');
    }

    if ($request->filled('boothtitle')) {
        $query->where('boothtitle', 'like', '%' . $request->boothtitle . '%');
    }

    // ?? Pagination
    $floorplans = $query
        ->orderBy('created_at', 'DESC')
        ->paginate(10)
        ->appends($request->query());
    // Public floorplan must only reveal client company profile after admin approval.
    $floorplans->getCollection()->transform(function ($item) {
        $isApproved = ($item->status ?? 'pending') === 'approved';
        $logoFile = $item->company_logo ?? null;
        $approvedLogo = $isApproved && $logoFile ? url('uploads/settings/' . $logoFile) : null;

        $item->is_company_profile_approved = $isApproved;
        $item->public_company_name = $isApproved ? ($item->company_profile_name ?: $item->company) : null;
        $item->public_company_details = $isApproved ? ($item->company_details ?? null) : null;
        $item->public_company_url = $isApproved ? ($item->company_url ?? null) : null;
        $item->company_logo = $approvedLogo;
        $item->company_url = $isApproved ? ($item->company_url ?? null) : null;

        return $item;
    });

    return response()->json([
        'msg' => 'All Floorplans fetched successfully',
        'details' => [
            'tickets' => $floorplans,
            'general_webmaster_sections' => $GeneralWebmasterSections,
        ]
    ], 200);
}


public function clientBoothList(Request $request)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'email' => 'required|email|max:255',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $booths = Floorplan::where('email', $validated['email'])
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function ($item) {
            $item->payment_file_url = $item->file ? url('uploads/topics/' . $item->file) : null;
            $item->company_logo_url = $item->company_logo ? url('uploads/settings/' . $item->company_logo) : null;
            $item->booth_design_image_url = $item->booth_design_image ? url('uploads/settings/' . $item->booth_design_image) : null;
            return $item;
        });

    return response()->json([
        'code' => 1,
        'msg' => 'Client booths fetched successfully',
        'details' => $booths,
    ], 200);
}

public function updateBoothCompanyProfile(Request $request, $id)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'email' => 'required|email|max:255',
        'company_profile_name' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'company_url' => 'nullable|url|max:255',
        'company_details' => 'nullable|string|max:5000',
        'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,svg|max:10048',
        'booth_design' => 'nullable|string|max:255',
        'booth_design_image' => 'nullable|file|mimes:jpg,jpeg,png,gif,svg,webp,pdf,mp4,mov,avi,webm,mkv|max:10240',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $floorplan = Floorplan::where('id', $id)->where('email', $validated['email'])->first();

    if (!$floorplan) {
        return response()->json(['code' => -1, 'msg' => 'Booth booking not found'], 404);
    }

    $floorplan->company_profile_name = $request->company_profile_name;
    $floorplan->company = $request->company;
    $floorplan->company_url = $request->company_url;
    $floorplan->company_details = $request->company_details;
    $floorplan->booth_design = $request->booth_design;
    $floorplan->status = 'pending';
    $floorplan->approval_message = null;

    if ($request->hasFile('company_logo')) {
        $file = $request->file('company_logo');
        $fileFinalName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/settings/';
        $file->move($path, $fileFinalName);

        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }

        $floorplan->company_logo = $fileFinalName;
    }


    if ($request->hasFile('booth_design_image')) {
        $file = $request->file('booth_design_image');
        $fileFinalName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/settings/';
        $file->move($path, $fileFinalName);

        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }

        $floorplan->booth_design_image = $fileFinalName;
    }
    $floorplan->save();
    $floorplan->company_logo_url = $floorplan->company_logo ? url('uploads/settings/' . $floorplan->company_logo) : null;
    $floorplan->booth_design_image_url = $floorplan->booth_design_image ? url('uploads/settings/' . $floorplan->booth_design_image) : null;

    return response()->json([
        'code' => 1,
        'msg' => 'Booth company profile submitted for admin review',
        'data' => $floorplan,
    ], 200);
}

public function clientSpeakerList(Request $request)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'email' => 'required|email|max:255',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $speakers = ClientSpeaker::where('email', $validated['email'])
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function ($speaker) {
            $speaker->photo_url = $speaker->photo ? url('uploads/topics/' . $speaker->photo) : null;
            return $speaker;
        });

    return response()->json([
        'code' => 1,
        'msg' => 'Client speakers fetched successfully',
        'details' => $speakers,
    ], 200);
}

public function clientSpeakerSubmit(Request $request)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'user_id' => 'nullable|integer',
        'email' => 'required|email|max:255',
        'name' => 'required|string|max:255',
        'designation' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'bio' => 'nullable|string|max:3000',
        'website' => 'nullable|url|max:255',
        'linkedin' => 'nullable|url|max:255',
        'instagram' => 'nullable|url|max:255',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10048',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $photoName = null;

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $photoName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $path = $this->uploadPath;
        $file->move($path, $photoName);
        Helper::imageResize($path . $photoName);
        Helper::imageOptimize($path . $photoName);
    }

    $speaker = ClientSpeaker::create([
        'user_id' => $request->user_id,
        'email' => $request->email,
        'name' => $request->name,
        'designation' => $request->designation,
        'company' => $request->company,
        'bio' => $request->bio,
        'website' => $request->website,
        'linkedin' => $request->linkedin,
        'instagram' => $request->instagram,
        'photo' => $photoName,
        'status' => 'pending',
    ]);

    return response()->json([
        'code' => 1,
        'msg' => 'Speaker profile submitted for admin approval',
        'data' => ['speaker_id' => $speaker->id, 'status' => $speaker->status],
    ], 201);
}

public function updateClientSpeaker(Request $request, $id)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'user_id' => 'nullable|integer',
        'email' => 'required|email|max:255',
        'name' => 'required|string|max:255',
        'designation' => 'nullable|string|max:255',
        'company' => 'nullable|string|max:255',
        'bio' => 'nullable|string|max:3000',
        'website' => 'nullable|url|max:255',
        'linkedin' => 'nullable|url|max:255',
        'instagram' => 'nullable|url|max:255',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10048',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $speaker = ClientSpeaker::where('id', $id)->where('email', $validated['email'])->first();

    if (!$speaker) {
        return response()->json(['code' => -1, 'msg' => 'Speaker profile not found'], 404);
    }

    $photoName = $speaker->photo;

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $photoName = time() . rand(1111, 9999) . '.' . $file->getClientOriginalExtension();
        $path = $this->uploadPath;
        $file->move($path, $photoName);
        Helper::imageResize($path . $photoName);
        Helper::imageOptimize($path . $photoName);
    }

    $speaker->update([
        'user_id' => $request->user_id,
        'name' => $request->name,
        'designation' => $request->designation,
        'company' => $request->company,
        'bio' => $request->bio,
        'website' => $request->website,
        'linkedin' => $request->linkedin,
        'instagram' => $request->instagram,
        'photo' => $photoName,
        'status' => 'pending',
        'admin_message' => null,
        'approved_by' => null,
        'approved_at' => null,
    ]);

    $speaker->photo_url = $speaker->photo ? url('uploads/topics/' . $speaker->photo) : null;

    return response()->json([
        'code' => 1,
        'msg' => 'Speaker profile updated. Admin approval pending.',
        'data' => $speaker,
    ], 200);
}

public function deleteClientSpeaker(Request $request, $id)
{
    $validated = $request->validate([
        'api_key' => 'required|string',
        'email' => 'required|email|max:255',
    ]);

    if ($validated['api_key'] !== Helper::GeneralWebmasterSettings("api_key")) {
        return response()->json(['code' => -1, 'msg' => 'Authentication failed'], 401);
    }

    $speaker = ClientSpeaker::where('id', $id)->where('email', $validated['email'])->first();

    if (!$speaker) {
        return response()->json(['code' => -1, 'msg' => 'Speaker profile not found'], 404);
    }

    $speaker->delete();

    return response()->json(['code' => 1, 'msg' => 'Speaker profile deleted successfully'], 200);
}
public function Sponsors()
{
    $lang = Helper::currentLanguage()->code;

    // 1?? Get the "Sponsors" section
    $sportsSection = WebmasterSection::where('title_en', 'Sponsors')
        ->where('status', 1)
        ->firstOrFail();

    // 2?? Get Categories under Sponsors
    $categories = Section::where('webmaster_id', $sportsSection->id)
        ->where('status', 1)
        ->orderBy('row_no')
        ->get();

    // 3?? Get Topics under Sponsors
    $topics = Topic::where('webmaster_id', $sportsSection->id)
        ->where('status', 1)
        ->get();

    // 4?? Get Topic ? Category mapping
    $topicCategories = TopicCategory::whereIn('topic_id', $topics->pluck('id'))
        ->get()
        ->groupBy('topic_id');

    // 5?? Build categories array with their topics
    $categoriesWithTopics = [];
    $filter = []; // Optional: list of category names for filtering

    foreach ($categories as $cat) {
        $catTopics = [];

        foreach ($topics as $topic) {
            if (isset($topicCategories[$topic->id])) {
                foreach ($topicCategories[$topic->id] as $tc) {
                    if ($tc->section_id == $cat->id) {
                        $catTopics[] = [
                            'id' => $topic->id,
                            'title' => $topic->title_en, // use $topic->title_ar if needed
                            'description' => $topic->details_en ?? '', // optional
                            'image' => $topic->photo_file 
                            ? url('uploads/topics/' . $topic->photo_file) 
                            : null, // full URL if exists, otherwise null
                        ];
                    }
                }
            }
        }

        $categoriesWithTopics[] = [
            'id' => $cat->id,
            'title' => strtoupper(trim($cat->title_en)),
            'topics' => $catTopics,
        ];

        $filter[] = strtoupper(trim($cat->title_en));
    }

    // 6?? Return JSON
    return response()->json([
        'success' => true,
        'categories' => $categoriesWithTopics,
        'count' => count($categoriesWithTopics),
        'categoryFilter' => $filter,
    ]);
}

public function speakers()
{
    $lang = Helper::currentLanguage()->code;

    // 1?? Get the "Speakers" section
    $speakersSection = WebmasterSection::where('title_en', 'speakers')
        ->where('status', 1)
        ->firstOrFail();

    // 2?? Get all fields defined for this section (field definitions)
    $sectionFields = DB::table('webmaster_section_fields')
        ->where('webmaster_id', $speakersSection->id)
        ->where('status', 1)
        ->orderBy('row_no')
        ->get();

    // 3?? Get all topics under this section
    $topics = Topic::where('webmaster_id', $speakersSection->id)
        ->where('status', 1)
        ->get();

    $topicIds = $topics->pluck('id');

    // 4?? Get all topic field values for these topics
    $topicFields = DB::table('topic_fields')
        ->whereIn('topic_id', $topicIds)
        ->get()
        ->groupBy('topic_id');

    // 5?? Build topics list with all extra fields
    $topicsList = $topics->map(function($topic) use ($topicFields, $sectionFields) {
        $fields = [];

        // Check if this topic has fields
        if (isset($topicFields[$topic->id])) {
            foreach ($topicFields[$topic->id] as $tf) {
                // Get field definition (title/type)
                $fieldDef = $sectionFields->firstWhere('id', $tf->field_id);

                $fields[] = [
                    'field_id' => $tf->field_id,
                    'field_title' => $fieldDef->title_en ?? '', // show label
                    'value' => $tf->field_value, // the actual stored value
                    'type' => $fieldDef->type ?? 'text', // field type
                ];
            }
        }

        return [
            'id' => $topic->id,
            'title' => $topic->title_en,
            'description' => $topic->details_en ?? '',
            'image' => $topic->photo_file 
                ? url('uploads/topics/' . $topic->photo_file) 
                : null,
            'fields' => $fields, // all extra fields included here
        ];
    });
    $approvedClientSpeakers = ClientSpeaker::where('status', 'approved')
        ->orderBy('approved_at', 'DESC')
        ->orderBy('updated_at', 'DESC')
        ->get();

    foreach ($approvedClientSpeakers as $clientSpeaker) {
        $topicsList->push([
            'id' => 'client-' . $clientSpeaker->id,
            'title' => $clientSpeaker->name,
            'description' => $clientSpeaker->designation ?? '',
            'image' => $clientSpeaker->photo ? url('uploads/topics/' . $clientSpeaker->photo) : null,
            'fields' => [
                [
                    'field_id' => null,
                    'field_title' => 'company',
                    'value' => $clientSpeaker->company ?? '',
                    'type' => 'text',
                ],
                [
                    'field_id' => null,
                    'field_title' => 'website',
                    'value' => $clientSpeaker->website ?? '',
                    'type' => 'url',
                ],
                [
                    'field_id' => null,
                    'field_title' => 'linkedin',
                    'value' => $clientSpeaker->linkedin ?? '',
                    'type' => 'url',
                ],
                [
                    'field_id' => null,
                    'field_title' => 'instagram',
                    'value' => $clientSpeaker->instagram ?? '',
                    'type' => 'url',
                ],
            ],
        ]);
    }
    // Return JSON
    return response()->json([
        'success' => true,
        'section_fields' => $sectionFields, // optional: all field definitions
        'topics' => $topicsList,            // topics with extra labels
        'count' => $topicsList->count(),
    ]);
}



     public function influencer()
{
    $lang = Helper::currentLanguage()->code;

    // Get webmaster section
    $webmasterSection = WebmasterSection::where('title_en', 'influencer')
        ->where('status', 1)
        ->first();

    if (!$webmasterSection) {
        return response()->json([
            'success' => false,
            'message' => 'Gallery section not found'
        ], 404);
    }

    $webmasterId = $webmasterSection->id;

    // Get category list
    $categories = Section::where('webmaster_id', $webmasterId)
        ->where('status', 1)
        ->orderBy('row_no', 'asc')
        ->get();

    // Get topic IDs
    $topicIds = Topic::where('webmaster_id', $webmasterId)
        ->where('status', 1)
        ->pluck('id')
        ->toArray();

    if (empty($topicIds)) {
        return response()->json([
            'success' => true,
            'items' => [],
            'message' => 'No gallery items found'
        ]);
    }

    // Get photos
    $photos = Photo::whereIn('topic_id', $topicIds)
        ->orderBy('row_no', 'asc')
        ->get();

    if ($photos->isEmpty()) {
        return response()->json([
            'success' => true,
            'items' => [],
            'message' => 'No photos found'
        ]);
    }

    // Topic-category mapping
    $topicCategories = TopicCategory::whereIn('topic_id', $topicIds)
        ->get()
        ->groupBy('topic_id')
        ->map(function ($items) {
            return $items->pluck('section_id')->toArray();
        });

    // Category names
    $categoryNames = [];
    foreach ($categories as $cat) {
        $categoryNames[$cat->id] = $cat->{'title_' . $lang} ?? $cat->title_en;
    }

    // Build gallery items
    $galleryItems = [];

    foreach ($photos as $photo) {
        $categoryIds = $topicCategories->get($photo->topic_id, []);
        $category = "all";

        if (!empty($categoryIds)) {
            $firstCat = $categoryIds[0];
            $category = $categoryNames[$firstCat] ?? "all";
        }

        if ($photo->file) {
            $galleryItems[] = [
                'image' => url('uploads/topics/' . $photo->file),
                'category' => $category,
                'alt' => $photo->title ?? $photo->file
            ];
        }
    }

    // Apply category filter
    $filter = request()->get('category', 'all');

    if ($filter !== "all") {
        $galleryItems = array_values(array_filter($galleryItems, function ($item) use ($filter) {
            return $item['category'] === $filter;
        }));
    }

    // Final JSON return
    return response()->json([
        'success' => true,
        'items' => $galleryItems,
        'count' => count($galleryItems),
        'categoryFilter' => $filter
    ]);
}
public function influencers()
{
    $lang = Helper::currentLanguage()->code;

    // 1?? Get influencers section
    $influencersSection = WebmasterSection::where('title_en', 'influencers')
        ->where('status', 1)
        ->firstOrFail();

    // 2?? Get section fields
    $sectionFields = DB::table('webmaster_section_fields')
        ->where('webmaster_id', $influencersSection->id)
        ->where('status', 1)
        ->orderBy('row_no')
        ->get();

    // 3?? Get topics
    $topics = Topic::where('webmaster_id', $influencersSection->id)
        ->where('status', 1)
        ->get();

    $topicIds = $topics->pluck('id');

    // 4?? Get topic fields
    $topicFields = DB::table('topic_fields')
        ->whereIn('topic_id', $topicIds)
        ->get()
        ->groupBy('topic_id');

    // 5?? Get topic tags
    $topicTags = DB::table('topic_tags')
        ->join('tags', 'topic_tags.tag_id', '=', 'tags.id')
        ->whereIn('topic_tags.topic_id', $topicIds)
        ->select(
            'topic_tags.topic_id',
            'tags.id as tag_id',
            'tags.title as tag_name',
            'tags.seo_url',
            'tags.details'
        )
        ->get()
        ->groupBy('topic_id');

    // 6?? Build response
    $topicsList = $topics->map(function ($topic) use ($topicFields, $sectionFields, $topicTags) {

        $fields = [];

        // Topic extra fields
        if (isset($topicFields[$topic->id])) {

            foreach ($topicFields[$topic->id] as $tf) {

                $fieldDef = $sectionFields->firstWhere('id', $tf->field_id);

                $fields[] = [
                    'field_id'    => $tf->field_id,
                    'field_title' => $fieldDef->title_en ?? '',
                    'value'       => $tf->field_value,
                    'type'        => $fieldDef->type ?? 'text',
                ];
            }
        }

        // Topic tags
        $tags = [];

        if (isset($topicTags[$topic->id])) {

            $tags = $topicTags[$topic->id]->map(function ($tag) {

                return [
                    'tag_id'    => $tag->tag_id,
                    'tag_name'  => $tag->tag_name,
                    'seo_url'   => $tag->seo_url,
                    'details'   => $tag->details,
                ];

            })->values();
        }

        return [
            'id'          => $topic->id,
            'title'       => $topic->title_en,
            'description' => $topic->details_en ?? '',

            'image' => $topic->photo_file
                ? url('uploads/topics/' . $topic->photo_file)
                : null,

            'tags'   => $tags,
            'fields' => $fields,
        ];
    });

    // 7?? Return JSON
    return response()->json([
        'success' => true,
        'section_fields' => $sectionFields,
        'topics' => $topicsList,
        'count' => $topicsList->count(),
    ]);
}


}








