<?php
include("../../../inc/inc.php");
include("../../../inc/inc.PHPExcel.php");
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$files = array();



function saveListOfFileNamesPHPExcel($file_name_array,$directory2Copy,$rel_path2_desitnation,$table_name)
{
    foreach ($file_name_array as $value) {
        $path2XLSX    = "$directory2Copy\\$value";
        $csv_filename = savePHPEXCELCSV($value, $path2XLSX, $rel_path2_desitnation);
        $path2file    = "$rel_path2_desitnation\\$csv_filename";
        insertData($table_name, $path2file);
        flush();
    }
}

function copyListOfDirectoryToCSV($g_path2_baan_work,$baan_dir_name,$rel_path2_reports,$table_name){
        $directory2Copy  = $g_path2_baan_work . $baan_dir_name;
        $file_name_array = getListOfFileNamesInDirectory($directory2Copy);
        saveListOfFileNamesPHPExcel($file_name_array,$directory2Copy,$rel_path2_reports,$table_name);
}
//truncateTable("mars", "gl_detail");
truncateTable("mars", "committed_po");
truncateTable("mars", "open_po");

$rel_path2_reports = "../../../util/csv_pfa_open_po";
$open_po_directory      = "../../../util/csv_PFA_Open_PO";
$committed_po_directory = "../../../util/csv_PFA_Committed_PO";
$gl_detail_directory    = "../../../util/csv_PFA_GL_Detail";

clearDirectory($open_po_directory);
copyListOfDirectoryToCSV($g_path2_baan_work,"PFA_Open_PO",$open_po_directory, "open_po");

clearDirectory($committed_po_directory);
copyListOfDirectoryToCSV($g_path2_baan_work,"PFA_Committed_PO",$committed_po_directory, "committed_po");

/*
clearDirectory($gl_detail_directory);
copyListOfDirectoryToCSV($g_path2_baan_work,"PFA_GL_Detail",$gl_detail_directory, "gl_detail");*/