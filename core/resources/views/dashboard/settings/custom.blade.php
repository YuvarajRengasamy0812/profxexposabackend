
<div class="tab-pane {{  ( Session::get('active_tab') == 'codeTab') ? 'active' : '' }}"
     id="tab-7">
    <div class="p-a-md"><h5><i class="material-icons">&#xe86f;</i>
            &nbsp; {!!  __('backend.customCode') !!}</h5></div>
    <div class="p-a-md col-md-12">
        <div class="form-group">
            <h6>{!!  __('backend.customCSS') !!}</h6>
            {!! Form::textarea('css_code',$Setting->css, array('dir' => "ltr",'class' => 'form-control','rows'=>'15')) !!}
        </div>
        <div class="form-group">
            <h6>{!!  __('backend.customCodeOnHead') !!}</h6>
            <textarea name="js_code" class="form-control" rows="10"  placeholder="<style>
...
</style>

<script>
...
</script>">{{ $Setting->js }}</textarea>
        </div>
        <div class="form-group">
            <h6>{!!  __('backend.customCodeOnBody') !!}</h6>
            {!! Form::textarea('body_code',$Setting->body, array('dir' => "ltr",'class' => 'form-control','rows'=>'10')) !!}
        </div>
    </div>
</div>
