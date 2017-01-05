<?php
include("../../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;
if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$prev_rpt_period    = getPreviousRPTPeriod($rpt_period);
$data               = returnPeriodData($ship_code, $prev_rpt_period,$rpt_period);
$prev_year          = $data["prev_year"];
$cur_year           = $data["cur_year"];
$prev_year_last2    = $data["prev_year_last2"];
$cur_year_last2     = $data["cur_year_last2"];
$prev_month         = $data["prev_month"];
$cur_month          = $data["cur_month"];
$cur_month_letters  = $data["cur_month_letters"];
$ship_name          = $data["ship_name"];

$array_of_dirs_to_change = array();
$array_of_dirs_to_change[] = "Cobra Backups";
$array_of_dirs_to_change[] = "Processing Checklists";
$array_of_dirs_to_change[] = "Reconciliations";
$array_of_dirs_to_change[] = "Working Files";

$path2_cobra_dir    = $base_path . "" . $ship_name . "/" . $ship_code ;
$previous_month_dir = $path2_cobra_dir."/".$ship_code." ".$prev_year."/".$ship_code." ".$prev_month.".".$prev_year_last2." Cobra Processing";
$cur_month_dir      = $path2_cobra_dir."/".$ship_code." ".$cur_year."/".$ship_code." ".$cur_month.".".$cur_year_last2." Cobra Processing";

$path2CobraBkup     = $cur_month_dir."/".$ship_code." ".$cur_month_letters." ". $cur_year." Cobra Backups";

if($control=="step_grid")
{
    $data = "[";
    $sql = "select id,step as name, action from processing_status.advance_calender order by `order`";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $id = $rs->fields["id"];
        $name = $rs->fields["name"];
        $action = $rs->fields["action"];
        $data.="{
            \"id\"      :$id,
            \"name\"    :\"$name\",
            \"action\"  :\"$action\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($filter =="start_rpt_period")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select period from processing_status.ship $wc group by period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["period"];
        $data.="      
        {
            \"id\": $rpt_period,
            \"text\": \"$rpt_period\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="to_rpt_period")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }
    $data ="{\"items\": [";

    $sql = "select period from processing_status.ship $wc group by period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["period"];
        $data.="      
        {
            \"id\": $rpt_period,
            \"text\": \"$rpt_period\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($control =="bkup")
{
    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "before_month_end_processing_begins",$debug);
    die("Cobra Backup-Complete");
}
if($control =="bkup_after_calendar_advance")
{
    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "bkup_after_calendar_advance",$debug);
    die("Cobra Backup-Complete");
}
if($control =="bkup_after_reclass")
{
    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "bkup_after_reclass",$debug);
    die("Cobra Backup-Complete");
}
if($control=="ff")
{
    $ship_code           = "04731116-test";

    $source_class        = "Forecast";
    $target_class        = "FF";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Target";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Copy F1 Forecast to FF-Complete");

}
if($control =="reclass_PF2_ff")
{
    $ship_code           = "04731116-test";

    $source_class        = "PF";
    $target_class        = "FF";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Target";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Change PF class type From FF to Manual (retain ETC)-Complete");
}
if($control =="reclass_ff2PF")
{
    $ship_code           = "04731116-test";

    $source_class        = "Forecast";
    $target_class        = "PF";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Target";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Global Reclass - Copy Forecast to PF class-Complete");
}
if($control =="reclass_a2_pa")
{
    $ship_code           = "04731116-test";

    $source_class        = "Actual";
    $target_class        = "PA";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Actuals to PA-Complete");
}
if($control =="reclass_b2_pb")
{
    $ship_code           = "04731116-test";

    $source_class        = "Budget";
    $target_class        = "PB";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Current Budget to PB-Complete");
}
if($control =="reclass_ra2_pb")
{
    $ship_code           = "04731116-test";

    $source_class        = "RA";
    $target_class        = "PB";
    $target_action       = "Add";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("RA Class to the PB Class-Complete");
}
if($control =="reclass_ea2_pe")
{
    $ship_code           = "04731116-test";

    $source_class        = "EA";
    $target_class        = "PE";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("EA Class to the PE Class-Complete");
}
if($control =="batch_rpt_cost_class_check")
{
    $ship_code      = "04731116-test";

    $batch_rpt_name = $ship_code." Class Check";
    runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);
    die("batch Reports-Complete");
}
if($control=="advance_calendar")
{
    $ship_code           = "04731116-test";

    $status_date_for_loe        = "1";
    $update_rates_fte           = "1";
    $sync_calendar              = "1";
    $rolling_wave_skip          = "1";
    $update_eac                 = "1";

    advanceCalendarCobraProject($ship_code,$g_path2CobraAPI,$g_path2AdvanceCalendarProjectCMD,$g_path2AdvanceCalendarProjectBAT,$status_date_for_loe,$update_rates_fte,$sync_calendar,$rolling_wave_skip, $update_eac,$debug);
    die("Calendar Advance-Complete");
}

