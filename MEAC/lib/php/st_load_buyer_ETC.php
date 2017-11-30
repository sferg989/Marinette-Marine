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
$rpt_period = 201707;

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


    return $header_array;
}
function returnOpenBuyWithETCSQL($rpt_period,$buyer_id){
    $sql = "
    SELECT
  mb.buyer                                 buyer,
  ship_code,
  (SELECT wp
   FROM meac.`201709_cbm` cbm
   WHERE cbm.ship_code = b.ship_code AND cbm.material = b.item
   LIMIT 1) AS                             wp,
  concat(right(left((SELECT wp
                     FROM meac.`201709_cbm` cbm
                     WHERE cbm.ship_code = b.ship_code AND cbm.material = b.item
                     LIMIT 1), 6), 1), 00) swbs_group,
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
        ( SELECT sum(etc) FROM meac.`reest3` meac WHERE meac.ship_code =b.ship_code AND meac.item = b.item AND remaining = 'yes' LIMIT 1) AS etc,
        ( SELECT vendor_name FROM meac.`".$rpt_period."_swbs_gl_summary` meac WHERE meac.ship_code =b.ship_code AND meac.item = b.item LIMIT 1) AS vendor,
        
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
WHERE b.buyer = $buyer_id
AND item_shortage > 0
ORDER BY ship_code, item;
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
    deleteFromTable("meac", "change_item", "ship_code", $value);
    loadEFDBChangeBAAN($value);
}

//$path2directory = "C:\\evms\\excel";
$path2directory = "Z:\\Purchasing";

clearDirectory($path2directory);
$buyers_array = getActiveBuyers();

$header_array = returnHeadersFOrOpenBuy();
//$buyers_array2[] = 8019;

foreach ($buyers_array as $buyer_id){

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
    $rs = dbCall($sql,"meac");
    print $sql;
    print "<br>";
    print "<br>";
    print "<br>";

    array_debug($rs);
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

        $yard_due_date      = fixExcelDateMySQL($rs->fields["yard_due_date"]);
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

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,10000);
    //$objWriter->save("C:/evms/excel/".$buyer."_OPENBUY".$token.".xlsx");
    $objWriter->save("Z:/Purchasing/".$buyer."_OPENBUY_".$token.".xlsx");
}


print time();