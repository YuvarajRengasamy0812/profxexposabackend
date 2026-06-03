@extends('vendor.installer.layouts.master-update')

@section('title', __('installer_messages.updater.final.title'))
@section('container')
    <p class="paragraph text-center">{{ session('message')['message'] }}</p>
    <div class="buttons">
        <a href="{{ url('/') }}" class="button">{{ __('installer_messages.updater.final.exit') }}</a>
    </div>
@stop
