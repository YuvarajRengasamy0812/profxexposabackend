@extends('vendor.installer.layouts.master-update')

@section('title', __('installer_messages.updater.welcome.title'))
@section('container')
    <p class="paragraph text-center">
    	{{ __('installer_messages.updater.welcome.message') }}
    </p>
    <div class="buttons">
        <a href="{{ route('LaravelUpdater::overview') }}" class="button">{{ __('installer_messages.next') }}</a>
    </div>
@stop
