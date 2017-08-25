<?php
include("../../../inc/inc.php");
include("../../../inc/inc.PHPExcel.php");
include("inc.insert_data.php");
include("inc.baan.fortis.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$ship_code ="0483";

function loaditem2buyer(){
    truncateTable("meac", "item2buyer");
    $sql = "
            insert into 
            item2buyer (item, buyer) 
            (select item, buyer from buyer_reponsible br
              left JOIN  master_buyer mb
                  on br.buyer_id = mb.id
              where br.buyer_id <> 0
                group by item );
    ";
    $junk = dbCall($sql, "meac");
}
truncateTable("meac", "master_buyer");
loadBaanBuyerIDList();

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

foreach ($array as $value){
    deleteFromTable("MEAC", "buyer_reponsible", "ship_code", $value);
    loadResponsibleBuyer($value);
}

loaditem2buyer();
