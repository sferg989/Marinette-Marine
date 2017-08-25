<?php
include("../../../inc/inc.php");
include('../../../inc/inc.cobra.php');
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$destination_schema = "z_meac";
$source_schema = "meac";

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
    correctShockOpenBuyItemShortage($ship_code);
}

$source_table = "wp_gl_detail";
$destination_table = "z_".$rpt_period."_".$source_table;
////duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);
truncateTable("meac", "wp_gl_detail");
insertGLdetailWITHWP();

$source_table = "wp_open_po";
$destination_table = "z_".$rpt_period."_".$source_table;
//duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);
////truncateTable("meac", "wp_open_po");
////insertOpenPOWithWP();

$source_table = "wp_committed_po";
$destination_table = "z_".$rpt_period."_".$source_table;
//duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);
////truncateTable("meac", "wp_committed_po");
////insertCommittedPOWP();

$source_table = "wp_open_buy";
$destination_table = "z_".$rpt_period."_".$source_table;
//duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);
////truncateTable("meac", "wp_open_buy");
////insertOpenBuyWithWP();

$source_table = "wp_ebom";
$destination_table = "z_".$rpt_period."_".$source_table;
//duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);
////truncateTable("meac", "wp_ebom");
//insertEBOMWP();

