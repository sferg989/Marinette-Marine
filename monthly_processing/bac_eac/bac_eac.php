<?php

include("../../inc/inc.php");

include("../../inc/lib/php/phpExcel-1.8/classes/phpexcel.php");
include("../../inc/lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$debug = true;
$schema = "bac_eac";
if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
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

$ship_name       = getProjectNameFromCode($ship_code);
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
$data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);

$prev_year          = $data["prev_year"];
$cur_year           = $data["cur_year"];
$prev_year_last2    = $data["prev_year_last2"];
$cur_year_last2     = $data["cur_year_last2"];
$prev_month         = $data["prev_month"];
$cur_month          = $data["cur_month"];
$prev_month_letters = $data["prev_month_letters"];
$cur_month_letters  = $data["cur_month_letters"];
$prev_full_month    = $data["prev_full_month"];
$cur_full_month     = $data["cur_full_month"];
$ship_name          = $data["ship_name"];
$g_path2_bac_eac_reports = $base_path.$ship_name."/".$ship_code."/EAC-BAC Compare/".$ship_code."/";
$cpr_file_array = array();

if($control =="load_data") {

    $ship_code = intval($ship_code);
    if($ship_code>=477)
    {
        $cpr_file_array["02-01 FY14AF CPR 1 LBR_Labor Only"]       = "_cpr1l";
        $cpr_file_array["02-01 FY14AF CPR 1 MAT_Material and ODC"] = "_cpr1m";
        $cpr_file_array["02-01 FY14AF CPR1 DOL"]                   = "_cpr1d";
        $cpr_file_array["02-01 FY14AF CPR1 HRS"]                   = "_cpr1h";
        $cpr_file_array["02-02 FY14AF CPR2D WBS"]                  = "_cpr2d_wbs";
        $cpr_file_array["02-02FY14AFCPR2DOBSBAT"]                  = "_cpr2d_obs";
        $cpr_file_array["02-02 FY14AF CPR2H OBS"]                  = "_cpr2h_obs";
        $cpr_file_array["02-02 FY14AF CPR2H WBS"]                  = "_cpr2h_wbs";
        $cpr_file_array["02-02 FY14AF CPR2L OBS_Labor Only"]       = "_cpr2l_obs";
        $cpr_file_array["02-02 FY14AF CPR2L WBS_Labor Only"]       = "_cpr2l_wbs";
        $cpr_file_array["02-02 FY14AF CPR2M OBS_Material and ODC"] = "_cpr2m_obs";
        $cpr_file_array["02-02 FY14AF CPR2M WBS_Material and ODC"] = "_cpr2m_wbs";
    }
    else{
        $cpr_file_array["02-02H CPR 2 OutSource"] = "_pre17_cpr2o";
        $cpr_file_array["02-02M CPR 2 Material"]  = "_pre17_cpr2m";
        $cpr_file_array["02-02L CPR 2 Labor"]     = "_pre17_cpr2l";
        $cpr_file_array["02-02D CPR 2 Dollars"]   = "_pre17_cpr2d";
        $cpr_file_array["02-02H CPR 2 Hours"]     = "_pre17_cpr2h";
        $cpr_file_array["02-01M CPR 1 Material"]  = "_pre17_cpr1m";
        $cpr_file_array["02-01L CPR 1 Labor"]     = "_pre17_cpr1l";
        $cpr_file_array["02-01H CPR 1 Hours"]     = "_pre17_cpr1h";
        $cpr_file_array["02-01D CPR 1 Dollars"]   = "_pre17_cpr1d";
    }
    if(strlen($code)==3)
    {
        $ship_code = "0".$code;
    }

    foreach ($cpr_file_array as $file_name => $table_name_short_name) {

        $have_we_reached_item = false;
        $table_name           = $rpt_period . $table_name_short_name;
        $create_table         = checkIfTableExists($schema, $table_name);

        if ($create_table == "create_table") {
            createTableFromBase($schema, "template" . $table_name_short_name, $table_name);
            sleep(.5);
        }
        deleteShipFromTable($ship_code, $table_name, $schema);
        $path2_input = $g_path2_bac_eac_reports . $file_name . ".csv";
        if(file_exists ($path2_input)==false){
            print "could not find $path2_input";
            continue;
        }
        $handle     = fopen($path2_input, "r");

        $insert_sql = "
            insert into $schema.$table_name (
                ship_code,
                item,
                s_cur,
                p_cur,
                a_cur,
                sv_cur,
                cv_cur,
                s_cum,
                p_cum,
                a_cum,
                sv_cum,
                cv_cum,
                adj_cv,
                adj_sv,
                adj_s,
                s_vac,
                est_vac,
                vac) 
                values
            ";
        //loop through the csv file and insert into database
        $sql = $insert_sql;
        /*create counter so insert 1000 rows at a time.*/

        $i = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $item       = trim($data[0]);
            $s_cur      = removeCommanDollarSignParan($data[2]);
            $p_cur      = removeCommanDollarSignParan($data[3]);
            $a_cur      = removeCommanDollarSignParan($data[4]);
            $sv_cur     = removeCommanDollarSignParan($data[5]);
            $cv_cur     = removeCommanDollarSignParan($data[6]);
            $s_cum      = removeCommanDollarSignParan($data[7]);
            $p_cum      = removeCommanDollarSignParan($data[8]);
            $a_cum      = removeCommanDollarSignParan($data[9]);
            $sv_cum     = removeCommanDollarSignParan($data[11]);
            $cv_cum     = removeCommanDollarSignParan($data[13]);
            $adj_cv     = removeCommanDollarSignParan($data[14]);
            $adj_sv     = removeCommanDollarSignParan($data[14]);
            $adj_s      = removeCommanDollarSignParan($data[16]);
            $s_vac      = removeCommanDollarSignParan($data[17]);
            $est_vac    = removeCommanDollarSignParan($data[18]);
            $vac        = removeCommanDollarSignParan($data[19]);
            $total_test = strpos($item, "TOTAL");
            if ($item == "ITEM") {
                $i++;
                $have_we_reached_item = true;
                continue;
            }
            if ($have_we_reached_item == false) {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "(1)") {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "HOURS") {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "LABOR Labor") {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "OUT") {
                $i++;
                continue;
            }
            if ($total_test !== false) {
                $i++;
                continue;
            }
            if ($item == "") {
                $i++;
                continue;
            }
            //print "this is the $item and the count $i\r";
            $sql .=
                "(
                $ship_code,
                    '$item',
                    $s_cur,
                    $p_cur,
                    $a_cur,
                    $sv_cur,
                    $cv_cur,             
                    $s_cum,
                    $p_cum,
                    $a_cum,
                    $sv_cum,
                    $cv_cum,
                    $adj_cv,
                    $adj_sv,
                    $adj_s,
                    $s_vac,
                    $est_vac,
                    $vac
                ),";
            $i++;
        }
        $sql = substr($sql, 0, -1);

        $junk = dbCall($sql, $schema);

    }
    die();
}
if($control=="beac_eac_detail_chart"){
    $table_headers = "
<table class=\"table table-sm \">
    <tr>
        <td>BAC EAC Comparison</td>
        <td  colspan=\"8\">Estimate at Complete</td>
    </tr>
    <tr>
        <td>Month End</td>
        <td colspan=\"4\">Hours</td>
        <td colspan=\"4\">Dollars</td>             
    </tr>
    <tr>
        <td>By Oragization</td>
        <td>$cur_full_month</td>
        <td>$prev_full_month</td>
        <td>Diff</td>
        <td>% Change</td>            
        <td>$cur_full_month</td>
        <td>$prev_full_month</td>
        <td>Diff</td>
        <td>% Change</td>
       
    </tr>    

    ";

    $table_array = returnTableArray($ship_code);

    foreach ($table_array as $key =>$value) {
        $html.= "$table_headers";
        $eac_data = getBACEACData($rpt_period, $prev_rpt_period, $ship_code,$value, "est_vac");

        foreach ($eac_data as $item=>$value){

            $html.="
            <tr>
                <td>$item</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"]-$value["prev_est_vach"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vach"]-$value["prev_est_vach"])/$value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"]-$value["prev_est_vacd"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vacd"]-$value["prev_est_vacd"])/$value["cur_est_vacd"])."</td>
                
            </tr>";
        }
        $html.= "<tr></tr><tr></tr></table>";
    }

    $table_headers = "
    <table class=\"table table-sm \">
        <tr>
            <td>BAC EAC Comparison</td>
            <td  colspan=\"8\">Budget at Complete</td>
        </tr>
        <tr>
            <td>Month End</td>
            <td colspan=\"4\">Hours</td>
            <td colspan=\"4\">Dollars</td>             
        </tr>
        <tr>
            <td>By Oragization</td>
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>
           
        </tr>    

    ";

    foreach ($table_array as $key =>$value) {
        $html.= "$table_headers";
        $eac_data = getBACEACData($rpt_period, $prev_rpt_period, $ship_code,$value, "s_vac");
        //var_dump($eac_data);
        foreach ($eac_data as $item=>$value){
            $html.="
            <tr>
                <td>$item</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"]-$value["prev_est_vach"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vach"]-$value["prev_est_vach"])/$value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"]-$value["prev_est_vacd"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vacd"]-$value["prev_est_vacd"])/$value["cur_est_vacd"])."</td>
                
            </tr>";
        }
        $html.= "<tr></tr><tr></tr></table>";
    }
    $table_headers = "
    <table class=\"table table-sm \">
        <tr>
            <td>BAC EAC Comparison</td>
            <td  colspan=\"8\">Actual Cost Increment</td>
        </tr>
        <tr>
            <td>Month End</td>
            <td colspan=\"4\">Hours</td>
            <td colspan=\"4\">Dollars</td>             
        </tr>
        <tr>
            <td>By Oragization</td>
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>
           
        </tr>    

    ";

    foreach ($table_array as $key =>$value) {
        $html.= "$table_headers";
        $eac_data = getBACEACData($rpt_period, $prev_rpt_period, $ship_code,$value, "a_cur");

        foreach ($eac_data as $item=>$value){
            $html.="
            <tr>
                <td>$item</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"]-$value["prev_est_vach"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vach"]-$value["prev_est_vach"])/$value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"]-$value["prev_est_vacd"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vacd"]-$value["prev_est_vacd"])/$value["prev_est_vacd"])."</td>
                
            </tr>";
        }
        $html.= "<tr></tr><tr></tr></table>";
    }
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."export.xls";
    $path = "../../util/excel_exports/".$token."export.xls";
    file_put_contents($path2_export,$html);

    /*BUILD SECOND  worksheet
    */
    $matl_html = "";
    $table_headers = "
    <table class=\"table table-sm \">
        <tr>
            <td>BAC EAC Comparison</td>
            <td  colspan=\"12\">ESTIMATE AT COMPLETE</td>
        </tr>
        <tr>
            <td>Month End</td>
            <td colspan=\"4\">LABOR DOLLARS</td>
            <td colspan=\"4\">MATERIAL Dollars</td>             
            <td colspan=\"4\">TOTAL Dollars</td>             
        </tr>
        <tr>
            <td>By Oragization</td>
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>
        </tr>    

    ";
    $table_array = returnTableArraylABORMATLDOLLARS($ship_code);
    foreach ($table_array as $key =>$value) {
        $matl_html.= "$table_headers";
        $eac_data = getBACEACDataLABORMTL($rpt_period, $prev_rpt_period, $ship_code,$value, "est_vac");

        foreach ($eac_data as $item=>$value){
            $cur_labor_dollars  = formatNumberPHPEXCEL($value["cur_est_vacl"]);
            $prev_labor_dollars = formatNumberPHPEXCEL($value["prev_est_vacl"]);
            $labor_diff         = $cur_labor_dollars - $prev_labor_dollars;
            $labor_pc           = formatPercentPHPEXCEL($labor_diff / $cur_labor_dollars);
     
            $cur_matl_dollars  = formatNumberPHPEXCEL($value["cur_est_vacm"]);
            $prev_matl_dollars = formatNumberPHPEXCEL($value["prev_est_vacm"]);
            $matl_diff         = $cur_matl_dollars - $prev_matl_dollars;
            $matl_pc           = formatPercentPHPEXCEL($matl_diff / $cur_matl_dollars);

            $cur_td = $cur_labor_dollars + $cur_matl_dollars;
            $prev_td = $prev_labor_dollars + $prev_matl_dollars;
            $td_diff = $cur_td- $prev_td;
            $td_pc  = formatPercentPHPEXCEL($td_diff/$cur_td);
            $matl_html.="
            <tr>
                <td>$item</td>
                <td>$cur_labor_dollars</td>
                <td>$prev_labor_dollars</td>
                <td>$labor_diff</td>
                <td>$labor_pc</td>
                <td>$cur_matl_dollars</td>
                <td>$prev_matl_dollars</td>
                <td>$matl_diff</td>
                <td>$matl_pc</td>
                <td>$cur_td</td>
                <td>$prev_td</td>
                <td>$td_diff</td>
                <td>$td_pc</td>
                
            </tr>";
        }
        $matl_html.= "<tr></tr><tr></tr></table>";
    }
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."matl_export.xls";
    $path2matlandLabor = "../../util/excel_exports/".$token."matl_export.xls";
    file_put_contents($path2_export,$matl_html);

    /*3.  Make Summary Page

     * */
    header("");
    //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';

    $objPHPExcel    = formatExcelSheet($path, "Cost Growth");
    $matlLaborSheet = formatExcelSheetLBR($path2matlandLabor, "Labor and MATL Dollars");

    $allsheets = $matlLaborSheet->getAllSheets();
    foreach ($allsheets as $sheet) {
        $objPHPExcel->addExternalSheet($sheet);
    }
    //$objPHPExcel->setActiveSheetIndex(1);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    $objWriter->save($path);
    die($html."<>".$path);
}