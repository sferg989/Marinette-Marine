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
print time();
/*
 * 1.  Check if it is a WE.
 * 2.  Check if it is a month end.
 * 
 *
 * */

$me= false;
$we = false;

$we = date("N");
/*numeric representation of day of Week
Monday = 1 and sunday = 7*/
if($we_day ==6){
    $we = true;
}
$date = date("");

if($day<5){
    $month = $month+1;
}
$source_table       = "z_20170731";
$source_schema      = "open_buy";
$destination_table  = "z_201707_me";
$destination_schema = "open_buy";

//duplicateTable($source_table, $source_schema, $destination_table,$destination_schema);

$source_table       = "z_20170731";
$source_schema      = "open_buy";
$destination_table  = "z_20170729_we";
$destination_schema = "open_buy";

//duplicateTable($source_table, $source_schema, $destination_table,$destination_schema);
deleteFromTable("meac", "baan_open_buy", "ship_code", $ship_code);
insertOpenBuyReport($ship_code);
die("made it");
print time();