<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/18/2017
 * Time: 3:12 PM
 */
include("../../../inc/inc.php");
include("inc.baan.fortis.php");

$array = array();
$array[] = 465;
$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;
function returnBaanComittedPOSQL($ship_code){
    $sql = "
        SELECT	a.t_cprj as ship_code,
                CASE
                    WHEN	a.t_pacn <> 0 THEN substring(a.t_pacn,2,3)
                    ELSE	b.t_cpcp
                END as swbs,
                a.t_item as item,
                CASE
                    WHEN	a.t_cprj <> '      ' THEN c.t_dsca
                    ELSE	d.t_dsca
                END as description,
                c.t_n1at as noun1,
                c.t_n2at as noun2,
                CASE
                    WHEN	a.t_cprj <> '      ' THEN
                        CASE
                            WHEN	c.t_csel = ' NR' THEN 'NRE'
                            ELSE	''
                        END
                    ELSE
                        CASE
                            WHEN	d.t_csel = ' NR' THEN 'NRE'
                            ELSE	''
                        END
                END as nre,
                a.t_suno as vendor,
                a.t_orno as po,
                a.t_pono as line,
                a.t_pric as unit_price,
                a.t_oqua as order_qty,
                a.t_dqua as delivered_qty,
                CASE
                    WHEN	a.t_dqua <> 0 THEN a.t_dqua + a.t_bqua
                    ELSE	a.t_oqua
                END as committed_qty,
                CASE
                    WHEN	a.t_dqua <> 0 THEN (a.t_dqua + a.t_bqua) * a.t_pric
                    ELSE	a.t_oqua * a.t_pric
                END as commit_amnt,
                CASE
                    WHEN	a.t_ddtc <> a.t_ddtd and a.t_ddtd <> '1753-01-01 00:00:00.000' THEN a.t_ddtd
                    ELSE
                        CASE
                            WHEN	a.t_ddta <> a.t_ddtc and a.t_ddtc <> '1753-01-01 00:00:00.000' THEN a.t_ddtc
                            ELSE
                                CASE
                                    WHEN	a.t_ddta = '1753-01-01 00:00:00.000' THEN ''
                                    ELSE	a.t_ddta
                                END
                        END
                END as delv_date,
                a.t_pacn + '/ ' + a.t_dim1 + ' / ' + a.t_dim2 as acct_proj_dept,
          (select
                    top 1 LTRIM(RTRIM(bc.t_bitm)) as Activity
                from ttipcs950490 as ab
                left join ttipcs952490 as bc on ab.t_bdgt = bc.t_bdgt
                where ab.t_cprj =a.t_cprj and bc.t_bdgt = ab.t_bdgt and bc.t_item = a.t_item ) wp
        
        FROM	ttdpur041490 a
        LEFT JOIN ttdpur045490 b on b.t_orno = a.t_orno and b.t_pono = a.t_pono and b.t_srnb = 0
        LEFT JOIN ttipcs021490 c on c.t_cprj = a.t_cprj and c.t_item = a.t_item
        LEFT JOIN ttiitm001490 d on d.t_item = a.t_item
        WHERE
          ((a.t_cprj like '%$ship_code%'))
        ORDER BY a.t_cprj, a.t_item, a.t_orno, a.t_pono
";
    return $sql;
}
function insertSQLBaanCommittedPO(){
    $sql = "
        insert into wp_baan_committed_po (
                program,
                ship_code,
                wp,
                swbs,
                item,
                description,
                noun_1,
                noun_2,
                nre,
                vendor,
                po,
                line,
                unit_price,
                order_qty,
                delivered_qty,
                committed_qty,
                commit_amnt,
                delv_date,
                acct_proj_dept,
                clin,
                effort) values ";
    return $sql;
}
function insertSQLSTRINGBaanComittedPO($program,$ship_code,$wp,$swbs,$item,$description,
                                            $noun_1,$noun_2,$nre,$vendor,$po,$line,$unit_price,$order_qty,
                                            $delivered_qty,$committed_qty,$commit_amnt,$delv_date,
                                            $acct_proj_dept,$clin,$effort){

    $sql ="(
                '$program',
                $ship_code,
                '$wp',
                $swbs,
                '$item',
                '$description',
                '$noun_1',
                '$noun_2',
                '$nre',
                $vendor,
                $po,
                $line,
                $unit_price,
                $order_qty,
                $delivered_qty,
                $committed_qty,
                $commit_amnt,
                '$delv_date',
                '$acct_proj_dept',
                '$clin',
                '$effort'),";
    return $sql;
}
function loadBaanCommittedPO($ship_code){
    $sql        = returnBaanComittedPOSQL($ship_code);
    $rs         = dbCallBaan($sql);
    $insert_sql = insertSQLBaanCommittedPO();
    $sql        = $insert_sql;
    $i = 0;
    while (!$rs->EOF) {
        $program        = "LCS";
        $ship_code      = intval($rs->fields["ship_code"]);
        $wp             = trim($rs->fields["wp"]);
        $swbs           = intval($rs->fields["swbs"]);
        $item           = addslashes(trim($rs->fields["item"]));
        $description    = addslashes(trim($rs->fields["description"]));
        $noun_1         = addslashes(trim($rs->fields["noun1"]));
        $noun_2         = addslashes(trim($rs->fields["noun2"]));
        $nre            = addslashes(trim($rs->fields["nre"]));
        $vendor         = intval($rs->fields["vendor"]);
        $po             = intval($rs->fields["po"]);
        $line           = intval($rs->fields["line"]);
        $unit_price     = formatNumber4decNoComma($rs->fields["unit_price"]);
        $order_qty      = formatNumber4decNoComma($rs->fields["order_qty"]);
        $delivered_qty  = formatNumber4decNoComma($rs->fields["delivered_qty"]);
        $committed_qty  = formatNumber4decNoComma($rs->fields["committed_qty"]);
        $commit_amnt    = formatNumber4decNoComma($rs->fields["commit_amnt"]);
        $delv_date      = fixExcelDateMySQL($rs->fields["delv_date"]);
        $acct_proj_dept = addslashes(trim($rs->fields["acct_proj_dept"]));
        $clin           = addslashes(trim($rs->fields["clin"]));
        $effort         = addslashes(trim($rs->fields["effort"]));

        $sql.=insertSQLSTRINGBaanComittedPO($program,$ship_code,$wp,$swbs,$item,$description,
            $noun_1,$noun_2,$nre,$vendor,$po,$line,$unit_price,$order_qty,
            $delivered_qty,$committed_qty,$commit_amnt,$delv_date,
            $acct_proj_dept,$clin,$effort);
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}
foreach ($array as $ship_code){
    deleteFromTable("meac", "wp_baan_committed_po", "ship_code", $ship_code);
    loadBaanCommittedPO($ship_code);

}

