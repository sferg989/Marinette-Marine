<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 2/8/2017
 * Time: 12:47 PM
 */
include("lib/php/phpExcel-1.8/classes/phpexcel.php");
include("lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");


function returnTableArray($ship_code){
    $table_array = array();
    if($ship_code>=477){

        $table_array[0]["h"] = "_cpr2h_obs";
        $table_array[0]["d"] = "_cpr2d_obs";
        $table_array[1]["h"] = "_cpr1h";
        $table_array[1]["d"] = "_cpr1d";
        $table_array[2]["h"] = "_cpr2h_wbs";
        $table_array[2]["d"] = "_cpr2d_wbs";
    }
    else{
        $table_array[0]["h"] = "_pre17_cpr2h";
        $table_array[0]["d"] = "_pre17_cpr2d";
        $table_array[1]["h"] = "_pre17_cpr1h";
        $table_array[1]["d"] = "_pre17_cpr1d";
    }
    return $table_array;
}
function returnTableArraylABORMATLDOLLARS($ship_code){
    $table_array = array();
    if($ship_code>=477){
        $table_array[0]["l"] = "_cpr2l_obs";
        $table_array[0]["m"] = "_cpr2m_obs";
        $table_array[1]["l"] = "_cpr1l";
        $table_array[1]["m"] = "_cpr1m";
        $table_array[2]["l"] = "_cpr2l_wbs";
        $table_array[2]["m"] = "_cpr2m_wbs";
    }
    else{
        $table_array[0]["l"] = "_pre17_cpr2l";
        $table_array[0]["m"] = "_pre17_cpr2m";
        $table_array[1]["l"] = "_pre17_cpr1l";
        $table_array[1]["m"] = "_pre17_cpr1m";
    }
    return $table_array;
}
function formatExcelSheet($path2xlsfile, $sheet_title)
{
    $objPHPExcel = PHPExcel_IOFactory::load($path2xlsfile);

    $objPHPExcel->getActiveSheet()->setTitle($sheet_title);
    $objPHPExcel->getActiveSheet()
        ->getStyle('B4:D230')
        ->getNumberFormat()
        ->setFormatCode('#,##0');
    $objPHPExcel->getActiveSheet()
        ->getStyle('F4:H230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);;
    $objPHPExcel->getActiveSheet()
        ->getStyle('E4:E230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
    $objPHPExcel->getActiveSheet()
        ->getStyle('I4:I230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
    return $objPHPExcel;
}
function formatExcelSheetLBR($path2xlsfile, $sheet_title){
    $objPHPExcel  = PHPExcel_IOFactory::load($path2xlsfile);

    $objPHPExcel->getActiveSheet()->setTitle($sheet_title);
    $objPHPExcel->getActiveSheet()
        ->getStyle('B4:D230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
    $objPHPExcel->getActiveSheet()
        ->getStyle('E4:E230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
    $objPHPExcel->getActiveSheet()
        ->getStyle('F4:H230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
    $objPHPExcel->getActiveSheet()
        ->getStyle('I4:I230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
    $objPHPExcel->getActiveSheet()
        ->getStyle('J4:L230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
    $objPHPExcel->getActiveSheet()
        ->getStyle('M4:M230')
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('j')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('k')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('l')->setWidth(15);
    return $objPHPExcel;
}
function createItemArray($rpt_period, $table_array){

    $table1 = $table_array["d"];
    $table2 = $table_array["h"];
    $data_array = array();


    $sql = " 
        select item, order_id from
          (select item, order_id from ".$rpt_period.$table1." union all select item, order_id from ".$rpt_period.$table2.") s
        where item <> 'CLASSIFICATION (When Filled In)'
        and item <> 'f. MANAGEMENT RESERVE'
        and item <> '9. RECONCILIATION TO CONTRACT BUDGET BASELINE'
        and item <> 'a. VARIANCE ADJUSTMENT'
        and item <> 'd. UNDISTRIBUTED BUDGET'
        group by item
        order by order_id
        ";

    $rs = dbCall($sql,"bac_eac");
    while (!$rs->EOF)
    {
        $item = $rs->fields["item"];
        $data_array[$item] = "";
        $rs->MoveNext();
    }
    //AD ub AFTER THE subTOTAL  row.
    $data_array["SUBTOTAL"] = "";
    $data_array["d. UNDISTRIBUTED BUDGET"] = "";
    return $data_array;
}
function createItemArrayLABORMTL($rpt_period, $table_array){

    $table1 = $table_array["l"];
    $table2 = $table_array["m"];
    $union_sql = "";
    /*Since WBS is the only group that has values in the item array, but does not have values, we want t
       include it in the list.
     * */
    $ob = "order_id";
    if(strpos($table1, "wbs")>0){
        $union_sql = "                    
            union all
                  select item, order_id from ".$rpt_period."_cpr2d_wbs 
                    union all
                  select item, order_id from ".$rpt_period."_cpr2h_wbs ";
        $ob = "item";
    }
    $data_array = array();
    $sql = " 
          select item, order_id from
              (
                  select item, order_id from ".$rpt_period.$table1." 
                    union all
                  select item, order_id from ".$rpt_period.$table2." 
                    $union_sql
              ) s
        where item <> 'CLASSIFICATION (When Filled In)'
        and item <> 'f. MANAGEMENT RESERVE'
        and item <> '9. RECONCILIATION TO CONTRACT BUDGET BASELINE'
        and item <> 'a. VARIANCE ADJUSTMENT'
        and item <> 'd. UNDISTRIBUTED BUDGET'
        and item <> 'f. MANAGEMENT RESERVE'
        group by item
        order by $ob
        ";

    $rs = dbCall($sql,"bac_eac");
    while (!$rs->EOF)
    {
        $item = $rs->fields["item"];
        $data_array[$item] = 0;
        $rs->MoveNext();
    }
    //AD ub AFTER THE subTOTAL  row.
    $data_array["SUBTOTAL"] = 0;
    $data_array["d. UNDISTRIBUTED BUDGET"] = 0;
    return $data_array;
}
function getTotalRow($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field)
{
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item and prev.ship_code = cur.ship_code
        where cur.ship_code = $ship_code 
        and  cur.item <> 'CLASSIFICATION (When Filled In)'
        and cur.item <> 'f. MANAGEMENT RESERVE'
        ";
    $rs = dbCall($sql, "bac_eac");
    $prev = $rs->fields["prev"];
    $cur = $rs->fields["cur"];
    $data["prev"] = $prev;
    $data["cur"] = $cur;
    return $data;
}


function getSubTotalRow($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field)
{
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item and prev.ship_code = cur.ship_code
        where cur.ship_code = $ship_code 
        and  cur.item <> 'CLASSIFICATION (When Filled In)'
        and  cur.item <> 'd. UNDISTRIBUTED BUDGET'
        and cur.item <> 'f. MANAGEMENT RESERVE'
        ";

    $rs = dbCall($sql, "bac_eac");
    $prev = $rs->fields["prev"];
    $cur = $rs->fields["cur"];
    $data["prev"] = $prev;
    $data["cur"] = $cur;
    return $data;
}

function getBACEACData($rpt_period, $prev_rpt_period, $ship_code, $table_array, $field){
    $data_array = createItemArray($rpt_period,$table_array);
    //var_dump($data_array);
    foreach ($table_array as $key=>$value){
        $sql = "
        select
              cur.item,
              sum(cur.$field) cur,
              sum(prev.$field)  prev
            from ".$prev_rpt_period.$value." prev 
              inner join ".$rpt_period.$value." cur 
            on prev.item = cur.item and prev.ship_code = cur.ship_code
            where cur.ship_code = $ship_code and  cur.item <> 'CLASSIFICATION (When Filled In)'
            group by cur.item 
        ";

        $rs = dbCall($sql,"bac_eac");
        while (!$rs->EOF)
        {
            $cur  = $rs->fields["cur"];
            $prev = $rs->fields["prev"];
            $item = $rs->fields["item"];
            if($item =="d. UNDISTRIBUTED BUDGET"){
                $sub_total_data = getSubTotalRow($prev_rpt_period,$rpt_period, $value, $ship_code, $field);
                $data_array["SUBTOTAL"]["cur_est_vac" . $key]  = $sub_total_data["cur"];
                $data_array["SUBTOTAL"]["prev_est_vac" . $key] = $sub_total_data["prev"];
            }
            if(array_key_exists($item,$data_array)==true)   {
                //print $item."<br>";

                $data_array[$item]["cur_est_vac" . $key]  = $cur;
                $data_array[$item]["prev_est_vac" . $key] = $prev;
            }
            $rs->MoveNext();
        }
        $sum_data = getTotalRow($prev_rpt_period,$rpt_period, $value, $ship_code, $field);
        $data_array["TOTAL"]["cur_est_vac" . $key]  = $sum_data["cur"];
        $data_array["TOTAL"]["prev_est_vac" . $key] = $sum_data["prev"];
    }
    //var_dump($data_array);
    return $data_array;
}
function getBACEACDataLABORMTL($rpt_period, $prev_rpt_period, $ship_code, $table_array, $field){
    $item_array = createItemArrayLABORMTL($rpt_period,$table_array);
    $value_array = array();
    foreach ($table_array as $key=>$value){
        $sql = "
        select
              cur.item,
              cur.$field cur,
              prev.$field prev
            from ".$prev_rpt_period.$value." prev 
              inner join ".$rpt_period.$value." cur 
            on prev.item = cur.item and prev.ship_code = cur.ship_code
            where cur.ship_code = $ship_code and  cur.item <> 'CLASSIFICATION (When Filled In)'
            group by cur.item 
        ";

        $rs = dbCall($sql,"bac_eac");
        while (!$rs->EOF)
        {
            $cur  = $rs->fields["cur"];
            $prev = $rs->fields["prev"];
            $item = $rs->fields["item"];
            if($item =="d. UNDISTRIBUTED BUDGET"){
                $sub_total_data = getSubTotalRow($prev_rpt_period,$rpt_period, $value, $ship_code, $field);
                $value_array["SUBTOTAL"]["cur_est_vac" . $key]  = $sub_total_data["cur"];
                $value_array["SUBTOTAL"]["prev_est_vac" . $key] = $sub_total_data["prev"];
            }
            if(array_key_exists($item,$item_array)==true)   {

                $value_array[$item]["cur_est_vac" . $key]  = $cur;
                $value_array[$item]["prev_est_vac" . $key] = $prev;
            }

            $rs->MoveNext();
        }
        $sum_data = getTotalRow($prev_rpt_period,$rpt_period, $value, $ship_code, $field);
        $value_array["TOTAL"]["cur_est_vac" . $key]  = $sum_data["cur"];
        $value_array["TOTAL"]["prev_est_vac" . $key] = $sum_data["prev"];
    }

    $result = array_merge($item_array,$value_array);
    return $result;
}
function getUB($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field)
{
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item and prev.ship_code = cur.ship_code
        where cur.ship_code = $ship_code 
        and  cur.item = 'd. UNDISTRIBUTED BUDGET'
        ";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $data["prev"]   = $prev;
    $data["cur"]    = $cur;
    return $data;
}
function getTotalOutsourceHours($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field, $data_type){
    $data = array();
    $sql = "        
        select
            cur.item,
            cur.$sum_field cur,
            prev.$sum_field  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item 
        and prev.ship_code = cur.ship_code
        and prev.data_type = cur.data_type
        where 
        prev.data_type = '$data_type' 
        and prev.item = '600 Manufacturing'
        and prev.ship_code = '$ship_code'
        ";
    print $sql;
    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $data["prev"]   = $prev;
    $data["cur"]    = $cur;
    return $data;
}
function getSubTotalRowNOCOM($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field)
{
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item and prev.ship_code = cur.ship_code
        where cur.ship_code = $ship_code 
        and  cur.item <> 'CLASSIFICATION (When Filled In)'
        and  cur.item <> 'd. UNDISTRIBUTED BUDGET'
        and cur.item <> 'f. MANAGEMENT RESERVE'
        and cur.item <> 'b. COST OF MONEY'
        ";

    $rs = dbCall($sql, "bac_eac");
    $prev = $rs->fields["prev"];
    $cur = $rs->fields["cur"];
    $data["prev"] = $prev;
    $data["cur"] = $cur;
    return $data;
}
