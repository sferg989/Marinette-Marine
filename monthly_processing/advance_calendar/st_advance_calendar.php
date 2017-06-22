<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$debug = false;
$month = date("m");
$year  = date("Y");
$rpt_period = $year."".$month;
$user = "fs11239";
$debug = false;
$sql = "select code, name from fmm_evms.master_project where active = 'true' and code in (0469,0477,0475,0485)";
print $sql;
$rs = dbCall($sql, "fmm_evms");
while (!$rs->EOF)
{
    $code      = $rs->fields["code"];
    $ship_name = $rs->fields["name"];
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

    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "before_month_end_processing_begins",$debug);

    reClassCobraProject($ship_code, $g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$debug);

    //this is the real one.  there were not enough characters allowed to have 'class check'
    $batch_rpt_name = $ship_code." Class Check";
    //$batch_rpt_name = $ship_code." Class";
    runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);

    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "bkup_after_reclass",$debug);

    $status_date_for_loe        = "0";
    $update_rates_fte           = "0";
    $sync_calendar              = "1";
    $rolling_wave_skip          = "1";
    $update_eac                 = "0";

    advanceCalendarCobraProject($ship_code,$g_path2CobraAPI,$g_path2AdvanceCalendarProjectCMD,$g_path2AdvanceCalendarProjectBAT,$status_date_for_loe,$update_rates_fte,$sync_calendar,$rolling_wave_skip, $update_eac,$debug);

    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "bkup_after_calendar_advance",$debug);


    $rs->MoveNext();
}


