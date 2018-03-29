<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.insert_data.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.baan.fortis.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\functiobuilder.php');
session_write_close();
/*
 * Version 4 is fixing hard coded periods
 * gl doc field is wrong
 * the po number has been inaccuracte
 *
 * */
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$rpt_period = 201711;
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);

//$array[] = 465;
//$array[] = 467;
//$array[] = 469;
//$array[] = 471;
//$array[] = 473;
//$array[] = 475;
//$array[] = 477;
//$array[] = 479;
//$array[] = 481;
//$array[] = 483;
$array[] = 485;


//clearDirectory($path2directory);

foreach ($array as $ship_code){
    buildMEACFile($ship_code, $prev_rpt_period,$rpt_period);
}


print time();