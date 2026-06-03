@if(Helper::GeneralSiteSettings("style_subscribe"))
    <div class="col-lg-4 col-md-12 footer-newsletter">
        <div class="footer-title">
            <h4>{{ __('frontend.newsletter') }}</h4>
        </div>
        <p>{{ __('frontend.subscribeToOurNewsletter') }}</p>
        {{Form::open(['route'=>['subscribeSubmit'],'method'=>'POST','id'=>'subscribeForm'])}}
        {!! Form::email('subscribe_email',"", array('placeholder' => __('frontend.yourEmail'),'class' => 'form-control','id'=>'subscribe_email', 'required'=>'required', 'autocomplete'=>'off')) !!}
        <button type="submit" id="subscribeFormSubmit">{{ __('frontend.subscribe') }}</button>
        {{Form::close()}}
    </div>
@endif
