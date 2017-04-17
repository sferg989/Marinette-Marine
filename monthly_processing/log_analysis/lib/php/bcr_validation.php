<?php
include("../../../../inc/inc.php");
include("../../../../inc/inc.cobra.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;

if(strlen($ship_code)==3)
{
    $ship_code = "0".$ship_code;
}
function getMonthEndDay($rpt_period){
    $sql = "select month_end from calendar where rpt_period = $rpt_period";
    $rs  = dbCall($sql, "fmm_evms");

    $day = $rs->fields["month_end"];
    return $day;
}


if($control=="load_cobra_data"){
    $schema = "cost2";
    insertCobraCostData($ship_code, $schema, $rpt_period);

    $table_name   = $rpt_period . "_bcr";
    $create_table = checkIfTableExists("bcr_log", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("bcr_log","template_bcr", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "bcr_log");
    loadCOBRABCRLOG($ship_code, $rpt_period, $table_name);

    $schema = "lcs_log";
    $table_name   = $rpt_period . "_ship";
    $create_table = checkIfTableExists($schema, $table_name);

    if($create_table== "create_table"){
        createTableFromBase($schema,"template_ship", $table_name);
    }

    deleteShipFromTable($ship_code,$table_name, $schema);
    loadLCSProgramData($ship_code, $table_name);


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
