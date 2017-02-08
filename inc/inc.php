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
    $date_array = explode("-", $date);
    $day = $date_array[2];
    $month = $date_array[1];
    $year = $date_array[0];
    if($day<15)
    {
        $month = intval($date_array[1])-1;
        if($month==0)
        {
            $month = 12;
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


    //$arhive_project_name = $ship_code."".$cur_2digit_month."".$cur_2digit_year;
    $arhive_project_name = $ship_code.""."-test";

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
function returnPeriodData($ship_code, $start_rpt_period,$to_rpt_period)
{
    $ship_name = getProjectNameFromCode($ship_code);
    $prev_year = substr($start_rpt_period, 0, 4);
    $cur_year  = substr($to_rpt_period, 0, 4);

    $prev_year_last2 = substr($prev_year, -2);
    $cur_year_last2  = substr($cur_year, -2);


    $prev_month = month2digit(substr($start_rpt_period, -2));
    $cur_month  = month2digit(substr($to_rpt_period, -2));
    $dateObj            = DateTime::createFromFormat('!m', $prev_month);
    $prev_month_letters = $dateObj->format('M');
    $prev_full_month = $dateObj->format('F');

    $dateObj           = DateTime::createFromFormat('!m', $cur_month);
    $cur_month_letters = $dateObj->format('M');
    $cur_full_month = $dateObj->format('F');

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
