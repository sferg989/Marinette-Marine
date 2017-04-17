<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 4/4/2017
 * Time: 9:31 AM
 */
include("../../../../inc/inc.php");
function makeSQLString($ship_code, $period_array)
{
    $sql = "";

    $i = 0;
    foreach ($period_array as $value){
        $lcs_table_name = $value."_ship";
        $sql.= "
            SELECT
              $value rpt_period,
              sum(IF(debit = 'mr', amount * 1, amount)) amt,
              lcs.mr as mr
            FROM `".$value."_bcr` bcr
            inner join lcs_log.$lcs_table_name lcs
              ON lcs.ship_code = bcr.ship_code
            WHERE
            bcr.ship_code = $ship_code
            AND debit IN ('mr')
            GROUP BY bcr.ship_code
            union all";
        $i++;
    }
    $sql = substr($sql, 0, -9);
    return $sql;
 }
 function createPeriodArray($rpt_period, $num_periods){
     $rpt_period_array   = array();
     $rpt_period_array[] = $rpt_period;
     $num_periods = $num_periods-1;
    for ($i=0; $i<$num_periods;$i++){

        $rpt_period         = getPreviousRPTPeriod($rpt_period);
        $rpt_period_array[] = $rpt_period;
    }
    return $rpt_period_array;
 }



if($control =="mr_by_period")
{

    $wc.=")";
    $data = "{
  \"cols\": [
        {\"id\":\"rpt_period\",\"label\":\"Reporting Period\",\"pattern\":\"\",\"type\":\"string\"},
        {\"id\":\"mr_change\",\"label\":\"MR Change \",\"pattern\":\"\",\"type\":\"number\"},
        {\"id\":\"mr\",\"label\":\"MR Balance \",\"pattern\":\"\",\"type\":\"number\"}
      ],
  \"rows\": [";

    $period_array = createPeriodArray($rpt_period, $num_periods);
    $sql          = makeSQLString($ship_code, $period_array);
    //print $sql;
    $rs           = dbCall($sql, "bcr_log");
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $amt        = $rs->fields["amt"];
        $mr        = $rs->fields["mr"];
        $data.="{\"c\":
            [   {\"v\":\"$rpt_period\",\"f\":null},
                {\"v\":$mr,\"f\":null},
                {\"v\":$amt,\"f\":null}

            ]},";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]}";
    die($data);
}