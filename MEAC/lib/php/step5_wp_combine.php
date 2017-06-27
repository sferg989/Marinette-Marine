<?php
include("../../../inc/inc.php");
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$files = array();
truncateTable("meac", "wp_gl_detail");
truncateTable("meac", "wp_open_po");
truncateTable("meac", "wp_committed_po");
truncateTable("meac", "wp_open_buy");
truncateTable("meac", "wp_ebom");
truncateTable("meac", "swbs_gl_summary");

insertGLdetailWITHWP();
insertOpenPOWithWP();
insertCommittedPOWP();
insertOpenBuyWithWP();
insertEBOMWP();
$array = array();
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
    insertSWBSSummary($value);
}

/*
$rpt_period = 201705;
$table_name   = $rpt_period . "_meac";
$create_table = checkIfTableExists($schema, $table_name);
if($create_table== "create_table"){
    createTableFromBase("meac","template_meac", $table_name);
}
truncateTable("meac", $table_name);
insertMEACDataNoCommodity($table_name);*/