<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/11/2017
 * Time: 8:23 AM
 */

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
$ship_array = array();
$ship_array[] = "0465";
$ship_array[] = "0467";
$ship_array[] = "0469";
$ship_array[] = "0471";
$ship_array[] = "0473";
$ship_array[] = "0475";
$ship_array[] = "0477";
$ship_array[] = "0479";
$ship_array[] = "0481";
$ship_array[] = "0483";
$ship_array[] = "0485";
foreach($ship_array as $value){
    deleteFromTable("meac", "change_item", "ship_code", $value);
    loadEFDBChangeBAAN($value);
}


die("made it");
print time();