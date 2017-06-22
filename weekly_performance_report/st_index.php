<?php

require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 1/26/2017
 * Time: 1:16 PM
 */

function deleteTotalLines($field, $table_name)
{
    $sql = "delete from weekly_performance_report.$table_name where $field like '%total%'";
    $junk = dbCall($sql,"weekly_performance_report");
}
function deleteSummaryTable($period)
{
    $sql = "delete from weekly_performance_report.summary where period like '%$period%'";
    $junk = dbCall($sql,"weekly_performance_report");
    print $sql;
}

function loadCSVSintoCrossHullTable($path2file, $table_name){
    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
        insert into weekly_performance_report.$table_name (
                project,
                provider,
                clin,
                effort,
                activity,
                ca,
                ecp_rea,
                swbs,
                `group`,
                soc,
                owning_org,
                resource,
                planning_unit,
                sequence,
                fm,
                wo,
                item,
                op,
                scope,
                task,
                progress,
                bac,
                estimate,
                p2bac,
                p2est,
                a,
                etc,
                target,
                provider_target,
                provider_a,
                eac,
                bac_cpi,
                est_cpi,
                bl_start,
                bl_finish,
                f_start,
                f_finish,
                parent_operation,
                prev_a,
                prev_provider_a,
                prev_etc,
                prev_eac,
                eac_growth,
                period) 
        values ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {

            $project     = intval($data[0]);
            $provider    = intval($data[1]);
            $clin        = intval($data[2]);
            $effort      = trim($data[3]);
            $activity    = addslashes(trim($data[4]));
            $ca          = addslashes(trim($data[5]));
            $ecp_rea     = $data[6];
            $change_code = $data[7];
            $swbs             = $data[8];
            $group            = $data[9];
            $soc              = intval($data[10]);
            $owning_org       = $data[11];
            $rsrc             = $data[12];
            $planning_unit    = $data[13];
            $sequence         = $data[14];
            $fm               = $data[15];
            $wo               = intval($data[16]);
            $item             = trim($data[17]);
            $op               = $data[18];
            $scope            = addslashes(trim($data[19]));
            $task             = intval($data[20]);
            $progress         = formatNumber4decNoComma($data[21]);
            $bac              = formatNumber4decNoComma($data[22]);
            $estimate         = formatNumber4decNoComma($data[23]);
            $p2bac            = formatNumber4decNoComma($data[24]);
            $p2est            = formatNumber4decNoComma($data[25]);
            $a                = formatNumber4decNoComma($data[26]);
            $etc              = formatNumber4decNoComma($data[27]);
            $target           = formatNumber4decNoComma($data[28]);
            $provider_target  = formatNumber4decNoComma($data[29]);
            $provider_a       = formatNumber4decNoComma($data[30]);
            $eac              = formatNumber4decNoComma($data[31]);
            $bac_cpi          = formatNumber4decNoComma($data[32]);
            $est_cpi          = formatNumber4decNoComma($data[33]);
            $bl_start         = fixExcelDateMySQL($data[34]);
            $bl_finish        = fixExcelDateMySQL($data[35]);
            $f_start          = fixExcelDateMySQL($data[36]);
            $f_finish         = fixExcelDateMySQL($data[37]);
            $parent_operation = trim($data[38]);
            $prev_a           = formatNumber4decNoComma($data[39]);
            $prev_provider_a  = formatNumber4decNoComma($data[40]);
            $prev_etc         = formatNumber4decNoComma($data[41]);
            $prev_eac         = formatNumber4decNoComma($data[42]);
            $eac_growth       = formatNumber4decNoComma($data[43]);
            $period           = 20170429;
        $sql.=
            "(
                $project,
                $provider,
                $clin,
                '$effort',
                '$activity',
                '$ca',
                '$ecp_rea',
                '$swbs',
                '$group',
                $soc,
                '$owning_org',
                '$rsrc',
                '$planning_unit',
                '$sequence',
                '$fm',
                $wo,
                '$item',
                '$op',
                '$scope',
                $task,
                $progress,
                $bac,
                $estimate,
                $p2bac,
                $p2est,
                $a,
                $etc,
                $target,
                $provider_target,
                $provider_a,
                $eac,
                $bac_cpi,
                $est_cpi,
                '$bl_start',
                '$bl_finish',
                '$f_start',
                '$f_finish',
                '$parent_operation',
                $prev_a,
                $prev_provider_a,
                $prev_etc,
                $prev_eac,
                $eac_growth,
                $period
            ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "weekly_performance_report");
            print $sql;
            print "<br> break";
            print "<br>";
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "weekly_performance_report");
    }
}

$rel_path2_reports      = 'C:\xampp\htdocs\fmg\util\csv_weekly_performance_report';
$g_path2_perform_report = "D:\\";
$current_week           = "20170506";
$directory              = $g_path2_perform_report."".$current_week;
print $directory;
if (! is_dir($directory)) {
    exit('Invalid diretory path');
}
/*
 * THis is the directory on the Y Drive that has all the weekly performance reports.
 * */
$files = array();
foreach (scandir($directory) as $file) {
    if ('.' === $file) continue;
    if ('..' === $file) continue;

    $files[] = $file;
}
var_dump($files);

clearDirectory($rel_path2_reports);
foreach ($files as $key=>$value){

    $path2xlsfile =$directory."\\".$value;

    $file_name = substr($value, 0, -5);
    savePHPEXCELCSV($file_name,$path2xlsfile,$rel_path2_reports);
    print $path2xlsfile;
    die("made it");
    flush();
}

/*Loop through the UTIL CSV files and load them all into the table.
*/
$csvfiles =     array();
foreach (scandir($g_path2weeklyPerformanceCSV) as $file) {
    if ('.' === $file) continue;
    if ('..' === $file) continue;
    //print $file."<br>";
    $csvfiles[] = $file;

}

$table_name   = "z_".$current_week;
$create_table = checkIfTableExists("weekly_performance_report", $table_name);
if($create_table== "create_table"){
    createTableFromBase("weekly_performance_report","template_summary", $table_name);
}
foreach ($csvfiles as $key=>$value){
    $path2xlsfile = $g_path2weeklyPerformanceCSV . "/$value";
    $file_name    = substr($value, 0, -5);
    print $value."<br>";
    loadCSVSintoCrossHullTable($path2xlsfile, $table_name);
}
deleteTotalLines("effort", $table_name);
deleteTotalLines("provider", $table_name);
deleteTotalLines("owning_org", $table_name);
clearDirectory($rel_path2_reports);

