<?php
include("../../../inc/inc.php");
include('../../../inc/inc.cobra.php');
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
function saveListOfFileNamesPHPExcelAndInsertETCLoadFile($file_name_array,$directory2Copy,$rel_path2_desitnation)
{
    foreach ($file_name_array as $value) {
        $path2XLSX    = "$directory2Copy/$value";
        print $path2XLSX;
        $csv_filename = savePHPEXCELCSV($value, $path2XLSX, $rel_path2_desitnation);
        $path2file    = "$rel_path2_desitnation\\$csv_filename";
        insertETCLOADFILE($path2file);
        flush();
    }
}
function copyListOfDirectoryToCSV($directory2Copy,$rel_path2_reports){
    $file_name_array = getListOfFileNamesInDirectory($directory2Copy);
    saveListOfFileNamesPHPExcelAndInsertETCLoadFile($file_name_array,$directory2Copy,$rel_path2_reports);
}

$array = array();
//$array[] = 465;
//$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
//$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;

foreach ($array as $value){
    deleteFromTable("MEAC", "swbs_gl_summary_stage", "ship_code", $value);
    insertSWBSSummaryStaging($value);
}
$rel_path2_reports = "../../../util/csv_etc_load_file";
$directory2Copy ="C:/evms/etc_load_file";
print $directory2Copy;
print $rel_path2_reports;
deleteFromTable("meac", "swbs_gl_summary_stage", "category", "Load File Entry");
clearDirectory($rel_path2_reports);
copyListOfDirectoryToCSV($directory2Copy,$rel_path2_reports);

foreach ($array as $value){
    deleteFromTable("MEAC", "swbs_gl_summary", "ship_code", $value);
    insertSWBSSummary($value);
}
deleteFromTableNotLike("MEAC", "swbs_gl_summary", "wp", "matl");