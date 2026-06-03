<div class="social-links text-center text-md-right pt-3 pt-md-0">
    @if(Helper::GeneralSiteSettings('social_link1'))
        <a href="{{Helper::GeneralSiteSettings('social_link1')}}" data-bs-toggle="tooltip" title="Facebook" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-facebook"><i
                class="bi bi-facebook"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link2'))
        <a href="{{Helper::GeneralSiteSettings('social_link2')}}" data-bs-toggle="tooltip" title="Twitter" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-x"><i
                class="bi bi-twitter-x"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link4'))
        <a href="{{Helper::GeneralSiteSettings('social_link4')}}" data-bs-toggle="tooltip" title="linkedin" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-linkedin"><i
                class="bi bi-linkedin"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link5'))
        <a href="{{Helper::GeneralSiteSettings('social_link5')}}" data-bs-toggle="tooltip" title="Youtube" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-youtube"><i
                class="bi bi-youtube"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link6'))
        <a href="{{Helper::GeneralSiteSettings('social_link6')}}" data-bs-toggle="tooltip" title="Instagram" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-instagram"><i
                class="bi bi-instagram"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link7'))
        <a href="{{Helper::GeneralSiteSettings('social_link7')}}" data-bs-toggle="tooltip" title="Pinterest" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-pinterest"><i
                class="bi bi-pinterest"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link8'))
        <a href="{{Helper::GeneralSiteSettings('social_link8')}}" data-bs-toggle="tooltip" title="Threads" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-threads"><i
                class="bi bi-threads"></i></a>
    @endif
    @if(Helper::GeneralSiteSettings('social_link9'))
        <a href="{{Helper::GeneralSiteSettings('social_link9')}}" data-bs-toggle="tooltip" title="Snapchat" data-bs-placement="{{ @$tt_position }}"
           target="_blank" class="social-snapchat"><i
                class="bi bi-snapchat"></i></a>
    @endif
</div>
