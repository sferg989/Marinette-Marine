<?php
include("../inc/inc.php");
include("../inc/inc.PHPExcel.php");


/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 1/26/2017
 * Time: 1:16 PM
 */

function deleteTotalLines($field)
{
    $sql = "delete from weekly_performance_report.summary where $field like '%total%'";
    $junk = dbCall($sql,"weekly_performance_report");
}
function deleteSummaryTable($period)
{
    $sql = "delete from weekly_performance_report.summary where period like '%$period%'";
    $junk = dbCall($sql,"weekly_performance_report");
    print $sql;
}

function loadCSVSintoCrossHullTable($path2file){
    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
        insert into weekly_performance_report.summary (
                project,
                provider,
                clin,
                effort,
                activity,
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
        $project          = intval($data[0]);
        $provider         = intval($data[1]);
        $clin             = intval($data[2]);
        $effort           = trim($data[3]);
        $activity         = addslashes(trim($data[4]));
        $ecp_rea          = $data[5];
        $swbs             = $data[6];
        $group            = $data[7];
        $soc              = intval($data[8]);
        $owning_org       = $data[9];
        $rsrc             = $data[10];
        $planning_unit    = $data[11];
        $sequence         = $data[12];
        $fm               = $data[13];
        $wo               = intval($data[14]);
        $item             = trim($data[15]);
        $op               = $data[16];
        $scope            = addslashes(trim($data[17]));
        $task             = intval($data[18]);
        $progress         = formatNumber4decNoComma($data[19]);
        $bac              = formatNumber4decNoComma($data[20]);
        $estimate         = formatNumber4decNoComma($data[21]);
        $p2bac            = formatNumber4decNoComma($data[22]);
        $p2est            = formatNumber4decNoComma($data[23]);
        $a                = formatNumber4decNoComma($data[24]);
        $etc              = formatNumber4decNoComma($data[25]);
        $target           = formatNumber4decNoComma($data[26]);
        $provider_target  = formatNumber4decNoComma($data[27]);
        $provider_a       = formatNumber4decNoComma($data[28]);
        $eac              = formatNumber4decNoComma($data[29]);
        $bac_cpi          = formatNumber4decNoComma($data[30]);
        $est_cpi          = formatNumber4decNoComma($data[31]);
        $bl_start         = fixExcelDateMySQL($data[32]);
        $bl_finish        = fixExcelDateMySQL($data[33]);
        $f_start          = fixExcelDateMySQL($data[34]);
        $f_finish         = fixExcelDateMySQL($data[35]);
        $parent_operation = trim($data[36]);
        $prev_a           = formatNumber4decNoComma($data[37]);
        $prev_provider_a  = formatNumber4decNoComma($data[38]);
        $prev_etc         = formatNumber4decNoComma($data[39]);
        $prev_eac         = formatNumber4decNoComma($data[40]);
        $eac_growth       = formatNumber4decNoComma($data[41]);
        $period           = "20170304";
        $sql.=
            "(
                $project,
                $provider,
                $clin,
                '$effort',
                '$activity',
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

$rel_path2_reports = "../util/csv_weekly_performance_report";
$directory = $base_path."Weekly Performance Reports/2017-03-04";
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

clearDirectory($rel_path2_reports);
foreach ($files as $key=>$value){

    $path2xlsfile =$directory."/$value";
    $file_name = substr($value, 0, -5);
    savePHPEXCELCSV($file_name,$path2xlsfile,$rel_path2_reports);
    flush();
}

/*Loop through the UTIL CSV files and load them all into the table.
*/
$csvfiles = array();
foreach (scandir($g_path2weeklyPerformanceCSV) as $file) {
    if ('.' === $file) continue;
    if ('..' === $file) continue;
    //print $file."<br>";
    $csvfiles[] = $file;

}
deleteSummaryTable(20170304);
foreach ($csvfiles as $key=>$value){
    $path2xlsfile = $g_path2weeklyPerformanceCSV . "/$value";
    $file_name    = substr($value, 0, -5);
    print $value."<br>";
    loadCSVSintoCrossHullTable($path2xlsfile);
}
deleteTotalLines("effort");
deleteTotalLines("provider");
