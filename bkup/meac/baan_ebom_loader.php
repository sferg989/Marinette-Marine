<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/18/2017
 * Time: 3:12 PM
 */
include("../../../inc/inc.php");
include("inc.insert_data.php");
include("inc.baan.fortis.php");

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
    deleteFromTable("meac", "wp_baan_ebom", "ship_code", $ship_code);
    loadBaanEbom($ship_code);

}

