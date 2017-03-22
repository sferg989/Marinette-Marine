<?php
include("../inc/inc.php");
include("../inc/inc.PHPExcel.php");
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$files = array();
$ship_code = 481;
$rpt_period= 201702;


$g_path2_mar_file = "C:/evms/CAM_Notebooks/04 - Material CAM Meetings, Material Reports/Material Reports/0481 LCS-21 Material Reports/Purchasing Material Tool/2017 LCS 21 Materials.xlsx";
function insertData($table_name, $path2file,$period){
    if($table_name=="open_po"){
        insertOpenPO($path2file, $period);
    }
    if($table_name=="committed_po"){
        insertCommittedPO($path2file, $period);
    }
    if($table_name=="gl_detail"){
        insertGLdetail($path2file, $period);
    }
}
function getListOfFileNamesInDirectory($directory){
    //print $directory;
    foreach (scandir($directory) as $file) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;

        $files[] = $file;
    }
    return $files;
}

function saveListOfFileNamesPHPExcel($file_name_array,$directory2Copy,$rel_path2_desitnation, $period, $table_name)
{

    foreach ($file_name_array as $value) {
        $path2XLSX    = "$directory2Copy\\$value";
        $csv_filename = savePHPEXCELCSV($value, $path2XLSX, $rel_path2_desitnation);
        $path2file    = "$rel_path2_desitnation\\$csv_filename";
        insertData($table_name, $path2file, $period);
        flush();
    }
}
function copyListOfDirectoryToCSV($g_path2_baan_work,$baan_dir_name,$rel_path2_reports, $period,$table_name){
        $directory2Copy  = $g_path2_baan_work . $baan_dir_name;
        $file_name_array = getListOfFileNamesInDirectory($directory2Copy);
        deleteFromTable("mars", $table_name,"period", $period);
        saveListOfFileNamesPHPExcel($file_name_array,$directory2Copy,$rel_path2_reports, $period, $table_name);
}
$rel_path2_reports = "../util/csv_pfa_open_po";

$open_po_directory      = "../util/csv_PFA_Open_PO";
$committed_po_directory = "../util/csv_PFA_Committed_PO";
$gl_detail_directory    = "../util/csv_PFA_GL_Detail";

$period = 201703;

//clearDirectory($open_po_directory);
//copyListOfDirectoryToCSV($g_path2_baan_work,"PFA_Open_PO",$open_po_directory, $period, "open_po");

//clearDirectory($committed_po_directory);
//copyListOfDirectoryToCSV($g_path2_baan_work,"PFA_Committed_PO",$committed_po_directory, $period, "committed_po");

//clearDirectory($gl_detail_directory);
//copyListOfDirectoryToCSV($g_path2_baan_work,"PFA_GL_Detail",$gl_detail_directory, $period, "gl_detail");
print $g_path2_mar_file;
die("made it");
$mars_file = loadPHPEXCELFile($g_path2_mar_file);
// Find the last cell in the second spreadsheet
$mars_file->setActiveSheetIndex(7);
$findEndDataRow = $mars_file->getActiveSheet->getHighestRow();
print "This is the end ".$findEndDataRow;
die("made it");