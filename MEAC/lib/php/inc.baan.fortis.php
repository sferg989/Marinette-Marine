<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 8/7/2017
 * Time: 5:43 PM
 */
function returnBaanOpenBuySQL($ship_code){
    $sql = "
SELECT DISTINCT
  b.t_buyr                   AS                            buyer,
  a.t_cprj                   AS                            ship_code,
  b.t_cpcp                   AS                            swbs,
  a.t_item                   AS                            item,
  b.t_dfit                   AS                            spn,
  -- Description
  b.t_dsca                                                 description,
  a.t_qana                   AS                            original_smos_qty,
  -- Production Allocations
  CASE
  WHEN (SELECT sum(g.t_qana)
        FROM ttipcs500490 g
        WHERE g.t_cprj = b.t_cprj AND g.t_item = b.t_item
              AND (g.t_koor = 1 OR g.t_koor = 8)
              AND t_kotr = 2) IS NOT NULL
    THEN (SELECT sum(g.t_qana)
          FROM ttipcs500490 g
          WHERE g.t_cprj = b.t_cprj AND g.t_item = b.t_item
                AND (g.t_koor = 1 OR g.t_koor = 8)
                AND t_kotr = 2)
  ELSE 0
  END                        AS                            production_allocations,

  -- Production Issues

  CASE
  WHEN (SELECT sum(h.t_qstk)
        FROM ttdilc301490 h
        WHERE h.t_cprj = b.t_cprj AND h.t_item = b.t_item
              AND h.t_koor = 1 AND h.t_kost = 7) >= 0
    THEN (SELECT sum(h.t_qstk)
          FROM ttdilc301490 h
          WHERE h.t_cprj = b.t_cprj AND h.t_item = b.t_item
                AND h.t_koor = 1 AND h.t_kost = 7)
  ELSE 0
  END                        AS                            production_issues,
  -- Remaining SMOS Qty
  (a.t_qana -
   CASE
   WHEN (SELECT sum(g.t_qana)
         FROM ttipcs500490 g
         WHERE g.t_cprj = b.t_cprj AND g.t_item = b.t_item
               AND (g.t_koor = 1 OR g.t_koor = 8) AND t_kotr = 2) IS NOT NULL
     THEN (SELECT sum(g.t_qana)
           FROM ttipcs500490 g
           WHERE g.t_cprj = b.t_cprj AND g.t_item = b.t_item
                 AND (g.t_koor = 1 OR g.t_koor = 8) AND t_kotr = 2)
   ELSE 0
   END -
   CASE
   WHEN (SELECT sum(h.t_qstk)
         FROM ttdilc301490 h
         WHERE h.t_cprj = b.t_cprj AND h.t_item = b.t_item
               AND h.t_koor = 1 AND h.t_kost = 7) >= 0
     THEN (SELECT sum(h.t_qstk)
           FROM ttdilc301490 h
           WHERE h.t_cprj = b.t_cprj AND h.t_item = b.t_item
                 AND h.t_koor = 1 AND h.t_kost = 7)
   ELSE 0
   END
  )                          AS                            remaining_smos_qty,
  a.t_ddat                   AS                            yard_due_date,
  b.t_oltm                   AS                            lead_time,
  -- Planned Order Date
  CASE
  WHEN a.t_ddat = '1753-01-01'
    THEN '1753-01-01'
  ELSE cast(a.t_ddat AS DATETIME) - b.t_oltm
  END                        AS                            plan_order_date,
  -- UOM
  b.t_cuni                   AS                            uom,
  -- Total PRP Purch Order Qty
  CASE
  WHEN (SELECT sum(i.t_oqan)
        FROM ttipcs520490 i
        WHERE i.t_cprj = a.t_cprj AND i.t_item = a.t_item
              AND i.t_osta = 1) IS NOT NULL
    THEN (SELECT sum(i.t_oqan)
          FROM ttipcs520490 i
          WHERE i.t_cprj = a.t_cprj AND i.t_item = a.t_item
                AND i.t_osta = 1)
  ELSE 0
  END                        AS                            total_prp_purch_ord_qty,
  a.t_hold                   AS                            on_hold,
  cast(a.t_edon AS DATETIME) AS                            entered_on,
  cast(a.t_lmon AS DATETIME) AS                            last_mod,
  -- Expected Amoount
  a.t_qana                   AS                            ebom,
  b.t_ordr                   AS                            on_order_qty,
  b.t_stoc                   AS                            stock,
  (SELECT
     TOP 1 LTRIM(RTRIM(bc.t_bitm)) AS Activity
   FROM ttipcs950490 AS ab
     LEFT JOIN ttipcs952490 AS bc ON ab.t_bdgt = bc.t_bdgt
   WHERE LTRIM(RTRIM(ab.t_cprj)) = LTRIM(RTRIM(a.t_cprj)) AND LTRIM(RTRIM(bc.t_bdgt)) = LTRIM(RTRIM(ab.t_bdgt)) AND
         LTRIM(RTRIM(bc.t_item)) = LTRIM(RTRIM(a.t_item))) wp
FROM ttiitm901490 a
  LEFT JOIN ttipcs021490 b ON b.t_cprj = a.t_cprj AND b.t_item = a.t_item
WHERE a.t_cprj
      LIKE '%$ship_code%'
      ";
    return $sql;
}
function returnBaanOpenPOSQL($ship_code){
    $sql = "
        SELECT distinct  a.t_cprj as ship_code,
                CASE
                    WHEN convert(INT,a.t_pacn) <> 0 THEN substring(a.t_pacn,2,3)
                    ELSE d.t_cpcp
                END as swbs,
                a.t_item as item,
                CASE
                    WHEN ltrim(rtrim(a.t_cprj)) <> '' THEN c.t_dsca
                    ELSE e.t_dsca
                END as description,
                c.t_n1at as noun_1,
                c.t_n2at as noun_2,
                CASE
                    WHEN ltrim(rtrim(a.t_cprj)) <> '      ' THEN
                        CASE
                            WHEN ltrim(rtrim(c.t_csel)) = 'NR' THEN 'NRE'
                            ELSE ''
                        END
                    ELSE
                        CASE
                            WHEN ltrim(rtrim(e.t_csel)) = 'NR' THEN 'NRE'
                            ELSE ''
                        END
                END as nre,
                a.t_suno as vendor,
                a.t_orno as po,
                a.t_pono as line,
                a.t_pric as unit_price,
                a.t_oqua as order_qty,
                a.t_dqua as delivered_qty,
                CASE
                    WHEN a.t_dqua <> 0 THEN a.t_bqua
                    ELSE a.t_oqua
                END as pending_qty,
                CASE
                    WHEN a.t_dqua <> 0 THEN a.t_bqua * a.t_pric
                    ELSE a.t_oqua * a.t_pric
                END as pending_amt,
                CASE
                    WHEN a.t_ddtc <> a.t_ddtd and a.t_ddtd <> '1753-01-01 00:00:00.000' THEN a.t_ddtd
                    ELSE
                        CASE
                            WHEN a.t_ddta <> a.t_ddtc and a.t_ddtc <> '1753-01-01 00:00:00.000' THEN a.t_ddtc
                            ELSE a.t_ddta
                        END
                END as delv_date,
                b.t_cpay as payment_terms,
                a.t_pacn as ledger_account,
                (select
                    top 1 LTRIM(RTRIM(bc.t_bitm)) as Activity
                    from ttipcs950490 as ab
                    left join ttipcs952490 as bc on ab.t_bdgt = bc.t_bdgt
                where ab.t_cprj =a.t_cprj and bc.t_bdgt = ab.t_bdgt and bc.t_item = a.t_item ) wp
        FROM	ttdpur041490 a
                left join ttdpur040490 b on b.t_orno = a.t_orno
                left join ttipcs021490 c on c.t_cprj = a.t_cprj and c.t_item = a.t_item
                left join ttdpur045490 d on (d.t_orno = a.t_orno and d.t_pono = a.t_pono)
                left join ttiitm001490 e on e.t_item = a.t_item
                where 
                a.t_cprj like '%$ship_code%'
                AND		((a.t_dqua <> 0 AND a.t_bqua <> 0)
  		        OR (a.t_dqua = 0 AND a.t_oqua <> 0))
        ORDER BY
                a.t_cprj, a.t_item";
    return $sql;
}
function loadBaanBuyerIDList(){
    $sql = "
          Select Distinct 
                a.t_buyr buyer_id,
                c.t_nama buyer
          From ttipcs021490 as a
                join ttccom001490 as c on a.t_buyr = c.t_emno
                Order by t_buyr
    ";
    $rs = dbCallBaan($sql);
    $insert_sql= "INSERT  into meac.master_buyer (id, buyer) values";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $buyer_id = intval($rs->fields["buyer_id"]);
        $buyer    = $rs->fields["buyer"];
        $sql.=
            "(
                $buyer_id,
                '$buyer'
                ),";
        if($i == 1000)
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}
function returnGlDetailBaanSQL($wc=""){
    $sql = "
        select  *,
            case
                when  qty > 0
                        and integr_amt = 0
                        and transaction_type like '%INV%'
                then (select t_quan from ttipcs700490 inv where
                    ltrim(rtrim(inv.t_cprj))= s.ship_code
                    and inv.t_item = s.item
                    and inv.t_orno = s.order2
                 and inv.t_pono  = 0  )
                else 0
            end as no_cost_transfers
        from (
        SELECT
            a.t_dim1 as ship_code,
            a.t_leac as ledger_acct,
            a.t_otyp as transaction_type,
            a.t_odoc as document,
            a.t_olin as line,
            f.t_citg as item_group,
            f.t_cpcp as swbs,
                CASE
                    WHEN a.t_intt = 1 THEN c.t_item
                    ELSE ''
                END as item,
                CASE
                    WHEN a.t_intt = 1 THEN
                        CASE
                            WHEN ltrim(rtrim(c.t_cprj)) = '' or (c.t_tror = 4 and c.t_fitr = 20) THEN e.t_dsca
                            ELSE f.t_dsca
                        END
                    ELSE
                        CASE
                            WHEN a.t_otyp in ('API','AMI','APC') THEN ''
                            ELSE a.t_refr
                        END
                END as description,
                CASE
                    WHEN a.t_intt = 1 THEN c.t_orno
                    ELSE
                        CASE
                            WHEN a.t_otyp = 'API' THEN g.t_orno
                        END
                END as order2,
                CASE
                    WHEN a.t_intt = 1 THEN c.t_pono
                    ELSE
                        CASE
                            WHEN a.t_otyp = 'API' THEN 0
                            ELSE a.t_olin
                        END
                END as position,
                CASE
                    WHEN a.t_intt = 1 THEN
                        CASE
                            WHEN ltrim(rtrim(c.t_suno)) <> '' THEN
                                (SELECT	h.t_nama
                                FROM	ttccom020490 h
                                WHERE	h.t_suno = c.t_suno)
                            WHEN ltrim(rtrim(c.t_suno)) = '' and ltrim(rtrim(c.t_cuno)) <> '' THEN
                                (SELECT	i.t_nama
                                FROM	ttccom010490 i
                                WHERE	i.t_cuno = c.t_cuno)
                        END
                    ELSE
                        CASE
                            WHEN ltrim(rtrim(a.t_suno)) <> '' THEN
                                (SELECT	h.t_nama
                                FROM	ttccom020490 h
                                WHERE	h.t_suno = a.t_suno)
                            WHEN ltrim(rtrim(a.t_suno)) = '' and ltrim(rtrim(a.t_cuno)) <> '' THEN
                                (SELECT	i.t_nama
                                FROM	ttccom010490 i
                                WHERE	i.t_cuno = a.t_cuno)
                        END
                END as cust_supp,
                CASE
                    WHEN a.t_intt = 1 THEN c.t_nuni
                    ELSE 0
                END as qty,
                c.t_cuni as unit,
                CASE
                    WHEN a.t_dbcr = 2 THEN 0 - a.t_amth
                    ELSE a.t_amth
                END as amt,
                d.t_tedt as date,
                CASE
                    WHEN a.t_intt = 1 THEN
                        CASE
                            WHEN c.t_dbcr = 2 THEN 0 - c.t_amth
                            ELSE c.t_amth
                        END
                    ELSE
                        CASE
                            WHEN a.t_dbcr = 2 THEN 0 - a.t_amth
                            ELSE a.t_amth
                        END
                END as integr_amt,
            (select
                    top 1 LTRIM(RTRIM(bc.t_bitm)) as Activity
                from ttipcs950490 as ab
                left join ttipcs952490 as bc on ab.t_bdgt = bc.t_bdgt
                where ab.t_cprj =f.t_cprj and bc.t_bdgt = ab.t_bdgt and bc.t_item = f.t_item ) wp
        
            FROM	  ttfgld106490 a
            left join ttfgld418490 b on b.t_fcom = a.t_ocmp and b.t_ttyp = a.t_otyp and b.t_docn = a.t_odoc and b.t_lino = a.t_olin
            left join ttfgld410490 c on c.t_ocom = b.t_ocom and c.t_tror = b.t_tror and c.t_fitr = b.t_fitr and c.t_trdt = b.t_trdt
               and c.t_trtm = b.t_trtm and c.t_sern = b.t_sern and c.t_line = b.t_line
            left join ttfgld100490 d on d.t_year = a.t_oyer and d.t_btno = a.t_obat
            left join ttiitm001490 e on e.t_item = c.t_item
            left join ttipcs021490 f on f.t_cprj = c.t_cprj and f.t_item = c.t_item
            left join ttfacp200490 g on g.t_ttyp = a.t_ctyp and g.t_ninv = a.t_cinv and g.t_line = 0 and g.t_tdoc = '' and g.t_docn = 0 and g.t_lino = 0
            WHERE ltrim(rtrim(a.t_leac)) BETWEEN '4000' AND '4999'
              AND a.t_dim1 = '0479' -- and c.t_item like '%234-01-00001-885%'
                AND		a.t_fyer BETWEEN 2008 and 2017
                AND		d.t_tedt BETWEEN '01/01/2008' and '07/29/2017'
                ) s;
    ";
    print $sql;
    return $sql;
}
function insertOpenBuyReport($ship_code){
    $i=0;
    $sql        = returnBaanOpenBuySQL($ship_code);
    $rs         = dbCallBaan($sql);
    $insert_sql = returnOpenBuyInsertSQL();
    $sql        = $insert_sql;
    $program = "LCS";
    while (!$rs->EOF)
    {
        $buyer              = trim($rs->fields["buyer"]);
        $wp                 = trim($rs->fields["wp"]);
        $ship_code          = trim($rs->fields["ship_code"]);
        $swbs               = trim($rs->fields["swbs"]);
        $item               = trim($rs->fields["item"]);
        $spn                = trim($rs->fields["spn"]);
        $description        = addslashes(str_replace("'", " ", trim($rs->fields["description"])));
        $origrinal_smos_qty = formatNumber4decNoComma($rs->fields["original_smos_qty"]);
        $production_issues  = formatNumber4decNoComma($rs->fields["production_issues"]);
        $remain_smos_qty    = formatNumber4decNoComma($rs->fields["remaining_smos_qty"]);
        $yard_due_date      = fixExcelDateMySQL($rs->fields["yard_due_date"]);
        $lead_time          = $rs->fields["lead_time"];
        $plan_order_date    = fixExcelDateMySQL($rs->fields["plan_order_date"]);
        $uom                = trim($rs->fields["uom"]);
        $stock              = formatNumber4decNoComma($rs->fields["stock"]);
        $on_order_qty       = formatNumber4decNoComma($rs->fields["on_order_qty"]);
        $ebom               = formatNumber4decNoComma($rs->fields["ebom"]);
        $on_hold            = $rs->fields["on_hold"];
        $entered_on         = fixExcelDateMySQL($rs->fields["entered_on"]);
        $last_mod           = fixExcelDateMySQL($rs->fields["last_mod"]);
        $item_shortage      = formatNumber4decNoComma($ebom - $stock - $on_order_qty- $production_issues);


        $sql.= " (
            '$program',
            $ship_code,
            '$wp',
            '$buyer',
            '$swbs',
            '$item',
            '$spn',
            '$description',
            $origrinal_smos_qty,
            $remain_smos_qty,
            '$yard_due_date',
            '$lead_time',
            '$plan_order_date',
            '$uom',
            $stock,
            $on_order_qty,
            $item_shortage,
            '$on_hold',
            '$entered_on',
            '$last_mod',
            $production_issues
        ),";
        if($i == 250)
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

    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=250)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}
function returnOpenBuyInsertSQL(){
    $insert_sql = "
    insert into meac.wp_baan_open_buy (
        program,
        ship_code,
        wp,
        buyer,
        swbs,
        item,
        spn,
        description,
        origrinal_smos_qty,
        remain_smos_qty,
        yard_due_date,
        lead_time,
        plan_order_date,
        uom,
        item_on_hand,
        item_on_order,
        item_shortage,
        on_hold,
        entered_on,
        last_mod,
        production_issues) VALUES ";
    return $insert_sql;
}

function returnOpenPOInsertSQL(){
    $insert_sql = "
        insert into meac.wp_baan_open_po (
            ship_code,
            swbs,
            item,
            wp,
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
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort,
            ecp_rea 
    ) VALUES 
       ";
    return $insert_sql;
}
function insertOpenPOReport($ship_code){
    $sql = returnBaanOpenPOSQL($ship_code);
    $rs = dbCallBaan($sql);
    $insert_sql= returnOpenPOInsertSQL();
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $ship_code     = intval($rs->fields["ship_code"]);
        $swbs          = intval($rs->fields["swbs"]);
        $wp          = intval($rs->fields["wp"]);
        $item          = addslashes(trim($rs->fields["item"]));
        $description   = addslashes(trim($rs->fields["description"]));
        $noun_1        = addslashes(trim($rs->fields["noun_1"]));
        $noun_2        = addslashes(trim($rs->fields["noun_2"]));
        $nre           = addslashes(trim($rs->fields["nre"]));
        $vendor        = intval($rs->fields["vendor"]);
        $po            = intval($rs->fields["po"]);
        $line          = intval($rs->fields["line"]);
        $unit_price    = formatNumber4decNoComma($rs->fields["unit_price"]);
        $order_qty     = formatNumber4decNoComma($rs->fields["order_qty"]);
        $delivered_qty = formatNumber4decNoComma($rs->fields["delivered_qty"]);
        $pending_qty   = formatNumber4decNoComma($rs->fields["pending_qty"]);
        $pending_amnt  = formatNumber4decNoComma($rs->fields["pending_amnt"]);
        $delv_date     = fixExcelDateMySQL($rs->fields["delv_date"]);
        $payment_terms = intval($rs->fields["payment_terms"]);
        $ledger_acct   = intval($rs->fields["ledger_acct"]);
        $clin          = addslashes(trim($rs->fields["clin"]));
        $effort        = addslashes(trim($rs->fields["effort"]));
        $ecp_rea       = trim($rs->fields["ecp_rea"]);

        $sql.=
            "(
                $ship_code,
                $swbs,
                '$item',
                '$wp',
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
                $pending_qty,
                $pending_amnt,
                '$delv_date',
                $payment_terms,
                $ledger_acct,
                '$clin',
                '$effort',
                '$ecp_rea'
                ),";
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}

function returnGlDetailInsertSQL(){
    $insert_sql = "
        INSERT  INTO meac.wp_baan_gl_detail (
            program, 
            ship_code, 
            wp, 
            ldger_acct,
            document,
            line,
            item,
            description,
            `order`,
            pos,
            cust_supp,
            qty,
            unit,
            amt,
            date,
            integr_amt,
            clin,
            effort,
            ecp_rea,
            no_cost_transfers) 
            values
           ";
    return $insert_sql;
}
function loadGlDetailBaan($ship_code="", $rpt_period=""){
    if($rpt_period!=""){
        $year      = intval(substr($rpt_period, 0, 4));
        $month     = month2digit(substr($rpt_period, -2));
        $period_wc = "AND a.t_fyer <=$year AND	a.t_fprd <=$month and ";
    }
    if($ship_code!=""){
        if($rpt_period!=""){
            $period_wc = substr($period_wc, 0, -5);
        }
        $ship_code_wc = "AND a.t_dim1 = '$ship_code'";

    }
    $wc = $period_wc." ".$ship_code_wc;

    $sql        = returnGlDetailBaanSQL($wc);
    $rs         = dbCallBaan($sql);
    $insert_sql = returnGlDetailInsertSQL();
    $sql        = $insert_sql;
    $i=0;
    while (!$rs->EOF)
    {
        $ldger_acct        = intval($rs->fields["ledger_acct"]);
        $transaction_type  = trim($rs->fields["transaction_type"]);
        $document          = $transaction_type . "  " . addslashes(trim($rs->fields["document"]));
        $line              = intval($rs->fields["line"]);
        $item              = addslashes(trim($rs->fields["item"]));
        $description       = addslashes(trim($rs->fields["description"]));
        $order             = intval($rs->fields["order2"]);
        $pos               = intval($rs->fields["position"]);
        $cust_supp         = addslashes(trim($rs->fields["cust_supp"]));
        $qty               = formatNumber4decNoComma($rs->fields["qty"]);
        $unit              = addslashes(trim($rs->fields["unit"]));
        $amt               = formatNumber4decNoComma($rs->fields["amt"]);
        $date              = fixExcelDateMySQL($rs->fields["date"]);
        $integr_amt        = formatNumber4decNoComma($rs->fields["integr_amt"]);
        $clin              = addslashes(trim($rs->fields["clin"]));
        $effort            = addslashes(trim($rs->fields["effort"]));
        $ecp_rea           = addslashes(trim($rs->fields["ecp_rea"]));
        $ship_code         = intval($rs->fields["ship_code"]);
        $no_cost_transfers = intval($rs->fields["no_cost_transfers"]);
        $wp                = trim($rs->fields["wp"]);

        $sql.=
            "(
                'LCS',
                $ship_code,
                '$wp',
                $ldger_acct,
                '$document',
                $line,
                '$item',
                '$description',
                $order,
                $pos,
                '$cust_supp',
                $qty,
                '$unit',
                $amt,
                '$date',
                $integr_amt,
                '$clin',
                '$effort',
                '$ecp_rea',
                $no_cost_transfers
                ),";
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    print $sql;
}
function returnFortisPOSQL($ship_code=""){
    $wc = "where Project_Number  <> ''";
    if($ship_code!=""){
        $wc = " and Project_Number = '$ship_code'";
    }

    $sql = "select
                project_number,
                po_number,
                supplier_name,
                supplier_number,
                notes,
                Purchasing_Manager_Notes,
                buyer_name,
                purchase_order_type,
                funding_source,
                total_amount,
                vendor_project_total,
                order_date,
                created_date,
                modified_date,
                (case
                when _cont.Container = 'Project Approved' or _cont.Container = 'Approved MRO' Then 'Approved'
                when _cont.Container = 'Purchase Orders Disapproved' Then 'Denied'
                when _cont.Container like '%Pending%' or _cont.Container like '%New PO%' Then 'Pending'
                when _cont.Container = 'No Approval' Then 'Approved'
                when _cont.Container like '%Complete%' Then 'Approved'
                when _cont.Container like '%Denied%' Then 'Denied'
                when _cont.Container like '%Pending%' Then 'Pending'
                when _cont.Container = 'New PO' Then 'New' else '' end)
                as fortisstatus
                from FMM_Purchase_Order
                left outer join FTBContainer _cont on _cont.Container_ID = F_ParentID
                $wc
    ";
    return $sql;
}
function returnFortisPOInsertSQL(){
    $insert_sql = "
    INSERT  into po_data (
        ship_code,
        po,
        vendor,
        vendor_id,
        notes,
        buyer,
        po_type,
        funding_source,
        amt,
        vendor_total,
        status,
        program,
        order_date,
        created_date,
        modified_date,
        purchasing_manager_notes)  
      VALUES 
    ";
    return $insert_sql;
}
function returnInsertPODATAInsertSQL($ship_code,$po,$vendor,$vendor_id,
                                     $notes,$buyer,$po_type,$funding_source,
                                     $amt,$vendor_total,$status,$program,$order_date,
                                     $created_date,$modified_date,$purchasing_manager_notes)
{
    $sql = "(
        $ship_code,
        $po,
        '$vendor',
        $vendor_id,
        '$notes',
        '$buyer',
        '$po_type',
        '$funding_source',
        $amt,
        $vendor_total,
        '$status',
        '$program',
        '$order_date',
        '$created_date',
        '$modified_date',
        '$purchasing_manager_notes'),";
    return $sql;
}
function processFortisNotes(){

}
function loadFortisPOData($ship_code= ""){
    $sql        = returnFortisPOSQL($ship_code);
    $rs         = dbCallFortis($sql);
    $insert_sql = returnFortisPOInsertSQL();
    $sql        = $insert_sql;

    $program = "LCS";
    $i = 0;
    while (!$rs->EOF)
    {
        $ship_code                = intval(trim($rs->fields["project_number"]));
        $po                       = intval(trim($rs->fields["po_number"]));
        $vendor                   = processDescription(trim($rs->fields["supplier_name"]));
        $vendor_id                = intval(trim($rs->fields["supplier_number"]));
        $notes                    = processDescription(trim($rs->fields["notes"]));
        $purchasing_manager_notes = processDescription(trim($rs->fields["Purchasing_Manager_Notes"]));
        $buyer                    = trim($rs->fields["buyer_name"]);
        $po_type                  = trim($rs->fields["purchase_order_type"]);
        $funding_source           = trim($rs->fields["funding_source"]);
        $status                   = trim($rs->fields["fortisstatus"]);
        $order_date               = $rs->fields["order_date"];
        $created_date             = $rs->fields["created_date"];
        $modified_date            = $rs->fields["modified_date"];
        $amt                      = formatNumber4decNoComma($rs->fields["total_amount"]);
        $vendor_total             = formatNumber4decNoComma($rs->fields["vendor_project_total"]);

        $sql.=returnInsertPODATAInsertSQL($ship_code,$po,$vendor,$vendor_id,
            $notes,$buyer,$po_type,$funding_source,
            $amt,$vendor_total,$status,$program, $order_date,
            $created_date, $modified_date,$purchasing_manager_notes);

        if($i == 2000)
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=2000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
    //print $sql;
}

function loadResponsibleBuyer($ship_code){
    $sql = "
         select DISTINCT t_cprj, t_buyr, t_item  from ttipcs021490 where t_cprj like '%$ship_code%'
    ";
    $rs = dbCallBaan($sql);
    $insert_sql= "insert into buyer_reponsible (ship_code, buyer_id,item) VALUES";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $ship_code = intval(trim($rs->fields["t_cprj"]));
        $buyer     = trim($rs->fields["t_buyr"]);
        $item      = trim($rs->fields["t_item"]);
        $sql.=
            "(
                $ship_code,
                $buyer,
                '$item'
                ),";
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i!=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}
function returnBaanEFDBSQL($ship_code){
    $sql = "
    Select 
        a.t_cprj as project,
        b.t_item as item,
        b.t_cmod as module,
        b.t_revi as efdb_change,
        b.t_nqan as change_quantity,
        CONVERT(CHAR(10), a.t_revd, 101) as change_date,
        a.t_revr as reason_code,
        c.t_dsca description,
        CASE WHEN a.t_appr=1 Then 'Yes' WHEN a.t_appr=2 Then 'No' Else 'No' END as hdr_processed,
        CASE WHEN b.t_proc=1 Then 'Yes' WHEN b.t_proc=2 Then 'No' Else 'No' END as dtl_processed
        From		ttifct030490 as a
        Left Join	ttifct035490 as b on a.t_cprj = b.t_cprj and a.t_revi = b.t_revi
        Left Join	ttisfc902490 as c on c.t_rwrk = a.t_revr
        Where
      a.t_cprj like '%$ship_code%'
        Order by a.t_cprj, b.t_item, b.t_cmod, b.t_revi DESC
      ";
    return $sql;
}
function returnBaanEFDBInsert(){
    $sql = "INSERT  into change_item (
            ship_code,
            item,
            module,
            efdb_change,
            change_qty,
            date,
            reason_code,
            description, 
            hdr_processed, 
            dtl_processed) VALUES 
            ";
    return $sql;
}
function insertEFDBChange($ship_code,$item, $module,
                          $efdb_change, $change_qty, $date,
                          $reason_code, $description, $hdr_processed,$dtl_processed){
    $sql = "(
            $ship_code,
            '$item',
            '$module',
            '$efdb_change',
            '$change_qty',
            '$date',
            '$reason_code',
            '$description',
            '$hdr_processed',
            '$dtl_processed'),";
    return $sql;
}

function loadEFDBChangeBAAN($ship_code){
    $sql        = returnBaanEFDBSQL($ship_code);
    $rs         = dbCallBaan($sql);
    $insert_sql = returnBaanEFDBInsert();
    $sql        = $insert_sql;
    $i=0;
    while (!$rs->EOF)
    {

        $ship_code     = intval($rs->fields["project"]);
        $item          = addslashes(trim($rs->fields["item"]));
        $module        = addslashes(trim($rs->fields["module"]));
        $efdb_change   = addslashes(trim($rs->fields["efdb_change"]));
        $change_qty    = formatNumber4decNoComma(trim($rs->fields["change_quantity"]));
        $date          = fixExcelDateMySQL($rs->fields["change_date"]);
        $reason_code   = addslashes(trim($rs->fields["reason_code"]));
        $description   = addslashes(trim($rs->fields["description"]));
        $hdr_processed = addslashes(trim($rs->fields["hdr_processed"]));
        $dtl_processed = addslashes(trim($rs->fields["dtl_processed"]));

        $sql.= insertEFDBChange($ship_code,$item, $module,$efdb_change,
                $change_qty, $date,$reason_code, $description,
                $hdr_processed,$dtl_processed);
        if($i == 1000)
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}

function insertCBMFromBaan($ship_code){

    $sql ="select 
        b.t_cprj as Project,
        LTRIM(RTRIM(a.t_bitm)) as Activity,
        c.t_prip as Budget,
        c.t_aamt as Assigned_Amount,
        a.t_pono as Position,
        a.t_item as Item,
        d.t_prip as Price,
        a.t_qana as Qty
        from ttipcs952490 as a
        join ttipcs950490 as b on a.t_bdgt = b.t_bdgt
        join ttipcs951490 as c on a.t_bdgt = c.t_bdgt and LTRIM(RTRIM(a.t_bitm)) = LTRIM(RTRIM(c.t_bitm))
        join ttipcs951490 as d on a.t_bdgt = d.t_bdgt and LTRIM(RTRIM(a.t_item)) = LTRIM(RTRIM(d.t_bitm))
        Where b.t_cprj like '%$ship_code%'
    Order by 1, 2, 3, 6";
    print $sql;
    $rs = dbCallBaan($sql);

    $insert_sql = " insert into meac.cbm (program, ship_code, wp, material, budget, assigned_amt, price, qty, pos) values ";
    $i=0;
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $ship_code  = intval($rs->fields["Project"]);
        $wp         = trim($rs->fields["Activity"]);
        $budget     = formatNumber4decNoComma($rs->fields["Budget"]);
        $assign_amt = formatNumber4decNoComma($rs->fields["Assigned_Amount"]);
        $pos        = formatNumber4decNoComma($rs->fields["Position"]);
        $item       = trim($rs->fields["Item"]);
        $price      = formatNumber4decNoComma($rs->fields["Price"]);
        $qty        = formatNumber4decNoComma($rs->fields["Qty"]);

        $sql.= "(                                                 
        'LCS',
        $ship_code,
        '$wp',
        '$item',
        $budget,
        $assign_amt,
        $price,
        $qty,
        $pos
        ),";
        if($i == 1000)
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
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
    {
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql, "meac");
    }
}
function returnEBOMBaanSQL($ship_code){

    $sql = "
          SELECT 
            a.t_cprj as ship_code,
            a.t_item as item,
            b.t_n1at as noun1,
            b.t_n2at as noun2,
            b.t_n3at as noun3,
            b.t_dsca as description,
            b.t_citg as item_group,
            b.t_cpcp as swbs,
            CASE
                WHEN b.t_cprj <> '' and b.t_item <> '' THEN b.t_dfit
                ELSE ''
            END as spn,
            CASE
                WHEN b.t_cprj <> '' and b.t_item <> '' THEN b.t_cuni
                ELSE c.t_cuni
            END as uom,
            a.t_qana as ebom,
          (select 
              top 1 LTRIM(RTRIM(bc.t_bitm)) as Activity
              from ttipcs950490 as ab
                    left join ttipcs952490 as bc on ab.t_bdgt = bc.t_bdgt
                    where ab.t_cprj =a.t_cprj and bc.t_bdgt = ab.t_bdgt and bc.t_item = a.t_item ) wp
          FROM	ttiitm901490 a
            LEFT JOIN ttipcs021490 b on b.t_cprj = a.t_cprj and b.t_item = a.t_item
            LEFT JOIN ttiitm001490 c on c.t_item = a.t_item
            LEFT JOIN ttifct020490 d on a.t_cprj = d.t_cprj and a.t_item = d.t_item
          WHERE	a.t_cprj like '%$ship_code%'
            and a.t_qana <> '0'
            order by a.t_cprj, a.t_item";
    return $sql;

}
function insertEbomValSQL($program,$ship_code,$item,$spn,$uom,
                          $item_group,$swbs,$ebom,$noun1,$noun2,$noun3,$description, $wp){
    $sql = "(
        '$program',
        $ship_code,
        '$item',
        '$spn',
        '$uom',
        '$item_group',
        $swbs,
        $ebom,
        '$noun1',
        '$noun2',
        '$noun3',
        '$description',
        '$wp'),";
    return $sql;
}
function loadBaanEbom($ship_code){
    $sql = returnEBOMBaanSQL($ship_code);
    $rs = dbCallBaan($sql);
    $insert_sql = "
            INSERT  into meac.wp_baan_ebom (program,
            ship_code,
            item,
            spn,
            uom,
            item_group,
            swbs,
            ebom,
            noun1,
            noun2,
            noun3,
            description,
            wp
            ) VALUES
    ";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF) {
        $program     = "LCS";
        $ship_code   = intval($rs->fields["ship_code"]);
        $item        = trim($rs->fields["item"]);
        $noun1       = trim($rs->fields["noun1"]);
        $noun2       = trim($rs->fields["noun2"]);
        $noun3       = trim($rs->fields["noun3"]);
        $description = processDescription3(trim($rs->fields["description"]));
        $item_group  = trim($rs->fields["item_group"]);
        $swbs        = intval($rs->fields["swbs"]);
        $spn         = trim($rs->fields["spn"]);
        $uom         = trim($rs->fields["uom"]);
        $wp          = trim($rs->fields["wp"]);
        $ebom        = formatNumber4decNoComma($rs->fields["ebom"]);

        $sql.=insertEbomValSQL($program,$ship_code,$item,$spn,$uom,
            $item_group,$swbs,$ebom,$noun1,$noun2,$noun3,$description,$wp);
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
    print $sql;
}
function loaditem2buyer(){
    truncateTable("meac", "item2buyer");
    $sql = "insert into
            item2buyer 
            (item, buyer, ship_code)
            (
              select 
              item, 
              buyer, 
              br.ship_code 
          from buyer_reponsible br
              left JOIN  master_buyer mb
                  on br.buyer_id = mb.id
              where br.buyer_id <> 0
                group by ship_code, item )
    ";
    $junk = dbCall($sql, "meac");
}

function returnBaanComittedPOSQL($ship_code){
    $sql = "
        SELECT
  a.t_cprj as ship_code,
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
