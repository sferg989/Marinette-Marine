<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');

function returnInsertSqlPoApprovalLog(){
    $sql = "INSERT INTO po_approval_log (
        ship_code,
        date,
        week,
        po,
        buyer,
        wp,
        swbs,
        item,
        val,
        etc,
        `change`,
        qty,
        ebom,
        remaining,
        cam,
        reason_for_change,
        funding_source,
        other_notes)  VALUES";
    return $sql;
}
if($control=="upload"){

    $currentDir = getcwd();
    $uploadDirectory = "\uploads\\";
    $uploadDirectory = $g_path_to_util."uploads\\";
    clearDirectory($uploadDirectory);
    $errors = []; // Store all foreseen and unforseen errors here

    $fileExtensions = ['xlsx','jpg','png']; // Get all the file extensions

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
    truncateTable("meac", "po_approval_log");

    //$inputFileName = $upload_path . $filename;
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    //$objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($uploadPath);
    $insert_sql = returnInsertSqlPoApprovalLog();
    $sql = $insert_sql;
    for ($sheet_index = 0; $sheet_index <= 1; $sheet_index++) {
        $objPHPExcel->setActiveSheetIndex($sheet_index);
        $sheet = $objPHPExcel->getActiveSheet();
        //now do whatever you want with the active sheet

        $highest_row = $sheet->getHighestRow();
        $last_column = $sheet->getHighestColumn();
        for ($i = 2; $i <= $highest_row; $i++) {
            $col = "A";

            $ship_code         = intval($sheet->getCell($col++.$i)->getValue());
            $date              = fixExcelDateMySQL($sheet->getCell($col++.$i)->getFormattedValue());

            $week              = $sheet->getCell($col++.$i)->getFormattedValue();
            $po                = intval($sheet->getCell($col++.$i)->getValue());
            $buyer             = $sheet->getCell($col++.$i)->getValue();
            $wp                = $sheet->getCell($col++.$i)->getValue();
            $swbs              = intval($sheet->getCell($col++.$i)->getValue());
            $item              = $sheet->getCell($col++.$i)->getValue();
            $val               = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $etc               = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $change            = formatNumber4decNoComma($sheet->getCell($col++.$i)->getFormattedValue());
            $qty               = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $ebom              = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $remaining         = formatNumber4decNoComma($sheet->getCell($col++.$i)->getFormattedValue());
            $cam               = $sheet->getCell($col++.$i)->getValue();
            $reason_for_change = $sheet->getCell($col++.$i)->getValue();
            $funding_source    = $sheet->getCell($col++.$i)->getValue();
            $other_notes       = processDescription($sheet->getCell($col++.$i)->getFormattedValue());
            $sql .="
        (
        $ship_code,
        '$date',
        '$week',
        $po,
        '$buyer',
        '$wp',
        $swbs,
        '$item',
        $val,
        $etc,
       $change,
        $qty,
        $ebom,
        $remaining,
        '$cam',
        '$reason_for_change',
        '$funding_source',
        '$other_notes'),";
        }
    }
    $sql = substr($sql, 0, -1);
    print $sql;

    $junk = dbCall($sql,"meac");
    die();
}
if($control=="po_approval_grid"){
$sql = "    
    select 
    ship_code,
    date,
    week,
    po,
    buyer,
    wp,
    swbs,
    item,
    val,
    etc,
    `change`,
    qty,
    ebom,
    remaining,
    cam,
    reason_for_change,
    funding_source from po_approval_log ORDER BY date  DESC, week, `change`";
$rs = dbCall($sql,"meac");
    $data = "[";
    $id = 1;
    $total_diff = 0;
    while (!$rs->EOF)
    {
        $ship_code         = $rs->fields["ship_code"];
        $date              = $rs->fields["date"];
        $week              = $rs->fields["week"];
        $po                = $rs->fields["po"];
        $buyer             = $rs->fields["buyer"];
        $wp                = $rs->fields["wp"];
        $swbs              = $rs->fields["swbs"];
        $item              = $rs->fields["item"];
        $val               = $rs->fields["val"];
        $etc               = $rs->fields["etc"];
        $change            = $rs->fields["change"];
        $qty               = $rs->fields["qty"];
        $ebom              = $rs->fields["ebom"];
        $remaining         = $rs->fields["remaining"];
        $cam               = $rs->fields["cam"];
        $reason_for_change = $rs->fields["reason_for_change"];
        $funding_source    = $rs->fields["funding_source"];
        
        $total_diff += $etc_diff;
        $data.="{
            \"id\"                  : $id,
            \"ship_code\"           : \"$ship_code\",
            \"date\"                : \"$date\",
            \"week\"                : $week,
            \"po\"                  : $po,
            \"buyer\"               : \"$buyer\",
            \"wp\"                  : \"$wp\",
            \"swbs\"                : \"$swbs\",
            \"item\"                : \"$item\",
            \"val\"                 : $val,
            \"etc\"                 : $etc,
            \"change\"              : $change,
            \"qty\"                 : $qty,
            \"ebom\"                : $ebom,
            \"remaining\"           : $remaining,
            \"cam\"                 : \"$cam\",
            \"reason_for_change\"   : \"$reason_for_change\",
            \"funding_source\"      : \"$funding_source\"
        },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}

