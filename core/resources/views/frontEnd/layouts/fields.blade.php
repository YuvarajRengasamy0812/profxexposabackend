@if(count($Topic->webmasterSection->customFields->where("in_listing",true)) >0)
    <div class="row">
        <div class="col-lg-12">
            <div class="col-lg-12">
                <?php
                $cf_title_var = "title_" . @Helper::currentLanguage()->code;
                $cf_title_var2 = "title_" . config('smartend.default_language');
                ?>
                @foreach($Topic->webmasterSection->customFields->where("in_listing",true) as $customField)
                    <?php
                    // check permission
                    $view_permission_groups = [];
                    if ($customField->view_permission_groups != "") {
                        $view_permission_groups = explode(",", $customField->view_permission_groups);
                    }
                    if (in_array(0, $view_permission_groups) || $customField->view_permission_groups == "") {
                    // have permission & continue
                    ?>
                    @if ($customField->in_listing)
                        <?php
                        if ($customField->$cf_title_var != "") {
                            $cf_title = $customField->$cf_title_var;
                        } else {
                            $cf_title = $customField->$cf_title_var2;
                        }


                        $cf_saved_val = "";
                        $cf_saved_val_array = array();
                        if (count($Topic->fields) > 0) {
                            foreach ($Topic->fields as $t_field) {
                                if ($t_field->field_id == $customField->id) {
                                    if ($customField->type == 7) {
                                        // if multi check
                                        $cf_saved_val_array = explode(", ", $t_field->field_value);
                                    } else {
                                        $cf_saved_val = $t_field->field_value;
                                    }
                                }
                            }
                        }

                        ?>

                        @if(($cf_saved_val!="" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == @Helper::currentLanguage()->code))
                            @if($customField->type ==12)
                                {{--Vimeo Video Link--}}
                            @elseif($customField->type ==11)
                                {{--Youtube Video Link--}}
                            @elseif($customField->type ==10)
                                {{--Video File--}}
                            @elseif($customField->type ==9)
                                {{--Attach File--}}
                            @elseif($customField->type ==8)
                                {{--Photo File--}}
                            @elseif($customField->type ==7)
                                {{--Multi Check--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        <?php
                                        $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                        $cf_details_var2 = "details_" . config('smartend.default_language');
                                        if ($customField->$cf_details_var != "") {
                                            $cf_details = $customField->$cf_details_var;
                                        } else {
                                            $cf_details = $customField->$cf_details_var2;
                                        }
                                        $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                        $line_num = 1;
                                        ?>
                                        @foreach ($cf_details_lines as $cf_details_line)
                                            @if (in_array($line_num,$cf_saved_val_array))
                                                <span
                                                    class="badge">
                                                            {!! $cf_details_line !!}
                                                        </span>
                                            @endif
                                            <?php
                                            $line_num++;
                                            ?>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($customField->type ==14)
                                {{--Checkbox--}}
                                <div class="row field-row">
                                    <div class="col-lg-12">
                                        {!! (($cf_saved_val == 1) ? "&check;" : "&bigotimes;"); !!} {!!  $cf_title !!} {!! "(".(($cf_saved_val == 1) ? __('backend.yes') : __('backend.no')).")" !!}
                                    </div>
                                </div>
                            @elseif($customField->type ==6 || $customField->type ==13)
                                {{--Select--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        <?php
                                        $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                        $cf_details_var2 = "details_" . config('smartend.default_language');
                                        if ($customField->$cf_details_var != "") {
                                            $cf_details = $customField->$cf_details_var;
                                        } else {
                                            $cf_details = $customField->$cf_details_var2;
                                        }
                                        $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                        $line_num = 1;
                                        ?>
                                        @foreach ($cf_details_lines as $cf_details_line)
                                            @if ($line_num == $cf_saved_val)
                                                {!! $cf_details_line !!}
                                            @endif
                                            <?php
                                            $line_num++;
                                            ?>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($customField->type ==5)
                                {{--Date & Time--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        {!! Helper::formatDate($cf_saved_val)." ".date("h:i A", strtotime($cf_saved_val)) !!}
                                    </div>
                                </div>
                            @elseif($customField->type ==4)
                                {{--Date--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        {!! Helper::formatDate($cf_saved_val) !!}
                                    </div>
                                </div>
                            @elseif($customField->type ==3)
                                {{--Email Address--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        {!! $cf_saved_val !!}
                                    </div>
                                </div>
                            @elseif($customField->type ==2)
                                {{--Number--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        {!! $cf_saved_val !!}
                                    </div>
                                </div>
                            @elseif($customField->type ==1)
                                {{--Text Area--}}
                            @else
                                {{--Text Box--}}
                                <div class="row field-row">
                                    <div class="col-lg-3">
                                        {!!  $cf_title !!} :
                                    </div>
                                    <div class="col-lg-9">
                                        {!! Helper::ParseLinks($cf_saved_val) !!}
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endif
                    <?php
                    }
                    ?>
                @endforeach
            </div>
        </div>
    </div>
@endif
