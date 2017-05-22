<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 4/4/2017
 * Time: 9:31 AM
 */
include("../../../../inc/inc.php");
function buildSPAChartSQL ($field, $rpt_list, $wc){
    $sql = "";
    $rpt_period_array = explode(",", $rpt_list);
    foreach ($rpt_period_array as $rpt_period){
        $rpt_period = str_replace("\"", "", $rpt_period);
        $table_name = $rpt_period."_ship";
        $sql.= "select $field from lcs_log.$table_name $wc
              UNION all ";
    }
    $sql = substr($sql, 0, -10);
    return $sql;
}
function buildSVCVChartSQL ($field, $rpt_list, $wc){
    $sql = "";

    $rpt_period_array = explode(",", $rpt_list);
    //var_dump($rpt_period_array);
    foreach ($rpt_period_array as $rpt_period){
        $rpt_period = str_replace("\"", "", $rpt_period);
        $table_name = $rpt_period."_ship";
        $sql.= "select $field, p from lcs_log.$table_name $wc
              UNION all ";
    }
    $sql = substr($sql, 0, -10);
    return $sql;
}
function buildTCPIChartSQL ($rpt_list, $wc){
    $sql = "";

    $rpt_period_array = explode(",", $rpt_list);
    //var_dump($rpt_period_array);
    foreach ($rpt_period_array as $rpt_period){
        $rpt_period = str_replace("\"", "", $rpt_period);
        $table_name = $rpt_period."_ship";
        $sql.= "select p,a, bac, eac from lcs_log.$table_name $wc
              UNION all ";
    }
    $sql = substr($sql, 0, -10);
    return $sql;
}

function returnSPAChartDataSet($field, $rpt_list, $wc){
    $sql = buildSPAChartSQL($field, $rpt_list, $wc);

    $rs  = dbCall($sql, "lcs_log");
    $data_string = "";
    while (!$rs->EOF)
    {
        $num = formatNumberNoComma($rs->fields["$field"]);
        $data_string.="$num,";
        $rs->MoveNext();
    }
    $data_string = substr($data_string, 0, -1);
    return $data_string;
}
function returnSVCVChartDataSet($field, $rpt_list, $wc){
    $sql = buildSVCVChartSQL($field, $rpt_list, $wc);

    $rs  = dbCall($sql, "lcs_log");
    $data_string = "";
    while (!$rs->EOF)
    {
        $num = formatNumberNoComma($rs->fields["$field"]);
        $p   = formatNumberNoComma($rs->fields["p"]);
        $var = $p-$num;
        $data_string.="$var,";
        $rs->MoveNext();
    }
    $data_string = substr($data_string, 0, -1);
    return $data_string;
}
function returnEVIndexChartDataSet($field, $rpt_list, $wc){
    $sql = buildSVCVChartSQL($field, $rpt_list, $wc);

    $rs  = dbCall($sql, "lcs_log");
    $data_string = "";
    while (!$rs->EOF)
    {
        $num = formatNumberNoComma($rs->fields["$field"]);
        $p   = formatNumberNoComma($rs->fields["p"]);
        $var = formatNumberNoComma($p/$num);
        $data_string.="$var,";
        $rs->MoveNext();
    }
    $data_string = substr($data_string, 0, -1);
    return $data_string;
}
function getShipTCPI($rpt_list, $wc){
    $sql = buildTCPIChartSQL($rpt_list,$wc);
    $rs  = dbCall($sql, "lcs_log");
    $data_string = "";
    while (!$rs->EOF)
    {
        $a    = formatNumberNoComma($rs->fields["a"]);
        $p    = formatNumberNoComma($rs->fields["p"]);
        $bac  = formatNumberNoComma($rs->fields["bac"]);
        $eac  = formatNumberNoComma($rs->fields["eac"]);
        $tcpi = formatNumberNoComma(evalTCPI($a, $p, $bac, $eac));

        $data_string.="$tcpi,";
        $rs->MoveNext();
    }
    $data_string = substr($data_string, 0, -1);
    return $data_string;
    return $tcpi;
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
function getStartRPTPeriod($rpt_period, $num_periods){
    for ($i=$num_periods;$i>1;$i--){
        $rpt_period = getPreviousRPTPeriod($rpt_period);
    }
    return $rpt_period;
}
function getRPTList($rpt_period, $num_periods){
    $start_rpt = getStartRPTPeriod($rpt_period, $num_periods);
    $rpt_period_string = "\"$start_rpt\",";

    for ($i=1;$i<$num_periods;$i++){
        $start_rpt = getNextRPTPeriod($start_rpt);
        $rpt_period_string.="\"$start_rpt\",";
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
if(isset($rpt_period)==false){
    /*date is YYYY-MM-dd*/
    $rpt_period = currentRPTPeriod();
}

if($control =="EV")
{
    //var_dump($filter);
    $wc       = buildLineChartWC($filter);

    $rpt_list = getRPTList($rpt_period, $num_periods);
    //print $rpt_list."<br>";
    $s = returnSPAChartDataSet("s", $rpt_list, $wc);
    $p = returnSPAChartDataSet("p", $rpt_list, $wc);
    $a = returnSPAChartDataSet("a", $rpt_list, $wc);

    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            \"label\": \"BCWS\",                            
                            \"fillColor\": \"rgba(220,220,220,0.2)\",
                            \"strokeColor\": \"rgba(220,20,20,1)\",
                            \"pointColor\": \"rgba(220,20,20,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(220,220,220,1)\",
                            \"data\": [$s]
                        },{
                            \"label\": \"BCWP\",
                            \"fillColor\": \"rgba(151,187,205,0.2)\",
                            \"strokeColor\": \"rgba(15,187,25,1)\",
                            \"pointColor\": \"rgba(15,187,25,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(151,187,205,1)\",
                            \"data\": [$p]
                        },{
                            \"type\": \"line\",
                            \"label\": \"ACWP\",
                            \"data\": [$a]
                        }
                    ]
    }";
    die($data);
}
if($control =="VARS")
{
    //var_dump($filter);
    $wc       = buildLineChartWC($filter);

    $rpt_list = getRPTList($rpt_period, $num_periods);
    //print $rpt_list."<br>";
    $sv = returnSVCVChartDataSet("s", $rpt_list, $wc);
    $cv = returnSVCVChartDataSet("a", $rpt_list, $wc);

    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            \"label\": \"SV\",
                            \"fillColor\": \"rgba(220,220,220,0.2)\",
                            \"strokeColor\": \"rgba(220,20,20,1)\",
                            \"pointColor\": \"rgba(220,20,20,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(220,220,220,1)\",
                            \"data\": [$sv]
                        },{
                            \"fillColor\": \"rgba(151,187,205,0.2)\",
                            \"strokeColor\": \"rgba(15,187,25,1)\",
                            \"pointColor\": \"rgba(15,187,25,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(151,187,205,1)\",
                            \"label\": \"CV\",
                            \"data\": [$cv]
                        }
                    ]
    }";
    die($data);
}
if($control =="ev_index")
{
    //var_dump($filter);
    $wc       = buildLineChartWC($filter);
    $rpt_list = getRPTList($rpt_period, $num_periods);
    //print $rpt_list."<br>";
    $spi  = returnEVIndexChartDataSet("s", $rpt_list, $wc);

    $cpi  = returnEVIndexChartDataSet("a", $rpt_list, $wc);

    $tcpi = getShipTCPI($rpt_list, $wc);


    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            \"label\": \"SPI\",
                            \"fillColor\": \"rgba(220,220,220,0.2)\",
                            \"strokeColor\": \"rgba(220,20,20,1)\",
                            \"pointColor\": \"rgba(220,20,20,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(220,220,220,1)\",
                            \"data\": [$spi]
                        },{
                            \"fillColor\": \"rgba(151,187,205,0.2)\",
                            \"strokeColor\": \"rgba(15,187,25,1)\",
                            \"pointColor\": \"rgba(15,187,25,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(151,187,205,1)\",
                            \"label\": \"CPI\",
                            \"data\": [$cpi]
                        },{
                            \"fillColor\": \"rgba(151,187,205,0.2)\",
                            \"strokeColor\": \"rgba(15,187,25,1)\",
                            \"pointColor\": \"rgba(15,187,25,1)\",
                            \"pointStrokeColor\": \"#fff\",
                            \"pointHighlightFill\": \"#fff\",
                            \"pointHighlightStroke\": \"rgba(151,187,205,1)\",
                            \"label\": \"TCPI\",
                            \"data\": [$tcpi]
                        }
                    ]
    }";
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
