<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\WebmasterSetting;
use Auth;
use File;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Mail;
use Redirect;
use ZipArchive;

class WebmasterLicenseController extends Controller
{
    private $ItemID = "19184332";
    private $ApiURL = "https://smartend.app/api/license/index.php";
    private $Error = "";


    public function index(Request $request)
    {
		return response()->json(['status' => 'success', 'msg' => __('backend.licenseSuccess')]);
    }

    private function api_call($action, $purchase_code, $current_version)
    {
        $request = [
            "item_id" => $this->ItemID,
            "action" => $action,
            "purchase_code" => $purchase_code,
            "version" => $current_version,
            "website" => @$_SERVER['SERVER_NAME'],
        ];
        return Http::withBody(json_encode($request), "text/plain;charset=UTF-8")->post($this->ApiURL);
    }

    private function check_license($purchase_code)
    {
		$WebmasterSetting = WebmasterSetting::find(1);
		$WebmasterSetting->license = 1;
		$WebmasterSetting->purchase_code = encrypt($purchase_code);
		$WebmasterSetting->save();
		return 1;
    }

    private function check_updates()
    {
        return ["status" => "success"];
    }

    private function system_update()
    {
        try {
            $WebmasterSetting = WebmasterSetting::find(1);
            try {
                $purchase_code = decrypt(@$WebmasterSetting->purchase_code);
            } catch (\Exception $e) {
                $purchase_code = "";
                $WebmasterSetting->purchase_code = null;
                $WebmasterSetting->license = 0;
                $WebmasterSetting->save();
            }
            $response = $this->api_call("check_update", $purchase_code, @$WebmasterSetting->version);
            if (@$response['file'] != "" && @$WebmasterSetting->version != @$response['version'] && version_compare(PHP_VERSION, @$response['php']) >= 0) {
                // check file structure
                $root_path = str_replace("/core", "/", base_path());
                if (!file_exists(base_path() . "/.env")) {
                    return 0;
                }

                // get DB info
                $DB_HOST = config('database.connections.mysql.host');
                $DB_PORT = config('database.connections.mysql.port');
                $DB_DATABASE = config('database.connections.mysql.database');
                $DB_USERNAME = config('database.connections.mysql.username');
                $DB_PASSWORD = config('database.connections.mysql.password');
                $DB_TABLE_PREFIX = config('database.connections.mysql.prefix');

                // download and install update files
                $local_zip_file = $root_path . "update.zip";
                $local_db_files = $root_path . "db_updates/";

                // clear cache files
                $cache_routes_file = $root_path . "core/bootstrap/cache/routes-v7.php";
                $cache_config_file = $root_path . "core/bootstrap/cache/config.php";

                if (file_exists($cache_routes_file)) {
                    try {
                        File::delete($cache_routes_file);
                    } catch (\Exception $e) {

                    }
                }
                if (file_exists($cache_config_file)) {
                    try {
                        File::delete($cache_config_file);
                    } catch (\Exception $e) {

                    }
                }

                // start updating
                if (@copy(@$response['file'], $local_zip_file)) {
                    $zip = new ZipArchive;
                    $res = $zip->open($local_zip_file);
                    if ($res === TRUE) {
                        if (@$response['code'] != "") {
                            $res = $zip->setPassword(@$response['code']);
                        }
                        $zip->extractTo($root_path);
                        $zip->close();
                    }


                    $DB_updated = 0;
                    if (count(@$response['dbs']) > 0) {
                        try {
                            if (file_exists($local_db_files)) {
                                $con = @mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE, $DB_PORT);
                                if (mysqli_connect_errno()) {
                                    $DB_updated = 0;
                                } else {
                                    try {
                                        // update DB
                                        mysqli_set_charset($con, "utf8");
                                        foreach (@$response['dbs'] as $sql_file_name) {
                                            if (file_exists($local_db_files . $sql_file_name . ".sql")) {
                                                $templine = '';
                                                $lines = file($local_db_files . $sql_file_name . ".sql");
                                                foreach ($lines as $line) {
                                                    if (substr($line, 0, 2) == '--' || $line == '') {
                                                        continue;
                                                    }

                                                    $templine .= $line;
                                                    if (substr(trim($line), -1, 1) == ';') {
                                                        $templine = str_replace("smartend_", $DB_TABLE_PREFIX, $templine);
                                                        mysqli_query($con, $templine);
                                                        $templine = '';
                                                    }
                                                }

                                            }
                                        }
                                        $DB_updated = 1;
                                    } catch (\Exception $e) {
                                        $DB_updated = 0;
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $DB_updated = 0;
                        }
                    }

                    // remove temp files
                    if (file_exists($local_zip_file)) {
                        try {
                            File::delete($local_zip_file);
                        } catch (\Exception $e) {

                        }
                    }
                    if (file_exists($local_db_files)) {
                        try {
                            File::deleteDirectory($local_db_files);
                        } catch (\Exception $e) {

                        }
                    }

                    // clear old sessions and cache
                    try {
                        \Session()->forget('_Loader_WebmasterSettings');
                        \Session()->forget('_Loader_Web_Settings');
                        \Session()->forget('_Loader_Languages');
                        \Session()->forget('_Loader_Events');
                        \Session()->forget('_Loader_WebmasterSections');
                    } catch (\Exception $e) {

                    }
                    return $DB_updated;
                }
            }
        } catch (\Exception $e) {

        }
        return 0;
    }
}
