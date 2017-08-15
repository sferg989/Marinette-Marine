<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/9/2016
 * Time: 12:39 PM
 */
//session_start();
//extract($_SESSION);
//extract($_COOKIE);
extract($_REQUEST);
set_time_limit(0);
error_reporting(0);

include('lib/php/adodb5/adodb.inc.php');


//Global Paths
$base_path      = "Y:/Program Management/cobra processing/";
//$base_path      = "C:/program_management_test/cobra processing/";
$g_path_to_util = "C:\\xampp\\htdocs\\fmg\\util\\";
$g_path2BAT     = $g_path_to_util . "backup_scripts\\BAT\\";
$g_path2CMD     = $g_path_to_util . "backup_scripts\\CMD\\";

$g_path2BATSQLUtil     = $g_path_to_util . "sql_util\\BAT\\";
$g_path2CMDSQLUtil     = $g_path_to_util . "sql_util\\CMD\\";

$g_path2BatrptBAT = $g_path_to_util . "batch_report_process\\BAT\\";
$g_path2BatrptCMD = $g_path_to_util . "batch_report_process\\CMD\\";

$g_path2ArhiveProjectBAT = $g_path_to_util . "archive_project\\BAT\\";
$g_path2ArhiveProjectCMD = $g_path_to_util . "archive_project\\CMD\\";

$g_path2ReClassProjectBAT = $g_path_to_util . "reclass\\BAT\\";
$g_path2ReClassProjectCMD = $g_path_to_util . "reclass\\CMD\\";

$g_path2AdvanceCalendarProjectBAT = $g_path_to_util . "advance_calendar\\BAT\\";
$g_path2AdvanceCalendarProjectCMD = $g_path_to_util . "advance_calendar\\CMD\\";
$g_path2weeklyPerformanceCSV      = $g_path_to_util . "csv_weekly_performance_report\\";

$g_path2CobraAPI = "\"program files (x86)\"\\deltek\\\"cobra 5\"\\cobra.api.exe";

$g_path2_wi           = "Y:\\Program Management\\Cobra Processing 5.1\\Cobra 5.1 Work Instructions";
$g_path2_wi_localhost = $g_path_to_util."wi";

$g_path2_ceac           = "Y:/File Transfer Folder/CEAC-20170429";

$g_path2_baan_work       = "A:\\";
$g_path2_outsourcing_dir = $base_path . "Outsourcing Data";
$g_path2_spreadsheetReader = "spreadsheet-reader-master/spreadsheet-reader-master/SpreadsheetReader.php";
