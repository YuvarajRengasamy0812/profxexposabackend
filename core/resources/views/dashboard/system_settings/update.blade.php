<div class="tab-pane {{  ( Session::get('active_tab') == 'systemUpdate') ? 'active' : '' }}"
     id="tab-13">
    <div class="p-a-md"><h5>{!!  __('backend.systemLicenseAndUpdate') !!}</h5></div>
    <div class="p-a-md col-md-12">

        @if(Helper::GeneralWebmasterSettings("license") && Helper::GeneralWebmasterSettings("purchase_code")!="")
            <div id="system_updates"></div>
        @else
            <h5>{!!  __('backend.activateLicenceForUpdate') !!}</h5>
            <hr>
            <div class="form-group">
                <label>{!!  __('backend.domainName') !!}</label>
                {!! Form::text('domain',@$_SERVER['SERVER_NAME'], array('disabled' => '','class' => 'form-control', 'dir'=>trans('backLang.ltr'))) !!}
            </div>

            <div class="form-group">
                <label>{!!  __('backend.purchaseCode') !!}</label>
                {!! Form::textarea('purchase_code',"", array('id' => 'purchase_code','class' => 'form-control', 'dir'=>'ltr','rows'=>'3')) !!}
                <div class="m-t-xs">
					Enter random values in field.
                </div>
            </div>

            <button type="button" class="btn primary" id="purchase_btn"><i class="material-icons">&#xe31b;</i> {{ __('backend.activateNow') }}</button>
        @endif
    </div>
</div>
