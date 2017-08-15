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

deleteFromTable("meac", "open_po", "ship_code", $ship_code);
insertOpenPOReport($ship_code);
die("made it");
print time();