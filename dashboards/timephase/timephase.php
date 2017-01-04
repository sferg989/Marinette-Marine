<?php
include('../../inc/inc.php');
$i = 0;
function getRptPeriods($start_period, $end_period)
{
    $start_year = substr($start_period,0,4);
    $end_year = substr($end_period,0,4);
    $start_month = substr($start_period, -2);
    $end_month = substr($end_period, -2);
    $rpt_periods_array = array();
    if($start_year==$end_year)
    {
        for ($i = $start_month; $i <= $end_month; $i++) {
            $month = $i;
            if(strlen($month)==1)
            {
                $month = "0".$month;
            }
            $period = $start_year."".$month;
            $rpt_periods_array[] = intval($period);
        }
    }
    if($start_year!=$end_year)
    {
        for ($i = $start_month; $i <= 12; $i++) {
            $month = $i;
            if(strlen($month)==1)
            {
                $month = "0".$month;
            }
            $period = $start_year."".$month;
            $rpt_periods_array[] = intval($period);
        }
        for ($i = 1; $i <= $end_month; $i++) {
            $month = $i;
            if(strlen($month)==1)
            {
                $month = "0".$month;
            }
            $period = $end_year."".$month;
            $rpt_periods_array[] = intval($period);
        }
    }
    return $rpt_periods_array;
    //var_dump($rpt_periods_array);
}

if($chart_type =="spa")
{
    $rpt_periods_array = getRptPeriods($start_period, $end_period);
    $wc = "rpt_period in (";
    foreach ($rpt_periods_array as $key=>$value) {
        $wc.="$value,";
    }
    $wc = substr($wc, 0, -1);
    $wc.=")";
    $data = "{
  \"cols\": [
        {\"id\":\"rpt_period\",\"label\":\"Reporting Period\",\"pattern\":\"\",\"type\":\"string\"},
        {\"id\":\"s_cur\",\"label\":\"BCWS \",\"pattern\":\"\",\"type\":\"number\"},
        {\"id\":\"p_cur\",\"label\":\"BCWP \",\"pattern\":\"\",\"type\":\"number\"},
        {\"id\":\"a_cur\",\"label\":\"ACWP\",\"pattern\":\"\",\"type\":\"number\"}
      ],
  \"rows\": [";


    $sql = "select rpt_period,s_dollars, p_dollars, a_dollars, eac_dollars 
  from cost.timephased where wbs_id = '$ca' and $wc order by rpt_period desc";
    //print $sql;
    $rs = dbCall($sql, "cost");
    while (!$rs->EOF) {
        $rpt_period  = $rs->fields["rpt_period"];
        $s_dollars   = $rs->fields["s_dollars"];
        $p_dollars   = $rs->fields["p_dollars"];
        $a_dollars   = $rs->fields["a_dollars"];
        $data.="{\"c\":
            [   {\"v\":\"$rpt_period\",\"f\":null},
                {\"v\":$s_dollars,\"f\":null},
                {\"v\":$p_dollars,\"f\":null},
                {\"v\":$a_dollars,\"f\":null}

            ]},";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]}";

    die($data);
}
if($chart_type =="spa_table")
{
    $rpt_periods_array = getRptPeriods($start_period, $end_period);
    $wc = "rpt_period in (";
    foreach ($rpt_periods_array as $key=>$value) {
        $wc.="$value,";
    }
    $wc = substr($wc, 0, -1);
    $wc.=")";
    $data_array = array();
    $data = "{
    \"cols\": [
        {\"id\":\"cost_set\",\"label\":\"Cost Set\",\"pattern\":\"\",\"type\":\"string\"},
        ";
        $sql = "select rpt_period,s_dollars, p_dollars, a_dollars, eac_dollars 
              from cost.timephased where wbs_id = '$ca' and $wc order by rpt_period ASC";
    //print $sql;
    $rs = dbCall($sql, "cost");
    while (!$rs->EOF)
    {
        $rpt_period  = $rs->fields["rpt_period"];
        $s_dollars   = floatval($rs->fields["s_dollars"]);
        $p_dollars   = floatval($rs->fields["p_dollars"]);
        $a_dollars   = floatval($rs->fields["a_dollars"]);
        $data_array["bcws"][$rpt_period] = $s_dollars;
        $data_array["bcwp"][$rpt_period] = $p_dollars;
        $data_array["acwp"][$rpt_period] = $a_dollars;
        $data.="{\"id\":\"$rpt_period\",\"label\":\"$rpt_period\",\"pattern\":\"\",\"type\":\"number\"},
        ";

        $rs->MoveNext();
    }
    $data = substr(trim($data), 0, -1);
    $data.="],
    \"rows\": [";
    foreach ($data_array as $cost_set=>$value)
    {
        $title = strtoupper($cost_set);
        $data.="{\"c\":
                [{\"v\":\"$title\"},
        ";
        foreach ($value as $rpt_period =>$cost)
        {
            $data.="{\"v\":$cost},
            ";
        }
        $data = substr(trim($data), 0, -1);
        $data.="
        ]},";
    }
    $data = substr(trim($data), 0, -1);
    $data.="]}";
    die($data);
}
if($chart_type =="spi_table")
{
    $rpt_periods_array = getRptPeriods($start_period, $end_period);
    $wc = "rpt_period in (";
    foreach ($rpt_periods_array as $key=>$value) {
        $wc.="$value,";
    }
    $wc = substr($wc, 0, -1);
    $wc.=")";
    $data_array = array();
    $data = "{
    \"cols\": [
        {\"id\":\"cost_set\",\"label\":\"Cost Set\",\"pattern\":\"\",\"type\":\"string\"},
        ";
        $sql = "
select rpt_period,s_dollars, p_dollars, a_dollars, eac_dollars 
              from cost.timephased where wbs_id = '$ca' and $wc order by rpt_period ASC";
    //print $sql;
    $rs = dbCall($sql, "cost");
    while (!$rs->EOF)
    {
        $rpt_period  = $rs->fields["rpt_period"];
        $s_dollars = 1;
        $p_dollars = 1;
        $a_dollars = 1;
        $spi = 1;
        $cpi = 1;
        $s_dollars   = floatval($rs->fields["s_dollars"]);
        $p_dollars   = floatval($rs->fields["p_dollars"]);
        $a_dollars   = floatval($rs->fields["a_dollars"]);
        if($a_dollars==0)
        {
            $cpi = 1;
        }
        else if($s_dollars ==0)
        {
            $spi = 1;
        }
        else
        {
            $spi = floatval($p_dollars / $s_dollars);
            $cpi = floatval($p_dollars / $a_dollars);
        }
        $tcpi        = 1;
        $data_array["spi"][$rpt_period] = $spi;
        $data_array["cpi"][$rpt_period] = $cpi;
        $data_array["tcpi"][$rpt_period] = 1;
        $data.="{\"id\":\"$rpt_period\",\"label\":\"$rpt_period\",\"pattern\":\"\",\"type\":\"number\"},
        ";

        $rs->MoveNext();
    }
    $data = substr(trim($data), 0, -1);
    $data.="],
    \"rows\": [";
    foreach ($data_array as $cost_set=>$value)
    {
        $title = strtoupper($cost_set);
        $data.="{\"c\":
                [{\"v\":\"$title\"},
        ";
        foreach ($value as $rpt_period =>$cost)
        {
            $data.="{\"v\":$cost},
            ";
        }
        $data = substr(trim($data), 0, -1);
        $data.="
        ]},";
    }
    $data = substr(trim($data), 0, -1);
    $data.="]}";
    die($data);
}
if($chart_type =="spi_cpi")
{
    $rpt_periods_array = getRptPeriods($start_period, $end_period);
    $wc = "rpt_period in (";
    foreach ($rpt_periods_array as $key=>$value) {
        $wc.="$value,";
    }
    $wc = substr($wc, 0, -1);
    $wc.=")";
    $data = "{
  \"cols\": [
        {\"id\":\"rpt_period\",\"label\":\"Reporting Period\",\"pattern\":\"\",\"type\":\"string\"},
        {\"id\":\"spi\",\"label\":\"spi \",\"pattern\":\"\",\"type\":\"number\"},
        {\"id\":\"cpi\",\"label\":\"cpi \",\"pattern\":\"\",\"type\":\"number\"},
        {\"id\":\"tcpi\",\"label\":\"tcpi\",\"pattern\":\"\",\"type\":\"number\"}
      ],
  \"rows\": [";


    $sql = "select rpt_period,s_dollars, p_dollars, a_dollars, eac_dollars 
        from cost.timephased where wbs_id = '$ca' and $wc order by rpt_period desc";
    //print $sql;
    $rs = dbCall($sql, "cost");
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $s_dollars   = floatval($rs->fields["s_dollars"]);
        $p_dollars   = floatval($rs->fields["p_dollars"]);
        $a_dollars   = floatval($rs->fields["a_dollars"]);
        if($a_dollars==0)
        {
            $cpi = 1;
        }
        else if($s_dollars ==0)
        {
            $spi = 1;
        }
        else
        {
            $spi = floatval($p_dollars / $s_dollars);
            $cpi = floatval($p_dollars / $a_dollars);
        }
        $tcpi        = 1;
        $data.="{\"c\":
            [   {\"v\":\"$rpt_period\",\"f\":null},
                {\"v\":$spi,\"f\":null},
                {\"v\":$cpi,\"f\":null},
                {\"v\":$tcpi,\"f\":null}
            ]},";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]}";

    die($data);
}

