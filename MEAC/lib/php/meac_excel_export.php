<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 7/19/2017
 * Time: 9:26 AM
 */
include('inc.insert_data.php');
include('inc.meac.excel.export.php');
include('../../../inc/inc.php');
include('../../../inc/inc.PHPExcel.php');
include("../../../inc/lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");

$user = $_SESSION["user_name"];
$user = "fs11239";
if($control=="excel_export"){
// Create your database query
    $pos =strpos($wc, "undefined");
    if($pos !== false){
        $wc ="";
    }
    $sql = getFieldNamesForSQL($wc);
    //$sql.=" limit 50";
    $rs = dbCall($sql, "meac");

// Instantiate a new PHPExcel object
    $objPHPExcel = new PHPExcel();
// Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    getFieldNames($objPHPExcel);
    $sheet = $objPHPExcel->getActiveSheet();
    $rowCount = 2;
    while (!$rs->EOF)
    {
        $program                = $rs->fields["program"];
        $ship_code              = $rs->fields["ship_code"];
        $category               = $rs->fields["category"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $wp                     = $rs->fields["wp"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $description            = processDescriptionAgain($rs->fields["description"]);
        $unit                   = $rs->fields["unit"];
        $noun1                  = $rs->fields["noun1"];
        $transfers              = $rs->fields["transfers"];
        $c_amt                  = $rs->fields["c_amt"];
        $c_unit_price           = $rs->fields["c_unit_price"];
        $last_unit_price        = $rs->fields["last_unit_price"];
        $gl_int_amt             = $rs->fields["gl_int_amt"];
        $ebom                   = $rs->fields["ebom"];
        $last_unit_price_ship   = $rs->fields["last_unit_price_ship"];
        $open_po_pending_amt    = $rs->fields["open_po_pending_amt"];
        $open_buy_item_shortage = $rs->fields["open_buy_item_shortage"];
        $etc                    = $rs->fields["etc"];
        $eac                    = $rs->fields["eac"];
        $uncommitted            = $rs->fields["uncommitted"];
        $target_qty             = $rs->fields["target_qty"];
        $target_unit_price      = $rs->fields["target_unit_price"];
        $target_ext_cost        = $rs->fields["target_ext_cost"];
        $vendor_name            = $rs->fields["vendor_name"];
        $vendor_id              = $rs->fields["vendor_id"];
        $var_target_cost        = $rs->fields["var_target_cost"];
        $c_qty                  = $rs->fields["c_qty"];
        $var_target_qty         = $rs->fields["var_target_qty"];
        $buyer                  = $rs->fields["buyer"];
        $gl_qty                 = $rs->fields["gl_qty"];
        $var_ebom               = $rs->fields["var_ebom"];
        $clin                   = $rs->fields["clin"];
        $effort                 = $rs->fields["effort"];
        $ecp_rea                = $rs->fields["ecp_rea"];
        $tc                     = $rs->fields["tc"];
        $po_data                = $rs->fields["po_data"];
        $change_date            = $rs->fields["change_date"];
        $change_reason          = $rs->fields["change_reason"];
        // Set cell An to the "name" column from the database (assuming you have a column called name)
        //    where n is the Excel row number (ie cell A1 in the first row)
        $col_letter = "A";
        $sheet->SetCellValue($col_letter.$rowCount, $program);
        $sheet->SetCellValue(++$col_letter.$rowCount, $ship_code);
        $sheet->SetCellValue(++$col_letter.$rowCount, $category);
        $sheet->SetCellValue(++$col_letter.$rowCount, $swbs_group);
        $sheet->SetCellValue(++$col_letter.$rowCount, $swbs);
        $sheet->SetCellValue(++$col_letter.$rowCount, $wp);
        $sheet->SetCellValue(++$col_letter.$rowCount, $spn);
        $sheet->SetCellValue(++$col_letter.$rowCount, $item);
        if(intval($last_unit_price) == 0 and intval($target_unit_price) == 0){
            colorCellPurple($col_letter.$rowCount, $objPHPExcel);
        }
        $pos = strpos($item, "L", -4);
        if ($pos !== false) {
            if($gl_int_amt !=0){
                colorCellBLUE($col_letter.$rowCount, $objPHPExcel);
            }
        }

        $sheet->SetCellValue(++$col_letter.$rowCount, $item_group);
        $sheet->SetCellValue(++$col_letter.$rowCount, $description);
        $sheet->SetCellValue(++$col_letter.$rowCount, $unit);
        $sheet->SetCellValue(++$col_letter.$rowCount, $noun1);
        $sheet->SetCellValue(++$col_letter.$rowCount, $transfers);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);
        $sheet->SetCellValue(++$col_letter.$rowCount, $c_amt);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);
        $sheet->SetCellValue(++$col_letter.$rowCount, $c_unit_price);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);
        $sheet->SetCellValue(++$col_letter.$rowCount, $last_unit_price);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);
        $sheet->SetCellValue(++$col_letter.$rowCount, $gl_int_amt);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);
        $sheet->SetCellValue(++$col_letter.$rowCount, $ebom);
        phpExcelFormatHours($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $last_unit_price_ship);
        $sheet->SetCellValue(++$col_letter.$rowCount, $open_po_pending_amt);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $open_buy_item_shortage);
        phpExcelFormatHours($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $etc);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $eac);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $uncommitted);
        phpExcelCurrency($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $vendor_name);
        $sheet->SetCellValue(++$col_letter.$rowCount, $vendor_id);
        $sheet->SetCellValue(++$col_letter.$rowCount, $c_qty);
        $sheet->SetCellValue(++$col_letter.$rowCount, $buyer);
        $sheet->SetCellValue(++$col_letter.$rowCount, $gl_qty);
        phpExcelFormatHours($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $var_ebom);
        if($var_ebom!=0){

            colorCellRED($col_letter.$rowCount, $objPHPExcel);
            if($item_group=="SRVC"){
                colorCellYellow($col_letter.$rowCount, $objPHPExcel);
            }
        }
        phpExcelFormatHours($col_letter.$rowCount, $objPHPExcel);

        $sheet->SetCellValue(++$col_letter.$rowCount, $clin);
        $sheet->SetCellValue(++$col_letter.$rowCount, $effort);
        $sheet->SetCellValue(++$col_letter.$rowCount, $ecp_rea);
        $sheet->SetCellValue(++$col_letter.$rowCount, $tc);
        $sheet->SetCellValue(++$col_letter.$rowCount, $po_data);
        $sheet->SetCellValue(++$col_letter.$rowCount, $change_date);
        $sheet->SetCellValue(++$col_letter.$rowCount, $change_reason);


        $rowCount++;
        $rs->MoveNext();
    }
    $objPHPExcel->getActiveSheet()->freezePane('I2');
// Instantiate a Writer to create an OfficeOpenXML Excel .xlsx file
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
// Write the Excel file to filename some_excel_file.xlsx in the current directory
    if($wc==""){
        $ship_code="ALLHULLS";
    }
    $token         = rand (0,1000);
    $objWriter->save("../../../util/excel_exports/$ship_code".$token."export.xlsx");
    $path = "../util/excel_exports/$ship_code".$token."export.xlsx";
    die($path);
}

if($control=="excel_export_list"){
    $sql = "select ship_code from swbs_gl_summary group by ship_code";
    $rs = dbCall($sql, "MEAC");
    $data = "<table class = 'table' id ='excel_export_table'><tr>";
    $i=0;
    while (!$rs->EOF)
    {
        $ship_code          = $rs->fields["ship_code"];
        //print $i;
        if($i%3 == 0) {
            $data.= "</tr><tr><td><input type=\"checkbox\" name=\"$ship_code\" > $ship_code</td>";
        }
        else{
            $data.= "<td><input type=\"checkbox\" name=\"$ship_code\" > $ship_code</td>";
        }
        $i++;
        $rs->MoveNext();
    }
    $data.="</table>";
    die($data);
}
if($control=="excel_export_custom"){
// Create your database query
    $sql = getFieldNamesForSQL($wc);
    $sql.="";
    $rs = dbCall($sql, "meac");

// Instantiate a new PHPExcel object
    $objPHPExcel = new PHPExcel();
// Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    getCustomFieldNames($layout_id,$objPHPExcel);

    $rowCount = 2;
    while (!$rs->EOF)
    {
        $field_name_array = getCustomFieldNameArray($layout_id);
        $target_unit_price  = $rs->fields["target_unit_price"];
        $last_unit_price    = $rs->fields["last_unit_price"];
        if($target_unit_price ==0 and $last_unit_price==0){
            $no_history = true;
        }
        foreach ($field_name_array  as $key=>$value){
            $cell = $key."".$rowCount;
            $$value = $rs->fields["$value"];
            //print "fIELD NAME".$value."<br>";
            //print "field value".$$value."<br>";
            $objPHPExcel->getActiveSheet()->SetCellValue($cell, $$value);
            if($value =="var_ebom"){
                if($$value!=0){
                    colorCellRED($cell, $objPHPExcel);
                    //cellColor($objPHPExcel, $cell, "FF0000");
                }
            }
            if($value =="item"){
                if($no_history==true){
                    colorCellPurple($cell, $objPHPExcel);
                }
            }
            setCellWidth($key, $objPHPExcel, 15);
        }

        // Increment the Excel row counter
        $rowCount++;
        $rs->MoveNext();
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("../../../util/excel_exports/$ship_code".$token."export.xlsx");
    $path = "../util/excel_exports/$ship_code".$token."export.xlsx";
    die($path);
}
if($control=="excel_exec_sum"){
    $sql ="select swbs_group, swbs from meac.swbs_gl_summary group by swbs_group, swbs";
    $rs = dbCall($sql, "meac");

    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $rowCount = 2;
    $i=1;
    while (!$rs->EOF)
    {
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];

        // Set cell An to the "name" column from the database (assuming you have a column called name)
        //    where n is the Excel row number (ie cell A1 in the first row)
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $swbs_group);
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $swbs);

        $i++;
        $rowCount++;
        $rs->MoveNext();
    }
    $highest_row = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    $group_start = 2;

    for ($row = 2; $row <= $highest_row; ++$row) {

        $cell_val  = $objPHPExcel->getActiveSheet()->getCell('A'.$row)->getValue();
        $next_row =  $row+1;
        $next_row_val =  $objPHPExcel->getActiveSheet()->getCell('A'.$next_row)->getValue();
        if($cell_val!=$next_row_val){
            $group_end =$row;
            for ($row = $group_start; $row <$group_end; ++$row) {
                $objPHPExcel->getActiveSheet()
                    ->getRowDimension($row)
                    ->setOutlineLevel(1)
                    ->setVisible(false)
                    ->setCollapsed(true);
            }
            if(strlen($cell_val)<3){
                $group_val = "000";
            }
            else{
                $group_val = $cell_val;
            }
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($group_end + 1, 1);
            $total_row = $group_end + 1;

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$total_row, "TOTAL for Group $group_val");
            $row = $row+1;
            $next_row = $total_row+1;
            $highest_row = $highest_row+1;
            $group_start = $group_end+2;
        }

    }

    createSwbsTabs($ship_code, $objPHPExcel);

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("../../../util/excel_exports/$ship_code".$token."export.xlsx");
    $path = "../util/excel_exports/$ship_code".$token."export.xlsx";
    die($path);
}
