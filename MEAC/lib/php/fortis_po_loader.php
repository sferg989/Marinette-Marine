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
//$ship_code ="0481";


//deleteFromTable("meac", "po_data", "ship_code", $value);
//duplicateTable("po_data", "meac", "z_po_data20170811", "z_meac");
truncateTable("meac", "po_data");
loadFortisPOData();



die("made it");
print time();