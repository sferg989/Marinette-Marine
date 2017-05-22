<?php
include('../../../../inc/inc.php');

function checkVal($bcrh_change, $bcrd_change){
    $printval = true;
    if($bcrh_change>1){
        $printval = true;
    }
    if($bcrh_change<-1){
        $printval = true;
    }
    if($bcrd_change>1){
        $printval = true;
    }
    if($bcrd_change<-1){
        $printval = true;
    }
    return $printval;
}
function returnSQL($filter_val,$ship_code, $prev_table,$cur_table, $cur_bcr_table){
    if($filter_val=="all"){
        $sql = "
        select s.ship_code, s.ca, s.wp, prevbac, curbac, prevh, curh, `desc`, sum(hours) hours, sum(bcr.amount) as amount 
            from (
            select
                prev.ship_code,
                prev.ca,
                prev.wp,
                prev.bac prevbac,
                cur.bac curbac,
                prev.bac_hours prevh,
                cur.bac_hours curh
            from $prev_table prev
            INNER JOIN  $cur_table cur
                on prev.ship_code = cur.ship_code
                and prev.ca = cur.ca
                and prev.wp = cur.wp
            where
            prev.ship_code = $ship_code and cur.ship_code = $ship_code
            union
            /*
            Just CUR PERIOD
            */
            select
                cur.ship_code,
                cur.ca,
                cur.wp,
                0 prevbac,
                cur.bac curbac,
                0 prevh,
                cur.bac_hours curh
            from $cur_table cur
            left JOIN  $prev_table prev
                on prev.ship_code = cur.ship_code
                and prev.ca = cur.ca
                and prev.wp = cur.wp
            where
            prev.ship_code is null
            union
            /*
            Just PREV PERIOD
            */
            select
                prev.ship_code,
                prev.ca,
                prev.wp,
                prev.bac prevbac,
                0 curbac,
                prev.bac_hours prevh,
                0 curh
            from $prev_table prev
            left JOIN  $cur_table cur
                on prev.ship_code = cur.ship_code
                and prev.ca = cur.ca
                and prev.wp = cur.wp
            where
            cur.ship_code is null) s
        left join $cur_bcr_table bcr
            on bcr.ship_code = s.ship_code
            AND bcr.ca = s.ca
            AND bcr.wp = s.wp
            where s.ship_code = $ship_code
            and s.wp <> ''
        group by 
        s.ship_code, s.ca, s.wp
        ";
    }
    if($filter_val=="all_bcrs"){
        $sql = "
            select
            ship_code,
            ca,
            wp,
            (select bac from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevbac,
            (select bac from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curbac,
            (select bac_hours from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevh,
            (select bac_hours from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curh,
            `desc`,
            debit,
            credit,
            hours,
            amount
            from $cur_bcr_table bcr where ship_code = $ship_code order by `desc`
        ";
    }
    if($filter_val=="no_ca"){
        $sql = "
            select
                ship_code,
                ca,
                wp,
                (select bac from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevbac,
                (select bac from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curbac,
                (select bac_hours from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevh,
                (select bac_hours from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curh,
                `desc`,
                debit,
                credit,
                hours,
                amount
            from $cur_bcr_table bcr where ship_code = $ship_code 
            and bcr.ca = ''
            order by `desc`
        ";
    }
    if($filter_val=="multiple_ca"){
        $sql = "
            select
            ship_code,
            ca,
            wp,
            (select bac from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevbac,
            (select bac from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curbac,
            (select bac_hours from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevh,
            (select bac_hours from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curh,
            `desc`,
            debit,
            credit,
            hours,
            amount
            from $cur_bcr_table bcr where bcr.ship_code = $ship_code 
            and `desc` like '%and%' order by `desc`
        ";
    }
    return $sql;

}
if($control=="bcm")
{
    $prev_rpt_period = getPreviousRPTPeriod($rpt_period);
    $prev_table      = "cost2.`".$prev_rpt_period . "_cost`";
    $cur_table       = "cost2.`".$rpt_period . "_cost`";
    $cur_bcr_table   = "bcr_log.`".$rpt_period . "_bcr`";
    if(isset($filter_val)==false){
        $filter_val = "all_bcrs";
    }
    $sql = returnSQL($filter_val, $ship_code, $prev_table, $cur_table, $cur_bcr_table);
    //print $sql;
    $data = "[";

    $rs = dbCall($sql, "cost2");

    $id = 1;
    while (!$rs->EOF)
    {
        //die("made it");


        $ship_code   = $rs->fields["ship_code"];
        $ca          = $rs->fields["ca"];
        $wp          = $rs->fields["wp"];
        $desc        = $rs->fields["desc"];
        $prevbac     = formatNumberNoComma($rs->fields["prevbac"]);
        $curbac      = formatNumberNoComma($rs->fields["curbac"]);
        $prevh       = formatNumberNoComma($rs->fields["prevh"]);
        $curh        = formatNumberNoComma($rs->fields["curh"]);
        $bcrh        = formatNumberNoComma($rs->fields["hours"]);
        $bcrd        = formatNumberNoComma($rs->fields["amount"]);
        $change_h    = formatNumberNoComma($curh - $prevh);
        $change_d    = formatNumberNoComma($curbac - $prevbac);
        $bcrh_change = formatNumberNoComma(abs($change_h) - $bcrh);
        $bcrd_change = formatNumberNoComma(abs($change_d) - $bcrd);
        $printval = checkVal($bcrh_change, $bcrd_change);
        if($printval==true){

            $data .= "{
            \"id\"          :$id,
            \"ship_code\"   :\"$ship_code\",
            \"ca\"          :\"$ca\",
            \"wp\"          :\"$wp\",
            \"desc\"        :\"$desc\",
            \"prevh\"       :$prevh,
            \"curh\"        :$curh,
            \"prevbac\"     :$prevbac,
            \"curbac\"      :$curbac,
            \"change_h\"    :$change_h,
            \"bcrh\"        :$bcrh,
            \"bcrd\"        :$bcrd,
            \"change_d\"    :$change_d,
            \"bcrd_change\" :$bcrd_change,
            \"bcrh_change\" :$bcrh_change
        },";
            $id++;
        }
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}


