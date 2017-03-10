<?php
include("../inc/inc.php");
include("../inc/lib/php/phpExcel-1.8/classes/phpexcel.php");
include("../inc/lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$files = array();
$rel_path2_reports = "../util/csv_baan_rpt";
$directory = $g_path2_baan_work;
function getListOfFileNamesInDirectory($directory){
    foreach (scandir($directory) as $file) {
        if ('.' === $file) continue;
        if ('..' === $file) continue;

        $files[] = $file;
    }
    return $files;
}
function saveListOfFileNamesPHPExcel($file_name_array,$path2xlsfile,$rel_path2_desitnation){
    foreach ($file_name_array as $value){
        savePHPEXCELCSV($value,$path2xlsfile,$rel_path2_desitnation);
        flush();
    }
}
$directories_2_copy[] ="PFA_Open_PO";
$directories_2_copy[] ="PFA_GL_Detail";
$directories_2_copy[] ="PFA_Committed_PO";
clearDirectory($rel_path2_reports);

foreach ($directories_2_copy as $value){
    $directory = $directory."\\".$value;
    $file_name_name_array = getListOfFileNamesInDirectory($directory);

    saveListOfFileNamesPHPExcel($file_name_name_array,$directory,$rel_path2_reports);
}