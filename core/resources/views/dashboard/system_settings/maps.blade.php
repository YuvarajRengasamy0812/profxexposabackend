<div class="tab-pane {{  ( Session::get('active_tab') == 'googleMapsTab') ? 'active' : '' }}"
     id="tab-10">
    <div class="p-a-md"><h5>{!!  __('backend.googleMaps') !!}</h5></div>


    <div class="p-a-md col-md-12">
        <div class="form-group">
            <label>{{ __('backend.googleMapsStatus') }} : </label>
            <div class="radio">
                <div>
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('google_maps_status','0',(config('smartend.google_maps_key') !="") ? false : true , array('id' => 'google_maps_status2','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.notActive') }}
                    </label>
                </div>
                <div style="margin-top: 5px;">
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('google_maps_status','1',(config('smartend.google_maps_key') !="") ? true : false , array('id' => 'google_maps_status1','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.active') }}
                    </label>
                </div>
            </div>
        </div>

        <div
            id="google_maps_div" {!!  ( config('smartend.google_maps_key') =="") ? "style='display:none'":"" !!}>

            <div class="form-group">
                <label>{!!  __('backend.googleMapsKey') !!}</label>
                {!! Form::text('google_maps_key',config('smartend.google_maps_key'), array('placeholder' => '','class' => 'form-control','id' => 'google_maps_key', 'dir'=>'ltr')) !!}
            </div>

        </div>

        <a href="https://developers.google.com/maps/documentation/javascript/get-api-key"
           style="text-decoration: underline" target="_blank">
            <small><i
                    class="material-icons">&#xe8fd;</i> Google Maps</small>
        </a>

    </div>
</div>
