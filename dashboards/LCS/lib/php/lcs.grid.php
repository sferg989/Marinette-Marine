<?php
include('../../../../inc/inc.php');

if($control=="lcs_grid")
{
    //var_dump($_REQUEST);
    //print json_decode($metaData);
    $wc = "";
    foreach ($metaData as $key=>$value){
        $gb = "$key,";
        $wc.="$key = '$value' and ";
    }
    $gb = substr($gb, 0, -1);
    $wc = substr($wc, 0, -4);
    //print $gb."<br>";
    //print $wc."<br>";
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
  ";
    $i=0;
    if($level==""){
        $level = "program";
    }
    $rs = dbCall($sql, "processing_status");
    while (!$rs->EOF)
    {
        //die("made it");


        $ship_code = $rs->fields["ship_code"];
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
    \"level\"          :\"$level\",
    \"id\"          :\"$i\",
    \"Hull\"          :\"$ship_code\",
    \"s\"         :$s,
    \"p\"         :$p,
    \"a\"         :$a,
    \"sv\"         :$sv,
    \"cv\"         :$cv,
    \"db\"              :$db,
    \"mr\"              :$mr,
    \"ub\"              :$ub
        },";
        $i++;
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}


