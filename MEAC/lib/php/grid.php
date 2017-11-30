<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
//include('../../../inc/lib/php/simplexlsx-master/simplexlsx.class.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
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
function returnOpenBuyInsert(){
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
    $insert_sql = returnOpenBuyInsert();
    $sql        = $insert_sql;
    for ($i = 2; $i <= $highest_row; $i++) {

        $col         = "A";
        $buyer              = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $ship_code          = trim($sheet->getCell($col++ . $i)->getFormattedValue());
        $swbs               = trim($sheet->getCell($col++ . $i)->getFormattedValue());
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

    $data ="{\"items\": [";

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
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($control =="ship_code" or $control =="ship_code_multi")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select code from fmm_evms.master_project where active = 'true' ORDER BY code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $code = $rs->fields["code"];
        $data.="      
        {
            \"id\": $code,
            \"text\": \"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
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
            $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
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
    }
    if($file_uploaded=="committed_po"){
        deleteFromTable("mars", $file_uploaded,"proj", $ship_code);
        insertCommittedPOXLSX($sheet);
    }
    if($file_uploaded=="gl_detail"){
        deleteFromTable("mars", $file_uploaded,"proj", $ship_code);
        insertGlDetailXLSX($ship_code, $sheet);
    }
    if($file_uploaded=="open_buy"){
        deleteFromTable("mars", $file_uploaded,"ship_code", $ship_code);
        insertOpenBuyXLSX($sheet);
    }
/*    $create_table = checkIfTableExists("mars", $rpt_period."_".$file_uploaded);
    if($create_table === true){
        dropTable("mars", $rpt_period."_".$file_uploaded);
        duplicateTable($file_uploaded, "mars", $rpt_period."_".$file_uploaded, "mars");
    }
    else{
        duplicateTable($file_uploaded, "mars", $rpt_period."_".$file_uploaded, "mars");
    }*/
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
