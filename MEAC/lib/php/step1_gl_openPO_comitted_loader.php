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
$rpt_period = 201710;
//duplicateTable("gl_detail", "mars", "201708_gl", "bkup");
//duplicateTable("committed_po", "mars", "201708_committed_po", "bkup");
//duplicateTable("open_po", "mars", "201708_open_po_bkup", "bkup");
//duplicateTable("open_buy", "mars", "201708_open_buy_bkup", "bkup");
//duplicateTable("ebom", "meac", "201708_ebom", "bkup");
//duplicateTable("swbs_gl_summary", "meac", "201708_swbs_gl_summary", "bkup");
//die("made it");

truncateTable("mars", "gl_detail");
truncateTable("mars", "committed_po");
truncateTable("mars", "open_po");

$rel_path2_reports = "../../../util/csv_pfa_open_po";
$open_po_directory      = "../../../util/csv_PFA_Open_PO";
$committed_po_directory = "../../../util/csv_PFA_Committed_PO";
$gl_detail_directory    = "../../../util/csv_PFA_GL_Detail";

$dir = $g_path2_baan_work;
$dir = "C:\\evms\\";

clearDirectory($open_po_directory);
copyListOfDirectoryToCSV($dir,"open_po",$open_po_directory, "open_po");

clearDirectory($committed_po_directory);
//copyListOfDirectoryToCSV($dir,"committed_pos",$committed_po_directory, "committed_po");

clearDirectory($gl_detail_directory);
copyListOfDirectoryToCSV($dir,"gl_detail",$gl_detail_directory, "gl_detail");

$create_table = checkIfTableExists("mars", $rpt_period."_open_po");
if($create_table === true){
    dropTable("mars", $rpt_period."_open_po");
    duplicateTable("open_po", "mars", $rpt_period."_open_po", "mars");
}
else{
    duplicateTable("open_po", "mars", $rpt_period."_open_po", "mars");
}

$create_table = checkIfTableExists("mars", $rpt_period."_committed_po");

if($create_table === true){
    dropTable("mars", $rpt_period."_committed_po");
    duplicateTable("committed_po", "mars", $rpt_period."_committed_po", "mars");
}
else{
    duplicateTable("committed_po", "mars", $rpt_period."_committed_po", "mars");
}

$create_table = checkIfTableExists("mars", $rpt_period."_gl_detail");
if($create_table === true){
    dropTable("mars", $rpt_period."_gl_detail");
    duplicateTable("gl_detail", "mars", $rpt_period."_gl_detail", "mars");
}
else{
    duplicateTable("gl_detail", "mars", $rpt_period."_gl_detail", "mars");

}
