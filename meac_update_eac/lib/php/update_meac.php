<?php
include('../../../inc/inc.php');
include('../../../inc/inc.cobra.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
//include('../../../inc/lib/php/simplexlsx-master/simplexlsx.class.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
//$user = $_SESSION["user_name"];
$user = "fs11239";

session_write_close();
function returnReEstPendingInsert(){
    $sql = "insert into reest_pending (ship_code, wp, item, eac_delta, user, rpt_period, `comment`, bucket) VALUES";
    return $sql;
}
function makeTheToolMatchCobra($ship_code, $combined_array){
    $today = date("Ymd");
    $destination_table = "z_".$today."_reest3";
    dropTable("z_meac", $destination_table);

    $source_table       = "reest3";
    $source_schema      = "meac";
    $destination_schema = "z_meac";
    duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);

    $insert_sql = "INSERT INTO reest3  (ship_code, wp,item, eac, inflation_eac, rpt_period, `comment`, bucket ) VALUES ";
    $sql = $insert_sql;
    foreach ($combined_array as $key=>$value){
        $wp                = $value["wp"];
        $tool_cobra_delta  = $value["tool_cobra_delta"];
        if($tool_cobra_delta!=0){
            $sql .="
            (
                $ship_code,
                '$wp',
                'JAN ADJ',
                $tool_cobra_delta,
                $tool_cobra_delta,
                201712,
                'Error Reconcile',
                'Error Reconcile'
            ),";
        }
    }
    $sql = substr($sql, 0, -1);
    print $sql;
    $junk = dbCall($sql,"meac");
}

function makeSureEveryCobraWPIsINTool($ship_code, $rpt_period){
    $cobra_wp_array = getCobraEACTphaseTable($ship_code);
    foreach ($cobra_wp_array as $wp =>$eac){
        $sql = "select ship_code, wp from meac.reest3 where ship_code = $ship_code and wp = '$wp'";

        $rs = dbCallZorro($sql, "meac");
        $wp = $rs->fields["wp"];
        if($wp =="" and $eac <> 0 ){
            print $sql."<br>";
            $insert_sql = "insert into reest3  (ship_code, wp,item, eac, inflation_eac, rpt_period, `comment`, bucket ) values ";
            $insert_sql.="
            (
                $ship_code,
                '$wp',
                'Allocation for $wp',
                $eac,
                $eac,
                $rpt_period,
                'Created WP',
                ''
            )";
            print $insert_sql."<br>";
            //$junk = dbCall($sql, "meac");
        }

    }
}

function getReestEAC($ship_code){
    $sql = "select wp, sum(inflation_eac) eac from reest3 where ship_code = $ship_code group by ship_code, wp";
    $tool_array = array();
    $rs = dbCall($sql, "meac");
    while (!$rs->EOF)
    {
        $wp  = $rs->fields["wp"];
        $eac = $rs->fields["eac"];
        $wp  = checkODCWP($ship_code, $wp);

        $tool_array[$wp] = $eac;
        $rs->MoveNext();
    }
    return $tool_array;
}

function makeMaterialEACandPcChanges($ship_code, $rpt_period,$g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil ){
    /*
     * 1.  get all the ACWP from COBRA by WP.
     * 2.  GET the accepted eac from the tool.
     * 3.  loop through the wp calculating the PC
     * 4.
     * */
    $next_rpt_period    = getNextRPTPeriod($rpt_period);
    $year               = intval(substr($next_rpt_period, 0, 4));
    $month              = month2digit(substr($next_rpt_period, -2));
    $day                = getMonthEndDay($next_rpt_period);

    if(strlen($ship_code)==3)
    {
        $ship_code = "0".$ship_code;
    }
    $sql_array = array();
    $a_array   = getTotalActualsForMATLWP($ship_code);
    $eac_array = getReestEAC($ship_code);
    foreach ($a_array as $wp =>$acwp){

        $cawpid = getCAWPID($ship_code, $wp);
        if($cawpid==""){

            continue;
        }
        $new_eac = formatNumber6decNoComma($eac_array[$wp]);

        $pc              = formatNumber6decNoComma($acwp / $new_eac) * 100;
        $future_offset   = getODCFutureCost($ship_code, $cawpid);
        $total_offset    = $acwp + $future_offset;
        $new_etc         = formatNumber4decNoComma($new_eac - $total_offset);
        $forcast_records = getTotalNumberofForecastRecords($ship_code, $cawpid);

        if($forcast_records<1){
            $forcast_records = 1;
        }
        $spread_val      = formatNumber4decNoComma($new_etc / $forcast_records);


        $record_count =checkIFForecastRecordExists($ship_code, $cawpid);
        if($record_count<1){
            //get the rest of the periods for this WP
            //insert all the records for each period between the next month, and the end of the last month.
            //print $wp ." DOES NOT EXIST IN THE TPHASE TABLE <br>";
            $sql = "insert into TPHASE (PROGRAM, CAWPID, CECODE, CLASS, DF_DATE, DIRECT) values ('$ship_code', $cawpid, 'MATL', 'Forecast', '$year-$month-$day 00:00:00.000',$new_etc)";
            //runSQLCommandUtil($ship_code,$sql, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
            $sql_array[]= $sql;
            //print $sql."<br>";

        }
        $sql_updateCAWP = "update cawp set eac = $new_eac, EAC_NONLAB = $new_eac, PC_COMP = $pc where PROGRAM = '$ship_code' and wp = '$wp'";
        $sql_array[]= $sql_updateCAWP;

        $sql_updateTPHASE= "update tphase set DIRECT = $spread_val where program = '$ship_code' and CAWPID in ($cawpid) and CLASS = 'Forecast'";
        $sql_array[] = $sql_updateTPHASE;
    }
    $split_array = array_chunk($sql_array, 999);
    foreach ($split_array as $sql_chunks){
        $sql_implode = implode(";", $sql_chunks);

        array_debug($sql_chunks);
        //print $sql_implode;
        runSQLCommandUtil($ship_code,$sql_implode, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
    }

}
function shipCodeWC($ship_code,$view){
    if($ship_code =="All"){
        if($view=="all"){
            $ship_code_wc = "";
        }
        else{

        }
    }
    else{
     $ship_code_wc = "re.ship_code = $ship_code and ";
    }
    return $ship_code_wc;
}
function getCobraEACTphaseTable($ship_code){
    if(strlen($ship_code)==3)
    {
        $ship_code = "0".$ship_code;
    }
    if($ship_code =="0471"){
        $ship_code = "0471-";
    }
    $sql = "      
        SELECT
          t.program,
          c.wp,
          (sum(DIRECT) +
           sum(GANDA) +
           sum(SYSGA) +
           sum(ODCNOGA)) sum
        FROM tphase t
          LEFT JOIN CAWP c ON
                             t.PROGRAM = c.PROGRAM
                             AND c.CAWPID = t.CAWPID
        WHERE t.PROGRAM = '$ship_code'
              AND (c.wp LIKE 'matl%' OR c.WP LIKE 'odc-%')
              AND CLASS IN ('Actual', 'EA', 'CA', 'forecast')
        GROUP BY t.program, c.wp
              ";
    //print $sql;
    $rs = dbCallCobra($sql);
    $i = 00;
    while (!$rs->EOF)
    {
        $ship_code        = intval($ship_code);
        $wp               = $rs->fields["wp"];
        $cobra_eac        = formatNumber4decNoComma($rs->fields["sum"]);
        $cobra_array[$wp] = $cobra_eac;
        $i++;
        $rs->MoveNext();
    }
    //print $sql;
    return $cobra_array;
}
function checkODCWP($ship_code, $wp){
    if($ship_code== 477 or $ship_code==479){
        $middle = substr($wp, 5,3);
        if($middle=="042" or $middle=="854"){
            $wp = "ODC-".$middle."-999";
            return $wp;
        }
        else{
            return $wp;
        }
    }
    else{
        return $wp;
    }
}
function findColumnInSheet($sheet,$col_name){
    $val = "";
    $start_cell= "A";
    $highest_col= $sheet->getHighestColumn();
    $i= 1;
    $lower_col_name = strtolower($col_name);
    while($val !=$lower_col_name){

        $val = strtolower(trim($sheet->getCell($start_cell."1")->getFormattedValue()));
        if($val==$lower_col_name){
            return $start_cell;

        }
        if($start_cell==$highest_col){
            return "Did not find it";
        }
        $start_cell++;
    }
    return "Did not find it";

}
function returnHeaders(){
    $header_array[] = "Hull";
    $header_array[] = "WP";
    $header_array[] = "ITEM";
    $header_array[] = "EAC (Including Adjustments)";
    $header_array[] = "RPT Period";
    $header_array[] = "Comment";
    $header_array[] = "Bucket";

    return $header_array;
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

    $sql = "select rpt_period from fmm_evms.calendar;";
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
if($control=="make_match"){
    $ship_code= "475";
    makeSureEveryCobraWPIsINTool("0473", 201802);
        die();
    $sql = "
    select
            ship_code,
            wp,
            (select sum(inflation_eac) eac from reest3 re2 where re2.ship_code = re.ship_code and re.wp = re2.wp group by re.ship_code, wp)as prev_EAC,
            sum(eac_delta) delta
        from reest_pending re
         where re.ship_code = $ship_code
         and re.wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        group by ship_code, wp
union
  select
            re.ship_code,
            re.wp,
            sum(inflation_eac) prev_EAC,
            0 delta
        from reest3 re
          left join reest_pending pending
         on re.ship_code = pending.ship_code and re.wp= pending.wp
        where  re.ship_code = $ship_code 
        and  re.wp like '%matl%'
        and re.wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
        and pending.ship_code is null
        group by ship_code, wp";

    $rs = dbCall($sql,"meac");
    $cobra_eac_array = getCobraEACTphaseTable("04751217");

    $id = 1;
    $total_diff = 0;
    $total_prev = 0;
    $total_new = 0;
    $tool_array = array();
    $i = 0;
    while (!$rs->EOF)
    {
        $ship_code = $rs->fields["ship_code"];
        $wp        = $rs->fields["wp"];
        //FIX 17 AND 19 ODC IN COBRA/MATL IN TOOL.
        $wp           = checkODCWP($ship_code, $wp);
        //print " this is wp ".$wp."<br><br><br>";
        $prev_EAC     = formatNumber4decNoComma($rs->fields["prev_EAC"]);
        $delta        = $rs->fields["delta"];
        $proposed_eac = formatNumber4decNoComma($prev_EAC + $delta);

        $tool_array[$i]["ship"]              = $ship_code;
        $tool_array[$i]["wp"]                = $wp;
        $tool_array[$i]["tool_eac"]          = $prev_EAC;
        $tool_array[$i]["tool_proposed_eac"] = $proposed_eac;
        $tool_array[$i]["tool_delta"]        = $delta;

        $i++;
        $rs->MoveNext();
    }

    //die($data);
    $combined_array = array();

    $i = 0;
    foreach ($tool_array as $key=>$value){

        $wp                = $value["wp"];
        $tool_eac          = $value["tool_eac"];
        $tool_delta        = $value["tool_delta"];
        $tool_proposed_eac = $value["tool_proposed_eac"];
        $cobra_val         = formatNumber4decNoComma($cobra_eac_array[$wp]);

        $combined_array[$i]["wp"]                = $wp;
        $combined_array[$i]["tool_eac"]          = $tool_eac;
        $combined_array[$i]["tool_proposed_eac"] = $tool_proposed_eac;
        $combined_array[$i]["cobra_eac"]         = $cobra_val;
        $combined_array[$i]["tool_cobra_delta"]  = formatNumber4decNoComma($tool_eac - $cobra_val);
        $combined_array[$i]["tool_delta"]        = $tool_delta;

        $i++;
        //array_debug($value);
    }

    makeTheToolMatchCobra($ship_code, $combined_array);

}
if($control=="upload_v2"){

    $currentDir = getcwd();
    $uploadDirectory = "\uploads\\";
    $uploadDirectory = $g_path_to_util."uploads\\";

    clearDirectory($uploadDirectory);
    $errors = []; // Store all foreseen and unforseen errors here
    $fileExtensions = ['xlsx','jpg','png']; // Get all the file extensions

    $fileName    = $_FILES['myfile']['name'];
    $fileSize    = $_FILES['myfile']['size'];
    $fileTmpName = $_FILES['myfile']['tmp_name'];
    $fileType    = $_FILES['myfile']['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));

    $uploadPath = $uploadDirectory . basename($fileName);
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
    die($uploadPath);

}
if($control=="insert_item_delta"){

    deleteFromTable("meac", "reest_pending","ship_code", $ship_code);
    $sql = "delete FROM reest3 where ship_code = $ship_code and rpt_period = $rpt_period";
    $junk = dbCall($sql,"meac");

    $objReader   = PHPExcel_IOFactory::createReader('Excel2007');
    $objPHPExcel = $objReader->load($uploadPath);

    $insert_sql = returnReEstPendingInsert();
    $sql        = $insert_sql;
    $objPHPExcel->setActiveSheetIndex(1);
    $sheet       = $objPHPExcel->getActiveSheet();
    $highest_row = $sheet->getHighestRow();
    $ship_code   = intval($sheet->getCell("A2")->getFormattedValue());
    $delta_col   = findColumnInSheet($sheet, "change");

    $sheet->getStyle($delta_col."2:".$delta_col.$highest_row)->getNumberFormat()->setFormatCode('0.00');

    $ship_code_col = findColumnInSheet($sheet, "Hull");
    $wp_col        = findColumnInSheet($sheet, "WP");
    $item_col      = findColumnInSheet($sheet, "item");
    $comment_col   = findColumnInSheet($sheet, "comment");
    $bucket_col    = findColumnInSheet($sheet, "bucket");
    for ($i = 2; $i <= $highest_row; $i++) {
        $col = "A";

        $ship_code = intval($sheet->getCell($ship_code_col . $i)->getFormattedValue());
        $wp        = trim($sheet->getCell($wp_col . $i)->getFormattedValue());
        $item      = trim($sheet->getCell($item_col . $i)->getFormattedValue());
        $delta     = formatNumber4decNoComma($sheet->getCell($delta_col . $i)->getFormattedValue());
        $comment   = processDescription(trim($sheet->getCell($comment_col . $i)->getFormattedValue()));
        $bucket    = trim($sheet->getCell($bucket_col . $i)->getFormattedValue());

        if($delta==0){
            continue;
        }
        if($ship_code==""){
            continue;
        }
        $sql .="
            (
            $ship_code,
            '$wp',
            '$item',
            $delta,
            '$user',
            $rpt_period,
            '$comment',
            '$bucket'),";
        if($i % 500==0)
        {
            $sql        = substr($sql, 0, -1);
            $junk       = dbCall($sql, "meac");
            $insert_sql = returnReEstPendingInsert();
            $sql        = $insert_sql;
        }
    }
    if($i!=500){
        $sql  = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql, "meac");
    }
    die("made");
}
if($control=="accept_changes_tool"){
    $today = date("Ymd");
    $destination_table = "z_".$today."_reest3";
    dropTable("z_meac", $destination_table);

    $source_table       = "reest3";
    $source_schema      = "meac";
    $destination_schema = "z_meac";
    duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);
    $sql = "delete FROM reest3 where ship_code = $ship_code and rpt_period = $rpt_period";
    $junk = dbCall($sql,$schema);

    $sql ="select ship_code, wp, item, eac_delta, `comment`, bucket  from reest_pending where ship_code = $ship_code";
    $insert_sql = "insert into reest3  (ship_code, wp,item, eac, inflation_eac, rpt_period, `comment`, bucket ) values ";
    $rs = dbCall($sql,"meac");
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $ship_code = $rs->fields["ship_code"];
        $wp        = trim($rs->fields["wp"]);
        $item      = trim($rs->fields["item"]);
        $comment   = trim($rs->fields["comment"]);
        $bucket    = trim($rs->fields["bucket"]);
        $delta     = formatNumber4decNoComma($rs->fields["eac_delta"]);
        $sql.="
        (
            $ship_code,
            '$wp',
            '$item',
            $delta,
            $delta,
            $rpt_period,
            '$comment',
            '$bucket'
        ),";
        $rs->MoveNext();
    }

    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql,"meac");
    deleteFromTable("meac", "reest_pending","ship_code", $ship_code);
    if($ship_code> 475 and $ship_code < 487){
        makeMaterialEACandPcChanges($ship_code, $rpt_period,$g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
    }
    die("Tool ");
}
if($control=="meac_eac_change_grid"){

    if($view =="all"){

        $sql = "
    select
            ship_code,
            wp,
            (select sum(inflation_eac) eac from reest3 re2 where re2.ship_code = re.ship_code and re.wp = re2.wp group by re.ship_code, wp)as prev_EAC,
            sum(eac_delta) delta
        from reest_pending re
         where re.ship_code = $ship_code
         and re.wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        group by ship_code, wp
union
  select
            re.ship_code,
            re.wp,
            sum(inflation_eac) prev_EAC,
            0 delta
        from reest3 re
          left join reest_pending pending
         on re.ship_code = pending.ship_code and re.wp= pending.wp
        where  re.ship_code = $ship_code 
        and  re.wp like '%matl%'
        and re.wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
        and pending.ship_code is null
        group by ship_code, wp";

    }
    elseif ($view =="pending"){


        $sql = "    
        select
            ship_code,
            wp,
            (select sum(inflation_eac) eac 
            from reest3 re2 
            where re2.ship_code = re.ship_code 
            and re.wp = re2.wp group by re.ship_code, wp)as prev_EAC,
            sum(eac_delta) delta
        from reest_pending re
        where ship_code = $ship_code
        and re.wp not in ('MATL-825-999','MATL-829-999', 'MATL-828-999')
        group by ship_code, wp
        ";


    }

    $rs = dbCall($sql,"meac");
    $cobra_eac_array = getCobraEACTphaseTable($ship_code);

    $data = "[";
    $id = 1;
    $total_diff = 0;
    $total_prev = 0;
    $total_new = 0;
    $tool_array = array();
    $i = 0;
    while (!$rs->EOF)
    {
        $ship_code = $rs->fields["ship_code"];
        $wp        = $rs->fields["wp"];
        //FIX 17 AND 19 ODC IN COBRA/MATL IN TOOL.
        $wp           = checkODCWP($ship_code, $wp);
        //print " this is wp ".$wp."<br><br><br>";
        $prev_EAC     = formatNumber4decNoComma($rs->fields["prev_EAC"]);
        $delta        = $rs->fields["delta"];
        $proposed_eac = formatNumber4decNoComma($prev_EAC + $delta);

        $tool_array[$i]["ship"]              = $ship_code;
        $tool_array[$i]["wp"]                = $wp;
        $tool_array[$i]["tool_eac"]          = $prev_EAC;
        $tool_array[$i]["tool_proposed_eac"] = $proposed_eac;
        $tool_array[$i]["tool_delta"]        = $delta;

        $i++;
        $rs->MoveNext();
    }

    //die($data);
    $combined_array = array();

    $i = 0;
    foreach ($tool_array as $key=>$value){

        $wp                = $value["wp"];
        $tool_eac          = $value["tool_eac"];
        $tool_delta        = $value["tool_delta"];
        $tool_proposed_eac = $value["tool_proposed_eac"];
        $cobra_val         = formatNumber4decNoComma($cobra_eac_array[$wp]);

        $combined_array[$i]["wp"]                = $wp;
        $combined_array[$i]["tool_eac"]          = $tool_eac;
        $combined_array[$i]["tool_proposed_eac"] = $tool_proposed_eac;
        $combined_array[$i]["cobra_eac"]         = $cobra_val;
        $combined_array[$i]["tool_cobra_delta"]  = formatNumber4decNoComma($tool_eac - $cobra_val);
        $combined_array[$i]["tool_delta"]        = $tool_delta;

        $i++;
        //array_debug($value);
    }
    $data = "[";
    $id = 1;
    foreach ($combined_array as $key=>$value){
        $wp                = $value["wp"];
        $tool_eac          = $value["tool_eac"];
        $tool_proposed_eac = $value["tool_proposed_eac"];
        $cobra_eac         = $value["cobra_eac"];
        $tool_cobra_delta  = $value["tool_cobra_delta"];
        $tool_delta        = $value["tool_delta"];


        $data.="{
            \"id\"                  : $id,
            \"ship_code\"           : \"$ship_code\",
            \"wp\"                  : \"$wp\",
            \"prev_eac\"            : $tool_eac,
            \"new_eac\"             : $tool_proposed_eac,
            \"cur_cobra_eac\"       : $cobra_eac,
            \"delta\"               : $tool_delta,
            \"tool_cobra_delta\"    : $tool_cobra_delta
        },";
        $id++;

    }
    //array_debug($combined_array);
    $tool_eac          = array_sum(array_column($combined_array, "tool_eac"));
    $tool_proposed_eac = array_sum(array_column($combined_array, "tool_proposed_eac"));
    $cobra_eac         = array_sum(array_column($combined_array, "cobra_eac"));
    $eac_delta_total   = array_sum(array_column($combined_array, "tool_delta"));
    $tool_cobra_delta  = array_sum(array_column($combined_array, "tool_cobra_delta"));
    $data.="{
            \"id\"               : $id,
            \"ship_code\"        : \"$ship_code\",
            \"wp\"               : \"TOTAL : \",
            \"prev_eac\"         : $tool_eac,
            \"new_eac\"          : $tool_proposed_eac,
            \"cur_cobra_eac\"    : $cobra_eac,
            \"delta\"             : $eac_delta_total,
            \"tool_cobra_delta\" : $tool_cobra_delta
        }";
    $data.="]";
    die($data);
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
          rpt_period,
          'implemented' as status,
          `comment`, 
          bucket
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
          rpt_period,
          'NOT implemented' as status,
          `comment`, 
          bucket
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
        $status     = $rs->fields["status"];
        $comment    = $rs->fields["comment"];
        $bucket     = $rs->fields["bucket"];


        $sheet->setCellValue($header_col++.$data_start, $ship_code);

        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col.$data_start, $eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col++.$data_start, $rpt_period);
        $sheet->setCellValue($header_col++.$data_start, $comment);
        $sheet->setCellValue($header_col++.$data_start, $bucket);
        $sheet->setCellValue($header_col++.$data_start, $status);

        $data_start++;
        $rs->MoveNext();
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/meac_eac_log".$token.".xlsx");
    $path = "../util/excel_exports/meac_eac_log".$token.".xlsx";
    die($path);
}