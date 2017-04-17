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
function buildSelect ($field, $num_periods){
    $sql = "select ";
    for ($i=1;$i<$num_periods;$i++){
        $sql.="period$i.$field,";
    }
    $sql = substr($sql, 0, -1);
    return $sql;
}
function buildFrom($rpt_period_list){
    $sql = "from ";
}
function buildEVSQL($ship_code, $rpt_list){

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
function getRPTList($rpt_period, $num_periods){
    $rpt_period_string = "\"$rpt_period\",";
    for ($i=1;$i<$num_periods;$i++){
        $rpt_period = getPreviousRPTPeriod($rpt_period);
        $rpt_period_string.="\"$rpt_period\",";
    }
    $rpt_period_string = substr($rpt_period_string, 0,-1);
     return $rpt_period_string;
}
function buildLineChartWC($filterarray){
    foreach ($filterarray as $key=>$value){
        if($key=="Hull"){
            $wc = "where ship_code = $value";
        }
    }
    return $wc;
}
if($control =="VARS")
{
    $myObj->success = true;

    $myJSON = json_encode($myObj);
    $data = "{\"success\":true,
            \"labels\": [\"Item 1\", \"Item 2\", \"Item 3\"],
            \"datasets\": [
                        {
                            \"type\": \"bar\",
                            \"label\": \"Bar Component\",
                             \"hoverBackgroundColor\": \"rgba(120,0,0)\",
                            \"backgroundColor\": \"rgb(120, 0, 0)\",
                            \"data\": [10, 20, 30]
                        },
                        {
                            \"type\": \"line\",
                            \"label\": \"Line Component\",
                            \"backgroundColor\": \"rgb(0, 207, 0)\",
                            \"data\": [30, 20, 10]
                        }
                    ]
    }";
    die($data);

    $data = "
                    [{\"labels\": [\"Item 1\", \"Item 2\", \"Item 3\"],
                    \"datasets\": [
                        {
                            \"type\": \"bar\",
                            \"label\": \"Bar Component\",
                            \"data\": [10, 20, 30],
                        },
                        {
                            \"type\": \"line\",
                            \"label\": \"Line Component\",
                            \"data\": [30, 20, 10],
                        }
                    ]
                }]";
        die($data);
}
if($control =="EV")
{
    //var_dump($filter);
    $wc       = buildLineChartWC($filterarray);
    $rpt_list = getRPTList($rpt_period, $num_periods);
    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            \"type\": \"bar\",
                            \"label\": \"Bar Component\",
                             \"hoverBackgroundColor\": \"rgba(120,0,0)\",
                            \"backgroundColor\": \"rgb(120, 0, 0)\",
                            \"data\": [10, 20, 30]
                        },
                        {
                            \"type\": \"line\",
                            \"label\": \"Line Component\",
                            \"backgroundColor\": \"rgb(0, 207, 0)\",
                            \"data\": [30, 20, 10]
                        }
                    ]
    }";
    die($data);

    $data = "
                    [{\"labels\": [\"Item 1\", \"Item 2\", \"Item 3\"],
                    \"datasets\": [
                        {
                            \"type\": \"bar\",
                            \"label\": \"Bar Component\",
                            \"data\": [10, 20, 30],
                        },
                        {
                            \"type\": \"line\",
                            \"label\": \"Line Component\",
                            \"data\": [30, 20, 10],
                        }
                    ]
                }]";
        die($data);
}
if($control =="CV")
{
    $data = "
    {\"success\":true,
        \"labels\": [\"Item 1\", \"Item 2\", \"Item 3\"],
        \"datasets\": [
            {
            \"data\": [10, 20, 30],
                \"backgroundColor\": [
                    \"#FF6384\",
                    \"#36A2EB\",
                    \"#FFCE56\"
                ],
                \"hoverBackgroundColor\": [
                    \"#FF6384\",
                    \"#36A2EB\",
                    \"#FFCE56\"
                ]}
            ]
    }";
    die($data);
}
if($control =="SV")
{
    $data = "
    {\"success\":true,
        \"labels\": [\"Item 1\", \"Item 2\", \"Item 3\"],
        \"datasets\": [
            {
            \"data\": [10, 20, 30],
                \"backgroundColor\": [
                    \"#FF6384\",
                    \"#36A2EB\",
                    \"#FFCE56\"
                ],
                \"hoverBackgroundColor\": [
                    \"#FF6384\",
                    \"#36A2EB\",
                    \"#FFCE56\"
                ]}
            ]
    }";
    die($data);
}
if($control =="var_trend")
{
    $data = "
    {\"success\":true,
        \"labels\": [\"Item 1\", \"Item 2\", \"Item 3\"],
        \"datasets\": [
            {
            \"data\": [10, 20, 30],
                \"backgroundColor\": [
                    \"#FF6384\",
                    \"#36A2EB\",
                    \"#FFCE56\"
                ],
                \"hoverBackgroundColor\": [
                    \"#FF6384\",
                    \"#36A2EB\",
                    \"#FFCE56\"
                ]}
            ]
    }";
    die($data);
}
