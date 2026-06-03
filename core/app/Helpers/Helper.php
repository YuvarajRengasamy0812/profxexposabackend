<?php

// This class file to define all general functions

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use App\Models\AnalyticsPage;
use App\Models\AnalyticsVisitor;
use App\Models\Banner;
use App\Models\Country;
use App\Models\Event;
use App\Models\Menu;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Topic;
use App\Models\Tag;
use App\Models\TopicCategory;
use App\Models\Webmail;
use App\Models\Language;
use App\Models\WebmasterSection;
use App\Models\WebmasterSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Auth;
use GeoIP;
use Spatie\Image\Image;
use Newsletter;

class Helper
{
    static function system_version()
    {
        return Helper::GeneralWebmasterSettings("version");
    }

    static function GeneralWebmasterSettings($var)
    {
        $_Loader_WebmasterSettings = session('_Loader_WebmasterSettings', []);
        if (empty($_Loader_WebmasterSettings)) {
            $_Loader_WebmasterSettings = WebmasterSetting::find(1);
            session(['_Loader_WebmasterSettings' => $_Loader_WebmasterSettings]);
        }
        return @$_Loader_WebmasterSettings->$var;
    }

    static function GeneralSiteSettings($var)
    {
        $_Loader_Web_Settings = session('_Loader_Web_Settings', []);
        if (empty($_Loader_Web_Settings)) {
            $_Loader_Web_Settings = Setting::find(1);
            session(['_Loader_Web_Settings' => $_Loader_Web_Settings]);
        }
        return @$_Loader_Web_Settings->$var;
    }

    static function eventsAlerts()
    {
        $_Loader_Events = session('_Loader_Events', []);
        if (empty($_Loader_Events)) {
            if (@Auth::user()->permissionsGroup->view_status) {
                $_Loader_Events = Event::where('created_by', '=', Auth::user()->id)->where('start_date', '>=', date('Y-m-d H:i:s'))->orderby('start_date', 'asc')->limit(10)->get();
            } else {
                $_Loader_Events = Event::where('start_date', '>=', date('Y-m-d H:i:s'))->orderby('start_date', 'asc')->limit(10)->get();
            }
            session(['_Loader_Events' => $_Loader_Events]);
        }
        return $_Loader_Events;
    }

    static function webmailsAlerts()
    {
        //List of all Webmails
        if (@Auth::user()->permissionsGroup->view_status) {
            $Webmails = Webmail::where('created_by', '=', Auth::user()->id)->orderby('id', 'desc')->where('status', '=',
                0)
                ->where('cat_id', '=', 0)->limit(4)->get();
        } else {
            $Webmails = Webmail::orderby('id', 'desc')->where('status', '=', 0)
                ->where('cat_id', '=', 0)->limit(4)->get();
        }

        return $Webmails;
    }

    static function webmailsNewCount()
    {
        //List of all Webmails
        if (@Auth::user()->permissionsGroup->view_status) {
            $Webmails = Webmail::where('created_by', '=', Auth::user()->id)->orderby('id', 'desc')->where('status', '=',
                0)->where('cat_id', '=', 0)->get();
        } else {
            $Webmails = Webmail::orderby('id', 'desc')->where('status', '=', 0)->where('cat_id', '=', 0)->get();
        }
        return count($Webmails);
    }

    static function BannersList($BannersSettingsId)
    {
        return Banner::where('section_id', $BannersSettingsId)->where('status', 1)->orderby('row_no', 'asc')->get();
    }

    static function MenuList($GroupId)
    {
        return Menu::where('father_id', $GroupId)->where('status', 1)->orderby('row_no', 'asc')->get();
    }

    static function getBrowser()
    {
        // check if IE 8 - 11+
        preg_match('/Trident\/(.*)/', @$_SERVER['HTTP_USER_AGENT'], $matches);
        if ($matches) {
            $version = intval($matches[1]) + 4;     // Trident 4 for IE8, 5 for IE9, etc
            return 'Internet Explorer ' . ($version < 11 ? $version : $version);
        }

        preg_match('/MSIE (.*)/', @$_SERVER['HTTP_USER_AGENT'], $matches);
        if ($matches) {
            return 'Internet Explorer ' . intval($matches[1]);
        }

        // check if Firefox, Opera, Chrome, Safari
        foreach (array('Firefox', 'OPR', 'Chrome', 'Safari') as $browser) {
            preg_match('/' . $browser . '/', @$_SERVER['HTTP_USER_AGENT'], $matches);
            if ($matches) {
                return str_replace('OPR', 'Opera',
                    $browser);   // we don't care about the version, because this is a modern browser that updates itself unlike IE
            }
        }
    }

    static function getOS()
    {

        $user_agent = @$_SERVER['HTTP_USER_AGENT'];

        $os_platform = "unknown";

        $os_array = array(
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $user_agent)) {
                $os_platform = $value;
            }

        }

        return $os_platform;

    }

    static function SaveVisitorInfo($PageTitle)
    {
        if (config('smartend.geoip_status')) {
            if (app()->runningInConsole()) {
                return;
            }

            $visitor_ip = request()->ip() ?: ($_SERVER['REMOTE_ADDR'] ?? null);
            $host = $_SERVER['HTTP_HOST'] ?? parse_url(config('app.url'), PHP_URL_HOST);
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $scheme = request()->getScheme() ?: parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';

            if (empty($visitor_ip) || empty($host)) {
                return;
            }

            $current_page_full_link = $scheme . '://' . $host . $requestUri;
            $page_load_time = round((microtime(true) - LARAVEL_START), 8);

            // Check is it already saved today to visitors?
            $SavedVisitor = AnalyticsVisitor::where('ip', '=', $visitor_ip)->where('date', '=', date('Y-m-d'))->first();
            if (empty($SavedVisitor) || @$SavedVisitor->country == "unknown") {

                // New to analyticsVisitors
                try {
                    $visitor_local_ip_details = AnalyticsVisitor::where('ip', $visitor_ip)->first();
                    if (!empty($visitor_local_ip_details)) {
                        $visitor_city = $visitor_local_ip_details->city;
                        $visitor_region = $visitor_local_ip_details->region;
                        $visitor_country_code = $visitor_local_ip_details->country_code;
                        $visitor_country = $visitor_local_ip_details->country;
                        $visitor_loc_0 = $visitor_local_ip_details->location_cor1;
                        $visitor_loc_1 = $visitor_local_ip_details->location_cor2;
                        $visitor_org = $visitor_local_ip_details->org;
                        $visitor_hostname = $visitor_local_ip_details->hostname;
                    } else {

                        $visitor_ip_details = [];
                        try {
                            $visitor_ip_details = GeoIP($visitor_ip);
                        } catch (\Exception $e) {

                        }

                        $visitor_city = @$visitor_ip_details->city;
                        if ($visitor_city == "") {
                            $visitor_city = "unknown";
                        }
                        $visitor_region = @$visitor_ip_details->state_name;
                        if ($visitor_region == "") {
                            $visitor_region = "unknown";
                        }
                        $visitor_country_code = @$visitor_ip_details->iso_code;
                        if ($visitor_country_code == "") {
                            $visitor_country_code = "unknown";
                        }
                        $visitor_country = @$visitor_ip_details->country;
                        if ($visitor_country == "") {
                            $visitor_country = "unknown";
                        }

                        $visitor_loc_0 = @$visitor_ip_details->lat;
                        if ($visitor_loc_0 == "") {
                            $visitor_loc_0 = "unknown";
                        }
                        $visitor_loc_1 = @$visitor_ip_details->lon;
                        if ($visitor_loc_1 == "") {
                            $visitor_loc_1 = "unknown";
                        }

                        $visitor_org = @$visitor_ip_details->timezone;
                        if ($visitor_org == "") {
                            $visitor_org = "unknown";
                        }
                        $visitor_hostname = @$visitor_ip_details->continent;
                        if ($visitor_hostname == "") {
                            $visitor_hostname = "unknown";
                        }
                    }
                } catch (Exception $e) {
                    $visitor_city = "unknown";
                    $visitor_region = "unknown";
                    $visitor_country_code = "unknown";
                    $visitor_country = "unknown";
                    $visitor_loc_0 = "unknown";
                    $visitor_loc_1 = "unknown";
                    $visitor_org = "unknown";
                    $visitor_hostname = "unknown";
                }

                $visitor_referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "unknown";
                $visitor_browser = Helper::getBrowser();
                $visitor_os = Helper::getOS();
                $visitor_screen_res = "unknown";

                // Start saving to database
                $Visitor = new AnalyticsVisitor;
                $Visitor->ip = $visitor_ip;
                $Visitor->city = $visitor_city;
                $Visitor->country_code = $visitor_country_code;
                $Visitor->country = $visitor_country;
                $Visitor->region = $visitor_region;
                $Visitor->location_cor1 = $visitor_loc_0;
                $Visitor->location_cor2 = $visitor_loc_1;
                $Visitor->os = $visitor_os;
                $Visitor->browser = $visitor_browser;
                $Visitor->resolution = $visitor_screen_res;
                $Visitor->referrer = $visitor_referrer;
                $Visitor->hostname = $visitor_hostname;
                $Visitor->org = $visitor_org;
                $Visitor->date = date('Y-m-d');
                $Visitor->time = date('H:i:s');
                $Visitor->save();

                // Start saving page info to database
                $VisitedPage = new AnalyticsPage;
                $VisitedPage->visitor_id = $Visitor->id;
                $VisitedPage->ip = $visitor_ip;
                $VisitedPage->title = $PageTitle;
                $VisitedPage->name = "unknown";
                $VisitedPage->query = $current_page_full_link;
                $VisitedPage->load_time = $page_load_time;
                $VisitedPage->date = date('Y-m-d');
                $VisitedPage->time = date('H:i:s');
                $VisitedPage->save();


            } else {
                // Already Saved to analyticsVisitors
                // Check if page saved
                $Savedpage = AnalyticsPage::where('visitor_id', '=', $SavedVisitor->id)->where('ip', '=',
                    $visitor_ip)->where('date', '=', date('Y-m-d'))->where('query', '=', $current_page_full_link)->first();
                if (empty($Savedpage)) {
                    $VisitedPage = new AnalyticsPage;
                    $VisitedPage->visitor_id = $SavedVisitor->id;
                    $VisitedPage->ip = $visitor_ip;
                    $VisitedPage->title = $PageTitle;
                    $VisitedPage->name = "unknown";
                    $VisitedPage->query = $current_page_full_link;
                    $VisitedPage->load_time = $page_load_time;
                    $VisitedPage->date = date('Y-m-d');
                    $VisitedPage->time = date('H:i:s');
                    $VisitedPage->save();
                }

            }
        }
    }

    static function Get_youtube_video_id($url)
    {
        if (preg_match('/youtu\.be/i', $url) || preg_match('/youtube\.com\/watch/i', $url)) {
            $pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
            preg_match($pattern, $url, $matches);
            if (count($matches) && strlen($matches[7]) == 11) {
                return $matches[7];
            }
        }

        return '';
    }

    static function Get_vimeo_video_id($url)
    {
        if (preg_match('/vimeo\.com/i', $url)) {
            $pattern = '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/';
            preg_match($pattern, $url, $matches);
            if (count($matches)) {
                return $matches[2];
            }
        }

        return '';
    }

    static function SocialShare($social, $title)
    {
        $shareLink = "";
        $URL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        switch ($social) {
            case "facebook":
                $shareLink = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($URL);
                break;
            case "twitter":
                $shareLink = "https://twitter.com/share?text=$title&url=" . urlencode($URL);
                break;
            case "linkedin":
                $shareLink = "http://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($URL) . "&title=$title";
                break;
            case "tumblr":
                $shareLink = "http://www.tumblr.com/share/link?url=" . urlencode($URL);
                break;
            case "whatsapp":
                $shareLink = "whatsapp://send?text=" . urlencode($URL);
                break;
        }

        return $shareLink;
    }

    static function GetIcon($path, $file, $size = "24px", $bootstrap = 4)
    {
        $ext = strrchr($file, ".");
        $ext = strtolower($ext);
        $ico = "fa";
        if ($bootstrap == 5) {
            $ico = "fa-solid";
        }
        $icon = "<i class=\"fa fa-file-o\"></i>";
        if ($ext == ".pdf") {
            $icon = "<i class=\"" . $ico . " fa-file-pdf-o\" style='color: red;font-size: " . $size . "'></i>";
        }
        if ($ext == '.png' or $ext == '.jpg' or $ext == '.jpeg' or $ext == '.gif') {
            $icon = "<img src='$path/$file' style='width: auto;height: " . $size . "' title=''>";
        }
        if ($ext == ".xls" or $ext == '.xlsx') {
            $icon = "<i class=\"" . $ico . " fa-file-excel-o\" style='color: green;font-size: " . $size . "'></i>";
        }
        if ($ext == ".ppt" or $ext == '.pptx' or $ext == '.pptm') {
            $icon = "<i class=\"" . $ico . " fa-file-powerpoint-o\" style='color: #1066E7;font-size:" . $size . "'></i>";
        }
        if ($ext == ".doc" or $ext == '.docx') {
            $icon = "<i class=\"" . $ico . " fa-file-word-o\" style='color: #0EA8DD;font-size: " . $size . "'></i>";
        }
        if ($ext == ".zip" or $ext == '.rar') {
            $icon = "<i class=\"" . $ico . " fa-file-zip-o\" style='color: #C8841D;font-size: " . $size . "'></i>";
        }
        if ($ext == ".txt" or $ext == '.rtf') {
            $icon = "<i class=\"" . $ico . " fa-file-text-o\" style='color: #7573AA;font-size: " . $size . "'></i>";
        }
        if ($ext == ".mp3" or $ext == '.wav') {
            $icon = "<i class=\"" . $ico . " fa-file-audio-o\" style='color: #8EA657;font-size: " . $size . "'></i>";
        }
        if ($ext == ".mp4" or $ext == '.avi') {
            $icon = "<i class=\"" . $ico . " fa-file-video-o\" style='color: #D30789;font-size: " . $size . "'></i>";
        }
        return $icon;

    }

    static function StringToSlug($string = "")
    {
        if ($string != "") {
            $separator = "-";
            $string = @strtolower($string);
            $string = @str_replace("/", $separator, $string);
            $string = @trim($string);
            $re = "/(\\s|\\" . $separator . ")+/mu";
            return preg_replace($re, $separator, $string);
        }
        return "";
    }

    static function SlugToString($slug = "")
    {
        if ($slug != "") {
            return @str_replace("-", " ", $slug);
        }
        return "";
    }

    static function URLSlug($url, $type = "", $id = 0, $num = 0)
    {
        $Check_SEO_st = true;
        $seo_url_slug = Helper::StringToSlug($url);

        $ReservedURLs = array(
            "search",
            "comment",
            "order",
            "sitemap"
        );


        if ($type == "section" && $id > 0) {
            // .. ..  Webmaster Sections
            if (count(Helper::languagesList()) > 0) {
                $check_WebmasterSection = [];
                $i = 0;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($i == 0) {
                        $check_WebmasterSection = WebmasterSection::where([['seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug], ['id', '!=', $id]]);
                    } else {
                        $check_WebmasterSection = $check_WebmasterSection->orWhere([['seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug], ['id', '!=', $id]]);
                    }
                    $i++;
                }
                $check_WebmasterSection_count = $check_WebmasterSection->count();
                if ($check_WebmasterSection_count > 0) {
                    $Check_SEO_st = false;
                }
            }
        } else {
            // .. ..  Webmaster Sections
            if (count(Helper::languagesList()) > 0) {
                $check_WebmasterSection = [];
                $i = 0;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($i == 0) {
                        $check_WebmasterSection = WebmasterSection::where('seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug);
                    } else {
                        $check_WebmasterSection = $check_WebmasterSection->orWhere('seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug);
                    }
                    $i++;
                }
                $check_WebmasterSection_count = $check_WebmasterSection->count();
                if ($check_WebmasterSection_count > 0) {
                    $Check_SEO_st = false;
                }
            }

        }

        if ($type == "category" && $id > 0) {
            // .. ..  Sections
            if (count(Helper::languagesList()) > 0) {
                $check_Section = [];
                $i = 0;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($i == 0) {
                        $check_Section = Section::where([['seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug], ['id', '!=', $id]]);
                    } else {
                        $check_Section = $check_Section->orWhere([['seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug], ['id', '!=', $id]]);
                    }
                    $i++;
                }
                $check_Section_count = $check_Section->count();
                if ($check_Section_count > 0) {
                    $Check_SEO_st = false;
                }
            }
        } else {
            // .. ..  Sections
            if (count(Helper::languagesList()) > 0) {
                $check_Section = [];
                $i = 0;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($i == 0) {
                        $check_Section = Section::where('seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug);
                    } else {
                        $check_Section = $check_Section->orWhere('seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug);
                    }
                    $i++;
                }
                $check_Section_count = $check_Section->count();
                if ($check_Section_count > 0) {
                    $Check_SEO_st = false;
                }
            }
        }

        if ($type == "topic" && $id > 0) {
            // .. ..  Topics
            if (count(Helper::languagesList()) > 0) {
                $check_Topic = [];
                $i = 0;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($i == 0) {
                        $check_Topic = Topic::where([['seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug], ['id', '!=', $id]]);
                    } else {
                        $check_Topic = $check_Topic->orWhere([['seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug], ['id', '!=', $id]]);
                    }
                    $i++;
                }
                $check_Topic_count = $check_Topic->count();
                if ($check_Topic_count > 0) {
                    $Check_SEO_st = false;
                }
            }
        } else {
            // .. ..  Topics
            if (count(Helper::languagesList()) > 0) {
                $check_Topic = [];
                $i = 0;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($i == 0) {
                        $check_Topic = Topic::where('seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug);
                    } else {
                        $check_Topic = $check_Topic->orWhere('seo_url_slug_' . $ActiveLanguage->code, $seo_url_slug);
                    }
                    $i++;
                }
                $check_Topic_count = $check_Topic->count();
                if ($check_Topic_count > 0) {
                    $Check_SEO_st = false;
                }
            }
        }

        if (in_array($seo_url_slug, $ReservedURLs)) {
            $Check_SEO_st = false;
        }

        if ($seo_url_slug == "") {
            $Check_SEO_st = true;
        }

        if ($Check_SEO_st) {
            return $seo_url_slug;
        } else {
            $url = preg_replace('/-' . $num . '$/', '', $url);
            $num++;
            $url = $url . "-" . $num;
            return Helper::URLSlug($url, $type, $id, $num);
        }
    }

    static function currentLanguage()
    {
        $locale = App::getLocale();
        if (session('locale', '') != "") {
            $locale = Session('locale');
        }
        $_Loader_Languages = session('_Loader_Languages', []);
        if (empty($_Loader_Languages)) {
            $_Loader_Languages = Language::all();
            session(['_Loader_Languages' => $_Loader_Languages]);
        }
        $Language = $_Loader_Languages->first(function ($item) use ($locale) {
            return $item->code == $locale;
        });
        if (empty($Language)) {
            $Language = Language::where("code", config('smartend.default_language'))->first();
        }
        return $Language;
    }

    static function LangFromCode($code)
    {
        return Language::where("code", $code)->first();
    }

    static function languagesList()
    {
        $_Loader_Languages = session('_Loader_Languages', []);
        if (empty($_Loader_Languages)) {
            $_Loader_Languages = Language::all();
            session(['_Loader_Languages' => $_Loader_Languages]);
        }
        return $_Loader_Languages->where("status", true);
    }

    static function languageName($Language)
    {
        if (@count(Helper::languagesList()) > 1) {
            $language_title = "<span class='label light text-dark lang-label'>";
            if (!empty($Language)) {
                if ($Language->icon != "") {
                    $language_title .= "<img src=\"" . asset('assets/dashboard/images/flags/' . $Language->icon . '.svg') . "\" alt=\"\">";
                }
                $language_title .= " <small>" . $Language->title . "</small></span>";
            }
            return $language_title;
        }
    }

    static function languageURL($lang_code, $page_type = "", $page_id = 0)
    {
        $lang_url = URL::to('lang/' . $lang_code);
        if (@$page_type == "section" && @$page_id > 0) {
            $lang_url = Helper::sectionURL(@$page_id, $lang_code);
        } elseif (@$page_type == "category" && @$page_id > 0) {
            $lang_url = Helper::categoryURL(@$page_id, $lang_code);
        } elseif (@$page_type == "topic" && @$page_id > 0) {
            $lang_url = Helper::topicURL(@$page_id, $lang_code);
        } elseif (@$page_type == "tag" && @$page_id > 0) {
            $lang_url = Helper::tagURL(@$page_id, $lang_code);
        } elseif (@$page_type == "404") {
            $lang_url = route("NotFound", ['lang' => $lang_code]);
        } elseif (@$page_type == "home") {
            if ($lang_code != config('smartend.default_language')) {
                $lang_url = URL::to($lang_code);
            } else {
                $lang_url = URL::to("/");
            }
        }
        return $lang_url;
    }

    static function canonicalURL($page_type = "", $page_id = 0)
    {
        $lang_code = @Helper::currentLanguage()->code;
        $canonical_url = url()->current();
        if (@$page_type == "section" && @$page_id > 0) {
            $canonical_url = Helper::sectionURL(@$page_id, $lang_code);
        } elseif (@$page_type == "category" && @$page_id > 0) {
            $canonical_url = Helper::categoryURL(@$page_id, $lang_code);
        } elseif (@$page_type == "topic" && @$page_id > 0) {
            $canonical_url = Helper::topicURL(@$page_id, $lang_code);
        } elseif (@$page_type == "404") {
            $canonical_url = route("NotFound");
        } elseif (@$page_type == "home") {
            if ($lang_code != config('smartend.default_language')) {
                $canonical_url = URL::to($lang_code);
            } else {
                $canonical_url = URL::to("/");
            }
        }
        return $canonical_url;
    }

    static function homeURL()
    {
        $lang = @Helper::currentLanguage()->code;
        if ($lang == config('smartend.default_language')) {
            return route("frontendRoute");
        }
        return route("frontendRoute", ["part1" => $lang]);
    }

    static function sectionURL($id, $lang = "", $WebmasterSection = [])
    {
        $section_url = "";
        try {
            if ($lang == "") {
                $lang = @Helper::currentLanguage()->code;
            }
            $title_var = "title_" . $lang;
            if (empty($WebmasterSection)) {
                $_Loader_WebmasterSections = session('_Loader_WebmasterSections', []);
                if (empty($_Loader_WebmasterSections)) {
                    $_Loader_WebmasterSections = WebmasterSection::all();
                    session(['_Loader_WebmasterSections' => $_Loader_WebmasterSections]);
                }
                $WebmasterSection = $_Loader_WebmasterSections->first(function ($item) use ($id) {
                    return $item->id == $id;
                });
            }

            if (!empty($WebmasterSection)) {
                if ($WebmasterSection->{'seo_url_slug_' . $lang} != "") {
                    $slug = $WebmasterSection->{'seo_url_slug_' . $lang};
                } else {
                    $slug = $WebmasterSection->{'seo_url_slug_' . config('smartend.default_language')};
                }
                if ($slug == "") {
                    $slug = Str::slug($WebmasterSection->$title_var, '-');
                }
                if ($lang != config('smartend.default_language')) {
                    $section_url = url($lang . "/" . $slug);
                } else {
                    $section_url = url($slug);
                }
            }
        } catch (\Exception $e) {

        }
        return $section_url;
    }

    static function categoryURL($id, $lang = "", $Category = [])
    {
        $category_url = "";
        try {
            if ($lang == "") {
                $lang = @Helper::currentLanguage()->code;
            }
            $title_var = "title_" . $lang;
            if (empty($Category)) {
                $Category = Section::find($id);
            }
            if (!empty($Category)) {
                if ($Category->{'seo_url_slug_' . $lang} != "") {
                    $cat_slug = $Category->{'seo_url_slug_' . $lang};
                } else {
                    $cat_slug = $Category->{'seo_url_slug_' . config('smartend.default_language')};
                }
                if ($cat_slug == "") {
                    $cat_slug = Str::slug($Category->$title_var, '-');
                }

                $WebmasterSection_slug = "NULL";
                $WebmasterSection = $Category->WebmasterSection;
                if (!empty($WebmasterSection)) {
                    if ($WebmasterSection->{'seo_url_slug_' . $lang} != "") {
                        $WebmasterSection_slug = $WebmasterSection->{'seo_url_slug_' . $lang};
                    } else {
                        $WebmasterSection_slug = $WebmasterSection->{'seo_url_slug_' . config('smartend.default_language')};
                    }
                    if ($WebmasterSection_slug == "") {
                        $WebmasterSection_slug = Str::slug($WebmasterSection->$title_var, '-');
                    }
                }

                $fatherSection2 = @$Category->fatherSection;
                if (!empty($fatherSection2)) {
                    if ($fatherSection2->{'seo_url_slug_' . $lang} != "") {
                        $cat2_slug = $fatherSection2->{'seo_url_slug_' . $lang};
                    } else {
                        $cat2_slug = $fatherSection2->{'seo_url_slug_' . config('smartend.default_language')};
                    }
                    if ($cat2_slug == "") {
                        $cat2_slug = Str::slug($fatherSection2->$title_var, '-');
                    }

                    $fatherSection1 = @$fatherSection2->fatherSection;
                    if (!empty($fatherSection1)) {
                        // is level 3
                        if ($fatherSection1->{'seo_url_slug_' . $lang} != "") {
                            $cat1_slug = $fatherSection1->{'seo_url_slug_' . $lang};
                        } else {
                            $cat1_slug = $fatherSection1->{'seo_url_slug_' . config('smartend.default_language')};
                        }
                        if ($cat1_slug == "") {
                            $cat1_slug = Str::slug($fatherSection1->$title_var, '-');
                        }
                        if ($lang != config('smartend.default_language')) {
                            $category_url = url($lang . "/" . $WebmasterSection_slug . "/" . $cat1_slug . "/" . $cat2_slug . "/" . $cat_slug);
                        } else {
                            $category_url = url($WebmasterSection_slug . "/" . $cat1_slug . "/" . $cat2_slug . "/" . $cat_slug);
                        }
                    } else {
                        // is level 2
                        if ($lang != config('smartend.default_language')) {
                            $category_url = url($lang . "/" . $WebmasterSection_slug . "/" . $cat2_slug . "/" . $cat_slug);
                        } else {
                            $category_url = url($WebmasterSection_slug . "/" . $cat2_slug . "/" . $cat_slug);
                        }
                    }
                } else {
                    // is level 1
                    if ($lang != config('smartend.default_language')) {
                        $category_url = url($lang . "/" . $WebmasterSection_slug . "/" . $cat_slug);
                    } else {
                        $category_url = url($WebmasterSection_slug . "/" . $cat_slug);
                    }
                }
            }

        } catch (\Exception $e) {

        }
        return $category_url;
    }

    static function topicURL($id, $lang = "", $Topic = [])
    {
        $topic_url = "";
        try {
            if ($lang == "") {
                $lang = @Helper::currentLanguage()->code;
            }
            $title_var = "title_" . $lang;
            if (empty($Topic)) {
                $Topic = Topic::find($id);
            }
            if (!empty($Topic)) {
                if ($Topic->{'seo_url_slug_' . $lang} != "") {
                    $topic_slug = $Topic->{'seo_url_slug_' . $lang};
                } else {
                    $topic_slug = $Topic->{'seo_url_slug_' . config('smartend.default_language')};
                }
                if ($topic_slug == "") {
                    $topic_slug = Str::slug($Topic->$title_var, '-');
                }
                if ($Topic->webmaster_id == 1) {
                    if ($lang != config('smartend.default_language')) {
                        $topic_url = url($lang . "/" . $topic_slug);
                    } else {
                        $topic_url = url($topic_slug);
                    }
                    return $topic_url;
                }

                $WebmasterSection_slug = "NULL";
                $WebmasterSection = $Topic->WebmasterSection;
                if (!empty($WebmasterSection)) {
                    if ($WebmasterSection->{'seo_url_slug_' . $lang} != "") {
                        $WebmasterSection_slug = $WebmasterSection->{'seo_url_slug_' . $lang};
                    } else {
                        $WebmasterSection_slug = $WebmasterSection->{'seo_url_slug_' . config('smartend.default_language')};
                    }
                    if ($WebmasterSection_slug == "") {
                        $WebmasterSection_slug = Str::slug($WebmasterSection->$title_var, '-');
                    }
                }

                $Category = [];
                $TopicCategory = TopicCategory::where('topic_id', $Topic->id)->first();
                if (!empty($TopicCategory)) {
                    $Category = Section::find($TopicCategory->section_id);
                }

                if (!empty($Category)) {
                    if ($Category->{'seo_url_slug_' . $lang} != "") {
                        $cat_slug = $Category->{'seo_url_slug_' . $lang};
                    } else {
                        $cat_slug = $Category->{'seo_url_slug_' . config('smartend.default_language')};
                    }
                    if ($cat_slug == "") {
                        $cat_slug = Str::slug($Category->$title_var, '-');
                    }

                    $fatherSection2 = @$Category->fatherSection;
                    if (!empty($fatherSection2)) {
                        if ($fatherSection2->{'seo_url_slug_' . $lang} != "") {
                            $cat2_slug = $fatherSection2->{'seo_url_slug_' . $lang};
                        } else {
                            $cat2_slug = $fatherSection2->{'seo_url_slug_' . config('smartend.default_language')};
                        }
                        if ($cat2_slug == "") {
                            $cat2_slug = Str::slug($fatherSection2->$title_var, '-');
                        }

                        $fatherSection1 = @$fatherSection2->fatherSection;
                        if (!empty($fatherSection1)) {
                            // is level 3
                            if ($fatherSection1->{'seo_url_slug_' . $lang} != "") {
                                $cat1_slug = $fatherSection1->{'seo_url_slug_' . $lang};
                            } else {
                                $cat1_slug = $fatherSection1->{'seo_url_slug_' . config('smartend.default_language')};
                            }
                            if ($cat1_slug == "") {
                                $cat1_slug = Str::slug($fatherSection1->$title_var, '-');
                            }
                            if ($lang != config('smartend.default_language')) {
                                $topic_url = url($lang . "/" . $WebmasterSection_slug . "/" . $cat1_slug . "/" . $cat2_slug . "/" . $cat_slug . "/" . $topic_slug);
                            } else {
                                $topic_url = url($WebmasterSection_slug . "/" . $cat1_slug . "/" . $cat2_slug . "/" . $cat_slug . "/" . $topic_slug);
                            }
                        } else {
                            // is level 2
                            if ($lang != config('smartend.default_language')) {
                                $topic_url = url($lang . "/" . $WebmasterSection_slug . "/" . $cat2_slug . "/" . $cat_slug . "/" . $topic_slug);
                            } else {
                                $topic_url = url($WebmasterSection_slug . "/" . $cat2_slug . "/" . $cat_slug . "/" . $topic_slug);
                            }
                        }
                    } else {
                        // is level 1
                        if ($lang != config('smartend.default_language')) {
                            $topic_url = url($lang . "/" . $WebmasterSection_slug . "/" . $cat_slug . "/" . $topic_slug);
                        } else {
                            $topic_url = url($WebmasterSection_slug . "/" . $cat_slug . "/" . $topic_slug);
                        }
                    }
                } else {
                    if ($lang != config('smartend.default_language')) {
                        $topic_url = url($lang . "/" . $WebmasterSection_slug . "/" . $topic_slug);
                    } else {
                        $topic_url = url($WebmasterSection_slug . "/" . $topic_slug);
                    }
                }
            }
        } catch (\Exception $e) {

        }
        return $topic_url;
    }

    static function tagURL($id, $lang = "", $DBTag = [])
    {
        $tag_url = "";
        try {
            if ($lang == "") {
                $lang = @Helper::currentLanguage()->code;
            }
            if (empty($DBTag)) {
                $DBTag = Tag::find($id);
            }

            if (!empty($DBTag)) {
                $slug = $DBTag->seo_url;
                if ($slug == "") {
                    $slug = Str::slug($DBTag->title, '-');
                }
                $tag_url = route("tag", $slug) . "?lang=" . $lang;
            }
        } catch (\Exception $e) {

        }
        return $tag_url;
    }

    static function formatDate($date = "")
    {
        if ($date != "") {
            $format = config('smartend.date_format');
            return date($format, strtotime($date));
        }
        return "";
    }

    static function dateForDB($date = "", $withTime = 0)
    {
        if ($date != "") {
            try {
                $format = config('smartend.date_format');
                if ($withTime) {
                    return Carbon::createFromFormat($format . " h:i A", $date)->format('Y-m-d H:i:s');
                } else {
                    return Carbon::createFromFormat($format, $date)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                return $date;
            }
        }
        return "";
    }

    static function jsDateFormat()
    {
        $format = config('smartend.date_format');
        $format = str_replace("Y", "YYYY", $format);
        $format = str_replace("m", "MM", $format);
        $format = str_replace("d", "DD", $format);
        return $format;
    }

    static function WebmasterSection($Id)
    {
        return WebmasterSection::find($Id);
    }

    static function SectionCategories($Id)
    {
        return Section::where('webmaster_id', '=', $Id)->where('father_id', '=',
            '0')->orderby('row_no', 'asc')->get();
    }

    static function ParseLinks($str)
    {
        $target = ' target="_blank"';
        $str = preg_replace('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.~]*(\?\S+)?)?)*)@', '<a href="$1" ' . $target . '>$1</a>', $str);
        $str = preg_replace('/<a\s[^>]*href\s*=\s*"((?!https?:\/\/)[^"]*)"[^>]*>/i', '<a href="http://$1" ' . $target . '>', $str);
        return $str;
    }

    static function Topic($id)
    {
        return Topic::where([['status', 1], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orwhere([['status', 1], ['expire_date', null]])->find($id);
    }

    static function Topics($SectionId, $CatId = 0, $limit = 12, $random = 0)
    {
        try {
            $Topics = Topic::where([['status', 1], ['webmaster_id', $SectionId], ['expire_date', '>=', date("Y-m-d")], ['expire_date', '<>', null]])->orwhere([['status', 1], ['webmaster_id', $SectionId], ['expire_date', null]]);
            if ($CatId > 0) {
                $Topics = $Topics->whereIn("id", TopicCategory::where('section_id', $CatId)->pluck("topic_id")->toarray());
            }
            if ($random) {
                $Topics = $Topics->inRandomOrder();
            } else {
                $Topics = $Topics->orderby('date', config('smartend.frontend_topics_order'))->orderby('id', config('smartend.frontend_topics_order'));
            }
            if ($limit > 0) {
                $Topics = $Topics->limit($limit);
            }
            $Topics = $Topics->get();
        } catch (\Exception $e) {
            $Topics = [];
        }
        return $Topics;
    }

    static function colorHexToRGB($color, $opacity = 1): string
    {
        $color = str_replace("#", "", $color);
        $parts = str_split($color, 2);
        $p1 = @hexdec(@$parts[0]);
        $p2 = @hexdec(@$parts[1]);
        $p3 = @hexdec(@$parts[2]);
        return "rgba(" . $p1 . "," . $p2 . "," . $p3 . "," . $opacity . ")";
    }

    static function colorHexToDarken($color, $darker=2) {

        $hash = (strpos($color, '#') !== false) ? '#' : '';
        $color = (strlen($color) == 7) ? str_replace('#', '', $color) : ((strlen($color) == 6) ? $color : false);
        if(strlen($color) != 6) return $hash.'000000';
        $darker = ($darker > 1) ? $darker : 1;

        list($R16,$G16,$B16) = str_split($color,2);

        $R = sprintf("%02X", floor(hexdec($R16)/$darker));
        $G = sprintf("%02X", floor(hexdec($G16)/$darker));
        $B = sprintf("%02X", floor(hexdec($B16)/$darker));

        return $hash.$R.$G.$B;
    }

    static function imageResize($file_path, $width = 0, $height = 0)
    {
        try {
            if (Helper::GeneralWebmasterSettings("image_resize")) {
                $supported_ext = ["jpg", "jpeg", "png", "gif", "webp"];
                $file_ext = strtolower(pathinfo(basename($file_path), PATHINFO_EXTENSION));
                if ($width > 0) {
                    $max_width = $width;
                } else {
                    $max_width = Helper::GeneralWebmasterSettings("image_resize_width");
                }
                if ($height > 0) {
                    $max_height = $height;
                } else {
                    $max_height = Helper::GeneralWebmasterSettings("image_resize_height");
                }
                if (in_array($file_ext, $supported_ext)) {
                    list($image_width, $image_height) = getimagesize($file_path);
                    if ($max_width < $image_width && $max_height < $image_height) {
                        Image::load($file_path)->width($max_width)->height($max_height)->save();
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }

    static function imageOptimize($file_path)
    {
        try {
            if (Helper::GeneralWebmasterSettings("image_optimize")) {
                $supported_ext = ["jpg", "jpeg", "png", "gif", "webp"];
                $file_ext = strtolower(pathinfo(basename($file_path), PATHINFO_EXTENSION));
                if (in_array($file_ext, $supported_ext)) {
                    Image::load($file_path)
                        ->optimize()
                        ->save($file_path);
                }
            }
        } catch (\Exception $e) {

        }
    }

    static function news_letter_subscribe($email, $first_name = "", $last_name = ""): int
    {
        $newsletter_status = config('smartend.newsletter_status');
        $newsletter_provider = config('smartend.newsletter_provider');
        $newsletter_api_key = config('smartend.newsletter_api_key');
        $newsletter_endpoint = config('smartend.newsletter_endpoint');
        $newsletter_list_id = config('smartend.newsletter_list_id');

        if ($newsletter_status && $newsletter_provider != "" && $newsletter_api_key != "" && $newsletter_list_id != "") {
            try {
                if ($newsletter_provider === "mailcoach") {
                    Newsletter::subscribeOrUpdate($email, ['first_name' => $first_name, 'last_name' => $last_name]);
                } else {
                    Newsletter::subscribeOrUpdate($email, ['FNAME' => $first_name, 'LNAME' => $last_name]);
                }
                return 1;
            } catch (\Exception $e) {

            }
        }
        return 0;
    }

    static function get_all_webmaster_section_columns($WebmasterSection, $col = ""): array
    {
        $COLS = [];
        if (!empty($WebmasterSection)) {
            if ($WebmasterSection->no_status) {
                $COLS["col_id"] = [
                    "title" => "ID",
                    "sortable" => 1,
                    "default" => 1,
                    "custom" => 0,
                ];
            }
            if ($WebmasterSection->title_status) {
                $COLS["col_title"] = [
                    "title" => __('backend.topicName'),
                    "sortable" => 1,
                    "default" => 1,
                    "custom" => 0,
                ];
            }
            if ($WebmasterSection->date_status) {
                $COLS["col_date"] = [
                    "title" => __('backend.topicDate'),
                    "sortable" => 1,
                    "default" => 1,
                    "custom" => 0,
                ];
            }
            if ($WebmasterSection->expire_date_status) {
                $COLS["col_expire_date"] = [
                    "title" => __('backend.expireDate'),
                    "sortable" => 1,
                    "default" => 1,
                    "custom" => 0,
                ];
            }
            if ($WebmasterSection->visits_status) {
                $COLS["col_visits"] = [
                    "title" => __('backend.visits'),
                    "sortable" => 1,
                    "default" => 1,
                    "custom" => 0,
                ];
            }
            if ($WebmasterSection->case_status) {
                $COLS["col_status"] = [
                    "title" => __('backend.status'),
                    "sortable" => 1,
                    "default" => 1,
                    "custom" => 0,
                ];
            }
            $cf_title_var = "title_" . @Helper::currentLanguage()->code;
            $cf_title_var2 = "title_" . config('smartend.default_language');
            foreach ($WebmasterSection->customFields->whereNotIn("type",[99]) as $customField) {
                $view_permission_groups = [];
                if ($customField->view_permission_groups != "") {
                    $view_permission_groups = explode(",", $customField->view_permission_groups);
                }
                if (in_array(Auth::user()->permissions_id, $view_permission_groups) || in_array(0, $view_permission_groups) || $customField->view_permission_groups == "") {
                    if ($customField->$cf_title_var != "") {
                        $cf_title = $customField->$cf_title_var;
                    } else {
                        $cf_title = $customField->$cf_title_var2;
                    }

                    $COLS["col_custom_" . $customField->id] = [
                        "title" => $cf_title,
                        "sortable" => 0,
                        "default" => ($customField->in_table) ? 1 : 0,
                        "custom" => 1,
                    ];

                }
            }

            $COLS["col_created_by"] = [
                "title" => __('backend.createdBy'),
                "sortable" => 1,
                "default" => 0,
                "custom" => 0,
            ];
            $COLS["col_created_at"] = [
                "title" => __('backend.createdAt'),
                "sortable" => 1,
                "default" => 0,
                "custom" => 0,
            ];
            $COLS["col_updated_by"] = [
                "title" => __('backend.updatedBy'),
                "sortable" => 1,
                "default" => 0,
                "custom" => 0,
            ];
            $COLS["col_updated_at"] = [
                "title" => __('backend.updatedAt'),
                "sortable" => 1,
                "default" => 0,
                "custom" => 0,
            ];
            if ($col != "") {
                if (!empty(@$COLS[$col])) {
                    return @$COLS[$col];
                }
            } else {
                return @$COLS;
            }
        }
        return [];
    }

    static function get_user_webmaster_columns($webmaster_id): array
    {
        $User = Auth::user();
        if (!empty($User)) {
            $table_columns = [];
            try {
                $table_columns = json_decode($User->table_columns, true);
            } catch (\Exception $e) {

            }
            if (!empty($table_columns)) {
                if (!empty(@$table_columns["sec_" . $webmaster_id])) {
                    return @$table_columns["sec_" . $webmaster_id];
                }
            }
        }
        return [];
    }

    static function get_webmaster_columns($WebmasterSection): array
    {
        $ColsList = [];
        $AllCols = Helper::get_all_webmaster_section_columns($WebmasterSection);
        $MyCols = Helper::get_user_webmaster_columns($WebmasterSection->id);
        if (count($MyCols) > 0) {
            foreach ($MyCols as $MyCol => $MyColSt) {
                if ($MyColSt) {
                    $COL = Helper::get_all_webmaster_section_columns($WebmasterSection, $MyCol);
                    if (!empty($COL)) {
                        $ColsList[$MyCol] = $COL;
                    }
                }
            }
        } else {
            foreach ($AllCols as $KEY => $COL) {
                if (@$COL['default']) {
                    $ColsList[$KEY] = $COL;
                }
            }
        }
        return $ColsList;
    }

}

?>
