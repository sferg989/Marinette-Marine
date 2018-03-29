<?php
include('../../../inc/inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
include('../../../inc/inc.cobra.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.insert_data.php');
include('../../../meac/lib/php/functiobuilder.php');
include('../../../meac/lib/php/inc.meac.excel.export.php');
//include('../../../inc/lib/php/simplexlsx-master/simplexlsx.class.php');
session_write_close();
//$user = $_SESSION["user_name"];
$user = "fs11239";
function checkIfFileExistsThenDeletes($path2_file){
    $file = file_exists($path2_file);
    if($file===true){
        unlink($path2_file);
        return true;
    }
    else{
        return true;
    }
}
function returnOpenBuyInsertXLSX(){
    $insert_sql = "
    insert into mars.open_buy (
        program,
        ship_code,
        buyer,
        swbs,
        item,
        spn,
        description,
        origrinal_smos_qty,
        remain_smos_qty,
        yard_due_date,
        lead_time,
        plan_order_date,
        uom,
        item_on_hand,
        item_on_order,
        item_shortage,
        on_hold,
        entered_on,
        last_mod,
        last_price,
        expected_amt) VALUES ";

    return $insert_sql;
}
function insertCommittedPOXLSX($sheet){
    $highest_row = $sheet->getHighestRow();
    //print $highest_row;
    $insert_sql = returnCMTPOInsert();
    $sql        = $insert_sql;
    for ($i = 2; $i <= $highest_row; $i++) {

        $col         = "A";
        $proj        = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $swbs        = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $item        = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $description = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $noun_1      = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $noun_2      = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $nre         = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $vendor      = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $col++;
        $po             = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $line           = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $unit_price     = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $order_qty      = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $delivered_qty  = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $committed_qty  = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $commit_amnt    = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $delv_date      = fixExcelDateMySQL($sheet->getCell($col++ . $i)->getFormattedValue());
        $acct_proj_dept = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $clin           = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $effort         = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));

        $sql.=
            "(
                $proj,
                $swbs,
                '$item',
                '$description',
                '$noun_1',
                '$noun_2',
                '$nre',
                $vendor,
                $po,
                $line,
                $unit_price,
                $order_qty,
                $delivered_qty,
                $committed_qty,
                $commit_amnt,
                '$delv_date',
                '$acct_proj_dept',
                '$clin',
                '$effort'
                ),";
        
        if($i % 500==0)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i % 500!=0)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }

}
function insertGlDetailXLSX($ship_code,$sheet){
    $highest_row = $sheet->getHighestRow();
    //print $highest_row;
    $insert_sql = returnGLDetailInsert();
    $sql        = $insert_sql;
    for ($i = 2; $i <= $highest_row; $i++) {

        $col         = "A";
        $ldger_acct  = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $document    = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $line        = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $item        = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $description = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $order       = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $pos         = intval($sheet->getCell($col++ . $i)->getFormattedValue());
        $cust_supp   = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $qty         = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $unit        = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $amt         = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $date        = fixExcelDateMySQL($sheet->getCell($col++ . $i)->getFormattedValue());
        $integr_amt  = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $clin        = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $effort      = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));
        $ecp_rea     = addslashes(trim($sheet->getCell($col++ . $i)->getFormattedValue()));

        $sql.=
            "(
                $ldger_acct,
                '$document',
                $line,
                '$item',
                '$description',
                $order,
                $pos,
                '$cust_supp',
                $qty,
                '$unit',
                $amt,
                '$date',
                $integr_amt,
                '$clin',
                '$effort',
                $ship_code,
                '$ecp_rea'
                ),";

        if($i % 500==0)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i % 500!=0)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }

}
function insertOpenBuyXLSX($sheet){
    $highest_row = $sheet->getHighestRow();
    print $highest_row;
    $insert_sql = returnOpenBuyInsertXLSX();
    $sql        = $insert_sql;
    for ($i = 2; $i <= $highest_row; $i++) {

        $col         = "A";
        $buyer              = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $ship_code          = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $swbs               = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $col++;
        $item               = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $spn                = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $description        = addslashes(str_replace("'", " ",trim($sheet->getCell($col++ . $i)->getFormattedValue())));
        $origrinal_smos_qty = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $remain_smos_qty    = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $yard_due_date      = fixExcelDateMySQL($sheet->getCell($col++ . $i)->getFormattedValue());
        $lead_time          = $sheet->getCell($col++ . $i)->getFormattedValue();
        $plan_order_date    = fixExcelDateMySQL($sheet->getCell($col++ . $i)->getFormattedValue());
        $uom                = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $item_on_hand       = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $item_on_order      = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $item_shortage      = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $on_hold            = $sheet->getCell($col++ . $i)->getFormattedValue();
        $entered_on         = fixExcelDateMySQL($sheet->getCell($col++ . $i)->getFormattedValue());
        $last_mod           = fixExcelDateMySQL($sheet->getCell($col++ . $i)->getFormattedValue());
        $last_price         = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        $expected_amt       = formatNumber4decNoComma($sheet->getCell($col++ . $i)->getFormattedValue());
        if($ship_code=="Total"){
            continue;
        }
        $sql.= " (
                    'LCS',
                    $ship_code,
                    '$buyer',
                    '$swbs',
                    '$item',
                    '$spn',
                    '$description',
                    '$origrinal_smos_qty',
                    '$remain_smos_qty',
                    '$yard_due_date',
                    '$lead_time',
                    '$plan_order_date',
                    '$uom',
                    '$item_on_hand',
                    '$item_on_order',
                    '$item_shortage',
                    '$on_hold',
                    '$entered_on',
                    '$last_mod',
                    '$last_price',
                    '$expected_amt'
                ),";

        if($i % 500==0)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i % 500!=0)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function returnGLDetailInsert(){
    $insert_sql = "
INSERT  INTO mars.gl_detail (
        ldger_acct,
        document,
        line,
        item,
        description,
        `order`,
        pos,
        cust_supp,
        qty,
        unit,
        amt,
        date,
        integr_amt,
        clin,
        effort,
        proj, 
        ecp_rea) 
        values
       ";
    return $insert_sql;
}
function returnOpenPOInsert(){
    $insert_sql = "
        insert into mars.open_po (
            proj,
            swbs,
            item,
            description,
            noun_1,
            noun_2,
            nre,
            vendor,
            po,
            line,
            unit_price,
            order_qty,
            delivered_qty,
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort,
            ecp_rea 
    ) VALUES 
       ";
    return $insert_sql;
}
function returnCMTPOInsert(){
    $insert_sql = "
        insert into mars.committed_po (
            proj,
            swbs,
            item,
            description,
            noun_1,
            noun_2,
            nre,
            vendor,
            po,
            line,
            unit_price,
            order_qty,
            delivered_qty,
            committed_qty,
            commit_amnt,
            delv_date,
            acct_proj_dept,
            clin,
            effort
    ) VALUES 
       ";
    return $insert_sql;
}
function returnHeaders(){
    $header_array[] = "Hull";
    $header_array[] = "WP";
    $header_array[] = "ITEM";
    $header_array[] = "EAC (Including Adjustments)";
    $header_array[] = "RPT Period";

    return $header_array;
}
function insertOpenPOXLSX($sheet){
    $highest_row = $sheet->getHighestRow();
    //print $highest_row;
    $insert_sql = returnOpenPOInsert();
    $sql        = $insert_sql;
    for ($i = 2; $i <= $highest_row; $i++) {
        $col = "A";

        $proj          = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $swbs          = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $item          = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $description   = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $noun_1        = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $noun_2        = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $nre           = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $vendor        = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $col++;//skipping one field.
        $po            = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $line          = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $unit_price    = formatNumber4decNoComma($sheet->getCell($col++. $i)->getFormattedValue());
        $order_qty     = formatNumber4decNoComma($sheet->getCell($col++. $i)->getFormattedValue());
        $delivered_qty = formatNumber4decNoComma($sheet->getCell($col++. $i)->getFormattedValue());
        $pending_qty   = formatNumber4decNoComma($sheet->getCell($col++. $i)->getFormattedValue());
        $pending_amnt  = formatNumber4decNoComma($sheet->getCell($col++. $i)->getFormattedValue());
        $delv_date     = fixExcelDateMySQL($sheet->getCell($col++. $i)->getFormattedValue());
        $payment_terms = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $ledger_acct   = intval($sheet->getCell($col++. $i)->getFormattedValue());
        $clin          = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $effort        = addslashes(trim($sheet->getCell($col++. $i)->getFormattedValue()));
        $ecp_rea       = trim($sheet->getCell($col++. $i)->getFormattedValue());
        

        $sql.=
            "(
                $proj,
                $swbs,
                '$item',
                '$description',
                '$noun_1',
                '$noun_2',
                '$nre',
                $vendor,
                $po,
                $line,
                $unit_price,
                $order_qty,
                $delivered_qty,
                $pending_qty,
                $pending_amnt,
                '$delv_date',
                $payment_terms,
                $ledger_acct,
                '$clin',
                '$effort',
                '$ecp_rea'
                ),";
        if($i % 500==0)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i % 500!=0)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function moveShipData($from, $to_table, $ship_code){
    $sql = "INSERT `$to_table` SELECT * FROM $from where proj = $ship_code";
    //print $sql;
    $junk = dbCall($sql,"mars");

}
function getCountForTable($rpt_period,$ship_code,$table, $field){
    $table_array = explode(".",$table);
    $table_name = $table_array[0].".".$rpt_period.$table_array[1];
    $sql   = "select count(*) count from $table_name where $field = $ship_code";

    $rs_count    = dbCall($sql, "mars");
    $count = $rs_count->fields["count"];
    if($count<1) {
        $valid = "Not ready";
    }
    else{
        $valid = "READY!!";
    }
    return $valid;
}
function getCobraBreakFile($ship_code){
    if(strlen($ship_code)==3)
    {
        $ship_code = "0".$ship_code;
    }
    $sql = "select CA_BD1 from program where program = '$ship_code'";
    print $sql;
    $rs = dbCallCobra($sql);
    $break_file = $rs->fields["CA_BD1"];
    return $break_file;
}
function returnCobraCodeInsert(){
    $sql = "insert into meac.cobra_codes (ship_code, code, `desc`, ca, wp) values";
    return $sql;
}
function insertWPsForCobraCode($insert_sql,$ship_code, $code, $description){
    $sql = "
          SELECT 
              PROGRAM, 
              CA1,
              wp 
          FROM CAWP 
          WHERE PROGRAM = '$ship_code' AND CA1 LIKE '%$code%' AND WP > '' AND WP LIKE '%matl%'
          GROUP BY PROGRAM, CA1,wp
        ";
    //print $sql;
    $rs = dbCallCobra($sql);
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $ship_code = trim($rs->fields["PROGRAM"]);
        $ca        = trim($rs->fields["CA1"]);
        $wp        = trim($rs->fields["wp"]);
        $sql.="($ship_code, '$code','$description', '$ca', '$wp'),";
        $rs->MoveNext();
    }
    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql);
}

if($control =="rpt_period")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="[";

    $sql = "select rpt_period from fmm_evms.calendar ORDER BY rpt_period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $data.="      
        {
            \"id\": $rpt_period,
            \"text\": \"$rpt_period\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control =="ship_code" or $control =="ship_code_cbm" or $control=="ship_code_wp_table" or $control=="ship_code_status_grid" or $control=="ship_code_swbs_summary")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="[";

    $sql = "select code from fmm_evms.master_project where active = 'true' ORDER BY code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $code = $rs->fields["code"];
        $data.="{
            \"id\": $code,
            \"text\": \"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="load_cbm"){
    $ship_code_array = explode(",", $ship_code);
    foreach ($ship_code_array as $value){
        if(strlen($value)==3)
        {
            $ship_code = "0".$value;
        }
        deleteFromTable("MEAC", $rpt_period."_cbm", "ship_code", $ship_code);
        insertCBMFromBaanRptPeriod($ship_code,$rpt_period);
        deleteFromTable("MEAC", $rpt_period."_wp_gl_detail", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_wp_open_po", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_wp_committed_po", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_wp_open_buy", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_wp_ebom", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);

    }
    deleteFromTable("meac", $rpt_period."_cbm", "material", "");
    die("CBM Loaded");

}
if($control=="build_meac_tables"){
    buildMEACTablesforRptPeriod($rpt_period);
    die("made it");
}
if($control=="upload_v2"){
    //array_debug($_REQUEST);
    $currentDir = getcwd();
    $uploadDirectory = "\uploads\\";
    $uploadDirectory = $g_path_to_util."uploads\\";

    $errors = []; // Store all foreseen and unforseen errors here
    $fileExtensions = ['xlsx','jpg','png']; // Get all the file extensions
    $file_uploaded = key($_FILES);

    $fileName    = $_FILES[$file_uploaded]['name'];
    $fileSize    = $_FILES[$file_uploaded]['size'];
    $fileTmpName = $_FILES[$file_uploaded]['tmp_name'];
    $fileType    = $_FILES[$file_uploaded]['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));

    $uploadPath = $uploadDirectory . basename($fileName);
    checkIfFileExistsThenDeletes($uploadPath);
    if (isset($fileName)) {

        if (! in_array($fileExtension,$fileExtensions)) {
            $errors[] = "This   file extension is not allowed. Please upload a JPEG or PNG file";
        }

        if ($fileSize > 5000000) {
            $errors[] = "This file is more than 20MB. Sorry, it has to be less than or equal to 2MB";
        }

        if (empty($errors)) {

            $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

            if ($didUpload) {
                //echo "The file " . basename($fileName) . " has been uploaded";
            } else {
                //echo "An error occurred somewhere. Try again or contact the admin";
            }
        } else {
            foreach ($errors as $error) {
                //echo $error . "These are the errors" . "\n";
            }
        }
    }

    $objReader   = PHPExcel_IOFactory::createReader('Excel2007');
    $objPHPExcel = $objReader->load($uploadPath);

    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
     //now do whatever you want with the active sheet

    $highest_row = $sheet->getHighestRow();
    $last_column = $sheet->getHighestColumn();
    if($file_uploaded=="open_po"){
        deleteFromTable("mars", $file_uploaded,"proj", $ship_code);
        insertOpenPOXLSX($sheet);
        $open_po_table = $rpt_period . "_open_po";
        $create_table  = checkIfTableExists($schema, $open_po_table);
        if($create_table== "create_table"){
            createTableFromBase("mars", "open_po", $rpt_period."_open_po");
        }
        deleteFromTable("mars",$rpt_period."_open_po" ,"proj", $ship_code);
        moveShipData("mars.open_po", $rpt_period."_open_po", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_wp_open_po", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);
    }
    if($file_uploaded=="committed_po"){
        deleteFromTable("mars", $file_uploaded,"proj", $ship_code);
        insertCommittedPOXLSX($sheet);
        $committed_po_table = $rpt_period . "_committed_po";
        $create_table       = checkIfTableExists($schema, $committed_po_table);
        if($create_table== "create_table"){
            createTableFromBase("mars", "committed_po", $rpt_period."_committed_po");
        }
        deleteFromTable("mars",$rpt_period."_committed_po" ,"proj", $ship_code);
        moveShipData("mars.committed_po", $rpt_period."_committed_po", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_wp_committed_po", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);

    }
    if($file_uploaded=="gl_detail"){
        deleteFromTable("mars", $file_uploaded,"proj", $ship_code);
        insertGlDetailXLSX($ship_code, $sheet);

        $gl_detail_table = $rpt_period . "_gl_detail";
        $create_table    = checkIfTableExists($schema, $gl_detail_table);
        if($create_table== "create_table"){
            createTableFromBase("mars", "gl_detail", $rpt_period."_gl_detail");
        }
        deleteFromTable("mars",$rpt_period."_gl_detail" ,"proj", $ship_code);
        moveShipData("mars.gl_detail", $rpt_period."_gl_detail", $ship_code);
        deleteFromTable("meac", $rpt_period."_swbs_gl_summary_stage","ship_code", $ship_code);
        deleteFromTable("meac", $rpt_period."_swbs_gl_summary","ship_code", $ship_code);
        deleteFromTable("meac", $rpt_period."_wp_gl_detail","ship_code", $ship_code);
    }
    if($file_uploaded=="open_buy"){
        deleteFromTable("mars", $file_uploaded,"ship_code", $ship_code);
        insertOpenBuyXLSX($sheet);

        $open_buy_table = $rpt_period . "open_buy";
        $create_table   = checkIfTableExists($schema, $open_buy_table);
        if($create_table== "create_table"){
            createTableFromBase("mars", "open_buy", $rpt_period."_open_buy");
        }
        deleteFromTable("mars",$rpt_period."_open_buy" ,"ship_code", $ship_code);
        $sql = "INSERT ".$rpt_period."_open_buy SELECT * FROM mars.open_buy where ship_code= $ship_code";

        $junk = dbCall($sql,"mars");
        correctShockOpenBuyItemShortageRptPeriod($ship_code, $rpt_period);
        deleteFromTable("MEAC", $rpt_period."_wp_open_buy", "ship_code", $ship_code);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);
    }
    die();
}
if($control=="excel_export"){
    $header_array = returnHeaders();
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("MEAC ITEM Level EAC");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sql = "
    SELECT
          ship_code,
          wp,
          item,
          inflation_eac eac,
          rpt_period
        FROM reest3
        WHERE ship_code = $ship_code
                and wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        and wp like '%MATL%'
        union ALL
        SELECT
          ship_code,
          wp,
          item,
          eac_delta eac,
          rpt_period
        FROM reest_pending
        WHERE ship_code = $ship_code
        and wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        and wp like '%MATL%'
    ";
    $rs = dbCall($sql,"meac");
    $data_start = 2;
    while (!$rs->EOF)
    {
        $header_col = "A";

        $ship_code  = $rs->fields["ship_code"];
        $wp         = $rs->fields["wp"];
        $item       = $rs->fields["item"];
        $eac        = $rs->fields["eac"];
        $rpt_period = $rs->fields["rpt_period"];


        $sheet->setCellValue($header_col++.$data_start, $ship_code);

        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col.$data_start, $eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col++.$data_start, $rpt_period);

        $data_start++;
        $rs->MoveNext();
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/meac_eac_log".$token.".xlsx");
    $path = "../util/excel_exports/meac_eac_log".$token.".xlsx";
    die($path);
}
if($control=="item_2buyer"){
    $ship_code_array = explode(",", $ship_code);
    truncateTable("meac", $rpt_period."_master_buyer");
    loadBaanBuyerIDListRptPeriod($rpt_period);
    //print "finished Master Buyer";

    foreach ($ship_code_array as $value){
        deleteFromTable("MEAC", $rpt_period."_buyer_reponsible", "ship_code", $value);
        loadResponsibleBuyerRptPeriod($value, $rpt_period);
        loaditem2buyerRptPeriodByShip($value, $rpt_period);
        deleteFromTable("meac", $rpt_period."_change_item", "ship_code", $value);
        loadEFDBChangeBAANRptPeriod($value, $rpt_period);
        //print "finished Responsible Buyer $value";
    }
    die("BUYER's Loaded");

}
if($control=="inv_trans"){
    $ship_code_array = explode(",", $ship_code);
    foreach ($ship_code_array as $ship_code){
        deleteFromTable("meac", $rpt_period."_inv_transfers", "ship_code", $ship_code);
        loadINVTranserfersRptPeriod($ship_code, $rpt_period);
        deleteFromTable("meac", $rpt_period."_po_data", "ship_code", $ship_code);
        loadFortisPODataRptPeriod($ship_code, $rpt_period);
    }
    die("INV Transfers Loaded");
}
if($control=="build_wp_gl_detail"){
    $ship_code_str = $ship_code;

    $ship_code_array = explode(",", $ship_code);
    foreach ($ship_code_array as $value){
        if(strlen($value)==3)
        {
            $ship_code = "0".$value;
        }
        $sql   = "select count(*) count from mars." . $rpt_period . "_gl_detail where proj = $ship_code";
        $rs    = dbCall($sql, "mars");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load GL Detail For ".$ship_code );
        }
        $sql   = "select count(*) count from ".$rpt_period."_inv_transfers where ship_code = $ship_code";
        $rs    = dbCall($sql, "meac");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load INV Transfers For ".$ship_code );
        }
        $sql   = "select count(*) count from meac.".$rpt_period."_cbm where ship_code = $ship_code";
        $rs    = dbCall($sql, "meac");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load CBM For ".$ship_code );
        }
    }
    deleteFromTableIN("meac", $rpt_period."_wp_gl_detail","ship_code", $ship_code_str);
    insertGLdetailWITHWPRptPeriod($rpt_period, $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary_stage","ship_code", $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary","ship_code", $ship_code_str);

    die("WP GL DETAIL LOADED");
}
if($control=="build_wp_open_po"){
    $ship_code_str = $ship_code;
    $ship_code_array = explode(",", $ship_code);
    foreach ($ship_code_array as $value){
        if(strlen($value)==3)
        {
            $ship_code = "0".$value;
        }
        $sql   = "select count(*) count from mars." . $rpt_period . "_open_po where proj = $ship_code";
        $rs    = dbCall($sql, "mars");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load OPen PO For ".$ship_code );
        }

        $sql   = "select count(*) count from meac.".$rpt_period."_cbm where ship_code = $ship_code";
        $rs    = dbCall($sql, "meac");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load CBM For ".$ship_code );
        }
    }
    deleteFromTableIN("meac", $rpt_period."_wp_open_po", "ship_code", $ship_code_str);
    insertOpenPOWithWPRptPeriod($rpt_period, $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary_stage","ship_code", $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary","ship_code", $ship_code_str);
    die("WP OPEN PO LOADED");

}
if($control=="build_wp_committed_po"){
    $ship_code_str = $ship_code;

    $ship_code_array = explode(",", $ship_code);
    foreach ($ship_code_array as $value){
        if(strlen($value)==3)
        {
            $ship_code = "0".$value;
        }
        $sql   = "select count(*) count from mars." . $rpt_period . "_committed_po where proj = $ship_code";
        $rs    = dbCall($sql, "mars");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load Committed PO For ".$ship_code );
        }

        $sql   = "select count(*) count from meac.".$rpt_period."_cbm where ship_code = $ship_code";
        $rs    = dbCall($sql, "meac");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load CBM For ".$ship_code );
        }
    }
    deleteFromTableIN("meac", $rpt_period."_wp_committed_po", "ship_code", $ship_code_str);
    insertCommittedPOWPRptPeriod($rpt_period, $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary_stage","ship_code", $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary","ship_code", $ship_code_str);
    die("WP Committed PO Loaded");
}
if($control=="build_wp_open_buy"){
    $ship_code_str = $ship_code;
    $ship_code_array = explode(",", $ship_code);

    foreach ($ship_code_array as $value){
        if(strlen($value)==3)
        {
            $ship_code = "0".$value;
        }
        $sql   = "select count(*) count from mars." . $rpt_period . "_open_buy where ship_code = $ship_code";
        $rs    = dbCall($sql, "mars");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load OPEN BUY For ".$ship_code );
        }

        $sql   = "select count(*) count from meac.".$rpt_period."_cbm where ship_code = $ship_code";
        $rs    = dbCall($sql, "meac");
        $count = $rs->fields["count"];
        if($count<1){
            die("please load CBM For ".$ship_code );
        }
    }
    deleteFromTableIN("meac", $rpt_period."_wp_open_buy", "ship_code", $ship_code_str);
    insertOpenBuyWithWPRptPeriod($rpt_period, $ship_code_str);
    die("OPEN BUY FOR $ship_code_str HAS BEEN LOADED!");
}
if($control =="build_wp_ebom"){
    $ship_code_str = $ship_code;
    $ship_code_array = explode(",", $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_wp_ebom", "ship_code", $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary_stage","ship_code", $ship_code_str);
    deleteFromTableIN("meac", $rpt_period."_swbs_gl_summary","ship_code", $ship_code_str);
    //array_debug($ship_code_array);
    foreach ($ship_code_array as $value){
        insertEBOMWPRptPeriod($rpt_period, $value);
    }

    die("WP EBOM Loaded FOR $ship_code_str");
}
if($control=="status_grid"){
    //print $ship_code;
    $sql = "select * from (";
    foreach ($ship_code as $value){
        $sql.= "select $value as ship_code, `check`, `table` tab, field, `order` ord from meac.steps_for_file union all ";
    }
    $sql = substr($sql, 0, -11);
    $sql.=") s order by s.ship_code, s.ord";
    $rs = dbCall($sql, "meac");
    $id = 1;
    $data = "[";
    while (!$rs->EOF)
    {
        $check     = $rs->fields["check"];
        $table     = $rs->fields["tab"];
        $ship_code = $rs->fields["ship_code"];
        $field     = $rs->fields["field"];
        $valid     = getCountForTable($rpt_period,$ship_code,$table,$field);

        //print $table;
        $data.="{
            \"id\"       : $id,
            \"ship_code\":\"$ship_code\",
            \"check\"    :\"$check\",
            \"valid\"    :\"$valid\"
        },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control =='build_swbs_summary_table'){
    $ship_code_array = explode(",", $ship_code);
    foreach ($ship_code_array as $value){
        $sql.= "select $value as ship_code, `check`, `table` tab, field from meac.steps_for_file where id <> 12 union all ";
    }
    $sql = substr($sql, 0, -11);
    $rs = dbCall($sql, "meac");
    $id = 1;
    while (!$rs->EOF)
    {
        $check     = $rs->fields["check"];
        $table     = $rs->fields["tab"];
        $ship_code = $rs->fields["ship_code"];
        $field     = $rs->fields["field"];
        $valid     = getCountForTable($rpt_period, $ship_code, $table, $field);
        if($valid=="Not ready"){
            die("Fix $ship_code $check");
        }
        $rs->MoveNext();
    }
    foreach ($ship_code_array as $value){

        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
        insertSWBSSummaryStagingRptPeriod($value,$rpt_period);
    }

    foreach ($ship_code_array as $value){
        deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);
        insertSWBSSummaryRptPeriod($value, $rpt_period);
    }
    die("PART Level MEAC Table BUILT");
}
if($control =="load_cobra_codes"){
    $ship_code_str = $ship_code;
    deleteFromTableIN("meac", "cobra_codes", "ship_code", $ship_code);
    $ship_code_array = explode(",",$ship_code);
    foreach ($ship_code_array as $ship_code){
        if(strlen($ship_code)==3)
        {
            $ship_code = "0".$ship_code;
        }

        $break_file = getCobraBreakFile($ship_code);
        $sql = "
        SELECT
              c3,
              (SELECT CODEDESC FROM BDNDETL bdn WHERE BREAKFILE = '$break_file' AND bdn.CODE = c.c3) AS description
        FROM CAWP c
        WHERE PROGRAM = '$ship_code' and c3 <> ''
        GROUP BY c3
        ORDER BY c3
        ";
        $rs = dbCallCobra($sql);
        $insert_sql = returnCobraCodeInsert();
        while (!$rs->EOF) {
            $description = $rs->fields["description"];
            $c3          = $rs->fields["c3"];
            insertWPsForCobraCode($insert_sql,$ship_code, $c3, $description);
            $rs->MoveNext();
        }
    }
    die($ship_code_str." Codes have been Loaded");
}

if($control=="build_meac_file"){
    $prev_rpt_period = getPreviousRPTPeriod($rpt_period);
    //print $sql;

    $path = buildMEACFile($ship_code, $prev_rpt_period,$rpt_period,$g_path_to_util);
    //$path = "../util/excel_exports/485- Tool Nov 2017568 MEAC Prelim.xlsx";
    die($path);
}