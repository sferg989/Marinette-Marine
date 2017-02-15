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
function getCorrespondingTable($ship_code, $table_name){
    if($ship_code>=477){
        switch ($table_name) {
            case "_cpr2h_obs":
                $table_name = "_cpr2h_obs";
        break;
            case "_cpr2d_obs":
                $table_name = "_cpr2d_obs";
        break;
            case "_cpr1h":
                $table_name = "_cpr1h";
        break;
            case "_cpr1d":
                $table_name = "_cpr1d";
        break;
            case "_cpr2h_wbs":
                $table_name = "_cpr2h_wbs";
        break;
            case "_cpr2d_wbs":
                $table_name = "_cpr2d_wbs";
        }
        return $table_name;
    }
    else{
        switch ($table_name) {
            case "_cpr2h_obs":
                    $table_name = "_pre17_cpr2h";
                break;
            case "_cpr2d_obs":
                $table_name = "_pre17_cpr2d";
                break;
            case "_cpr1h":
                $table_name = "_pre17_cpr1h";
                break;
            case "_cpr1d":
                $table_name = "_pre17_cpr1d";
                break;
            case "_cpr1l":
                $table_name = "_pre17_cpr1l";
                break;
            case "_cpr1m":
                $table_name = "_pre17_cpr1m";
                break;
            case "_cpr2h_wbs":
                $table_name = "_pre17_cpr2h";
                break;
            case "_cpr2d_wbs":
                $table_name = "_pre17_cpr2d";
                break;
            case "_cpr2o":
                $table_name = "_pre17_cpr2o";
                break;
            case "_cpr2m_wbs":
                $table_name = "_pre17_cpr2m";
                break;
            case "_cpr2l_wbs":
                $table_name = "_pre17_cpr2l";
                break;
            case "_cpr2l_obs":
                $table_name = "_pre17_cpr2l";
                break;
            case "_cpr2m_obs":
                $table_name = "_pre17_cpr2m";
                break;
        }
        return $table_name;
    }
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
function createItemArray($rpt_period, $table_array,$ship_code){

    $table1 = $table_array["d"];
    $table2 = $table_array["h"];
    $data_array = array();


    $sql = " 
        select item, order_id from
          (select item, order_id from ".$rpt_period.$table1." where ship_code = '$ship_code'
          union all select item, order_id from ".$rpt_period.$table2." where ship_code = '$ship_code') s
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
function createItemArrayLABORMTL($rpt_period, $table_array, $ship_code){

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
                  select item, order_id from ".$rpt_period."_cpr2d_wbs where ship_code = '$ship_code'
                    union all
                  select item, order_id from ".$rpt_period."_cpr2h_wbs where ship_code = '$ship_code'";
        $ob = "item";
    }
    $data_array = array();
    $sql = " 
          select item, order_id from
              (
                  select item, order_id from ".$rpt_period.$table1."
                   where ship_code = '$ship_code'
                    union all
                  select item, order_id from ".$rpt_period.$table2." 
                  where ship_code = '$ship_code'
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
    $data_array = createItemArray($rpt_period,$table_array, $ship_code);
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
    $item_array = createItemArrayLABORMTL($rpt_period,$table_array, $ship_code);
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
        //print $sql;
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
    //print $sql;
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
function getTotalHoursByOBS($prev_rpt_period,$rpt_period,$table_name, $ship_code, $sum_field, $obs){
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
        where 
        prev.item = '$obs'
        and prev.ship_code = '$ship_code'
        ";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $data["prev"]   = $prev;
    $data["cur"]    = $cur;
    return $data;
}
function getTotalHoursByMMCOtherSalary($prev_rpt_period,$rpt_period,$table_name, $ship_code, $sum_field){
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item 
        and prev.ship_code = cur.ship_code
        where 
        prev.item in 
        ('650 Production Planning & Control', 
        '300 Purchasing',
        '611 Program Management', 
        '200 Finance',
        '800 Contracts',
        '546 Quality Assurance')
        and prev.ship_code = '$ship_code'
        group by prev.ship_code";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $data["prev"]   = $prev;
    $data["cur"]    = $cur;
    return $data;
}
function getTotalHoursByMMCOtherSalaryPRE17($prev_rpt_period,$rpt_period,$table_name, $ship_code, $sum_field){
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item 
        and prev.ship_code = cur.ship_code
        where 
        prev.item in 
        ('180 SECURITY', 
        '611 PROGRAM MANAGEMENT',
        '300 PURCHASING', 
        '310 INTEGRATED LOGISTICS SUPPORT',
        '800 CONTRACTS',
        '546 QUALITY CONTROL',
        '650 PLANNING'
        )
        and prev.ship_code = '$ship_code'
        group by prev.ship_code";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $table_name    = getCorrespondingTable($ship_code, "_cpr2o");
    $out_h         = getTotalOutsourceHoursBYOBS($prev_rpt_period, $rpt_period, $table_name, $ship_code, "est_vac", "OUT", "546 QUALITY CONTROL");
    $prev_546out_h = $out_h["prev"];
    $cur_546out_h  = $out_h["cur"];
    $data["prev"]   = $prev-$prev_546out_h;
    $data["cur"]    = $cur-$cur_546out_h;
    return $data;
}

function getMR($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field)
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
        and  cur.item = 'f. MANAGEMENT RESERVE'
        ";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $data["prev"]   = $prev;
    $data["cur"]    = $cur;
    return $data;
}
function getLABOREACDIFF($prev_rpt_period,$rpt_period,$ship_code)
{
    if($ship_code>=477){
        $table_name = "_cpr2h_obs";
    }
    else{
        $table_name = "_pre17_cpr2h";
    }
    $sql  = "        
        select
            cur.item
        from ".$prev_rpt_period.$table_name." prev 
        left join " . $rpt_period.$table_name." cur 
        on prev.item = cur.item 
        and prev.ship_code = cur.ship_code
        where prev.ship_code = '$ship_code'
        and  cur.item <> 'CLASSIFICATION (When Filled In)'
        and  cur.item <> 'd. UNDISTRIBUTED BUDGET'
        and cur.item <> 'f. MANAGEMENT RESERVE'
        and cur.item <> 'b. COST OF MONEY'
        and cur.item <> 'c. GENERAL AND ADMINISTRATIVE'
        group by item order by prev.order_id
        ";
    $rs = dbCall($sql,"bac_eac");
    $html = "";
    $total_hours = 0;
    $total_lrb_d = 0;
    while (!$rs->EOF)
    {
        $item = $rs->fields["item"];
        $diff = getLaborDollarsandHoursEACDiff($prev_rpt_period,$rpt_period, $ship_code, $item);
        $hours = $diff["hours"];
        $labor_d = $diff["d"];
        $html.= "
            <tr>
                <td>$item</td>
                <td>$hours</td>
                <td>$labor_d</td>
                <td></td>
            </tr>
        ";
        $total_hours+=$hours;
        $total_lrb_d+=$labor_d;
        $rs->MoveNext();
    }
    $html.="
                <tr>
                <td>TOTAL</td>
                <td>$total_hours</td>
                <td>$total_lrb_d</td>
                <td>**NO G&A or COM in DOLLARS</td>
            </tr>
    ";
    return $html;
}
function getLaborDollarsandHoursEACDiff($prev_rpt_period,$rpt_period, $ship_code, $item)
{
    if($ship_code>=477){
        $table_name = "_cpr2h_obs";
    }
    else{
        $table_name = "_pre17_cpr2h";
    }
    $diff["hours"] = getEACDiff($prev_rpt_period,$rpt_period, $table_name, $ship_code, $item);


    if($ship_code>=477){
        $table_name = "_cpr2d_obs";
    }
    else{
        $table_name = "_pre17_cpr2d";
    }
    $diff["d"] = getEACDiff($prev_rpt_period,$rpt_period, $table_name, $ship_code, $item);
    return $diff;
}
function getEACDiff($prev_rpt_period,$rpt_period, $table_name, $ship_code, $item)
{
    $sql = "        
        select
            cur.item,
            sum(cur.est_vac) cur,
            sum(prev.est_vac)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item and prev.ship_code = cur.ship_code
        where cur.ship_code = $ship_code 
        and  cur.item = '$item'
        ";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $diff = $cur-$prev;
    return $diff;
}
function getMATLEACDIFF($prev_rpt_period,$rpt_period,$ship_code)
{
    $html = "";
    if($ship_code>=477){
        $table_name = "_cpr1m";
    }
    else{
        $table_name = "_pre17_cpr1m";
    }
    $sub_total_m = getSubTotalRow($prev_rpt_period, $rpt_period, $table_name, $ship_code, "est_vac");
    $prev_total_m = $sub_total_m["prev"];
    $cur_total_m = $sub_total_m["cur"];
    $html.="
                <tr>
                <td>Month End $prev_rpt_period Material EAC Changes</td>
                <td>$prev_total_m</td>
                <td></td>
                <td></td>
            </tr>
    ";
    $html.="<tr></tr>";

    $sql  = "        
        select
            cur.item
        from ".$prev_rpt_period.$table_name." prev 
        left join " . $rpt_period.$table_name." cur 
        on prev.item = cur.item 
        and prev.ship_code = cur.ship_code
        where prev.ship_code = '$ship_code'
        and  cur.item <> 'CLASSIFICATION (When Filled In)'
        and  cur.item <> 'd. UNDISTRIBUTED BUDGET'
        and cur.item <> 'f. MANAGEMENT RESERVE'
        and cur.item <> 'b. COST OF MONEY'
        and cur.item <> '9. RECONCILIATION TO CONTRACT BUDGET BASELINE'
        and cur.item <> 'a. VARIANCE ADJUSTMENT'
        and cur.item <> 'c. GENERAL AND ADMINISTRATIVE'
        group by item order by prev.order_id
        ";
    $rs = dbCall($sql,"bac_eac");
    $total_d = 0;
    while (!$rs->EOF)
    {
        $item = $rs->fields["item"];
        $d = getMaterialDollarsEACDiff($prev_rpt_period,$rpt_period, $ship_code, $item);
        $html.= "
            <tr>
                <td>$item</td>
                <td>$d</td>
                <td></td>
                <td></td>
            </tr>
        ";
        $total_d+=$d;
        $rs->MoveNext();
    }
    $html.="<tr></tr>";
    $html.="
                <tr>
                <td>Month End $rpt_period Material EAC Changes</td>
                <td>$total_d</td>
                <td></td>
                <td></td>
            </tr>
    ";
    $html.="<tr></tr>";
    $html.="
                <tr>
                <td>Month End $rpt_period Material EAC Changes</td>
                <td>$cur_total_m</td>
                <td></td>
                <td></td>
            </tr>
    ";
    return $html;
}
function getMaterialDollarsEACDiff($prev_rpt_period,$rpt_period, $ship_code, $item)
{
    if($ship_code>=477){
        $table_name = "_cpr1m";
    }
    else{
        $table_name = "_pre17_cpr1m";
    }
    $sql = "        
        select
            cur.item,
            sum(cur.est_vac) cur,
            sum(prev.est_vac)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item and prev.ship_code = cur.ship_code
        where cur.ship_code = $ship_code 
        and  cur.item = '$item'
        ";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $diff = $cur-$prev;
    return $diff;
}
function getTotalOutsourceHoursBYOBS($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field, $data_type, $obs){
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
        and prev.item = '$obs'
        and prev.ship_code = '$ship_code'
        ";

    $rs           = dbCall($sql, "bac_eac");
    $prev         = $rs->fields["prev"];
    $cur          = $rs->fields["cur"];
    $data["prev"] = $prev;
    $data["cur"]  = $cur;
    return $data;
}

function buildCurCumSpiCpi(){
    $html = "";
    $html.="
    <table>
        <tr>
            <td></td>
            <td colspan='2'>Program</td>
            <td colspan='2'>Labor</td>
            <td colspan='2'>Material</td>
        </tr>        
        <tr>
            <td></td>
            <td >Current</td>
            <td >previous</td>
            <td >Current</td>
            <td >previous</td>
            <td >Current</td>
            <td >previous</td>
        </tr>
        <tr>
            <td>CUR SPI</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>        
        <tr>
            <td>CUR CPI</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>        
        <tr>
            <td>CUM SPI</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>        
        <tr>
            <td>CUM CPI</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    ";
    return $html;
}
function  returnBACEACTableParts($field, $prev,$cur,$diff,$pc){
    $html ="        
        <tr>
            <td>$field</td>    
            <td>$cur</td>    
            <td>$prev</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>";
    return $html;
}
function addExtraTableRow(){
    $html ="        
        <tr>
        </tr>";
    return $html;
}
function formatExcelSheetForDollars($sheet_index, $objPHPExcel, $range)
{
    $objPHPExcel->setActiveSheetIndex($sheet_index);
    $objPHPExcel->getActiveSheet()
        ->getStyle($range)
        ->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD);
}
function getTotalOutsourceDollarsPre17($prev_rpt_period,$rpt_period, $table_name, $ship_code, $sum_field){
    $data = array();
    $sql = "        
        select
            cur.item,
            sum(cur.$sum_field) cur,
            sum(prev.$sum_field)  prev
        from ".$prev_rpt_period.$table_name." prev 
        inner join ".$rpt_period.$table_name." cur 
        on prev.item = cur.item 
        and prev.ship_code = cur.ship_code
        where         
         prev.item in ('600 Manufacturing', '620 MANUFACTURING SUPERVISION', '546 QUALITY CONTROL')
        and prev.ship_code = '$ship_code'
        group by prev.ship_code";

    $rs     = dbCall($sql, "bac_eac");
    $prev   = $rs->fields["prev"];
    $cur    = $rs->fields["cur"];
    $data["prev"]   = $prev;
    $data["cur"]    = $cur;
    return $data;
}
