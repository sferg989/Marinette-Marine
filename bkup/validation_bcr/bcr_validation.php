<?php
include("../../inc/inc.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;

if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
function getMonthEndDay($rpt_period){
    $sql = "select month_end from calendar where rpt_period = $rpt_period";
    $rs  = dbCall($sql, "fmm_evms");
    $day = $rs->fields["month_end"];
    return $day;
}
function loadCOBRABCRLOG($ship_code, $rpt_period, $table_name){
    $year = intval(substr($rpt_period, 0, 4));
    $month = month2digit(substr($rpt_period, -2));
    $day  = getMonthEndDay($rpt_period);

    $sql = "
        select 
            PROGRAM,
            CA1,
            CA2,
            WP,
            LOGCOMMENT,
            DEBIT,  
            CREDIT,
            HOURS,
            AMOUNT 
        from BASELOG 
        where PROGRAM = '$ship_code' 
            and  (DATEPART(yy, STATUSDATE) = $year
            AND    DATEPART(mm, STATUSDATE) = $month
            AND    DATEPART(dd, STATUSDATE) =$day)
            and LOGCOMMENT not like '%log%'
    ";

    $rs = dbCallCobra($sql);
    $insert_sql = "
    insert into bcr_log.".$table_name." 
        (ship_code,
        ca,
        ca2,
        wp,
        `desc`,
        debit,
        credit,
        hours,
        amount) 
        values
 ";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ca     = addslashes(trim($rs->fields["CA1"]));
        $ca2    = addslashes(trim($rs->fields["CA1"]));
        $wp     = addslashes(trim($rs->fields["WP"]));
        $desc   = addslashes(trim($rs->fields["LOGCOMMENT"]));
        $debit  = addslashes(trim($rs->fields["DEBIT"]));
        $credit = addslashes(trim($rs->fields["CREDIT"]));
        $hours  = formatNumber4decNoComma($rs->fields["HOURS"]);
        $amt    = formatNumber4decNoComma($rs->fields["AMOUNT"]);

        $sql.= "(
        $ship_code,
        '$ca',
        '$ca2',
        '$wp',
        '$desc',
        '$debit',
        '$credit',
        $hours,
        $amt),";
        if($i==500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,"bcr_log");
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql,"bcr_log");
        $sql = $insert_sql;
    }
}

if($control=="load_cobra_data"){
    $schema = "cost2";
    $table_name   = $rpt_period . "_cost";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_cost", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    insertCobraCostData($ship_code, $schema, $table_name);

    $table_name   = $rpt_period . "_bcr";
    $create_table = checkIfTableExists("bcr_log", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("bcr_log","template_bcr", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "bcr_log");
    loadCOBRABCRLOG($ship_code, $rpt_period, $table_name);
}

if($filter =="rpt_period")
{
    if($q!="")
    {
        $wc = "where rpt_period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select rpt_period from fmm_evms.calendar $wc order by rpt_period DESC ";
    //print $sql;
    $rs = dbCall($sql, "fmm_evms");
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $data.="      
        {
            \"id\"  : $rpt_period,
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
