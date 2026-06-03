@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ __('installer_messages.environment.menu.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-cog fa-fw" aria-hidden="true"></i>
    {!! __('installer_messages.environment.menu.title') !!}
@endsection

@section('container')

    <p class="text-center">
        {!! __('installer_messages.environment.menu.desc') !!}
    </p>
    <div class="buttons">
        <a href="{{ route('LaravelInstaller::environmentWizard') }}" class="button button-wizard">
            Set Database Connection <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
        </a>
    </div>

@endsection
