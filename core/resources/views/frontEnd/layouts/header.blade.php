<header id="header" class="{{ (Helper::GeneralSiteSettings("style_header"))?"fixed-top":"" }} {{ (Helper::GeneralSiteSettings("style_bg_type"))?"header-no-bg":"header-bg" }}">
    <div class="container d-flex align-items-center">
        <a class="logo me-auto" href="{{ Helper::homeURL() }}">
            @if(Helper::GeneralSiteSettings("style_logo_" . @Helper::currentLanguage()->code) !="")
                <img alt="{{ Helper::GeneralSiteSettings("site_title_" . @Helper::currentLanguage()->code) }}"
                     src="{{ URL::to('uploads/settings/'.Helper::GeneralSiteSettings("style_logo_" . @Helper::currentLanguage()->code)) }}"  class="img-fluid" width="230" height="50">
            @else
                <img alt="{{ Helper::GeneralSiteSettings("site_title_" . @Helper::currentLanguage()->code) }}" src="{{ URL::to('uploads/settings/nologo.png') }}"  class="img-fluid"  width="172" height="50">
            @endif
        </a>

        @include('frontEnd.layouts.menu')
    </div>
</header>
