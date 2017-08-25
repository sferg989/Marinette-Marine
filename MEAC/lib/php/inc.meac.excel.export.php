<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/7/2017
 * Time: 1:54 PM
 */
function getFieldNames($objPHPExcel){
    $sql = "select common_name, excel_cell_width  from field_list 
              where common_name not like '%BID%'  and id not  in (18,19, 43) order by default_order";
    $rs = dbCall($sql,"meac");
    $letters = range('A', 'Z');
    $i=0;
    while (!$rs->EOF)
    {
        $field_name = $rs->fields["common_name"];
        $excel_cell_width = $rs->fields["excel_cell_width"];
        if($i>25){
            $index = $i-26;
            $cell = "A".$letters[$index];
        }else{
            $cell = $letters[$i];
        }
        $cell_with_row = $cell.'1';
        //print $cell."\r";

        $objPHPExcel->getActiveSheet()->SetCellValue($cell_with_row, $field_name);
        setCellWidth($cell, $objPHPExcel, $excel_cell_width);
        $i++;
        $rs->MoveNext();
    }
}
function buildSWBSDetailTabs($ship_code, $swbs_group,$objPHPExcel,  $sheet_index){
    $sql = "
    SELECT 
        ship_code,
        swbs_group,
        swbs,
        item_group,
        case 
          when item_group_description is null then item_group
          when item_group is null or item_group = '' then  'NA'
          ELSE item_group_description
        end as item_group_description,
        buyer, 
        sum(gl_int_amt) gl,
        sum(open_po_pending_amt) open_po,
        sum(c_amt) c_amt,
        sum(etc) etc,
        sum(eac) eac 
    from swbs_gl_summary 
    where 
      ship_code = $ship_code 
      and swbs_group = $swbs_group
    GROUP BY ship_code,
    swbs_group,
    swbs,
    item_group
";
    $rs = dbCall($sql,"meac");

    $i=7;
    $objPHPExcel->setActiveSheetIndex($sheet_index);
    $sheet = $objPHPExcel->getActiveSheet();
    while (!$rs->EOF)
    {
        $swbs                   = $rs->fields["swbs"];
        $item_group_description = $rs->fields["item_group_description"];
        $gl                     = $rs->fields["gl"];
        $buyer                  = $rs->fields["buyer"];
        $open_po                = $rs->fields["open_po"];
        $c_amt                  = $rs->fields["c_amt"];
        $etc                    = $rs->fields["etc"];
        $eac                    = $rs->fields["eac"];
        $col_letter = "A";
        $sheet->SetCellValue($col_letter++.$i, $swbs);
        $sheet->SetCellValue($col_letter++.$i, $item_group_description);
        $sheet->SetCellValue($col_letter++.$i, $buyer);
        phpExcelCurrencySheet($col_letter.$i, $sheet);
        $sheet->SetCellValue($col_letter++.$i, $gl);
        phpExcelCurrencySheet($col_letter.$i, $sheet);
        $sheet->SetCellValue($col_letter++.$i, $open_po);
        phpExcelCurrencySheet($col_letter.$i, $sheet);
        $sheet->SetCellValue($col_letter++.$i, $etc);
        phpExcelCurrencySheet($col_letter.$i, $sheet);
        $sheet->SetCellValue($col_letter++.$i, $eac);

        $i++;
        $rs->MoveNext();
    }

}
function setSwbsTabTieldHeaders($objPHPExcel, $i){
    $swbs_detail_tab_headers = array();
    $col_letter = "A";
    $swbs_detail_tab_headers[$col_letter++] ="SWBS";
    $swbs_detail_tab_headers[$col_letter++] ="ITEM GROUP Description";
    $swbs_detail_tab_headers[$col_letter++] ="Buyers Responsible";
    $swbs_detail_tab_headers[$col_letter++] ="Gl Actuals";
    $swbs_detail_tab_headers[$col_letter++] ="Open PO";
    $swbs_detail_tab_headers[$col_letter++] ="ETC";
    $swbs_detail_tab_headers[$col_letter++] ="EAC";
    $objPHPExcel->setActiveSheetIndex($i);
    $sheet = $objPHPExcel->getActiveSheet();
    foreach ($swbs_detail_tab_headers as $cell=>$value) {
        $sheet->SetCellValue($cell."5", $value);
        switch ($value) {
            case "ITEM GROUP Description":
                setCellWidth($cell."1", $objPHPExcel, 39);
                break;
            case "Gl Actuals":
            case "Open PO":
            case "ETC":
            case "EAC":
                setCellWidth($cell."1", $objPHPExcel, 11);
                break;
            default:
                setCellWidth($cell."1", $objPHPExcel, 20);
        }
        colorCellHeaderTitleSheet($cell."5", $sheet);
    }
}
function createSwbsTabs($ship_code,$objPHPExcel){
    $sql ="select swbs_group from meac.swbs_gl_summary group by swbs_group";
    $rs = dbCall($sql, "meac");
    $i=1;
    while (!$rs->EOF)
    {
        $swbs_group   = $rs->fields["swbs_group"];
        $objWorkSheet = $objPHPExcel->createSheet($i); //Setting index when creating
        if(strlen($swbs_group)<3){
            $swbs_group = "000";
        }
        setSwbsTabTieldHeaders($objPHPExcel, $i);
        buildSWBSDetailTabs($ship_code, $swbs_group,$objPHPExcel,  $i);
        $highest_row = $objPHPExcel->setActiveSheetIndex($i)->getHighestRow();
        $group_start = 7;
        $sheet = $objPHPExcel->getActiveSheet();
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
                if(strlen($cell_val)<3){
                    $group_val = "000";
                }
                else{
                    $group_val = $cell_val;
                }
                $sheet->insertNewRowBefore($group_end + 1, 1);
                $total_row = $group_end + 1;
                $header_col = "D";

                $sheet->SetCellValue('A'.$total_row, "TOTAL for Group $cell_val");
                phpExcelCurrencySheetBOLDAndCOLOR('A'.$total_row, $sheet);
                phpExcelCurrencySheetBOLDAndCOLOR('B'.$total_row, $sheet);
                phpExcelCurrencySheetBOLDAndCOLOR('C'.$total_row, $sheet);

                $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
                phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$total_row, $sheet);

                $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
                phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$total_row, $sheet);

                $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
                phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$total_row, $sheet);

                $sheet->SetCellValue($header_col.$total_row, "=SUM($header_col".$group_start.":$header_col".$group_end.")");
                phpExcelCurrencySheetBOLDAndCOLOR($header_col++.$total_row, $sheet);
                $row = $row+1;
                $next_row = $total_row+1;
                $highest_row = $highest_row+1;
                $group_start = $group_end+2;
            }

        }
        $objWorkSheet->setTitle("Group $swbs_group");
        $objWorkSheet->setShowGridlines(False);
        $objWorkSheet->getTabColor()->setARGB('FF0094FF');
        $i++;
        $rs->MoveNext();
    }

}
function getCustomFieldNames($layout_id,$objPHPExcel){
    $sql = "
        select 
            common_name,
            field_name
        from field_list fl 
        INNER join ud_layout ud
            on ud.field_list_id=fl.id 
        and  ud.id = '$layout_id' order by fl.group desc";
    $rs=dbCall($sql,"MEAC");
    $i=0;
    $letters = range('A', 'Z');
    while (!$rs->EOF)
    {
        $field_name = $rs->fields["field_name"];
        if($i>25){
            $index = $i-26;
            $cell = "A".$letters[$index];
        }else{
            $cell = $letters[$i];
        }
        $cell = $cell.'1';
        //print $cell."\r";

        $objPHPExcel->getActiveSheet()->SetCellValue($cell, $field_name);
        $i++;
        $rs->MoveNext();
    }

}
function getCustomFieldNameArray($layout_id){
    $sql = "
        select 
            field_name 
        from field_list fl 
        INNER join ud_layout ud
            on ud.field_list_id=fl.id 
        and  ud.id = '$layout_id' order by fl.group desc";
    $rs=dbCall($sql,"MEAC");
    $i=0;
    $letters = range('A', 'Z');
    while (!$rs->EOF)
    {
        if($i>25){
            $index = $i-26;
            $cell = "A".$letters[$index];
        }else{
            $cell = $letters[$i];
        }
        $field_name = $rs->fields["field_name"];

        $field_name_array[$cell] = $field_name;
        $i++;

        $rs->MoveNext();
    }
    return $field_name_array;
}
function colorCellRED($cell, $objPHPExcel){
    $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF0000')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FDFEFE')
            )
        )
    );
}
function colorCellREDSheet($cell, $sheet){
    $sheet->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF0000')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FDFEFE')
            )
        )
    );
}
function colorCellYellow($cell, $objPHPExcel){
    $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FFFF00')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000000')
            )
        )
    );
}
function colorCellYellowSheet($cell, $sheet){
    $sheet->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FFFF00')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000000')
            )
        )
    );
}
function colorCellBLUE($cell, $objPHPExcel){
    $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '0000FF')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FDFEFE')
            )
        )
    );
}
function colorCellBLUESheet($cell, $sheet){
    $sheet->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '0000FF')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FDFEFE')
            )
        )
    );
}
function colorCellPurple($cell, $objPHPExcel){
    $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF00FF')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FDFEFE')
            )
        )
    );
}
function colorCellPurpleSheet($cell, $sheet){
    $sheet->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF99CC')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => '000000')
            )
        )
    );
}
function colorCellHeaderTitleSheet($cell, $sheet){
    $sheet->getStyle($cell)->applyFromArray(
        array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF9933')
            ),
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF')
            )
        )
    );
}
function setCellWidth($column, $objPHPExcel, $width){
    $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($width);
}
