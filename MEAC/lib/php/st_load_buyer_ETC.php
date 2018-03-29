<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.insert_data.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.baan.fortis.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.meac.excel.export.php');
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
session_write_close();

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
$array[] = 487;
$rpt_period = 201802;

function getEfdbdataByShipItem($ship_code, $item){
    if(strlen($ship_code)==3 or strlen($ship_code)==7)
    {
        $ship_code = "0".$ship_code;
    }
    $sql = "
        SELECT
          concat(b.TYPE, '-', b.NUMBER)                change_code,
          PROBLEM prob,
          CONVERT(VARCHAR(8), a.COMPLETION_DATE, 1) AS efdb_date
        FROM Engineering.CHANGE_NOTICE a
          LEFT JOIN Engineering.CHANGE_ORIGIN b
            ON a.ID = b.CHANGE_ID
          LEFT JOIN Engineering.FORECAST_CHANGE c
            ON a.ID = c.CHANGE_ID
        WHERE ltrim(rtrim(PART_NO)) = '$item'
              AND ltrim(rtrim(a.PROJECT)) = '$ship_code'
        ORDER BY COMPLETION_DATE DESC
    ";
    $rs = dbCallK2($sql);
    $change_code = $rs->fields["change_code"];
    $efdb_date   = $rs->fields["efdb_date"];
    $prob         = $rs->fields["prob"];
    $data_array = array();
    $data_array["date"] = $efdb_date;
    $data_array["prob"] = $prob;
    $data_array["change_code"] = $change_code;
    return $data_array;
}
function getBaanFirstData($item){
    $sql = "select top 1
  sub.t_cprj,
  CONVERT(VARCHAR(8), sub.last_date, 1) first_date,
  sub.t_oqua,
  (select t_pric from ttdpur041490 baan where baan.t_cprj = sub.t_cprj and baan.t_item = sub.item and baan.t_odat = sub.last_date) as price
from (
SELECT
        a.t_cprj,
        a.t_item AS item,
        a.t_oqua,
        min(a.t_odat) last_date
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
  group by a.t_cprj,a.t_item, a.t_oqua) sub order by last_date";
    $rs = dbCallBaan($sql);
    $data = array();
    $data["ship_code"]  = $rs->fields["t_cprj"];
    $data["date"]       = $rs->fields["first_date"];
    $data["qty"]        = $rs->fields["t_oqua"];
    $data["price"]      = $rs->fields["price"];
    $data["c_amt"]      = $rs->fields["price"]*$rs->fields["t_oqua"];
    return $data;
}

function getBaanLastData($item){
    $sql = "select top 1
  sub.t_cprj,
  CONVERT(VARCHAR(8), sub.last_date, 1) first_date,
  sub.t_oqua,
  (select t_pric from ttdpur041490 baan where baan.t_cprj = sub.t_cprj and baan.t_item = sub.item and baan.t_odat = sub.last_date) as price
from (
SELECT
  a.t_cprj,
a.t_item AS item,
  a.t_oqua,
max(a.t_odat) last_date
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
  group by a.t_cprj,a.t_item, a.t_oqua) sub order by last_date desc";
    $rs = dbCallBaan($sql);
    $data = array();
    $data["ship_code"]  = $rs->fields["t_cprj"];
    $data["date"]       = $rs->fields["first_date"];
    $data["qty"]        = $rs->fields["t_oqua"];
    $data["price"]      = $rs->fields["price"];
    $data["c_amt"]      = $rs->fields["price"]*$rs->fields["t_oqua"];
    return $data;
}


function returnHeadersFOrOpenBuy(){
    $header_array[] = "buyer";
    $header_array[] = "ship code";
    $header_array[] = "swbs group";
    $header_array[] = "swbs";
    $header_array[] = "wp";
    $header_array[] = "item";
    $header_array[] = "spn";
    $header_array[] = "description";
    $header_array[] = "EBOM";
    $header_array[] = "ORIG";
    $header_array[] = "Consum";
    $header_array[] = "remain";
    $header_array[] = "Group";
    $header_array[] = "supplier";
    $header_array[] = "Last Vendor";
    $header_array[] = "HOLD";
    $header_array[] = "mriy date";
    $header_array[] = "lead time";
    $header_array[] = "shelf life";
    $header_array[] = "Plan Order Date";
    $header_array[] = "UOM";
    $header_array[] = "item on hand";
    $header_array[] = "item on order";
    $header_array[] = "Alloc";
    $header_array[] = "EFDB";
    $header_array[] = "ACT issue";
    $header_array[] = "Item Shortage";
    $header_array[] = "Budget";
    $header_array[] = "QTY Price";
    $header_array[] = "EFDB change qty";
    $header_array[] = "EFDB change date";
    $header_array[] = "EFDB change description";
    $header_array[] = "EFDB Change Code";
    $header_array[] = "EFDB Problem";
    $header_array[] = "EFDB Completion Date";
    $header_array[] = "First Buy Date";
    $header_array[] = "First Buy Quantity";
    $header_array[] = "First Buy Price";
    $header_array[] = "First Buy C Amount";
    $header_array[] = "Last Buy Date";
    $header_array[] = "Last Buy Quantity";
    $header_array[] = "Last Buy Price";
    $header_array[] = "Last Buy C Amount";
    $header_array[] = "Growth";


    return $header_array;
}
function returnHeadersFOrOpenBuyProgressPay(){
    $header_array[] = "HULL";
    $header_array[] = "ITEM";
    $header_array[] = "BUDGET";
    return $header_array;
}
function createProgressPayTab($objPHPExcel,$progress_pay_header_array){
    $objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating
    $objPHPExcel->setActiveSheetIndex(1);
    $objWorkSheet->setTitle("Progress Pay Budgets");
    $sheet      = $objPHPExcel->getActiveSheet();
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";
    foreach ($progress_pay_header_array as $header){
        $header = strtoupper($header);
        $sheet->SetCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sql = "
            select * from (
            SELECT
              ship_code,
              substr(item, 1,12) as parent_item,
              sum(etc) target
            FROM reest3
            WHERE
                  remaining = 'yes'
                  AND item <> ''
                   and substr(item, 13, 2) = '-P'
            group by ship_code, substr(item, 1,12) ) s where s.target > 0
    ";
    $rs = dbCall($sql, "MEAC");
    print $sql;
    $data_start = 2;
    while (!$rs->EOF)
    {
        $ship_code  = $rs->fields["ship_code"];
        $item       = $rs->fields["parent_item"];
        $budget     = $rs->fields["target"];
        $header_col = "A";
        $sheet->SetCellValue($header_col++.$data_start, $ship_code);
        $sheet->SetCellValue($header_col++.$data_start, $item);
        $sheet->SetCellValue($header_col.$data_start, $budget);
        //phpExcelCurrencySheet($header_col++.$data_start, $budget);

        $data_start++;
        $rs->MoveNext();
    }
}
function returnOpenBuyWithETCSQL($rpt_period,$buyer_id){
    if(is_array($buyer_id) ==true){
        $buyer_wc = "b.buyer in (";
        foreach ($buyer_id as $value){
            $buyer_wc.= "$value,";
        }
        $buyer_wc = substr($buyer_wc, 0, -1);
        $buyer_wc.=")";
    }
    else{
        $buyer_wc = "b.buyer = $buyer_id";
    }
    $sql = "
    select *,
              (eac-(gl+open_po)) etc
            from (

SELECT
  mb.buyer                                 buyer,
  ship_code,
  concat(left(swbs,1),'00') as swbs_group,
  swbs,
          item,
          spn,
          description,
          ebom,
          orig,
          consum,
          remain,
          `group`,
          supplier,
          hold,
          mriy_date,
          lead_time,
          shelf_life,
          plan_order_date,
          uom,
          item_on_hand,
          item_on_order,
          item_shortage,
          alloc,
          efdb,
          act_issue,
        ( SELECT sum(gl_int_amt) FROM meac.`".$rpt_period."_swbs_gl_summary` meac WHERE meac.ship_code =b.ship_code AND meac.item = b.item  and meac.item <> '') AS gl,
        ( SELECT sum(open_po_pending_amt) FROM meac.`".$rpt_period."_swbs_gl_summary` meac WHERE meac.ship_code =b.ship_code AND meac.item = b.item  and meac.item <> '') AS open_po,
        ( SELECT sum(eac) FROM  meac.reest3 r WHERE r.ship_code =b.ship_code AND r.item = b.item  and r.item <> '') AS eac,
        ( SELECT vendor_name FROM meac.`".$rpt_period."_swbs_gl_summary` meac WHERE meac.item = b.item and meac.item <> '' and vendor_name <> '' ORDER BY ship_code desc limit 1) AS vendor,
        ( SELECT sum(change_qty)
        FROM meac.change_item ci
        WHERE ci.ship_code = b.ship_code AND ci.item = b.item
        GROUP BY ci.ship_code, ci.item) AS change_qty,
        ( SELECT DATE
        FROM meac.change_item ci
        WHERE ci.ship_code = b.ship_code AND ci.item = b.item
        ORDER BY DATE DESC
        LIMIT 1) AS change_date,
        ( SELECT ci.description
        FROM meac.change_item ci
        WHERE ci.ship_code = b.ship_code AND ci.item = b.item
        ORDER BY DATE DESC
        LIMIT 1) AS change_description
FROM mars.efdb_open_buy b
LEFT JOIN ".$rpt_period."_master_buyer mb ON
mb.id = b.buyer
WHERE $buyer_wc

AND item_shortage > 0
limit 10
ORDER BY ship_code, item) sub
";
    return $sql;
}
function getActiveBuyers(){
    $buyers_array = array();
    $sql = "select buyer from mars.efdb_open_buy  group by buyer";
    $rs = dbCall($sql,"mars");
    while (!$rs->EOF)
    {
        $buyer_id = $rs->fields["buyer"];
        $buyers_array[] = $buyer_id;
        $rs->MoveNext();
    }
    return $buyers_array;
}

foreach($array as $ship_code){
    //deleteFromTable("meac", "wp_baan_open_buy", "ship_code", $ship_code);
    //insertOpenBuyReport($ship_code);
}

foreach($array as $value){
    //deleteFromTable("meac", "change_item", "ship_code", $value);
    //loadEFDBChangeBAAN($value);
}

$path2directory = "C:\\evms\\excel";
//$path2directory = "Z:\\Purchasing";

clearDirectory($path2directory);
$buyers_array = getActiveBuyers();

$header_array              = returnHeadersFOrOpenBuy();
$progress_pay_header_array = returnHeadersFOrOpenBuyProgressPay();
$buyers_array2[] = 8019;

foreach ($buyers_array2 as $buyer_id){

    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();

    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->SetCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }

    $sql = returnOpenBuyWithETCSQL($rpt_period,$buyer_id);
    print $sql;
    die();
    $rs = dbCall($sql,"meac");
    print $sql;
    print "<br>";
    print "<br>";
    print "<br>";

    //array_debug($rs);
    //die("made ");
    $data_start = 2;

    while (!$rs->EOF)
    {
        $buyer           = trim($rs->fields["buyer"]);
        $wp              = trim($rs->fields["wp"]);
        $ship_code       = trim($rs->fields["ship_code"]);
        $swbs_group      = trim($rs->fields["swbs_group"]);
        $swbs            = trim($rs->fields["swbs"]);
        $item            = trim($rs->fields["item"]);
        $spn             = trim($rs->fields["spn"]);
        $description     = trim($rs->fields["description"]);
        $ebom            = trim($rs->fields["ebom"]);
        $orig            = trim($rs->fields["orig"]);
        $consum          = trim($rs->fields["consum"]);
        $remain          = trim($rs->fields["remain"]);
        $group           = trim($rs->fields["group"]);
        $supplier        = trim($rs->fields["supplier"]);
        $hold            = trim($rs->fields["hold"]);
        $mriy_date       = fixExcelDateMySQL(trim($rs->fields["mriy_date"]));
        $lead_time       = trim($rs->fields["lead_time"]);
        $shelf_life      = trim($rs->fields["shelf_life"]);
        $plan_order_date = fixExcelDateMySQL(trim($rs->fields["plan_order_date"]));
        $uom             = trim($rs->fields["uom"]);
        $item_on_hand    = trim($rs->fields["item_on_hand"]);
        $item_on_order   = trim($rs->fields["item_on_order"]);
        $item_shortage   = trim($rs->fields["item_shortage"]);
        $alloc           = trim($rs->fields["alloc"]);
        $efdb            = trim($rs->fields["efdb"]);
        $act_issue       = trim($rs->fields["act_issue"]);

        $lead_time          = $rs->fields["lead_time"];
        $plan_order_date    = fixExcelDateMySQL($rs->fields["plan_order_date"]);
        $uom                = trim($rs->fields["uom"]);
        $item_shortage      = formatNumber4decNoComma($rs->fields["item_shortage"]);
        $change_qty         = formatNumber4decNoComma($rs->fields["change_qty"]);
        $change_date        = fixExcelDateMySQL($rs->fields["change_date"]);
        $change_description = trim($rs->fields["change_description"]);
        $vendor             = trim($rs->fields["vendor"]);
        $etc                = formatNumber4decNoComma($rs->fields["etc"]);
        $qty_price          = formatNumber4decNoComma($etc / $item_shortage);
        $efdb_array     = array();
        $baan_array     = array();
        $efdb_array     = getEfdbdataByShipItem($ship_code, $item);
        $change_code    = $efdb_array["change_code"];
        $efdb_date      = $efdb_array["date"];
        $prob           = $efdb_array["prob"];
        
        $first_baan_array    = getBaanFirstData($item);
        array_debug($first_baan_array);
        die();
        $first_buy_date     = $first_baan_array["date"];
        $first_buy_qty      = $first_baan_array["qty"];
        $first_buy_price    = $first_baan_array["price"];
        $first_buy_c_amt    = $first_baan_array["c_amt"];
        $last_baan_array    = getBaanLastData($item);
        $last_buy_date     = $last_baan_array["date"];
        $last_buy_qty      = $last_baan_array["qty"];
        $last_buy_price    = $last_baan_array["price"];
        $last_buy_c_amt    = $last_baan_array["c_amt"];
        $pc_growth = formatNumber4decNoComma($last_buy_price/$first_buy_price)*100;
        $header_col = "A";
        $sheet->SetCellValue($header_col++.$data_start, $buyer);
        $sheet->SetCellValue($header_col++.$data_start, $ship_code);
        $sheet->SetCellValue($header_col++.$data_start, $swbs_group);
        $sheet->SetCellValue($header_col++.$data_start, $swbs);
        $sheet->SetCellValue($header_col++.$data_start, $wp);
        $sheet->SetCellValue($header_col++.$data_start, $item);
        $sheet->SetCellValue($header_col++.$data_start, $spn);
        $sheet->SetCellValue($header_col++.$data_start, $description);
        $sheet->SetCellValue($header_col++.$data_start, $ebom);
        $sheet->SetCellValue($header_col++.$data_start, $orig);
        $sheet->SetCellValue($header_col++.$data_start, $consum);
        $sheet->SetCellValue($header_col++.$data_start, $remain);
        $sheet->SetCellValue($header_col++.$data_start, $group);
        $sheet->SetCellValue($header_col++.$data_start, $supplier);
        $sheet->SetCellValue($header_col++.$data_start, $vendor);
        $sheet->SetCellValue($header_col++.$data_start, $hold);
        $sheet->SetCellValue($header_col++.$data_start, $mriy_date);
        $sheet->SetCellValue($header_col++.$data_start, $lead_time);
        $sheet->SetCellValue($header_col++.$data_start, $shelf_life);
        $sheet->SetCellValue($header_col++.$data_start, $plan_order_date);
        $sheet->SetCellValue($header_col++.$data_start, $uom);
        $sheet->SetCellValue($header_col++.$data_start, $item_on_hand);
        $sheet->SetCellValue($header_col++.$data_start, $item_on_order);
        $sheet->SetCellValue($header_col++.$data_start, $alloc);
        $sheet->SetCellValue($header_col++.$data_start, $efdb);
        $sheet->SetCellValue($header_col++.$data_start, $act_issue);
        $sheet->SetCellValue($header_col++.$data_start, $item_shortage);
        $sheet->SetCellValue($header_col.$data_start, $etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->SetCellValue($header_col.$data_start, $qty_price);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->SetCellValue($header_col++.$data_start, $change_qty);
        $sheet->SetCellValue($header_col++.$data_start, $change_date);
        $sheet->SetCellValue($header_col++.$data_start, $change_description);
        $sheet->SetCellValue($header_col++.$data_start, $change_code);
        $sheet->SetCellValue($header_col++.$data_start, $prob);
        $sheet->SetCellValue($header_col++.$data_start, $efdb_date);
        $sheet->SetCellValue($header_col++.$data_start, $efdb_date);
        $sheet->SetCellValue($header_col++.$data_start, $first_buy_date);
        $sheet->SetCellValue($header_col++.$data_start, $first_buy_qty);
        $sheet->SetCellValue($header_col.$data_start, $first_buy_price);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->SetCellValue($header_col++.$data_start, $first_buy_c_amt);
        phpExcelCurrencySheet($header_col.$data_start, $sheet);
        $sheet->SetCellValue($header_col++.$data_start, $last_buy_date);
        $sheet->SetCellValue($header_col++.$data_start, $last_buy_qty);
        $sheet->SetCellValue($header_col.$data_start, $last_buy_price);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->SetCellValue($header_col.$data_start, $last_buy_c_amt);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->SetCellValue($header_col.$data_start, $pc_growth);
        phpExcelFormatPercentageSheet($header_col.$data_start, $sheet);

        $data_start++;
        $rs->MoveNext();
    }
    $sheet->freezePane('A2');

    /*creae progress pay Budget tab*/
    /*creae progress pay Budget tab*/
    /*creae progress pay Budget tab*/
    /*creae progress pay Budget tab*/

    //createProgressPayTab($objPHPExcel,$progress_pay_header_array);


    $objPHPExcel->setActiveSheetIndex(0);

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,10000);
    $objWriter->save("C:/evms/excel/".$buyer."_OPENBUY".$token.".xlsx");
    //$objWriter->save("Z:/Purchasing/".$buyer."_OPENBUY_".$token.".xlsx");
}
die();
/**/
/*DO ALL ITEMS ON THE BUY*/
/*ALL BUYERS*/
/**/
/**/

$objPHPExcel = new PHPExcel();
// Set the active Excel worksheet to sheet 0
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();

$header_row= 1;
$header_col = "A";
foreach ($header_array as $header){
    $header = strtoupper($header);
    $sheet->SetCellValue($header_col.$header_row, $header);
    colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
}

$sql = returnOpenBuyWithETCSQL($rpt_period,$buyers_array);
$rs = dbCall($sql,"meac");
print $sql;
print "<br>";
print "<br>";
print "<br>";
//die("made ");
$data_start = 2;

while (!$rs->EOF)
{
    $buyer           = trim($rs->fields["buyer"]);
    $wp              = trim($rs->fields["wp"]);
    $ship_code       = trim($rs->fields["ship_code"]);
    $swbs_group      = trim($rs->fields["swbs_group"]);
    $swbs            = trim($rs->fields["swbs"]);
    $item            = trim($rs->fields["item"]);
    $spn             = trim($rs->fields["spn"]);
    $description     = trim($rs->fields["description"]);
    $ebom            = trim($rs->fields["ebom"]);
    $orig            = trim($rs->fields["orig"]);
    $consum          = trim($rs->fields["consum"]);
    $remain          = trim($rs->fields["remain"]);
    $group           = trim($rs->fields["group"]);
    $supplier        = trim($rs->fields["supplier"]);
    $hold            = trim($rs->fields["hold"]);
    $mriy_date       = fixExcelDateMySQL(trim($rs->fields["mriy_date"]));
    $lead_time       = trim($rs->fields["lead_time"]);
    $shelf_life      = trim($rs->fields["shelf_life"]);
    $plan_order_date = fixExcelDateMySQL(trim($rs->fields["plan_order_date"]));
    $uom             = trim($rs->fields["uom"]);
    $item_on_hand    = trim($rs->fields["item_on_hand"]);
    $item_on_order   = trim($rs->fields["item_on_order"]);
    $item_shortage   = trim($rs->fields["item_shortage"]);
    $alloc           = trim($rs->fields["alloc"]);
    $efdb            = trim($rs->fields["efdb"]);
    $act_issue       = trim($rs->fields["act_issue"]);

    $lead_time          = $rs->fields["lead_time"];
    $plan_order_date    = fixExcelDateMySQL($rs->fields["plan_order_date"]);
    $uom                = trim($rs->fields["uom"]);
    $item_shortage      = formatNumber4decNoComma($rs->fields["item_shortage"]);
    $change_qty         = formatNumber4decNoComma($rs->fields["change_qty"]);
    $change_date        = fixExcelDateMySQL($rs->fields["change_date"]);
    $change_description = trim($rs->fields["change_description"]);
    $vendor             = trim($rs->fields["vendor"]);
    $etc                = formatNumber4decNoComma($rs->fields["etc"]);
    $qty_price          = formatNumber4decNoComma($etc / $item_shortage);


    $header_col = "A";
    $sheet->SetCellValue($header_col++.$data_start, $buyer);
    $sheet->SetCellValue($header_col++.$data_start, $ship_code);
    $sheet->SetCellValue($header_col++.$data_start, $swbs_group);
    $sheet->SetCellValue($header_col++.$data_start, $swbs);
    $sheet->SetCellValue($header_col++.$data_start, $wp);
    $sheet->SetCellValue($header_col++.$data_start, $item);
    $sheet->SetCellValue($header_col++.$data_start, $spn);
    $sheet->SetCellValue($header_col++.$data_start, $description);
    $sheet->SetCellValue($header_col++.$data_start, $ebom);
    $sheet->SetCellValue($header_col++.$data_start, $orig);
    $sheet->SetCellValue($header_col++.$data_start, $consum);
    $sheet->SetCellValue($header_col++.$data_start, $remain);
    $sheet->SetCellValue($header_col++.$data_start, $group);
    $sheet->SetCellValue($header_col++.$data_start, $supplier);
    $sheet->SetCellValue($header_col++.$data_start, $vendor);
    $sheet->SetCellValue($header_col++.$data_start, $hold);
    $sheet->SetCellValue($header_col++.$data_start, $mriy_date);
    $sheet->SetCellValue($header_col++.$data_start, $lead_time);
    $sheet->SetCellValue($header_col++.$data_start, $shelf_life);
    $sheet->SetCellValue($header_col++.$data_start, $plan_order_date);
    $sheet->SetCellValue($header_col++.$data_start, $uom);
    $sheet->SetCellValue($header_col++.$data_start, $item_on_hand);
    $sheet->SetCellValue($header_col++.$data_start, $item_on_order);
    $sheet->SetCellValue($header_col++.$data_start, $alloc);
    $sheet->SetCellValue($header_col++.$data_start, $efdb);
    $sheet->SetCellValue($header_col++.$data_start, $act_issue);
    $sheet->SetCellValue($header_col++.$data_start, $item_shortage);
    $sheet->SetCellValue($header_col.$data_start, $etc);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);
    $sheet->SetCellValue($header_col.$data_start, $qty_price);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);
    $sheet->SetCellValue($header_col++.$data_start, $change_qty);
    $sheet->SetCellValue($header_col++.$data_start, $change_date);
    $sheet->SetCellValue($header_col++.$data_start, $change_description);


    $data_start++;
    $rs->MoveNext();
}
$sheet->freezePane('A2');
createProgressPayTab($objPHPExcel,$progress_pay_header_array);
$objPHPExcel->setActiveSheetIndex(0);

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$token         = rand (0,10000);
//$objWriter->save("C:/evms/excel/All_buyers_OPENBUY".$token.".xlsx");
$objWriter->save("Z:/Purchasing/All_buyers_OPENBUY_".$token.".xlsx");

print time();