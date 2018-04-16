<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\meac\lib\php\inc.insert_data.php');
require('C:\xampp\htdocs\fmg\meac\lib\php\inc.baan.fortis.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');

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
        if(is_dir("$directory2Copy\\$value")){
            continue;
        }
        else{
            $path2XLSX    = "$directory2Copy\\$value";
            $csv_filename = savePHPEXCELCSV($value, $path2XLSX, $rel_path2_desitnation);
            $path2file    = "$rel_path2_desitnation\\$csv_filename";
            insertData($table_name, $path2file);
            flush();
        }
    }
}

function copyListOfDirectoryToCSV($g_path2_baan_work,$baan_dir_name,$rel_path2_reports,$table_name){
    $directory2Copy  = $g_path2_baan_work . $baan_dir_name;
    $file_name_array = getListOfFileNamesInDirectory($directory2Copy);
    saveListOfFileNamesPHPExcel($file_name_array,$directory2Copy,$rel_path2_reports,$table_name);
}

$date               = date('Ymd');
$destination_schema = "z_meac";
$source_table       = "wp_gl_detail";
$destination_table  = "z_" . $date . "_" . $source_table;
$create_table       = checkIfTableExists($destination_schema, $destination_table);

if($create_table== "create_table") {
    duplicateTable($source_table, "meac", $destination_table, $destination_schema);
}

$source_table = "wp_committed_po";
$destination_table = "z_".$date."_".$source_table;
$create_table = checkIfTableExists($destination_schema, $destination_table);
if($create_table== "create_table") {
    duplicateTable($source_table, "meac", $destination_table, $destination_schema);
}
//truncateTable("mars", "gl_detail");
//truncateTable("meac", "wp_gl_detail");
//truncateTable("mars", "committed_po");
//truncateTable("meac", "wp_committed_po");
//truncateTable("meac", "inv_transfers");
//truncateTable("meac", "master_buyer");
//truncateTable("meac", "cbm");


$committed_po_directory = 'C:\xampp\htdocs\fmg\util\csv_PFA_Committed_PO';
$gl_detail_directory    = 'C:\xampp\htdocs\fmg\util\csv_PFA_GL_Detail';


$dir = $g_path2_baan_work;


//clearDirectory($committed_po_directory);
//copyListOfDirectoryToCSV($dir,"PFA_Committed_PO",$committed_po_directory, "committed_po");

//clearDirectory($gl_detail_directory);
//copyListOfDirectoryToCSV($dir,"PFA_GL_Detail",$gl_detail_directory, "gl_detail");


$array = array();

$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;
$array[] = 487;

foreach ($array as $ship_code){
    deleteFromTable("meac", "inv_transfers", "ship_code", $ship_code);
    loadINVTranserfers($ship_code);
}
foreach ($array as $ship_code){
    if(strlen($value)==3)
    {
        $ship_code = "0".$ship_code;
    }
    deleteFromTable("MEAC", "cbm", "ship_code", $ship_code);
    print $ship_code;
    insertCBMFromBaan($ship_code);
}

deleteFromTable("meac", "cbm", "material", "");
loadBaanBuyerIDList();

foreach ($array as $ship_code){
    deleteFromTable("MEAC", "buyer_reponsible", "ship_code", $ship_code);
    loadResponsibleBuyer($ship_code);
}
loaditem2buyer();
insertGLdetailWITHWP();
insertCommittedPOWP();

die("mad eit");
