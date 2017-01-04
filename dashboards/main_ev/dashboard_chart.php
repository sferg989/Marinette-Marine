<?php
include('../../inc/inc.php');


if($chart_type =="spi")
{

    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];

    $sql = " 
        select
          sum(s_".$rpt_type.") s,
          sum(p_".$rpt_type.") p
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id        
        $wc
        $gb  
    ";
    //print $sql;
    $rs = dbCall($sql,"cost");
    $s = $rs->fields["s"];
    $p = $rs->fields["p"];
    $spi = $p/$s;
    die($spi);
}
if($chart_type =="cpi")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];

    $sql = " 
        select
          sum(a_".$rpt_type.") a,
          sum(p_".$rpt_type.") p
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id          $wc
        $gb  
  ";
    //print $sql;
    $rs = dbCall($sql,"cost");
    $a = $rs->fields["a"];
    $p = $rs->fields["p"];
    $cpi = $p/$a;
    die($cpi);
}
if($chart_type =="tcpi")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];
    $sql = " 
        select
          sum(a_".$rpt_type.") a,
          sum(p_".$rpt_type.") p,
          sum(bac_".$rpt_type.") bac,
          sum(eac_".$rpt_type.") eac
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id          
        $wc
        $gb  
  ";

    //print $sql;
    $rs = dbCall($sql, "cost");
    $p = $rs->fields["p"];
    $a = $rs->fields["a"];
    $bac = $rs->fields["bac"];
    $eac = $rs->fields["eac"];
    $tcpi = evalTCPI($a, $p,$bac,$eac);
    die($tcpi);
}
if($chart_type =="bei_start")
{
    die(1.1);
}
if($chart_type =="bei_finish")
{
    die(1.1);
}
if($chart_type =="ps")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];
    $sql = " 
        select
          sum(s_".$rpt_type.") s,
        sum(bac_".$rpt_type.") as bac
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id          
        $wc
        $gb  
";
    $rs = dbCall($sql, "cost");
    $s = $rs->fields["s"];
    $bac = $rs->fields["bac"];
    $percent_scheduled = (($s / $bac) * 100);
    die($percent_scheduled);
}
if($chart_type =="pc")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];
    $sql = " 
        select
          sum(p_".$rpt_type.") p,
        sum(bac_".$rpt_type.") as bac
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id          
        $wc
        $gb  
  ";
    $rs = dbCall($sql, "cost");
    $p = $rs->fields["p"];
    $bac = $rs->fields["bac"];
    $percent_complete = (($p / $bac) * 100);
    die($percent_complete);
}
if($chart_type =="actuals_spent")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];
    $sql = " 
        select
          sum(a_".$rpt_type.") a,
        sum(eac_".$rpt_type.") as eac
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id          
        $wc
        $gb  
  ";
    $rs = dbCall($sql, "cost");
    $a = $rs->fields["a"];
    $eac = $rs->fields["eac"];
    $actuals_spent = (($a / $eac) * 100);
    die($actuals_spent);
}


