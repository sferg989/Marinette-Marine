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


$array = array();
//$array[] = 465;
//$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;
foreach ($array as $value){
    $wc = "where ship_code = $value";

    $sql = getFieldNamesForSQL($wc);
    //$sql.=" limit 50";
    print $sql;
    $rs = dbCall($sql, "meac");

    $objPHPExcel = new PHPExcel();
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
            colorCellPurpleSheet($col_letter.$rowCount, $sheet);
        }
        $pos = strpos($item, "L", -4);
        if ($pos !== false) {
            if($gl_int_amt !=0){
                colorCellBLUESheet($col_letter.$rowCount, $sheet);
            }
        }

        $sheet->SetCellValue(++$col_letter.$rowCount, $item_group);
        $sheet->SetCellValue(++$col_letter.$rowCount, $description);
        $sheet->SetCellValue(++$col_letter.$rowCount, $unit);
        $sheet->SetCellValue(++$col_letter.$rowCount, $noun1);
        $sheet->SetCellValue(++$col_letter.$rowCount, $transfers);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);
        $sheet->SetCellValue(++$col_letter.$rowCount, $c_amt);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);
        $sheet->SetCellValue(++$col_letter.$rowCount, $c_unit_price);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);
        $sheet->SetCellValue(++$col_letter.$rowCount, $last_unit_price);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);
        $sheet->SetCellValue(++$col_letter.$rowCount, $gl_int_amt);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);
        $sheet->SetCellValue(++$col_letter.$rowCount, $ebom);
        phpExcelFormatHoursSheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $last_unit_price_ship);
        $sheet->SetCellValue(++$col_letter.$rowCount, $open_po_pending_amt);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $open_buy_item_shortage);
        phpExcelFormatHoursSheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $etc);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $eac);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $uncommitted);
        phpExcelCurrencySheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $vendor_name);
        $sheet->SetCellValue(++$col_letter.$rowCount, $vendor_id);
        $sheet->SetCellValue(++$col_letter.$rowCount, $c_qty);
        $sheet->SetCellValue(++$col_letter.$rowCount, $buyer);
        $sheet->SetCellValue(++$col_letter.$rowCount, $gl_qty);
        phpExcelFormatHoursSheet($col_letter.$rowCount, $sheet);

        $sheet->SetCellValue(++$col_letter.$rowCount, $var_ebom);
        if($var_ebom!=0){

            colorCellREDSheet($col_letter.$rowCount, $sheet);
            if($item_group=="SRVC"){
                colorCellYellowSheet($col_letter.$rowCount, $sheet);
            }
        }
        phpExcelFormatHoursSheet($col_letter.$rowCount, $sheet);

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
    $sheet->freezePane('I2');
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

    $token         = rand (0,1000);
    $objWriter->save("C:/evms/excel/".$ship_code."_".$token."export.xlsx");
    //$objWriter->save("Y:/File Transfer Folder/Stephen Ferguson/schedule_task/".$ship_code."_".$token."export.xlsx");
    $path = "../util/excel_exports/".$ship_code."_".$token."export.xlsx";
    print $path;
}

