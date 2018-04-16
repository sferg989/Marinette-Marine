<?php
include("../../../inc/inc.php");
include('../../../inc/inc.PHPExcel.php');
include('../../../inc/inc.cobra.php');
include("inc.insert_data.php");
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
    deleteFromTable("MEAC", "swbs_gl_summary_stage", "ship_code", $value);
    insertSWBSSummaryStaging($value);
}
foreach ($array as $value){
    deleteFromTable("MEAC", "swbs_gl_summary", "ship_code", $value);
    insertSWBSSummary($value);
}
deleteFromTableNotLike("MEAC", "swbs_gl_summary", "wp", "matl");