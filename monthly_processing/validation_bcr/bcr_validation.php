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
$schema = "cost2";
if(strlen($code)==3)
{
    $ship_code = "0".$code;
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
$ship_name          = $data["ship_name"];
function getMonthEndDay($rpt_period){
    $sql = "select month_end from calendar where rpt_period = $rpt_period";
    $rs  = dbCall($sql, "fmm_evms");
    $day = $rs->fields["month_end"];
    return $day;
}
function insertCobraCostData($ship_code, $schema, $table_name){
    $sql = "
    select 
        PROGRAM,
        CA1,
        WP,
        DESCRIP,
        BCWS,
        BCWP,
        ACWP,
        BAC,
        EAC,
        BCWS_HRS,
        BCWP_HRS,
        ACWP_HRS,
        BAC_HRS,
        EAC_HRS,
        PC_COMP 
    from CAWP where PROGRAM = '$ship_code' and wp > '' 
    ";
    $rs = dbCallCobra($sql);
    $insert_sql = "
        insert into $schema.".$table_name." (
        ship_code,
        ca,
        wp,
        `desc`,
        s,
        p,
        a,
        bac,
        eac,
        s_hours,
        p_hours,
        a_hours,
        bac_hours,
        eac_hours,
        PC_COMP) values 
 ";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ca        = addslashes(trim($rs->fields["CA1"]));
        $wp        = addslashes(trim($rs->fields["WP"]));
        $desc      = addslashes(trim($rs->fields["DESCRIP"]));
        $s         = formatNumber4decNoComma($rs->fields["BCWS"]);
        $p         = formatNumber4decNoComma($rs->fields["BCWP"]);
        $a         = formatNumber4decNoComma($rs->fields["ACWP"]);
        $bac       = formatNumber4decNoComma($rs->fields["BAC"]);
        $eac       = formatNumber4decNoComma($rs->fields["EAC"]);
        $s_hours   = formatNumber4decNoComma($rs->fields["BCWS_HRS"]);
        $p_hours   = formatNumber4decNoComma($rs->fields["BCWP_HRS"]);
        $a_hours   = formatNumber4decNoComma($rs->fields["ACWP_HRS"]);
        $bac_hours = formatNumber4decNoComma($rs->fields["BAC_HRS"]);
        $eac_hours = formatNumber4decNoComma($rs->fields["EAC_HRS"]);
        $pc        = formatNumber4decNoComma($rs->fields["PC_COMP"]);

        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$desc',
            $s,
            $p,
            $a,
            $bac,
            $eac,
            $s_hours,
            $p_hours,
            $a_hours,
            $bac_hours,
            $eac_hours,
            $pc),";
        if($i==500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,$schema);
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }
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
    //print $sql;
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
if($control=="bcr_valid_check"){

}
if($control=="data_check"){
    $baseline = "false";
    $bcr = "false";
    $table_array["p6_pcs_table_name"]      = $rpt_period . "_p6_bl_labor";

    $msg = "";
    foreach ($table_array as $key=>$table_name){
        $create_table = checkIfTableExists($schema, $table_name);
        if($create_table==true)
        {
            $sql = "select count(*) as count from $table_name where ship_code = $ship_code";
            //print $sql;
            $rs = dbCall($sql,$schema);
            $count = $rs->fields["count"];
            if($count<1){
                if(strpos($key, "cobra")>0){
                    $msg = "Please Load Cobra Data!";
                }else{
                    $msg = "Please Load P6 Data!";
                }
            }
        }
        else{
        $msg= "Please Load Data";
        }
    }
    die($msg);
}

