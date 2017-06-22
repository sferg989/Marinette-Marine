<?php

include("mmev.inc.php");
session_start();

function dbCall($sql,$schema="fmm_evms",$server="localhost"){

    $user     = "steve";
    $password = "all4him";
    $db = ADONewConnection('mysql');
    $db->Connect($server, $user, $password, $schema);

    $result = $db->Execute($sql);
    return $result;
}
function dbCallCobra($sql){

    $db = ADONewConnection('odbc_mssql');

    $dsn = "Driver={SQL Server};Server=mmcsqlapp;Database=Cobra51;";
//declare the SQL statement that will query the database
    $db->Connect($dsn);
    $db->SetFetchMode(3);
    $result = $db->Execute($sql);
    return $result;
}
function now()
{
    $timestamp = date("Y-m-d h:i:s");
    return $timestamp;
}

function truncateTable($schema, $table){
    $sql = "truncate table $schema.$table";
    $junk = dbCall($sql, $schema);
}
function buildWCandGB($pmid,$cmid="",$cam="")
{
    $wc = "where ";
    $gb = "group by ";
    if($pmid!="")
    {
        $wc.= "cost.pmid = $pmid";
        $gb.= "cost.pmid,";
    }
    if($cmid!="")
    {
        $wc.= " and cost.cmid = $cmid";
        $gb.= "cost.cmid,";
    }
    if($cam!="")
    {
        $wc.= " and ca.cam = '$cam'";
        $gb.= "ca.cam,";
    }

    $gb = substr($gb, 0, -1);
    $data["wc"] = $wc;
    $data["gb"] = $gb;
    return $data;
}
function evalTCPI($a, $p,$bac,$eac)
{
    $step1 = $bac-$p;
    if ($step1 == 0.0) {
        return 0;
    }
    $step2 = $eac-$a;
    if($step2 == 0.0)
    {
        return 0;
    }
    $tcpi = ($step1/$step2);
    return $tcpi;

}
function fixExcelDate($date)
{
    //print "THis is the date. ".$date."<br>";
    if ($date != "") {
        $date_array = explode("/", $date);
        $new_date   = "$date_array[2]-$date_array[0]-$date_array[1]";
        //print "THis is the NEW date. ".$new_date."<br>";
        return $new_date;
    }
    else return null;
}
function getCMID($wbs_id)
{
    $sql = "select id from fmm_evms.master_ca where wbs_id = '$wbs_id'";
    $rs = dbCall($sql);
    $cmid = $rs->fields["id"];
    return $cmid;
}
function getLastId($schema, $table, $field)
{
    $sql = "select max($field) id from $table";
    //print $sql;
    $rs = dbCall($sql, $schema);
    $id = $rs->fields["id"];
    return $id;
}

function fixCostField($dollar_amount)
{
    $dollar_amount = str_replace("$", "", $dollar_amount);
    $dollar_amount = str_replace(",", "", $dollar_amount);
    $dollar_amount = str_replace("(", "-", $dollar_amount);
    $dollar_amount = str_replace(")", "", $dollar_amount);
    return floatval($dollar_amount);
}

function insertMasterCA($pmid, $ca_name, $wbs_id, $cam="")
{
    $sql = "INSERT INTO master_ca (pmid, name, wbs_id, cam) VALUES
      (2,'$ca_name','$wbs_id', '$cam')
      ";
    $junk = dbCall($sql, "fmm_evms");
}

function checkMasterCA($wbs_id,$ca_name)
{
    $sql = "select id from master_ca where wbs_id = '$wbs_id'";
    $rs = dbCall($sql,"fmm_evms");
    $id = $rs->fields["id"];

    if(empty($id)==true)
    {
        print "inserting ".$ca_name;
        insertMasterCA(2, $ca_name, $wbs_id);
    }
}
function createRPTfromDate($date){
    $date_array = explode("-", trim($date));

    $day = intval($date_array[2]);
    $month = $date_array[1];
    $year = $date_array[0];
    if($day<22)
    {
        $month = intval($month)-1;
        if($month==0)
        {
            $month = 12;
            $year = $year-1;
        }
    }
    if(strlen($month)==1)
    {
        $month = "0".$month;
    }
    $rpt_period = "$year"."$month";
    return $rpt_period;
}
function createRPTfromDateSlash($date){
    $date_array = explode("/", $date);
    $month = $date_array[0];
    $day = $date_array[1];
    $year = $date_array[2];
    if($day<15)
    {
        $month = intval($month)-1;
        if($month==0)
        {
            $month = 12;
            $year = $year-1;
        }
    }
    if(strlen($month)==1)
    {
        $month = "0".$month;
    }
    $rpt_period = "$year"."$month";
    return $rpt_period;
}
function createRPTPeriodfromDate($date){
    $date_array = explode("-", $date);
    $day = $date_array[2];
    $month = $date_array[1];
    $year = $date_array[0];
    if($day>15)
    {
        $month = $month+1;
    }
    if(strlen($month)==1)
    {
        $month = "0".$month;
    }
    $rpt_period = "$year"."$month";
    return $rpt_period;
}

function makeNewYearDIR($ship_name,$ship_code,$new_year,$base_path)
{
    $path = $base_path."".$ship_name."/".$ship_code."/".$ship_code." ".$new_year;
    if(file_exists($path)==false)
    {
        mkdir($path);
    }
}
function clearFilesinDir($path)
{
    $files = glob($path.'/*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }
}
function deleteFilesfromDir($path)
{
    $files = glob($path."/*"); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file);
    }
}

function month2digit($month)
{
    if(strlen($month)==1)
    {
        $month = "0".$month;
    }
    return $month;
}
function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function getProjectNameFromCode($code)
{
    $sql = "select name from fmm_evms.master_project where code = $code";
    $rs= dbCall($sql);
    //print $sql;
    $ship_name = $rs->fields["name"];
    return $ship_name;
}
function createBKUPCMDFile($ship_code,$path2CobraAPI,$g_path2CMD,$g_path2BAT)
{
    $content            = file_get_contents($g_path2CMD . "cmdCobraTEMPLATE.cmd");
    $new_cmd_file_name  = $g_path2CMD . "" . $ship_code . "_bkup.cmd";

    $path2_new_bat_file = $g_path2BAT. $ship_code . "_bkup.BAT";
    $content_cobra_api  = str_replace("####", $path2CobraAPI, $content);
    $content_final      = str_replace("****", $path2_new_bat_file, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);
    return $new_cmd_file_name;
}
function createCobraBKUPBatFile($ship_code,$copy_dest,$g_path2BAT)
{
    /*1. Get the template Batch file, and use a str replace to make the proper
        ship code and destination. #### - is project code. **** is the destination
    2.  make a copy of the project in cobra
    3.  rename the copy
     * */
    $content                   = file_get_contents($g_path2BAT . "COBRAbkuptemplate.bat");
    $content_replace_ship_code = str_replace("####", $ship_code, $content);
    $content_final             = str_replace("****", $copy_dest, $content_replace_ship_code);
    $path2_new_bat_file        = $g_path2BAT . $ship_code . "_bkup.BAT";
    file_put_contents($path2_new_bat_file,$content_final);
    return $path2_new_bat_file;
}

function copyProjectFromCobra($ship_code,$copy_dest, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,$bkup_name="final",$debug=false)
{
    $cmd_file   = createBKUPCMDFile($ship_code,$g_path2CobraAPI,$g_path2CMD,$g_path2BAT);
    $batch_file = createCobraBKUPBatFile($ship_code,$copy_dest,$g_path2BAT);
    if($debug==false)
    {
        exec($cmd_file);
        $copy_old_name = $copy_dest."/".$ship_code.".CMP";
        $copy_new_name = $copy_dest."/".$ship_code."".$bkup_name.".CMP";
        rename($copy_old_name, $copy_new_name);
    }

}

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r"));
    }
    else {
        exec($cmd . " > /dev/null &");
    }
}
function createCobraBatchrptCMDFile($ship_code,$path2CobraAPI,$g_path2CMD,$bat_file_name)
{
    $token              = rand(0, 100);
    $content            = file_get_contents($g_path2CMD . "cmdCobraTEMPLATE.cmd");
    $new_cmd_file_name  = $g_path2CMD."".$ship_code."-".$token.".cmd";

    $content_cobra_api  = str_replace("####", $path2CobraAPI, $content);
    $content_final      = str_replace("****", $bat_file_name, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);
    return $new_cmd_file_name;
}
function createCobraBatchrptBATFile($bacth_report_name,$ship_code,$g_path2BAT)
{
    /*1. Get the template Batch file, and use a str replace to run the given Batch Report Process
        **** is the name of the Batch Report Process
     * */
    $token              = rand(0, 100);
    $content            = file_get_contents($g_path2BAT . "runbatchreporttemplate.bat");
    $content_final      = str_replace("****", $bacth_report_name, $content);
    $path2_new_bat_file = $g_path2BAT . $ship_code . "-" . $token . ".BAT";

    file_put_contents($path2_new_bat_file,$content_final);
    return $path2_new_bat_file;
}

function runCobraBatchReportProcess($ship_code,$batch_rpt_process_name, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,$debug=false)
{
    $bat_file_name = createCobraBatchrptBATFile($batch_rpt_process_name, $ship_code, $g_path2BAT);
    sleep(.5);
    $cmd_file      = createCobraBatchrptCMDFile($ship_code, $g_path2CobraAPI, $g_path2CMD, $bat_file_name);
    if($debug==false)
    {
        exec($cmd_file);
    }
}
function createArchiveCMDFile($ship_code,$path2CobraAPI,$g_path2CMD,$g_path2BAT)
{
    $content            = file_get_contents($g_path2CMD . "cmdCobraTEMPLATE.cmd");
    $new_cmd_file_name  = $g_path2CMD . "" . $ship_code . "_archive.cmd";
    $path2_new_bat_file = $g_path2BAT. $ship_code . "_archive.BAT";
    $content_cobra_api  = str_replace("####", $path2CobraAPI, $content);
    $content_final      = str_replace("****", $path2_new_bat_file, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);
    return $new_cmd_file_name;
}
function createArchiveBatFile($ship_code,$prev_2digit_month,$prev_2digit_year,$prev_month_letters,$prev_year,$g_path2BAT)
{

    /*
    *  ****- Project to Copy
     * #### Name of the Arhived Project
     * $$$$ Description of Project
    */

    $project2copy = $ship_code;


    $arhive_project_name = $ship_code."".$prev_2digit_month."".$prev_2digit_year;
    //$arhive_project_name = $ship_code.""."-test";

    $project_description = $ship_code." ".$prev_month_letters." ".$prev_year." Archive";

    $content            = file_get_contents($g_path2BAT . "archiveprojectTEMPLATE.bat");
    $content1           = str_replace("****", $project2copy, $content);
    $content2           = str_replace("####", $arhive_project_name, $content1);
    $content_final      = str_replace("$$$$", $project_description, $content2);
    $path2_new_bat_file = $g_path2BAT . $ship_code . "_archive.BAT";
    file_put_contents($path2_new_bat_file,$content_final);
    return $path2_new_bat_file;
}
function archiveCobraProject($ship_code, $prev_month,$cur_year_last2,$cur_month_letters,$cur_year,$g_path2CobraAPI,$g_path2CMD,$g_path2BAT, $debug=false)
{
    $cmd_file   = createArchiveCMDFile($ship_code,$g_path2CobraAPI,$g_path2CMD,$g_path2BAT);
    $batch_file = createArchiveBatFile($ship_code,$prev_month,$cur_year_last2,$cur_month_letters,$cur_year,$g_path2BAT);
    if($debug==false)
    {
        exec($cmd_file);
    }
}

function createReClassCMDFile($ship_code,$path2CobraAPI,$g_path2CMD,$bat_file_name)
{
    $token = rand (0,100);
    $content            = file_get_contents($g_path2CMD . "cmdCobraTEMPLATE.cmd");
    $new_cmd_file_name  = $g_path2CMD ."".$ship_code."".$token."_reclass.cmd";
    $content_cobra_api  = str_replace("####", $path2CobraAPI, $content);
    $content_final      = str_replace("****", $bat_file_name, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);
    return $new_cmd_file_name;
}
function createReClassBatFile($ship_code,$g_path2BAT)
{

    /*
    *  **** Project to ReClass
     *
     */
    $project2ReClass = $ship_code;

    $content            = file_get_contents($g_path2BAT . "reclassTEMPLATE.v.2.bat");

    $content_final     = str_replace("****", $project2ReClass, $content);
    $token         = rand (0,100);
    $path2_new_bat_file = $g_path2BAT.$ship_code."-".$token."_reclass.BAT";
    file_put_contents($path2_new_bat_file,$content_final);
    return $path2_new_bat_file;
}
function reClassCobraProject($ship_code, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,$debug=false)
{
    $bat_file_name  = createReClassBatFile($ship_code,$g_path2BAT);
    $cmd_file       = createReClassCMDFile($ship_code,$g_path2CobraAPI,$g_path2CMD,$bat_file_name);
    if($debug==false)
    {
        exec($cmd_file);
    }
}

function createAdvanceCalendarCMDFile($ship_code,$g_path2CobraAPI,$g_path2AdvanceCalendarProjectCMD,$bat_file_name)
{
    $token = rand (0,100);
    $content            = file_get_contents($g_path2AdvanceCalendarProjectCMD . "cmdCobraTEMPLATE.cmd");
    $new_cmd_file_name  = $g_path2AdvanceCalendarProjectCMD ."".$ship_code."".$token."advance_calendar.cmd";
    $content_cobra_api  = str_replace("####", $g_path2CobraAPI, $content);
    $content_final      = str_replace("****", $bat_file_name, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);
    return $new_cmd_file_name;
}
function createAdvanceCalendarBatFile($ship_code,$g_path2BAT,$status_date_for_loe=1,$update_rates_fte,$sync_calendar,$rolling_wave_skip,$update_eac)
{

    /*
    *  **** Project to Advance Calendar
     * @@@@ UseStatusDateAsActualStartDateForLoE
     * %%%% UpdateRateSetsUsedWithFTE -
     * #### SynchCalendarWithProjectStatus -
     * !!!! Skip Rolling Wave
     * ???? UpdateEAC.
     */
    $project2advance = $ship_code;

    $content            = file_get_contents($g_path2BAT . "advance_calendarTEMPLATE.bat");

    $content1      = str_replace("****", $project2advance, $content);
    $content2      = str_replace("@@@@", $status_date_for_loe, $content1);
    $content3      = str_replace("%%%%", $update_rates_fte, $content2);
    $content4      = str_replace("####", $sync_calendar, $content3);
    $content5      = str_replace("!!!!", $rolling_wave_skip, $content4);
    $content_final = str_replace("????", $update_eac, $content5);
    $token         = rand (0,100);
    $path2_new_bat_file = $g_path2BAT.$ship_code."-".$token."advance_calendar.BAT";
    file_put_contents($path2_new_bat_file,$content_final);
    return $path2_new_bat_file;
}
function advanceCalendarCobraProject($ship_code,$g_path2CobraAPI,$g_path2AdvanceCalendarProjectCMD,$g_path2AdvanceCalendarProjectBAT,$status_date_for_loe,$update_rates_fte,$sync_calendar,$rolling_wave_skip, $update_eac,$debug)
{
    $bat_file_name  = createAdvanceCalendarBatFile($ship_code,$g_path2AdvanceCalendarProjectBAT,$status_date_for_loe,$update_rates_fte,$sync_calendar,$rolling_wave_skip,$update_eac);
    $cmd_file       = createAdvanceCalendarCMDFile($ship_code,$g_path2CobraAPI,$g_path2AdvanceCalendarProjectCMD,$bat_file_name);
    if($debug==false)
    {
        exec($cmd_file);
    }
}

function getPreviousRPTPeriod($rpt_period)
{
    $year = intval(substr($rpt_period, 0, 4));
    $month = month2digit(substr($rpt_period, -2));
    if($month=="01")
    {
        $new_year = $year-1;
        $new_month = 12;
    }
    else{
        $new_year = $year;
        $new_month = intval($month)-1;
        $new_month = month2digit($new_month);
    }
    $prev_period = $new_year."".$new_month;
    return $prev_period;

}
function getNextRPTPeriod($rpt_period){
    $year = intval(substr($rpt_period, 0, 4));
    $month = month2digit(substr($rpt_period, -2));
    if($month=="12")
    {
        $new_year = $year+1;
        $new_month = "01";
    }
    else{
        $new_year = $year;
        $new_month = intval($month)+1;
        $new_month = month2digit($new_month);
    }
    $next_period = $new_year."".$new_month;
    return $next_period;
}
function returnPeriodData($ship_code, $start_rpt_period,$to_rpt_period)
{
    $ship_name = getProjectNameFromCode($ship_code);
    $prev_year = substr($start_rpt_period, 0, 4);
    $cur_year  = substr($to_rpt_period, 0, 4);

    $prev_year_last2 = substr($prev_year, -2);
    $cur_year_last2  = substr($cur_year, -2);


    $prev_month         = month2digit(substr($start_rpt_period, -2));
    $cur_month          = month2digit(substr($to_rpt_period, -2));
    $dateObj            = DateTime::createFromFormat('!m', $prev_month);
    $prev_month_letters = $dateObj->format('M');
    $prev_full_month    = $dateObj->format('F');

    $dateObj           = DateTime::createFromFormat('!m', $cur_month);
    $cur_month_letters = $dateObj->format('M');
    $cur_full_month    = $dateObj->format('F');

    $data["prev_year"]          = $prev_year;
    $data["cur_year"]           = $cur_year;
    $data["prev_year_last2"]    = $prev_year_last2;
    $data["cur_year_last2"]     = $cur_year_last2;
    $data["prev_month"]         = $prev_month;
    $data["cur_month"]          = $cur_month;
    $data["prev_month_letters"] = $prev_month_letters;
    $data["cur_month_letters"]  = $cur_month_letters;
    $data["ship_name"]          = $ship_name;
    $data["prev_full_month"]    = $prev_full_month;
    $data["cur_full_month"]     = $cur_full_month;
    return $data;
}
function checkifArray($variable){
    if(is_array($variable)==false){
        return $variable;
    }
    else{
        $variable = "";
        return $variable;
    }
}
function fixExcelDateTime($date){
    //10/19/2016 1:42:47 pm
    //2016-19-20 13:42:47
    $data_date  = substr(trim($date), 0, 10);
    $time_part  = substr(trim($date), -10);
    $data_date  = fixExcelDate($data_date);
    $time       = date("H:i:s", strtotime($time_part));
    $final      = $data_date . " " . $time;
    return $final;
}
function fixExcelDate2DigitYear($date)
{
    //print "THis is the date. ".$date."<br>";
    if($date=="")
    {
        return "";
    }
    return  date('Y-m-d', strtotime($date));
}
function fixExcelDateMySQL($date)
{
    //print "THis is the date. ".$date."<br>";
    if($date=="")
    {
        return "0000-00-00";
    }
    return  date('Y-m-d', strtotime($date));
}
function removeCommanDollarSignParan($number){
    $number_no_sign = str_replace("$", "", $number);
    $no_comma = str_replace(",", "", $number_no_sign);
    $no_paran = str_replace(")", "", $no_comma);
    $make_neg = number_format(floatval(str_replace("(", "-", $no_paran)),4, ".","");
    return $make_neg;
}
function formatCurrencyNumber($number){
    if($number!="" or $number !=0){
        $value = number_format($number,2,".",",");
        $number = htmlentities("$".$value);
        return $number;
    }
    return "";
}
function formatNumber($number){
    if($number!="" or $number !=0){
        $value = number_format($number,2,".",",");
        $number = $value;
        return $number;
    }
    return "";
}function formatNumberNoComma($number){
    if($number!="" or $number !=0){
        $value = number_format($number,2,".","");
        $number = $value;
        return $number;
    }
    return 0;
}
function formatNumber4decNoComma($number){
    $no_comma = str_replace(",", "", $number);
    $no_sign = str_replace("$", "", $no_comma);

    $value    = number_format($no_sign, 4, ".", "");
    if($value ==""){
        $value = 0;
    }
    return $value;
}
function formatNumber4decCobra($number){

    $value    = number_format($number, 4, ".", "");
    if($value ==""){
        $value = 0;
    }
    return $value;
}
function formatNumberPHPEXCEL($number){
    if($number!="" or $number !=0){

        $value = number_format($number,0,".","");
        $number = $value;
        return $number;
    }
    return "";
}
function formatPercent($number){
    if($number!="" or $number !=0){

        $value = number_format($number,4,".",",");

        $number = $value*100;

        return $number."%";
    }
    return "";
}
function formatPercentPHPEXCEL($number){
    if($number!="" or $number !=0){

        $number = number_format($number,4,".","");

        return $number."";
    }
    return "";
}
function deleteShipFromTable($ship_code,$table_name, $schema)
{
    $sql = "delete from $schema.$table_name where ship_code = $ship_code";
    //print $sql;
    $junk = dbCall($sql,$schema);
}
function checkIfTableExists($schema, $table_name){
    $sql = "select table_name from information_schema.tables where table_schema = '$schema' and table_name = '$table_name'";
    //print $sql;
    $rs = dbcall($sql, "information_schema");
    $val = $rs->fields["table_name"];
    if($val==""){
        return "create_table";
    }else{
        return true;
    }
}
function createTableFromBase($schema,$base_table, $new_table_name){
    $sql = "show create table $schema.$base_table";
    $rs = dbcall($sql);
    $create_table_stmt = $rs->fields["Create Table"];

    $sql = str_replace($base_table, $new_table_name, $create_table_stmt);
    $junk = dbCall($sql, $schema);
}
function clearDirectory($path2directory){
    $files = glob($path2directory."/*"); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }
}
function deleteFromTable($schema, $table,$field, $value)
{
    $sql = "delete from $schema.$table where $field = '$value'";
    $junk = dbCall($sql,$schema);
}
function threeLetterMonth2Number($month){
    //print $month."<br>";
    $date_array["Jan"] = "01";
    $date_array["Feb"] = "02";
    $date_array["Mar"] = "03";
    $date_array["Apr"] = "04";
    $date_array["May"] = "05";
    $date_array["Jun"] = "06";
    $date_array["Jul"] = "07";
    $date_array["Aug"] = "08";
    $date_array["Sep"] = "09";
    $date_array["Oct"] = "10";
    $date_array["Nov"] = "11";
    $date_array["Dec"] = "12";

    return $date_array[$month];
}
function insertCobraCurData($ship_code, $rpt_period, $schema){
    $table_name   = $rpt_period . "_stage";
    $create_table = checkIfTableExists("cost2", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("cost2","template_cost", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);

}


function getListOfFileNamesInDirectory($directory){
    //print $directory;
    foreach (scandir($directory) as $file) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;

        $files[] = $file;
    }
    return $files;
}
function currentRPTPeriod(){
    $day = date("d");
    $month = intval(date("m"));
    $year = date("Y");

    if($day>22)
    {
        $month = $month;
    }
    else{
        $month = $month-1;
    }

    if($month == 0){
        $year = $year-1;
        $month = 12;
    }
    $month = month2digit($month);
    $rpt_period = "$year"."$month";
    return $rpt_period;
}
function returnSOCName($soc){
    $name = "";
    switch ($soc) {
        case 1:
            $name = "Fab/Subassembly";
        break;
        case 2:
            $name = "Module/Construct";
        break;
        case 3:
            $name = "Blast/Paint";
        break;
        case 4:
            $name = "Post Paint Preoutfit";
        break;
        case 5:
            $name = "Module Erect";
        break;
        case 6:
            $name = "ship compl- Pre launch";
        break;
        case 7:
            $name = "Launch";
        break;
        case 8:
            $name = "Ship compl - post Launch";
        break;
        case 9:
            $name = "Shipboard Testing";
        break;
        case 10:
            $name = "Gigs";
        break;
        case 11:
            $name = "SW Work LOE";
        break;
            case 17:
            $name = "Change Discrete";
        break;

        default:
    }
    return $name;
}

function eacColor($eac,$tcpi,$cpi, $cum_cv, $vac,$most_likely,$percent_complete,$percent_spent,$bac, $best_case)
{
    //this function will perform 5 test to determine the color of the eac.
    //will return an array,
    // 1st element will be the background color
    //2nd element will be the color of the font

    $red            = getColorCode('red');
    $yellow         = getColorCode('yellow');
    $green          = getColorCode('lime');

    //handle conditions where there is no performance
    if($percent_complete=='100' or $percent_complete=='0')
    {
        $color = "white";
        $font = 'black';

        $bg_and_font = array($color, $font);
    }
    else
    {
        $i = 1;
        $passed = 0;
        //TEST NO. 1 TCPI, CPI-- |((TCPIeac - CPIcum) * 100)|
        $test_1 = abs(($tcpi -$cpi) * 100);
        if($test_1 <= 5)
        {
            $passed = $i;
        }
        //TEST No. 2-- |(((EAC - MOST LIKELY) / EAC) * 100)|
        $test_2 = abs((($eac - $most_likely)/$eac)*100);
        if ($test_2<=5)
        {
            $passed = $passed + $i;
        }
        //TEST NO.  3-- |(((Percent Complete)/ Percent Spent) - (BAC/EAC)) * 100)|
        $test_3 = abs((($percent_complete/$percent_spent)-($bac/$eac))* 100);
        if ($test_3<=5)
        {
            $passed = $passed + $i;
        }
        //TEST NO. 4-- Comparison    (Both CVcum and VAC are <0 and CVcum > VAC) or (Both CVcum and VAC are >0 and CVcum < VAC)
        if(($cum_cv<0 and $vac<0 and $cum_cv>$vac) or ($cum_cv>0 and $vac>0 and $cum_cv<$vac))
        {
            $passed = $passed + $i;
        }
        //TEST NO. 5 =  EAC > BEST CASE
        if($eac>=$best_case) $passed = $passed + 1;

        //evaluate passed and assign default bg and font color for meatball/eac
        if($passed>=4) $bg_and_font = array($green, 'black');
        if($passed==3) $bg_and_font = array($yellow, 'black');
        if($passed<3) $bg_and_font = array($red, 'white');

        //if you are normally green, but fail test 5, you are yellow. and if you are yellow and fail test 5 you are RED.
        if($passed>=4 and $eac<$best_case)
        {
            $color = "$yellow";
            $font = 'black';

            $bg_and_font = array($color, $font);
        }

        if($passed==3 and $eac<$best_case)
        {
            $color = "$red";
            $font = 'white';

            $bg_and_font = array($color, $font);
        }
    }
    return $bg_and_font;
}

function getColorCode($common_name_for_color)
{
    //purpose: to unify Premier's color scheme
    /*
        $red    = getColorCode('red');
        $yellow = getColorCode('yellow');
        $green  = getColorCode('lime');
        $blue   = getColorCode('royal blue');
        $white  = getColorCode('white');
        $black  = getColorCode('black');
    */

    $color = strtolower($common_name_for_color);

    //standard colors
    //these colors are commented out in the 'other colors' section
    if($color=='red') $color_code = '#FF0000';
    if($color=='yellow') $color_code = '#FFFF00';
    if($color=='lime') $color_code = '#00FF00'; //our green
    if($color=='white') $color_code = '#FFFFFF';
    if($color=='royal blue') $color_code = '#0182FF'; //our blue
    if($color=='black') $color_code = '#000000';

    //other colors
    //"*TITLE01*"   "REDS"
    if($color=='indian red') $color_code = "#CD5C5C";
    if($color=='light coral') $color_code = "#F08080";
    if($color=='salmon') $color_code = "#FA8072";
    if($color=='dark salmon') $color_code = "#E9967A";
    if($color=='light salmon') $color_code = "#FFA07A";
    if($color=='crimson') $color_code = "#DC143C";
    //if($color=='red') $color_code = "#FF0000";
    if($color=='fire brick') $color_code = "#B22222";
    if($color=='dark red') $color_code = "#8B0000";

    //"*TITLE02*"   "PINKS"
    if($color=='pink') $color_code = "#FFC0CB";
    if($color=='light pink') $color_code = "#FFB6C1";
    if($color=='hot pink') $color_code = "#FF69B4";
    if($color=='deep pink') $color_code = "#FF1493";
    if($color=='medium violet red') $color_code = "#C71585";
    if($color=='pale violet red') $color_code = "#DB7093";

    //"*TITLE03*"   "ORANGES"
    if($color=='light salmon') $color_code = "#FFA07A";
    if($color=='coral') $color_code = "#FF7F50";
    if($color=='tomato') $color_code = "#FF6347";
    if($color=='orange red') $color_code = "#FF4500";
    if($color=='dark orange') $color_code = "#FF8C00";
    if($color=='orange') $color_code = "#FFA500";

    //"*TITLE04*"   "YELLOWS"
    if($color=='gold') $color_code = "#FFD700";
    //if($color=='yellow') $color_code = "#FFFF00";
    if($color=='light yellow') $color_code = "#FFFFE0";
    if($color=='lemon chiffon') $color_code = "#FFFACD";
    if($color=='light goldenrod yellow') $color_code = "#FAFAD2";
    if($color=='papaya whip') $color_code = "#FFEFD5";
    if($color=='moccasin') $color_code = "#FFE4B5";
    if($color=='peach puff') $color_code = "#FFDAB9";
    if($color=='pale goldenrod') $color_code = "#EEE8AA";
    if($color=='khaki') $color_code = "#F0E68C";
    if($color=='dark khaki') $color_code = "#BDB76B";

    //"*TITLE05*"    "PURPLES"
    if($color=='lavender') $color_code = "#E6E6FA";
    if($color=='thistle') $color_code = "#D8BFD8";
    if($color=='plum') $color_code = "#DDA0DD";
    if($color=='violet') $color_code = "#EE82EE";
    if($color=='orchid') $color_code = "#DA70D6";
    if($color=='fuchsia') $color_code = "#FF00FF";
    if($color=='magenta') $color_code = "#FF00FF";
    if($color=='medium orchid') $color_code = "#BA55D3";
    if($color=='medium purple') $color_code = "#9370DB";
    if($color=='blue violet') $color_code = "#8A2BE2";
    if($color=='dark violet') $color_code = "#9400D3";
    if($color=='dark orchid') $color_code = "#9932CC";
    if($color=='dark magenta') $color_code = "#8B008B";
    if($color=='purple') $color_code = "#800080";
    if($color=='indigo') $color_code = "#4B0082";
    if($color=='slate blue') $color_code = "#6A5ACD";
    if($color=='dark slate blue') $color_code = "#483D8B";

    //"*TITLE06*"   "GREENS"
    if($color=='green yellow') $color_code = "#ADFF2F";
    if($color=='chartreuse') $color_code = "#7FFF00";
    if($color=='lawn green') $color_code = "#7CFC00";
    //if($color=='lime') $color_code = "#00FF00";
    if($color=='lime green') $color_code = "#32CD32";
    if($color=='pale green') $color_code = "#98FB98";
    if($color=='lightgreen') $color_code = "#90EE90";
    if($color=='medium spring green') $color_code = "#00FA9A";
    if($color=='spring green') $color_code = "#00FF7F";
    if($color=='medium sea green') $color_code = "#3CB371";
    if($color=='sea green') $color_code = "#2E8B57";
    if($color=='forest green') $color_code = "#228B22";
    if($color=='green') $color_code = "#008000";
    if($color=='dark green') $color_code = "#006400";
    if($color=='yellow green') $color_code = "#9ACD32";
    if($color=='olive drab') $color_code = "#6B8E23";
    if($color=='olive') $color_code = "#808000";
    if($color=='dark olive green') $color_code = "#556B2F";
    if($color=='medium aquamarine') $color_code = "#66CDAA";
    if($color=='dark sea green') $color_code = "#8FBC8F";
    if($color=='light sea green') $color_code = "#20B2AA";
    if($color=='dark cyan') $color_code = "#008B8B";
    if($color=='teal') $color_code = "#008080";

    //"*TITLE07*"   "BLUES"
    if($color=='aqua') $color_code = "#00FFFF";
    if($color=='cyan') $color_code = "#00FFFF";
    if($color=='light cyan') $color_code = "#E0FFFF";
    if($color=='pale turquoise') $color_code = "#AFEEEE";
    if($color=='aquamarine') $color_code = "#7FFFD4";
    if($color=='turquoise') $color_code = "#40E0D0";
    if($color=='medium turquoise') $color_code = "#48D1CC";
    if($color=='dark turquoise') $color_code = "#00CED1";
    if($color=='cadet blue') $color_code = "#5F9EA0";
    if($color=='steel blue') $color_code = "#4682B4";
    if($color=='light steel blue') $color_code = "#B0C4DE";
    if($color=='powder blue') $color_code = "#B0E0E6";
    if($color=='light blue') $color_code = "#ADD8E6";
    if($color=='sky blue') $color_code = "#87CEEB";
    if($color=='light sky blue') $color_code = "#87CEFA";
    if($color=='deep sky blue') $color_code = "#00BFFF";
    if($color=='dodger blue') $color_code = "#1E90FF";
    if($color=='cornflower blue') $color_code = "#6495ED";
    if($color=='medium slate blue') $color_code = "#7B68EE";
    if($color=='royal blue') $color_code = "#4169E1";
    if($color=='blue') $color_code = "#0000FF";
    if($color=='medium blue') $color_code = "#0000CD";
    if($color=='dark blue') $color_code = "#00008B";
    if($color=='navy') $color_code = "#000080";
    if($color=='midnight blue') $color_code = "#191970";
    //if($color=='royal blue') $color_code = "#0182FF";

    //"*TITLE08*"   "BROWNS"
    if($color=='cornsilk') $color_code = "#FFF8DC";
    if($color=='blanched almond') $color_code = "#FFEBCD";
    if($color=='bisque') $color_code = "#FFE4C4";
    if($color=='navajo white') $color_code = "#FFDEAD";
    if($color=='wheat') $color_code = "#F5DEB3";
    if($color=='burly wood') $color_code = "#DEB887";
    if($color=='tan') $color_code = "#D2B48C";
    if($color=='rosy brown') $color_code = "#BC8F8F";
    if($color=='sandy brown') $color_code = "#F4A460";
    if($color=='goldenrod') $color_code = "#DAA520";
    if($color=='dark goldenrod') $color_code = "#B8860B";
    if($color=='peru') $color_code = "#CD853F";
    if($color=='chocolate') $color_code = "#D2691E";
    if($color=='saddle brown') $color_code = "#8B4513";
    if($color=='sienna') $color_code = "#A0522D";
    if($color=='brown') $color_code = "#A52A2A";
    if($color=='maroon') $color_code = "#800000";

    //"*TITLE09*"   "WHITES"
    //if($color=='white') $color_code = "#FFFFFF";
    if($color=='snow') $color_code = "#FFFAFA";
    if($color=='honeydew') $color_code = "#F0FFF0";
    if($color=='mint cream') $color_code = "#F5FFFA";
    if($color=='azure') $color_code = "#F0FFFF";
    if($color=='alice blue') $color_code = "#F0F8FF";
    if($color=='ghost white') $color_code = "#F8F8FF";
    if($color=='white smoke') $color_code = "#F5F5F5";
    if($color=='seashell') $color_code = "#FFF5EE";
    if($color=='beige') $color_code = "#F5F5DC";
    if($color=='old lace') $color_code = "#FDF5E6";
    if($color=='floral white') $color_code = "#FFFAF0";
    if($color=='ivory') $color_code = "#FFFFF0";
    if($color=='antique white') $color_code = "#FAEBD7";
    if($color=='linen') $color_code = "#FAF0E6";
    if($color=='lavender blush') $color_code = "#FFF0F5";
    if($color=='misty rose') $color_code = "#FFE4E1";

    //"*TITLE10*"   "GREYS"
    if($color=='gainsboro') $color_code = "#DCDCDC";
    if($color=='gray80') $color_code = "#CCCCCC";
    if($color=='light grey') $color_code = "#D3D3D3";
    if($color=='silver') $color_code = "#C0C0C0";
    if($color=='dark gray') $color_code = "#A9A9A9";
    if($color=='gray') $color_code = "#808080";
    if($color=='dim gray') $color_code = "#696969";
    if($color=='dim gray rev') $color_code = "#969696"; // pp window
    if($color=='gray88') $color_code = "#E0E0E0"; // pp window
    if($color=='gray91') $color_code = "#E8E8E8";
    if($color=='steady gray') $color_code = "#E9E9E9";
    if($color=='light slate gray') $color_code = "#778899";
    if($color=='slate gray') $color_code = "#708090";
    if($color=='dark slate gray') $color_code = "#2F4F4F";
    //if($color=='black') $color_code = "#000000";

    return $color_code;
}
function cpispiColors($the_value,$type='',$return_font_color_also=false)
{
    $red    = getColorCode('red');
    $yellow = getColorCode('yellow');
    $green  = getColorCode('lime');
    $blue   = getColorCode('royal blue');
    $white  = getColorCode('white');
    $black  = getColorCode('black');

    if(strtolower($type)=='tcpi')
    {
        //if($the_value <=.89) {$bgcolor='#0182ff'; $font_color='#ffffff';}
        if($the_value <=.89) {$bgcolor=$red; $font_color=$white;}
        if($the_value>=.90 and $the_value<=.95) {$bgcolor=$yellow; $font_color=$black;}
        if($the_value>=.96 and $the_value<=1.05) {$bgcolor=$green; $font_color=$black;}
        if($the_value>=1.06) {$bgcolor=$red; $font_color=$white;}
    }
    else if(strtolower($type)=='tcpi_cpi_relationship')
    {
        if($the_value<-11) {$bgcolor=$red; $font_color=$white;}
        if($the_value>=-11 and $the_value<-5) {$bgcolor=$yellow; $font_color=$black;}
        if($the_value>=-5 and $the_value<=5) {$bgcolor=$green; $font_color=$black;}
        if($the_value>5 and $the_value<=11) {$bgcolor=$yellow; $font_color=$black;}
        if($the_value>11) {$bgcolor=$red; $font_color=$white;}
    }
    else
    {
        if($the_value <= .89) {$bgcolor=$red; $font_color=$white;}
        if($the_value>=.90 and $the_value<=.95) {$bgcolor=$yellow; $font_color=$black;}
        if($the_value>=.96 and $the_value<=1.05) {$bgcolor=$green; $font_color=$black;}
        if($the_value>=1.06) {$bgcolor=$blue; $font_color=$white;}
    }
    if($bgcolor=='') $bgcolor=$green;
    if($font_color=='') $font_color = $black;

    //handle conditions where there is no performance
    if((number_format(abs($the_value),2)=='0.00' and strtolower($type)!='tcpi_cpi_relationship') or (abs($the_value)==100 and strtolower($type)=='tcpi_cpi_relationship'))
    {
        $bgcolor='white';
        $font_color = $black;
    }

    if($return_font_color_also==false)
    {
        return $bgcolor;
    }
    else
    {
        return array($bgcolor,$font_color);
    }
}
function processJustification($justification){
    $justification = trim($justification);
    $justification = str_replace("\"", "'", $justification);
    $justification = str_replace("\t", '', $justification); // remove tabs
    $justification = str_replace("\n", '', $justification); // remove new lines
    $justification = str_replace("\r", '', $justification);
    return $justification;
}
function getMonthEndDay($rpt_period){
    $sql = "select month_end from fmm_evms.calendar where rpt_period = $rpt_period";
    $rs  = dbCall($sql, "fmm_evms");

    $day = $rs->fields["month_end"];
    return $day;
}
function createSQLUtilCMDFile($ship_code,$path2CobraAPI,$g_path2CMD,$g_path2BAT)
{
    $content            = file_get_contents($g_path2CMD . "COBRASQLUtilTemplate.cmd");
    $new_cmd_file_name  = $g_path2CMD . "" . $ship_code . "_sqlUtil.cmd";
    print $new_cmd_file_name;
    $path2_new_bat_file = $g_path2BAT. $ship_code . "_sqlUtil.BAT";
    $content_cobra_api  = str_replace("####", $path2CobraAPI, $content);
    $content_final      = str_replace("****", $path2_new_bat_file, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);
    return $new_cmd_file_name;
}
function createSQLUtilBatFile($sql,$ship_code, $g_path2BAT)
{
    $content                   = file_get_contents($g_path2BAT . "COBRASQLUtilTemplate.bat");
    $sql_command     = str_replace("####", $sql, $content);
    $path2_new_bat_file        = $g_path2BAT . $ship_code . "_sqlUtil.BAT";
    file_put_contents($path2_new_bat_file,$sql_command);
    return $path2_new_bat_file;
}
function runSQLCommandUtil($ship_code,$sql, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,$debug=false)
{
    createSQLUtilBatFile($sql,$ship_code, $g_path2BAT);
    $cmd_file   = createSQLUtilCMDFile($ship_code,$g_path2CobraAPI,$g_path2CMD,$g_path2BAT);
    if($debug==false)
    {
        print $cmd_file;
        exec($cmd_file);
    }

}