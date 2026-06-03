<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Imports\ReadExcelFile;
use App\Imports\TopicsImport;
use App\Mail\NotificationEmail;
use App\Models\AttachFile;
use App\Models\Comment;
use App\Http\Requests;
use App\Models\Map;
use App\Models\Photo;
use App\Models\RelatedTopic;
use App\Models\Section;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\TopicCategory;
use App\Models\TopicField;
use App\Models\TopicTag;
use App\Models\User;
use App\Models\WebmasterSection;
use App\Models\WebmasterSectionField;
use Auth;
use File;
use Helper;
use http\Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Redirect;
use URL;
use Mail;
use Excel;
use Validator;

class TopicsController extends Controller
{
    private string $uploadPath = "uploads/topics/";
    private string $allowed_file_types = "png,gif,jpg,jpeg,svg,webp,psd,pdf,doc,docx,txt,rtf,xls,xlsx,ppt,pptx,mp3,mp4,wav,zip,rar";

    public function __construct()
    {
        $this->middleware('auth');

    }

    public function index($webmasterId)
    {
        // Check Permissions
        $data_sections_arr = explode(",", Auth::user()->permissionsGroup->data_sections);
        if (!in_array($webmasterId, $data_sections_arr)) {
            return redirect()->route('NoPermission');
        }
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //Webmaster Topic Details
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            $statics = [];
            foreach ($WebmasterSection->customFields as $customField) {
                if ($customField->in_statics && ($customField->type == 6 || $customField->type == 7)) {
                    $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                    $cf_details_var2 = "details_" . config('smartend.default_language');
                    if ($customField->$cf_details_var != "") {
                        $cf_details = $customField->$cf_details_var;
                    } else {
                        $cf_details = $customField->$cf_details_var2;
                    }
                    $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                    $line_num = 1;
                    $statics_row = [];
                    foreach ($cf_details_lines as $cf_details_line) {
                        if ($customField->type == 6) {
                            $tids = TopicField::select("topic_id")->where("field_id", $customField->id)->where("field_value", $line_num);
                        } else {
                            $tids = TopicField::select("topic_id")->where("field_id", $customField->id)->where("field_value", 'like', '%' . $line_num . '%');
                        }
                        $Topics_count = Topic::where('webmaster_id', '=', $WebmasterSection->id)->wherein('id', $tids)->count();
                        $statics_row[$line_num] = $Topics_count;
                        $line_num++;
                    }
                    $statics[$customField->id] = $statics_row;
                }
            }

            $fatherSections = Section::where('webmaster_id', '=', $WebmasterSection->id)->where('father_id', '=',
                '0')->orderby('row_no', 'asc')->get();
            $UsersList = User::all();
            return view("dashboard.topics.list", compact("GeneralWebmasterSections", "WebmasterSection", "statics", "UsersList", "fatherSections"));
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function list(Request $request)
    {

        $title_var = "title_" . @Helper::currentLanguage()->code;
        $title_var2 = "title_" . config('smartend.default_language');

        $limit = $request->input('length');
        $start = $request->input('start');
        $dir = $request->input('order.0.dir');

        \Cookie::queue("user_documents_page_order", 3, 31104000);

        //search inputs
        $folder_id = $request->input('folder_id');
        \Session()->put('current_admin_temp_folder_id', $folder_id);

        $webmasterId = $request->input('webmaster_id');
        $q = $request->input('find_q');
        $find_date = $request->input('find_date');
        $section_id = $request->input('section_id');
        $created_by = $request->input('created_by');

        if (@Auth::user()->permissionsGroup->view_status) {
            $Topics = Topic::where('created_by', '=', Auth::user()->id)->where('webmaster_id', '=', $webmasterId);
        } else {
            $Topics = Topic::where('webmaster_id', '=', $webmasterId);
        }

        if ($q != "") {
            $tids = TopicField::select("topic_id")->where("field_value", 'like', '%' . $q . '%');
            $Topics = $Topics->where(function ($query) use ($q, $tids) {
                $query->where('title_' . Helper::currentLanguage()->code, 'like', '%' . $q . '%')
                    ->orwhere('seo_title_' . Helper::currentLanguage()->code, 'like', '%' . $q . '%')
                    ->orwhere('details_' . Helper::currentLanguage()->code, 'like', '%' . $q . '%')
                    ->orwherein('id', $tids);
            });
        }
        if ($find_date != "") {
            $Topics = $Topics->where("date", Helper::dateForDB($find_date));
        }
        if ($section_id > 0) {
            $category_topics = TopicCategory::where('section_id', $section_id)->pluck("topic_id")->toarray();
            $Topics = $Topics->whereIn('id', $category_topics);
        }
        if ($created_by > 0) {
            $Topics = $Topics->where("created_by", $created_by);
        }

        $WebmasterSection = WebmasterSection::find($webmasterId);

        $order = "id";
        if (!empty($WebmasterSection)) {
            $Cols = Helper::get_webmaster_columns($WebmasterSection);

            $columns = [];
            if (@Auth::user()->permissionsGroup->edit_status) {
                $columns[] = "id";
            }
            foreach ($Cols as $KEY => $COL) {
                if ($KEY == "col_id") {
                    $columns[] = "id";
                } elseif ($KEY == "col_title") {
                    $columns[] = $title_var;
                } elseif ($KEY == "col_date") {
                    $columns[] = "date";
                } elseif ($KEY == "col_expire_date") {
                    $columns[] = "expire_date";
                } elseif ($KEY == "col_visits") {
                    $columns[] = "visits";
                } elseif ($KEY == "col_status") {
                    $columns[] = "status";
                } elseif ($KEY == "col_created_by") {
                    $columns[] = "created_by";
                } elseif ($KEY == "col_created_at") {
                    $columns[] = "created_at";
                } elseif ($KEY == "col_updated_by") {
                    $columns[] = "updated_by";
                } elseif ($KEY == "col_updated_at") {
                    $columns[] = "updated_at";
                } else {
                    $columns[] = "id";
                }
            }

            $order = $columns[$request->input('order.0.column')];
            if ($order == "") {
                $order = "id";
            }
            foreach ($WebmasterSection->customFields->whereNotIn("type", [99]) as $customField) {
                if ($customField->in_search) {
                    $FField_D = $request->input('customField_' . $customField->id);
                    if ($FField_D != "") {
                        if ($customField->type == 5) {
                            $FField_D = Helper::dateForDB($FField_D, 1);
                        } elseif ($customField->type == 4) {
                            $FField_D = Helper::dateForDB($FField_D);
                        }

                        if ($customField->type == 7) {
                            $topics_ids = TopicField::select("topic_id")->where("field_id", $customField->id)->whereRaw("FIND_IN_SET(" . $FField_D . ",REPLACE(`field_value`, ' ', ''))");
                        } else if ($customField->type == 6 || $customField->type == 13) {
                            $topics_ids = TopicField::select("topic_id")->where("field_id", $customField->id)->where("field_value", $FField_D);
                        } else {
                            $topics_ids = TopicField::select("topic_id")->where("field_id", $customField->id)->where("field_value", 'like', '%' . $FField_D . '%');
                        }

                        $Topics = $Topics->wherein("id", $topics_ids);
                    }
                }
            }
        }

        $totalData = $Topics->count();
        $totalFiltered = $totalData;
        //order, paginate
        if ($limit > 0) {
            $Topics = $Topics->offset($start)->limit($limit);
        }
        $Topics = $Topics->orderBy($order, $dir)->orderBy("id", "desc")->get();

        $data = array();
        if ($totalFiltered > 0) {

            $topic_ids = $Topics->pluck("id");
            $WebmasterCustomFields = WebmasterSectionField::where("webmaster_id", $WebmasterSection->id)->whereNotIn("type", [99])->get();
            $TopicFields = TopicField::whereIn("topic_id", $topic_ids)->get();
            $TopicsCount = $Topics->count();
            $x = 0;
            foreach ($Topics as $Topic) {
                $x++;
                $ExtraFieldsData = [];
                foreach ($WebmasterCustomFields->whereNotIn("type", [99])->where("webmaster_id", @$Topic->webmaster_id) as $customField) {
                    // check permission
                    $view_permission_groups = [];
                    if ($customField->view_permission_groups != "") {
                        $view_permission_groups = explode(",", $customField->view_permission_groups);
                    }
                    if (in_array(Auth::user()->permissions_id, $view_permission_groups) || in_array(0, $view_permission_groups) || $customField->view_permission_groups == "") {
                        // have permission & continue

                        $cf_saved_val = "";
                        $cf_saved_val_array = array();
                        $TField = $TopicFields->where("topic_id", $Topic->id)->where("field_id", $customField->id)->first();
                        if (!empty($TField)) {
                            if ($customField->type == 7) {
                                // if multi check
                                $cf_saved_val_array = explode(", ", $TField->field_value);
                            } else {
                                $cf_saved_val = $TField->field_value;
                            }
                        }

                        $cf_data = "";
                        if (($customField->lang_code == "all" || $customField->lang_code == @Helper::currentLanguage()->code)) {
                            if ($customField->type == 12) {
                                if (trim($cf_saved_val) != "") {
                                    $CF_Vimeo_id = Helper::Get_vimeo_video_id($cf_saved_val);
                                    $cf_data = "<a target='_blank' href='https://player.vimeo.com/video/$CF_Vimeo_id?title=0&amp;byline=0'><i class='fa fa-play'></i></a>";
                                }

                            } elseif ($customField->type == 11) {
                                if (trim($cf_saved_val) != "") {
                                    $CF_Youtube_id = Helper::Get_youtube_video_id($cf_saved_val);
                                    $cf_data = "<a target='_blank' href='https://www.youtube.com/embed/$CF_Youtube_id'><i class='fa fa-play'></i></a>";
                                }
                            } elseif ($customField->type == 10) {
                                if (trim($cf_saved_val) != "") {
                                    $cf_data = "<a target='_blank' href='" . URL::to('uploads/topics/' . $cf_saved_val) . "'><i class='fa fa-play'></i></a>";
                                }
                            } elseif ($customField->type == 9) {
                                if (trim($cf_saved_val) != "") {
                                    $cf_data = "<a target='_blank' href='" . URL::to('uploads/topics/' . $cf_saved_val) . "'><i class='fa fa-paperclip'></i></a>";
                                }
                            } elseif ($customField->type == 8) {
                                if (trim($cf_saved_val) != "") {
                                    $cf_data = "<a target='_blank' href='" . URL::to('uploads/topics/' . $cf_saved_val) . "'><i class='fa fa-picture-o'></i></a>";
                                }
                            } elseif ($customField->type == 7) {
                                $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                $cf_details_var2 = "details_" . config('smartend.default_language');
                                if ($customField->$cf_details_var != "") {
                                    $cf_details = $customField->$cf_details_var;
                                } else {
                                    $cf_details = $customField->$cf_details_var2;
                                }
                                $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                $line_num = 1;
                                foreach ($cf_details_lines as $cf_details_line) {
                                    if (in_array($line_num, $cf_saved_val_array)) {
                                        $cf_data .= "<span class=\"label text-sm\">" . $cf_details_line . "</span> ";
                                    }
                                    $line_num++;
                                }
                            } elseif ($customField->type == 14) {
                                $cf_data = "<div class='text-center'> <i class=\"fa " . (($cf_saved_val == 1) ? "fa-check text-success" : "fa-times text-danger") . " inline\"></i> " . (($cf_saved_val == 1) ? __('backend.yes') : __('backend.no')) . "</div>";
                            } elseif ($customField->type == 6 || $customField->type == 13) {
                                $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                $cf_details_var2 = "details_" . config('smartend.default_language');
                                if ($customField->$cf_details_var != "") {
                                    $cf_details = $customField->$cf_details_var;
                                } else {
                                    $cf_details = $customField->$cf_details_var2;
                                }
                                $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                $line_num = 1;
                                foreach ($cf_details_lines as $cf_details_line) {
                                    if ($line_num == $cf_saved_val) {
                                        $cf_data .= "<span class=\"label text-sm\">" . $cf_details_line . "</span> ";
                                    }
                                    $line_num++;
                                }
                            } elseif ($customField->type == 5) {
                                $cf_data = Helper::dateForDB($cf_saved_val, 1);
                            } elseif ($customField->type == 4) {
                                $cf_data = Helper::dateForDB($cf_saved_val);
                            } else {
                                $cf_data = $cf_saved_val;
                            }
                            $ExtraFieldsData["col_custom_" . $customField->id] = $cf_data;
                            $ExtraFieldsData["class_col_custom_" . $customField->id] = $customField->css_class;
                        }
                    }
                }
                foreach ($Cols as $KEY => $COL) {
                    $CLS = "text-center";
                    $Cell_Value = "";
                    if ($KEY == "col_id") {
                        $Cell_Value = $Topic->id;
                    } elseif ($KEY == "col_title") {
                        $CLS = '';
                        if ($Topic->$title_var != "") {
                            $title = $Topic->$title_var;
                        } else {
                            $title = $Topic->$title_var2;
                        }

                        // Get Categories list
                        $section = "";
                        if ($WebmasterSection->sections_status != 0) {
                            foreach ($Topic->categories as $category) {
                                try {
                                    if (@$category->section->$title_var != "") {
                                        $cat_title = @$category->section->$title_var;
                                    } else {
                                        $cat_title = @$category->section->$title_var2;
                                    }
                                    if ($cat_title != "") {
                                        $section .= "<span class='label dker b-a text-sm m-t-sm'>" . $cat_title . "</span> ";
                                    }

                                } catch (\Exception $e) {

                                }

                            }
                            if ($section == "") {
                                $section = "<span style='color: orangered'><i>" . __('backend.topicDeletedSection') . "</i></span>";
                            }
                        }

                        //comments
                        $comments = "";
                        if (count($Topic->newComments) > 0) {
                            $comments = "<div class='m-t-xs'><a href='" . route('topicsComments', [$WebmasterSection->id, $Topic->id]) . "'><span style='color:red'>" . __('backend.comments') . " <span class='label rounded label-sm danger'>" . count($Topic->newComments) . "</span></span></a></div>";
                        }


                        $photo = "";
                        if ($Topic->photo_file != "") {
                            $photo = " <div class=\"pull-right\"><img src=\"" . asset('uploads/topics/' . $Topic->photo_file) . "\" style=\"height: 40px\" alt=\"" . $title . "\"></div>";
                        }

                        $icon = "";
                        if ($Topic->icon != "") {
                            $icon = "<i class=\"fa " . $Topic->icon . "\"></i> ";
                        }
                        if ($WebmasterSection->title_status) {
                            if (@Auth::user()->permissionsGroup->edit_status) {
                                $Cell_Value = "<a href='" . route("topicsEdit", ["webmasterId" => @$Topic->webmasterSection->id, "id" => $Topic->id]) . "'>" . $photo . "<div class='h6 m-b-0'>" . $icon . $title . "</div>" . $section . $comments . "</a>";
                            } else {
                                $Cell_Value = $photo . "<div class='h6'>" . $icon . $title . "</div>" . $section . $comments;
                            }
                        }
                    } elseif ($KEY == "col_date") {
                        $Cell_Value = Helper::formatDate($Topic->date);
                    } elseif ($KEY == "col_expire_date") {
                        $Cell_Value = "<div " . (($Topic->expire_date < date("Y-m-d")) ? "style='color:red'" : "") . ">" . Helper::formatDate($Topic->expire_date) . "</div>";
                    } elseif ($KEY == "col_visits") {
                        $Cell_Value = $Topic->visits;
                    } elseif ($KEY == "col_status") {
                        $Cell_Value = "<i class=\"fa " . (($Topic->status == 1) ? "fa-check text-success" : "fa-times text-danger") . " inline\"></i>";
                    } elseif ($KEY == "col_created_by") {
                        $Cell_Value = @$Topic->user->name;
                    } elseif ($KEY == "col_created_at") {
                        $Cell_Value = Helper::formatDate($Topic->created_at) . " " . date("h:i A", strtotime($Topic->created_at));
                    } elseif ($KEY == "col_updated_by") {
                        $Cell_Value = @$Topic->updated_user->name;
                        $Cell_Value = @$Topic->user->name;
                    } elseif ($KEY == "col_updated_at") {
                        $Cell_Value = Helper::formatDate($Topic->updated_at) . " " . date("h:i A", strtotime($Topic->updated_at));
                    } else {
                        $Cell_Value = @$ExtraFieldsData[$KEY];
                        $Cell_Class = @$ExtraFieldsData["class_" . $KEY];
                        $nestedData['class_' . $KEY] = $Cell_Class;
                    }
                    $nestedData[$KEY] = "<div class=\"" . $CLS . "\">" . $Cell_Value . "</div>";
                }
                $nestedData['check'] = "<div class='row_checker'><label class=\"ui-check m-a-0\">
                                                <input type=\"checkbox\" name=\"ids[]\" value=\"" . $Topic->id . "\"><i
                                                        class=\"dark-white\"></i>
                                                        <input type='hidden' name='row_ids[]' value='" . $Topic->id . "' class='form-control row_no'>
                                            </label>
                                        </div>";

                $options = '
                      <div class="dropdown ' . ((($x + 2) >= $TopicsCount) ? "dropup" : "") . '">
        <button type="button" class="btn btn-sm light dk dropdown-toggle" data-toggle="dropdown"><i class="material-icons">&#xe5d4;</i> ' . __('backend.options') . '</button>
        <div class="dropdown-menu pull-right">
          <a class="dropdown-item" href="' . ((@$Topic->webmasterSection->type == 4 || @$Topic->webmasterSection->type == 6 || @$Topic->webmasterSection->type == 7 || !@$Topic->status) ? route("topicView", ["webmasterId" => @$Topic->webmasterSection->id, "id" => $Topic->id]) : Helper::topicURL($Topic->id, '', $Topic)) . '" ' . ((@$Topic->webmasterSection->type == 4 || @$Topic->webmasterSection->type == 6 || @$Topic->webmasterSection->type == 7) ? "" : "target='_blank'") . '><i class="material-icons">&#xe8f4;</i> ' . __('backend.preview') . '</a>';
                if (@Auth::user()->permissionsGroup->edit_status) {
                    $options .= '<a class="dropdown-item" href="' . route("topicsEdit", ["webmasterId" => @$Topic->webmasterSection->id, "id" => $Topic->id]) . '"><i class="material-icons">&#xe3c9;</i> ' . __('backend.edit') . '</a>';
                }
                if (@Auth::user()->permissionsGroup->add_status) {
                    $options .= '<a class="dropdown-item" href="' . route("topicsClone", ["webmasterId" => @$Topic->webmasterSection->id, "id" => $Topic->id]) . '"><i class="material-icons">&#xe14d;</i> ' . __('backend.clone') . '</a>';
                }
                if (@Auth::user()->permissionsGroup->delete_status) {
                    $options .= '<a class="dropdown-item text-danger" onclick="DeleteTopic(\'' . $Topic->id . '\')"><i class="material-icons">&#xe872;</i> ' . __('backend.delete') . '</a>';
                }
                $options .= '</div></div>';

                $nestedData['options'] = "<div class='text-center'>" . $options . "</div>";

                $data[] = $nestedData;
            }
        }
        $statics = [];
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
            "statics" => $statics
        );

        return json_encode($json_data);
    }

    public function print(Request $request, $webmasterId)
    {

        $title_var = "title_" . @Helper::currentLanguage()->code;
        $title_var2 = "title_" . config('smartend.default_language');

        \Cookie::queue("user_documents_page_order", 3, 31104000);

        //search inputs
        $folder_id = $request->input('folder_id');
        \Session()->put('current_admin_temp_folder_id', $folder_id);

        $stat = $request->stat;
        $q = $request->input('find_q');
        $find_date = $request->input('find_date');

        if (@Auth::user()->permissionsGroup->view_status) {
            $Topics = Topic::where('created_by', '=', Auth::user()->id)->where('webmaster_id', '=', $webmasterId);
        } else {
            $Topics = Topic::where('webmaster_id', '=', $webmasterId);
        }

        if ($q != "") {
            $tids = TopicField::select("topic_id")->where("field_value", 'like', '%' . $q . '%');
            $Topics = $Topics->where(function ($query) use ($q, $tids) {
                $query->where('title_' . Helper::currentLanguage()->code, 'like', '%' . $q . '%')
                    ->orwhere('seo_title_' . Helper::currentLanguage()->code, 'like', '%' . $q . '%')
                    ->orwhere('details_' . Helper::currentLanguage()->code, 'like', '%' . $q . '%')
                    ->orwherein('id', $tids);
            });
        }
        if ($find_date != "") {
            $Topics = $Topics->where("date", Helper::dateForDB($find_date));
        }

        $WebmasterSection = WebmasterSection::find($webmasterId);

        if (!empty($WebmasterSection)) {

            foreach ($WebmasterSection->customFields as $customField) {
                if ($customField->in_search) {
                    $FField_D = $request->input('customField_' . $customField->id);
                    if ($FField_D != "") {
                        if ($customField->type == 5) {
                            $FField_D = Helper::dateForDB($FField_D, 1);
                        } elseif ($customField->type == 4) {
                            $FField_D = Helper::dateForDB($FField_D);
                        }

                        if ($customField->type == 7) {
                            $topics_ids = TopicField::select("topic_id")->where("field_id", $customField->id)->whereRaw("FIND_IN_SET(" . $FField_D . ",REPLACE(`field_value`, ' ', ''))");
                        } else if ($customField->type == 6) {
                            $topics_ids = TopicField::select("topic_id")->where("field_id", $customField->id)->where("field_value", $FField_D);
                        } else {
                            $topics_ids = TopicField::select("topic_id")->where("field_id", $customField->id)->where("field_value", 'like', '%' . $FField_D . '%');
                        }

                        $Topics = $Topics->wherein("id", $topics_ids);
                    }
                }
            }
            $Topics = $Topics->orderBy("id", "desc")->get();

            return view("dashboard.topics.print", compact("Topics", "WebmasterSection", "stat"));
        }

        return "Error";
    }

    public function create($webmasterId)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->add_status) {
            return redirect()->route('NoPermission');
        }
        //
        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        //Webmaster Topic Details
        $WebmasterSection = WebmasterSection::find($webmasterId);

        $TagsList = Tag::where("status", 1)->get();
        if (!empty($WebmasterSection)) {
            $fatherSections = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=',
                '0')->orderby('row_no', 'asc')->get();

            $allowed_file_types = $this->allowed_file_types;
            return view("dashboard.topics.create",
                compact("GeneralWebmasterSections", "WebmasterSection", "fatherSections", "TagsList", "allowed_file_types"));
        }
        return redirect()->route('NotFound');
    }

    public function store(Request $request, $webmasterId)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //

            // validate required  custom fields
            $validate_inputs = [
                'photo_file' => 'image',
                'audio_file' => 'mimes:mpga,wav,mp3',
                'video_file' => 'mimes:mp4,ogv,webm',
                'attach_file' => 'mimes:' . $this->allowed_file_types
            ];

            // validate required  custom fields
            $CustomFields = $WebmasterSection->customFields;
            if (count($CustomFields) > 0) {
                foreach ($CustomFields->whereNotIn("type", [99])->where("required", 1) as $customField) {
                    // check permission
                    $add_permission_groups = [];
                    if ($customField->add_permission_groups != "") {
                        $add_permission_groups = explode(",", $customField->add_permission_groups);
                    }
                    if (in_array(Auth::user()->permissions_id, $add_permission_groups) || in_array(0, $add_permission_groups) || $customField->add_permission_groups == "") {
                        $validate_inputs["customField_" . $customField->id] = 'required';
                    }
                }
            }

            if (count($validate_inputs) > 0) {
                $this->validate($request, $validate_inputs);
            }

            $next_nor_no = Topic::where('webmaster_id', '=', $webmasterId)->max('row_no');
            if ($next_nor_no < 1) {
                $next_nor_no = 1;
            } else {
                $next_nor_no++;
            }

            // Start of Upload Files
            $formFileName = "photo_file";
            $fileFinalName = "";
            if ($request->$formFileName != "") {
                $fileFinalName = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $fileFinalName);

                // resize & optimize
                Helper::imageResize($path . $fileFinalName);
                Helper::imageOptimize($path . $fileFinalName);
            }

            $formFileName = "audio_file";
            $audioFileFinalName = "";
            if ($request->$formFileName != "") {
                $audioFileFinalName = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $audioFileFinalName);
            }

            $formFileName = "attach_file";
            $attachFileFinalName = "";
            if ($request->$formFileName != "") {
                $attachFileFinalName = time() . rand(1111, 9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $attachFileFinalName);

                // resize & optimize
                Helper::imageResize($path . $attachFileFinalName);
                Helper::imageOptimize($path . $attachFileFinalName);
            }

            if ($request->video_type == 3) {
                $videoFileFinalName = $request->embed_link;
            } elseif ($request->video_type == 2) {
                $videoFileFinalName = $request->vimeo_link;
            } elseif ($request->video_type == 1) {
                $videoFileFinalName = $request->youtube_link;
            } else {
                $formFileName = "video_file";
                $videoFileFinalName = "";
                if ($request->$formFileName != "") {
                    $videoFileFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $request->file($formFileName)->move($path, $videoFileFinalName);
                }

            }
            // End of Upload Files


            // create new topic
            $Topic = new Topic;

            // Save topic details
            $Topic->row_no = $next_nor_no;
            foreach (Helper::languagesList() as $ActiveLanguage) {
                if ($ActiveLanguage->box_status) {
                    $Topic->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                    $Topic->{"details_" . $ActiveLanguage->code} = $request->{"details_" . $ActiveLanguage->code};

                    // meta info
                    $Topic->{"seo_title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                    $Topic->{"seo_description_" . $ActiveLanguage->code} = mb_substr(strip_tags(stripslashes($request->{"details_" . $ActiveLanguage->code})), 0, 165, 'UTF-8');
                    $Topic->{"seo_url_slug_" . $ActiveLanguage->code} = Helper::URLSlug(strip_tags($request->{"title_" . $ActiveLanguage->code}), "topic", 0);

                }
            }
            $Topic->date = Helper::dateForDB($request->date);
            if (@$request->expire_date != "") {
                $Topic->expire_date = Helper::dateForDB($request->expire_date);
            }
            if ($fileFinalName != "") {
                $Topic->photo_file = $fileFinalName;
            }
            if ($audioFileFinalName != "") {
                $Topic->audio_file = $audioFileFinalName;
            }
            if ($attachFileFinalName != "") {
                $Topic->attach_file = $attachFileFinalName;
            }
            if ($videoFileFinalName != "") {
                $Topic->video_file = $videoFileFinalName;
            }
            $Topic->icon = $request->icon;
            $Topic->video_type = $request->video_type;
            $Topic->webmaster_id = $webmasterId;
            $Topic->created_by = Auth::user()->id;
            $Topic->visits = 0;
            $Topic->section_id = 0;
            $Topic->form_id = $request->page_form_id;
            $Topic->popup_id = $request->popup_id;
            if (@Auth::user()->permissionsGroup->active_status) {
                if ($WebmasterSection->case_status) {
                    $Topic->status = ($request->status) ? 1 : 0;
                } else {
                    $Topic->status = 1;
                }
            } else {
                $Topic->status = 0;
            }
            $Topic->save();

            if ($request->section_id != "" && $request->section_id != 0) {
                // Save categories
                foreach ($request->section_id as $category) {
                    if ($category > 0) {
                        $TopicCategory = new TopicCategory;
                        $TopicCategory->topic_id = $Topic->id;
                        $TopicCategory->section_id = $category;
                        $TopicCategory->save();
                    }
                }
            }

            if ($WebmasterSection->tags_status) {
                if ($request->tags != "") {
                    $tags = @explode(",", $request->tags);
                    foreach ($tags as $tag_item) {
                        if ($tag_item != "") {
                            $Tag = Tag::where("title", $tag_item)->first();
                            if (empty($Tag)) {
                                $Tag = new Tag;
                                $Tag->title = $tag_item;
                                $Tag->seo_url = Helper::URLSlug($tag_item);
                                $Tag->status = 1;
                                $Tag->visits = 0;
                                $Tag->created_by = Auth::user()->id;
                                $Tag->save();
                            }
                            $TopicTag = new TopicTag;
                            $TopicTag->topic_id = $Topic->id;
                            $TopicTag->tag_id = $Tag->id;
                            $TopicTag->save();
                        }
                    }
                }
            }

            // Save additional Fields
            if (count($CustomFields) > 0) {
                foreach ($CustomFields as $customField) {
                    // check permission
                    $add_permission_groups = [];
                    if ($customField->add_permission_groups != "") {
                        $add_permission_groups = explode(",", $customField->add_permission_groups);
                    }
                    if (in_array(Auth::user()->permissions_id, $add_permission_groups) || in_array(0, $add_permission_groups) || $customField->add_permission_groups == "") {
                        // have permission & continue

                        $field_value_var = "customField_" . $customField->id;

                        if ($request->$field_value_var != "") {
                            if ($customField->type == 8 || $customField->type == 9 || $customField->type == 10) {
                                // upload file
                                if ($request->$field_value_var != "") {
                                    $uploadedFileFinalName = time() . rand(1111,
                                            9999) . '.' . $request->file($field_value_var)->getClientOriginalExtension();
                                    $path = $this->uploadPath;
                                    $request->file($field_value_var)->move($path, $uploadedFileFinalName);
                                    $field_value = $uploadedFileFinalName;
                                }
                            } elseif ($customField->type == 15) {
                                $field_value = @str_replace("+", "", $request->{$field_value_var . "_phone_full"});
                            } elseif ($customField->type == 14) {
                                $field_value = ($request->$field_value_var == 1) ? 1 : 0;
                            } elseif ($customField->type == 5) {
                                if ($request->$field_value_var != "") {
                                    $field_value = Helper::dateForDB($request->$field_value_var, 1);
                                }
                            } elseif ($customField->type == 4) {
                                if ($request->$field_value_var != "") {
                                    $field_value = Helper::dateForDB($request->$field_value_var);
                                }
                            } elseif ($customField->type == 7) {
                                // if multi check
                                $field_value = implode(", ", $request->$field_value_var);
                            } else {
                                $field_value = strip_tags($request->$field_value_var);
                            }
                            $TopicField = new TopicField;
                            $TopicField->topic_id = $Topic->id;
                            $TopicField->field_id = $customField->id;
                            $TopicField->field_value = $field_value;
                            $TopicField->save();
                        }
                    }
                }
            }

            // SEND Notification Email
            $this->send_notification($WebmasterSection, $Topic, "New");

            if (@Auth::user()->permissionsGroup->edit_status) {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $Topic->id])->with('doneMessage',
                    __('backend.addDone'));
            } else {
                return redirect()->action('Dashboard\TopicsController@index')->with('doneMessage',
                    __('backend.addDone'));
            }

        }
        return redirect()->route('NotFound');
    }

    public function edit($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->edit_status) {
                return redirect()->route('NoPermission');
            }
            //
            // General for all pages
            $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
            // General END

            if (@Auth::user()->permissionsGroup->view_status) {
                $Topic = Topic::where('created_by', '=', Auth::user()->id)->find($id);
            } else {
                $Topic = Topic::find($id);
            }
            if (!empty($Topic)) {
                //Topic Topics Details
                $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);

                $fatherSections = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=', '0')->orderby('row_no', 'asc')->get();

                $TagsList = Tag::where("status", 1)->get();

                $allowed_file_types = $this->allowed_file_types;

                return view("dashboard.topics.edit",
                    compact("Topic", "GeneralWebmasterSections", "WebmasterSection", "fatherSections", "TagsList", "allowed_file_types"));
            } else {
                return redirect()->action('Dashboard\TopicsController@index', $webmasterId);
            }
        }
        return redirect()->route('NotFound');
    }

    public function update(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $Topic = Topic::find($id);
            if (!empty($Topic)) {


                // validate required  custom fields
                $validate_inputs = [
                    'photo_file' => 'image',
                    'audio_file' => 'mimes:mpga,wav,mp3',
                    'video_file' => 'mimes:mp4,ogv,webm',
                    'attach_file' => 'mimes:' . $this->allowed_file_types
                ];

                // validate required  custom fields
                $CustomFields = $WebmasterSection->customFields;
                if (count($CustomFields) > 0) {
                    foreach ($CustomFields->whereNotIn("type", [99])->where("required", 1) as $customField) {
                        // check permission
                        $edit_permission_groups = [];
                        if ($customField->edit_permission_groups != "") {
                            $edit_permission_groups = explode(",", $customField->edit_permission_groups);
                        }
                        if (in_array(Auth::user()->permissions_id, $edit_permission_groups) || in_array(0, $edit_permission_groups) || $customField->edit_permission_groups == "") {
                            $validate_inputs["customField_" . $customField->id] = 'required';
                        }
                    }
                }

                if (count($validate_inputs) > 0) {
                    $this->validate($request, $validate_inputs);
                }

                // Start of Upload Files
                $formFileName = "photo_file";
                $fileFinalName = "";
                if ($request->$formFileName != "") {
                    // Delete a Topic photo
                    if ($Topic->$formFileName != "" && $Topic->$formFileName != "default.png") {
                        File::delete($this->uploadPath . $Topic->$formFileName);
                    }

                    $fileFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $request->file($formFileName)->move($path, $fileFinalName);

                    // resize & optimize
                    Helper::imageResize($path . $fileFinalName);
                    Helper::imageOptimize($path . $fileFinalName);
                }


                $formFileName = "audio_file";
                $audioFileFinalName = "";
                if ($request->$formFileName != "") {
                    // Delete file if there is a new one
                    if ($Topic->$formFileName != "" && $Topic->$formFileName != "default.png") {
                        File::delete($this->uploadPath . $Topic->$formFileName);
                    }

                    $audioFileFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $request->file($formFileName)->move($path, $audioFileFinalName);
                }

                $formFileName = "attach_file";
                $attachFileFinalName = "";
                if ($request->$formFileName != "") {
                    // Delete file if there is a new one
                    if ($Topic->$formFileName != "" && $Topic->$formFileName != "default.png") {
                        File::delete($this->uploadPath . $Topic->$formFileName);
                    }
                    $attachFileFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $request->file($formFileName)->move($path, $attachFileFinalName);

                    // resize & optimize
                    Helper::imageResize($path . $attachFileFinalName);
                    Helper::imageOptimize($path . $attachFileFinalName);
                }

                if ($request->video_type == 3) {
                    $videoFileFinalName = $request->embed_link;
                } elseif ($request->video_type == 2) {
                    $videoFileFinalName = $request->vimeo_link;
                } elseif ($request->video_type == 1) {
                    $videoFileFinalName = $request->youtube_link;
                } else {
                    $formFileName = "video_file";
                    $videoFileFinalName = "";
                    if ($request->$formFileName != "") {
                        // Delete file if there is a new one
                        if ($Topic->$formFileName != "" && $Topic->$formFileName != "default.png") {
                            File::delete($this->uploadPath . $Topic->$formFileName);
                        }
                        $videoFileFinalName = time() . rand(1111,
                                9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                        $path = $this->uploadPath;
                        $request->file($formFileName)->move($path, $videoFileFinalName);
                    }

                }
                // End of Upload Files
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $Topic->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                        $Topic->{"details_" . $ActiveLanguage->code} = $request->{"details_" . $ActiveLanguage->code};
                    }
                }
                $Topic->date = Helper::dateForDB($request->date);
                if (@$request->expire_date != "") {
                    $Topic->expire_date = Helper::dateForDB(@$request->expire_date);
                }

                if ($request->photo_delete == 1) {
                    // Delete photo_file
                    if ($Topic->photo_file != "" && $Topic->photo_file != "default.png") {
                        File::delete($this->uploadPath . $Topic->photo_file);
                    }

                    $Topic->photo_file = "";
                }

                if ($fileFinalName != "") {
                    $Topic->photo_file = $fileFinalName;
                }
                if ($audioFileFinalName != "") {
                    $Topic->audio_file = $audioFileFinalName;
                }

                if ($request->attach_delete == 1) {
                    // Delete attach_file
                    if ($Topic->attach_file != "") {
                        File::delete($this->uploadPath . $Topic->attach_file);
                    }

                    $Topic->attach_file = "";
                }

                if ($attachFileFinalName != "") {
                    $Topic->attach_file = $attachFileFinalName;
                }
                if ($videoFileFinalName != "") {
                    $Topic->video_file = $videoFileFinalName;
                }

                $Topic->icon = $request->icon;
                $Topic->video_type = $request->video_type;
                if ($WebmasterSection->case_status) {
                    $Topic->status = ($request->status) ? 1 : 0;
                }
                if (!@Auth::user()->permissionsGroup->active_status) {
                    $Topic->status = 0;
                }
                $Topic->form_id = $request->page_form_id;
                $Topic->popup_id = $request->popup_id;
                $Topic->updated_by = Auth::user()->id;
                $Topic->save();

                // Remove old categories
                TopicCategory::where('topic_id', $Topic->id)->delete();
                // Save new categories
                if ($request->section_id != "" && $request->section_id != 0) {
                    foreach ($request->section_id as $category) {
                        if ($category > 0) {
                            $TopicCategory = new TopicCategory;
                            $TopicCategory->topic_id = $Topic->id;
                            $TopicCategory->section_id = $category;
                            $TopicCategory->save();
                        }
                    }
                }

                TopicTag::where('topic_id', $Topic->id)->delete();
                if ($WebmasterSection->tags_status) {
                    if ($request->tags != "") {
                        $tags = @explode(",", $request->tags);
                        foreach ($tags as $tag_item) {
                            if ($tag_item != "") {
                                $Tag = Tag::where("title", $tag_item)->first();
                                if (empty($Tag)) {
                                    $Tag = new Tag;
                                    $Tag->title = $tag_item;
                                    $Tag->seo_url = Helper::URLSlug($tag_item);
                                    $Tag->status = 1;
                                    $Tag->visits = 0;
                                    $Tag->created_by = Auth::user()->id;
                                    $Tag->save();
                                }
                                $TopicTag = new TopicTag;
                                $TopicTag->topic_id = $Topic->id;
                                $TopicTag->tag_id = $Tag->id;
                                $TopicTag->save();
                            }
                        }
                    }
                }

                // Save additional Fields
                if (count($CustomFields) > 0) {
                    foreach ($CustomFields as $customField) {
                        // check permission
                        $edit_permission_groups = [];
                        if ($customField->edit_permission_groups != "") {
                            $edit_permission_groups = explode(",", $customField->edit_permission_groups);
                        }
                        if (in_array(Auth::user()->permissions_id, $edit_permission_groups) || in_array(0, $edit_permission_groups) || $customField->edit_permission_groups == "") {
                            // have permission & continue

                            // Remove old Fields Values
                            TopicField::where('topic_id', $Topic->id)->where('field_id', $customField->id)->delete();

                            $field_value = "";
                            $field_value_var = "customField_" . $customField->id;
                            $file_del_id = 'file_delete_' . $customField->id;
                            $file_old_id = 'file_old_' . $customField->id;

                            if ($customField->type == 8 || $customField->type == 9 || $customField->type == 10) {
                                // upload file
                                if ($request->$field_value_var != "") {
                                    $uploadedFileFinalName = time() . rand(1111,
                                            9999) . '.' . $request->file($field_value_var)->getClientOriginalExtension();
                                    $path = $this->uploadPath;
                                    $request->file($field_value_var)->move($path, $uploadedFileFinalName);
                                    $field_value = $uploadedFileFinalName;
                                } else {
                                    // if old file still
                                    $field_value = $request->$file_old_id;
                                }
                                if ($request->$file_del_id) {
                                    // if want to delete the file
                                    File::delete($this->uploadPath . $request->$file_old_id);
                                    $field_value = "";
                                }
                            } elseif ($customField->type == 14) {
                                $field_value = ($request->$field_value_var == 1) ? 1 : 0;
                            } elseif ($customField->type == 5) {
                                if ($request->$field_value_var != "") {
                                    $field_value = Helper::dateForDB($request->$field_value_var, 1);
                                }
                            } elseif ($customField->type == 4) {
                                if ($request->$field_value_var != "") {
                                    $field_value = Helper::dateForDB($request->$field_value_var);
                                }
                            } elseif ($customField->type == 7) {
                                // if multi check
                                if ($request->$field_value_var != "") {
                                    $field_value = implode(", ", $request->$field_value_var);
                                }
                            } else {
                                $field_value = strip_tags($request->$field_value_var);
                            }
                            if ($field_value != "") {
                                $TopicField = new TopicField;
                                $TopicField->topic_id = $Topic->id;
                                $TopicField->field_id = $customField->id;
                                $TopicField->field_value = $field_value;
                                $TopicField->save();
                            }
                        }
                    }
                }

                // SEND Notification Email
                $this->send_notification($WebmasterSection, $Topic, "Update");


                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'));
            } else {
                return redirect()->action('Dashboard\TopicsController@index', $webmasterId);
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function clone($webmasterId, $id)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->add_status) {
            return redirect()->route('NoPermission');
        }

        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            $Topic = Topic::find($id);
            if (!empty($Topic)) {
                $NewTopic = $Topic->replicate();

                $next_nor_no = Topic::where('webmaster_id', '=', $webmasterId)->max('row_no');
                if ($next_nor_no < 1) {
                    $next_nor_no = 1;
                } else {
                    $next_nor_no++;
                }
                $Topic->row_no = $next_nor_no;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $NewTopic->{"title_" . $ActiveLanguage->code} = $Topic->{"title_" . $ActiveLanguage->code} . " - Copy";
                        // meta info
                        if ($Topic->{"seo_title_" . $ActiveLanguage->code} != "") {
                            $NewTopic->{"seo_title_" . $ActiveLanguage->code} = $Topic->{"seo_title_" . $ActiveLanguage->code} . " - Copy";
                        }
                        $NewTopic->{"seo_url_slug_" . $ActiveLanguage->code} = Helper::URLSlug($Topic->{"title_" . $ActiveLanguage->code}, "topic", 0);

                    }
                }
                $path = $this->uploadPath;
                if ($Topic->photo_file != "") {
                    $new_file_name = "c" . $Topic->photo_file;
                    $copied = File::copy($path . $Topic->photo_file, $path . $new_file_name);
                    if ($copied) {
                        $NewTopic->photo_file = $new_file_name;
                    }
                }
                if ($Topic->audio_file != "") {
                    $new_file_name = "c" . $Topic->audio_file;
                    $copied = File::copy($path . $Topic->audio_file, $path . $new_file_name);
                    if ($copied) {
                        $NewTopic->audio_file = $new_file_name;
                    }
                }
                if ($Topic->attach_file != "") {
                    $new_file_name = "c" . $Topic->attach_file;
                    $copied = File::copy($path . $Topic->attach_file, $path . $new_file_name);
                    if ($copied) {
                        $NewTopic->attach_file = $new_file_name;
                    }
                }
                if ($Topic->video_file != "") {
                    $new_file_name = "c" . $Topic->video_file;
                    $copied = File::copy($path . $Topic->video_file, $path . $new_file_name);
                    if ($copied) {
                        $NewTopic->video_file = $new_file_name;
                    }
                }
                if (@Auth::user()->permissionsGroup->active_status) {
                    if ($WebmasterSection->case_status) {
                        $NewTopic->status = $Topic->status;
                    } else {
                        $NewTopic->status = 1;
                    }
                } else {
                    $NewTopic->status = 0;
                }
                $NewTopic->visits = 0;
                $NewTopic->created_by = Auth::user()->id;
                $NewTopic->updated_by = null;
                $NewTopic->save();

                // copy categories
                foreach ($Topic->categories as $category) {
                    if (@$category->section_id > 0) {
                        $TopicCategory = new TopicCategory;
                        $TopicCategory->topic_id = $NewTopic->id;
                        $TopicCategory->section_id = @$category->section_id;
                        $TopicCategory->save();
                    }
                }

                // copy extra fields
                foreach ($Topic->fields as $Field) {
                    $TopicField = new TopicField;
                    $TopicField->topic_id = $NewTopic->id;
                    $TopicField->field_id = $Field->field_id;
                    $TopicField->field_value = $Field->field_value;
                    $TopicField->save();
                }

                // copy photos
                foreach ($Topic->photos as $TPhoto) {
                    $new_file_name = "c" . $TPhoto->file;
                    $copied = File::copy($path . $TPhoto->file, $path . $new_file_name);
                    if ($copied) {
                        $next_nor_no = Photo::where('topic_id', '=', $NewTopic->id)->max('row_no');
                        if ($next_nor_no < 1) {
                            $next_nor_no = 1;
                        } else {
                            $next_nor_no++;
                        }

                        $Photo = new Photo;
                        $Photo->row_no = $next_nor_no;
                        $Photo->file = $new_file_name;
                        $Photo->title = $TPhoto->title;
                        $Photo->topic_id = $NewTopic->id;
                        $Photo->created_by = Auth::user()->id;
                        $Photo->save();
                    }
                }

                // copy attach files
                foreach ($Topic->attachFiles as $TFile) {
                    $new_file_name = "c" . $TFile->file;
                    $copied = File::copy($path . $TFile->file, $path . $new_file_name);
                    if ($copied) {
                        $next_nor_no = AttachFile::where('topic_id', '=', $NewTopic->id)->max('row_no');
                        if ($next_nor_no < 1) {
                            $next_nor_no = 1;
                        } else {
                            $next_nor_no++;
                        }

                        $AttachFile = new AttachFile;
                        $AttachFile->topic_id = $NewTopic->id;
                        $AttachFile->row_no = $next_nor_no;
                        foreach (Helper::languagesList() as $ActiveLanguage) {
                            if ($ActiveLanguage->box_status) {
                                $AttachFile->{"title_" . $ActiveLanguage->code} = $TFile->{"title_" . $ActiveLanguage->code};
                            }
                        }
                        $AttachFile->file = $new_file_name;
                        $AttachFile->created_by = Auth::user()->id;
                        $AttachFile->save();
                    }
                }

                // copy related topics
                foreach ($Topic->relatedTopics as $RTopic) {

                    $next_nor_no = RelatedTopic::where('topic_id', '=', $NewTopic->id)->max('row_no');
                    if ($next_nor_no < 1) {
                        $next_nor_no = 1;
                    } else {
                        $next_nor_no++;
                    }

                    $RelatedTopic = new RelatedTopic;
                    $RelatedTopic->topic_id = $NewTopic->id;
                    $RelatedTopic->topic2_id = $RTopic->topic2_id;
                    $RelatedTopic->row_no = $next_nor_no;
                    $RelatedTopic->created_by = Auth::user()->id;
                    $RelatedTopic->save();
                }

                // copy maps
                foreach ($Topic->maps as $TMap) {
                    $next_nor_no = Map::where('topic_id', '=', $NewTopic->id)->max('row_no');
                    if ($next_nor_no < 1) {
                        $next_nor_no = 1;
                    } else {
                        $next_nor_no++;
                    }

                    $Map = new Map;
                    $Map->row_no = $next_nor_no;
                    $Map->longitude = $TMap->longitude;
                    $Map->latitude = $TMap->latitude;
                    foreach (Helper::languagesList() as $ActiveLanguage) {
                        if ($ActiveLanguage->box_status) {
                            $Map->{"title_" . $ActiveLanguage->code} = $TMap->{"title_" . $ActiveLanguage->code};
                            $Map->{"details_" . $ActiveLanguage->code} = $TMap->{"details_" . $ActiveLanguage->code};
                        }
                    }
                    $Map->icon = $TMap->icon;
                    $Map->topic_id = $NewTopic->id;
                    $Map->status = 1;
                    $Map->created_by = Auth::user()->id;
                    $Map->save();
                }


                if (@Auth::user()->permissionsGroup->edit_status) {
                    return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $NewTopic->id])->with('doneMessage',
                        __('backend.addDone'));
                } else {
                    return redirect()->action('Dashboard\TopicsController@index', $webmasterId)->with('doneMessage',
                        __('backend.addDone'));
                }
            }
        }
        return redirect()->route('NotFound');
    }

    public function view($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {

            // General for all pages
            $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
            // General END

            if (@Auth::user()->permissionsGroup->view_status) {
                $Topic = Topic::where('created_by', '=', Auth::user()->id)->find($id);
            } else {
                $Topic = Topic::find($id);
            }
            if (!empty($Topic)) {
                $Topic->visits = $Topic->visits + 1;
                $Topic->save();

                //Topic Topics Details
                $WebmasterSection = WebmasterSection::find($Topic->webmaster_id);

                $fatherSections = Section::where('webmaster_id', '=', $webmasterId)->where('father_id', '=',
                    '0')->orderby('row_no', 'asc')->get();

                return view("dashboard.topics.view",
                    compact("Topic", "GeneralWebmasterSections", "WebmasterSection", "fatherSections"));
            } else {
                return redirect()->action('Dashboard\TopicsController@index', $webmasterId);
            }
        }
        return redirect()->route('NotFound');
    }

    public function destroy($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->delete_status) {
                return json_encode(array("stat" => "error", "id" => $id));
            }
            //
            if (@Auth::user()->permissionsGroup->view_status) {
                $Topic = Topic::where('created_by', '=', Auth::user()->id)->find($id);
            } else {
                $Topic = Topic::find($id);
            }
            if (!empty($Topic)) {
                // Delete a Topic photo
                if ($Topic->photo_file != "" && $Topic->photo_file != "default.png") {
                    File::delete($this->uploadPath . $Topic->photo_file);
                }
                if ($Topic->attach_file != "") {
                    File::delete($this->uploadPath . $Topic->attach_file);
                }
                if ($Topic->audio_file != "") {
                    File::delete($this->uploadPath . $Topic->audio_file);
                }
                if ($Topic->video_type == 0 && $Topic->video_file != "") {
                    File::delete($this->uploadPath . $Topic->video_file);
                }
                //delete topic tags
                TopicTag::where('topic_id', $Topic->id)->delete();
                //delete additional fields
                TopicField::where('topic_id', $Topic->id)->delete();
                //delete Related Topics
                RelatedTopic::where('topic_id', $Topic->id)->delete();
                RelatedTopic::where('topic2_id', $Topic->id)->delete();
                // Remove categories
                TopicCategory::where('topic_id', $Topic->id)->delete();
                // Remove comments
                Comment::where('topic_id', $Topic->id)->delete();
                // Remove maps
                Map::where('topic_id', $Topic->id)->delete();
                // Remove Photos
                $PhotoFiles = Photo::where('topic_id', $Topic->id)->get();
                if (count($PhotoFiles) > 0) {
                    foreach ($PhotoFiles as $PhotoFile) {
                        if ($PhotoFile->file != "" && $PhotoFile->file != "default.png") {
                            File::delete($this->uploadPath . $PhotoFile->file);
                        }
                    }
                }
                Photo::where('topic_id', $Topic->id)->delete();
                // Remove Attach Files
                $AttachFiles = AttachFile::where('topic_id', $Topic->id)->get();
                if (count($AttachFiles) > 0) {
                    foreach ($AttachFiles as $AttachFile) {
                        if ($AttachFile->file != "" && $AttachFile->file != "default.png") {
                            File::delete($this->uploadPath . $AttachFile->file);
                        }
                    }
                }
                AttachFile::where('topic_id', $Topic->id)->delete();

                //Remove Topic
                $Topic->delete();
                return json_encode(array("stat" => "success", "id" => $id));
            } else {
                return json_encode(array("stat" => "error", "id" => $id));
            }
        } else {
            return json_encode(array("stat" => "error", "id" => $id));
        }
    }

    public function updateAll(Request $request, $webmasterId)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            if ($request->action == "order") {
                foreach ($request->row_ids as $rowId) {
                    $Topic = Topic::find($rowId);
                    if (!empty($Topic)) {
                        $row_no_val = "row_no_" . $rowId;
                        $Topic->row_no = $request->$row_no_val;
                        $Topic->save();
                    }
                }

            } else {
                if ($request->ids != "") {
                    if ($request->action == "activate") {
                        Topic::wherein('id', $request->ids)
                            ->update(['status' => 1]);

                    } elseif ($request->action == "block") {
                        Topic::wherein('id', $request->ids)
                            ->update(['status' => 0]);

                    } elseif ($request->action == "delete") {
                        // Check Permissions
                        if (!@Auth::user()->permissionsGroup->delete_status) {
                            return redirect()->route('NoPermission');
                        }
                        // Delete Topics photo
                        $Topics = Topic::wherein('id', $request->ids)->get();
                        foreach ($Topics as $Topic) {
                            if ($Topic->photo_file != "" && $Topic->photo_file != "default.png") {
                                File::delete($this->uploadPath . $Topic->photo_file);
                            }
                            if ($Topic->attach_file != "") {
                                File::delete($this->uploadPath . $Topic->attach_file);
                            }
                            if ($Topic->audio_file != "") {
                                File::delete($this->uploadPath . $Topic->audio_file);
                            }
                            if ($Topic->video_type == 0 && $Topic->video_file != "") {
                                File::delete($this->uploadPath . $Topic->video_file);
                            }
                        }

                        // Delete photo files
                        $PhotoFiles = Photo::wherein('topic_id', $request->ids)->get();
                        foreach ($PhotoFiles as $PhotoFile) {
                            if ($PhotoFile->file != "" && $PhotoFile->file != "default.png") {
                                File::delete($this->uploadPath . $PhotoFile->file);
                            }
                        }

                        // Delete attach files
                        $AttachFile_Files = AttachFile::wherein('topic_id', $request->ids)->get();
                        foreach ($AttachFile_Files as $AttachFile_File) {
                            if ($AttachFile_File->file != "") {
                                File::delete($this->uploadPath . $AttachFile_File->file);
                            }
                        }

                        //delete topic tags
                        TopicTag::wherein('topic_id', $request->ids)->delete();
                        //delete additional fields
                        TopicField::wherein('topic_id', $request->ids)
                            ->delete();
                        //delete Related Topics
                        RelatedTopic::wherein('topic_id', $request->ids)
                            ->delete();
                        RelatedTopic::wherein('topic2_id', $request->ids)->delete();
                        // Remove categories
                        TopicCategory::wherein('topic_id', $request->ids)
                            ->delete();
                        // Remove Photos
                        Photo::wherein('topic_id', $request->ids)
                            ->delete();
                        // Remove Attach Files
                        AttachFile::wherein('topic_id', $request->ids)
                            ->delete();
                        // Remove Attach Maps
                        Map::wherein('topic_id', $request->ids)
                            ->delete();
                        // Remove Attach Comments
                        Comment::wherein('topic_id', $request->ids)
                            ->delete();

                        //Remove Topics
                        Topic::wherein('id', $request->ids)
                            ->delete();

                    }
                }
            }
            return redirect()->action('Dashboard\TopicsController@index', $webmasterId)->with('doneMessage',
                __('backend.saveDone'));
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function seo(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $Topic = Topic::find($id);
            if (!empty($Topic)) {
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $Topic->{"seo_title_" . $ActiveLanguage->code} = strip_tags($request->{"seo_title_" . $ActiveLanguage->code});
                        $Topic->{"seo_description_" . $ActiveLanguage->code} = strip_tags($request->{"seo_description_" . $ActiveLanguage->code});
                        $Topic->{"seo_keywords_" . $ActiveLanguage->code} = strip_tags($request->{"seo_keywords_" . $ActiveLanguage->code});
                        $Topic->{"seo_url_slug_" . $ActiveLanguage->code} = Helper::URLSlug(strip_tags($request->{"seo_url_slug_" . $ActiveLanguage->code}), "topic", $id);
                    }
                }
                $Topic->updated_by = Auth::user()->id;
                $Topic->save();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'))->with('activeTab', 'seo');
            } else {
                return redirect()->action('Dashboard\TopicsController@index', $webmasterId);
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function photos(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $this->validate($request, [
                'file' => 'image',
            ]);

            $next_nor_no = Photo::where('topic_id', '=', $id)->max('row_no');
            if ($next_nor_no < 1) {
                $next_nor_no = 1;
            } else {
                $next_nor_no++;
            }

            // Start of Upload Files
            $formFileName = "file";
            $fileFinalName = "";
            $fileFinalTitle = ""; // Original file name without extension
            if ($request->$formFileName != "") {
                $fileFinalTitle = basename($request->file($formFileName)->getClientOriginalName(),
                    '.' . $request->file($formFileName)->getClientOriginalExtension());
                $fileFinalName = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $fileFinalName);

                // resize & optimize
                Helper::imageResize($path . $fileFinalName);
                Helper::imageOptimize($path . $fileFinalName);
            }
            // End of Upload Files
            if ($fileFinalName != "") {
                $Photo = new Photo;
                $Photo->row_no = $next_nor_no;
                $Photo->file = $fileFinalName;
                $Photo->title = $fileFinalTitle;
                $Photo->topic_id = $id;
                $Photo->created_by = Auth::user()->id;
                $Photo->save();

                return response()->json('success', 200);
            } else {
                return response()->json('error', 400);
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function photosDestroy($webmasterId, $id, $photo_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->delete_status) {
                return redirect()->route('NoPermission');
            }
            //
            $Photo = Photo::find($photo_id);
            if (!empty($Photo)) {
                // Delete a Topic photo
                if ($Photo->file != "" && $Photo->file != "default.png") {
                    File::delete($this->uploadPath . $Photo->file);
                }


                $Photo->delete();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.deleteDone'))->with('activeTab', 'photos');
            } else {
                return redirect()->action('Dashboard\TopicsController@index', $webmasterId);
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function photosUpdateAll(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            if ($request->action == "order") {
                foreach ($request->row_ids as $rowId) {
                    $Photo = Photo::find($rowId);
                    if (!empty($Photo)) {
                        $row_no_val = "row_no_" . $rowId;
                        $Photo->row_no = $request->$row_no_val;
                        $Photo->save();
                    }
                }

            } else {
                if ($request->ids != "") {
                    if ($request->action == "delete") {
                        // Check Permissions
                        if (!@Auth::user()->permissionsGroup->delete_status) {
                            return redirect()->route('NoPermission');
                        }
                        // Delete Photos
                        $Photos = Photo::wherein('id', $request->ids)->get();
                        foreach ($Photos as $Photo) {
                            if ($Photo->file != "" && $Photo->file != "default.png") {
                                File::delete($this->uploadPath . $Photo->file);
                            }
                        }

                        Photo::wherein('id', $request->ids)
                            ->delete();

                    }
                }
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'photos');
        } else {
            return redirect()->route('NotFound');
        }
    }

    // Comments Functions

    public function topicsComments($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'comments');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function commentsCreate($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->add_status) {
                return redirect()->route('NoPermission');
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                'comments')->with('commentST', 'create');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function commentsStore(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required',
                'comment' => 'required'
            ]);


            $next_nor_no = Comment::where('topic_id', '=', $id)->max('row_no');
            if ($next_nor_no < 1) {
                $next_nor_no = 1;
            } else {
                $next_nor_no++;
            }

            $Comment = new Comment;
            $Comment->row_no = $next_nor_no;
            $Comment->name = strip_tags($request->name);
            $Comment->email = strip_tags($request->email);
            $Comment->comment = strip_tags($request->comment);
            $Comment->topic_id = $id;
            $Comment->date = date("Y-m-d H:i:s");
            $Comment->status = 1;
            $Comment->created_by = Auth::user()->id;
            $Comment->save();

            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'comments');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function commentsEdit($webmasterId, $id, $comment_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->edit_status) {
                return redirect()->route('NoPermission');
            }

            $Comment = Comment::find($comment_id);
            if (!empty($Comment)) {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                    'comments')->with('commentST', 'edit')->with('Comment', $Comment);
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'comments');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function commentsUpdate(Request $request, $webmasterId, $id, $comment_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $Comment = Comment::find($comment_id);
            if (!empty($Comment)) {


                $this->validate($request, [
                    'name' => 'required',
                    'email' => 'required',
                    'comment' => 'required'
                ]);
                $Comment->name = strip_tags($request->name);
                $Comment->email = strip_tags($request->email);
                $Comment->comment = strip_tags($request->comment);
                $Comment->status = $request->status;
                $Comment->updated_by = Auth::user()->id;
                $Comment->save();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'))->with('activeTab', 'comments');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'comments');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function commentsDestroy($webmasterId, $id, $comment_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->delete_status) {
                return redirect()->route('NoPermission');
            }
            //
            $Comment = Comment::find($comment_id);
            if (!empty($Comment)) {
                $Comment->delete();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.deleteDone'))->with('activeTab', 'comments');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'comments');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function commentsUpdateAll(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            if ($request->action == "order") {
                foreach ($request->row_ids as $rowId) {
                    $Comment = Comment::find($rowId);
                    if (!empty($Comment)) {
                        $row_no_val = "row_no_" . $rowId;
                        $Comment->row_no = $request->$row_no_val;
                        $Comment->save();
                    }
                }
            } else {
                if ($request->ids != "") {
                    if ($request->action == "activate") {
                        Comment::wherein('id', $request->ids)
                            ->update(['status' => 1]);

                    } elseif ($request->action == "block") {
                        Comment::wherein('id', $request->ids)
                            ->update(['status' => 0]);

                    } elseif ($request->action == "delete") {
                        // Check Permissions
                        if (!@Auth::user()->permissionsGroup->delete_status) {
                            return redirect()->route('NoPermission');
                        }

                        Comment::wherein('id', $request->ids)
                            ->delete();

                    }
                }
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'comments');
        } else {
            return redirect()->route('NotFound');
        }
    }

    // Maps Functions

    public function topicsMaps($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'maps');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function mapsCreate($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->add_status) {
                return redirect()->route('NoPermission');
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                'maps')->with('mapST', 'create');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function mapsStore(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $this->validate($request, [
                'longitude' => 'required',
                'latitude' => 'required'
            ]);


            $next_nor_no = Map::where('topic_id', '=', $id)->max('row_no');
            if ($next_nor_no < 1) {
                $next_nor_no = 1;
            } else {
                $next_nor_no++;
            }

            $Map = new Map;
            $Map->row_no = $next_nor_no;
            $Map->longitude = strip_tags($request->longitude);
            $Map->latitude = strip_tags($request->latitude);
            foreach (Helper::languagesList() as $ActiveLanguage) {
                if ($ActiveLanguage->box_status) {
                    $Map->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                    $Map->{"details_" . $ActiveLanguage->code} = strip_tags($request->{"details_" . $ActiveLanguage->code});
                }
            }
            $Map->icon = $request->icon;
            $Map->topic_id = $id;
            $Map->status = 1;
            $Map->created_by = Auth::user()->id;
            $Map->save();

            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'maps');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function mapsEdit($webmasterId, $id, $map_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->edit_status) {
                return redirect()->route('NoPermission');
            }

            $Map = Map::find($map_id);
            if (!empty($Map)) {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                    'maps')->with('mapST', 'edit')->with('Map', $Map);
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'maps');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function mapsUpdate(Request $request, $webmasterId, $id, $map_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $Map = Map::find($map_id);
            if (!empty($Map)) {


                $this->validate($request, [
                    'longitude' => 'required',
                    'latitude' => 'required'
                ]);
                $Map->longitude = strip_tags($request->longitude);
                $Map->latitude = strip_tags($request->latitude);
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $Map->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                        $Map->{"details_" . $ActiveLanguage->code} = strip_tags($request->{"details_" . $ActiveLanguage->code});
                    }
                }
                $Map->icon = $request->icon;
                $Map->status = $request->status;
                $Map->updated_by = Auth::user()->id;
                $Map->save();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'))->with('activeTab', 'maps');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'maps');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function mapsDestroy($webmasterId, $id, $map_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->delete_status) {
                return redirect()->route('NoPermission');
            }
            //
            $Map = Map::find($map_id);
            if (!empty($Map)) {
                $Map->delete();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.deleteDone'))->with('activeTab', 'maps');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'maps');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function mapsUpdateAll(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            if ($request->action == "order") {
                foreach ($request->row_ids as $rowId) {
                    $Map = Map::find($rowId);
                    if (!empty($Map)) {
                        $row_no_val = "row_no_" . $rowId;
                        $Map->row_no = $request->$row_no_val;
                        $Map->save();
                    }
                }
            } else {
                if ($request->ids != "") {
                    if ($request->action == "activate") {
                        Map::wherein('id', $request->ids)
                            ->update(['status' => 1]);

                    } elseif ($request->action == "block") {
                        Map::wherein('id', $request->ids)
                            ->update(['status' => 0]);

                    } elseif ($request->action == "delete") {

                        // Check Permissions
                        if (!@Auth::user()->permissionsGroup->delete_status) {
                            return redirect()->route('NoPermission');
                        }

                        Map::wherein('id', $request->ids)
                            ->delete();

                    }
                }
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'maps');
        } else {
            return redirect()->route('NotFound');
        }
    }

    // Files Functions

    public function topicsFiles($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'files');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function filesCreate($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->add_status) {
                return redirect()->route('NoPermission');
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                'files')->with('fileST', 'create');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function filesStore(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $this->validate($request, [
                'file' => 'required|mimes:' . $this->allowed_file_types
            ]);

            // Start of Upload Files
            $formFileName = "file";
            $fileFinalName = "";
            if ($request->$formFileName != "") {
                $fileFinalName = time() . rand(1111,
                        9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                $path = $this->uploadPath;
                $request->file($formFileName)->move($path, $fileFinalName);
            }
            if ($fileFinalName != "") {

                $next_nor_no = AttachFile::where('topic_id', '=', $id)->max('row_no');
                if ($next_nor_no < 1) {
                    $next_nor_no = 1;
                } else {
                    $next_nor_no++;
                }

                $AttachFile = new AttachFile;
                $AttachFile->topic_id = $id;
                $AttachFile->row_no = $next_nor_no;
                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $AttachFile->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                    }
                }
                $AttachFile->file = $fileFinalName;
                $AttachFile->created_by = Auth::user()->id;
                $AttachFile->save();

                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'))->with('activeTab', 'files');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'files');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function filesEdit($webmasterId, $id, $file_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->edit_status) {
                return redirect()->route('NoPermission');
            }

            $AttachFile = AttachFile::find($file_id);
            if (!empty($AttachFile)) {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                    'files')->with('fileST', 'edit')->with('AttachFile', $AttachFile);
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'files');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function filesUpdate(Request $request, $webmasterId, $id, $file_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            $this->validate($request, [
                'file' => 'mimes:' . $this->allowed_file_types
            ]);

            $AttachFile = AttachFile::find($file_id);
            if (!empty($AttachFile)) {

                // Start of Upload Files
                $formFileName = "file";
                $fileFinalName = "";
                if ($request->$formFileName != "") {
                    // Delete a Topic photo
                    if ($AttachFile->$formFileName != "" && $AttachFile->$formFileName != "default.png") {
                        File::delete($this->uploadPath . $AttachFile->$formFileName);
                    }

                    $fileFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $request->file($formFileName)->move($path, $fileFinalName);
                }

                foreach (Helper::languagesList() as $ActiveLanguage) {
                    if ($ActiveLanguage->box_status) {
                        $AttachFile->{"title_" . $ActiveLanguage->code} = strip_tags($request->{"title_" . $ActiveLanguage->code});
                    }
                }
                if ($fileFinalName != "") {
                    $AttachFile->file = $fileFinalName;
                }
                $AttachFile->updated_by = Auth::user()->id;
                $AttachFile->save();

                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'))->with('activeTab', 'files');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'files');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function filesDestroy($webmasterId, $id, $file_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->delete_status) {
                return redirect()->route('NoPermission');
            }
            //
            $AttachFile = AttachFile::find($file_id);
            if (!empty($AttachFile)) {
                // Delete file
                if ($AttachFile->file != "" && $AttachFile->file != "default.png") {
                    File::delete($this->uploadPath . $AttachFile->file);
                }

                $AttachFile->delete();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.deleteDone'))->with('activeTab', 'files');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'files');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function filesUpdateAll(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            if ($request->action == "order") {
                foreach ($request->row_ids as $rowId) {
                    $AttachFile = AttachFile::find($rowId);
                    if (!empty($AttachFile)) {
                        $row_no_val = "row_no_" . $rowId;
                        $AttachFile->row_no = $request->$row_no_val;
                        $AttachFile->save();
                    }
                }
            } else {
                if ($request->ids != "") {
                    if ($request->action == "delete") {
                        // Check Permissions
                        if (!@Auth::user()->permissionsGroup->delete_status) {
                            return redirect()->route('NoPermission');
                        }

                        // Delete Topics photo
                        $AttachFiles = AttachFile::wherein('id', $request->ids)->get();
                        foreach ($AttachFiles as $AttachFile) {
                            if ($AttachFile->file != "" && $AttachFile->file != "default.png") {
                                File::delete($this->uploadPath . $AttachFile->file);
                            }
                        }

                        AttachFile::wherein('id', $request->ids)
                            ->delete();

                    }
                }
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'files');
        } else {
            return redirect()->route('NotFound');
        }
    }

    // Related Topics Functions

    public function topicsRelated($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'related');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function topicsRelatedLoad($id)
    {

        $link_title_var = "title_" . @Helper::currentLanguage()->code;
        $TopicsLoaded = Topic::where('webmaster_id', '=', $id)->orderby('row_no', 'asc')->get();
        $i = 0;
        foreach ($TopicsLoaded as $TopicLoaded) {
            $title = $TopicLoaded->$link_title_var;
            $tid = $TopicLoaded->id;
            echo "
<label class=\"ui-check\">
<input type='checkbox' name='related_topics_id[]' value='$tid' id='related_topics_$i' class=''>
<i class=\"dark-white\"></i> &nbsp;<label for=\"related_topics_$i\">$title</label>
</label>
        ";
            echo "<br>";
            $i++;
        }
    }

    public function relatedCreate($webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->add_status) {
                return redirect()->route('NoPermission');
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab',
                'related')->with('relatedST', 'create');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function relatedStore(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            foreach ($request->related_topics_id as $related_topic_id) {
                $next_nor_no = RelatedTopic::where('topic_id', '=', $id)->max('row_no');
                if ($next_nor_no < 1) {
                    $next_nor_no = 1;
                } else {
                    $next_nor_no++;
                }

                $RelatedTopic = new RelatedTopic;
                $RelatedTopic->topic_id = $id;
                $RelatedTopic->topic2_id = $related_topic_id;
                $RelatedTopic->row_no = $next_nor_no;
                $RelatedTopic->created_by = Auth::user()->id;
                $RelatedTopic->save();
            }
            if (count($request->related_topics_id) > 0) {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.saveDone'))->with('activeTab', 'related');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function relatedDestroy($webmasterId, $id, $file_id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            // Check Permissions
            if (!@Auth::user()->permissionsGroup->delete_status) {
                return redirect()->route('NoPermission');
            }
            //
            $RelatedTopic = RelatedTopic::find($file_id);
            if (!empty($RelatedTopic)) {
                $RelatedTopic->delete();
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                    __('backend.deleteDone'))->with('activeTab', 'related');
            } else {
                return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('activeTab', 'related');
            }
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function relatedUpdateAll(Request $request, $webmasterId, $id)
    {
        $WebmasterSection = WebmasterSection::find($webmasterId);
        if (!empty($WebmasterSection)) {
            //
            if ($request->action == "order") {
                foreach ($request->row_ids as $rowId) {
                    $RelatedTopic = RelatedTopic::find($rowId);
                    if (!empty($RelatedTopic)) {
                        $row_no_val = "row_no_" . $rowId;
                        $RelatedTopic->row_no = $request->$row_no_val;
                        $RelatedTopic->save();
                    }
                }
            } else {
                if ($request->ids != "") {
                    if ($request->action == "delete") {
                        // Check Permissions
                        if (!@Auth::user()->permissionsGroup->delete_status) {
                            return redirect()->route('NoPermission');
                        }

                        RelatedTopic::wherein('id', $request->ids)
                            ->delete();

                    }
                }
            }
            return redirect()->action('Dashboard\TopicsController@edit', [$webmasterId, $id])->with('doneMessage',
                __('backend.saveDone'))->with('activeTab', 'related');
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function upload(Request $request)
    {
        //
        $this->validate($request, [
            'file' => 'image',
        ]);

        // Start of Upload Files
        $formFileName = "file";
        $fileFinalName = "";
        $fileFinalTitle = ""; // Original file name without extension
        if ($request->$formFileName != "") {
            $fileFinalTitle = basename($request->file($formFileName)->getClientOriginalName(),
                '.' . $request->file($formFileName)->getClientOriginalExtension());
            $fileFinalName = time() . rand(1111,
                    9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
            $path = $this->uploadPath;
            $request->file($formFileName)->move($path, $fileFinalName);

            // resize & optimize
            Helper::imageResize($path . $fileFinalName);
            Helper::imageOptimize($path . $fileFinalName);
        }
        // End of Upload Files
        if ($fileFinalName != "") {
            return $fileFinalName;
        } else {
            return "Error";
        }

    }

    public function send_notification($WebmasterSection, $Topic, $Case = "")
    {
        try {
            if (($WebmasterSection->type == 4 && @Helper::GeneralSiteSettings('notify_private_status')) || ($WebmasterSection->type == 5 && @Helper::GeneralSiteSettings('notify_table_status'))) {
                $site_email = @Helper::GeneralSiteSettings("site_webmails");
                $recipient = explode(",", str_replace(" ", "", $site_email));

                $no_reply_email = @Helper::GeneralWebmasterSettings("mail_no_replay");
                $site_title_var = "site_title_" . @Helper::currentLanguage()->code;
                $site_title = @Helper::GeneralSiteSettings($site_title_var);

                $tpc_title = @$Topic->{'title_' . @Helper::currentLanguage()->code};

                $fields_details = "";
                try {
                    if (count($Topic->webmasterSection->customFields) > 0) {
                        $fields_details .= "<hr>";
                        $cf_title_var = "title_" . @Helper::currentLanguage()->code;
                        $cf_title_var2 = "title_" . config('smartend.default_language');
                        $i = 0;
                        foreach ($Topic->webmasterSection->customFields as $customField) {
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
                            if (($cf_saved_val != "" || count($cf_saved_val_array) > 0) && ($customField->lang_code == "all" || $customField->lang_code == @Helper::currentLanguage()->code)) {
                                if ($customField->type == 12) {
                                    //
                                } elseif ($customField->type == 11) {
                                    //
                                } elseif ($customField->type == 10) {
                                    //
                                } elseif ($customField->type == 9) {
                                    //
                                } elseif ($customField->type == 8) {
                                    //
                                } elseif ($customField->type == 7) {
                                    $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                    $cf_details_var2 = "details_" . config('smartend.default_language');
                                    if ($customField->$cf_details_var != "") {
                                        $cf_details = $customField->$cf_details_var;
                                    } else {
                                        $cf_details = $customField->$cf_details_var2;
                                    }
                                    $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                    $line_num = 1;

                                    $fields_details .= "<div><strong>" . $cf_title . " : </strong>";
                                    foreach ($cf_details_lines as $cf_details_line) {
                                        if (in_array($line_num, $cf_saved_val_array)) {
                                            $fields_details .= "<div>" . $cf_details_line . "</div>";
                                        }
                                        $line_num++;
                                    }
                                    $fields_details .= "</div>";
                                } elseif ($customField->type == 6) {
                                    $cf_details_var = "details_" . @Helper::currentLanguage()->code;
                                    $cf_details_var2 = "details_" . config('smartend.default_language');
                                    if ($customField->$cf_details_var != "") {
                                        $cf_details = $customField->$cf_details_var;
                                    } else {
                                        $cf_details = $customField->$cf_details_var2;
                                    }
                                    $cf_details_lines = preg_split('/\r\n|[\r\n]/', $cf_details);
                                    $line_num = 1;
                                    $fields_details .= "<div><strong>" . $cf_title . " : </strong>";
                                    foreach ($cf_details_lines as $cf_details_line) {
                                        if ($line_num == $cf_saved_val) {
                                            $fields_details .= "<div>" . $cf_details_line . "</div>";
                                        }
                                        $line_num++;
                                    }
                                    $fields_details .= "</div>";
                                } elseif ($customField->type == 5) {
                                    $fields_details .= "<div><strong>" . $cf_title . " : </strong>" . Helper::dateForDB($cf_saved_val, 1) . "</div>";
                                } elseif ($customField->type == 4) {
                                    $fields_details .= "<div><strong>" . $cf_title . " : </strong>" . Helper::dateForDB($cf_saved_val) . "</div>";
                                } else {
                                    if ($tpc_title == "") {
                                        $tpc_title = $cf_saved_val;
                                    }
                                    $fields_details .= "<div><strong>" . $cf_title . " : </strong>" . $cf_saved_val . "</div>";
                                }
                            }
                            $i++;
                        }
                    }
                } catch (\Exception $e) {

                }

                $message_details = "<h3>" . $tpc_title . "</h3>" . Auth::user()->name . $fields_details . "<hr><a href='" . route("topicsEdit", [@$WebmasterSection->id, @$Topic->id]) . "'>View All Details</a>";

                Mail::to($recipient)->send(new NotificationEmail(
                    [
                        "title" => $Case . ": " . $tpc_title . " By: " . Auth::user()->name,
                        "details" => $message_details,
                        "from_email" => $no_reply_email,
                        "from_name" => $site_title
                    ]
                ));
            }
        } catch (\Exception $e) {

        }
    }

    public function keditor($topic_id)
    {
        $lang = \request()->input('lang');
        if ($lang == "") {
            $lang = @Helper::currentLanguage()->code;
        }
        if (@Auth::user()->permissionsGroup->view_status) {
            $Topic = Topic::where('created_by', '=', @Auth::user()->id)->find($topic_id);
        } else {
            $Topic = Topic::find($topic_id);
        }
        if (!empty($Topic)) {
            $title = @$Topic->{'title_' . @Helper::currentLanguage()->code};
            $content = @$Topic->{'details_' . $lang};
            if (!str_contains($content, 'ui-resizable')) {
                $content = '<div class="row"><div class="col-sm-12 ui-resizable" data-type="container-content"><div data-type="component-text">' . $content . '</div></div></div>';
            }
            return view("dashboard.topics.keditor.edit", compact("Topic", "title", "content", "lang"));
        } else {
            return redirect()->route('NotFound');
        }
    }

    public function keditor_snippets()
    {
        return view("dashboard.topics.keditor.snippets");
    }

    public function keditor_save(Request $request)
    {
        if (!@Auth::user()->permissionsGroup->edit_status) {
            return redirect()->route('NoPermission');
        }

        if (@Auth::user()->permissionsGroup->view_status) {
            $Topic = Topic::where('created_by', '=', Auth::user()->id)->find($request->topic_id);
        } else {
            $Topic = Topic::find($request->topic_id);
        }
        if (!empty($Topic)) {
            $lang = $request->lang;
            if ($lang != "") {
                $Topic->{"details_" . $lang} = $request->html_content;
                $Topic->updated_by = Auth::user()->id;
                $Topic->save();
            }

            return json_encode(array("stat" => "success", "msg" => __("backend.saveDone")));
        }
        return json_encode(array("stat" => "error", "msg" => __("backend.error")));
    }

    public function import(Request $request)
    {
        if ($request->step == 2) {
            $validator = Validator::make($request->all(), [
                'file_name' => 'required',
                'row_key' => 'required',
                'from_row' => 'required'
            ]);
            $WebmasterSection = WebmasterSection::find(decrypt($request->section_id));
            if (!empty($WebmasterSection)) {
                if ($validator->passes()) {
                    if (file_exists($this->uploadPath . $request->file_name)) {
                        $xls_file_path = $this->uploadPath . $request->file_name;
                        $import = new TopicsImport($request, $WebmasterSection->id);
                        Excel::import($import, $xls_file_path);

                        $result = $import->getResult();

                        if (@$result["finished"]) {
                            // Delete file
                            File::delete($this->uploadPath . $request->file_name);
                        }

                        return response()->json(['stat' => 'success', 'step' => 3, 'data' => $result]);
                    }
                }
            }
            return response()->json(['stat' => 'error', 'error' => [__('backend.error')]]);

        } elseif ($request->step == 1) {
            $this->validate($request, [
                'file' => 'required|mimes:xls,xlsx'
            ]);
            $WebmasterSection = WebmasterSection::find(decrypt($request->section_id));
            if (!empty($WebmasterSection)) {
                // delete old data
                if ($request->old_data_delete) {
                    $OldTopics = Topic::where('webmaster_id', $WebmasterSection->id)->get();
                    if (count($OldTopics) > 0) {
                        foreach ($OldTopics as $Topic) {
                            // delete topic
                            // Delete a Topic photo
                            if ($Topic->photo_file != "" && $Topic->photo_file != "default.png") {
                                File::delete($this->uploadPath . $Topic->photo_file);
                            }
                            if ($Topic->attach_file != "") {
                                File::delete($this->uploadPath . $Topic->attach_file);
                            }
                            if ($Topic->audio_file != "") {
                                File::delete($this->uploadPath . $Topic->audio_file);
                            }
                            if ($Topic->video_type == 0 && $Topic->video_file != "") {
                                File::delete($this->uploadPath . $Topic->video_file);
                            }
                            //delete additional fields
                            TopicField::where('topic_id', $Topic->id)->delete();
                            //delete Related Topics
                            RelatedTopic::where('topic_id', $Topic->id)->delete();
                            RelatedTopic::where('topic2_id', $Topic->id)->delete();
                            // Remove categories
                            TopicCategory::where('topic_id', $Topic->id)->delete();
                            // Remove comments
                            Comment::where('topic_id', $Topic->id)->delete();
                            // Remove maps
                            Map::where('topic_id', $Topic->id)->delete();
                            // Remove Photos
                            $PhotoFiles = Photo::where('topic_id', $Topic->id)->get();
                            if (count($PhotoFiles) > 0) {
                                foreach ($PhotoFiles as $PhotoFile) {
                                    if ($PhotoFile->file != "" && $PhotoFile->file != "default.png") {
                                        File::delete($this->uploadPath . $PhotoFile->file);
                                    }
                                }
                            }
                            Photo::where('topic_id', $Topic->id)->delete();
                            // Remove Attach Files
                            $AttachFiles = AttachFile::where('topic_id', $Topic->id)->get();
                            if (count($AttachFiles) > 0) {
                                foreach ($AttachFiles as $AttachFile) {
                                    if ($AttachFile->file != "" && $AttachFile->file != "default.png") {
                                        File::delete($this->uploadPath . $AttachFile->file);
                                    }
                                }
                            }
                            AttachFile::where('topic_id', $Topic->id)->delete();

                            //Remove Topic
                            $Topic->delete();
                        }
                    }
                }

                // upload file
                $formFileName = "file";
                $ExcelFinalName = "";
                if ($request->$formFileName != "") {
                    $ExcelFinalName = time() . rand(1111,
                            9999) . '.' . $request->file($formFileName)->getClientOriginalExtension();
                    $path = $this->uploadPath;
                    $uploaded = $request->file($formFileName)->move($path, $ExcelFinalName);
                    if (!$uploaded) {
                        $ExcelFinalName = "";
                    }
                }
                $ExcelColumns = [];
                $row_key = 0;
                if ($ExcelFinalName != "") {
                    $xls_file_path = $this->uploadPath . $ExcelFinalName;
                    $NewImport = new ReadExcelFile();
                    Excel::import($NewImport, $xls_file_path);
                    $ExcelColumns = $NewImport->data;
                    $row_key = $NewImport->row_key;
                }
                //return print_r($ExcelColumns);
                if (count($ExcelColumns) > 0) {

                    $fatherSections = Section::where('webmaster_id', '=', $WebmasterSection->id)->where('father_id', '=',
                        '0')->orderby('row_no', 'asc')->get();

                    $html = view("dashboard.topics.import.step2", compact("WebmasterSection", "ExcelFinalName", "row_key", "ExcelColumns", "fatherSections"))->render();
                    return response()->json(['stat' => 'success', 'step' => 2, 'html' => $html]);
                } else {
                    return response()->json(['stat' => 'error', 'error' => [__('backend.fileNotExcel')]]);
                }
            }
            return response()->json(['stat' => 'error', 'error' => [__('backend.error')]]);
        } else {
            try {
                $WebmasterSection = WebmasterSection::find(decrypt($request->section_id));
                if (!empty($WebmasterSection)) {
                    return view("dashboard.topics.import.step1", compact("WebmasterSection"));
                }
            } catch (\Exception $e) {

            }
        }
        return "<div class='alert alert-danger m-a'>" . __("backend.error") . "</div>";
    }

    public function update_table_columns(Request $request)
    {
        if ($request->webmaster_id > 0 && $request->columns_position != "") {
            $User = Auth::user();
            if (!empty($User)) {
                $new_cols_sort = [];
                if (!$request->reset_default) {
                    $active_count = 0;
                    foreach (@explode(",", $request->columns_position) as $col_position) {
                        $new_cols_sort[$col_position] = ($request->$col_position) ? 1 : 0;
                        if ($request->$col_position) {
                            $active_count++;
                        }
                    }
                    if ($active_count == 0) {
                        return redirect()->back()->with('errorMessage', __('backend.error'));
                    }
                }
                $table_columns = [];
                if ($User->table_columns != "") {
                    try {
                        $table_columns = json_decode($User->table_columns);
                    } catch (\Exception $e) {

                    }
                }
                if (empty($table_columns)) {
                    $table_columns["sec_" . $request->webmaster_id] = $new_cols_sort;
                } else {
                    $table_columns->{"sec_" . $request->webmaster_id} = $new_cols_sort;
                }
                $User->table_columns = json_encode($table_columns);
                $User->save();

                return redirect()->back()->with('doneMessage', __('backend.saveDone'));
            }
        }
        return redirect()->back();
    }
}
