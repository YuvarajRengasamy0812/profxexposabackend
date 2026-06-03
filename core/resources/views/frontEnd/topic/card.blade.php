@if(!empty($Topic))
    <article>
        <div class="card post-card pa-0 mb-4">
            @if($Topic->webmasterSection->type==2 && $Topic->video_file!="")
                {{--video--}}
                <a href="{{ $topic_link_url }}">
                    <div class="video-container position-relative">
                        @if ($Topic->photo_file != "")
                            <img class="card-img-top"
                                 src="{{ URL::to('uploads/topics/'.$Topic->photo_file) }}"  width="100%" height="100%"
                                 alt="{{ $title }}" loading="lazy"/>
                        @else
                            <?php
                            $img_url = "";
                            ?>
                            @if($Topic->video_type ==1)
                                <?php
                                $Youtube_id = Helper::Get_youtube_video_id($Topic->video_file);
                                $img_url = "http://img.youtube.com/vi/" . $Youtube_id . "/0.jpg";
                                ?>
                                <img class="card-img-top" src="{{ $img_url }}"  width="100%" height="100%"
                                     alt="{{ $title }}" loading="lazy"/>
                            @else
                                <div
                                    class="bg-secondary w-100 rounded-top h-200px"></div>
                            @endif
                        @endif
                        <h2 class="position-absolute top-50 start-50 translate-middle">
                                                                    <span
                                                                        class="badge  rounded opacity-75  bg-white text-primary"><i
                                                                            class="fa-solid fa-play"></i></span>
                        </h2>
                    </div>
                </a>
            @elseif($Topic->webmasterSection->type==3 && $Topic->audio_file!="")
                {{--audio--}}
                <div class="audio-container position-relative">
                    @if($Topic->photo_file !="")
                        <img class="card-img-top" loading="lazy"  width="100%" height="100%"
                             src="{{ URL::to('uploads/topics/'.$Topic->photo_file) }}"
                             alt="{{ $title }}"/>
                    @endif
                    <div class="audio-player">
                        <audio crossorigin preload="none">
                            <source
                                src="{{ URL::to('uploads/topics/'.$Topic->audio_file) }}"
                                type="audio/mpeg">
                        </audio>
                    </div>
                </div>
            @elseif(count($Topic->photos)>0)
                {{--photo slider--}}
                <a href="{{ $topic_link_url }}">
                    <div class="image-container position-relative">
                        @if($Topic->photo_file !="")
                            <img class="card-img-top" loading="lazy"  width="100%" height="100%"
                                 src="{{ URL::to('uploads/topics/'.$Topic->photo_file) }}"
                                 alt="{{ $title }}"/>

                        @else
                            @foreach($Topic->photos->random(1) as $photo)
                                <img class="card-img-top" loading="lazy"
                                     src="{{ URL::to('uploads/topics/'.$photo->file) }}"  width="100%" height="100%"
                                     alt="{{ $title }}"/>
                            @endforeach
                        @endif
                        @if($Topic->photos->count() >1)
                            <span class="extra-images-count"><span
                                    class="badge  rounded-pill text-primary bg-light"><i class="fa-solid fa-images"></i> {{ $Topic->photos->count() }}</span>
                            </span>
                        @endif
                    </div>
                </a>
            @else
                {{--one photo--}}
                <a href="{{ $topic_link_url }}">
                    <div class="image-container position-relative">
                        @if($Topic->photo_file !="")
                            <img class="card-img-top" loading="lazy"  width="100%" height="100%"
                                 src="{{ URL::to('uploads/topics/'.$Topic->photo_file) }}"
                                 alt="{{ $title }}"/>
                        @endif
                    </div>
                </a>
            @endif
            <div class="mt-3">
                <a href="{{ $topic_link_url }}">
                    <h3 class="card-title">
                        @if($Topic->icon !="")
                            <i class="fa {!! $Topic->icon !!} "></i>&nbsp;
                        @endif
                        {{ $title }}
                    </h3>
                </a>
                {{--Additional Feilds--}}
                @include("frontEnd.topic.fields",["cols"=>12,"Fields"=>@$Topic->webmasterSection->customFields->where("in_listing",true)])

                @if(strip_tags($Topic->$details) !="")
                    <p class="card-text mb-0 mt-2">
                        {!! mb_substr(strip_tags($Topic->$details),0, 180)."..." !!} <a
                            href="{{ $topic_link_url }}" class="read-more-link">{{ __("frontend.moreDetails") }}</a>
                    </p>
                @endif
            </div>
        </div>
    </article>
@endif
