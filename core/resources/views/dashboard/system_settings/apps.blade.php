<div class="tab-pane {{  ( Session::get('active_tab') == 'appsSettingsTab') ? 'active' : '' }}"
     id="tab-1">
    <div class="p-a-md"><h5>{!!  __('backend.appsSettings') !!}</h5></div>
    <div class="p-a-md col-md-12">

        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('analytics_status','1',$WebmasterSetting->analytics_status, array('id' => 'analytics_status')) !!}
                <i class="success"></i><label
                    for="analytics_status">{{ __('backend.visitorsAnalytics') }}</label>
            </label>
        </div>

        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('newsletter_status','1',$WebmasterSetting->newsletter_status, array('id' => 'newsletter_status')) !!}
                <i class="success"></i><label
                    for="newsletter_status">{{ __('backend.newsletter') }}</label>
            </label>
        </div>

        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('inbox_status','1',$WebmasterSetting->inbox_status, array('id' => 'inbox_status')) !!}
                <i class="success"></i></i><label
                    for="inbox_status">{{ __('backend.siteInbox') }}</label>
            </label>
        </div>

        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('calendar_status','1',$WebmasterSetting->calendar_status, array('id' => 'calendar_status')) !!}
                <i class="success"></i><label
                    for="calendar_status">{{ __('backend.calendar') }}</label>
            </label>
        </div>

        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('banners_status','1',$WebmasterSetting->banners_status, array('id' => 'banners_status')) !!}
                <i class="success"></i><label
                    for="banners_status">{{ __('backend.adsBanners') }}</label>
            </label>
        </div>


        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('popups_status','1',$WebmasterSetting->popups_status, array('id' => 'popups_status')) !!}
                <i class="success"></i><label
                    for="popups_status">{{ __('backend.popups') }}</label>
            </label>
        </div>

        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('tags_status','1',$WebmasterSetting->tags_status, array('id' => 'tags_status')) !!}
                <i class="success"></i><label
                    for="tags_status">{{ __('backend.tags') }}</label>
            </label>
        </div>
        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('menus_status','1',$WebmasterSetting->menus_status, array('id' => 'menus_status')) !!}
                <i class="success"></i><label
                    for="menus_status">{{ __('backend.siteMenus') }}</label>
            </label>
        </div>
        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('file_manager_status','1',$WebmasterSetting->file_manager_status, array('id' => 'file_manager_status')) !!}
                <i class="success"></i><label
                    for="file_manager_status">{{ __('backend.fileManager') }}</label>
            </label>
        </div>
        <div class="checkbox">
            <label class="md-check">
                {!! Form::checkbox('settings_status','1',$WebmasterSetting->settings_status, array('id' => 'settings_status')) !!}
                <i class="success"></i><label
                    for="settings_status">{{ __('backend.generalSiteSettings') }}</label>
            </label>
        </div>
    </div>
</div>
