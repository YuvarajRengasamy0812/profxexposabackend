<div class="p-a-2">
    <div class="form-group row">
        <label
            class="col-sm-3 form-control-label">{!!  __('backend.tag') !!}
        </label>
        <div class="col-sm-9">
            {!! Form::text('title',$Tag->title, array('placeholder' => '','class' => 'form-control','required'=>'','maxlength'=>191)) !!}
        </div>
    </div>
    <div class="form-group row">
        <label
            class="col-sm-3 form-control-label">{!!  __('backend.friendlyURL') !!}
        </label>
        <div class="col-sm-9">
            {!! Form::text('seo_url',$Tag->seo_url, array('placeholder' => '','class' => 'form-control','required'=>'','maxlength'=>191)) !!}
        </div>
    </div>
    <div class="form-group row">
        <label
            class="col-sm-3 form-control-label">{!!  __('backend.bannerDetails') !!}
        </label>
        <div class="col-sm-9">
            {!! Form::textarea('details',$Tag->details, array('class' => 'form-control','rows'=>'5')) !!}
        </div>
    </div>
    <input type="hidden" name="tag_id" value="{{ $Tag->id }}">
</div>
