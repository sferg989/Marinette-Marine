<?php
include("../../inc/inc.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 2/10/2017
 * Time: 1:22 PM
 */
$schema = "bac_eac";
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
if($control=="data_check")
{
    if($code>=477){
        $table_name   = $rpt_period . "_cpr1d";
    }
    else{
        $table_name   = $rpt_period . "_pre17_cpr1d";
    }
    $cur_period = "false";
    $prev_period = "false";
    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table==true)
    {
        $sql = "select count(*) as count from $table_name where ship_code = $code";
        //print $sql;
        $rs = dbCall($sql,$schema);
        $count = $rs->fields["count"];
        if($count>0){
            $cur_period = "true";
        }
    }

    if($code>=477){
        $table_name   = $prev_rpt_period . "_cpr1d";
    }
    else{
        $table_name   = $prev_rpt_period . "_pre17_cpr1d";
    }
    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table==true)
    {
        $sql = "select count(*) as count from $table_name where ship_code = $code";
        $rs = dbCall($sql,$schema);

        $count = $rs->fields["count"];
        if($count>0){
            $prev_period = "true";
        }
    }
    $checks = "cur:$cur_period,prev:$prev_period";
    print $checks;
}