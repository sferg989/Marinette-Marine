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
        $sql.= "
        select 
          IFNULL((select $field from lcs_log.$table_name $wc) ,0) $field              
        UNION all";
    }
    $sql = substr($sql, 0, -10);
    //print $sql;
    return $sql;
}
function buildSVCVChartSQL ($field, $rpt_list, $wc){
    $sql = "";

    $rpt_period_array = explode(",", $rpt_list);
    //var_dump($rpt_period_array);
    foreach ($rpt_period_array as $rpt_period){
        $rpt_period = str_replace("\"", "", $rpt_period);
        $table_name = "`".$rpt_period."_ship`";

        $sql.= "SELECT 
                      IFNULL((select $field from lcs_log.$table_name $wc) ,0) $field,
                      IFNULL((select p from lcs_log.$table_name $wc) ,0) p 
                      union all ";
        //$sql.= "select $field, p from lcs_log.$table_name $wc UNION all ";
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
    //print $sql;
    $rs  = dbCall($sql, "lcs_log");
    $data_string = "";
    while (!$rs->EOF)
    {
        $num = formatNumberNoComma($rs->fields["$field"]);
        $p   = formatNumberNoComma($rs->fields["p"]);
        $var = formatNumberNoComma($p-$num);
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
function returnChartDisplayData($type){
    $common = "\"label\": \"$type\",
        \"fill\": false,    
        \"borderWidth\": 3,    
        \"pointBorderWidth\": 1,  
        \"pointHitRadius\": 75,
    ";
    $data = "";
    switch ($type) {
        case "bcws":
            $data = $common."
                \"borderColor\": \"blue\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"blue\",
            ";
        break;
        case "bcwp":
            $data = $common."
                \"borderColor\"     : \"red\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\" : \"red\",
            ";
        break;
        case "acwp":
            $data = $common."
                \"borderColor\": \"green\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"green\",
            ";
        break;
        case "bac":
            $data = $common."
                \"borderColor\": \"black\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"black\",
            ";
        break;
        case "SV":
            $data = $common."
                \"borderColor\": \"blue\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"blue\",
            ";
        break;
        case "CV":
            $data = $common."
                \"borderColor\": \"green\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"green\",
            ";
        break;
        case "SPI":
            $data = $common."
                \"borderColor\": \"blue\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"blue\",
            ";
        break;
        case "CPI":
            $data = $common."
                \"borderColor\": \"green\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"green\",
            ";
        break;
        case "TCPI":
            $data = $common."
                \"borderColor\": \"red\",
                \"pointBorderColor\": \"black\",
                \"backgroundColor\": \"red\",
            ";
        break;
        default:
    }
    return $data;
}
if(isset($rpt_period)==false){
    /*date is YYYY-MM-dd*/
    $rpt_period = currentRPTPeriod();
    $rpt_period = getPreviousRPTPeriod($rpt_period);

}

if($control =="EV")
{
    //var_dump($filter);
    $wc       = buildLineChartWC($filter);
    $bcws_display =
    $rpt_list = getRPTList($rpt_period, $num_periods);
    //print $rpt_list."<br>";
    $s       = returnSPAChartDataSet("s", $rpt_list, $wc);
    $p       = returnSPAChartDataSet("p", $rpt_list, $wc);
    $a       = returnSPAChartDataSet("a", $rpt_list, $wc);
    $bac     = returnSPAChartDataSet("bac", $rpt_list, $wc);
    $s_css   = returnChartDisplayData("bcws");
    $p_css   = returnChartDisplayData("bcwp");
    $a_css   = returnChartDisplayData("acwp");
    $bac_css = returnChartDisplayData("bac");
    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            $s_css
                            \"data\": [$s]
                        },{
                            $p_css
                            \"data\": [$p]
                        },{
                            $a_css
                            \"data\": [$a]
                        },{
                            $bac_css
                            \"data\": [$bac]
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
    $sv_css   = returnChartDisplayData("SV");
    $cv_css   = returnChartDisplayData("CV");

    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            $sv_css  
                            \"data\": [$sv]
                        },{
                            $cv_css   
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

    $spi_css   = returnChartDisplayData("SPI");
    $cpi_css   = returnChartDisplayData("CPI");
    $tcpi_css   = returnChartDisplayData("TCPI");

    $data = "{\"success\":true,
            \"labels\": [$rpt_list],
            \"datasets\": [
                        {
                            $spi_css
                            \"data\": [$spi]
                        },{
                            $cpi_css
                            \"data\": [$cpi]
                        },{
                        $tcpi_css
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
