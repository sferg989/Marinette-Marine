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
function currentStage($rpt_period, $stage_dates){

    foreach ($stage_dates as $key=>$value){
        $stage = $value["stage"];
        $date  = $value["date"];
        print "THIS IS TH STAGE ".$stage." and date".$date." <br>";
        if($rpt_period<$date){
            print "THE DATE $rpt_period is  LESSthen $date";
            print "THis is the STAge ".$stage;
            break;
        }
    }
}
$stage_dates = getStageDates("0481");
$time = strtotime('07/15/2017');

$rpt_period = date('Y-m-d', $time);

$next_rpt = getNextRPTPeriod($rpt_period);

//var_dump($stage_dates);
currentStage($rpt_period, $stage_dates);

$date1 = new DateTime("2009-09-01");
$date2 = new DateTime("2010-05-01");

$interval = date_diff($date1, $date2);
echo $interval->m + ($interval->y * 12) . ' months';

die("made it");
