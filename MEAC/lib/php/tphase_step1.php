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
function returnCaseStageSQL($ship_code){
    getStageDates($ship_code);
    $ship_tage_dates_array = getStageDates($ship_code);

    $stage1_name = $ship_tage_dates_array[0]["stage"];
    $stage1_date = $ship_tage_dates_array[0]["date"];


    $stage2_name = $ship_tage_dates_array[1]["stage"];
    $stage2_date = $ship_tage_dates_array[1]["date"];

    $stage3_name = $ship_tage_dates_array[2]["stage"];
    $stage3_date = $ship_tage_dates_array[2]["date"];

    $stage4_name = $ship_tage_dates_array[3]["stage"];
    $stage4_date = $ship_tage_dates_array[3]["date"];

    $stage5_name = $ship_tage_dates_array[4]["stage"];
    $stage5_date = $ship_tage_dates_array[4]["date"];
    $sql = "case
              when date <  '$stage1_date' then 'Contract Award'
              when date BETWEEN '$stage1_date' and '$stage2_date' then '$stage1_name'
              when date BETWEEN '$stage2_date' and '$stage3_date' then '$stage2_name'
              when date BETWEEN '$stage3_date' and '$stage4_date' then '$stage3_name'
              when date BETWEEN '$stage4_date' and '$stage5_date' then '$stage4_name'
              when date > '$stage5_date' then '$stage5_name'
                else 'next level'
                end as stage
    ";
    return $sql;
}
function insertSWBSGLByRPTPeriod($ship_code, $case_sql, $rpt_period,$rpt_start_date, $rpt_end_date ){
    $sql = "
            select   
            ship_code,
            $rpt_period as rpt_period,
            case when CHAR_LENGTH(swbs) = 3 then concat(left(swbs,1),'00') 
              ELSE '000' end as swbs_group,
            swbs,
            sum(integr_amt)  as cost,
            $case_sql
            from meac.wp_gl_detail 
            where ship_code = $ship_code 
            and date BETWEEN '$rpt_start_date' and '$rpt_end_date' 
            and wp like '%matl%' 
            group by swbs";
    print $sql;
    print "<br>";
    $rs = dbCall($sql, "meac");
    while (!$rs->EOF)
    {

        $rpt_period = trim($rs->fields["rpt_period"]);
        $swbs_group = trim($rs->fields["swbs_group"]);
        $swbs       = trim($rs->fields["swbs"]);
        $ship_code  = trim($rs->fields["ship_code"]);
        $cost       = trim($rs->fields["cost"]);
        $stage      = trim($rs->fields["stage"]);

        $sql= "
        insert into tphase_step1 (rpt_period, swbs, ship_code, cost, stage, swbs_group)  VALUES ($rpt_period, $swbs, $ship_code, $cost, '$stage', $swbs_group)";
        $junk = dbCall($sql, "meac");

        $rs->MoveNext();
    }
    print $sql;
    print "<br>";
}
function insertTPhaseStep1($ship_code){
    $case_sql = returnCaseStageSQL($ship_code);
    $sql = "select rpt_period, month_end from fmm_evms.calendar  ORDER BY rpt_period ";
    $rs = dbCall($sql, "fmm_evms");
    $i=0;
    while (!$rs->EOF)
    {
        $rpt_period = $rs->fields["rpt_period"];
        $end_day  = $rs->fields["month_end"];

        if($i==0) {
            $rpt_start_date = "2011-01-01";
        }
        else{
            $prev_rpt_period = getPreviousRPTPeriod($rpt_period);
            $start_year       = intval(substr($prev_rpt_period, 0, 4));
            $start_month      = month2digit(substr($prev_rpt_period, -2));

            $start_day        = getMonthEndDay($prev_rpt_period);

            if($start_day<5){
                if($start_month==12){
                    $start_month = 0;
                    $start_year = $start_year+1;

                }
                $start_month = $start_month+1;
            }
            $rpt_start_date = "$start_year-$start_month-$start_day";
        }

        $end_year       = intval(substr($rpt_period, 0, 4));
        $end_month      = month2digit(substr($rpt_period, -2));

        if($end_day<5){
            if($end_month==12){
                $end_month = 0;
                $end_year = $end_year+1;

            }
            $end_month = $end_month+1;
        }

        $rpt_end_date = "$end_year-$end_month-$end_day";
        insertSWBSGLByRPTPeriod($ship_code, $case_sql, $rpt_period,$rpt_start_date, $rpt_end_date );
        $i++;
        $rs->MoveNext();
    }

}
function getStageDates($ship_code){
    $sql = "select stage, date from tphase_stage where ship_code = $ship_code";

    $rs = dbCall($sql, "meac");
    $ship_stage_array = array();
    $i= 0 ;
    while (!$rs->EOF)
    {
        $stage = trim($rs->fields["stage"]);
        $date  = trim($rs->fields["date"]);
        $ship_stage_array[$i]["stage"] = $stage;
        $ship_stage_array[$i]["date"] = $date;
        $i++;
        $rs->MoveNext();
    }
    return $ship_stage_array;
}
$array = array();
//$array[] = 465;
//$array[] = 467;
$array[] = 469;
//$array[] = 471;
//$array[] = 473;
//$array[] = 475;
//$array[] = 477;
//$array[] = 479;
//$array[] = 481;
//$array[] = 483;
//$array[] = 485;

foreach ($array as $ship_code){
    deleteFromTable("meac", "tphase_step1", "ship_code", $ship_code);
    insertTPhaseStep1($ship_code);
}


die("made it");
print time();