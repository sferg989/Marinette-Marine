<?php
include('../../../inc/inc.php');
include('../../../inc/inc.cobra.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
session_write_close();
//$user = $_SESSION["user_name"];
$rpt_period      = currentRPTPeriod();
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
$data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);
$prev_year_last2    = $data["prev_year_last2"];
$prev_month         = $data["prev_month"];
function getP6CalendarIDs($ppm_bl_id){
    $clndr_id_array = array();
    $sql = "select clndr_id from task where proj_id = $ppm_bl_id and task_type <> 'TT_Mile' group by clndr_id";
    $rs = dbCallP6($sql);
    while (!$rs->EOF)
    {
        $clndr_id = $rs->fields["clndr_id"];
        $clndr_id_array[] = $clndr_id;
        $rs->MoveNext();
    }
    return $clndr_id_array;
}

function  calcTpCurveLaborUnitsperDay($total_hours, $total_labor_units, $hours_per_day,$tp_weight=.05){
    if($tp_weight> 1){
        $tp_weight = formatNumber6decNoComma($tp_weight/100);
    }
    $total_labor_weight          = formatNumber6decNoComma($total_labor_units * $tp_weight);
    $five_percent_duration_hours = formatNumber6decNoComma($total_hours * .05);
    $five_percent_duration_days  = formatNumber6decNoComma($five_percent_duration_hours / $hours_per_day);
    $labor_units_per_day         = formatNumber6decNoComma($total_labor_weight / $five_percent_duration_days);
    return $labor_units_per_day;
}
function getPPmBlID($ship_code){
    $sql = "select proj_id from PROJECT where proj_short_name like '%".$ship_code."_IMS_Base_Cur%'";
    $rs = dbCallP6($sql);
    $ppm_bl_id = $rs->fields["proj_id"];

    $sql = "select proj_id from PROJECT where proj_short_name like '%".$ship_code."_IMS_Base_Cur'";
    $rs = dbCallP6($sql);
    $ppm_bl_id = $rs->fields["proj_id"];
    print $ppm_bl_id;
    if($ppm_bl_id==""){

        die("DID NOT FIND THE PROJECT IN P6!");
    }
    return $ppm_bl_id;

}
function getPPmAPID($ship_code){
    $sql = "select proj_id from PROJECT where proj_short_name like '%".$ship_code."_IMS_Status_Cur%'";
    $rs = dbCallP6($sql);
    $ppm_bl_id = $rs->fields["proj_id"];
    //die();
    return $ppm_bl_id;

}
function loadCalendarP6X($clndr_id){

}
function loadP6Calendar($ship_code){
    $ppm_bl_id = getPPmBlID($ship_code);
    $clndr_ids =getP6CalendarIDs($ppm_bl_id);
    foreach ($clndr_ids as $clndr_id){
        loadCalendarP6X($clndr_id);
    }
}
function checkP6Hours($ppm_bl_id, $wp){
    $sql = "select target_work_qty from TASK t WHERE proj_id = $ppm_bl_id AND task_code = '$wp' and delete_date is null";
    $rs = dbCallP6($sql);
    $bac_hours = $rs->fields["target_work_qty"];
    return $bac_hours;
}
function getCurveIdsArray($ppm_bl_id){
    $sql = "select curv_id from TASKRSRC where proj_id =$ppm_bl_id and  curv_id is NOT NULL group by curv_id";
    $rs = dbCallP6($sql);
    $curve_array = array();
    while (!$rs->EOF)
    {
        $curv_id = $rs->fields["curv_id"];
        $curve_array[] = $curv_id;
        $rs->MoveNext();
    }
    return $curve_array;
}
function loadP6RSRCWeight($ppm_bl_id){
    $curve_array = getCurveIdsArray($ppm_bl_id);
    //array_debug($curve_array);
    $stage_array = array();
    $stage_array[] = 0;
    $stage_array[] = 5;
    $stage_array[] = 10;
    $stage_array[] = 15;
    $stage_array[] = 20;
    $stage_array[] = 25;
    $stage_array[] = 30;
    $stage_array[] = 35;
    $stage_array[] = 40;
    $stage_array[] = 45;
    $stage_array[] = 50;
    $stage_array[] = 55;
    $stage_array[] = 60;
    $stage_array[] = 65;
    $stage_array[] = 70;
    $stage_array[] = 75;
    $stage_array[] = 80;
    $stage_array[] = 85;
    $stage_array[] = 90;
    $stage_array[] = 95;
    $stage_array[] = 100;
    foreach ($curve_array as $curve_id){
        deleteFromTable("bl_validation", "p6_rsrc_curve", "curve_id", $curve_id);
        $sql = "";
        foreach ($stage_array as $stage){
            $sql.= "
        select 
          $stage duration,
          value".$stage."  weight
          from RSRCCURVX x
        where curv_id = $curve_id
        UNION all";
        }
        $sql = substr($sql,0,-9);
        $insert_sql = "insert into p6_rsrc_curve (curve_id, duration, weight) values";
        $rs = dbCallP6($sql);
        $sql = $insert_sql;
        $i= 0 ;
        while (!$rs->EOF)
        {

            $duration = $rs->fields["duration"];
            $weight   = ($rs->fields["weight"]);

            $sql.="($curve_id, $duration, $weight),";
            $rs->MoveNext();
        }
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "bl_validation");
    }
}

function getCobraP6LaborDifferencesArray($ship_code){
    $ppm_bl_id = getPPmBlID($ship_code);

    $wc = "";
    if($ship_code>=477 and $ship_code<=485){
        $wc = " and wp not like '%matl%'";
    }
    if(strlen($ship_code)==3)
    {
        $program = "0".$ship_code;
    }

    $sql = "select wp, BAC_HRS from CAWP where PROGRAM = '$program' $wc and wp > '' and DESCRIP not like '%for History%'";
    $rs = dbCallCobra($sql);
    $i= 0 ;
    $diff_array = array();
    while (!$rs->EOF)
    {
        $wp       = $rs->fields["wp"];
        $bac_hrs  = $rs->fields["BAC_HRS"];
        $p6_hours = checkP6Hours($ppm_bl_id, $wp);
        $diff     = intval($p6_hours - $bac_hrs);
        if($diff <> 0 ){
            $diff_array[$i]["wp"]    = $wp;
            $diff_array[$i]["cobra"] = $bac_hrs;
            $diff_array[$i]["p6"]    = $p6_hours;
            $diff_array[$i]["diff"]  = $diff;
            $i++;
        }
        $rs->MoveNext();
    }
    return $diff_array;
}
function loadRsrcStraightRate($ship_code,$ppm_bl_id){
    $insert_sql = "insert INTO p6_tp (ship_code, wp, val, date) VALUES ";

    /*
     * RATE of Budgeted Labor units consumed per day, for all resources that are not on a
     * RSRC CURV.
     * for example. if I have target work qty per hour of .5.  Multiply
     * .5 * number of total workhours.  = number of units consumed per day.
     * */

    $sql = " SELECT
         task_code,
         CONVERT(VARCHAR(10), c.daydate, 120) daydate,
         sum(target_qty_per_hr * c.totalworkhours) val
       FROM (
              SELECT
                task_id,
                target_qty,
                target_qty_per_hr,
                (SELECT t.clndr_id
                 FROM TASK t
                 WHERE t.task_id = r.task_id) AS cal_id,
                (SELECT early_start_date
                 FROM TASK t
                 WHERE t.task_id = r.task_id) AS start,
                (SELECT task_code
                 FROM TASK t
                 WHERE t.task_id = r.task_id) AS task_code,
                (SELECT early_end_date
                 FROM TASK t
                 WHERE t.task_id = r.task_id)    end_date
              FROM TASKRSRC r

              WHERE proj_id = $ppm_bl_id and delete_date is null and curv_id is null) sub
         LEFT JOIN CALENDARX c ON c.clndr_id = sub.cal_id
       WHERE
         workdayflag = 'Y' AND daydate >= start AND daydate <= end_date
       GROUP BY task_code,c.daydate ORDER BY task_code,c.daydate
";
    $rs = dbCallP6($sql);
    $sql = $insert_sql;
    $i= 0 ;
    while (!$rs->EOF)
    {

        $wp      = $rs->fields["task_code"];
        $daydate = ($rs->fields["daydate"]);
        $val     = $rs->fields["val"];

        $sql.="($ship_code, '$wp', '$val', '$daydate'),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "bl_validation");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!= 500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "bl_validation");
        $i=0;
        //clear out the sql stmt.
        $sql = $insert_sql;
    }
}
function getStageTPWeight($curve_id){
    $stage_array = array();
    $sql = "select weight from p6_rsrc_curve where curve_id = $curve_id order by duration";
    $rs = dbCall($sql, "bl_validation");
    $count = $rs->RecordCount();
    if($count==0){
        for($i=1 ; $i<=20; $i++){
            $stage_array[$i] = 5;
        }
    }
    $i= 0;
    while (!$rs->EOF)
    {
        $weight          = $rs->fields["weight"];
        $stage_array[$i] = $weight;
        $i++;
        $rs->MoveNext();
    }
    return $stage_array;
}
function loadRsrcCruveRate($ship_code, $ppm_bl_id){
    /*
     * 1.  Get total hours for each task.
     * 2.  how many hours are in 5% of duration.
     * 3.  how many labor units consumed in 5% of duration.
     *   weight * (total hours).
     * 4.  How may days are in 5% duration.
     * 5.  how many labor units per day.
     * 104794
     * */
    //loadP6BLUnits($ship_code);
    /*Loop through all the WP that have a curve ID*/
    $sql = "select t.task_code,
              t.task_id task_id,
              r.taskrsrc_id rsrc_id,
              r.curv_id curve_id,
              t.target_work_qty labor_units,
              t.target_drtn_hr_cnt total_hours
            from task t left join
              TASKRSRC r ON t.task_id = r.task_id
            
            where t.proj_id =$ppm_bl_id and t.task_code = 'QCX'
            and r.curv_id is not null
            and r.delete_date is NULL and t.delete_date is NULL
    ";
    $rs = dbCallP6($sql);
    while (!$rs->EOF)
    {

        $task_code   = $rs->fields["task_code"];
        $total_hours = $rs->fields["total_hours"];
        $labor_units = $rs->fields["labor_units"];
        $curve_id    = $rs->fields["curve_id"];
        $rsrc_id     = $rs->fields["rsrc_id"];
        $task_id     = $rs->fields["task_id"];
        $insert_sql  = "INSERT INTO p6_tp (ship_code, wp, val, date) VALUES ";

        $days_in5_pc = round((($total_hours * .05) / 8));
        //print $days_in5_pc."This is days in 5%"."<br>"."<br>"."<br>";
        $tp_weight_array = getStageTPWeight($curve_id);
        $sql = "   
             select
              task_code,
              daydate,
              val
            
            from (
            SELECT
                 task_code,
                taskrsrc_id rsrc_id,
                     CONVERT(VARCHAR(10), c.daydate, 120) daydate,
                     sum(c.totalworkhours) val
                   FROM (
                          SELECT
                            task_id,
                            taskrsrc_id,
                            (SELECT t.clndr_id
                             FROM TASK t
                             WHERE t.task_id = r.task_id) AS cal_id,
            
                            (SELECT early_start_date
                             FROM TASK t
                             WHERE t.task_id = r.task_id) AS start,
                            (SELECT task_code
                             FROM TASK t
                             WHERE t.task_id = r.task_id) AS task_code,
                            (SELECT early_end_date
                             FROM TASK t
                             WHERE t.task_id = r.task_id)    end_date
                          FROM TASKRSRC r
            
                          WHERE proj_id = $ppm_bl_id and task_id = $task_id and taskrsrc_id = $rsrc_id and delete_date is NULL and curv_id is not null
                        ) sub
                     LEFT JOIN CALENDARX c ON c.clndr_id = sub.cal_id
                   WHERE
                     workdayflag = 'Y' AND daydate >= start AND daydate <= end_date
                   GROUP BY task_code,taskrsrc_id,c.daydate) sub2
            ";
        //print $sql;
        $rs_task = dbCallP6($sql);
        $sql = $insert_sql;
        $day_counter= 0;
        $stage_counter= 1;
        while (!$rs_task->EOF)
        {

            $daydate       = $rs_task->fields["daydate"];
            $hours_per_day = $rs_task->fields["val"];
            $tp_weight     = $tp_weight_array[$stage_counter];
            if($day_counter%$days_in5_pc){
                //nextdays is same stage
                $lu_per_day = calcTpCurveLaborUnitsperDay($total_hours, $labor_units, $hours_per_day,$tp_weight);
            }else{
                //nextday is the next 5% weight.
                $stage_counter++;
                $tp_weight = $tp_weight_array[$stage_counter];

                $lu_per_day = calcTpCurveLaborUnitsperDay($total_hours, $labor_units, $hours_per_day,$tp_weight);

                //echo "$tp_weight  This is the next stage";
            }
            $sql .="($ship_code, '$task_code', $lu_per_day, '$daydate'),";
            $day_counter++;
            $rs_task->MoveNext();
        }
        //print "this is the number of days " .$day_counter."<br>"."<br>"."<br>";
        //print "this is the stage " .$stage_counter;
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "bl_validation");
        //print $sql;
        $rs->MoveNext();
    }
}
function loadP6BLTimePhase($ship_code){
    $ppm_bl_id      = getPPmBlID($ship_code);

    deleteFromTable("bl_validation", "p6_tp","ship_code", $ship_code);
    //loadRsrcStraightRate($ship_code,$ppm_bl_id);
    loadRsrcCruveRate($ship_code, $ppm_bl_id);
}
function loadP6BLUnits($ship_code){
    deleteFromTable("bl_validation", "p6_bl_units","ship_code", $ship_code);
    $insert_sql = "insert into p6_bl_units (ship_code, wp, bl_units, start, finish)  VALUES ";
    $ppm_bl_id      = getPPmBlID($ship_code);

    $sql = " 
        SELECT
          proj_id,
          task_code,
          target_work_qty bl_units,
          early_start_date start,
          early_end_date finish
        FROM TASK t
        WHERE proj_id = $ppm_bl_id and task_code not like '%IMP%'
        and 
";
    //print $sql;
    $rs = dbCallP6($sql);
    $sql = $insert_sql;
    $i= 0 ;
    while (!$rs->EOF)
    {

        $wp       = $rs->fields["task_code"];
        $start    = $rs->fields["start"];
        $finish   = $rs->fields["finish"];
        $bl_units = $rs->fields["bl_units"];

        $sql.="($ship_code, '$wp', $bl_units, '$start', '$finish'),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "bl_validation");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }

    if($i!= 500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "bl_validation");
        //print $sql;
        $i=0;
        //clear out the sql stmt.
        $sql = $insert_sql;
    }
}
function getCobraRPTPeriodsBeforeCur($rpt_period, $program="0481"){
    $year       = intval(substr($rpt_period, 0, 4));
    $month      = month2digit(substr($rpt_period, -2));
    $day        = getMonthEndDay($rpt_period);

    if($day<5){
        $month = $month+1;
    }
    $month_end_array = array();
    $sql = "select 
        CONVERT(VARCHAR(10), FSC_DATE, 120) month_end 
        from FISCDETL 
        where FISCFILE = '$program' 
            and FSC_DATE <= '$year-$month-$day'";
    $rs = dbCallCobra($sql);
    while (!$rs->EOF) {
        $month_end = trim($rs->fields["month_end"]);
        $month_end_array[] = $month_end;
        $rs->MoveNext();
    }
    return $month_end_array;
}
function getCobraRPTPeriodsALL($program="0481"){
    $month_end_array = array();
    $sql = "select 
        CONVERT(VARCHAR(10), FSC_DATE, 120) month_end 
        from FISCDETL 
        where FISCFILE = '$program' ";
    $rs = dbCallCobra($sql);
    while (!$rs->EOF) {
        $month_end = trim($rs->fields["month_end"]);
        $month_end_array[] = $month_end;
        $rs->MoveNext();
    }
    return $month_end_array;
}
function getNextCobraMonthEnd($rpt_period){
    $sql = "
        select top 1 
            CONVERT(VARCHAR(10), FSC_DATE, 120) month_end 
        from FISCDETL 
        where 
        FISCFILE = '0481' 
        and FSC_DATE < '$rpt_period'";
    $rs = dbCallCobra($sql);
    $count = $rs->RecordCount();
    if($count==0){
        return "next";
    }
    //print $sql;
    $month_end = $rs->fields["month_end"];
    return $month_end;

}
function getP6WPTpData($ship_code, $wp, $prev_month_end,$month_end){
    /*$sql = "
    SELECT
      wp,
      sum(val)
    FROM p6_tp
    WHERE ship_code = 481 AND
          date > '2015-04-25'  and date <= '2015-06-1' -- monday after month end
    GROUP BY ship_code, wp";
    */

    $mon_after_next_month_end = date('Y-m-d', strtotime($month_end. ' + 2 days'));
    /*
     * gets the value
     * */
    $sql = "
    SELECT
      wp,
      sum(val) val
    FROM p6_tp
    WHERE ship_code = $ship_code AND
          date > '$prev_month_end'  
          and date <= '$mon_after_next_month_end' -- monday after month end
          and wp = '$wp'
          and ship_code = $ship_code
    GROUP BY ship_code, wp";
    print $sql."<br>";
    $rs     = dbCall($sql, "bl_validation");
    $val    = $rs->fields["val"];
    return $val;
}
function getRPTPeriodFromMonthEndDate($month_end_date){
    $date_array    = explode("-", $month_end_date);
    $year          = $date_array[0];
    $month = $date_array[1];
    $month_end_day = intval($date_array[2]);
    if($month_end_day<5){
        $month = $month-1;
        if(strlen($month)==1)
        {
            $month = "0".$month;
        }
    }
    $sql = "select rpt_period from fmm_evms.calendar where month_end = $month_end_day and rpt_period like '$year%' and right(rpt_period, 2) = $month";
    $rs = dbCall($sql,"fmm_evms");
    $rpt_period = $rs->fields["rpt_period"];
    return $rpt_period;
}
function HCdollars($ship_code,$cobra_month_end_array, $class_array){

    $dollars_array = array();
    $length = count($cobra_month_end_array);
    foreach ($class_array as $class){
        if($class =="Actual"){
            $class_wc = "and class in ('Actual', 'CA', 'EA')";
        }
        else{
            $class_wc = "AND class = '$class'";
        }
        for($i = 1; $i <= $length; $i++) {
            $month_end_date = $cobra_month_end_array[$i];
            $prev_month_end = $cobra_month_end_array[$i-1];

            $sql = "
                    SELECT
                    t.program,
                      (sum(DIRECT) +
                       sum(GANDA) +
                       sum(SYSGA) +
                       sum(OH) +
                       sum(COM) +
                       sum(ODCNOGA)) sum
                    FROM tphase t
                    WHERE t.PROGRAM = '$ship_code'
                          $class_wc
                          AND DF_DATE > '$prev_month_end' 
                          AND DF_DATE <= '$month_end_date'
                    GROUP BY t.program
                ";
            $rs = dbCallCobra($sql);
            $dollars = formatNumber6decNoComma($rs->fields["sum"]);
            $dollars_array[$month_end_date][$class] =$dollars;
        }
    }
    return $dollars_array;
}
if($control =="cobra_proj")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="[";

    $sql = "select code from fmm_evms.master_project where active = 'true' ORDER BY code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $code = $rs->fields["code"];
        $data.="{
            \"id\": $code,
            \"text\": \"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="p6_proj"){
    $year_2digit = substr($rpt_period,2,2);
    $data ="[";

    $sql = "
            SELECT
              proj_short_name,
              proj_id 
            FROM PROJECT
            WHERE proj_short_name LIKE '%".$cobra_proj."_IMS_base_cur%'
                  AND delete_date IS NULL
            union all
            SELECT
              proj_short_name,
              proj_id 
            FROM PROJECT
            WHERE proj_short_name LIKE '%".$cobra_proj."_IMS_".$year_2digit."%'
                  AND delete_date IS NULL
                  ";
    $rs = dbCallP6($sql);
    while (!$rs->EOF) {
        $proj_short_name = $rs->fields["proj_short_name"];
        $proj_id         = $rs->fields["proj_id"];
        $data.="{
            \"id\": $proj_id,
            \"text\": \"$proj_short_name\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}

function getHCWp($program, $prev_program, $class, $rpt_period){

    $cur_rpt_period = getRPTPeriodFromMonthEndDate($rpt_period);
    $prev_rpt_period = getPreviousRPTPeriod($cur_rpt_period);

    $year            = intval(substr($cur_rpt_period, 0, 4));
    $month           = month2digit(substr($cur_rpt_period, -2));
    $day             = getMonthEndDay($cur_rpt_period);

    if($day<5){
        $month = $month+1;
    }
    $prev_rpt_year  = intval(substr($prev_rpt_period, 0, 4));
    $prev_rpt_month = month2digit(substr($prev_rpt_period, -2));
    $prev_rpt_day   = getMonthEndDay($prev_rpt_period);

    if($prev_rpt_day<5){
        $prev_rpt_month = $prev_rpt_month+1;
    }

    /*$sql = "
    SELECT
       t.program,
       t.CAWPID                                                                      cawpid,
       c.wp wp,
       (sum(DIRECT) + sum(GANDA) + sum(SYSGA) + sum(OH) + sum(COM) + sum(ODCNOGA)) sum,
       (SELECT (sum(DIRECT) + sum(GANDA) + sum(SYSGA) + sum(OH) + sum(COM) + sum(ODCNOGA))
        FROM tphase t2
        WHERE t2.PROGRAM = '$prev_program' AND t2.CLASS = '$class' AND t2.CAWPID = (select c3.CAWPID from cawp c3 where c3.PROGRAM = '$prev_program' and c3.wp =c.wp )
              AND t2.DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year' AND
              t2.DF_DATE <='$month-$day-$year') AS                                       prev_sum
     FROM tphase t
       left join CAWP c
       on c.PROGRAM = t.PROGRAM
       and c.CAWPID = t.CAWPID
     WHERE t.PROGRAM = '$program' 
     AND class = '$class' 
     AND DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year'
     AND DF_DATE <= '$month-$day-$year'
     GROUP BY t.program, t.CAWPID,c.wp
    ";*/
    $sql = "
     SELECT
       t.program,
       t.CAWPID                                                                    cawpid,
       c.wp                                                                        wp,
   (sum(DIRECT) + sum(GANDA) + sum(SYSGA) + sum(OH) + sum(COM) + sum(ODCNOGA)) sum,
   (SELECT (sum(DIRECT) + sum(GANDA) + sum(SYSGA) + sum(OH) + sum(COM) + sum(ODCNOGA))
    FROM tphase t2
    WHERE t2.PROGRAM = '$prev_program' AND t2.CLASS = '$class' AND t2.CAWPID = (SELECT c3.CAWPID
                                                                           FROM cawp c3
                                                                           WHERE
                                                                             c3.PROGRAM = '$prev_program' AND c3.wp = c.wp)
           AND t2.DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year' AND
              t2.DF_DATE <='$month-$day-$year') AS     prev_sum
 FROM tphase t LEFT JOIN CAWP c ON c.PROGRAM = t.PROGRAM AND c.CAWPID = t.CAWPID
 WHERE t.PROGRAM = '$program' AND class = '$class' AND DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year'
     AND DF_DATE <= '$month-$day-$year'
 GROUP BY t.program, t.CAWPID, c.wp
 union
    SELECT
   t.program,
   t.CAWPID                                                                    cawpid,
   c.wp                                                                        wp,
   (sum(DIRECT) + sum(GANDA) + sum(SYSGA) + sum(OH) + sum(COM) + sum(ODCNOGA)) sum,
   (SELECT (sum(DIRECT) + sum(GANDA) + sum(SYSGA) + sum(OH) + sum(COM) + sum(ODCNOGA))
    FROM tphase t2
    WHERE t2.PROGRAM = '$program' AND t2.CLASS = '$class' AND t2.CAWPID = (SELECT c3.CAWPID
                                                                           FROM cawp c3
                                                                           WHERE
                                                                             c3.PROGRAM = '$program' AND c3.wp = c.wp)
          AND t2.DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year' AND
              t2.DF_DATE <='$month-$day-$year') AS     prev_sum
 FROM tphase t LEFT JOIN CAWP c ON c.PROGRAM = t.PROGRAM AND c.CAWPID = t.CAWPID
 WHERE t.PROGRAM = '$prev_program' AND class = '$class' AND DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year'
     AND DF_DATE <= '$month-$day-$year'
 GROUP BY t.program, t.CAWPID, c.wp
    ";

    $rs = dbCallCobra($sql);
    $data = "";
    $i= 1;
    while (!$rs->EOF)
    {
        $ship_code = trim($rs->fields["program"]);
        $wp        = trim($rs->fields["wp"]);
        $sum       = formatNumber6decNoComma($rs->fields["sum"]);
        $prev_sum  = formatNumber6decNoComma($rs->fields["prev_sum"]);
        $diff      = formatNumber6decNoComma($prev_sum - $sum);
        if($diff!=0){
            $data.="
            {
                \"id\"          : $i,
                \"ship_code\"   : \"$ship_code\",
                \"wp\"          : \"$wp\",
                \"rpt_period\"    : \"$cur_rpt_period\",
                \"prev_val\"    : $prev_sum,
                \"cur_val\"     : $sum,
                \"diff\"        : $diff,
                \"type\"        : \"Dollars\"
            },";

            $i++;
        }

        $rs->MoveNext();
    }
    return $data;

}
function getHCWpHours($program, $prev_program, $class, $rpt_period){

    $cur_rpt_period = getRPTPeriodFromMonthEndDate($rpt_period);
    $prev_rpt_period = getPreviousRPTPeriod($cur_rpt_period);

    $year            = intval(substr($cur_rpt_period, 0, 4));
    $month           = month2digit(substr($cur_rpt_period, -2));
    $day             = getMonthEndDay($cur_rpt_period);

    if($day<5){
        $month = $month+1;
    }
    $prev_rpt_year  = intval(substr($prev_rpt_period, 0, 4));
    $prev_rpt_month = month2digit(substr($prev_rpt_period, -2));
    $prev_rpt_day   = getMonthEndDay($prev_rpt_period);

    if($prev_rpt_day<5){
        $prev_rpt_month = $prev_rpt_month+1;
    }

    $sql = "
     SELECT
       t.program,
       t.CAWPID                                                                    cawpid,
       c.wp                                                                        wp,
   (sum(HOURS)) sum,
   (SELECT (sum(HOURS))
    FROM tphase t2
    WHERE t2.PROGRAM = '$prev_program' AND t2.CLASS = '$class' AND t2.CAWPID = (SELECT c3.CAWPID
                                                                           FROM cawp c3
                                                                           WHERE
                                                                             c3.PROGRAM = '$prev_program' AND c3.wp = c.wp)
           AND t2.DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year' AND
              t2.DF_DATE <='$month-$day-$year') AS     prev_sum
 FROM tphase t LEFT JOIN CAWP c ON c.PROGRAM = t.PROGRAM AND c.CAWPID = t.CAWPID
 WHERE t.PROGRAM = '$program' AND class = '$class' AND DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year'
     AND DF_DATE <= '$month-$day-$year'
 GROUP BY t.program, t.CAWPID, c.wp
 union
    SELECT
   t.program,
   t.CAWPID                                                                    cawpid,
   c.wp                                                                        wp,
   (sum(HOURS)) sum,
   (SELECT (sum(HOURS))
    FROM tphase t2
    WHERE t2.PROGRAM = '$program' AND t2.CLASS = '$class' AND t2.CAWPID = (SELECT c3.CAWPID
                                                                           FROM cawp c3
                                                                           WHERE
                                                                             c3.PROGRAM = '$program' AND c3.wp = c.wp)
          AND t2.DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year' AND
              t2.DF_DATE <='$month-$day-$year') AS     prev_sum
 FROM tphase t LEFT JOIN CAWP c ON c.PROGRAM = t.PROGRAM AND c.CAWPID = t.CAWPID
 WHERE t.PROGRAM = '$prev_program' AND class = '$class' AND DF_DATE > '$prev_rpt_month-$prev_rpt_day-$prev_rpt_year'
     AND DF_DATE <= '$month-$day-$year'
 GROUP BY t.program, t.CAWPID, c.wp
    ";

    $rs = dbCallCobra($sql);
    while (!$rs->EOF)
    {
        $ship_code = trim($rs->fields["program"]);
        $wp        = trim($rs->fields["wp"]);
        $sum       = formatNumber6decNoComma($rs->fields["sum"]);
        $prev_sum  = formatNumber6decNoComma($rs->fields["prev_sum"]);
        $diff      = formatNumber6decNoComma($prev_sum - $sum);
        if($diff!=0){
            print "RPT PERIOD $rpt_period this is the program $ship_code  <br> WP$wp this is the DIFF ".$diff." THis is the PREV VAL $prev_sum <br> this is the cur val $sum<br><br>";
        }
        $rs->MoveNext();
    }
}
function HCHours($ship_code,$cobra_month_end_array, $class_array){
    $length      = count($cobra_month_end_array);
    $hours_array = array();
    foreach ($class_array as $class){
        if($class =="Actual"){
            $class_wc = "and class in ('Actual', 'CA', 'EA')";
        }
        else{
            $class_wc = "AND class = '$class'";
        }
        for($i = 1; $i <= $length; ++$i) {
            $month_end_date = $cobra_month_end_array[$i];
            $prev_month_end = $cobra_month_end_array[$i-1];

            $sql = "
                SELECT
                  t.PROGRAM,
                  sum(HOURS) tp_hours
                  FROM TPHASE t
                WHERE t.PROGRAM = '$ship_code' $class_wc
                      AND DF_DATE > '$prev_month_end' AND DF_DATE <= '$month_end_date'
                GROUP BY t.PROGRAM
                order by t.PROGRAM
                ";
            $rs = dbCallCobra($sql);
            $hours = formatNumber6decNoComma($rs->fields["tp_hours"]);
            $hours_array[$month_end_date][$class] =$hours;
        }
    }
    return$hours_array;
}

if($control =="bl_labor_units"){
    $diff_array = getCobraP6LaborDifferencesArray($ship_code);
    array_debug($diff_array);

    if(count($diff_array)> 0 ){
        $data = "[";
        foreach ($diff_array as $key=>$value){
            $wp    = $value["wp"];
            $cobra = $value["cobra"];
            $p6    = $value["p6"];
            $diff  = $value["diff"];
            $data.="{
                \"id\"   : $key,
                \"wp\"   :\"$wp\",
                \"cobra\":\"$cobra\",
                \"p6\"   :\"$p6\",
                \"diff\" :\"$diff\"
            },";
        }
    }else{
        $data = "
            [{
                \"id\"  : 1,
                \"wp\"  : \"NO RECORDS\"
            }]
        ";
    }
    die($data);
}
if($control=="load_p6"){
    loadCalendarP6X($ship_code);

}
if($control=="tp_check"){
    /*
     *the values overlap on the monday after month end.
     * */
    if(strlen($ship_code)==3)
    {
        $program = "0".$ship_code;
    }
    $ppm_bl_id      = getPPmBlID($ship_code);

    loadP6RSRCWeight($ppm_bl_id);
    //print $ppm_bl_id;
    loadP6BLTimePhase($ship_code);
    die();
    //loadP6BLUnits($ship_code);
    $rpt_period     = 201712;
    $month_end_date = getMonthEndDay($rpt_period);
    /*
     * 1.  find 1st month end.
     * 2.  find month end after.
     * 3.  find monday after month end.
     * */

    $cobra_month_end_array = getCobraRPTPeriodsBeforeCur($rpt_period);
    array_debug($cobra_month_end_array);
    $wp_data_array = array();
    for($i = 1; $i <= count($cobra_month_end_array); ++$i) {
        $month_end_date = $cobra_month_end_array[$i];
        $prev_month_end = $cobra_month_end_array[$i-1];
        $sql = "
                SELECT top 10 
                  t.PROGRAM,
                  c.wp wp,
                  sum(HOURS) tp_hours
                  FROM TPHASE t
                    left join CAWP c
                    on c.PROGRAM = t.PROGRAM
                    and c.CAWPID = t.CAWPID
                WHERE t.PROGRAM = '$program' AND class = 'Budget'
                and c.wp = 'QCX'
                      AND DF_DATE > '$prev_month_end' AND DF_DATE <= '$month_end_date'
                GROUP BY t.PROGRAM, c.wp
                order by t.PROGRAM, c.wp
                ";
        print $sql."<br>";
        print "<br>";
        print "<br>";
        print "<br>";
        $rs = dbCallCobra($sql);
        $i= 0;
        while (!$rs->EOF) {
            /*
             * EVERY WP and for every period.  check to see if it matches P6.

            */
            $wp                      = trim($rs->fields["wp"]);
            $tp_hours                = trim($rs->fields["tp_hours"]);
            $p6_tp_hours             = getP6WPTpData($ship_code, $wp, $prev_month_end, $month_end_date);
            $wp_data_array[$i]["wp"] = $wp;
            print "RPT PERIOD= " . $month_end_date . "<br>";
            print "WP = " . $wp . "<br>";
            print "p6 = " . $p6_tp_hours . "<br>";
            print "COBRA = " . $tp_hours . "<br>";
            $i++;
            $rs->MoveNext();
        }
    }
}
if($control=="project_grid")
{

    //print $sql;
    $count = $rs->RecordCount();
    if($count==0){
        $data = "
            [{
                \"id\"  : 1,
                \"wp\"  : \"NO RECORDS\"
            }]
        ";
        die($data);
    }
    $data = "[";
    $id = 1;
    $total_diff = 0;

    while (!$rs->EOF)
    {
        $ship_code   = trim($rs->fields["ship_code"]);
        $wp          = trim($rs->fields["wp"]);
        $buyer       = trim($rs->fields["buyer"]);
        $item        = trim($rs->fields["item"]);
        $description = processDescription($rs->fields["description"]);
        $po          = $rs->fields["po"];
        $line        = $rs->fields["line"];
        $vendor_id   = $rs->fields["vendor"];
        $ebom        = formatNumber4decNoComma($rs->fields["ebom"]);

        $order_qty       = formatNumber4decNoComma($rs->fields["order_qty"]);
        $c_unit_price    = formatNumber4decNoComma($rs->fields["unit_price"]);
        $c_amnt          = formatNumber4decNoComma($rs->fields["c_amnt"]);
        $gl_data         = getGlQTYWithTransfers($order_qty, $ship_code, $item);
        $c_qty           = formatNumber4decNoComma($gl_data["c_qty"]);
        $vendor          = getVendorName($vendor_id);
        $eac             = getReEstEACByItem($ship_code, $item);
        $last_months_cmt = getLastMonthsGLandOpenPO($prev_rpt_period, $ship_code, $item);
        $etc             = formatNumber4decNoComma($eac - ($last_months_cmt));

        $etc_diff          = formatNumber4decNoComma($etc - $c_amnt);
        $total_diff        += $etc_diff;
        $explanation       = fundingSrcGuess($item,$etc);
        $reason_for_change = reasonForChangeGuess($item, $etc_diff);
        $data.="{
            \"id\"                  : $id,
            \"ship_code\"           :\"$ship_code\",
            \"buyer\"               :\"$buyer\",
            \"wp\"                  :\"$wp\",
            \"item\"                :\"$item\",
            \"desc\"                :\"$description\",
            \"po\"                  :\"$po\",
            \"line\"                :\"$line\",
            \"vendor\"              :\"$vendor\",
            \"order_qty\"           :\"$order_qty\",
            \"explanation\"         :\"$explanation\",
            \"reason_for_change\"   :\"$reason_for_change\",
            \"other_notes\"         :\"\",
            \"ebom\"                :$ebom,
            \"c_unit_price\"        :$c_unit_price,
            \"c_amnt\"              :$c_amnt,
            \"c_qty\"               :$c_qty,
            \"meac_re_est_etc\"     :$etc,
            \"etc_diff\"            :$etc_diff
        },";
        $id++;
        $rs->MoveNext();
    }
    $total_diff = formatNumber4decNoComma($total_diff);
    $data.="{
            \"id\"                  : $id,
            \"ship_code\"           :\"\",
            \"buyer\"           :\"\",
            \"wp\"                  :\"\",
            \"item\"                :\"TOTAL DIFF \",
            \"order_qty\"          :0,
            \"c_unit_price\"       :0,
            \"etc_diff\"          :$total_diff
        }";
    $data.="]";
    die($data);
}
if($control =="hc"){


    $class_array = array();
    $class_array[] = "Budget";
    $class_array[] = "Actual";
    $class_array[] = "Earned";

    $program = returnCobraProgram($ship_code);
    $month_end_date        = getMonthEndDay($rpt_period);
    $cobra_month_end_array = getCobraRPTPeriodsBeforeCur($rpt_period, $program);
    $prev_program_name     = $program . "" . $prev_month . "" . $prev_year_last2;
    //print $prev_program_name." this is it ";

    /* project DOLLARS*/
    /*project DOLLARS*/
    /*project DOLLARS*/
    /*project DOLLARS*/
    $cur_proj_dollars  = HCdollars($program, $cobra_month_end_array, $class_array);
    $prev_proj_dollars = HCdollars($prev_program_name, $cobra_month_end_array, $class_array);
    $hc_dollars_array  = array();
    //array_debug($cur_proj_dollars);
    //array_debug($prev_proj_dollars);
    foreach ($cur_proj_dollars as $rpt_period=>$val_array){
        $s = $val_array["Budget"];
        $a = $val_array["Actual"];
        $p = $val_array["Earned"];
        $prev_s = $prev_proj_dollars[$rpt_period]["Budget"];
        $prev_a = $prev_proj_dollars[$rpt_period]["Actual"];
        $prev_p = $prev_proj_dollars[$rpt_period]["Earned"];

        $s_diff = formatNumber6decNoComma($s-$prev_s);
        $a_diff = formatNumber6decNoComma($a-$prev_a);
        $p_diff = formatNumber6decNoComma($p-$prev_p);

        if($s!=$prev_s ){
            $hc_dollars_array[$rpt_period]["Budget"] = $s_diff;
        }if($p!=$prev_p ){
            $hc_dollars_array[$rpt_period]["Earned"] = $p_diff;
        }if($a!=$prev_a ){
            $hc_dollars_array[$rpt_period]["Actual"] = $a_diff;
        }
    }
    /* project HOURS*/
    /*project HOURS*/
    /*project HOURS*/
    /*project HOURS*/
    $cur_proj_hours  = HCHours($program, $cobra_month_end_array, $class_array);
    $prev_proj_hours = HCHours($prev_program_name, $cobra_month_end_array, $class_array);
    //array_debug($cur_proj_hours);
    //array_debug($prev_proj_hours);
    $hc_hours_array = array();
    foreach ($cur_proj_hours as $rpt_period=>$val_array){
        $s = $val_array["Budget"];
        $a = $val_array["Actual"];
        $p = $val_array["Earned"];
        $prev_s = $prev_proj_hours[$rpt_period]["Budget"];
        $prev_a = $prev_proj_hours[$rpt_period]["Actual"];
        $prev_p = $prev_proj_hours[$rpt_period]["Earned"];

        $s_diff = formatNumber6decNoComma($s-$prev_s);

        $a_diff = formatNumber6decNoComma($a-$prev_a);
        $p_diff = formatNumber6decNoComma($p-$prev_p);
        if($s!=$prev_s ){
            $hc_hours_array[$rpt_period]["Budget"] = $s_diff;

        }if($p!=$prev_p ){
            $hc_hours_array[$rpt_period]["Earned"] = $p_diff;
        }if($a!=$prev_a ){
            $hc_hours_array[$rpt_period]["Actual"] = $a_diff;
        }
    }
    
    /*ETC HOURS FORECAST HC*/
    /*ETC FORECAST HC*/
    /*ETC FORECAST HC*/
    /*ETC FORECAST HC*/
    /*ETC FORECAST HC*/
    $class_array             = array();
    $class_array[]           = "Forecast";
    $cobra_all_periods_array = getCobraRPTPeriodsALL($program);
    $cur_etc_proj_hours      = HCHours($program, $cobra_all_periods_array, $class_array);
    $prev_etc_proj_hours     = HCHours($prev_program_name, $cobra_all_periods_array, $class_array);
    $hc_ETC_hours_array = array();
    foreach ($cur_etc_proj_hours as $rpt_period=>$val_array){
        $etc = $val_array["Forecast"];
        $prev_etc = $prev_etc_proj_hours[$rpt_period]["Forecast"];
        $etc_diff = formatNumber6decNoComma($etc-$prev_etc);
        if($etc!=$prev_etc ){
            $hc_ETC_hours_array[$rpt_period]["Forecast"] = $etc_diff;
        }
    }

    $cur_etc_proj_dollars      = HCdollars($program, $cobra_all_periods_array, $class_array);
    $prev_etc_proj_dollars     = HCdollars($prev_program_name, $cobra_all_periods_array, $class_array);
    $hc_ETC_dollars_array = array();
    foreach ($cur_etc_proj_hours as $rpt_period=>$val_array){
        $etc = $val_array["Forecast"];
        $prev_etc = $prev_etc_proj_hours[$rpt_period]["Forecast"];
        $etc_diff = formatNumber6decNoComma($etc-$prev_etc);
        if($etc!=$prev_etc ){
            $hc_ETC_dollars_array[$rpt_period]["Forecast"] = $etc_diff;
        }
    }
    $dollars_count        = count($hc_dollars_array);
    $hours_count          = count($hc_hours_array);
    $etc_hours            = count($hc_ETC_hours_array);
    $hc_ETC_dollars_array = count($hc_ETC_dollars_array);
    //array_debug($hc_dollars_array);
    foreach ($hc_dollars_array as $key=>$value){
/*        print "THIS IS THE RPT PERIOD $key";
        print "<br>";
        print "<br>";
        print "<br>";
        print "<br>";*/
        $data = getHCWp($program, $prev_program_name, "Budget", $key);

    }
    $data = substr($data, 0,-1);
    $data = "[".$data."]";
    die($data);
    //array_debug($hc_dollars_array);

    foreach ($hc_hours_array as $key=>$value){
        print "HOURS THIS IS THE RPT PERIOD $key";
        print "<br>";
        print "<br>";
        print "<br>";
        print "<br>";
        getHCWphOURS($program, $prev_program_name, "Budget", $key);

    }
    print "Dollars";
    //array_debug($hc_dollars_array);
    //array_debug($hc_hours_array);
    //array_debug($hc_ETC_hours_array);
    //array_debug($hc_ETC_dollars_array);
    die("mad eit");
}
