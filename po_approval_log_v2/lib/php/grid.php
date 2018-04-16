<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
$user            = $_SESSION["user_name"];
session_write_close();
$rpt_period      = currentRPTPeriod();
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);

function returnHeaders(){
    $header_array[] = "Hull";
    $header_array[] = "DATE";
    $header_array[] = "WEEK";
    $header_array[] = "PO";
    $header_array[] = "Buyer";
    $header_array[] = "WP";
    $header_array[] = "SWBS GROUP";
    $header_array[] = "ITEM";
    $header_array[] = "Value";
    $header_array[] = "ETC";
    $header_array[] = "Change";
    $header_array[] = "QTY";
    $header_array[] = "EBOM";
    $header_array[] = "Remaining";
    $header_array[] = "CAM";
    $header_array[] = "Reason for Change";
    $header_array[] = "Funding Source";
    $header_array[] = "Additional Notes";
    $header_array[] = "Week For Rollup";
    $header_array[] = "Year";
    return $header_array;
}

function return0487Headers(){
    $header_array[] = "Hull";
    $header_array[] = "WP";
    $header_array[] = "ITEM";
    $header_array[] = "Description";
    $header_array[] = "EAC";
    $header_array[] = "EBOM REQURIEMENT";
    $header_array[] = "LAST HULL ";
    $header_array[] = "LAST PO ";
    $header_array[] = "LAST PO LINE";
    $header_array[] = "PO DATE";
    $header_array[] = "PO UNIT PRICE";
    $header_array[] = "PO QTY";
    $header_array[] = "PO DOLALRS";
    return $header_array;
}
function returnBaanItemDataSql($item){
    $sql = "
            SELECT
              TOP 1
              a.t_cprj AS                                                                     last_hull,
              CONVERT(VARCHAR(8), a.t_odat, 1)                                                create_date,
              CASE
              WHEN a.t_pacn <> 0
                THEN substring(a.t_pacn, 2, 3)
              ELSE b.t_cpcp
              END      AS                                                                     swbs,
              a.t_item AS                                                                     item,
              CASE
              WHEN a.t_cprj <> '      '
                THEN c.t_dsca
              ELSE d.t_dsca
              END      AS                                                                     description,
              CASE
              WHEN a.t_cprj <> '      '
                THEN
                  CASE
                  WHEN c.t_csel = ' NR'
                    THEN 'NRE'
                  ELSE ''
                  END
              ELSE
                CASE
                WHEN d.t_csel = ' NR'
                  THEN 'NRE'
                ELSE ''
                END
              END      AS                                                                     nre,
              a.t_suno AS                                                                     vendor,
              a.t_orno AS                                                                     po,
              a.t_pono AS                                                                     line,
              a.t_pric AS                                                                     unit_price,
              a.t_oqua AS                                                                     order_qty,
              a.t_ecpr AS                                                                     ecp_rea,
              e.t_qana AS                                                                     ebom,
              CASE
              WHEN a.t_dqua <> 0
                THEN (a.t_dqua + a.t_bqua) * a.t_pric
              ELSE a.t_oqua * a.t_pric
              END      AS                                                                     c_amnt,
              (SELECT
                 TOP 1 LTRIM(RTRIM(bc.t_bitm)) AS wp
               FROM ttipcs950490 AS ab
                 LEFT JOIN ttipcs952490 AS bc ON ab.t_bdgt = bc.t_bdgt
               WHERE ab.t_cprj = a.t_cprj AND bc.t_bdgt = ab.t_bdgt AND bc.t_item = a.t_item) wp
            
            FROM ttdpur041490 a
              LEFT JOIN ttdpur045490 b ON b.t_orno = a.t_orno AND b.t_pono = a.t_pono AND b.t_srnb = 0
              LEFT JOIN ttipcs021490 c ON c.t_cprj = a.t_cprj AND c.t_item = a.t_item
              LEFT JOIN ttiitm001490 d ON d.t_item = a.t_item
              LEFT JOIN ttiitm901490 e ON c.t_cprj = e.t_cprj AND c.t_item = e.t_item
              LEFT JOIN ttccom001490 f ON f.t_emno = c.t_buyr
            WHERE ltrim(rtrim(a.t_item)) = '$item' 
            and a.t_oqua <> 0
            and ltrim(rtrim(a.t_cprj)) in (
                  '0465',
                  '0467',
                  '0469',
                  '0471',
                  '0473',
                  '0475',
                  '0477',
                  '0479',
                  '0481',
                  '0483',
                  '0485'
            )
            and CASE WHEN a.t_dqua <> 0
                THEN (a.t_dqua + a.t_bqua) * a.t_pric
              ELSE a.t_oqua * a.t_pric
              END > 0
            ORDER BY a.t_odat DESC";
    return $sql;
}
function returnBaanPOSQL($po){
    $sql = "               
        SELECT
              a.t_cprj AS                                                                     ship_code,
            CONVERT(VARCHAR(8), a.t_odat, 1) create_date,
              CASE
              WHEN a.t_pacn <> 0
                THEN substring(a.t_pacn, 2, 3)
              ELSE b.t_cpcp
              END      AS                                                                     swbs,
              a.t_item AS                                                                     item,
              CASE
              WHEN a.t_cprj <> '      '
                THEN c.t_dsca
              ELSE d.t_dsca
              END      AS                                                                     description,
              CASE
              WHEN a.t_cprj <> '      '
                THEN
                  CASE
                  WHEN c.t_csel = ' NR'
                    THEN 'NRE'
                  ELSE ''
                  END
              ELSE
                CASE
                WHEN d.t_csel = ' NR'
                  THEN 'NRE'
                ELSE ''
                END
              END      AS                                                                     nre,
              a.t_suno AS                                                                     vendor,
              a.t_orno AS                                                                     po,
              a.t_pono AS                                                                     line,
              a.t_pric AS                                                                     unit_price,
              a.t_oqua AS                                                                     order_qty,
              a.t_ecpr AS                                                                     ecp_rea,
        e.t_qana as ebom,
              CASE
              WHEN a.t_dqua <> 0
                THEN (a.t_dqua + a.t_bqua) * a.t_pric
              ELSE a.t_oqua * a.t_pric
              END      AS                                                                     c_amnt,
              (SELECT
                 TOP 1 LTRIM(RTRIM(bc.t_bitm)) AS wp
               FROM ttipcs950490 AS ab
                 LEFT JOIN ttipcs952490 AS bc ON ab.t_bdgt = bc.t_bdgt
               WHERE ab.t_cprj = a.t_cprj AND bc.t_bdgt = ab.t_bdgt AND bc.t_item = a.t_item) wp
        
            FROM ttdpur041490 a
              LEFT JOIN ttdpur045490 b ON b.t_orno = a.t_orno AND b.t_pono = a.t_pono AND b.t_srnb = 0
              LEFT JOIN ttipcs021490 c ON c.t_cprj = a.t_cprj AND c.t_item = a.t_item
              LEFT JOIN ttiitm001490 d ON d.t_item = a.t_item
              left join ttiitm901490 e on  c.t_cprj = e.t_cprj and c.t_item = e.t_item
              left join  ttccom001490 f  on f.t_emno = c.t_buyr
            WHERE
            a.t_orno = $po
            ORDER BY a.t_pono";
    return $sql;
}
function getFortisBuyer($po){
    $sql = "select top 1 Buyer_Name from fmm_purchase_order where PO_Number = $po order by Modified_Date DESC";
    $rs = dbCallFortis($sql);
    $buyer_name = $rs->fields["Buyer_Name"];
    return $buyer_name;
}
function getGlQTYWithTransfers($c_qty, $ship_code, $item){
    $data = array();
    $sql = "
            select
                coalesce((select sum(committed_qty) from meac.wp_committed_po cp where cp.ship_code=gl.ship_code and cp.item=gl.item), 0) cmt_qty,
                coalesce((select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%PUR%' and gl2.integr_amt < 0), 0) gl_pur_qty_off,
                coalesce((select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%PUR%' and gl2.integr_amt > 0), 0) gl_pur_qty_on,
                coalesce((select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt < 0), 0) gl_qty_transfers_off,
                coalesce((select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt > 0), 0) gl_qty_transfers_on,
                coalesce((select sum(no_cost_transfers) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item),0) no_cost_transfers

            from wp_gl_detail gl
            where item  = '$item'
            and ship_code = $ship_code
            group by ship_code, item";
    $rs = dbcall($sql, "meac");
    $cmt_qty                 = formatNumber4decNoComma($rs->fields["cmt_qty"]);
    $gl_pur_qty_off          = formatNumber4decNoComma($rs->fields["gl_pur_qty_off"]);
    $gl_pur_qty_on           = formatNumber4decNoComma($rs->fields["gl_pur_qty_on"]);
    $gl_qty_transfers_off    = formatNumber4decNoComma($rs->fields["gl_qty_transfers_off"]);
    $gl_qty_transfers_on     = formatNumber4decNoComma($rs->fields["gl_qty_transfers_on"]);
    $no_cost_transfers       = formatNumber4decNoComma($rs->fields["no_cost_transfers"]);

    $gl_qty          = $gl_pur_qty_on - $gl_pur_qty_off + $gl_qty_transfers_on - $gl_qty_transfers_off+ $no_cost_transfers;
    $c_qty           = $c_qty + $gl_qty_transfers_on - $gl_qty_transfers_off+ $no_cost_transfers;
    $data["c_qty"] = $c_qty;
    return $data;

}
function getReEstEACByItem($ship_code, $item){
    $sql = "
        SELECT 
        sum(eac) eac
        FROM reest3
        where ship_code = $ship_code AND item = '$item'
        GROUP by ship_code, item
    ";

    $rs = dbCall($sql,"Meac");
    $eac = $rs->fields["eac"];
    return $eac;
}
function fundingSRCGuess($item, $etc){
    $hpr_start = substr($item, 0, 3);
    if($hpr_start=="HPR"){
        $funding_source = "HPR";
        return $funding_source;
    }
    $shock = substr($item,13, 1);
    if($shock=="S"){
        $funding_source = "Shock";
        return $funding_source;
    }
    if($etc>0){
        $funding_source = "IN MEAC";
        return $funding_source;
    }
    if($etc<=0){
        $funding_source = "NOT IN MEAC";
        return $funding_source;
    }
}
function reasonForChangeGuess($item,$etc_diff){
    $reason_for_change = "";
    $hpr_start = substr($item, 0, 3);
    if($hpr_start=="HPR"){
        $reason_for_change = "HPR";
        return $reason_for_change;
    }
    $shock = substr($item, 13, 1);
    if($shock=="S"){
        $reason_for_change = "Shock";
        return $reason_for_change;
    }
    $rework = strpos($item,"-R");
    if($rework>0){
        $reason_for_change = "Rework";
        return $reason_for_change;
    }
    $vc = strpos($item,"-VC-");
    if($vc>0){
        $reason_for_change = "Vendor Claim";
        return $reason_for_change;
    }/*
    $ils= strpos($item,"989");
    if($ils!==false){
        $reason_for_change = "ILS";
        return $reason_for_change;
    }*/
    $cert= strpos($item,"-L");
    if($cert> 0){
        $reason_for_change = "Certifications";
        return $reason_for_change;
    }
    if($etc_diff>0){
        $reason_for_change = "Cost Savings";
        return $reason_for_change;
    }
    if($etc_diff<0){
        $reason_for_change = "Price Increase";
        return $reason_for_change;
    }
    if ($reason_for_change==""){
        $reason_for_change = "Cannot Identify";
    }
    return $reason_for_change;

}
function getLastMonthsGLandOpenPO($prev_rpt_period,$ship_code, $item){

    $sql = "
        select 
        (gl_int_amt+open_po_pending_amt) as cmt
    from 
    `".$prev_rpt_period."_swbs_gl_summary` 
    where ship_code = $ship_code AND item = '$item'
    ";
    //print $sql."<br>";
    $rs = dbCall($sql,"Meac");
    $cmt = $rs->fields["cmt"];
    return $cmt;
}
function getVendorName($vendor_id){
    $sql ="select vendor from po_data where vendor_id = $vendor_id limit 1";
    $rs=dbCall($sql,"meac");
    $vendor = $rs->fields["vendor"];
    return $vendor;
}
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
if($control=="project_grid")
{
    $sql= returnBaanPOSQL($po);
    $rs  = dbCallBaan($sql);
    $count = $rs->RecordCount();
    if($count==0){
        $data = "
            [{
                \"id\"  : 1,
                \"wp\"  : \"NO RECORDS\"
            }]
        ";
        die($data);
    }
    $data       = "[";
    $id         = 1;
    $total_diff = 0;
    $table_exist = checkIfTableExists("meac", $prev_rpt_period."_swbs_gl_summary");
    if($table_exist!==true){
        $prev_rpt_period = getPreviousRPTPeriod($prev_rpt_period);
    }
    $today = date('Y-m-d');
    $date = new DateTime($today);
    $week = $date->format("W");
    $buyer = getFortisBuyer($po);
    while (!$rs->EOF)
    {
        $ship_code   = trim($rs->fields["ship_code"]);
        $wp          = trim($rs->fields["wp"]);
        $create_date = trim($rs->fields["create_date"]);
        $item        = trim($rs->fields["item"]);
        $ecp_rea     = trim($rs->fields["ecp_rea"]);
        $description = processDescription($rs->fields["description"]);
        $po          = $rs->fields["po"];
        $line        = intval($rs->fields["line"]);
        $vendor_id   = $rs->fields["vendor"];
        $ebom        = formatNumber4decNoComma($rs->fields["ebom"]);

        $order_qty       = formatNumber4decNoComma($rs->fields["order_qty"]);
        $c_unit_price    = formatNumber4decNoComma($rs->fields["unit_price"]);
        $c_amnt          = formatNumber4decNoComma($rs->fields["c_amnt"]);
        $gl_data         = getGlQTYWithTransfers($order_qty, $ship_code, $item);
        $c_qty           = formatNumber4decNoComma($gl_data["c_qty"]);
        $vendor          = getVendorName($vendor_id);
        $eac             = getReEstEACByItem($ship_code, $item);
        $last_months_cmt = getLastMonthsGLandOpenPO($prev_rpt_period, $ship_code, $item);
        $etc             = formatNumber4decNoComma($eac - ($last_months_cmt));

        $etc_diff          = formatNumber4decNoComma($etc - $c_amnt);
        $total_diff        += $etc_diff;
        $explanation       = fundingSrcGuess($item, $etc);
        $reason_for_change = reasonForChangeGuess($item, $etc_diff);

        $swbs              = substr($wp, 5, 3);
        $data.="{
            \"id\"                  : $id,
            \"ship_code\"           :\"$ship_code\",
            \"date\"                :\"$create_date\",
            \"week\"                :\"$week\",
            \"po\"                  :\"$po\",
            \"buyer\"               :\"$buyer\",
            \"wp\"                  :\"$wp\",
            \"swbs\"                :\"$swbs\",
            \"item\"                :\"$item\",
            \"ecp_rea\"             :\"$ecp_rea\",
            \"desc\"                :\"$description\",
            \"po\"                  :\"$po\",
            \"line\"                :$line,
            \"vendor\"              :\"$vendor\",
            \"order_qty\"           :$order_qty,
            \"explanation\"         :\"$explanation\",
            \"reason_for_change\"   :\"$reason_for_change\",
            \"other_notes\"         :\"\",
            \"ebom\"                :$ebom,
            \"c_unit_price\"        :$c_unit_price,
            \"c_amnt\"              :$c_amnt,
            \"c_qty\"               :$c_qty,
            \"meac_re_est_etc\"     :$etc,
            \"etc_diff\"            :$etc_diff
        },";
        $id++;
        $rs->MoveNext();
    }
    $total_diff = formatNumber4decNoComma($total_diff);
    $data.="{
            \"id\"                  : $id,
            \"ship_code\"           :\"\",
            \"buyer\"           :\"\",
            \"wp\"                  :\"\",
            \"item\"                :\"TOTAL DIFF \",
            \"desc\"                :\"\",
            \"po\"                 :\"\",
            \"line\"               :\"\",
            \"vendor\"             :\"\",
            \"order_qty\"          :0,
            \"c_unit_price\"       :0,
             \"explanation\"       :\"\",
            \"other_notes\"        :\"\",
            \"reason_for_change\"  :\"\",
            \"c_amnt\"             :0,
            \"c_qty\"             :0,
            \"meac_re_est_etc\"   :0,
            \"etc_diff\"          :$total_diff
        }";
    $data.="]";
    die($data);
}
if($control=="excel_export_po"){
    $header_array = returnHeaders();
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("PO Approval");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sql= returnBaanPOSQL($po);
    $rs  = dbCallBaan($sql);
    $header_col = "A";
    $data_start = 2;
    $total_diff = 0;
    $table_exist = checkIfTableExists("meac", $prev_rpt_period."_swbs_gl_summary");
    if($table_exist!==true){
        $prev_rpt_period = getPreviousRPTPeriod($prev_rpt_period);
    }
    $today = date('Y-m-d');
    $date  = new DateTime($today);
    $week_for_rollup  = $date->format("W");
    $year= $date->format("Y");
    $buyer = getFortisBuyer($po);
    while (!$rs->EOF)
    {
        $header_col      = "A";
        $ship_code       = trim(intval($rs->fields["ship_code"]));
        $create_date     = trim($rs->fields["create_date"]);
        $date            = new DateTime($create_date);
        $week            = $date->format("W");
        $wp              = trim($rs->fields["wp"]);
        $swbs            = trim($rs->fields["swbs"]);
        //swbs group
        $swbs            = substr($swbs, 0, 1);
        $item            = trim($rs->fields["item"]);
        $ecp_rea         = trim($rs->fields["ecp_rea"]);
        $description     = processDescription($rs->fields["description"]);
        $po              = $rs->fields["po"];
        $line            = intval($rs->fields["line"]);
        $vendor_id       = $rs->fields["vendor"];
        $ebom            = formatNumber4decNoComma($rs->fields["ebom"]);
        $order_qty       = formatNumber4decNoComma($rs->fields["order_qty"]);
        $c_unit_price    = formatNumber4decNoComma($rs->fields["unit_price"]);
        $c_amnt          = formatNumber4decNoComma($rs->fields["c_amnt"]);
        $gl_data         = getGlQTYWithTransfers($order_qty, $ship_code, $item);
        $c_qty           = formatNumber4decNoComma($gl_data["c_qty"]);
        $vendor          = getVendorName($vendor_id);
        $eac             = getReEstEACByItem($ship_code, $item);
        $last_months_cmt = getLastMonthsGLandOpenPO($prev_rpt_period, $ship_code, $item);
        $etc             = formatNumber4decNoComma($eac - ($last_months_cmt));

        $etc_diff          = formatNumber4decNoComma($etc - $c_amnt);
        $total_diff        += $etc_diff;
        $explanation       = fundingSrcGuess($item, $etc);
        $reason_for_change = reasonForChangeGuess($item, $etc_diff);
        $remaining         = intval($ebom - $order_qty);

        $sheet->setCellValue($header_col++.$data_start, $ship_code);

        $sheet->setCellValue($header_col++.$data_start, $create_date);
        $sheet->setCellValue($header_col++.$data_start, $week);
        $sheet->setCellValue($header_col++.$data_start, $po);
        $sheet->setCellValue($header_col++.$data_start, $buyer);
        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $swbs);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col.$data_start, $c_amnt);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $etc_diff);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col++.$data_start, $order_qty);
        $sheet->setCellValue($header_col++.$data_start, $ebom);

        $sheet->setCellValue($header_col++.$data_start, $remaining);
        $sheet->setCellValue($header_col++.$data_start, $user);
        $sheet->setCellValue($header_col++.$data_start, $reason_for_change);
        $sheet->setCellValue($header_col++.$data_start, $explanation);
        $sheet->setCellValue($header_col++.$data_start, "");
        $sheet->setCellValue($header_col++.$data_start, $week_for_rollup);
        $sheet->setCellValue($header_col++.$data_start, $year);

        $data_start++;

        $rs->MoveNext();
    }
    /*$sheet->getStyle("E")
        ->getAlignment()
        ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    */
    $sheet->freezePane('C2');

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/PO_".$token.".xlsx");
    $path = "../util/excel_exports/PO_".$token.".xlsx";
    die($path);
}

if($control=="excel_export_0487_last_po"){
    $header_array = return0487Headers();
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("LCS 27 Data");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    /*everything, and put the budbet on the parent*/
    $sql = "
        
          select ship_code, wp, item, eac 
              from reest3 
              where ship_code = 487
              and substr(item, 13, 2) <> '-P'
          union
          select ship_code,wp,item,target eac from (
                SELECT
                  ship_code,
                  wp,
                  substr(item, 1,12) as item,
                  sum(eac) target
                FROM reest3
                WHERE
                item <> ''
                and substr(item, 13, 2) = '-P'
                and ship_code = 487
                group by ship_code, substr(item, 1,12) ) s 
    ";
    $data_start = 2;

    $rs = dbCall($sql,"MEAC");
    while (!$rs->EOF) {
        $header_col      = "A";

        $ship_code = $rs->fields["ship_code"];
        $wp        = $rs->fields["wp"];
        $item      = $rs->fields["item"];
        $eac       = $rs->fields["eac"];

        $sql     = returnBaanItemDataSql($item);
        $baan_rs = dbCallBaan($sql);
        if($baan_rs){
            $po          = $baan_rs->fields["po"];
            $line        = $baan_rs->fields["line"];
            $last_hull   = $baan_rs->fields["last_hull"];
            $create_date = $baan_rs->fields["create_date"];
            $description = $baan_rs->fields["description"];
            $unit_price  = $baan_rs->fields["unit_price"];
            $order_qty   = $baan_rs->fields["order_qty"];
            $unit_price   = $baan_rs->fields["unit_price"];
            $ebom        = $baan_rs->fields["ebom"];
            $c_amnt      = $baan_rs->fields["c_amnt"];
        }
        $sheet->setCellValue($header_col++.$data_start, "0487");
        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col++.$data_start, $description);
        $sheet->setCellValue($header_col.$data_start, $eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col++.$data_start, $ebom);
        $sheet->setCellValue($header_col++.$data_start, $last_hull);
        $sheet->setCellValue($header_col++.$data_start, $po);
        $sheet->setCellValue($header_col++.$data_start, $line);
        $sheet->setCellValue($header_col++.$data_start, $create_date);
        $sheet->setCellValue($header_col.$data_start, $unit_price);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col++.$data_start, $order_qty);
        $sheet->setCellValue($header_col.$data_start, $c_amnt);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $data_start++;
        $rs->MoveNext();
    }


    $sheet->freezePane('B2');

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/LAST_PO_0487_".$token.".xlsx");
    $path = "../util/excel_exports/LAST_PO_0487_".$token.".xlsx";
    die($path);
}
if($control=="delete_po_before_approve"){
    deleteFromTable("meac", "po_approval_log", "po", $po);
    die("mdae it");
}
if($control =="approve_po"){

    $insert_sql = returnInsertSqlPoApprovalLog();
    $sql = $insert_sql;
    foreach ($rows as $key=>$value){
        //array_debug($rows);

        $ship_code = $rows[$key]["ship_code"];
        $item      = $rows[$key]["item"];
        $po        = $rows[$key]["po"];
        $buyer     = $rows[$key]["buyer"];
        $wp        = $rows[$key]["wp"];
        $swbs      = substr($wp, 5, 3);
        $today     = fixExcelDateMySQL(date('Y-m-d'));
        $date      = new DateTime(date('Y-m-d'));
        $week              = $date->format("W");
        $item              = $rows[$key]["item"];
        $val               = $rows[$key]["c_amnt"];
        $line              = $rows[$key]["line"];
        $qty               = $rows[$key]["order_qty"];
        $ebom              = $rows[$key]["ebom"];
        $cam               = $user;
        $etc               = $rows[$key]["meac_re_est_etc"];
        $change            = $rows[$key]["etc_diff"];
        $reason_for_change = $rows[$key]["reason_for_change"];
        $funding_source    = $rows[$key]["explanation"];
        $other_notes       = $rows[$key]["other_notes"];
        $remaining         = $etc - $val;

        if($item =="TOTAL DIFF "){
            break ;
        }

        $sql .="
        (
        $ship_code,
        '$today',
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
    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql,"meac");
    die();
}
if($control=="reload_fortis"){
    truncateTable("meac", "po_data");
    loadFortisPOData();
    die("yes");
}
if($control=="excel_export"){
    $header_array = returnHeaders();
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("PO Approval LOG");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }

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
        funding_source,
         other_notes from po_approval_log order by date DESC 
    ";

    $rs = dbCall($sql,"Meac");
    $header_col = "A";
    $data_start = 2;

    while (!$rs->EOF)
    {
        $header_col = "A";

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
        $other_notes       = $rs->fields["other_notes"];

        $sheet->setCellValue($header_col++.$data_start, $ship_code);

        $sheet->setCellValue($header_col++.$data_start, $date);
        $sheet->setCellValue($header_col++.$data_start, $week);
        $sheet->setCellValue($header_col++.$data_start, $po);
        $sheet->setCellValue($header_col++.$data_start, $buyer);
        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $swbs);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col.$data_start, $val);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col.$data_start, $etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $change);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col++.$data_start, $qty);
        $sheet->setCellValue($header_col++.$data_start, $ebom);
        $sheet->setCellValue($header_col.$data_start, $remaining);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col++.$data_start, $cam);
        $sheet->setCellValue($header_col++.$data_start, $reason_for_change);
        $sheet->setCellValue($header_col++.$data_start, $funding_source);
        $sheet->setCellValue($header_col++.$data_start, $other_notes);

        $data_start++;
        $rs->MoveNext();
    }


    $path = "../util/excel_exports/".$token."export.xls";
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/po_log_".$token.".xlsx");
    $path = "../util/excel_exports/po_log_".$token.".xlsx";
    die($path);
}