<?php

//require dirname(__FILE__) . "../../inc/inc.php";
//include("../../inc/inc.php");
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

$sql = "select code, name from fmm_evms.master_project where active = 'true' and code in (473, 475, 485)";
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
    print $code;
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
        //die();
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
                        if($file_name=="Thumbs.db"){
                            continue;
                        }
                        if(file_exists($full_path)==true)
                        {
                            rename($full_path,$new_name);
                        }
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

    //$ship_code= "04731216";
    archiveCobraProject($ship_code, $prev_month,$prev_year_last2,$prev_month_letters,$prev_year,$g_path2CobraAPI,$g_path2ArhiveProjectCMD,$g_path2ArhiveProjectBAT,$debug);
    sleep(120);
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT,"final",false);


    //die("made it");
    $rs->MoveNext();
}

