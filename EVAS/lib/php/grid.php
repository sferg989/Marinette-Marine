<?php
include('../../../inc/inc.php');

if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
/*
 * Do the test and if it fails insert it with the appropriate testID.
 * */
function evPCvsIMSPC($ship_code){


}
$rpt_period = currentRPTPeriod();
function insertTest($test_id, $ship_code, $wp){
    $sql = "INSERT  into results (test_id, ship_code, wp) values ($test_id,$ship_code, '$wp')";
    $junk  = dbCall($sql, "evas");
}

if($control=="project_grid")
{
    $sql = "
            SELECT
             id,
             test_step,
             threshold,
             (select count(*) from results r where r.test_id =evas.id  and ship_code = 477 GROUP BY r. ship_code) AS count
            FROM evas.metrics evas";

    $rs  = dbCall($sql, "evas");
    $data = "[";
    while (!$rs->EOF)
    {
        $id        = $rs->fields["id"];
        $test_step = $rs->fields["test_step"];
        $threshold = $rs->fields["threshold"];
        $count     = formatNumber4decNoComma($rs->fields["count"]);
        $data.="{
            \"id\"          : $id,
            \"threshold\"   : $threshold,
            \"count\"       : $count,
            \"test_step\"   :\"$test_step\"
        },";

        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="load_test_results"){
    deleteFromTable("evas", "results", "ship_code", $ship_code);
    /*test 1*/
    $sql = "
            select * from (
            select 
            c.ship_code,
            c.wp c_wp,
             c.pc c_pc,
             p6.wp,
             (p6.pc*100) p6_pc from cost2.201708_cost c  left join evas.p6 p6
            on c.ship_code = p6.ship_code and
            c.wp = p6.wp
            where c.ship_code = 477 and p6.ev_technique like '%comp%') s where s.c_pc<> s.p6_pc
            ";
    $rs  = dbCall($sql, "evas");
    while (!$rs->EOF)
    {
        $ship_code = $rs->fields["ship_code"];
        $wp        = $rs->fields["c_wp"];
        $c_pc      = $rs->fields["c_pc"];
        $p6_pc     = $rs->fields["p6_pc"];
        $result = abs($c_pc-$p6_pc);
        if($result>1){
            insertTest(1, $ship_code, $wp);
        }
        $rs->MoveNext();
    }
}
if($control=='test_drill_grid'){
    $sql = "select *, (p6_pc-c_pc) as res  from (
            select
                c.ship_code,
                c.wp c_wp,
                 c.pc c_pc,
                 (p6.pc*100) p6_pc from cost2.201708_cost c  left join evas.p6 p6
                on c.ship_code = p6.ship_code and
                c.wp = p6.wp
                where c.ship_code = 477) s where s.p6_pc<> s.c_pc order by res desc";

    $rs  = dbCall($sql, "evas");
    $id = 1;
    $data = "[";
    while (!$rs->EOF)
    {
        $ship_code = $rs->fields["ship_code"];
        $wp        = $rs->fields["c_wp"];
        $c_pc      = $rs->fields["c_pc"];
        $p6_pc     = $rs->fields["p6_pc"];
        $result    = abs($c_pc - $p6_pc);
        if($result>4)
        {
            $data.="{
            \"id\"          : $id,
            \"ship_code\"   : $ship_code,
            \"c_pc\"        : $c_pc,
            \"p6_pc\"       : $p6_pc,
            \"result\"      : $result,
            \"wp\"          :\"$wp\"
        },";
        }


        $rs->MoveNext();
        $id++;
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}