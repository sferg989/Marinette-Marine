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
//$array[] = 469;
//$array[] = 471;
//$array[] = 473;
//$array[] = 475;
//$array[] = 477;
$array[] = 479;
//$array[] = 481;
//$array[] = 483;
//$array[] = 485;
function getAllCategoriesByShip($ship_code){
    $sql = "select category from meac.swbs_gl_summary where ship_code = $ship_code group by category";
    $rs = dbCall($sql, "meac");
    while (!$rs->EOF)
    {
        $category         = $rs->fields["category"];
        $category_array[] = $category;
        $rs->MoveNext();
    }
    return $category_array;
}
function getValByCategory($field, $category, $ship_code, $swbs){
    $sql = "
      select 
        sum($field)
      from swbs_gl_summary 
      where 
      ship_code = $ship_code 
      and category = '$category' 
      and swbs = $swbs 
      group by ship_code, swbs_group, swbs
";
    $rs = dbCall($sql, "meac");
    print $sql;
    while (!$rs->EOF)
    {
        $category         = $rs->fields["category"];
        $category_array[] = $category;
        $rs->MoveNext();
    }
    return $category_array;
}
function createTotalLINEByCategory($ship_code, $sheet, $highest_row){
    $sql ="
    select
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) as c_amt from swbs_gl_summary where ship_code = $ship_code and category = 'commodity' group by ship_code) as t_gl_commodity,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'HPR' group by ship_code) as t_gl_hpr,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'ILS' group by ship_code) as t_gl_ils,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'Outsource' group by ship_code) as t_gl_outsource,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'Rental' group by ship_code) as t_gl_rental,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'Rework' group by ship_code) as t_gl_rework,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'SMOS' group by ship_code) as t_gl_smos,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'Turnkey' group by ship_code) as t_gl_turnkey,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category = 'Vendor Service' group by ship_code) as t_gl_vs,
      (select (sum(gl_int_amt) + sum(open_po_pending_amt)) s from swbs_gl_summary where ship_code = $ship_code and category in ('Commodity', 'HPR','ILS',
                                                                  'Outsource','Rental','Rework','SMOS',
                                                                  'Turnkey','Vendor Service')  group by ship_code) as t_gl,
      (select sum(eac) as c_amt from swbs_gl_summary where ship_code = $ship_code and category = 'commodity' group by ship_code) as t_re_est_commodity,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'HPR' group by ship_code) as t_re_est_hpr,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'ILS' group by ship_code) as t_re_est_ils,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'Outsource' group by ship_code) as t_re_est_outsource,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'Rental' group by ship_code) as t_re_est_rental,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'Rework' group by ship_code) as t_re_est_rework,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'SMOS' group by ship_code) as t_re_est_smos,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'Turnkey' group by ship_code) as t_re_est_turnkey,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category = 'Vendor Service' group by ship_code) as t_re_est_vs,
      (select sum(eac)  from swbs_gl_summary where ship_code = $ship_code and category in ('Commodity', 'HPR','ILS',
                                                                  'Outsource','Rental','Rework','SMOS',
                                                                  'Turnkey','Vendor Service')  group by ship_code) as t_re_est
  ";
    //print $sql;
    $rs = dbCall($sql, "meac");
    $highest_row++;
    $t_gl_commodity = $rs->fields["t_gl_commodity"];
    $t_gl_hpr       = $rs->fields["t_gl_hpr"];
    $t_gl_ils       = $rs->fields["t_gl_ils"];
    $t_gl_outsource = $rs->fields["t_gl_outsource"];
    $t_gl_rental    = $rs->fields["t_gl_rental"];
    $t_gl_rework    = $rs->fields["t_gl_rework"];
    $t_gl_smos      = $rs->fields["t_gl_smos"];
    $t_gl_turnkey   = $rs->fields["t_gl_turnkey"];
    $t_gl_vs        = $rs->fields["t_gl_vs"];
    $t_gl           = $rs->fields["t_gl"];

    $t_re_est_commodity = $rs->fields["t_re_est_commodity"];
    $t_re_est_hpr       = $rs->fields["t_re_est_hpr"];
    $t_re_est_ils       = $rs->fields["t_re_est_ils"];
    $t_re_est_outsource = $rs->fields["t_re_est_outsource"];
    $t_re_est_rental    = $rs->fields["t_re_est_rental"];
    $t_re_est_rework    = $rs->fields["t_re_est_rework"];
    $t_re_est_smos      = $rs->fields["t_re_est_smos"];
    $t_re_est_turnkey   = $rs->fields["t_re_est_turnkey"];
    $t_re_est_vs        = $rs->fields["t_re_est_vs"];
    $t_re_est           = $rs->fields["t_re_est"];
    
    $sheet->SetCellValue("A".$highest_row, "TOTAL : ");
    $header_col = "E";
    $sheet->SetCellValue($header_col.$highest_row, $t_gl_commodity);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);



    $sheet->SetCellValue($header_col.$highest_row, $t_gl_hpr);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_ils);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_outsource);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_rental);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_rework);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_smos);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_turnkey);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl_vs);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_gl);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);
    /*
     * RE FORECAST DATA
     * */
    $header_col++;//BLANK COLUMN

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_commodity);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_hpr);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_ils);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_outsource);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_rental);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_rework);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_smos);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_turnkey);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est_vs);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);

    $sheet->SetCellValue($header_col.$highest_row, $t_re_est);
    phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$highest_row, $sheet);
}

foreach ($array as $value){
    $sql ="
            select
                swbs_group,
                swbs,
                (select (sum(gl_int_amt)+ sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Commodity') as gl_commodity,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'HPR') as gl_hpr,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'ILS') as gl_ils,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Outsource') as gl_outsource,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Rental') as gl_rental,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Rework') as gl_rework,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'SMOS') as gl_smos,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Turnkey') as gl_turnkey,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Vendor Service') as gl_vs,
                (select (sum(gl_int_amt) + sum(open_po_pending_amt)) from swbs_gl_summary gl2 where 
                                                                  gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs 
                                                                  and gl2.category in ('Commodity', 'HPR','ILS',
                                                                  'Outsource','Rental','Rework','SMOS',
                                                                  'Turnkey','Vendor Service')) as total,                
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Commodity') as re_est_commodity,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'HPR') as re_est_hpr,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'ILS') as re_est_ils,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Outsource') as re_est_outsource,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Rental') as re_est_rental,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Rework') as re_est_rework,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'SMOS') as re_est_smos,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Turnkey') as re_est_turnkey,
                (select sum(eac) from swbs_gl_summary gl2 where gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs and gl2.category = 'Vendor Service') as re_est_vs,
                (select sum(eac) from swbs_gl_summary gl2 where 
                                                                  gl2.ship_code=gl.ship_code and gl2.swbs=gl.swbs 
                                                                  and gl2.category in ('Commodity', 'HPR','ILS',
                                                                  'Outsource','Rental','Rework','SMOS',
                                                                  'Turnkey','Vendor Service')) as total
            from meac.swbs_gl_summary gl
            where ship_code = $value
            group by swbs_group, swbs
";
/*    $sql ="
            select
                swbs_group,
                swbs,
                10000 as gl_commodity,
                10000 as gl_hpr,
                10000 as gl_ils,
                10000 as gl_outsource,
                10000 as gl_rental,
                10000 as gl_rework,
                10000 as gl_smos,
                10000 as gl_turnkey,
                10000 as gl_vs,
                120000 as gl_total,
                10000 as re_est_commodity,
                10000 as re_est_hpr,
                10000 as re_est_ils,
                10000 as re_est_outsource,
                10000 as re_est_rental,
                10000 as re_est_rework,
                10000 as re_est_smos,
                10000 as re_est_turnkey,
                10000 as re_est_vs,
                120000 as re_est_total
            from meac.swbs_gl_summary gl
            where ship_code = $value
            group by swbs_group, swbs
";*/
    $rs = dbCall($sql, "meac");
    $ship_name = getProjectNameFromCode($value);
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();



    /*
     * Start HEADERS on row 5*/
    $header_row= 5;
    $i=1;
    $header_col = "E";
    colorCellHeaderTitleSheet($header_col.$header_row, $sheet);

    $category_array[] = "Commodity";
    $category_array[] = "HPR";
    $category_array[] = "ILS";
    $category_array[] = "Outsource";
    $category_array[] = "Rental";
    $category_array[] = "Rework";
    $category_array[] = "SMOS";
    $category_array[] = "Turnkey";
    $category_array[] = "Vendor Service";
    $category_array[] = "Total ";
    foreach ($category_array as $category){
        $sheet->SetCellValue($header_col.$header_row, $category);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $header_col = "P";
    foreach ($category_array as $category){
        $sheet->SetCellValue($header_col.$header_row, $category);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    /*
     * DATA STARTS ON ROW 7 */
    $data_start = 7;

    while (!$rs->EOF)
    {
        $swbs_group = $rs->fields["swbs_group"];
        $swbs       = $rs->fields["swbs"];
        $commodity  = $rs->fields["gl_commodity"];
        $hpr        = $rs->fields["gl_hpr"];
        $ils        = $rs->fields["gl_ils"];
        $outsource  = $rs->fields["gl_outsource"];
        $rental     = $rs->fields["gl_rental"];
        $rework     = $rs->fields["gl_rework"];
        $smos       = $rs->fields["gl_smos"];
        $turnkey    = $rs->fields["gl_turnkey"];
        $vs         = $rs->fields["gl_vs"];

        $re_est_commodity  = $rs->fields["re_est_commodity"];
        $re_est_hpr        = $rs->fields["re_est_hpr"];
        $re_est_ils        = $rs->fields["re_est_ils"];
        $re_est_outsource  = $rs->fields["re_est_outsource"];
        $re_est_rental     = $rs->fields["re_est_rental"];
        $re_est_rework     = $rs->fields["re_est_rework"];
        $re_est_smos       = $rs->fields["re_est_smos"];
        $re_est_turnkey    = $rs->fields["re_est_turnkey"];
        $re_est_vs         = $rs->fields["re_est_vs"];

        $col_letter = "A";
        $header_col = "E";
        $gl_start_total = $header_col;

        $sheet->SetCellValue($col_letter++.$data_start, "GROUP ".$swbs_group);
        $sheet->SetCellValue($col_letter++.$data_start, $swbs);

        $sheet->SetCellValue($header_col.$data_start, $commodity);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $hpr);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $ils);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $outsource);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $rental);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $rework);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $smos);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $turnkey);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $vs);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, "=SUM(".$gl_start_total.$data_start.":".chr(ord($header_col)-1).$data_start.")");
        phpExcelCurrencySheetBOLD($header_col++.$data_start, $sheet);
        /*RE FORECAST DATA!
        RE FORECAST DATA!
        RE FORECAST DATA!
        RE FORECAST DATA!
        */
        $header_col++; /*EMPTY COLUMN*/

        $re_est_start_total = $header_col;

        $sheet->SetCellValue($header_col.$data_start, $re_est_commodity);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_hpr);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_ils);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_outsource);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_rental);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_rework);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_smos);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_turnkey);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $re_est_vs);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, "=SUM(".$re_est_start_total.$data_start.":".chr(ord($header_col)-1).$data_start.")");
        phpExcelCurrencySheetBOLD($header_col++.$data_start, $sheet);

        $i++;
        $data_start++;
        $rs->MoveNext();
    }
    $highest_row = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    $group_start = 7;

    for ($row = 7; $row <= $highest_row; ++$row) {

        $cell_val  = $sheet->getCell('A'.$row)->getValue();
        $next_row =  $row+1;
        $next_row_val =  $sheet->getCell('A'.$next_row)->getValue();
        if($cell_val!=$next_row_val){
            $group_end =$row;
            for ($row = $group_start; $row <$group_end; ++$row) {
                $sheet->getRowDimension($row)
                    ->setOutlineLevel(1)
                    ->setVisible(false)
                    ->setCollapsed(true);
            }
            $sheet->mergeCells("A".$group_start.":A".$group_end);
            $sheet->getStyle("A".$group_start.":A".$group_end)
                ->getAlignment()
                ->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            if(strlen($cell_val)<3){
                $group_val = "000";
            }
            else{
                $group_val = $cell_val;
            }
            $sheet->insertNewRowBefore($group_end + 1, 1);
            $total_row = $group_end + 1;
            $header_col = "E";
            $gl_start_total = $header_col;
            $sheet->SetCellValue('A'.$total_row, "TOTAL for Group $group_val");
            boldAndUnderLine('A'.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM(".$gl_start_total.$total_row.":".chr(ord($header_col)-1).$total_row.")");
            phpExcelCurrencySheetBOLD($header_col.$total_row, $sheet);

            $header_col++; /*EMPTY COLUMN*/
            $header_col++; /*EMPTY COLUMN*/

            $re_est_start_total = $header_col;
            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
            phpExcelCurrencySheetBOLD($header_col++.$total_row, $sheet);

            $sheet->SetCellValue($header_col.$total_row, "=SUM(".$re_est_start_total.$total_row.":".chr(ord($header_col)-1).$total_row.")");
            phpExcelCurrencySheetBOLD($header_col.$total_row, $sheet);

            $row = $row+1;
            $next_row = $total_row+1;
            $highest_row = $highest_row+1;
            $group_start = $group_end+2;
        }

    }
    $sheet->freezePane('E7');
    $sheet->mergeCells("A1:D6");
    $sheet->getStyle("A1:D6")
        ->getAlignment()
        ->setVERTICAL(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    $sheet->mergeCells("E4:N4");
    $sheet->mergeCells("P4:X4");


    $style = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'FF99CC')
        ),
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => '000000')
        )
    );
    $sheet->getStyle("P4:X4")->applyFromArray($style);
    $sheet->getStyle("E4:N4")->applyFromArray($style);
    $sheet->SetCellValue("E4", "ACTUALS ITD");
    $sheet->SetCellValue("P4", "Re-Forecast 3");

    $sheet->setShowGridlines(False);
    boldAndUnderLine('A1', $sheet);
    $highest_row = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
    createTotalLINEByCategory($value, $sheet, $highest_row);
    createSwbsTabs($value, $objPHPExcel);


    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("C:/evms/excel/".$value."_".$token."EXECSUMMARYexport.xlsx");
    //$objWriter->save("Y:/File Transfer Folder/Stephen Ferguson/schedule_task/".$value."_".$token."export.xlsx");
    $path = "../evms/excel/".$value."_".$token."export.xlsx";
    print $path;
}

