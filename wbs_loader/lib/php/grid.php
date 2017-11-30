<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');

function rtnInsert(){
    $sql = "INSERT INTO fmm_evms.wbs (wbs, descrip, sow, ship_code)  VALUES";
    return $sql;
}
if($control=="upload"){
    $currentDir = getcwd();
    $uploadDirectory = "\uploads\\";
    $uploadDirectory = $g_path_to_util."uploads\\";
    clearDirectory($uploadDirectory);
    $errors = []; // Store all foreseen and unforseen errors here

    $fileExtensions = ['xlsx','jpg','png', "csv"]; // Get all the file extensions

    $fileName = $_FILES['myfile']['name'];
    $fileSize = $_FILES['myfile']['size'];
    $fileTmpName  = $_FILES['myfile']['tmp_name'];
    $fileType = $_FILES['myfile']['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));

    $uploadPath = $uploadDirectory . basename($fileName);

    if (isset($fileName)) {

        if (! in_array($fileExtension,$fileExtensions)) {
            $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
        }

        /*    if ($fileSize > 2000000) {
                $errors[] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
            }*/

        if (empty($errors)) {
            $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

            if ($didUpload) {
                //echo "The file " . basename($fileName) . " has been uploaded";
            } else {
                //echo "An error occurred somewhere. Try again or contact the admin";
            }
        } else {
            foreach ($errors as $error) {
                //echo $error . "These are the errors" . "\n";
            }
        }
    }
    deleteFromTable("fmm_evms","wbs","ship_code", $ship_code);

    //$inputFileName = $upload_path . $filename;
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    //$objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($uploadPath);
    $insert_sql = rtnInsert();
    $sql = $insert_sql;
    $objPHPExcel->setActiveSheetIndex(1);
    $sheet = $objPHPExcel->getActiveSheet();
    //now do whatever you want with the active sheet

    $highest_row = $sheet->getHighestRow();
    $last_column = $sheet->getHighestColumn();
    for ($i = 5; $i <= $highest_row; $i++) {
        $col = "A";
        if($sheet->getCell("A". $i)->getFormattedValue()!=""){
            $wbs         = $sheet->getCell("A". $i)->getValue();
        }

        $description = processDescription($sheet->getCell("B". $i)->getValue());
        $sow         = $sheet->getCell("C". $i)->getValue();

        $sql .="
    (
    '$wbs',
    '$description',
    '$sow',
    $ship_code
    ),";
        if($i % 500 == 0){
            $sql = substr($sql, 0, -1);
            print $sql;
            $junk = dbCall($sql,"meac");
            $sql = $insert_sql;
        }
    }
    if($i % 500 != 0){
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql,"meac");
    }
    die();
}
