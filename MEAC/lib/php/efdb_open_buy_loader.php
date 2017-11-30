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

function saveListOfFileNamesPHPExcelAndInsertEBOM($file_name_array,$directory2Copy,$rel_path2_desitnation)
{
    foreach ($file_name_array as $value) {
        $path2XLSX    = "$directory2Copy/$value";
        print $path2XLSX;
        $csv_filename = savePHPEXCELCSV($value, $path2XLSX, $rel_path2_desitnation);
        $path2file    = "$rel_path2_desitnation\\$csv_filename";
        insertEFDBOpenBuy($path2file);
        //sleep(10);
        //flush();
    }
}
function copyListOfDirectoryToCSV($directory2Copy,$rel_path2_reports){
        $file_name_array = getListOfFileNamesInDirectory($directory2Copy);
        saveListOfFileNamesPHPExcelAndInsertEBOM($file_name_array,$directory2Copy,$rel_path2_reports);
}

$rel_path2_reports = "../../../util/csv_openbuy";
//$directory2Copy =$base_path."open_buy";
$directory2Copy ="C:/evms/efdb_open_buy";
$today = date("Ymd");

$destination_table = "z_".$today."_efdb_open_buy";
duplicateTable("efdb_open_buy", "mars", $destination_table, "z_meac");

truncateTable("mars", "efdb_open_buy");
print $directory2Copy;
print $rel_path2_reports;
clearDirectory($rel_path2_reports);
copyListOfDirectoryToCSV($directory2Copy,$rel_path2_reports);

//this remains for loading the open buy for month end MEAC
//duplicateTable("open_buy", "mars", "201709_open_buy", "mars");
