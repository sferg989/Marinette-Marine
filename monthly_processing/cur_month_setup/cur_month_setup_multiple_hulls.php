<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user       = $_SESSION["user_name"];
$debug      = true;
$rpt_period = 201802;
$code       = 481;

$ship_array = array();
//$ship_array[] = 465;
//$ship_array[] = 467;
//$ship_array[] = 469;
$ship_array[] = 471;
//$ship_array[] = 473;
//$ship_array[] = 475;

function createFolderPath($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = createFolderPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}

foreach ($ship_array as $code){

    if(strlen($code)==3)
    {
        $ship_code = "0".$code;
    }
    else{
        $ship_code = $code;
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


    $path2_cobra_dir    = $base_path."".$ship_name."/".$ship_code ;
    $previous_month_dir = $path2_cobra_dir."/".$ship_code . " " . $prev_year . "/" . $ship_code . " " . $prev_month . "." . $prev_year_last2 . " Cobra Processing";
    $cur_month_dir      = $path2_cobra_dir."/". $ship_code . " " . $cur_year . "/" . $ship_code . " " . $cur_month . "." . $cur_year_last2 . " Cobra Processing";

    $prev_path2CobraBkup = $previous_month_dir . "/" . $ship_code . " " . $prev_month_letters . " " . $prev_year . " Cobra Backups";
    $cur_path2CobraBkup  = $cur_month_dir . "/" . $ship_code . " " . $cur_month_letters . " " . $cur_year . " Cobra Backups";

    /*LIST OF STEPS*/
    /*LIST OF STEPS*/
    /*LIST OF STEPS*/
    /*
     * 1.  ARCHIVE PROJECT
     * 2.  BKUP PROJ
     * 3.  BKUP PROJ
     * 4.  RECLASS
     * 5.  RUN BATCH REPORT
     * 6.  BKUP PROJECT
     * 7.  ADVANCE CALENDAR
     * 8. BKUP PROJ
     * */

    /*STEP 1*/
    /*STEP 1*/
    /*STEP 1*/
    /*STEP 1
    MAKE NEW FOLDERS
    */

    //clearDirectory($g_path2CMDCurMonthSetup);

    $content            = file_get_contents($g_path2CMDCurMonthSetup . "templates\\template_cmd_cur_month_setup.cmd");
    $new_cmd_file_name  = $g_path2CMDCurMonthSetup . "" . $ship_code . "_cmd_cur_month_setup.cmd";

    $arhive_project_name = $ship_code."".$prev_month."".$prev_year_last2;
    $project_description = $ship_code." ".$prev_month_letters." ".$prev_year." Archive";


    $path2_new_bat_file = $g_path2BATCurMonthSetup. $ship_code . "_bat_cur_month_setup.BAT";
    $content_cobra_api  = str_replace("####", $g_path2CobraAPI, $content);
    $content_final      = str_replace("****", $path2_new_bat_file, $content_cobra_api);
    file_put_contents($new_cmd_file_name,$content_final);

    $path2_final_bkup        = $prev_path2CobraBkup . "/final";
    $path2_before_begin_bkup = $cur_path2CobraBkup . "/before_begins";
    $path2_after_reclass     = $cur_path2CobraBkup . "/after_reclass";
    $path2_after_cal_advance = $cur_path2CobraBkup . "/after_cal_advance";

    createFolderPath($prev_path2CobraBkup . "/final");
    createFolderPath($cur_path2CobraBkup . "/before_begins");
    createFolderPath($cur_path2CobraBkup . "/after_reclass");
    createFolderPath($cur_path2CobraBkup . "/after_cal_advance");


    $sql_array = array();
    if($ship_code=="0471"){
        $ship_code = "0471-";
    }
    $sql_array = updateCalendarSet($ship_code, $rpt_period);
    $sql_cal_update = implode(";", $sql_array);

    $content = file_get_contents($g_path2BATCurMonthSetup . "templates\\template_bat_cur_month_setup.bat");
    $content = str_replace("####", $ship_code, $content);
    $content = str_replace("****", $arhive_project_name, $content);
    $content = str_replace("^^^^", $project_description, $content);
    $content = str_replace("&&&&", $path2_final_bkup, $content);
    $content = str_replace("(((((", $path2_before_begin_bkup, $content);
    $content = str_replace(")))))", $path2_after_reclass, $content);
    $content = str_replace("+++++", $path2_after_cal_advance, $content);
    $content = str_replace("%%%%", $sql_cal_update, $content);

    file_put_contents($path2_new_bat_file,$content);
    exec($g_path2CMDCurMonthSetup . "" . $ship_code . "_cmd_cur_month_setup.cmd");
}

;
die("made it");

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

    //rename($previos_name,$new_name);
    //sleep(10);

    switch ($folder_name)
    {
        case "Cobra Backups":
            //deleteFilesfromDir($new_name);
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
                    //rename($full_path,$new_name);
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
                    //rename($full_path,$new_name);
                }
            }
            break;
    }

}

if($control =="bkup")
{

    if($ship_code=="0471"){
        $ship_code = "0471-";
    }
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,"final",$debug);
    die("Cobra Backup has been Created");
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