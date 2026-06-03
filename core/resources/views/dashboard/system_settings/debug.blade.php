<div class="tab-pane {{  ( Session::get('active_tab') == 'debugMode') ? 'active' : '' }}"
     id="tab-14">
    <div class="p-a-md"><h5>{!!  __('backend.debugMode') !!}</h5></div>


    <div class="p-a-md col-md-12">
        <div class="form-group">
            <label>{{ __('backend.debugModeWarn') }} </label>
            <hr>
            <div class="radio">
                <div>
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('debug_mode_status','0',(config('smartend.app_debug') !="") ? false : true , array('id' => 'debug_mode_status0','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.notActive') }}
                    </label>
                </div>
                <div style="margin-top: 5px;">
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('debug_mode_status','1',(config('smartend.app_debug') !="") ? true : false , array('id' => 'debug_mode_status1','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.active') }}
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>
