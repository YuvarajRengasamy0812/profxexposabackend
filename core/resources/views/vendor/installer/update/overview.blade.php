@extends('vendor.installer.layouts.master-update')

@section('title', __('installer_messages.updater.welcome.title'))
@section('container')
    <p class="paragraph text-center">{{ trans_choice('installer_messages.updater.overview.message', $numberOfUpdatesPending, ['number' => $numberOfUpdatesPending]) }}</p>
    <div class="buttons">
        <a href="{{ route('LaravelUpdater::database') }}" class="button">{{ __('installer_messages.updater.overview.install_updates') }}</a>
    </div>
@stop
