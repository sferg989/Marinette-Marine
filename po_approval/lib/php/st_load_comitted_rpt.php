<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.baan.fortis.php');
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
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
    deleteFromTable("meac", "wp_baan_committed_po", "ship_code", $value);
    loadBaanCommittedPO($value);
    print "This is the hull".$value;
}

truncateTable("meac", "po_data");
loadFortisPOData();