<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');

//$user = $_SESSION["user_name"];
$rpt_period = currentRPTPeriod();
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
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
function getPPmBlID($ship_code){
    $sql = "select proj_id from PROJECT where proj_short_name like '%".$ship_code."_IMS_Base_Cur%'";

    $rs = dbCallP6($sql);
    $ppm_bl_id = $rs->fields["proj_id"];
    return $ppm_bl_id;

}
function getPPmAPID($ship_code){
    $sql = "select ppm_ap_id from master_project where code = $ship_code";
    $rs        = dbCall($sql, "fmm_evms");
    $ppm_bl_id = $rs->fields["ppm_ap_id"];
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
    array_debug($curve_array);
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
        print $sql;
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
        print $sql;
        $junk = dbCall($sql, "bl_validation");
    }
}
/*
 * Check Labor Units.
 * Check labor units Timephase the same.
 *
 * */
function getCobraP6LaborDifferencesArray($ship_code){
    $ppm_bl_id = getPPmBlID($ship_code);

    $wc = "";
    if($ship_code>=477){
        $wc = " and wp not like '%matl%'";
    }
    if(strlen($ship_code)==3)
    {
        $program = "0".$ship_code;
    }

    $sql = "select wp, BAC_HRS from CAWP where PROGRAM = '$program' $wc and wp > ''";
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
function loadRsrcCruveRate($ppm_bl_id){
    /*
     * 1.  Get total hours for each task.
     * 2.  how many hours are in 5% of duration.
     * 3.  how many labor units consumed in 5% of duration.
     *   weight * (total hours).
     * 4.  How may days are in 5% duration.
     * 5.  how many labor units per day.
     * */
    $sql = "
    SELECT
         task_id,
         curv_id,
         (SELECT t.clndr_id FROM TASK t  WHERE t.task_id = r.task_id) AS cal_id,
         (SELECT task_code  FROM TASK t  WHERE t.task_id = r.task_id) AS task_code,
         (SELECT target_work_qty  FROM TASK t  WHERE t.task_id = r.task_id) AS target_work_qty,
         (SELECT target_drtn_hr_cnt  FROM TASK t  WHERE t.task_id = r.task_id) AS target_drtn_hr_cnt,
         (SELECT early_start_date  FROM TASK t  WHERE t.task_id = r.task_id) AS start,
         (SELECT early_end_date  FROM TASK t  WHERE t.task_id = r.task_id)    finish
       FROM TASKRSRC r
       WHERE proj_id = 98707 AND delete_date IS NULL AND curv_id IS NOT NULL";
    $rs = dbCallP6($sql);

    $rs = dbCallP6($sql);
    while (!$rs->EOF)
    {
        $cal_id           = $rs->fields["cal_id"];
        $start           = $rs->fields["start"];
        $finish          = $rs->fields["finish"];
        $task_code       = $rs->fields["task_code"];
        $target_work_qty = $rs->fields["target_work_qty"];
        $total_hours     = $rs->fields["target_drtn_hr_cnt"];

        $hours_in5_percent = formatNumber4decNoComma(.05 * $total_hours);
        $days_in_5_pc      = intval($hours_in5_percent/8);

        $days_in_total_hours = intval($total_hours/8);

        for($i=1;$i<=$days_in_total_hours;$i++){
            /*goes to next weitgh level*/
            if($i %  $days_in_5_pc== 0){
                //move2NextWeight;

            }
        }


        $rs->MoveNext();
    }



}
function loadP6BLTimePhase($ship_code){
    $ppm_bl_id      = getPPmBlID($ship_code);

    deleteFromTable("bl_validation", "p6_tp","ship_code", $ship_code);
    loadRsrcStraightRate($ship_code,$ppm_bl_id);

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
function getCobraRPTPeriodsBeforeCur($rpt_period){
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
        where FISCFILE = '0481' 
            and FSC_DATE <= '$year-$month-$day'";
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
    WHERE ship_code = 481 AND
          date > '$prev_month_end'  
          and date <= '$mon_after_next_month_end' -- monday after month end
          and wp = '$wp'
          and ship_code = $ship_code
    GROUP BY ship_code, wp";
    //print $sql."<br>";
    $rs     = dbCall($sql, "bl_validation");
    $val    = $rs->fields["val"];
    return $val;
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
    $ppm_bl_id      = getPPmBlID($ship_code);
    loadP6RSRCWeight($ppm_bl_id);
    die();
    loadP6BLTimePhase($ship_code);
    loadP6BLUnits($ship_code);
    $ship_code      = 481;
    $rpt_period     = 201710;
    $month_end_date = getMonthEndDay($rpt_period);
    /*
     * 1.  find 1st month end.
     * 2.  find month end after.
     * 3.  find monday after month end.
     * */

    $cobra_month_end_array = getCobraRPTPeriodsBeforeCur($rpt_period);
    $length = count($cobra_month_end_array);
    for($i = 1; $i <= $length; ++$i) {
        $month_end_date = $cobra_month_end_array[$i];
        $prev_month_end = $cobra_month_end_array[$i-1];
        $sql = "
                SELECT
                  t.PROGRAM,
                  c.wp wp,
                  sum(HOURS) tp_hours
                  FROM TPHASE t
                    left join CAWP c
                    on c.PROGRAM = t.PROGRAM
                    and c.CAWPID = t.CAWPID
                WHERE t.PROGRAM = '0481' AND class = 'Budget'
                and c.wp = 'WBD'
                      AND DF_DATE > '$prev_month_end' AND DF_DATE <= '$month_end_date'
                GROUP BY t.PROGRAM, c.wp
                order by t.PROGRAM, c.wp
                ";
        //print $sql;
        $rs = dbCallCobra($sql);
        while (!$rs->EOF) {
            /*
             * EVERY WP and for every period.  check to see if it matches P6.

            */
            $wp             = trim($rs->fields["wp"]);
            $tp_hours       = trim($rs->fields["tp_hours"]);
            $p6_tp_hours    = getP6WPTpData($ship_code, $wp, $prev_month_end,$month_end_date);

            print "RPT PERIOD= " . $month_end_date . "<br>";
            print "WP = " . $wp . "<br>";
            print "p6 = " . $p6_tp_hours . "<br>";
            print "COBRA = " . $tp_hours . "<br>";
            $rs->MoveNext();
        }

    }

}
if($control=="project_grid")
{
    $sql = "
                
SELECT
          a.t_cprj AS                                                                     ship_code,
  f.t_nama as buyer,
          CASE
          WHEN a.t_pacn <> 0
            THEN substring(a.t_pacn, 2, 3)
          ELSE b.t_cpcp
          END      AS                                                                     swbs,
          a.t_item AS                                                                     item,
          CASE
          WHEN a.t_cprj <> '      '
            THEN c.t_dsca
          ELSE d.t_dsca
          END      AS                                                                     description,
          CASE
          WHEN a.t_cprj <> '      '
            THEN
              CASE
              WHEN c.t_csel = ' NR'
                THEN 'NRE'
              ELSE ''
              END
          ELSE
            CASE
            WHEN d.t_csel = ' NR'
              THEN 'NRE'
            ELSE ''
            END
          END      AS                                                                     nre,
          a.t_suno AS                                                                     vendor,
          a.t_orno AS                                                                     po,
          a.t_pono AS                                                                     line,
          a.t_pric AS                                                                     unit_price,
          a.t_oqua AS                                                                     order_qty,
  e.t_qana as ebom,
          CASE
          WHEN a.t_dqua <> 0
            THEN (a.t_dqua + a.t_bqua) * a.t_pric
          ELSE a.t_oqua * a.t_pric
          END      AS                                                                     c_amnt,
          (SELECT
             TOP 1 LTRIM(RTRIM(bc.t_bitm)) AS wp
           FROM ttipcs950490 AS ab
             LEFT JOIN ttipcs952490 AS bc ON ab.t_bdgt = bc.t_bdgt
           WHERE ab.t_cprj = a.t_cprj AND bc.t_bdgt = ab.t_bdgt AND bc.t_item = a.t_item) wp

        FROM ttdpur041490 a
          LEFT JOIN ttdpur045490 b ON b.t_orno = a.t_orno AND b.t_pono = a.t_pono AND b.t_srnb = 0
          LEFT JOIN ttipcs021490 c ON c.t_cprj = a.t_cprj AND c.t_item = a.t_item
          LEFT JOIN ttiitm001490 d ON d.t_item = a.t_item
          left join ttiitm901490 e on  c.t_cprj = e.t_cprj and c.t_item = e.t_item
          left join  ttccom001490 f  on f.t_emno = c.t_buyr
        WHERE
        a.t_orno = $po
        ORDER BY a.t_cprj, a.t_item, a.t_orno, a.t_pono";
    $rs  = dbCallBaan($sql);
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
            \"desc\"                :\"\",
            \"po\"                 :\"\",
            \"line\"               :\"\",
            \"vendor\"             :\"\",
            \"order_qty\"          :0,
            \"c_unit_price\"       :0,
             \"explanation\"       :\"\",
            \"other_notes\"        :\"\",
            \"reason_for_change\"  :\"\",
            \"c_amnt\"             :0,
            \"c_qty\"             :0,
            \"meac_re_est_etc\"   :0,
            \"etc_diff\"          :$total_diff
        }";
    $data.="]";
    die($data);
}
if($control =="hc"){

}
