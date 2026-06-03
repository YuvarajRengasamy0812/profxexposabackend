<div class="tab-pane {{  ( Session::get('active_tab') == 'SEOSettingTab') ? 'active' : '' }}"
     id="tab-3">
    <div class="p-a-md"><h5>{!!  __('backend.seoTabTitle') !!}</h5></div>

    <div class="p-a-md col-md-12">
        <div class="form-group">
            <label for="image_optimize1" class="h6">{{ __('backend.optimizeImages') }}</label>
            <div class="radio">
                <div>
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('image_optimize','1',$WebmasterSetting->image_optimize ? true : false , array('id' => 'image_optimize1','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.active') }}
                    </label>
                </div>
                <div style="margin-top: 5px;">
                    <label class="ui-check ui-check-md">
                        {!! Form::radio('image_optimize','0',$WebmasterSetting->image_optimize ? false : true , array('id' => 'image_optimize2','class'=>'has-value')) !!}
                        <i class="dark-white"></i>
                        {{ __('backend.notActive') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="image_optimize1" class="h6">{{ __('backend.resizeImages') }}</label>
            <div class="row">
                <div class="col-sm-6">
                    <div class="radio m-t-sm">
                        <div>
                            <label class="ui-check ui-check-md"
                                   onclick="document.getElementById('image_resize_options').style.display='block'">
                                {!! Form::radio('image_resize','1',$WebmasterSetting->image_resize ? true : false , array('id' => 'image_resize1','class'=>'has-value')) !!}
                                <i class="dark-white"></i>
                                {{ __('backend.active') }}
                            </label>
                        </div>
                        <div style="margin-top: 5px;">
                            <label class="ui-check ui-check-md"
                                   onclick="document.getElementById('image_resize_options').style.display='none'">
                                {!! Form::radio('image_resize','0',$WebmasterSetting->image_resize ? false : true , array('id' => 'image_resize2','class'=>'has-value')) !!}
                                <i class="dark-white"></i>
                                {{ __('backend.notActive') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div id="image_resize_options" class="{{ ($WebmasterSetting->image_resize)?"":"displayNone" }}">
                        <div class="row">
                            <div class="col-sm-6 col-xs-6">
                                <label for="image_resize_width">{{ __('backend.maxImageWidth') }}</label>
                                <input type="number" class="form-control" min="100" max="10000"
                                       name="image_resize_width" id="image_resize_width"
                                       value="{{ $WebmasterSetting->image_resize_width }}">
                            </div>
                            <div class="col-sm-6 col-xs-6">
                                <label for="image_resize_height">{{ __('backend.maxImageHeight') }}</label>
                                <input type="number" class="form-control" min="100" max="10000"
                                       name="image_resize_height" id="image_resize_height"
                                       value="{{ $WebmasterSetting->image_resize_height }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="p-a b-a white dk">
            <h6>{{ __('backend.seoFixUrls') }}</h6>
            <div class="text-muted">{!! __('backend.seoFixUrlsService') !!}</div>
            <a href="{{ route("webmasterSEORepair") }}"
               onclick="return confirm('{{ __("backend.seoFixUrlsConfirm") }}')"
               class="btn white btn-sm m-t-xs">{{ __('backend.seoFixUrlsStart') }}</a>
        </div>
        <br>
        <div>
            <h6>{{ __('backend.sitemapLinks') }}</h6>
            @foreach(Helper::languagesList() as $ActiveLanguage)
                <?php
                $link = route('siteMapByLang', $ActiveLanguage->code);
                if ($ActiveLanguage->code == config('smartend.default_language')) {
                    $link = route('siteMap');
                }
                ?>
                <div class="p-a-sm m-b-sm white dk b-a">
                    <span class="label text-sm pull-right">{{ $ActiveLanguage->title }}</span>
                    <a href="{{ $link }}" target="_blank">{{ $link }}</a>
                </div>
            @endforeach
        </div>
    </div>
</div>
