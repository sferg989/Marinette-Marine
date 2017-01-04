<?php
include("../../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */

/** Include PHPExcel */
/*Processing Steps
 * 1A. Create all the varibles and Paths Needed.
 * 1B. Make a copy of last Months Directories.
 * 2. Rename the new Copy's to the Current RPT Name.
 * 3. Loop through each Direcotry and Perform appropriate functions on the,
 *
 * */
$request_url = explode("/", $_SERVER['REQUEST_URI']);

if($control =="move_directories")
{/*
    *Step 1A.
   * $previousperiod and $curperiod come over URL.
* */

    $prev_year = substr($previousperiod, 0, 4);
    $cur_year  = substr($curperiod, 0, 4);

    $prev_year_last2 = substr($prev_year, -2);
    $cur_year_last2  = substr($cur_year, -2);

    $prev_month = month2digit(substr($previousperiod, -2));
    $cur_month  = month2digit(substr($curperiod, -2));
    $prev_month = intval($prev_month);
    $cur_month  = intval($cur_month);

    $dateObj            = DateTime::createFromFormat('!m', $prev_month);
    $prev_month_letters = $dateObj->format('M');

    $dateObj           = DateTime::createFromFormat('!m', $cur_month);
    $cur_month_letters = $dateObj->format('M');

    $array_of_dirs_to_change = array();
    $array_ofShipDirs        = array();


    $array_of_dirs_to_change[] = "Cobra Backups";
    $array_of_dirs_to_change[] = "Processing Checklists";
    $array_of_dirs_to_change[] = "Reconciliations";
    $array_of_dirs_to_change[] = "Working Files";

    $array_of_codes = explode(",",$ship_codes);

    foreach ($array_of_codes as $key=>$ship_code)
    {
        $ship_name = getProjectNameFromCode($ship_code);
        if(strlen($ship_code)==3)
        {
            $ship_code = "0".$ship_code;
        }
        $array_ofShipDirs[$ship_name."-".$ship_code] = $base_path."".$ship_name."/".$ship_code."/".$ship_code." ".$cur_year;
    }
    /*
     * If we are advanving to the next year
    */
    if($prev_year!=$cur_year)
    {
        makeNewYearDIR($array_ofShipDirs,$cur_year);
    }
    /*Loop through all the ships that are given!*/
    foreach ($array_ofShipDirs as $key =>$value)
    {
        $array = explode("-", $key);
        $ship_name = $array[0];
        $ship_code = $array[1];
        //MAKE SURE ship code has 4 digits.
        if(strlen($ship_code)==3)
        {
            $ship_code = "0".$ship_code;
        }
        $dir2copy       = $base_path."".$ship_name."/".$ship_code."/".$ship_code." ". $prev_year."/".$ship_code." ".$prev_month.".".$prev_year_last2." Cobra Processing";
        $new_location   = $base_path."".$ship_name."/".$ship_code."/".$ship_code." ". $cur_year."/".$ship_code." ".$cur_month.".".$cur_year_last2." Cobra Processing";
        if(file_exists($dir2copy)==false)
        {
            print "This Directory ".$dir2copy." Does not exist to copy!!";
            die("Made it");
        }
        if(file_exists($new_location)==true)
        {
            print "This Directory already exists!".$new_location." Cannot copy it again!";
            continue;
        }

        /*Step 1B. -- Copy the directory for new Month
        */

        recurse_copy($dir2copy,$new_location);
        /*loop through each directory and rename it to the new month*/
        foreach($array_of_dirs_to_change as $folder_name)
        {
            $previos_name = $new_location . "/" . $ship_code . " " . $prev_month_letters . " " . $prev_year . " " . $folder_name;
            $new_name     = $new_location . "/" . $ship_code . " " . $cur_month_letters . " " . $cur_year . " " . $folder_name;
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
                    /*Create Backups for the project*/
                    copyProjectFromCobra($ship_code,$new_name, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT);
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
                            $file_name =$fileinfo->getFilename();
                            $file_name = substr($file_name, 13);
                            $new_name = $path."/" . $ship_code . " " . $cur_month_letters . " " . $cur_year . " ".$file_name;
                            $full_path = $path."/".$fileinfo->getFilename();
                            rename($full_path,$new_name);
                        }
                    }
                    break;
            }
        }
    }
}
if($control=="project_grid")
{
    $data = "[";
    $sql = "select id, name, code from fmm_evms.master_project";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $pmid = $rs->fields["id"];
        $name = $rs->fields["name"];
        $code = $rs->fields["code"];
        $data.="{
            \"ID\":$pmid,
            \"project_select\":\"on\",
            \"project_name\":\"$name\",
            \"code\":\"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control =="project")
{

    if($q!="")
    {
        $wc = "where name like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select code, name from fmm_evms.master_project $wc order by code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $code = $rs->fields["code"];
        $name = $rs->fields["name"];
        $data.="      
        {
            \"id\": $code,
            \"text\": \"$name\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}

die("yup this worked");