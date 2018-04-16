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
$regression_interval = 10;
function dateDiffInDays($s, $e){
    $date1 = date_create($s);
    $date2 = date_create($e);
    $diff = date_diff($date1,$date2);

    return $diff->format("%a");
}
function getSWBSReEstEAC($swbs, $ship_code){
    $sql = "
            SELECT
                est.ship_code,
                est.wp,
                item,
                concat(right(left(wp, 6), 1), '00') swbs_group,
                sum(inflation_eac) eac
              FROM reest3 est
              WHERE est.ship_code = $ship_code
        and concat(right(left(wp, 6), 1), '00')  = $swbs
        and wp like '%matl%' AND est.item not in ('982-01-00001')
        group by ship_code, swbs_group
    ";
    print $sql;

    $rs = dbCall($sql,"meac");
    $eac = $rs->fields["eac"];
    return $eac;
}
function currentStage($rpt_period, $stage_dates){

    foreach ($stage_dates as $key=>$value){
        $stage = $value["stage"];
        $s_date  = $value["start_date"];
        $e_date  = $value["end_date"];
        $num_days_in_stage = dateDiffInDays($s_date, $e_date);
        $num_days_in_regression = intval($num_days_in_stage/$regression_interval);
        if($rpt_period>$s_date and $rpt_period<$e_date){
            print "THis is the STAge ".$stage."<br>";
            print $s_date."<br>";
            print $e_date."<br>";
            $days = dateDiffInDays($s_date, $e_date);
            print $days;
            break;
        }
    }
}
function splitDates($min, $max, $parts = 7, $output = "Y-m-d") {
    $dataCollection[] = date($output, strtotime($min));
    $diff = (strtotime($max) - strtotime($min)) / $parts;
    $convert = strtotime($min) + $diff;

    for ($i = 1; $i < $parts; $i++) {
        $dataCollection[] = date($output, $convert);
        $convert += $diff;
    }
    $dataCollection[] = date($output, strtotime($max));
    return $dataCollection;
}
function insertWeight($stage, $swbs_group, $interval, $weight,$ship_code){
    $sql = "insert into tphase_weight_v2 (stage, swbs_group, `interval`, weight, ship_code) values
        ('$stage', $swbs_group, $interval, $weight, $ship_code)
        ";
    $junk =dbCall($sql,"meac");
}
function getETCLEFT($ship_code){
    $sql = "SELECT
              swbs_group,
              sum(weight) as weight
            FROM tphase_weight_v2
            GROUP BY swbs_group";
    $rs = dbCall($sql,"meac");
    while (!$rs->EOF) {
        $swbs_group = intval($rs->fields["swbs_group"]);
        $weight     = formatNumber6decNoComma($rs->fields["weight"]);
        $diff = formatNumber6decNoComma((1- $weight)/10);
        for ($i = 1; $i <= 10; $i++) {
            insertWeight("Post Delivery", $swbs_group, $i, $diff,$ship_code);
        }

        $rs->MoveNext();
    }
}
function getCostByInterval($start, $end, $ship_code, $swbs_group){
    if($swbs_group =="000"){
        $swbs_wc = " and length(swbs) <= 2";
    }

    else{
        $swbs = substr($swbs_group, 0,1);
        print " this is the SWBS ".$swbs;
        $swbs_wc = " and left(swbs, 1) = $swbs ";
    }
    $sql = "
            SELECT
              s.swbs_group,
              s.amt
            FROM (
                   SELECT
                     ship_code,
                     concat(right(left(wp, 6), 1), '00') swbs_group,
                     sum(integr_amt)                     amt
                   FROM `201709_wp_gl_detail`
                   WHERE ship_code = $ship_code $swbs_wc AND date BETWEEN '$start' AND '$end') s
            GROUP BY ship_code, swbs_group";
    print $sql;
    $rs = dbCall($sql,"meac");
    $amt = $rs->fields["amt"];
    return $amt;
}
function createDateRangeFromArray($date_range){
    $i= 0;
    $date_between_array = array();
    for ($i=0;$i< count($date_range); $i++){
        $date_between_array[$i]["start"] = $date_range[$i];
        //$date_between_array[$i]["end"] = $date_range[$i];

    }
    reset($date_between_array);
    reset($date_range);
    for ($i=0;$i< count($date_range); $i++){
        //$date_between_array[$i]["start"] = $date_range[$i];
        $date_between_array[$i]["end"] = $date_range[$i+1];

    }
    /*
    foreach ($date_range as $key=>$value){
        if($i==0){
            $date_between_array[$i]["start"] = $date_range[$i];
            $i++;
            continue;
        }
        $date_between_array[$i]["end"] = $date_range[$i];
        if(isset($date_between_array[$i]["start"])==true){
            $i++;
            continue;
        }
        else{
            $date_between_array[$i]["start"] = $date_range[$i-1];
            $i++;
            continue;
        }
    }*/
    array_pop($date_between_array);
    return $date_between_array;

}



$stage_dates = getStageDates("469");
//array_debug($stage_dates);
$swbs_array = array();
/*$swbs_array[] = "000";
$swbs_array[] = "100";
$swbs_array[] = "200";
$swbs_array[] = "300";
$swbs_array[] = "400";
$swbs_array[] = "500";
$swbs_array[] = "600";
$swbs_array[] = "700";
$swbs_array[] = "800";*/
$swbs_array[] = "900";
$ship_code = "469";
//truncateTable("meac", "tphase_weight_v2");
deleteFromTable("meac", "tphase_weight_v2", "ship_code", $ship_code);
foreach ($swbs_array as $swbs){
    $total_eac_swbs = getSWBSReEstEAC($swbs, $ship_code);

    print $total_eac_swbs;

    foreach ($stage_dates as $key=>$value){
        $stage                  = $value["stage"];
        $s_date                 = $value["start_date"];
        $e_date                 = $value["end_date"];

        if($s_date =="0000-00-00")
        {
            $s_date = "2000-04-01";
        }
        if($e_date =="0000-00-00")
        {
            $e_date = "2017-12-30";
        }
        print $s_date."<br>";
        print $e_date."<br>";

        $date_range = splitDates($s_date, $e_date, 10);

        $between_date_array = createDateRangeFromArray($date_range);
        array_debug($between_date_array);

        $i = 1;
        foreach ($between_date_array as $key=>$value){

            $start = $value["start"];
            $end   = $value["end"];
            $swbs_total_for_stage_and_interval = getCostByInterval($start, $end, $ship_code, $swbs);
            $weight = formatNumber6decNoComma($swbs_total_for_stage_and_interval/$total_eac_swbs);
            insertWeight($stage, $swbs, $i, $weight, $ship_code);
            print "THis is part SWBS GROUP $swbs <br><br>";
            print "THis is part $i  for dates $start and End $end and this is the cost $swbs_total_for_stage_and_interval";

            print "<br><br><br><br><br>";
            $i++;
        }
        //array_debug($between_date_array);
        //getCostByInterval($date_range);
        //var_dump($between_date_array);
    }

}

getETCLEFT($ship_code);


die();

/*
 * get number of days in the satge
 * */



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
