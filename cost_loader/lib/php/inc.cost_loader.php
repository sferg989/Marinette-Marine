<?php

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 5/3/2017
 * Time: 9:01 AM
 */

function loadCOBRABCRLOG($ship_code, $rpt_period, $table_name, $ship_code_wc){
    $year = intval(substr($rpt_period, 0, 4));
    $month = month2digit(substr($rpt_period, -2));
    $day  = getMonthEndDay($rpt_period);
    if($day<5){
        $month = $month+1;
    }

    $sql = "
        select 
            PROGRAM,
            CA1,
            CA2,
            WP,
            LOGCOMMENT,
            DEBIT,  
            CREDIT,
            HOURS,
            AMOUNT 
        from BASELOG 
        where PROGRAM = '$ship_code_wc' 
            and  (DATEPART(yy, STATUSDATE) = $year
            AND    DATEPART(mm, STATUSDATE) = $month
            AND    DATEPART(dd, STATUSDATE) =$day)
            and LOGCOMMENT not like '%log%'
    ";
    print $sql;
    $rs = dbCallCobra($sql);
    $insert_sql = "
    insert into bcr_log.".$table_name." 
        (ship_code,
        ca,
        ca2,
        wp,
        `desc`,
        debit,
        credit,
        hours,
        amount) 
        values
 ";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ca     = addslashes(trim($rs->fields["CA1"]));
        $ca2    = addslashes(trim($rs->fields["CA1"]));
        $wp     = addslashes(trim($rs->fields["WP"]));
        $desc   = addslashes(trim($rs->fields["LOGCOMMENT"]));
        $debit  = addslashes(trim($rs->fields["DEBIT"]));
        $credit = addslashes(trim($rs->fields["CREDIT"]));
        $hours  = formatNumber4decNoComma($rs->fields["HOURS"]);
        $amt    = formatNumber4decNoComma($rs->fields["AMOUNT"]);

        $sql.= "(
        $ship_code,
        '$ca',
        '$ca2',
        '$wp',
        '$desc',
        '$debit',
        '$credit',
        $hours,
        $amt),";
        if($i==500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,"bcr_log");
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);

        $junk = dbCall($sql,"bcr_log");
        $sql = $insert_sql;
    }
}
function getStageInsertSQL($table_name, $schema){
    $insert_sql = "
        insert into $schema.".$table_name." (
        ship_code,
        ca,
        wp,
        descr,
        c1,
        c2,
        c3,
        s,
        p,
        a,
        bac,
        eac,
        s_hours,
        p_hours,
        a_hours,
        bac_hours,
        eac_hours,
        pc,
        ssd, sfd, asd, afd, esd, efd, lsd, lfd) values
 ";
    return $insert_sql;
}
function insertCobraCostDataStage($ship_code, $schema, $table_name, $ship_code_wc){
    $sql = "
    select
        PROGRAM,
        CA1,
        C1,
        C2,
        C3,
        WP,
        ssd, sfd, asd, afd, esd, efd, lsd, lfd,
        DESCRIP,
        BCWS,
        BCWP,
        ACWP,
        BAC,
        EAC,
        BCWS_HRS,
        BCWP_HRS,
        ACWP_HRS,
        BAC_HRS,
        EAC_HRS,
        PC_COMP
    from CAWP where PROGRAM = '$ship_code_wc'
    ";

    $rs = dbCallCobra($sql);
    $insert_sql = getStageInsertSQL($table_name,$schema);
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ca        = addslashes(trim($rs->fields["CA1"]));
        $wp        = addslashes(trim($rs->fields["WP"]));
        $descr     = addslashes(trim($rs->fields["DESCRIP"]));
        $c1        = addslashes(trim($rs->fields["C1"]));
        $c2        = addslashes(trim($rs->fields["C2"]));
        $c3        = addslashes(trim($rs->fields["C3"]));
        $s         = formatNumber4decCobra($rs->fields["BCWS"]);
        $p         = formatNumber4decCobra($rs->fields["BCWP"]);
        $a         = formatNumber4decCobra($rs->fields["ACWP"]);
        $bac       = formatNumber4decCobra($rs->fields["BAC"]);
        $eac       = formatNumber4decCobra($rs->fields["EAC"]);
        $s_hours   = formatNumber4decCobra($rs->fields["BCWS_HRS"]);
        $p_hours   = formatNumber4decCobra($rs->fields["BCWP_HRS"]);
        $a_hours   = formatNumber4decCobra($rs->fields["ACWP_HRS"]);
        $bac_hours = formatNumber4decCobra($rs->fields["BAC_HRS"]);
        $eac_hours = formatNumber4decCobra($rs->fields["EAC_HRS"]);
        $pc        = formatNumber4decCobra($rs->fields["PC_COMP"]);
        $ssd       = fixExcelDateMySQL($rs->fields["ssd"]);
        $sfd       = fixExcelDateMySQL($rs->fields["sfd"]);
        $asd       = fixExcelDateMySQL($rs->fields["asd"]);
        $afd       = fixExcelDateMySQL($rs->fields["afd"]);
        $esd       = fixExcelDateMySQL($rs->fields["esd"]);
        $efd       = fixExcelDateMySQL($rs->fields["efd"]);
        $lsd       = fixExcelDateMySQL($rs->fields["lsd"]);
        $lfd       = fixExcelDateMySQL($rs->fields["lfd"]);

        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$descr',
            '$c1',
            '$c2',
            '$c3',
            $s,
            $p,
            $a,
            $bac,
            $eac,
            $s_hours,
            $p_hours,
            $a_hours,
            $bac_hours,
            $eac_hours,
            $pc,
            '$ssd', '$sfd', '$asd', '$afd', '$esd', '$efd', '$lsd', '$lfd'),";
        if($i==500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,$schema);

            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }

}
function returnCurVal($cur, $prev){

    if($cur==$prev){
        $cur_val = 0;
        return $cur_val;
    }
    $cur_val = formatNumber4decCobra($cur-$prev);
    return $cur_val;
}
function returnCurInsertSQL($schema, $cur_table_name){
    $insert_sql = "
        insert into $schema.".$cur_table_name." (
        ship_code,
        ca,
        wp,
        descr,
        c1,
        c2,
        c3,
        s,
        s_cur,
        p,
        p_cur,
        a,
        a_cur,
        bac,
        bac_diff,
        eac,
        eac_diff,
        s_hours,
        s_hours_cur,
        p_hours,
        p_hours_cur,
        a_hours,
        a_hours_cur,
        bac_hours,
        bac_hours_diff,
        eac_hours,
        eac_hours_diff,
        cv_cur,
        cv_cum,
        sv_cum,
        sv_cur,
        vac,
        cv_cur_h,
        cv_cum_h,
        sv_cum_h,
        sv_cur_h,
        vac_h,
        pc,ssd, sfd, asd, afd, esd, efd, lsd, lfd
) values
 ";
    return $insert_sql;
}
function insertCobraCostDataCur($ship_code, $schema, $stage_table_name, $cur_table_name, $prev_table_name){
    $sql = "
        select
        stage.ship_code,
            stage.ca,
            stage.wp,
            stage.descr,
            stage.c1,
            stage.c2,
            stage.c3,
            stage.s,
            prev.s s_prev,
            stage.p,
            prev.p p_prev,
            prev.a a_prev,
            stage.a,
            prev.bac bac_prev,
            stage.bac,
            prev.eac eac_prev,
            stage.eac,
            prev.s_hours s_hours_prev,
            stage.s_hours,
            prev.p_hours p_hours_prev,
            stage.p_hours,
            prev.a_hours a_hours_prev,
            stage.a_hours,
            prev.bac_hours bac_hours_prev,
            stage.bac_hours,
            prev.eac_hours eac_hours_prev,
            stage.eac_hours,
            stage.pc,
            stage.ssd, stage.sfd, stage.asd, stage.afd, stage.esd, stage.efd, stage.lsd, stage.lfd
        from cost2.".$stage_table_name." stage
        left join
        $schema.$prev_table_name prev on
        stage.ship_code = prev.ship_code
        and stage.ca = prev.ca
        and stage.wp = prev.wp
        where stage.ship_code = $ship_code
 ";
    print $sql;
    $rs = dbCall($sql, $schema);
    $insert_sql = returnCurInsertSQL($schema, $cur_table_name);
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ca             = addslashes(trim($rs->fields["ca"]));
        $wp             = addslashes(trim($rs->fields["wp"]));
        $descr          = addslashes(trim($rs->fields["descr"]));
        $c1             = addslashes(trim($rs->fields["c1"]));
        $c2             = addslashes(trim($rs->fields["c2"]));
        $c3             = addslashes(trim($rs->fields["c3"]));
        $s              = formatNumber4decCobra($rs->fields["s"]);
        $s_prev         = formatNumber4decCobra($rs->fields["s_prev"]);
        $s_cur          = returnCurVal($s, $s_prev);
        $p              = formatNumber4decCobra($rs->fields["p"]);
        $p_prev         = formatNumber4decCobra($rs->fields["p_prev"]);
        $p_cur          = returnCurVal($p, $p_prev);
        $a              = formatNumber4decCobra($rs->fields["a"]);
        $a_prev         = formatNumber4decCobra($rs->fields["a_prev"]);
        $a_cur          = returnCurVal($a, $a_prev);
        $bac            = formatNumber4decCobra($rs->fields["bac"]);
        $bac_prev       = formatNumber4decCobra($rs->fields["bac_prev"]);
        $bac_diff       = returnCurVal($bac, $bac_prev);
        $eac            = formatNumber4decCobra($rs->fields["eac"]);
        $eac_prev       = formatNumber4decCobra($rs->fields["eac_prev"]);
        $eac_diff       = returnCurVal($eac, $eac_prev);
        $s_hours        = formatNumber4decCobra($rs->fields["s_hours"]);
        $s_hours_prev   = formatNumber4decCobra($rs->fields["s_hours_prev"]);
        $s_hours_cur    = returnCurVal($s_hours, $s_hours_prev);
        $p_hours        = formatNumber4decCobra($rs->fields["p_hours"]);
        $p_hours_prev   = formatNumber4decCobra($rs->fields["p_hours_prev"]);
        $p_hours_cur    = returnCurVal($p_hours, $p_hours_prev);
        $a_hours        = formatNumber4decCobra($rs->fields["a_hours"]);
        $a_hours_prev   = formatNumber4decCobra($rs->fields["a_hours_prev"]);
        $a_hours_cur    = returnCurVal($a_hours, $a_hours_prev);
        $bac_hours      = formatNumber4decCobra($rs->fields["bac_hours"]);
        $bac_hours_prev = formatNumber4decCobra($rs->fields["bac_hours_prev"]);
        $bac_hours_diff = returnCurVal($bac_hours, $bac_hours_prev);
        $eac_hours      = formatNumber4decCobra($rs->fields["eac_hours"]);
        $eac_hours_prev = formatNumber4decCobra($rs->fields["eac_hours_prev"]);
        $eac_hours_diff = returnCurVal($eac_hours, $eac_hours_prev);
        $pc         = formatNumber4decCobra($rs->fields["pc"]);
        $cv_cum     = formatNumber4decCobra($p - $a);
        $cv_cur     = formatNumber4decCobra($p_cur - $a_cur);
        $sv_cum     = formatNumber4decCobra($p - $s);
        $sv_cur     = formatNumber4decCobra($p_cur - $s_cur);
        $vac        = formatNumber4decCobra($bac - $eac);
        $cv_cum_h = formatNumber4decCobra($p_hours - $a_hours);
        $cv_cur_h = formatNumber4decCobra($p_hours_cur - $a_hours_cur);
        $sv_cum_h = formatNumber4decCobra($p_hours - $s_hours);
        $sv_cur_h = formatNumber4decCobra($p_hours_cur - $s_hours_cur);
        $vac_h = formatNumber4decCobra($bac_hours - $eac_hours);
        $ssd = fixExcelDateMySQL($rs->fields["ssd"]);
        $sfd = fixExcelDateMySQL($rs->fields["sfd"]);
        $asd = fixExcelDateMySQL($rs->fields["asd"]);
        $afd = fixExcelDateMySQL($rs->fields["afd"]);
        $esd = fixExcelDateMySQL($rs->fields["esd"]);
        $efd = fixExcelDateMySQL($rs->fields["efd"]);
        $lsd = fixExcelDateMySQL($rs->fields["lsd"]);
        $lfd = fixExcelDateMySQL($rs->fields["lfd"]);

        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$descr',
            '$c1',
            '$c2',
            '$c3',
            $s,
            $s_cur,
            $p,
            $p_cur,
            $a,
            $a_cur,
            $bac,
            $bac_diff,
            $eac,
            $eac_diff,
            $s_hours,
            $s_hours_cur,
            $p_hours,
            $p_hours_cur,
            $a_hours,
            $a_hours_cur,
            $bac_hours,
            $bac_hours_diff,
            $eac_hours,
            $eac_hours_diff,
            $cv_cur,
            $cv_cum,
            $sv_cum,
            $sv_cur,
            $vac,
            $cv_cur_h,
            $cv_cum_h,
            $sv_cum_h,
            $sv_cur_h,
            $vac_h,
            $pc,
'$ssd', '$sfd', '$asd', '$afd', '$esd', '$efd', '$lsd', '$lfd'),";

        if($i==500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,$schema);
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        print $sql;
        $sql = substr($sql, 0, -1);

        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }

}
function loadLCSProgramData($ship_code, $table_name, $ship_code_wc){
    $sql = "
     select
            PROGRAM,
            ctc,
            auw,
            otc,
            cbb,
            fee,
            mr,
            ub,
            eac_best,
            eac_worst,
            acwp,
            acwp_hrs,
            bcwp,
            bcwp_hrs,
            bcws,
            bcws_hrs,
            bac,
            bac_hrs,
            eac,
            eac_hrs,
            estprice
             from
          program
          where program = '$ship_code_wc'
     ";
    $rs = dbCallCobra($sql);
    $schema = "lcs_log";
    $insert_sql = "
        INSERT into $schema.$table_name
            (ship_code,
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
            est_price)
          values

 ";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {

        $ctc       = formatNumber4decNoComma($rs->fields["ctc"]);
        $auw       = formatNumber4decNoComma($rs->fields["auw"]);
        $otc       = formatNumber4decNoComma($rs->fields["otc"]);
        $cbb       = formatNumber4decNoComma($rs->fields["cbb"]);
        $fee       = formatNumber4decNoComma($rs->fields["fee"]);
        $mr        = formatNumber4decNoComma($rs->fields["mr"]);
        $ub        = formatNumber4decNoComma($rs->fields["ub"]);
        $eac_best  = formatNumber4decNoComma($rs->fields["eac_best"]);
        $eac_worst = formatNumber4decNoComma($rs->fields["eac_worst"]);
        $s         = formatNumber4decNoComma($rs->fields["bcws"]);
        $p         = formatNumber4decNoComma($rs->fields["bcwp"]);
        $a         = formatNumber4decNoComma($rs->fields["acwp"]);
        $bac       = formatNumber4decNoComma($rs->fields["bac"]);
        $eac       = formatNumber4decNoComma($rs->fields["eac"]);
        $s_hours   = formatNumber4decNoComma($rs->fields["bcws_hrs"]);
        $p_hours   = formatNumber4decNoComma($rs->fields["bcwp_hrs"]);
        $a_hours   = formatNumber4decNoComma($rs->fields["acwp_hrs"]);
        $bac_hours = formatNumber4decNoComma($rs->fields["bac_hrs"]);
        $eac_hours = formatNumber4decNoComma($rs->fields["eac_hrs"]);
        $est_price = formatNumber4decNoComma($rs->fields["estprice"]);

        $sql.="(
            $ship_code,
            $ctc,
            $auw,
            $otc,
            $cbb,
            $fee,
            $mr,
            $ub,
            $eac_best,
            $eac_worst,
            $a,
            $a_hours,
            $p,
            $p_hours,
            $s,
            $s_hours,
            $bac,
            $bac_hours,
            $eac,
            $eac_hours,
            $est_price
            ),";
        if($i==500){
            $sql = substr($sql, 0, -1);

            $junk = dbCall($sql,$schema);
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        print $sql;

        $junk = dbCall($sql,$schema);

        $sql = $insert_sql;
    }
}
function getRPTFORSHIPCODE($ship_code){
    if($ship_code<=481){
        $rpt_period_array[$ship_code."0116"] = 201601;
        $rpt_period_array[$ship_code."0216"] = 201602;
        $rpt_period_array[$ship_code."0316"] = 201603;
        $rpt_period_array[$ship_code."0416"] = 201604;
        $rpt_period_array[$ship_code."0516"] = 201605;
        $rpt_period_array[$ship_code."0616"] = 201606;
        $rpt_period_array[$ship_code."0716"] = 201607;
        $rpt_period_array[$ship_code."0816"] = 201608;
        $rpt_period_array[$ship_code."0916"] = 201609;
        $rpt_period_array[$ship_code."1016"] = 201610;
        $rpt_period_array[$ship_code."1116"] = 201611;
        $rpt_period_array[$ship_code."1216"] = 201612;
        $rpt_period_array[$ship_code."0117"] = 201701;
        $rpt_period_array[$ship_code."0217"] = 201702;
    }
    elseif ($ship_code==483){
        $rpt_period_array[$ship_code."0216"] = 201602;
        $rpt_period_array[$ship_code."0316"] = 201603;
        $rpt_period_array[$ship_code."0416"] = 201604;
        $rpt_period_array[$ship_code."0516"] = 201605;
        $rpt_period_array[$ship_code."0616"] = 201606;
        $rpt_period_array[$ship_code."0716"] = 201607;
        $rpt_period_array[$ship_code."0816"] = 201608;
        $rpt_period_array[$ship_code."0916"] = 201609;
        $rpt_period_array[$ship_code."1016"] = 201610;
        $rpt_period_array[$ship_code."1116"] = 201611;
        $rpt_period_array[$ship_code."1216"] = 201612;
        $rpt_period_array[$ship_code."0117"] = 201701;
        $rpt_period_array[$ship_code."0217"] = 201702;
    }
    elseif ($ship_code==485){
        $rpt_period_array[$ship_code."0616"] = 201606;
        $rpt_period_array[$ship_code."0716"] = 201607;
        $rpt_period_array[$ship_code."0816"] = 201608;
        $rpt_period_array[$ship_code."0916"] = 201609;
        $rpt_period_array[$ship_code."1016"] = 201610;
        $rpt_period_array[$ship_code."1116"] = 201611;
        $rpt_period_array[$ship_code."1216"] = 201612;
        $rpt_period_array[$ship_code."0117"] = 201701;
        $rpt_period_array[$ship_code."0217"] = 201702;
    }
    return $rpt_period_array;

}
function insertCobraCostData( $ship_code, $schema, $rpt_period, $ship_code_wc){

    $stage_table_name   = $rpt_period . "_stage";
    $create_table = checkIfTableExists($schema, $stage_table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema, "template_stage", $stage_table_name);
    }
    deleteShipFromTable($ship_code,$stage_table_name, $schema);

    insertCobraCostDataStage($ship_code, $schema, $stage_table_name, $ship_code_wc);

    $prev_rpt_period = getPreviousRPTPeriod($rpt_period);
    $prev_table_name = $prev_rpt_period . "_cost";

    $cur_table_name   = $rpt_period . "_cost";
    $create_table = checkIfTableExists($schema, $cur_table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema, "template_cost", $cur_table_name);
    }
    deleteShipFromTable($ship_code,$cur_table_name, $schema);

    insertCobraCostDataCur($ship_code, $schema, $stage_table_name, $cur_table_name, $prev_table_name);


    $table_name   = $rpt_period . "_ship";
    $create_table = checkIfTableExists("lcs_log", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("lcs_log", "template_ship", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "lcs_log");
    loadLCSProgramData($ship_code, $table_name, $ship_code_wc);

    $table_name   = $rpt_period . "_bcr";
    $create_table = checkIfTableExists("bcr_log", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("bcr_log","template_bcr", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "bcr_log");
    loadCOBRABCRLOG($ship_code, $rpt_period, $table_name, $ship_code_wc);
}