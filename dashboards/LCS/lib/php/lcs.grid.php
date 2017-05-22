<?php
include('../../../../inc/inc.php');
function findDrillLevel($field){
    print $field;
    if($field=="ship_code"){
        print "this is the".$field;

        $drill_level = "c3";
    }
    if($field=="c3"){
        $drill_level = "ca";
    }
    if($field=="ca"){
        $drill_level = "wp";
    }
    return $drill_level;
}
function changegridName2FieldName($grid_name){
    if($grid_name=="Hull"){
        $field_name = "ship_code";
    }
    else if($grid_name=="WBS"){
        $field_name = "c3";
    }
    else{
        $field_name = $grid_name;
    }
    return $field_name;
}
function returnTableName($cur_level){
    if($cur_level=="ship_code"){
        $table_suffix  = "_ship";
        $schema = "lcs_log";
    }
    else {
        $table_suffix  = "_ship";
        $schema = "cost2";
    }
    $data["table_suffix"] = $table_suffix;
    $data["schema"] = $schema;
}

if($control=="lcs_grid")
{


    $rpt_period = currentRPTPeriod();
    $rpt_period = getPreviousRPTPeriod($rpt_period);

    $data = "[";
    $table_name = $rpt_period."_ship";

    $sql = "
        SELECT
            ship_code,
            ctc,
            auw,
            otc,
            cbb,
            fee,
            mr,
            ub,
            eac_best,
            eac_worst,
            a,
            a_hours,
            p,
            p_hours,
            s,
            s_hours,
            bac,
            bac_h,
            eac,
            eac_h,
            est_price 
            from lcs_log.$table_name
            order by ship_code
    ";
    $i=0;
    //print $sql;

    $rs = dbCall($sql, "lcs_log");
    while (!$rs->EOF)
    {

        $ship_code = $rs->fields["ship_code"];
        $bcr = $rs->fields["bcr"];
        $s   = $rs->fields["s"];
        $p   = formatNumber4decNoComma($rs->fields["p"]);
        $a   = formatNumber4decNoComma($rs->fields["a"]);
        $mr  = formatNumber4decNoComma($rs->fields["mr"]);
        $ub  = formatNumber4decNoComma($rs->fields["ub"]);
        $bac  = formatNumber4decNoComma($rs->fields["bac"]);
        $eac  = formatNumber4decNoComma($rs->fields["eac"]);
        $sv   = $p-$s;
        $cv   = $p-$a;

        $data .= "{
            \"id\"   :\"$i\",
            \"Hull\":\"$ship_code\",
            \"s\"   :$s,
            \"p\"   :$p,
            \"a\"   :$a,
            \"bac\" :$bac,
            \"eac\" :$eac,
            \"sv\"  :$sv,
            \"cv\"  :$cv,
            \"mr\"  :$mr,
            \"ub\"  :$ub
            },";
        $i++;
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}
if($control=="chart_grid"){

}


