<?php
include("../inc/inc.php");
include("../inc/inc.PHPExcel.php");


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

function loadCSVSWeeklyPerformanceRPT($path2file,$current_week){
    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
        insert into weekly_performance_report.summary (
                ship_code,
                provider,
                clin,
                effort,
                wp,
                ca,
                ecp_rea,
                change_code,
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
    $z=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $i                = 0;
        $ship_code        = intval($data[$i++]);
        $provider         = intval($data[$i++]);
        $clin             = intval($data[$i++]);
        $effort           = trim($data[$i++]);
        $wp               = addslashes(trim($data[$i++]));
        $ca               = addslashes(trim($data[$i++]));
        $ecp_rea          = $data[$i++];
        $change_code      = $data[$i++];
        $swbs             = $data[$i++];
        $group            = $data[$i++];
        $soc              = intval($data[$i++]);
        $owning_org       = $data[$i++];
        $rsrc             = $data[$i++];
        $planning_unit    = $data[$i++];
        $sequence         = $data[$i++];
        $fm               = $data[$i++];
        $wo               = intval($data[$i++]);
        $item             = trim($data[$i++]);
        $op               = $data[$i++];
        $scope            = addslashes(trim($data[$i++]));
        $task             = intval($data[$i++]);
        $progress         = formatNumber4decNoComma($data[$i++]);
        $bac              = formatNumber4decNoComma($data[$i++]);
        $estimate         = formatNumber4decNoComma($data[$i++]);
        $p2bac            = formatNumber4decNoComma($data[$i++]);
        $p2est            = formatNumber4decNoComma($data[$i++]);
        $a                = formatNumber4decNoComma($data[$i++]);
        $etc              = formatNumber4decNoComma($data[$i++]);
        $target           = formatNumber4decNoComma($data[$i++]);
        $provider_target  = formatNumber4decNoComma($data[$i++]);
        $provider_a       = formatNumber4decNoComma($data[$i++]);
        $eac              = formatNumber4decNoComma($data[$i++]);
        $bac_cpi          = formatNumber4decNoComma($data[$i++]);
        $est_cpi          = formatNumber4decNoComma($data[$i++]);
        $bl_start         = fixExcelDateMySQL($data[$i++]);
        $bl_finish        = fixExcelDateMySQL($data[$i++]);
        $f_start          = fixExcelDateMySQL($data[$i++]);
        $f_finish         = fixExcelDateMySQL($data[$i++]);
        $parent_operation = trim($data[$i++]);
        $prev_a           = formatNumber4decNoComma($data[$i++]);
        $prev_provider_a  = formatNumber4decNoComma($data[$i++]);
        $prev_etc         = formatNumber4decNoComma($data[$i++]);
        $prev_eac         = formatNumber4decNoComma($data[$i++]);
        $eac_growth       = formatNumber4decNoComma($data[$i++]);
        $period           = $current_week;
        $sql.=
            "(
                $ship_code,
                $provider,
                $clin,
                '$effort',
                '$wp',
                '$ca',
                '$ecp_rea',
                '$change_code',
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
        if($z == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "weekly_performance_report");
            print $sql;
            print "<br> break";
            print "<br>";
            $z=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $z++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($z !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "weekly_performance_report");
    }
}

$rel_path2_reports      = "../util/csv_weekly_performance_report";
$g_path2_perform_report = "D:\\";
$current_week           = "20180120";
$directory              = "$g_path2_perform_report$current_week";
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

clearDirectory($rel_path2_reports);
foreach ($files as $key=>$value){

    $path2xlsfile =$directory."/$value";
    $file_name = substr($value, 0, -5);
    //savePHPEXCELCSV($file_name,$path2xlsfile,$rel_path2_reports);
    //flush();
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
duplicateTable("summary", "weekly_performance_report", $table_name,"z_weekly_performance_report");
truncateTable("weekly_performance_report", "summary");
die("mdae it");

foreach ($csvfiles as $key=>$value){
    $path2xlsfile = $g_path2weeklyPerformanceCSV . "/$value";
    $file_name    = substr($value, 0, -5);
    print $value."<br>";
    loadCSVSWeeklyPerformanceRPT($path2xlsfile,$current_week);
}
deleteTotalLines("effort");
deleteTotalLines("provider");
deleteTotalLines("owning_org");

//clearDirectory($rel_path2_reports);

