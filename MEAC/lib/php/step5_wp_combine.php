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
//truncateTable("meac", "wp_gl_detail");
//truncateTable("meac", "wp_open_po");
//truncateTable("meac", "wp_open_buy");
//truncateTable("meac", "wp_ebom");
//truncateTable("meac", "wp_committed_po");
//truncateTable("meac", "swbs_gl_summary");

//insertGLdetailWITHWP();
//insertOpenPOWithWP();
//insertOpenBuyWithWP();
//insertEBOMWP();
//insertCommittedPOWP();
insertSWBSSummary(483);

/*
$rpt_period = 201705;
$table_name   = $rpt_period . "_meac";
$create_table = checkIfTableExists($schema, $table_name);
if($create_table== "create_table"){
    createTableFromBase("meac","template_meac", $table_name);
}
truncateTable("meac", $table_name);
insertMEACDataNoCommodity($table_name);*/