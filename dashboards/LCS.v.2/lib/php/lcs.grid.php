<?php
include('../../../../inc/inc.php');
function returnSQLFields(){
    $sql = "
  ship_code, 
  c3 wbs,
  sum(s) s,
  sum(p) p,
  sum(a) a,
  sum(bac) bac,
  sum(eac) eac,
  sum(eac_hours) eac_h,
  sum(bac_hours) bac_h
    ";
    return $sql;
}
function findDrillLevel($field){
    //print $field;
    if($field=="ship_code"){
        //print "this is the".$field;

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
        $schema = "cost2";
    }
    else {
        $table_suffix  = "_cost";
        $schema = "cost2";
    }
    $data["table_suffix"] = $table_suffix;
    $data["schema"] = $schema;
    return $data;
}

if($control=="lcs_grid")
{
    //var_dump($_REQUEST);
    //print json_decode($metaData);
    if(isset($rpt_period)==false){
        /*date is YYYY-MM-dd*/
        $rpt_period = currentRPTPeriod();
    }

    $wc = "";
    $gb = "";
    $ob = "";
    $sql_fields = "            
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
            est_price";
    $table_name = $rpt_period."_ship";
    $schema = "lcs_log";
    if(isset($metaData) == true){

        $gb_fields = array();

        foreach ($metaData as $key=>$value){
            $field_name = changegridName2FieldName($key);
            $gb_fields[] = $field_name;
            $gb.= "$field_name, ";
            $ob.= "$field_name, ";
            $wc.="$field_name = '$value' and ";
        }
        $array_count = count($gb_fields)-1;
        $last_element = $gb_fields[$array_count];
        $drill_level = findDrillLevel($last_element);
        //print "<br>";
        $gb.="$drill_level";
        $ob.="$drill_level";
        //$gb = substr($gb, 0, -1);
        $wc = substr($wc, 0, -4);
        $wc = "where $wc";
        $gb = "group by $gb";
        $ob = "order by $ob";
        $table_array = returnTableName($drill_level);
        $table_suffix = $table_array["table_suffix"];
        $table_name = $rpt_period."".$table_suffix;
        $schema = $table_array["schema"];
        $sql_fields = returnSQLFields();
    }

    //print $gb."<br>";
    //print $wc."<br>";
    $data = "[";

    $sql = "
        SELECT
          $sql_fields 
            from $schema.$table_name
            $wc $gb
            $ob
  ";
    $i=0;
    //print $sql;
    if(isset($level)==false){
        $level = "program";
    }

    $rs = dbCall($sql, $schema);
    while (!$rs->EOF)
    {
        //die("made it");


        $ship_code = $rs->fields["ship_code"];
        $wbs= $rs->fields["wbs"];
        $bcr = $rs->fields["bcr"];
        $s   = $rs->fields["a"];
        $p   = formatNumber4decNoComma($rs->fields["p"]);
        $a   = formatNumber4decNoComma($rs->fields["s"]);
        $db  = formatNumber4decNoComma($rs->fields["db"]);
        $mr  = formatNumber4decNoComma($rs->fields["mr"]);
        $ub  = formatNumber4decNoComma($rs->fields["ub"]);
        $sv   = $p-$s;
        $cv   = $p-$a;

        $data .= "{
            \"level\":\"$level\",
            \"id\"   :\"$i\",
            \"Hull\":\"$ship_code\",
            \"wbs\":\"$wbs\",
            \"s\"   :$s,
            \"p\"   :$p,
            \"a\"   :$a,
            \"sv\"  :$sv,
            \"cv\"  :$cv,
            \"db\"  :$db,
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
if($control=="deliq_grid"){

}


