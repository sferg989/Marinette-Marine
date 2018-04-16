<?php

include("inc/inc.php");
//include("inc/inc.cobra.php");
function insertCalendar($rpt_period, $month_end)
{
    $sql = "insert into fmm_evms.calendar (rpt_period, month_end) values ( $rpt_period, $month_end)";
    //print $sql;
    $junk = dbcall($sql, "fmm_evms");

}
function loadFiscalCalendar(){
    //$sql = "select FSC_DATE, FIELD00, FIELD01 from FISCDETL where FSC_DATE < '2016-01-30 00:00:00.000' and FISCFILE = '0473' order by FSC_DATE";
    $sql = "select FSC_DATE, FIELD00, FIELD01 from FISCDETL where FSC_DATE < '2013-01-26 00:00:00.000' and FISCFILE = '0465' order by FSC_DATE";
    $rs = dbcallCobra($sql);
    while (!$rs->EOF)
    {

        $status_date = trim($rs->fields["FSC_DATE"]);
        $cobra_rpt_period  = trim($rs->fields["FIELD00"]);
        /*cobra format
        2018-01-27 00:00:00.000
        rpt format
        01-2018
         * */
        $rpt_period_array = explode("-", $cobra_rpt_period);
        $year             = $rpt_period_array[1];
        $month            = month2digit($rpt_period_array[0]);
        $rpt_period = $year."".$month;

        $date_array = explode("-", $status_date);
        $month_end = substr($date_array[2], 0, 2);
        insertCalendar($rpt_period, $month_end);
        $rs->MoveNext();
    }
}




/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 4/13/2017
 * Time: 2:52 PM
 */

$cur_rpt_period = 201711;
updateCalendarSet("0483",$cur_rpt_period);
die();
//loadFiscalCalendar();

//insertCalendar(202001, 30);
//$g_path2_perform_report          = "D:\\";
/*foreach (scandir($g_path2_perform_report) as $file) {
    if ('.' === $file) continue;
    if ('..' === $file) continue;
    print $file."<br>";
    //$csvfiles[] = $file;

}*/

/*
 * **************************************
 * *************************************
 * ***************************************
 * Load 201512
 * */




