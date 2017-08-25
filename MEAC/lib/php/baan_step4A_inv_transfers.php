<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/18/2017
 * Time: 3:12 PM
 */
include("../../../inc/inc.php");
include("inc.baan.fortis.php");
function loadINVTranserfers($ship_code){

    $sql = "
        select 
            t_cprj,
            t_item,
            t_orno,
            t_quan 
         from ttipcs700490 
         where t_cprj like '%$ship_code%' and t_pono  = 0
         ";
    $rs = dbCallBaan($sql);
    $insert_sql = "insert into inv_transfers (ship_code, item, `order`, qty) values";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF) {
        $ship_code = intval($rs->fields["t_cprj"]);
        $item      = trim($rs->fields["t_item"]);
        $order     = intval($rs->fields["t_orno"]);
        $qty       = formatNumber4decNoComma($rs->fields["t_quan"]);

        $sql.=" ($ship_code, '$item', $order, $qty),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}

$array = array();
$array[] = 465;
$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;

foreach ($array as $ship_code){
    deleteFromTable("meac", "inv_transfers", "ship_code", $ship_code);
    loadINVTranserfers($ship_code);
}
