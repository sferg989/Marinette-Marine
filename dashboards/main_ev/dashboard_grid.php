<?php
include('../../inc/inc.php');
$request_url=explode("/", $_SERVER['REQUEST_URI']);
$path = $request_url[5];

function spiLOE()
{
    $sql = "        select
          sum(s_dollars) s,
          sum(a_dollars) p
        from cost.`201610` 
        where evt <> 'Level of Effort'
        group by pmid
        ";
    $rs = dbCall($sql,"cost");
    $s = $rs->fields["s"];
    $p = $rs->fields["p"];
    $spi_loe = $p/$s;
    return $spi_loe;
}
function pcLOE()
{
    $sql = "select count(*) count from cost.`201610`";
    $rs = dbCall($sql, "cost");
    $total_count = $rs->fields["count"];
    $sql = "select count(*) loe_count from cost.`201610` where evt = 'level of effort'";
    $rs = dbCall($sql, "cost");
    $loe_count = $rs->fields["loe_count"];
    $pc_loe = ($loe_count/$total_count) *100;
    return $pc_loe;
}

if($grid=="cost_data")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];
    $sql = " 
        select
          sum(s_".$rpt_type.") s,
          sum(a_".$rpt_type.") p,
          sum(p_".$rpt_type.") a,
        sum(bac_".$rpt_type.") as bac,
        sum(eac_".$rpt_type.") as eac
        from cost.`$period` cost 
        left join fmm_evms.master_ca ca 
        on cost.pmid= ca.pmid and cost.cmid = ca.id          
        $wc
        $gb  
    ";
    $rs = dbCall($sql,"cost");
    $s = $rs->fields["s"];
    $p = $rs->fields["p"];
    $a = $rs->fields["a"];
    $bac = $rs->fields["bac"];
    $eac = $rs->fields["eac"];
    $sv = $p-$s;
    $cv = $p-$a;
    $vac = $bac-$eac;
    $bcwr = $bac-$p;
    $etc = $eac-$a;
    /*
    WE DON NOT HAVE AN ACCURATE CBB

    $cbb = $rs->fields["a"];

    */

    $cbb = $rs->fields["s"];
    $vac_bac = $bac-$eac;
    $vac_cbb = $rs->fields["a"];

    $data = "{\"value\":
    [{
        \"ID\":0,
        \"s_cum\":$s,
        \"p_cum\":$p,
        \"a_cum\":$a,
        \"sv\":$sv,
        \"cv\":$cv,
        \"bac\":$bac,
        \"eac\":$eac,
        \"bcwr\":$bcwr,
        \"etc\":$etc,
        \"cbb\":$cbb,
        \"vac_bac\":$vac_cbb,
        \"vac_cbb\":$vac_cbb
    }
    ]}";
    die($data);
}
if($grid=="cost_indices")
{
    $data = buildWCandGB($project,$ca,$cam);
    $wc = $data["wc"];
    $gb = $data["gb"];
    $sql = " 
        select
          sum(s_".$rpt_type.") s,
          sum(a_".$rpt_type.") a,
          sum(p_".$rpt_type.") p,
        sum(bac_".$rpt_type.") as bac,
        sum(eac_".$rpt_type.") as eac
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
    $a = $rs->fields["a"];
    $bac = $rs->fields["bac"];
    $eac = $rs->fields["eac"];
    $spi = $p/$s;
    $cpi = $p/$a;
    $spi_loe = spiLOE();
    $pc_loe = pcLOE();
    $tcpi = evalTCPI($a, $p,$bac,$eac);
    $tcpi_cpi = $tcpi-$cpi;
    $mr = 0;


    $data = "{\"value\":
    [{
        \"ID\":1,
        \"cpi\":$cpi,
        \"spi\":$spi,
        \"spi_loe\":$spi_loe,
        \"loe\":$pc_loe,
        \"tcpi\":$tcpi,
        \"tcpi_cpi\":$tcpi_cpi,
        \"mr\":0
    }
    ]}";
    die($data);
}
