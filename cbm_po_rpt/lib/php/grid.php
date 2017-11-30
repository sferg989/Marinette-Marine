<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');

$user            = $_SESSION["user_name"];
$rpt_period      = currentRPTPeriod();
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
function returnHeaders(){
    $header_array[] = "Hull";
    $header_array[] = "PO";
    $header_array[] = "LINE";
    $header_array[] = "WP";
    $header_array[] = "ITEM";
    $header_array[] = "DESCRIPTION";
    $header_array[] = "BUYER";
    return $header_array;
}
function getActiveProjectsWC(){
    $wc = "(";
    $sql = "select code from fmm_evms.master_project where active = 'true'";
    $rs = dbCall($sql,"fmm_evms");
    while (!$rs->EOF)
    {
        $code            = trim($rs->fields["code"]);
        if(strlen($code)==3)
        {
            $ship_code = "0".$code;
        }

        $wc.="'$ship_code',";
        $rs->MoveNext();
    }
    $wc = substr($wc, 0,-1);
    $wc.=")";
    return $wc;
}
function getFortisPONumsWC(){
/*not RUN LIVE ON FORTIS live on Fortis */
    $project_wc = getActiveProjectsWC();
    $wc = "(";
    $sql = "
        select
            po_number po
            from FMM_Purchase_Order
            left outer join FTBContainer _cont on _cont.Container_ID = F_ParentID
        where Project_Number  <> '' and
              (case
                  when _cont.Container = 'Project Approved' or _cont.Container = 'Approved MRO' Then 'Approved'
                  when _cont.Container = 'Purchase Orders Disapproved' Then 'Denied'
                  when _cont.Container like '%Pending%' or _cont.Container like '%New PO%' Then 'Pending'
                  when _cont.Container = 'No Approval' Then 'Approved'
                  when _cont.Container like '%Complete%' Then 'Approved'
                  when _cont.Container like '%Denied%' Then 'Denied'
                  when _cont.Container like '%Pending%' Then 'Pending'
                  when _cont.Container = 'New PO' Then 'New' else '' end)
              not in ('Approved','Denied')
              and Project_Number in $project_wc
        group by PO_Number
        order by max(Modified_Date),PO_Number DESC
    ";
    //print $sql;
    $rs = dbCallFortis($sql);
    while (!$rs->EOF)
    {

        $po            = trim($rs->fields["po"]);
        $wc.="$po,";
        $rs->MoveNext();
    }
    $wc = substr($wc, 0,-1);
    $wc.=")";

    return $wc;
}
function getBaanPOSQL($po_wc){
    $sql = "SELECT
          a.t_cprj AS                                                                     ship_code,
        f.t_nama as buyer,
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
        a.t_orno in $po_wc
        and
            (SELECT
            TOP 1 LTRIM(RTRIM(bc.t_bitm)) AS wp
            FROM ttipcs950490 AS ab
            LEFT JOIN ttipcs952490 AS bc ON ab.t_bdgt = bc.t_bdgt
            WHERE ab.t_cprj = a.t_cprj AND bc.t_bdgt = ab.t_bdgt AND bc.t_item = a.t_item)
        is null
        and
            CASE
              WHEN a.t_pacn <> 0
              THEN substring(a.t_pacn, 2, 3)
            ELSE b.t_cpcp
            END <> '825'
       and f.t_nama is not null
        ORDER BY a.t_orno, a.t_pono";
    return $sql;
}
if($control=="project_grid")
{
    $po_wc = getFortisPONumsWC();
    $data  = "[";
    $id    = 1;
    $sql   = getBaanPOSQL($po_wc);
    $rs  = dbCallBaan($sql);
    while (!$rs->EOF)
    {
        $ship_code   = trim($rs->fields["ship_code"]);
        $wp          = trim($rs->fields["wp"]);
        $buyer       = trim($rs->fields["buyer"]);
        $item        = trim($rs->fields["item"]);
        $description = processDescription($rs->fields["description"]);
        $po          = trim($rs->fields["po"]);
        $line        = trim($rs->fields["line"]);
        $data.="{
        \"id\"                  : $id,
        \"ship_code\"            :\"$ship_code\",
        \"buyer\"               :\"$buyer\",
        \"wp\"                  :\"$wp\",
        \"item\"                :\"$item\",
        \"desc\"                :\"$description\",
        \"po\"                  :\"$po\",
        \"line\"                :\"$line\"
    },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="excel_export"){
    $header_array = returnHeaders();
    $objPHPExcel = new PHPExcel();
// Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("ITEMS Not in CBM");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $po_wc = getFortisPONumsWC();
    $sql   = getBaanPOSQL($po_wc);
    $rs = dbCallBaan($sql);
    $header_col = "A";
    $data_start = 2;

    while (!$rs->EOF)
    {
        $header_col = "A";

        $ship_code   = trim($rs->fields["ship_code"]);
        $wp          = trim($rs->fields["wp"]);
        $buyer       = trim($rs->fields["buyer"]);
        $item        = trim($rs->fields["item"]);
        $description = processDescription($rs->fields["description"]);
        $po          = trim($rs->fields["po"]);
        $line        = trim($rs->fields["line"]);

        $sheet->setCellValue($header_col++.$data_start, $ship_code);

        $sheet->setCellValue($header_col++.$data_start, $po);
        $sheet->setCellValue($header_col++.$data_start, $line);
        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col++.$data_start, $description);
        $sheet->setCellValue($header_col++.$data_start, $buyer);

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