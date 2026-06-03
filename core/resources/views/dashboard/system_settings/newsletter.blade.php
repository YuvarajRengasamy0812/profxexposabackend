<div class="tab-pane {{  ( Session::get('active_tab') == 'newsletterTab') ? 'active' : '' }}"
     id="tab-15">
    <div class="p-a-md"><h5>{!!  __('backend.newsletterProvider') !!}</h5></div>

    <div class="p-a-md col-md-12">

        <div class="form-group">
            <label for="newsletter_provider_status0">{{ __('backend.newsletterStatus') }} : </label>
            <div class="radio">
                <div>
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('newsletter_provider_status','0',(config('smartend.newsletter_status') ==0) , array('id' => 'newsletter_provider_status0','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.notActive') }}
                    </label>
                </div>
                <div style="margin-top: 5px;">
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('newsletter_provider_status','1',(config('smartend.newsletter_status') ==1), array('id' => 'newsletter_provider_status1','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.active') }}
                    </label>
                </div>
            </div>
        </div>
        <div id="newsletter_service_info" class="{{ (config('smartend.newsletter_status') ==1)?"":"displayNone" }}">
            <div class="form-group">
                <label for="newsletter_provider">{!!  __('backend.newsletterProvider') !!}</label>
                <select name="newsletter_provider" id="newsletter_provider" class="form-control c-select">
                    <option
                        value="mailchimp" {{ (config('smartend.newsletter_provider') == "mailchimp") ? "selected='selected'":""  }}>
                        mailchimp.com ( Default )
                    </option>
                    <option
                        value="mailcoach" {{ (config('smartend.newsletter_provider') == "mailcoach") ? "selected='selected'":""  }}>
                        mailcoach.app
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>API Key</label>
                {!! Form::text('newsletter_api_key',config('smartend.newsletter_api_key'), array('placeholder' => '','class' => 'form-control', 'dir'=>__('backLang.ltr'))) !!}
            </div>
            <div class="form-group {{ (config('smartend.newsletter_provider') =="mailcoach")?"":"displayNone" }}" id="newsletter_endpoint_div">
                <label>End Point</label>
                {!! Form::text('newsletter_endpoint',config('smartend.newsletter_endpoint'), array('placeholder' => '','class' => 'form-control', 'dir'=>__('backLang.ltr'))) !!}
            </div>
            <div class="form-group">
                <label>List ID</label>
                {!! Form::text('newsletter_list_id',config('smartend.newsletter_list_id'), array('placeholder' => '','class' => 'form-control', 'dir'=>__('backLang.ltr'))) !!}
            </div>
            <div class="form-group">
                <label>{!!  __('backend.analyticsApiMsg') !!} :</label>
                <div>
                    <a href="https://mailchimp.com/" class="btn rounded btn-outline b-info text-info" target="_blank"><i class="material-icons">&#xe895;</i>
                        mailchimp.com</a>
                    <a href="https://mailcoach.app/" class="btn rounded btn-outline b-info text-info" target="_blank"><i class="material-icons">&#xe895;</i>
                        mailcoach.app</a>
                </div>
            </div>
        </div>
    </div>
</div>
