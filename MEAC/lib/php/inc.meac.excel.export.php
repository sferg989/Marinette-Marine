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

    $i=2;
    $objPHPExcel->setActiveSheetIndex($sheet_index);
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

        $objPHPExcel->getActiveSheet()->SetCellValue("A".$i, $swbs);
        $objPHPExcel->getActiveSheet()->SetCellValue("B".$i, $item_group_description);
        $objPHPExcel->getActiveSheet()->SetCellValue("C".$i, $buyer);
        $objPHPExcel->getActiveSheet()->SetCellValue("D".$i, $gl);
        $objPHPExcel->getActiveSheet()->SetCellValue("E".$i, $open_po);
        $objPHPExcel->getActiveSheet()->SetCellValue("F".$i, $etc);
        $objPHPExcel->getActiveSheet()->SetCellValue("F".$i, $eac);
        $i++;
        $rs->MoveNext();
    }

}
function setSwbsTabTieldHeaders($objPHPExcel, $i){
    $swbs_detail_tab_headers = array();
    $swbs_detail_tab_headers["A"] ="SWBS";
    $swbs_detail_tab_headers["B"] ="ITEM GROUP Description";
    $swbs_detail_tab_headers["C"] ="Buyers Responsible";
    $swbs_detail_tab_headers["D"] ="Gl Actuals";
    $swbs_detail_tab_headers["E"] ="Open PO";
    $swbs_detail_tab_headers["F"] ="ETC";
    $swbs_detail_tab_headers["G"] ="EAC";
    $objPHPExcel->setActiveSheetIndex($i);
    foreach ( $swbs_detail_tab_headers as $cell=>$value) {
        $objPHPExcel->getActiveSheet()->SetCellValue($cell."1", $value);
        setCellWidth($cell."1", $objPHPExcel, 20);
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
        $objWorkSheet->setTitle("Group $swbs_group");

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
function setCellWidth($column, $objPHPExcel, $width){
    $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth($width);
}
