<div class="p-a-2">
    <div class="form-group row">
        <label
            class="col-sm-3 form-control-label">{!!  __('backend.tag') !!}
        </label>
        <div class="col-sm-9">
            {!! Form::text('title','', array('placeholder' => '','class' => 'form-control','required'=>'','maxlength'=>191)) !!}
        </div>
    </div>
    <div class="form-group row">
        <label
            class="col-sm-3 form-control-label">{!!  __('backend.friendlyURL') !!}
        </label>
        <div class="col-sm-9">
            {!! Form::text('seo_url','', array('placeholder' => '','class' => 'form-control','required'=>'','maxlength'=>191)) !!}
        </div>
    </div>
    <div class="form-group row">
        <label
            class="col-sm-3 form-control-label">{!!  __('backend.bannerDetails') !!}
        </label>
        <div class="col-sm-9">
            {!! Form::textarea('details','', array('class' => 'form-control','rows'=>'5')) !!}
        </div>
    </div>
</div>
