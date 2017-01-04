<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 11/23/2016
 * Time: 9:09 AM
 */
include('../../inc/inc.php');
error_reporting(0);
$request_url = explode("/", $_SERVER['REQUEST_URI']);


if($filter =="ca")
{

    if($q!="")
    {
        $wc = "where name like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select id, name from fmm_evms.master_ca $wc";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $cmid = $rs->fields["id"];
        $name = $rs->fields["name"];
        $ca_name = explode("/", $name);
        $ca_name = $ca_name[0];
        $data.="      
        {
            \"id\": $cmid,
            \"text\": \"$ca_name\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="project")
{

    if($q!="")
    {
        $wc = "where name like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select id, name from fmm_evms.master_project $wc";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $pmid = $rs->fields["id"];
        $name = $rs->fields["name"];
        $data.="      
        {
            \"id\": $pmid,
            \"text\": \"$name\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="cam")
{

    if($q!="")
    {
        $wc = "where cam like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select id, cam from fmm_evms.master_ca $wc GROUP BY cam";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $cmid = $rs->fields["id"];
        $cam = $rs->fields["cam"];
        $data.="      
        {
            \"id\": \"$cam\",
            \"text\": \"$cam\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="period")
{

    if($q!="")
    {
        $wc = "where TABLE_NAME like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select TABLE_NAME from information_schema.tables WHERE TABLE_SCHEMA = 'cost' $wc";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $TABLE_NAME = $rs->fields["TABLE_NAME"];
        $data.="      
        {
            \"id\": \"$TABLE_NAME\",
            \"text\": \"$TABLE_NAME\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="rpt_type")
{
    $data ="{\"items\": [";
    $data.="      
        {
            \"id\": \"dollars\",
            \"text\": \"Dollars\"
        },{
            \"id\": \"hours\",
            \"text\": \"Hours\"
        }";
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="start_period")
{

    if($q!="")
    {
        $wc = "where TABLE_NAME like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select rpt_period from cost.timephased group by rpt_period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $data.="      
        {
            \"id\": $rpt_period,
            \"text\": \"$rpt_period\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}
if($filter =="end_period")
{

    if($q!="")
    {
        $wc = "where TABLE_NAME like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select rpt_period from cost.timephased group by rpt_period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $data.="      
        {
            \"id\": $rpt_period,
            \"text\": \"$rpt_period\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}

