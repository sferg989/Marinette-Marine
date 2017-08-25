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
$ship_code ="0479";
$array = array();
$array[] = 465;
$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
//$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;

foreach ($array as $ship_code){
    deleteFromTable("meac", "wp_baan_open_po", "ship_code", $ship_code);
    insertOpenPOReport($ship_code);
}


die("made it");
print time();