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
function getLastWKLYREPORTTBLNAME(){
    $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = 'weekly_performance_report' AND TABLE_NAME <> 'summary'
        AND TABLE_NAME <> 'template_summary'
    ";
    $rs           = dbCall($sql, "information_schema");
    $latest_table = $rs->fields["TABLE_NAME"];
    return $latest_table;
}
function returnSQLSTMT($top_five_type, $table_name, $ship_code, $data_type){
    $data_type_suffix = "";
    if($data_type=="hours"){
        $data_type_suffix = "_h";
    }
    switch ($top_five_type) {
        case "cv_cum_unfav":
            $sql = "
              select 
                ca,  
                c1, 
                cv_cum$data_type_suffix 
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by cv_cum  limit 5";
        break;
        case "cv_cum_fav":
            $sql = "
              select 
                ca,  
                c1, 
                cv_cum$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by cv_cum  desc limit 5";
            break;
        case "sv_cum_unfav":
            $sql = "
              select 
                ca,  
                c1, 
                sv_cum$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by sv_cum limit 5";
            break;
        case "sv_cum_fav":
            $sql = "
              select 
                ca,  
                c1, 
                sv_cum$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by sv_cum desc limit 5";
            break;
        case "cv_cur_unfav":
            $sql = "
              select 
                ca,  
                c1, 
                cv_cur$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by cv_cur  limit 5";
        break;
        case "cv_cur_fav":
            $sql = "
              select 
                ca,  
                c1, 
                cv_cur$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by cv_cur  desc limit 5";
            break;
        case "sv_cur_unfav":
            $sql = "
              select 
                ca,  
                c1, 
                sv_cur$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by sv_cur limit 5";
            break;
        case "sv_cur_fav":
            $sql = "
              select 
                ca,  
                c1, 
                sv_cur$data_type_suffix  
              from cost2.$table_name 
                where ship_code = $ship_code 
              and wp = '' 
              order by sv_cur desc limit 5";
            break;
    }
    return $sql;
}
function returnfieldnameForRS($top_five_type, $data_type){
    $data_type_suffix = "";
    $data_rs_field    = "";
    if($data_type=="hours"){
        $data_type_suffix = "_h";
    }
    switch ($top_five_type) {
        case "cv_cum_unfav" :
        case "cv_cum_fav"   :
        $data_rs_field = "cv_cum$data_type_suffix";
            break;
        case "sv_cum_unfav" :
        case "sv_cum_fav"   :
        $data_rs_field = "sv_cum$data_type_suffix";
            break;
        case "cv_cur_unfav" :
        case "cv_cur_fav"   :
        $data_rs_field = "cv_cur$data_type_suffix";
            break;
        case "sv_cur_unfav" :
        case "sv_cur_fav"   :
            $data_rs_field = "sv_cur$data_type_suffix";
            break;
    }
    return $data_rs_field;
}
if($control=="lcs_grid")
{
    //var_dump($_REQUEST);
    //print json_decode($metaData);
    $wc = "";
    $gb = "";
    if(isset($rpt_period)==false){
        /*date is YYYY-MM-dd*/
        $rpt_period = currentRPTPeriod();
        $rpt_period = getPreviousRPTPeriod($rpt_period);
    }

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
            order by ship_code
  ";
    $i=0;
    if(isset($level)==false){
        $level = "program";
    }

    $rs = dbCall($sql, "lcs_log");
    while (!$rs->EOF)
    {
        //die("made it");


        $ship_code = $rs->fields["ship_code"];
        $bcr       = $rs->fields["bcr"];
        $s         = $rs->fields["s"];
        $p         = formatNumber4decNoComma($rs->fields["p"]);
        $a         = formatNumber4decNoComma($rs->fields["a"]);
        $db        = formatNumber4decNoComma($rs->fields["db"]);
        $mr        = formatNumber4decNoComma($rs->fields["mr"]);
        $ub        = formatNumber4decNoComma($rs->fields["ub"]);
        $bac       = formatNumber4decNoComma($rs->fields["bac"]);
        $eac_best  = formatNumber4decNoComma($rs->fields["eac_best"]);
        $eac_worst = formatNumber4decNoComma($rs->fields["eac_worst"]);
        $bac       = formatNumber4decNoComma($rs->fields["bac"]);
        $bac       = formatNumber4decNoComma($rs->fields["bac"]);
        $eac       = formatNumber4decNoComma($rs->fields["eac"]);
        $sv        = $p - $s;
        $cv        = $p - $a;
        $vac       = $bac - $eac;
        $tcpi      = evalTCPI($a, $p, $bac, $eac);
        $spi       = formatNumber4decNoComma($p / $s);
        $cpi       = formatNumber4decNoComma($p / $a);
        $pc        = formatNumber4decNoComma($a / $eac);
        $ps        = formatNumber4decNoComma($a / $bac);
        $spi_color = cpispiColors($spi, "spi", true);
        $spi_bk    = $spi_color[0];
        $spi_font  = $spi_color[1];

        $cpi_color = cpispiColors($cpi, "cpi", true);
        $cpi_bk    = $cpi_color[0];
        $cpi_font  = $cpi_color[1];

        $tcpi_color = cpispiColors($tcpi, "tcpi", true);
        $tcpi_bk    = $tcpi_color[0];
        $tcpi_font  = $tcpi_color[1];

        $eac_color  = eacColor($eac, $tcpi, $cpi, $cv, $vac, $eac, $pc, $ps, $bac, $eac_best);
        $eac_bk     = $eac_color[0];
        $eac_font   = $eac_color[1];
        $ps = $ps*100;
        $pc = $pc*100;
        $data .= "{
            \"id\"          :\"$i\",
            \"Hull\"        :\"$ship_code\",
            \"eac_color\"   :\"$eac_bk\",
            \"eac_font\"    :\"$eac_font\",
            \"spi\"         :\"$spi\",
            \"spi_color\"   :\"$spi_bk\",
            \"spi_font\"    :\"$spi_font\",
            \"cpi\"         :\"$cpi\",
            \"cpi_color\"   :\"$cpi_bk\",
            \"cpi_font\"    :\"$cpi_font\",
            \"tcpi\"        :\"$tcpi\",
            \"tcpi_color\"  :\"$tcpi_bk\",
            \"tcpi_font\"   :\"$tcpi_font\",
            \"s\"           :$s,
            \"p\"           :$p,
            \"a\"           :$a,
            \"bac\"         :$bac,
            \"eac\"         :$eac,
            \"vac\"         :$vac,
            \"eac_best\"    :$eac_best,
            \"eac_worst\"   :$eac_worst,
            \"sv\"  :$sv,
            \"cv\"  :$cv,
            \"db\"  :$db,
            \"mr\"  :$mr,
            \"pc\"  :$pc,
            \"ps\"  :$ps,
            \"ub\"  :$ub
        },";
        $i++;
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}
if($control=="wo_grid")
{
    //var_dump($_REQUEST);
    //print json_decode($metaData);
    $wc = "";
    $gb = "";

    //print $gb."<br>";
    //print $wc."<br>";
    $data = "[";

    $table_name = getLastWKLYREPORTTBLNAME();

    $sql = "
        select
            ca,
            activity,
            item,
            scope,
            soc,
            wo,
            resource,
            a,
            progress,
            eac,
            eac_growth,
            bac_cpi,
            bac,
            p2bac,
            bl_start,
            bl_finish,
            f_start,
            f_finish
        from 
        weekly_performance_report.$table_name 
        where project = '$ship_code' 
        and progress <> 100 and progress <> 0 
        and scope not like '%loe%'
        order by eac_growth
";
    $i=0;
    $data = "[";
    $latestDate = substr($table_name, 2);
    //print $sql;
    $rs = dbCall($sql, "weekly_performance_report");
    while (!$rs->EOF)
    {

        $ca         = $rs->fields["ca"];
        $wp         = $rs->fields["activity"];
        $soc         = returnSOCName($rs->fields["soc"]);
        $item       = $rs->fields["item"];
        $scope      = $rs->fields["scope"];
        $wo         = $rs->fields["wo"];
        $rsrc       = $rs->fields["resource"];
        $a          = formatNumber4decNoComma($rs->fields["a"]);
        $pc         = formatNumber4decNoComma($rs->fields["progress"]);
        $eac        = formatNumber4decNoComma($rs->fields["eac"]);
        $bac        = formatNumber4decNoComma($rs->fields["bac"]);
        $eac_growth = formatNumber4decNoComma($rs->fields["eac_growth"]);
        $bac_cpi    = formatNumber4decNoComma($rs->fields["bac_cpi"]);
        $p2bac      = formatNumber4decNoComma($rs->fields["p2bac"]);
        $bl_start   = $rs->fields["bl_start"];
        $bl_finish  = $rs->fields["bl_finish"];
        $f_start    = $rs->fields["f_start"];
        $f_finish   = $rs->fields["f_finish"];

        $data .= "{
            \"latestDate\"   :\"$latestDate\",
            \"id\"   :\"$i\",
            \"wo\"   :\"$wo\",
            \"wp\"   :\"$wp\",
            \"soc\"   :\"$soc\",
            \"ca\"   :\"$ca\",
            \"wp\"   :\"$wp\",
            \"item\" :\"$item\",
            \"scope\":\"$scope\",
            \"rsrc\" :\"$rsrc\",
            \"a\"    :$a,
            \"pc\"   :$pc,
            \"eac\"  :$eac,
            \"eac_growth\"   :$eac_growth,
            \"bac_cpi\"   :$bac_cpi,
            \"bac\"   :$bac,
            \"p2bac\"  :$p2bac
        },";
        $i++;
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}
if($control == "top_5_grid"){
    if(isset($rpt_period)==false){
        /*date is YYYY-MM-dd*/
        $rpt_period = currentRPTPeriod();
        $rpt_period = getPreviousRPTPeriod($rpt_period);
    }
    $table_name = "`".$rpt_period."_cost`";
    $sql = returnSQLSTMT($top_five_type,$table_name,$ship_code, $data_type);
    //print $sql;
    $i=0;
    $data = "[";
    $rs_field = returnfieldnameForRS($top_five_type,$data_type);
    $rs = dbCall($sql, "cost2");

    while (!$rs->EOF)
    {
        $ca        = addslashes($rs->fields["ca"]);
        $cam       = $rs->fields["c1"];
        $field_val = formatNumber4decNoComma($rs->fields["$rs_field"]);

        $data .= "{
            \"id\"   :\"$i\",
            \"ca\"   :\"$ca\",
            \"cam\"  :\"$cam\",
            \"data_type\":\"$data_type\",
            \"val\"  :\"$field_val\"
        },";
        $i++;
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}


