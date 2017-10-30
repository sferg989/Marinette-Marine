<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 7/19/2017
 * Time: 9:26 AM
*/
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\meac\lib\php\inc.insert_data.php');
require('C:\xampp\htdocs\fmg\meac\lib\php\inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
require('C:\xampp\htdocs\fmg\inc\lib\php\phpExcel-1.8\classes\phpexcel\IOFactory.php');

$user = $_SESSION["user_name"];
$user = "fs11239";
$wc = "where ship_code in (0477)";
$sql = getFieldNamesForSQL($wc);

$sql.="limit 50";
$rs = dbCall($sql, "meac");
$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
getFieldNames($objPHPExcel);

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
    $ebom_on_hand           = $rs->fields["ebom_on_hand"];
    $ebom_issued            = $rs->fields["ebom_issued"];
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
    // Set cell An to the "name" column from the database (assuming you have a column called name)
    //    where n is the Excel row number (ie cell A1 in the first row)
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $program);
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $ship_code);
    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $category);
    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $swbs_group);
    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $swbs);
    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $wp);
    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $spn);
    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $item);
    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $item_group);
    $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $description);
    $objPHPExcel->getActiveSheet()->SetCellValue('K'.$rowCount, $unit);
    $objPHPExcel->getActiveSheet()->SetCellValue('L'.$rowCount, $noun1);
    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$rowCount, $transfers);
    $objPHPExcel->getActiveSheet()->SetCellValue('N'.$rowCount, $c_amt);
    $objPHPExcel->getActiveSheet()->SetCellValue('O'.$rowCount, $c_unit_price);
    $objPHPExcel->getActiveSheet()->SetCellValue('P'.$rowCount, $last_unit_price);
    $objPHPExcel->getActiveSheet()->SetCellValue('Q'.$rowCount, $gl_int_amt);
    $objPHPExcel->getActiveSheet()->SetCellValue('R'.$rowCount, $ebom);
    $objPHPExcel->getActiveSheet()->SetCellValue('S'.$rowCount, $ebom_on_hand);
    $objPHPExcel->getActiveSheet()->SetCellValue('T'.$rowCount, $ebom_issued);
    $objPHPExcel->getActiveSheet()->SetCellValue('U'.$rowCount, $last_unit_price_ship);
    $objPHPExcel->getActiveSheet()->SetCellValue('V'.$rowCount, $open_po_pending_amt);
    $objPHPExcel->getActiveSheet()->SetCellValue('W'.$rowCount, $open_buy_item_shortage);
    $objPHPExcel->getActiveSheet()->SetCellValue('X'.$rowCount, $etc);
    $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$rowCount, $eac);
    $objPHPExcel->getActiveSheet()->SetCellValue('Z'.$rowCount, $uncommitted);
    $objPHPExcel->getActiveSheet()->SetCellValue('AA'.$rowCount, $target_qty);
    $objPHPExcel->getActiveSheet()->SetCellValue('AB'.$rowCount, $target_unit_price);
    $objPHPExcel->getActiveSheet()->SetCellValue('AC'.$rowCount, $target_ext_cost);
    $objPHPExcel->getActiveSheet()->SetCellValue('AD'.$rowCount, $vendor_name);
    $objPHPExcel->getActiveSheet()->SetCellValue('AE'.$rowCount, $vendor_id);
    $objPHPExcel->getActiveSheet()->SetCellValue('AF'.$rowCount, $var_target_cost);
    $objPHPExcel->getActiveSheet()->SetCellValue('AG'.$rowCount, $c_qty);
    $objPHPExcel->getActiveSheet()->SetCellValue('AH'.$rowCount, $var_target_qty);
    $objPHPExcel->getActiveSheet()->SetCellValue('AI'.$rowCount, $buyer);
    $objPHPExcel->getActiveSheet()->SetCellValue('AJ'.$rowCount, $gl_qty);
    $objPHPExcel->getActiveSheet()->SetCellValue('AK'.$rowCount, $var_ebom);
    $objPHPExcel->getActiveSheet()->SetCellValue('AL'.$rowCount, $clin);
    $objPHPExcel->getActiveSheet()->SetCellValue('AM'.$rowCount, $effort);
    $objPHPExcel->getActiveSheet()->SetCellValue('AN'.$rowCount, $ecp_rea);
    $objPHPExcel->getActiveSheet()->SetCellValue('AO'.$rowCount, $tc);
    $objPHPExcel->getActiveSheet()->SetCellValue('AP'.$rowCount, $po_data);
    if(intval($last_unit_price) == 0 and intval($target_unit_price) == 0){
        colorCellPurple('H'.$rowCount, $objPHPExcel);
    }

    $pos = strpos($item, "L", -4);
    if ($pos !== false) {
            if($gl_int_amt !=0){
                colorCellBLUE('H'.$rowCount, $objPHPExcel);
            }
    }
    phpExcelCurrency("O:O", $objPHPExcel);
    phpExcelCurrency("N:N", $objPHPExcel);
    phpExcelCurrency("P:P", $objPHPExcel);
    phpExcelCurrency("U:U", $objPHPExcel);
    phpExcelCurrency("q:q", $objPHPExcel);
    phpExcelCurrency("v:v", $objPHPExcel);
    phpExcelCurrency("x:x", $objPHPExcel);
    phpExcelCurrency("y:y", $objPHPExcel);
    phpExcelCurrency("z:z", $objPHPExcel);
    phpExcelCurrency("ab:ab", $objPHPExcel);
    phpExcelCurrency("ac:ac", $objPHPExcel);
    phpExcelCurrency("af:af", $objPHPExcel);
    phpExcelFormatHours("R:R", $objPHPExcel);
    phpExcelFormatHours("S:S", $objPHPExcel);
    phpExcelFormatHours("T:T", $objPHPExcel);
    phpExcelFormatHours("W:W", $objPHPExcel);
    phpExcelFormatHours("AA:AA", $objPHPExcel);
    phpExcelFormatHours("AG:AG", $objPHPExcel);
    phpExcelFormatHours("AH:AH", $objPHPExcel);
    phpExcelFormatHours("AJ:AJ", $objPHPExcel);
    phpExcelFormatHours("AK:AK", $objPHPExcel);

    if($var_ebom!=0){

        colorCellRED('AK'.$rowCount, $objPHPExcel);
        if($item_group=="SRVC"){
            colorCellYellow('AK'.$rowCount, $objPHPExcel);
        }
    }

    $rowCount++;
    $rs->MoveNext();
}
$objPHPExcel->getActiveSheet()->freezePane('I2');
/*
 * WP SUMMARY
 * WP SUMMARY
 * WP SUMMARY
 * WP SUMMARY
 * */

$objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating
$objWorkSheet->setTitle("Grouped by WP");
$objWorkSheet->getTabColor()->setARGB('FF0094FF');
$objPHPExcel->setActiveSheetIndex(1);
$wc = "where ship_code in (0477)";
$sql = getFieldNamesForSQL($wc);

$sql.=" limit 100";
$rs = dbCall($sql, "meac");

$rowCount = 2;
$col_letter = "A";
$header_row= 1;
$sheet = $objPHPExcel->getActiveSheet();

$sheet->SetCellValue($col_letter.$header_row, "SWBS GROUP");
$sheet->SetCellValue(++$col_letter.$header_row, "WP");
$sheet->SetCellValue(++$col_letter.$header_row, "ITEM");
$sheet->SetCellValue(++$col_letter.$header_row, "Description");
$sheet->SetCellValue(++$col_letter.$header_row, "UOM");
$sheet->SetCellValue(++$col_letter.$header_row, "GL Actuals");
$sheet->SetCellValue(++$col_letter.$header_row, "EBOM");
$sheet->SetCellValue(++$col_letter.$header_row, "EBOM Issued");
$sheet->SetCellValue(++$col_letter.$header_row, "Open PO Pending AMT");
$sheet->SetCellValue(++$col_letter.$header_row, "Open Buy Item Shortage");
$sheet->SetCellValue(++$col_letter.$header_row, "Last Price Paid");
$sheet->SetCellValue(++$col_letter.$header_row, "ETC");
$sheet->SetCellValue(++$col_letter.$header_row, "EAC");
setCellWidth("A1", $objPHPExcel, 23);
setCellWidth("B1", $objPHPExcel, 20);
setCellWidth("C1", $objPHPExcel, 30);
setCellWidth("D1", $objPHPExcel, 5);
setCellWidth("E10", $objPHPExcel, 10);

while (!$rs->EOF)
{
    $wp                     = $rs->fields["wp"];
    $item                   = $rs->fields["item"];
    $description            = processDescriptionAgain($rs->fields["description"]);
    $unit                   = $rs->fields["unit"];
    $item_group             = $rs->fields["item_group"];
    $last_unit_price        = $rs->fields["last_unit_price"];
    $gl_int_amt             = $rs->fields["gl_int_amt"];
    $ebom                   = $rs->fields["ebom"];
    $ebom_on_hand           = $rs->fields["ebom_on_hand"];
    $ebom_issued            = $rs->fields["ebom_issued"];
    $open_po_pending_amt    = $rs->fields["open_po_pending_amt"];
    $open_buy_item_shortage = $rs->fields["open_buy_item_shortage"];
    $etc                    = $rs->fields["etc"];
    $eac                    = $rs->fields["eac"];
    $c_qty                  = $rs->fields["c_qty"];
    $var_ebom               = $rs->fields["var_ebom"];
    $tc                     = $rs->fields["tc"];
    $po_data                = $rs->fields["po_data"];
    // Set cell An to the "name" column from the database (assuming you have a column called name)
    //    where n is the Excel row number (ie cell A1 in the first row)

    $str = "A";
    $sheet->setCellValue($str.$rowCount,"GROUP ".$swbs_group)
        ->setCellValue(++$str.$rowCount,$wp)
        ->setCellValue(++$str.$rowCount,$item)
        ->setCellValue(++$str.$rowCount,$description)
        ->setCellValue(++$str.$rowCount,$unit)
        ->setCellValue(++$str.$rowCount,$gl_int_amt)
        ->setCellValue(++$str.$rowCount,$ebom)
        ->setCellValue(++$str.$rowCount,$open_po_pending_amt)
        ->setCellValue(++$str.$rowCount,$open_buy_item_shortage)
        ->setCellValue(++$str.$rowCount,$last_unit_price)
        ->setCellValue(++$str.$rowCount,$etc)
        ->setCellValue(++$str.$rowCount,$eac);
    $pos = strpos($item, "L", -4);
    if ($pos !== false) {
        if($gl_int_amt !=0){
            colorCellBLUE('H'.$rowCount, $objPHPExcel);
        }
    }
    if($var_ebom!=0){
        colorCellRED('G'.$rowCount, $objPHPExcel);
        if($item_group=="SRVC"){
            colorCellYellow('U'.$rowCount, $objPHPExcel);
        }
    }
    $rowCount++;
    $rs->MoveNext();
}

$highest_row = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();
$group_start = 2;
$wp_group_start = 2;
$sheet = $objPHPExcel->getActiveSheet();
for ($row = 2; $row <= $highest_row; ++$row) {

    $swbs_cell_val     = $sheet->getCell('A' . $row)->getValue();
    $next_row          = $row + 1;
    $swbs_next_row_val = $sheet->getCell('A' . $next_row)->getValue();

    if($swbs_cell_val  !=$swbs_next_row_val ){
        $group_end =$row;
        for ($row = $group_start; $row <$group_end; ++$row) {
            $sheet->getRowDimension($row)
                ->setOutlineLevel(1)
                ->setVisible(true)
                ->setCollapsed(false);
        }
        $sheet->mergeCells("A".$group_start.":A".$group_end);
        $sheet->getStyle("A".$group_start.":A".$group_end)
                ->getAlignment()
                ->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        //$sheet->insertNewRowBefore($group_end + 1, 1);
        //$total_row = $group_end + 1;

        //$sheet->SetCellValue('A'.$total_row, "TOTAL for  $cell_val");
        //$sheet->SetCellValue('E'.$total_row, "=SUM(E".$group_start.":E".$group_end.")");
        //$sheet->SetCellValue('F'.$total_row, "=SUM(F".$group_start.":F".$group_end.")");
        //$sheet->SetCellValue('G'.$total_row, "=SUM(G".$group_start.":G".$group_end.")");
        //$sheet->SetCellValue('H'.$total_row, "=SUM(H".$group_start.":H".$group_end.")");
        //$sheet->SetCellValue('I'.$total_row, "=SUM(I".$group_start.":I".$group_end.")");

        //$sheet->SetCellValue('K'.$total_row, "=SUM(K".$group_start.":K".$group_end.")");
        //$sheet->SetCellValue('L'.$total_row, "=SUM(L".$group_start.":L".$group_end.")");

        //$row = $row+1;
        //$next_row = $total_row+1;
        //$highest_row = $highest_row+1;
        $group_start = $group_end+1;
    }
}
for ($row = 2; $row <= $highest_row; ++$row) {

    $wp_cell_val     = $sheet->getCell('B' . $row)->getValue();
    $next_row          = $row + 1;
    $wp_next_row_val = $sheet->getCell('B' . $next_row)->getValue();

    if($wp_cell_val  !=$wp_next_row_val ){
        $wp_group_end =$row;
        for ($row = $wp_group_start; $row <$wp_group_end; ++$row) {
            $sheet->getRowDimension($row)
                ->setOutlineLevel(2)
                ->setVisible(true)
                ->setCollapsed(false);
        }
        $sheet->mergeCells("B".$wp_group_start.":B".$wp_group_end);
        $sheet->getStyle("B".$wp_group_start.":B".$wp_group_end)
            ->getAlignment()
            ->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);


        $wp_group_start = $wp_group_end+1;
    }
}
phpExcelCurrency("H2:H".$highest_row, $objPHPExcel);
phpExcelCurrency("E2:E".$highest_row, $objPHPExcel);
phpExcelCurrency("J2:J".$highest_row, $objPHPExcel);
phpExcelCurrency("K2:K".$highest_row, $objPHPExcel);
phpExcelCurrency("L2:L".$highest_row, $objPHPExcel);
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

$ship_code="ALLHULLS_";
$token         = rand (0,1000);
$objWriter->save("../../../util/excel_exports/$ship_code".$token."export.xlsx");
$path = "../util/excel_exports/$ship_code".$token."export.xlsx";
die($path);

