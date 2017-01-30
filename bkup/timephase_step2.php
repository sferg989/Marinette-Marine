<?php
include('inc/inc.php');
/*
 * step 1 -make sure all the CA's are in CA master
 * step 2 - insert all ca's with period, and S
 * step 3 - update table with corresponding P, A, EAC.
 * - looping through every record in the cost.timepashed table using the wbs_id and the rpt period to find the
 * corresponding record in the fmm_evms.timephased but with a different cost set.  We have S inserted, now we need P, A and EAC.
 * we update that record with each value.  So we are running 3 updates on each record in the entire table.
 *
 * */


function updateCostTimephasedRecord($period, $wbs_id, $amt)
{
    $p_dollars   = floatval($amt["p"]);
    $a_dollars   = floatval($amt["a"]);
    $eac_dollars = floatval($amt["eac"]);
    $etc_dollars = floatval($amt["etc"]);

    $sql = "
      update 
        cost.timephased SET 
        p_dollars = $p_dollars,
        a_dollars = $a_dollars,
        eac_dollars = $eac_dollars,
        etc_dollars = $etc_dollars
      where rpt_period = '$period' 
      and wbs_id = '$wbs_id'
  ";
    print $sql."<br>";
    $junk = dbCall($sql, "cost");
}

set_time_limit (15000);
print "Made it to Step 2!!";
$sql = "select fmm_evms.timephased.wbs_id, ca name from fmm_evms.timephased group by wbs_id";
$rs = dbCall($sql, "fmm_evms");
while (!$rs->EOF) {
    $wbs_id = $rs->fields["wbs_id"];
    $ca_name = $rs->fields["name"];
    checkMasterCA($wbs_id,$ca_name);
    $rs->MoveNext();
}

truncateTable("cost","timephased");

$insert_stmt = "insert into cost.timephased (pmid, cmid, rpt_period, s_dollars, wbs_id) values ";
$insert_sql = $insert_stmt;
$sql = "
        select
            2 as pmid,
            ca.id as cmid,
            time.period,
            sum(cost) s,
            time.wbs_id
        from fmm_evms.timephased time 
          left join fmm_evms.master_ca ca
        on time.wbs_id = ca.wbs_id
        where time.cost_set = 'budget'
        group by time.wbs_id, cost_set, period order by period
";
$rs = dbCall($sql);
$i=0;
while (!$rs->EOF)
{
    $pmid       = $rs->fields["pmid"];
    $cmid       = $rs->fields["cmid"];
    $rpt_period = $rs->fields["period"];

    $s_dollars = $rs->fields["s"];
    $wbs_id    = $rs->fields["wbs_id"];

    $insert_sql.="($pmid, $cmid, $rpt_period, $s_dollars, '$wbs_id'),";
    $i++;
    if($i ==1000)
    {
        $insert_sql = substr($insert_sql, 0, -1);
        $junk = dbCall($insert_sql, "cost");
        $insert_sql = $insert_stmt;
    }
    $rs->MoveNext();
}
if($i!=1000)
{
    $insert_sql = substr($insert_sql, 0, -1);
    $junk = dbCall($insert_sql, "cost");
}


$cost_set_array = array();
$cost_set_array["a"]   = "actuals";
$cost_set_array["eac"] = "eac";
$cost_set_array["p"]   = "Earned";
$cost_set_array["etc"]   = "etc";
/*
 * Step 3 select
 *
 * */
$wc = "";
$wc="where wbs_id in  ('1.16.1.8.13.521', 
'1.16.1.1.1.621', 
'1.16.1.1.1.623', 
'1.16.1.1.1.625', 
'1.16.1.1.101.301', 
'1.16.2.1.041.510', 
'1.16.2.1.086.510', 
'1.16.2.1.089.510')";

$sql = "select wbs_id, rpt_period from cost.timephased $wc group by wbs_id, rpt_period order by rpt_period";

$rs = dbCall($sql,"cost");
while (!$rs->EOF)
{
    $wbs_id     = $rs->fields["wbs_id"];
    $rpt_period = $rs->fields["rpt_period"];
    $amt_array = array();
    foreach ($cost_set_array as $key=> $value)
    {
        $sql = "
                select sum(cost) as amt
                  from fmm_evms.timephased 
                  where 
                cost_set = '$value' and 
                period = '$rpt_period' and 
                timephased.wbs_id = '$wbs_id'
                GROUP BY timephased.wbs_id, cost_set, period
                ";
        print $sql;
        echo "<br>";
        $time_rs    = dbCall($sql, "fmm_evms");
        $amt_array[$key]  = $time_rs->fields["amt"];
    }
    updateCostTimephasedRecord($rpt_period, $wbs_id, $amt_array);
    reset($cost_set_array);
    $rs->MoveNext();

}
die("I made it");