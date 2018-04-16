<?php
include('../../../inc/inc.php');
include('../../../inc/inc.cobra.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
session_write_close();
//$user = $_SESSION["user_name"];
//$rpt_period      = currentRPTPeriod();
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
$data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);
$prev_year_last2    = $data["prev_year_last2"];
$prev_month         = $data["prev_month"];

function getPPmAPID($ship_code){
    $sql = "
        select proj_id 
        from PROJECT 
        where proj_short_name like '%".$ship_code."_IMS_Status_Cur%' 
        and delete_date is NULL 
        and proj_short_name not like '%cur-1%'";
    $rs = dbCallP6($sql);
    $ppm_bl_id = $rs->fields["proj_id"];
    //die();
    return $ppm_bl_id;

}
function getCobraLaborWPEACHours($ship_code){
    if(strlen($ship_code)==3)
    {
        $ship_code = "0".$ship_code;
    }
    $cobra_hours_array = array();
    $sql ="
        SELECT
          wp wp,
          sum(HOURS) eac_hours
        FROM tphase t LEFT JOIN CAWP p
            ON t.PROGRAM = p.PROGRAM AND t.CAWPID = p.CAWPID
        WHERE t.PROGRAM = '$ship_code'
        and p.wp not like '%matl%' 
        -- and p.wp not like '%sub-%'
              AND CLASS IN ('Actual', 'EA', 'CA', 'forecast')
        GROUP BY t.PROGRAM, p.WP";
    $rs = dbCallCobra($sql);
    while (!$rs->EOF)
    {
        $wp                     = $rs->fields["wp"];
        $eac_hours              = $rs->fields["eac_hours"];
        $cobra_hours_array[$wp] = $eac_hours;
        $rs->MoveNext();
    }
    return $cobra_hours_array;
}
function getCobraMaterialWPEACDollars($ship_code){
    if(strlen($ship_code)==3)
    {
        $ship_code = "0".$ship_code;
    }
    $cobra_dollars_array = array();
    $sql = "
        select
            wp,
            (sum(DIRECT) +
            sum(GANDA)+
            sum(SYSGA) +
            sum(ODCNOGA)) eac_d
        from tphase t left join CAWP p
        on t.PROGRAM = p.PROGRAM and t.CAWPID = p.CAWPID
        where t.PROGRAM = '$ship_code'
        and CLASS in ('Actual', 'EA', 'CA', 'forecast')
          and (wp like '%matl%' or wp like '%odc%')
    group by t.PROGRAM, p.WP
    ";
    $rs = dbCallCobra($sql);
    while (!$rs->EOF)
    {
        $wp                     = $rs->fields["wp"];
        $eac_d                  = $rs->fields["eac_d"];
        $cobra_dollars_array[$wp] = $eac_d;
        $rs->MoveNext();
    }
    return $cobra_dollars_array;
}
function getP6EACHours($ppm_ap_id,$wp){
    $sql= "
        SELECT
          task_code,
          act_work_qty a,
          remain_work_qty etc
        FROM task
        WHERE proj_id = $ppm_ap_id AND task_code = '$wp'
        and delete_date is null
        ";
    $rs             = dbCallP6($sql);
    $p6_a_hours     = $rs->fields["a"];
    $p6_etc_hours   = $rs->fields["etc"];
    $eac_hours      = formatNumber4decNoComma($p6_a_hours + $p6_etc_hours);
    return $eac_hours;
}
function getP6MatlACWP($ppm_ap_id, $wp){
    $sql = "
        SELECT
          tr.act_this_per_cost acwp
        FROM TASKRSRC tr LEFT JOIN task t
            ON t.proj_id = tr.proj_id
               AND t.task_id = tr.task_id
          WHERE t.proj_id = $ppm_ap_id
              AND t.delete_date IS NULL
              AND t.task_code = '$wp'
      ";
    $rs = dbCallP6($sql);
    $acwp = $rs->fields["acwp"];
    return $acwp;
}
function getP6MatlEAC($ppm_ap_id, $wp){
    $sql = "
        SELECT
          (tr.remain_cost + tr.act_this_per_cost) eac
        
        FROM TASKRSRC tr LEFT JOIN task t
            ON t.proj_id = tr.proj_id
               AND t.task_id = tr.task_id
        WHERE t.proj_id = $ppm_ap_id
              AND t.task_code = '$wp';
      ";
    $rs = dbCallP6($sql);
    $eac = $rs->fields["eac"];
    return $eac;
}
function getCobraTPHoursByMonth($ship_code,$rpt_period, $wp){
    $prev_rpt_period = getPreviousRPTPeriod($rpt_period);

    $year            = intval(substr($rpt_period, 0, 4));
    $month           = month2digit(substr($rpt_period, -2));
    $day             = getMonthEndDay($rpt_period);
    if($day<5){
        $month = $month+1;
    }
    $prev_rpt_year  = intval(substr($prev_rpt_period, 0, 4));
    $prev_rpt_month = month2digit(substr($prev_rpt_period, -2));
    $prev_rpt_day   = getMonthEndDay($prev_rpt_period);

    if($prev_rpt_day<5){
        $prev_rpt_month = $prev_rpt_month+1;
    }

    if(strlen($ship_code)==3)
    {
        $ship_code = "0".$ship_code;
    }
    $sql = "
        SELECT
          t.program,
          wp,
          sum(hours) hours
        FROM tphase t
          left join CAWP c
          on t.PROGRAM = c.PROGRAM
          and t.CAWPID = c.CAWPID
        WHERE t.PROGRAM ='$ship_code'
              AND class = 'forecast'
              AND DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year'
              AND DF_DATE <= '$month-$day-$year'
        AND wp = '$wp'
        group by t.program, wp";
    $rs= dbCallCobra($sql);
    $hours = $rs->fields["hours"];
    return $hours;
}

if($control =="labor_eac_compare"){

    $ppm_ap_id = getPPmAPID($ship_code);
    $ppm_ap_id = 108247;

    $cobra_eac_hours_array = getCobraLaborWPEACHours($ship_code);
    //array_debug($cobra_eac_hours_array);
    foreach ($cobra_eac_hours_array as $wp=>$cobra_eac_hour){
        $p6_eac_hour = getP6EACHours($ppm_ap_id,$wp);
        $diff = formatNumber4decNoComma($p6_eac_hour-$cobra_eac_hour);
        //print "THIS IS the p6 val $p6_eac_hour";
        if(abs($diff) >1 ){
            print "tjhis is the WP $wp and this is the diff $diff <br> ";
            print "p6 $p6_eac_hour Cobra  $cobra_eac_hour<br> ";
        }
    }
}
if($control =="a_compare"){

    $ppm_ap_id = getPPmAPID($ship_code);
    //$ppm_ap_id = 107825;

    $a_array   = getTotalActualsForMATLWP($ship_code);
    //array_debug($a_array);
    foreach ($a_array as $wp=>$acwp){
        $p6_acwp = getP6MatlACWP($ppm_ap_id, $wp);
        $diff = formatNumber4decNoComma($p6_acwp-$acwp);
        //print " $wp - $acwp P6 - $p6_acwp <br>";
        if($diff !=0 ){
            print "tjhis is the WP $wp and this is the diff $diff <br> ";
            print "p6 $p6_acwp Cobra  $acwp<br> ";
        }
    }
    die($ppm_ap_id);
}
if($control =="matl_eac_dollar"){
    $ppm_ap_id = getPPmAPID($ship_code);
    //$ppm_ap_id = 107668;
    $ppm_ap_id = 107668;

    $eac_array   = getCobraMaterialWPEACDollars($ship_code);
    //array_debug($eac_array);
    foreach ($eac_array as $wp=>$eac){
        $p6_eac = getP6MatlEAC($ppm_ap_id, $wp);
        $diff = formatNumber4decNoComma($p6_eac-$eac);
        //print " $wp - $eac P6 - $p6_eac <br>";
        if($diff !=0 ){
            print "tjhis is the WP $wp and this is the diff $diff <br> ";
            print "p6 $p6_eac Cobra  $eac<br> ";
        }
    }
    die($ppm_ap_id);
}
if($control =="tp_labor"){
    $sql = "
      select 
      wp,
      val, 
      date
      from ".$rpt_period."_p6_tp_check 
      where wp not like '%matl%'
      ";
    $rs = dbCall($sql, "status_validation");
    while (!$rs->EOF)
    {
        $wp         = $rs->fields["wp"];
        $p6_val     = $rs->fields["val"];
        $rpt_period = trim($rs->fields["date"]);

        $cobra_val = getCobraTPHoursByMonth($ship_code,$rpt_period, $wp);
        $diff = intval($cobra_val-$p6_val);
        if($diff !=0 ){
            print "tjhis is the WP $wp and this is the diff $diff <br> ";
            print "p6 $p6_val Cobra  $cobra_val<br> ";

        }
        $rs->MoveNext();
    }
    die("made it");
}