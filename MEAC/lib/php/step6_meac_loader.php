<?php
include("../../../inc/inc.php");
include("inc.insert_data.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
/*
 *objecttive - compile all the tables previously loaded inorder to make the reporting of the MEAC easier.
 *-- deal with WP that are not listed in the CBM, and they need to be defaulted to the Commondity.
 *
 *
 *  Step 1 loop through everthing that is not a commodity
 *
 * */
$rpt_period = 201705;
$table_name   = $rpt_period . "_meac";
$create_table = checkIfTableExists($schema, $table_name);
if($create_table== "create_table"){
    createTableFromBase("meac","template_meac", $table_name);
}
truncateTable("meac", $table_name);
insertMEACDataNoCommodity($table_name);
