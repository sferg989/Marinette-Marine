<?php
include("../../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = $_SESSION["user_name"];
$debug = false;
if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$ship_name       = getProjectNameFromCode($ship_code);
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
$data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);

$prev_year          = $data["prev_year"];
$cur_year           = $data["cur_year"];
$prev_year_last2    = $data["prev_year_last2"];
$cur_year_last2     = $data["cur_year_last2"];
$prev_month         = $data["prev_month"];
$cur_month          = $data["cur_month"];
$prev_month_letters = $data["prev_month_letters"];
$cur_month_letters  = $data["cur_month_letters"];
$ship_name          = $data["ship_name"];
$array_of_dirs_to_change = array();

$array_of_dirs_to_change[] = "Cobra Backups";
$array_of_dirs_to_change[] = "Processing Checklists";
$array_of_dirs_to_change[] = "Reconciliations";
$array_of_dirs_to_change[] = "Working Files";

$path2_cobra_dir    = $base_path."".$ship_name."/".$ship_code ;
$previous_month_dir = $path2_cobra_dir."/".$ship_code . " " . $prev_year . "/" . $ship_code . " " . $prev_month . "." . $prev_year_last2 . " Cobra Processing";
$cur_month_dir      = $path2_cobra_dir."/". $ship_code . " " . $cur_year . "/" . $ship_code . " " . $cur_month . "." . $cur_year_last2 . " Cobra Processing";

$path2CobraBkup     = $previous_month_dir."/".$ship_code." ".$prev_month_letters." ". $prev_year." Cobra Backups";

if($control =="archive_project")
{
    //$ship_code= "04731216";

    if($ship_code=="0471"){
        $ship_code = "0471-";
    }
    archiveCobraProject($ship_code, $prev_month,$prev_year_last2,$prev_month_letters,$prev_year,$g_path2CobraAPI,$g_path2ArhiveProjectCMD,$g_path2ArhiveProjectBAT,$debug);
    die("Archive Prior Month Cobra Project");
}
if($control =="bkup")
{

    if($ship_code=="0471"){
        $ship_code = "0471-";
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,"final",$debug);
    die("Cobra Backup has been Created");
}

if($control =="new_folder")
{
    if($prev_year!=$cur_year)
    {
        print "made it";
        makeNewYearDIR($ship_name,$ship_code,$cur_year,$base_path);
    }
    if(file_exists($previous_month_dir)==false)
    {
        print "This Directory ".$previous_month_dir." Does not exist to copy!!";
        die();
    }
    if(file_exists($cur_month_dir)==true)
    {
        print "This Directory already exists!".$cur_month_dir." Cannot copy it again!";
        die();
    }

    recurse_copy($previous_month_dir,$cur_month_dir);

    /*loop through each directory and rename it to the new month*/
    foreach($array_of_dirs_to_change as $folder_name)
    {
        $previos_name = $cur_month_dir . "/" . $ship_code . " " . $prev_month_letters . " " . $prev_year . " " . $folder_name;
        $new_name     = $cur_month_dir . "/" . $ship_code . " " . $cur_month_letters . " " . $cur_year . " " . $folder_name;
        /*
         * Step 2 Rename the Directories.
         * */
        /*give the CPU time to create the copy and rename it.*/
        rename($previos_name,$new_name);
        sleep(10);

        switch ($folder_name)
        {
            case "Cobra Backups":
                deleteFilesfromDir($new_name);
                break;
            case "Reconciliations":
                $path  = $new_name;
                $dir = new DirectoryIterator(dirname($path."/*"));
                foreach ($dir as $fileinfo)
                {
                    if (!$fileinfo->isDot())
                    {
                        $file_name =$fileinfo->getFilename();
                        $file_name = substr($file_name, 13);
                        $new_name = $path."/" . $ship_code . " " . $cur_month_letters . " " . $cur_year . " ".$file_name;
                        $full_path = $path."/".$fileinfo->getFilename();
                        rename($full_path,$new_name);
                    }
                }
                break;
            case "Working Files":
                $path  = $new_name;
                $dir = new DirectoryIterator(dirname($path."/*"));
                foreach ($dir as $fileinfo)
                {
                    if (!$fileinfo->isDot())
                    {
                        $file_name = $fileinfo->getFilename();
                        $file_name = substr($file_name, 13);
                        $new_name  = $path . "/" . $ship_code . " " . $cur_month_letters . " " . $cur_year . " " . $file_name;
                        $full_path = $path . "/" . $fileinfo->getFilename();
                        rename($full_path,$new_name);
                    }
                }
                break;
        }
    }
    die("The New Folder has been Created.");
}

if($control=="bkup_reclass_report")
{
    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    if($ship_code=="0471"){
        $ship_code = "0471-";
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "before_month_end_processing_begins",$debug);


    reClassCobraProject($ship_code, $g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$debug);

    //this is the real one.  there were not enough characters allowed to have 'class check'
    $batch_rpt_name = $ship_code." Class Check";
    //$batch_rpt_name = $ship_code." Class";
    if($ship_code=="0471-"){
        $ship_code = "0471";
    }
    runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);
    die("BKUP-Reclass-Report have completed");
}

if($control=="bkup_advance_bkup")
{
    if(file_exists($path2CobraBkup)==false)
    {
        print "This Directory ".$path2CobraBkup." Does not exist!!  Go Back and Create this months Processing Folders!";
        die();
    }
    if($ship_code=="0471"){
        $ship_code = "0471-";
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
    die("bkup-advance-bkup has completed");
}

