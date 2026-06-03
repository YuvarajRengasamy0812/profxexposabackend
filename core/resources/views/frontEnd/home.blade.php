@extends('frontEnd.layouts.master')

@section('content')
    <div class="home-page">
        <?php
        $title_var = "title_" . @Helper::currentLanguage()->code;
        $title_var2 = "title_" . config('smartend.default_language');
        $details_var = "details_" . @Helper::currentLanguage()->code;
        $details_var2 = "details_" . config('smartend.default_language');
        ?>

        @include('frontEnd.layouts.slider')
        @include('frontEnd.homepage.row1')
        @include('frontEnd.homepage.row2')
        @include('frontEnd.homepage.row3')
        @include('frontEnd.homepage.row4')
        @include('frontEnd.homepage.row5')
        @include('frontEnd.homepage.row6')
        @include('frontEnd.homepage.row7')

        @include('frontEnd.layouts.popup',['Popup'=>@$Popup])
    </div>
@endsection
@push('after-styles')
    @if(Helper::GeneralSiteSettings("style_header") && Helper::GeneralSiteSettings("style_bg_type"))
        <style>
            .fixed-top-margin {
                margin-top: 0 !important;
            }


            .header-bg, .header-bg a {
                color: #444444;
            }

            @media (min-width: 968px) {

                .header-no-bg, .header-no-bg a, .topbar-no-bg, .topbar-no-bg a {
                    color: #fff;
                }

                .header-no-bg .navbar a, .topbar-no-bg .header-dropdown .btn {
                    color: #fff;
                }

                .dropdown-item {
                    color: #212529 !important;
                }

                .header-scrolled .navbar a, .header-scrolled .header-dropdown .btn {
                    color: #444444;
                }
            }

            .topbar-no-bg {
                box-shadow: 0 0 1px rgba(255, 255, 255, 0.5) !important;
            }
        </style>
    @endif
@endpush
