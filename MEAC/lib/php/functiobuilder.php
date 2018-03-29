<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 6/19/2017
 * Time: 3:36 PM
 */


function buildMEACTablesforRptPeriod($rpt_period){
    $schema = "meac";
    $sql = "select `table` as table_name from meac_table_list";
    $rs = dbCall($sql, "meac");
    while (!$rs->EOF) {
        $table          = $rs->fields["table_name"];
        $new_table_name = $rpt_period."_".$table;
        $create_table   = checkIfTableExists($schema, $new_table_name);
        $base_table     = "template_" . $table;
        if ($create_table == "create_table") {
            createTableFromBase($schema, $base_table, $new_table_name);
        }
        $rs->MoveNext();

    }
}
function loadINVTranserfersRptPeriod($ship_code, $rpt_period){

    $sql = "
        select 
            t_cprj,
            t_item,
            t_orno,
            t_quan 
         from ttipcs700490 
         where t_cprj like '%$ship_code%' and t_pono  = 0
         ";
    $rs = dbCallBaan($sql);
    $insert_sql = "insert into ".$rpt_period."_inv_transfers (ship_code, item, `order`, qty) values";
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF) {
        $ship_code = intval($rs->fields["t_cprj"]);
        $item      = trim($rs->fields["t_item"]);
        $order     = intval($rs->fields["t_orno"]);
        $qty       = formatNumber4decNoComma($rs->fields["t_quan"]);

        $sql.=" ($ship_code, '$item', $order, $qty),";
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
function insertSWBSSummaryStagingRptPeriod($ship_code,$rpt_period){
    insertSWBSSummaryOPENPORptPeriod($ship_code, $rpt_period);
    insertSWBSGLSUMRptPeriod($ship_code, $rpt_period);
    insertSWBSSummaryOPENBUYRptPeriod($ship_code, $rpt_period);
    insertSWBSSUmmaryEBOMRptPeriod($ship_code, $rpt_period);
    insertGlChargesNoPartNumAllocRptPeriod($ship_code, $rpt_period);
    insertGlChargesNoPartNumRptPeriod($ship_code, $rpt_period);
    insertJournalEntriesRptPeriod($ship_code, $rpt_period);

}
function insertCommittedPOWPRptPeriod($rpt_period, $ship_code){
    if($ship_code==""){
        $ship_code_wc = "";
    }
    else{
        $ship_code_wc = "and po.proj in($ship_code)";
    }
    $insert_sql = "
        insert into meac.".$rpt_period."_wp_committed_po (
        program,
        ship_code,
        wp,
        swbs,
        cbm_material,
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
        effort) values
       ";
    $sql = "
        select
        proj as ship_code,
        wp,
        swbs,
        cbm.material cbm_material,
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
        effort
        from mars.".$rpt_period."_committed_po po
            LEFT JOIN (select ship_code, wp, material from meac.".$rpt_period."_cbm group by ship_code, material) cbm
            on po.proj = cbm.ship_code
            and po.item= cbm.material
    where cbm.wp is not null
    $ship_code_wc
    ";
    $i=0;
    //print $sql;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program        = "LCS";
        $ship_code      = intval($rs->fields["ship_code"]);
        $wp             = $rs->fields["wp"];
        $swbs           = $rs->fields["swbs"];
        $cbm_material   = $rs->fields["cbm_material"];
        $material       = $rs->fields["item"];
        $description    = addslashes(processDescription($rs->fields["description"]));
        $noun_1         = $rs->fields["noun_1"];
        $noun_2         = $rs->fields["noun_2"];
        $nre            = $rs->fields["nre"];
        $vendor         = $rs->fields["vendor"];
        $po             = $rs->fields["po"];
        $line           = $rs->fields["line"];
        $unit_price     = formatNumber4decNoComma($rs->fields["unit_price"]);
        $order_qty      = formatNumber4decNoComma($rs->fields["order_qty"]);
        $delivered_qty  = formatNumber4decNoComma($rs->fields["delivered_qty"]);
        $committed_qty  = formatNumber4decNoComma($rs->fields["committed_qty"]);
        $commit_amnt    = formatNumber4decNoComma($rs->fields["commit_amnt"]);
        $delv_date      = $rs->fields["delv_date"];
        $acct_proj_dept = $rs->fields["acct_proj_dept"];
        $clin           = $rs->fields["clin"];
        $effort         = $rs->fields["effort"];

        $sql.=
            "('$program',
        $ship_code,
        '$wp',
        '$swbs',
        '$cbm_material',
        '$material',  
        '$description',
        '$noun_1',
        '$noun_2',
        '$nre',
        '$vendor',
        '$po',
        '$line',
        $unit_price,
        $order_qty,
        $delivered_qty,
        $committed_qty,
        $commit_amnt,
        '$delv_date',
        '$acct_proj_dept',
        '$clin',
        '$effort'),";
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
    /*
    Default to Commodity*/
    /************/
    /************/
    /************/
    /************/

    $sql = "
        select
        proj as ship_code,
        wp,
        swbs,
        cbm.material cbm_material,
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
        effort
        from mars.".$rpt_period."_committed_po po
        left join meac.".$rpt_period."_cbm cbm on
        po.proj = cbm.ship_code
        and po.item= cbm.material
    where cbm.wp is null
    $ship_code_wc
    ";
    $i=0;

    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program        = "LCS";
        $ship_code      = intval($rs->fields["ship_code"]);
        $swbs           = checkSWBSLength($rs->fields["swbs"]);
        $wp             = "MATL-" . $swbs . "-999";
        $cbm_material   = $rs->fields["cbm_material"];
        $material       = $rs->fields["item"];
        $description    = processDescription($rs->fields["description"]);
        $noun_1         = $rs->fields["noun_1"];
        $noun_2         = $rs->fields["noun_2"];
        $nre            = $rs->fields["nre"];
        $vendor         = $rs->fields["vendor"];
        $po             = $rs->fields["po"];
        $line           = $rs->fields["line"];
        $unit_price     = formatNumber4decNoComma($rs->fields["unit_price"]);
        $order_qty      = formatNumber4decNoComma($rs->fields["order_qty"]);
        $delivered_qty  = formatNumber4decNoComma($rs->fields["delivered_qty"]);
        $committed_qty  = formatNumber4decNoComma($rs->fields["committed_qty"]);
        $commit_amnt    = formatNumber4decNoComma($rs->fields["commit_amnt"]);
        $delv_date      = $rs->fields["delv_date"];
        $acct_proj_dept = $rs->fields["acct_proj_dept"];
        $clin           = $rs->fields["clin"];
        $effort         = $rs->fields["effort"];

        $sql.=
            "('$program',
        $ship_code,
        '$wp',
        '$swbs',
        '$cbm_material',
        '$material',  
        '$description',
        '$noun_1',
        '$noun_2',
        '$nre',
        '$vendor',
        '$po',
        '$line',
        $unit_price,
        $order_qty,
        $delivered_qty,
        $committed_qty,
        $commit_amnt,
        '$delv_date',
        '$acct_proj_dept',
        '$clin',
        '$effort'),";
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
function loaditem2buyerRptPeriod($rpt_period){
    truncateTable("meac", $rpt_period."_item2buyer");
    $sql = "insert into
            ".$rpt_period."_item2buyer 
            (item, buyer, ship_code)
            (
              select 
              item, 
              buyer, 
              br.ship_code 
          from ".$rpt_period."_buyer_reponsible br
              left JOIN  ".$rpt_period."_master_buyer mb
                  on br.buyer_id = mb.id
              where br.buyer_id <> 0
                group by ship_code, item )
    ";
    $junk = dbCall($sql, "meac");
}
function loaditem2buyerRptPeriodByShip($ship_code, $rpt_period){
    deleteFromTable("meac", $rpt_period."_item2buyer", "ship_code", $ship_code);
    $sql = "insert into
            ".$rpt_period."_item2buyer 
            (item, buyer, ship_code)
            (
              select 
              item, 
              buyer, 
              br.ship_code 
          from ".$rpt_period."_buyer_reponsible br
              left JOIN  ".$rpt_period."_master_buyer mb
                  on br.buyer_id = mb.id
              where br.buyer_id <> 0 and br.ship_code = $ship_code
                group by ship_code, item )
    ";
    $junk = dbCall($sql, "meac");
}


function loadResponsibleBuyerRptPeriod($ship_code, $rpt_period){
    $sql = "
         select DISTINCT t_cprj, t_buyr, t_item  from ttipcs021490 where t_cprj like '%$ship_code%'
    ";
    $rs = dbCallBaan($sql);
    $insert_sql= "
        insert into ".$rpt_period."_buyer_reponsible 
            (ship_code, buyer_id,item) VALUES
            ";
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
function loadBaanBuyerIDListRptPeriod($rpt_period){
    $sql = "
          Select Distinct 
                a.t_buyr buyer_id,
                c.t_nama buyer
          From ttipcs021490 as a
                join ttccom001490 as c on a.t_buyr = c.t_emno
                Order by t_buyr
    ";
    $rs = dbCallBaan($sql);
    $insert_sql= "INSERT  into meac.".$rpt_period."_master_buyer (id, buyer) values";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    while (!$rs->EOF)
    {
        $buyer_id = intval($rs->fields["buyer_id"]);
        $buyer    = trim($rs->fields["buyer"]);
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
}
function returnBaanEFDBInsertRptPeriod($rpt_period){
    $sql = "INSERT  into ".$rpt_period."_change_item (
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
function loadEFDBChangeBAANRptPeriod($ship_code, $rpt_period){
    $sql        = returnBaanEFDBSQL($ship_code);
    $rs         = dbCallBaan($sql);
    $insert_sql = returnBaanEFDBInsertRptPeriod($rpt_period);
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
function returnOpenBuyInsertRptPeriod($rpt_period){
    $insert_sql = "insert into meac.".$rpt_period."_wp_open_buy(
            program,
            ship_code,
            wp,
            cbm_material,
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
            last_price,
            expected_amt
        ) VALUES ";
    return $insert_sql;
}
function insertCBMFromBaanRptPeriod($ship_code, $rpt_period){

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
    $rs = dbCallBaan($sql);

    $insert_sql = " insert into meac.".$rpt_period."_cbm (program, ship_code, wp, material, budget, assigned_amt, price, qty, pos) values ";
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
        $junk = dbCall($sql, "meac");
    }
}
function insertOpenBuyWithWPRptPeriod($rpt_period, $ship_code=""){
    //$ship_code_wc = "and ob.ship_code = 485";
    if($ship_code==""){
        $ship_code_wc = "";
    }
    else{
        $ship_code_wc = "and ob.ship_code in ($ship_code)";
    }
    $ob = "order by ob.ship_code";
    $gb = "group by ob.ship_code,cbm.wp, ob.item ";
    $sql = "
        select
            ob.program,
            ob.ship_code,
            wp,
            cbm.material cbm_material,
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
            last_price,
            expected_amt
        from mars.".$rpt_period."_open_buy ob
        LEFT JOIN (select ship_code, wp, material from meac.".$rpt_period."_cbm group by ship_code, material) cbm
        on ob.ship_code = cbm.ship_code
        and ob.item = cbm.material 
        where wp is not null  
        $ship_code_wc
        $gb $ob
    ";
    $rs= dbCall($sql, "meac");

    $sql = returnOpenBuyInsertRptPeriod($rpt_period);
    $i=0;
    while (!$rs->EOF)
    {
        $program            = $rs->fields["program"];
        $ship_code          = $rs->fields["ship_code"];
        $wp                 = $rs->fields["wp"];
        $cbm_material       = trim($rs->fields["cbm_material"]);
        $buyer              = trim($rs->fields["buyer"]);
        $swbs               = $rs->fields["swbs"];
        $item               = trim($rs->fields["item"]);
        $spn                = trim($rs->fields["spn"]);
        $description        = processDescription($rs->fields["description"]);
        $origrinal_smos_qty = formatNumber4decNoComma($rs->fields["origrinal_smos_qty"]);
        $remain_smos_qty    = formatNumber4decNoComma($rs->fields["remain_smos_qty"]);
        $yard_due_date      = $rs->fields["yard_due_date"];
        $lead_time          = formatNumber4decNoComma($rs->fields["lead_time"]);
        $plan_order_date    = $rs->fields["plan_order_date"];
        $uom                = $rs->fields["uom"];
        $item_on_hand       = formatNumber4decNoComma($rs->fields["item_on_hand"]);
        $item_on_order      = formatNumber4decNoComma($rs->fields["item_on_order"]);
        $item_shortage      = formatNumber4decNoComma($rs->fields["item_shortage"]);
        $on_hold            = trim($rs->fields["on_hold"]);
        $entered_on         = $rs->fields["entered_on"];
        $last_mod           = $rs->fields["last_mod"];
        $last_price         = formatNumber4decNoComma($rs->fields["last_price"]);
        $expected_amt       = formatNumber4decNoComma($rs->fields["expected_amt"]);

        $sql.= insertOpenBuyValues($program,$ship_code,$wp,$cbm_material,$buyer,$swbs,
            $item,$spn,$description,$origrinal_smos_qty,$remain_smos_qty,
            $yard_due_date,$lead_time,$plan_order_date,$uom,$item_on_hand,
            $item_on_order,$item_shortage,$on_hold,$entered_on,$last_mod,
            $last_price,$expected_amt);
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = returnOpenBuyInsertRptPeriod($rpt_period);
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

    /*
     * DEFAULT TO COMMODITY
     * DEFAULT TO COMMODITY
     * DEFAULT TO COMMODITY
     * DEFAULT TO COMMODITY
     * */
    $sql = "
        select
            ob.program,
            ob.ship_code,
            concat('MATL-',swbs, '-999') as wp,
            cbm.material cbm_material,
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
            last_price,
            expected_amt
        from mars.".$rpt_period."_open_buy ob left join meac.".$rpt_period."_cbm cbm
        on ob.ship_code = cbm.ship_code
        and ob.item = cbm.material
        where wp is null
        $ship_code_wc
        $gb $ob
    ";
    $rs= dbCall($sql, "meac");
    $sql = returnOpenBuyInsertRptPeriod($rpt_period);
    $i=0;
    while (!$rs->EOF)
    {
        $program            = $rs->fields["program"];
        $ship_code          = $rs->fields["ship_code"];
        $wp                 = $rs->fields["wp"];
        $cbm_material       = $rs->fields["cbm_material"];
        $buyer              = trim($rs->fields["buyer"]);
        $swbs               = $rs->fields["swbs"];
        $item               = $rs->fields["item"];
        $spn                = $rs->fields["spn"];
        $description        = processDescription($rs->fields["description"]);
        $origrinal_smos_qty = formatNumber4decNoComma($rs->fields["origrinal_smos_qty"]);
        $remain_smos_qty    = formatNumber4decNoComma($rs->fields["remain_smos_qty"]);
        $yard_due_date      = $rs->fields["yard_due_date"];
        $lead_time          = formatNumber4decNoComma($rs->fields["lead_time"]);
        $plan_order_date    = $rs->fields["plan_order_date"];
        $uom                = $rs->fields["uom"];
        $item_on_hand       = formatNumber4decNoComma($rs->fields["item_on_hand"]);
        $item_on_order      = formatNumber4decNoComma($rs->fields["item_on_order"]);
        $item_shortage      = formatNumber4decNoComma($rs->fields["item_shortage"]);
        $on_hold            = trim($rs->fields["on_hold"]);
        $entered_on         = $rs->fields["entered_on"];
        $last_mod           = $rs->fields["last_mod"];
        $last_price         = formatNumber4decNoComma($rs->fields["last_price"]);
        $expected_amt       = formatNumber4decNoComma($rs->fields["expected_amt"]);

        $sql.= insertOpenBuyValues($program,$ship_code,$wp,$cbm_material,$buyer,$swbs,
            $item,$spn,$description,$origrinal_smos_qty,$remain_smos_qty,
            $yard_due_date,$lead_time,$plan_order_date,$uom,$item_on_hand,
            $item_on_order,$item_shortage,$on_hold,$entered_on,$last_mod,
            $last_price,$expected_amt);
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");

            $i=0;
            //clear out the sql stmt.
            $sql = returnOpenBuyInsertRptPeriod($rpt_period);
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
function insertEBOMWPRptPeriod($rpt_period, $ship_code=""){
    $insert_sql = "

        insert into meac.".$rpt_period."_wp_ebom (
            program,
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
            wp) VALUES 
       ";
    $sql = returnEBOMBaanSQLMappedWP($ship_code);
    $i=0;
    $rs= dbCallBaan($sql);
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $ship_code    = intval(trim($rs->fields["ship_code"]));
        $wp           = trim($rs->fields["wp"]);
        $material     = trim($rs->fields["item"]);
        $spn          = trim($rs->fields["spn"]);
        $uom          = trim($rs->fields["uom"]);
        $item_group   = trim($rs->fields["item_group"]);
        $description   = processDescription(trim($rs->fields["description"]));
        $swbs         = trim(checkSWBSLength($rs->fields["swbs"]));
        $ebom         = formatNumber4decNoComma($rs->fields["ebom"]);
        $noun1        = trim($rs->fields["noun1"]);
        $noun2        = trim($rs->fields["noun2"]);
        $noun3        = trim($rs->fields["noun3"]);

        $sql.=
            "(
                'LCS',
                $ship_code,
                '$material',
                '$spn',
                '$uom',
                '$item_group',
                '$swbs',
                $ebom,
                '$noun1',
                '$noun2',
                '$noun3',
                '$description',
                '$wp'
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
    /*
    Default to Commodity*/
    /************/
    /************/
    /************/
    /************/
    $sql = returnEBOMBaanSQLNOTMappedWP($ship_code);
    $i=0;
    $rs= dbCallBaan($sql);
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $ship_code   = intval(trim($rs->fields["ship_code"]));
        $material    = trim($rs->fields["item"]);
        $spn         = trim($rs->fields["spn"]);
        $uom         = trim($rs->fields["uom"]);
        $item_group  = trim($rs->fields["item_group"]);
        $description = processDescription(trim($rs->fields["description"]));
        $swbs        = trim(checkSWBSLength($rs->fields["swbs"]));
        $wp          = "MATL-" . $swbs . "-999";
        $ebom        = formatNumber4decNoComma($rs->fields["ebom"]);
        $noun1       = trim($rs->fields["noun1"]);
        $noun2       = trim($rs->fields["noun2"]);
        $noun3       = trim($rs->fields["noun3"]);

        $sql.=
            "(
                'LCS',
                $ship_code,
                '$material',
                '$spn',
                '$uom',
                '$item_group',
                '$swbs',
                $ebom,
                '$noun1',
                '$noun2',
                '$noun3',
                '$description',
                '$wp'
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
}
function insertOpenPOWithWPRptPeriod($rpt_period, $ship_code=""){
    if($ship_code==""){
        $ship_code_wc = "";
    }
    else{
        $ship_code_wc = "and po.proj in ($ship_code)";
    }
    $insert_sql = "
    insert into meac.".$rpt_period."_wp_open_po (
            ship_code,
            wp,
            swbs,
            cbm_material,
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
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort,
            ecp_rea) VALUES ";
    $sql = "
    select
            po.proj,
            wp,
            swbs,
            cbm.material cbm_material,
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
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort,
            ecp_rea
        from mars.".$rpt_period."_open_po po  
        LEFT JOIN 
            (select ship_code, wp, material from meac.".$rpt_period."_cbm group by ship_code, material) cbm
        on po.proj = cbm.ship_code
        and po.item = cbm.material
        where cbm.wp is not null
        $ship_code_wc
    ";
    //print $sql;
    $i=0;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $ship_code     = $rs->fields["proj"];
        $wp            = $rs->fields["wp"];
        $swbs          = $rs->fields["swbs"];
        $cbm_material  = addslashes(trim($rs->fields["cbm_material"]));
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
        $ecp_rea       = addslashes(trim($rs->fields["ecp_rea"]));

        $sql.=
            "(
                $ship_code,
                '$wp',
                $swbs,
                '$cbm_material',
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
    /*
    Default to Commodity*/
    /************/
    /************/
    /************/
    /************/
    $insert_sql = "
    insert into meac.".$rpt_period."_wp_open_po (
            ship_code,
            wp,
            swbs,
            cbm_material,
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
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort,
            ecp_rea) VALUES ";
    $sql = "
    select
        po.proj,
        swbs,
        cbm.material cbm_material,
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
        pending_qty,
        pending_amnt,
        delv_date,
        payment_terms,
        ledger_acct,
        clin,
        effort,
        ecp_rea
    from mars.".$rpt_period."_open_po po 
    left join meac.".$rpt_period."_cbm cbm on
        po.proj = cbm.ship_code
        and po.item = cbm.material
        where cbm.wp is null
        $ship_code_wc
    ";
    $i=0;
    //print $sql;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $ship_code     = $rs->fields["proj"];
        $swbs          = checkSWBSLength($rs->fields["swbs"]);
        $wp            = "MATL-".$swbs."-999";

        $cbm_material  = addslashes(trim($rs->fields["cbm_material"]));
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
        $ecp_rea        = addslashes(trim($rs->fields["ecp_rea"]));

        $sql.=
            "(
                $ship_code,
                '$wp',
                $swbs,
                '$cbm_material',
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
    /************/
    /************/
    /************/
}
function insertGLdetailWITHWPRptPeriod($rpt_period, $ship_code=""){
    /*
     * insert the GL with the matching WP and swbs.  So we can sum everything together properly.
     * items that dont match in the CBM get defaulted to the Commodity.
     * */
    if($ship_code==""){
        $ship_code_wc = "";
    }
    else{
        $ship_code_wc = "and gl.proj in ($ship_code)";
    }
    $insert_sql = "
    INSERT  INTO meac.".$rpt_period."_wp_gl_detail (
        ship_code, 
        item,
        wp,
        cbm_material,
        swbs,
        ldger_acct,
        document,
        line,
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
        no_cost_transfers
        )(
    SELECT
        gl.proj,
        gl.item,
        cbm.wp,
        cbm.material AS cbm_material,
        right(ldger_acct, 3) as swbs,
        ldger_acct,
        document,
        line,
        description,
        `order`,
        gl.pos,
        cust_supp,
        gl.qty,
        unit,
        amt,
        date,
        integr_amt,
        clin,
        effort,
        ecp_rea,
        case
          when  qty > 0
                and integr_amt = 0
                and document like 'INV%'
          then (select qty from ".$rpt_period."_inv_transfers inv where inv.ship_code = gl.proj and inv.item = gl.item and gl.`order` = inv.`order`)
          else 0
        end as no_cost_transfers
    FROM mars.".$rpt_period."_gl_detail gl LEFT JOIN (select ship_code, wp, material from meac.".$rpt_period."_cbm group by ship_code, material) cbm
      ON cbm.ship_code = gl.proj
      AND cbm.material = gl.item
      where cbm.wp is not null 
      $ship_code_wc
      
    )";
    //$sql = "and gl.description not like '%total%'";
    $junk = dbCall($insert_sql,"meac");
    $insert_sql = "
    INSERT  INTO meac.".$rpt_period."_wp_gl_detail (
        ship_code, 
        item,
        wp,
        cbm_material,
        swbs,
        ldger_acct,
        document,
        line,
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
        no_cost_transfers
        )(
    SELECT
        gl.proj,
        gl.item,
      concat('MATL-',right(ldger_acct, 3), '-999') as wp,
        'NA' AS cbm_material,
        right(ldger_acct, 3) as swbs,
        ldger_acct,
        document,
        line,
        description,
        `order`,
        gl.pos,
        cust_supp,
        gl.qty,
        unit,
        amt,
        date,
        integr_amt,
        clin,
        effort,
        ecp_rea,
        case
          when  qty > 0
                and integr_amt = 0
                and document like 'INV%'
          then (select qty from ".$rpt_period."_inv_transfers inv where inv.ship_code = gl.proj and inv.item = gl.item and gl.`order` = inv.`order`)
          else 0
        end as no_cost_transfers
    FROM mars.".$rpt_period."_gl_detail gl  LEFT JOIN (select ship_code, wp, material from meac.".$rpt_period."_cbm group by ship_code, material) cbm
      ON cbm.ship_code = gl.proj
      AND cbm.material = gl.item
      where cbm.wp is  null 
      and gl.description not like '%Total:%'
      $ship_code_wc
    )";
    $junk = dbCall($insert_sql,"meac");
}
function loadFortisPODataRptPeriod($ship_code= "", $rpt_period){
    $sql        = returnFortisPOSQL($ship_code);
    $rs         = dbCallFortis($sql);
    $insert_sql = returnFortisPOInsertSQLRptPeriod($rpt_period);
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
        $buyer                    = processDescription(trim($rs->fields["buyer_name"]));
        $po_type                  = trim($rs->fields["purchase_order_type"]);
        $funding_source           = trim($rs->fields["funding_source"]);
        $status                   = trim($rs->fields["fortisstatus"]);
        $order_date               = $rs->fields["order_date"];
        $created_date             = $rs->fields["created_date"];
        $modified_date            = $rs->fields["modified_date"];
        $amt                      = formatNumber4decNoComma($rs->fields["total_amount"]);
        $vendor_total             = formatNumber4decNoComma($rs->fields["vendor_project_total"]);
        $purchasing_manager_notes = processDescription(trim($rs->fields["purchasing_manager_notes"]));

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
}
function returnFortisPOInsertSQLRptPeriod($rpt_period){
    $insert_sql = "
    INSERT  into ".$rpt_period."_po_data (
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
function insertSWBSGLSUMRptPeriod($ship_code, $rpt_period){
    $insert_sql = returnInsertSQLSWBSSumRptPeriod($rpt_period, "swbs_gl_summary_stage");
    $ob = "gl.ship_code, gl.wp, gl.item";
    $gb = "gl.ship_code, gl.wp, gl.item";
    $sql = "
    select
            gl.ship_code,
            gl.wp,
            case when
                (select category from category cat where cat.ship_code = gl.ship_code and cat.item = gl.item limit 1 ) is null then
                (select category from category cat where cat.item = gl.item limit 1)
            ELSE
                (select category from category cat where cat.ship_code = gl.ship_code and cat.item = gl.item limit 1 )
            END as category,
            case when CHAR_LENGTH(gl.swbs) = 3 then concat(left(gl.swbs,1),'00') ELSE
              '000' end as swbs_group,
            gl.swbs,
            e.spn,
            gl.item,
            e.item_group,
            (select ig.description from meac.item_group ig where ig.item_group= e.item_group limit 1) item_group_description,
            e.noun1,
            gl.description,
            gl.unit,
            e.ebom,
            e.issued ebom_issued,
            e.on_hand ebom_onhand,
            (select ext_cost from meac.target_cost tc where tc.item=gl.item  limit 1) target_ext_cost,
            (select qty from meac.target_cost tc where tc.item=gl.item  limit 1) target_qty,
            (select unit_cost  from meac.target_cost tc where tc.item=gl.item  limit 1) target_unit_cost,
            gl.cust_supp as vendor_name,
            gl.document as document,
            (SELECT GROUP_CONCAT(DISTINCT CONCAT(`order`, '-', pos)) FROM meac.".$rpt_period."_wp_gl_detail gl2 where gl2.item= gl.item and gl2.ship_code= gl.ship_code ) po_data,
            (SELECT GROUP_CONCAT(DISTINCT CONCAT(`origins`, ' - ')) FROM meac.k2_efdb k2 where k2.item= gl.item and k2.ship_code= gl.ship_code ) tc,
            (select ci.date from meac.".$rpt_period."_change_item ci where ci.ship_code=gl.ship_code and ci.item=gl.item  order by ci.date DESC limit 1) change_date,
            (select ci.description from meac.".$rpt_period."_change_item ci where ci.ship_code=gl.ship_code and ci.item=gl.item  order by ci.date DESC limit 1) change_reason,
            (select vendor from meac.".$rpt_period."_wp_committed_po wpc where wpc.ship_code=gl.ship_code and wpc.item = gl.item limit 1) vendor_id,
            (select item_shortage from meac.".$rpt_period."_wp_open_buy ob where ob.ship_code=gl.ship_code and ob.item=gl.item  limit 1) open_buy_item_shortage,
            (select sum(pending_amnt) from meac.".$rpt_period."_wp_open_po opo where opo.ship_code=gl.ship_code and opo.item=gl.item) open_po_pending_amt,
            (select sum(integr_amt) from meac.".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%') transfers,
            (select sum(commit_amnt) from meac.".$rpt_period."_wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_amt,
            (select avg(unit_price) from meac.".$rpt_period."_wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) c_unit_price,
            (select unit_price from meac.".$rpt_period."_wp_committed_po c where c.item=gl.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
            (select c.ship_code from meac.".$rpt_period."_wp_committed_po c where c.item=gl.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
            (select sum(committed_qty) from meac.".$rpt_period."_wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_qty,
            (select sum(qty) from meac.".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%PUR%' and gl2.integr_amt < 0) gl_pur_qty_off,
            (select sum(qty) from meac.".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%PUR%' and gl2.integr_amt > 0) gl_pur_qty_on,
            (select sum(qty) from meac.".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt < 0) gl_qty_transfers_off,
            (select sum(qty) from meac.".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt > 0) gl_qty_transfers_on,
            (select sum(no_cost_transfers) from meac.".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item) no_cost_transfers,
            sum(gl.integr_amt) int_amt,
            case when
                (select buyer from ".$rpt_period."_item2buyer br where br.ship_code = gl.ship_code and br.item = gl.item limit 1 ) is null then
                (select buyer from ".$rpt_period."_item2buyer br where br.item = gl.item limit 1)
            ELSE
                (select buyer from ".$rpt_period."_item2buyer br where br.ship_code = gl.ship_code and br.item = gl.item limit 1 )
            END as buyer,
            gl.ecp_rea as ecp_rea,
            gl.clin as clin,
            gl.effort as effort
        from meac.".$rpt_period."_wp_gl_detail gl
        left join meac.".$rpt_period."_wp_ebom e
                on e.ship_code = gl.ship_code and e.item = gl.item
        left join meac.".$rpt_period."_wp_open_buy open_buy
            on gl.ship_code = open_buy.ship_code and gl.item= open_buy.item
        where gl.ship_code = $ship_code 
        group by $gb
        order by $ob 
      ";
    $i=0;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                 = "LCS";
        $ship_code               = $rs->fields["ship_code"];
        $category                = $rs->fields["category"];
        $wp                      = $rs->fields["wp"];
        $swbs_group              = $rs->fields["swbs_group"];
        $swbs                    = $rs->fields["swbs"];
        $spn                     = $rs->fields["spn"];
        $item                    = $rs->fields["item"];
        $item_group              = $rs->fields["item_group"];
        $noun1                   = $rs->fields["noun1"];
        $tc                      = $rs->fields["tc"];
        $item_group_description  = $rs->fields["item_group_description"];
        $description             = processDescription($rs->fields["description"]);
        $unit                    = $rs->fields["unit"];
        $change_date            = fixExcelDateMySQL($rs->fields["change_date"]);
        $change_reason           = processDescription($rs->fields["change_reason"]);
        $ebom                    = formatNumber4decNoComma($rs->fields["ebom"]);
        $ebom_issued             = formatNumber4decNoComma($rs->fields["ebom_issued"]);
        $ebom_on_hand            = formatNumber4decNoComma($rs->fields["ebom_onhand"]);
        $transfers               = formatNumber4decNoComma($rs->fields["transfers"]);
        $last_unit_price_ship    = $rs->fields["last_unit_price_ship"];
        $ecp_rea                 = $rs->fields["ecp_rea"];
        $clin                    = $rs->fields["clin"];
        $effort                  = $rs->fields["effort"];
        $po_data                 = addslashes($rs->fields["po_data"]);
        $open_buy_item_shortage  = formatNumber4decNoComma($rs->fields["open_buy_item_shortage"]);
        $open_po_pending_amt     = formatNumber4decNoComma($rs->fields["open_po_pending_amt"]);
        $c_amt                   = formatNumber4decNoComma($rs->fields["commit_amt"]);
        $last_unit_price         = formatNumber4decNoComma($rs->fields["last_unit_price"]);
        $gl_int_amt              = formatNumber4decNoComma($rs->fields["int_amt"]);
        $target_qty              = formatNumber4decNoComma($rs->fields["target_qty"]);
        $target_unit_price       = formatNumber4decNoComma($rs->fields["target_unit_cost"]);
        $target_ext_cost         = formatNumber4decNoComma($rs->fields["target_ext_cost"]);
        $c_unit_price            = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name             = processDescription($rs->fields["vendor_name"]);
        $vendor_id               = intval($rs->fields["vendor_id"]);
        $buyer                   = trim($rs->fields["buyer"]);
        $document                = $rs->fields["document"];
        $c_qty                   = formatNumber4decNoComma($rs->fields["commit_qty"]);

        $gl_pur_qty_off          = formatNumber4decNoComma($rs->fields["gl_pur_qty_off"]);
        $gl_pur_qty_on           = formatNumber4decNoComma($rs->fields["gl_pur_qty_on"]);
        $gl_qty_transfers_off    = formatNumber4decNoComma($rs->fields["gl_qty_transfers_off"]);
        $gl_qty_transfers_on     = formatNumber4decNoComma($rs->fields["gl_qty_transfers_on"]);
        $no_cost_transfers       = formatNumber4decNoComma($rs->fields["no_cost_transfers"]);

        $gl_qty          = $gl_pur_qty_on - $gl_pur_qty_off + $gl_qty_transfers_on - $gl_qty_transfers_off+ $no_cost_transfers;
        $c_qty           = $c_qty + $gl_qty_transfers_on - $gl_qty_transfers_off+ $no_cost_transfers;

        $var_target_qty  = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom        = calcVarEBOM($ebom, $c_qty, $category, $description);
        $etc             = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage, $var_ebom);
        $eac             = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted     = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);

        $sql.=createInsertValuesString($program, $ship_code,$category,$swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name,
            $vendor_id, $buyer,$c_qty, $var_target_qty,
            $var_target_cost, $gl_qty, $var_ebom,
            $c_unit_price, $document, $ecp_rea,$clin,
            $effort, $po_data, $tc,$item_group_description, $change_date, $change_reason);

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
function returnInsertSQLSWBSSumRptPeriod($rpt_period, $table_name){
    $table  = $rpt_period."_".$table_name;
    $insert_sql = "
    insert into meac.$table(
    program,
    ship_code,
    category,
    swbs_group,
    swbs,
    wp,
    spn,
    item,
    item_group,
    description,
    unit,
    noun1,
    transfers,
    c_amt,
    last_unit_price,
    gl_int_amt,
    ebom,
    ebom_on_hand,
    ebom_issued,
    open_po_pending_amt,
    open_buy_item_shortage,
    last_unit_price_ship,
    etc,
    eac,
    uncommitted,
    target_qty,
    target_unit_price,
    target_ext_cost,
    vendor_name,
    vendor_id,
    buyer,
    c_qty,
    var_target_qty,
    var_target_cost,
    gl_qty,
    var_ebom,
    c_unit_price,
    document,
    clin,
    effort,
    ecp_rea,
    po_data,
    tc,
    item_group_description,
    change_date,
    change_reason
    ) VALUES 
";
    return $insert_sql;
}
function insertSWBSSummaryOPENPORptPeriod($ship_code, $rpt_period)
{
    $ob = "open_po.ship_code, open_po.wp, open_po.item";
    $gb = "open_po.ship_code, open_po.wp, open_po.item";
    $insert_sql = returnInsertSQLSWBSSumRptPeriod($rpt_period, "swbs_gl_summary_stage");
    $sql = "
                select
                    open_po.ship_code,
                    open_po.wp,
                    case when
                        (select category from category cat where cat.ship_code = open_po.ship_code and cat.item = open_po.item limit 1 ) is null then
                        (select category from category cat where cat.item = open_po.item limit 1)
                    ELSE
                        (select category from category cat where cat.ship_code = open_po.ship_code and cat.item = open_po.item limit 1 )
                    END as category,
                    case when CHAR_LENGTH(open_po.swbs) = 3 then concat(left(open_po.swbs,1),'00') 
                      ELSE '000' end as swbs_group,
                    open_po.swbs,
                    e.spn,
                    open_po.item,
                    e.item_group,
                    (select ig.description from meac.item_group ig where ig.item_group= e.item_group limit 1) item_group_description,
                    e.noun1,
                    open_po.description,
                    e.uom as unit,
                    e.ebom,
                    e.issued ebom_issued,
                    e.on_hand ebom_onhand,
                    concat(open_po.po, ' - ', open_po.line) po_data,
                    (SELECT GROUP_CONCAT(DISTINCT CONCAT(`origins`, ' - ')) FROM meac.k2_efdb k2 where k2.item= open_po.item and k2.ship_code= open_po.ship_code ) tc,
                    (select ext_cost from target_cost tc where tc.item=open_po.item  limit 1) target_ext_cost,
                    (select ci.date from meac.".$rpt_period."_change_item ci where ci.ship_code=open_po.ship_code and ci.item=open_po.item  order by ci.date DESC limit 1) change_date,
                    (select ci.description from meac.".$rpt_period."_change_item ci where ci.ship_code=open_po.ship_code and ci.item=open_po.item  order by ci.date DESC limit 1) change_reason,
                    (select qty from target_cost tc where tc.item=open_po.item  limit 1) target_qty,
                    (select unit_cost  from target_cost tc where tc.item=open_po.item  limit 1) target_unit_cost,
                    (SELECT vendor FROM meac.".$rpt_period."_po_data po_data where open_po.vendor=po_data.vendor_id  limit 1) vendor_name,
                    gl.document as document,
                    open_po.vendor as vendor_id,
                    (select item_shortage from ".$rpt_period."_wp_open_buy ob where ob.ship_code=open_po.ship_code and ob.item=open_po.item  limit 1) open_buy_item_shortage,
                    (select sum(pending_amnt) from ".$rpt_period."_wp_open_po opo where opo.ship_code=open_po.ship_code and opo.item=open_po.item) open_po_pending_amt,
                    (select sum(integr_amt) from ".$rpt_period."_wp_gl_detail gl2 where gl2.ship_code=open_po.ship_code and gl2.item=open_po.item and gl2.document like '%INV%') transfers,
                    (select sum(commit_amnt) from ".$rpt_period."_wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) commit_amt,
                    (select avg(unit_price) from ".$rpt_period."_wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) c_unit_price,
                    (select unit_price from ".$rpt_period."_wp_committed_po c where c.item=open_po.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
                    (select c.ship_code from ".$rpt_period."_wp_committed_po c where c.item=open_po.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
                    (select sum(committed_qty) from ".$rpt_period."_wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) commit_qty,
                    0 as gl_qty,
                    0 as int_amt,
                    open_po.ecp_rea,
                    open_po.clin,
                    open_po.effort,
                    case when
                        (select buyer from ".$rpt_period."_item2buyer br where br.ship_code = open_po.ship_code and br.item = open_po.item limit 1 ) is null then
                        (select buyer from ".$rpt_period."_item2buyer br where br.item = open_po.item limit 1)
                    ELSE
                        (select buyer from ".$rpt_period."_item2buyer br where br.ship_code = open_po.ship_code and br.item = open_po.item limit 1 )
                    END as buyer
                from ".$rpt_period."_wp_open_po open_po
                left join ".$rpt_period."_wp_ebom e
                  on e.ship_code = open_po.ship_code and e.item =open_po.item
                left join ".$rpt_period."_wp_gl_detail gl
                  on open_po.ship_code = gl.ship_code and open_po.item= gl.item
                left join ".$rpt_period."_wp_open_buy open_buy
                    on open_po.ship_code = open_buy.ship_code and open_po.item= open_buy.item
                where gl.ship_code is null
                  and open_po.ship_code = $ship_code
                group by $gb
                order by $ob
";

    $i=0;

    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                = "LCS";
        $ship_code              = $rs->fields["ship_code"];
        $category               = $rs->fields["category"];
        $wp                     = $rs->fields["wp"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $noun1                  = $rs->fields["noun1"];
        $ecp_rea                = $rs->fields["ecp_rea"];
        $clin                   = $rs->fields["clin"];
        $effort                 = $rs->fields["effort"];
        $tc                     = $rs->fields["tc"];
        $item_group_description = $rs->fields["item_group_description"];
        $description            = processDescription($rs->fields["description"]);
        $unit                   = $rs->fields["unit"];
        $change_date            = fixExcelDateMySQL($rs->fields["change_date"]);
        $change_reason          = processDescription($rs->fields["change_reason"]);
        $ebom                   = formatNumber4decNoComma($rs->fields["ebom"]);
        $ebom_issued            = formatNumber4decNoComma($rs->fields["ebom_issued"]);
        $ebom_on_hand           = formatNumber4decNoComma($rs->fields["ebom_onhand"]);
        $transfers              = formatNumber4decNoComma($rs->fields["transfers"]);
        $last_unit_price_ship   = $rs->fields["last_unit_price_ship"];
        $open_buy_item_shortage = formatNumber4decNoComma($rs->fields["open_buy_item_shortage"]);
        $open_po_pending_amt    = formatNumber4decNoComma($rs->fields["open_po_pending_amt"]);
        $c_amt                  = formatNumber4decNoComma($rs->fields["commit_amt"]);
        $last_unit_price        = formatNumber4decNoComma($rs->fields["last_unit_price"]);
        $gl_int_amt             = formatNumber4decNoComma($rs->fields["int_amt"]);
        $target_qty             = formatNumber4decNoComma($rs->fields["target_qty"]);
        $target_unit_price      = formatNumber4decNoComma($rs->fields["target_unit_cost"]);
        $target_ext_cost        = formatNumber4decNoComma($rs->fields["target_ext_cost"]);
        $c_unit_price           = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name            = processDescription($rs->fields["vendor_name"]);
        $vendor_id              = intval($rs->fields["vendor_id"]);
        $buyer                  = trim($rs->fields["buyer"]);
        $document               = $rs->fields["document"];
        $po_data                = $rs->fields["po_data"];

        $c_qty                  = formatNumber4decNoComma($rs->fields["commit_qty"]);

        $gl_qty                 = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $var_target_qty         = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost        = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom               = calcVarEBOM($ebom, $c_qty, $category, $description);
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage, $var_ebom);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);

        $sql.=createInsertValuesString($program, $ship_code,$category, $swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name, $vendor_id, $buyer,
            $c_qty, $var_target_qty, $var_target_cost, $gl_qty, $var_ebom,
            $c_unit_price, $document, $ecp_rea,$clin,
            $effort, $po_data, $tc,$item_group_description, $change_date,$change_reason );
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
function insertSWBSSUmmaryEBOMRptPeriod($ship_code, $rpt_period){
    $ob = "e.ship_code, e.wp, e.item";
    $gb = "e.ship_code, e.wp, e.item";
    $insert_sql = returnInsertSQLSWBSSumRptPeriod($rpt_period, "swbs_gl_summary_stage");
    $sql = "
     select
            e.ship_code,
            e.wp,
            case when
                (select category from category cat where cat.ship_code = e.ship_code and cat.item = e.item limit 1 ) is null then
                (select category from category cat where cat.item = e.item limit 1)
            ELSE
                (select category from category cat where cat.ship_code = e.ship_code and cat.item = e.item limit 1 )
            END as category,
            case when CHAR_LENGTH(e.swbs) = 3 then concat(left(e.swbs,1),'00')
              ELSE '000' end as swbs_group,
            e.swbs,
            e.spn,
            e.item as item,
            '' as description,
            e.item_group,
            (select ig.description from meac.item_group ig where ig.item_group= e.item_group limit 1) item_group_description,
            e.noun1,
            e.uom as unit,
            e.ebom,
            '' po_data,
            (SELECT GROUP_CONCAT(DISTINCT CONCAT(`origins`, ' - ')) FROM meac.k2_efdb k2 where k2.item= e.item and k2.ship_code= e.ship_code ) tc,
            (select ext_cost from target_cost tc where tc.item=e.item limit 1) target_ext_cost,
            (select ci.date from meac.".$rpt_period."_change_item ci where ci.ship_code=e.ship_code and ci.item=e.item order by ci.date DESC limit 1) change_date,
            (select ci.description from meac.".$rpt_period."_change_item ci where ci.ship_code=e.ship_code and ci.item=e.item order by ci.date DESC limit 1) change_reason,
            (select qty from target_cost tc where tc.item=e.item limit 1) target_qty,
            (select unit_cost  from target_cost tc where tc.item=e.item limit 1) target_unit_cost,
            '' vendor_name,
            '' document,
            '' as vendor_id,
            '' open_buy_item_shortage,
            '' open_po_pending_amt,
            '' transfers,
            '' commit_amt,
            '' c_unit_price,
            (select unit_price from ".$rpt_period."_wp_committed_po c where c.item=e.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
            '' last_unit_price_ship,
            '' commit_qty,
            0 as gl_qty,
            0 as int_amt,
            '' ecp_rea,
            '' clin,
            '' effort,
            case when
                (select buyer from ".$rpt_period."_item2buyer br where br.ship_code = e.ship_code and br.item = e.item limit 1 ) is null then
                (select buyer from ".$rpt_period."_item2buyer br where br.item = e.item limit 1)
            ELSE
              (select buyer from ".$rpt_period."_item2buyer br where br.ship_code = e.ship_code and br.item = e.item limit 1 )
            END as buyer
        from ".$rpt_period."_wp_ebom e
        left join ".$rpt_period."_wp_gl_detail gl
          on gl.ship_code= e.ship_code
          and gl.item = e.item
        left join ".$rpt_period."_wp_open_buy ob
          on ob.ship_code= e.ship_code
          and ob.item = e.item
        left join ".$rpt_period."_wp_open_po po
          on po.ship_code= e.ship_code
          and po.item = e.item
        where
          e.ship_code = $ship_code and
          gl.ship_code is null and
          ob.ship_code is null and
          po.ship_code is null
          and e.ebom > 0
                group by $gb
                order by $ob
                ";

    $i=0;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                = "LCS";
        $ship_code              = $rs->fields["ship_code"];
        $category               = $rs->fields["category"];
        $wp                     = $rs->fields["wp"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $noun1                  = $rs->fields["noun1"];
        $ecp_rea                = $rs->fields["ecp_rea"];
        $clin                   = $rs->fields["clin"];
        $effort                 = $rs->fields["effort"];
        $tc                     = $rs->fields["tc"];
        $item_group_description = $rs->fields["item_group_description"];
        $description            = processDescription($rs->fields["description"]);
        $unit                   = $rs->fields["unit"];
        $change_date            = fixExcelDateMySQL($rs->fields["change_date"]);
        $change_reason          = processDescription($rs->fields["change_reason"]);
        $ebom                   = formatNumber4decNoComma($rs->fields["ebom"]);
        $ebom_issued            = formatNumber4decNoComma($rs->fields["ebom_issued"]);
        $ebom_on_hand           = formatNumber4decNoComma($rs->fields["ebom_onhand"]);
        $transfers              = formatNumber4decNoComma($rs->fields["transfers"]);
        $last_unit_price_ship   = $rs->fields["last_unit_price_ship"];
        $open_buy_item_shortage = formatNumber4decNoComma($rs->fields["open_buy_item_shortage"]);
        $open_po_pending_amt    = formatNumber4decNoComma($rs->fields["open_po_pending_amt"]);
        $c_amt                  = formatNumber4decNoComma($rs->fields["commit_amt"]);
        $last_unit_price        = formatNumber4decNoComma($rs->fields["last_unit_price"]);
        $gl_int_amt             = formatNumber4decNoComma($rs->fields["int_amt"]);
        $target_qty             = formatNumber4decNoComma($rs->fields["target_qty"]);
        $target_unit_price      = formatNumber4decNoComma($rs->fields["target_unit_cost"]);
        $target_ext_cost        = formatNumber4decNoComma($rs->fields["target_ext_cost"]);
        $c_unit_price           = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name            = processDescription($rs->fields["vendor_name"]);
        $vendor_id              = intval($rs->fields["vendor_id"]);
        $buyer                  = trim($rs->fields["buyer"]);
        $document               = $rs->fields["document"];
        $po_data                = $rs->fields["po_data"];

        $c_qty                  = formatNumber4decNoComma($rs->fields["commit_qty"]);

        $gl_qty                 = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $var_target_qty         = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost        = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom               = calcVarEBOM($ebom, $c_qty, $category, $description);
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage, $var_ebom);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);

        $sql.=createInsertValuesString($program, $ship_code,$category, $swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name, $vendor_id, $buyer,
            $c_qty, $var_target_qty, $var_target_cost, $gl_qty, $var_ebom,
            $c_unit_price, $document, $ecp_rea,$clin,
            $effort, $po_data, $tc,$item_group_description, $change_date,$change_reason );
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
function insertSWBSSummaryOPENBUYRptPeriod($ship_code, $rpt_period)
{
    $insert_sql = returnInsertSQLSWBSSumRptPeriod($rpt_period, "swbs_gl_summary_stage");
    $ob = "open_buy.ship_code, open_buy.wp, open_buy.item";
    $sql = "
            select open_buy.ship_code,
                open_buy.wp,
                case when
                    (select category from category cat where cat.ship_code = open_buy.ship_code and cat.item = open_buy.item limit 1 ) is null then
                    (select category from category cat where cat.item = open_buy.item limit 1)
                ELSE
                    (select category from category cat where cat.ship_code = open_buy.ship_code and cat.item = open_buy.item limit 1 )
                END as category,
                case when CHAR_LENGTH(open_buy.swbs) = 3 then concat(left(open_buy.swbs,1),'00') ELSE
                  '000' end as swbs_group,
                open_buy.swbs,
                e.spn,
                open_buy.item,
                e.item_group,
               (select ig.description from meac.item_group ig where ig.item_group= e.item_group limit 1) item_group_description,
                e.noun1,
                open_buy.description,
                e.uom as unit,
                e.ebom,
                e.issued ebom_issued,
                e.on_hand ebom_onhand,
                open_buy.item_shortage open_buy_item_shortage,
                (SELECT GROUP_CONCAT(DISTINCT CONCAT(`origins`, ' - ')) FROM meac.k2_efdb k2 where k2.item= open_buy.item and k2.ship_code= open_buy.ship_code ) tc,
                (select ext_cost from target_cost tc where tc.item=open_buy.item  limit 1) target_ext_cost,
                (select qty from target_cost tc where tc.item=open_buy.item  limit 1) target_qty,
                (select unit_cost  from target_cost tc where tc.item=open_buy.item  limit 1) target_unit_cost,
                0 as open_po_pending_amt,
                0 as num_transfers,
                0 commit_amt,
                0 c_unit_price,
                '' as document,
                '' as vendor_name,
                '' as vendor_id,
                (select unit_price from ".$rpt_period."_wp_committed_po c where c.item=open_buy.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
                (select ci.date from meac.".$rpt_period."_change_item ci where ci.ship_code=open_buy.ship_code and ci.item=open_buy.item  order by ci.date DESC limit 1) change_date,
                (select ci.description from meac.".$rpt_period."_change_item ci where ci.ship_code=open_buy.ship_code and ci.item=open_buy.item  order by ci.date DESC limit 1) change_reason,
                (select c.ship_code from ".$rpt_period."_wp_committed_po c where c.item=open_buy.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
                0 as  commit_qty,
                0 as gl_qty,
                0 as int_amt,
                (select buyer from ".$rpt_period."_master_buyer mb where open_buy.buyer = mb.id) buyer
            from ".$rpt_period."_wp_open_buy open_buy
            left join ".$rpt_period."_wp_ebom e
              on e.ship_code = open_buy.ship_code and e.item =open_buy.item
            left join ".$rpt_period."_wp_gl_detail gl
              on open_buy.ship_code = gl.ship_code and open_buy.item= gl.item
            left join ".$rpt_period."_wp_open_po open_po
              on open_po.ship_code = open_buy.ship_code and open_po.item= open_buy.item
            where
              gl.ship_code is null and
                open_po.ship_code is null
              and open_buy.ship_code = $ship_code
              order by $ob
";

    $i=0;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                = "LCS";
        $ship_code              = $rs->fields["ship_code"];
        $category               = $rs->fields["category"];
        $wp                     = $rs->fields["wp"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $noun1                  = $rs->fields["noun1"];
        $tc                     = $rs->fields["tc"];
        $item_group_description = $rs->fields["item_group_description"];
        $description            = processDescription($rs->fields["description"]);
        $unit                   = $rs->fields["unit"];
        $change_date            = fixExcelDateMySQL($rs->fields["change_date"]);
        $change_reason          = processDescription($rs->fields["change_reason"]);
        $ebom                   = formatNumber4decNoComma($rs->fields["ebom"]);
        $ebom_issued            = formatNumber4decNoComma($rs->fields["ebom_issued"]);
        $ebom_on_hand           = formatNumber4decNoComma($rs->fields["ebom_onhand"]);
        $transfers              = formatNumber4decNoComma($rs->fields["transfers"]);
        $last_unit_price_ship   = $rs->fields["last_unit_price_ship"];
        $open_buy_item_shortage = formatNumber4decNoComma($rs->fields["open_buy_item_shortage"]);
        $open_po_pending_amt    = formatNumber4decNoComma($rs->fields["open_po_pending_amt"]);
        $c_amt                  = formatNumber4decNoComma($rs->fields["commit_amt"]);
        $last_unit_price        = formatNumber4decNoComma($rs->fields["last_unit_price"]);
        $gl_int_amt             = formatNumber4decNoComma($rs->fields["int_amt"]);
        $target_qty             = formatNumber4decNoComma($rs->fields["target_qty"]);
        $target_unit_price      = formatNumber4decNoComma($rs->fields["target_unit_cost"]);
        $target_ext_cost        = formatNumber4decNoComma($rs->fields["target_ext_cost"]);
        $c_unit_price           = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name            = processDescription($rs->fields["vendor_name"]);
        $vendor_id              = intval($rs->fields["vendor_id"]);
        $buyer                  = trim($rs->fields["buyer"]);
        $document               = $rs->fields["document"];
        $ecp_rea                = "";
        $clin                   = "";
        $effort                 = "";;
        $po_data                = "";;
        $c_qty                  = formatNumber4decNoComma($rs->fields["commit_qty"]);
        $gl_qty                 = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $var_target_qty         = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost        = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom               = calcVarEBOM($ebom, $c_qty, $category, $description);
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage, $var_ebom);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc +$open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);

        $sql.=createInsertValuesString($program, $ship_code, $category,$swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name,
            $vendor_id, $buyer,$c_qty, $var_target_qty,
            $var_target_cost, $gl_qty, $var_ebom,
            $c_unit_price, $document,$ecp_rea, $clin, $effort,
            $po_data, $tc,$item_group_description, $change_date,$change_reason);
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
function returnAllocInsertSQLRptPeriod($rpt_period){
    $sql = "
          insert into ".$rpt_period."_swbs_gl_summary_stage (
            ship_code,
            wp,
            category,
            swbs_group,
            swbs,
            item,
            description,
            unit,
            vendor_name,
            gl_qty,
gl_int_amt) VALUES";
    return $sql;
}
function insertGlChargesNoPartNumAllocRptPeriod($ship_code, $rpt_period)
{
    $insert_sql = returnAllocInsertSQLRptPeriod($rpt_period);
    $sql = "
           select
            gl.ship_code,
            gl.wp,
            case when description like '%alloc%' and description like '%shoc%' then 'Shock Allocations'
              else 'Allocations' end as category,
            case when CHAR_LENGTH(gl.swbs) = 3 then concat(left(gl.swbs,1),'00') ELSE
              '000' end as swbs_group,
            gl.swbs,
            gl.item,
            gl.description,
            gl.unit,
            gl.cust_supp as vendor_name,
            gl.qty as  gl_qty,
            gl.integr_amt int_amt
        from ".$rpt_period."_wp_gl_detail gl
        where gl.ship_code = $ship_code
        and description like '%alloc%' and item = ''
        ";
    $i=0;
    //print $sql;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program     = "LCS";
        $ship_code   = $rs->fields["ship_code"];
        $wp          = $rs->fields["wp"];
        $category    = $rs->fields["category"];
        $swbs_group  = $rs->fields["swbs_group"];
        $swbs        = $rs->fields["swbs"];
        $item        = $rs->fields["item"];
        $description = processDescription($rs->fields["description"]);
        $unit        = $rs->fields["unit"];
        $vendor_name = $rs->fields["vendor_name"];
        $gl_qty      = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $gl_int_amt  = formatNumber4decNoComma($rs->fields["int_amt"]);
        $sql.=createInsertValuesStringAllocations($program, $ship_code, $category,$swbs_group, $swbs,
            $wp, $item, $description,$unit, $gl_int_amt,$vendor_name, $gl_qty);
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
function returnOtherNOPartNumInsertSQLRptPeriod($rpt_period){
    $sql = "
    insert into ".$rpt_period."_swbs_gl_summary_stage (ship_code,
        wp,
        category,
        swbs_group,
        swbs,
        item,
        description,
        unit,
        vendor_name,
        gl_qty,
        gl_int_amt) VALUES";
    return $sql;
}
function insertGlChargesNoPartNumRptPeriod($ship_code, $rpt_period)
{
    $insert_sql = returnOtherNOPartNumInsertSQLRptPeriod($rpt_period);
    $sql = "
         select
            gl.ship_code,
            gl.wp,
            left(document, 3) as category,
           case when CHAR_LENGTH(gl.swbs) = 3 then concat(left(gl.swbs,1),'00') ELSE
              '000' end as swbs_group,
            gl.swbs,
            gl.item,
            gl.description, 
            gl.unit,
            gl.cust_supp as vendor_name,
            gl.qty as  gl_qty,
            gl.integr_amt int_amt
        from ".$rpt_period."_wp_gl_detail gl
        where gl.ship_code = $ship_code
        and description not like '%alloc%' and item = ''
        ";
    $i=0;
    //print $sql;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program     = "LCS";
        $ship_code   = $rs->fields["ship_code"];
        $wp          = $rs->fields["wp"];
        $category    = $rs->fields["category"];
        $swbs_group  = $rs->fields["swbs_group"];
        $swbs        = $rs->fields["swbs"];
        $item        = $rs->fields["item"];
        $description = processDescription($rs->fields["description"]);
        $unit        = $rs->fields["unit"];
        $vendor_name = $rs->fields["vendor_name"];
        $gl_qty      = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $gl_int_amt  = formatNumber4decNoComma($rs->fields["int_amt"]);
        $sql.=createInsertValuesStringNOPartNum($program, $ship_code, $category,$swbs_group, $swbs,
            $wp, $item, $description,$unit, $gl_int_amt,$vendor_name, $gl_qty);
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
function insertJournalEntriesRptPeriod($ship_code, $rpt_period){
    if($ship_code == 471){
        $program = "0471-";
    }
    if(strlen($ship_code)==3)
    {
        $program = "0".$ship_code;

    }
    $year = intval(substr($rpt_period, 0, 4));
    $month = month2digit(substr($rpt_period, -2));
    $day  = getMonthEndDay($rpt_period);

    if($day<5){
        $month = $month+1;
    }


    $insert_sql = "
        insert into meac.".$rpt_period."_swbs_gl_summary_stage (
        program,
        ship_code,
        category,
        swbs_group,
        swbs,
        wp,
        gl_int_amt) VALUES 
        ";

    $sql = "
        select CAWPID, ea from
          (select
             cawpid,
             sum(DIRECT) ea
           from TPHASE where
             PROGRAM = '$program'
                 and CLASS in  ('ea', 'CA')
                 and CAWPID in (select CAWPID from CAWP 
                 where CAWP.PROGRAM = '$program' 
                 and wp LIKE '%matl%'
                         and  (DF_DATE <= '$year-$month-$day')
    )
               group BY PROGRAM, CAWPID)
              s where s.ea <> 0
    ";
    $rs = dbCallCobra($sql);
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $cawpid     = addslashes(trim($rs->fields["CAWPID"]));
        $gl_int_amt = formatNumber4decNoComma(trim($rs->fields["ea"]));
        $wp         = getWPFromCAWPID($program, $cawpid);
        $swbs       = substr($wp, 5, 3);
        $swbs_group = substr($wp, 5, 1);
        $swbs_group .= "00";
        if (stripos($wp, 'matl') !== false) {
            $sql.= "('LCS', $ship_code, 'Journal', '$swbs_group', '$swbs', '$wp', $gl_int_amt),";
        }
        $rs->MoveNext();
    }
    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql, "meac");
}
function insertSWBSSummaryRptPeriod($value, $rpt_period){
    $insert_sql= returnInsertSQLSWBSSumRptPeriod($rpt_period, "swbs_gl_summary");
    $sql = "
              SELECT
            program,
            ship_code,
            category,
            max(swbs_group) swbs_group,
            max(swbs)       swbs,
            wp,
            spn,
            item,
            item_group,
            description,
            unit,
            noun1,
            transfers,
            c_amt,
            c_unit_price,
            last_unit_price,
            gl_int_amt,
            ebom,
            ebom_on_hand,
            ebom_issued,
            last_unit_price_ship,
            open_po_pending_amt,
            open_buy_item_shortage,
            sum(etc)        etc,
            sum(eac)        eac,
            uncommitted,
            target_qty,
            target_unit_price,
            target_ext_cost,
            vendor_name,
            vendor_id,
            var_target_cost,
            c_qty,
            var_target_qty,
             buyer,
            gl_qty,
            var_ebom,
            document,
            clin,
            effort,
            ecp_rea,
            po_data,
            tc,
            item_group_description,
            change_date,
            change_reason
            FROM ".$rpt_period."_swbs_gl_summary_stage stage
            WHERE ship_code = $value
            GROUP BY ship_code, wp, item 
            
    ";
    $rs = dbCall($sql, "meac");
    $sql = $insert_sql;
    $i=0;
    while (!$rs->EOF) {
        $program                = $rs->fields["program"];
        $ship_code              = $rs->fields["ship_code"];
        $category               = $rs->fields["category"];
        $swbs_group             = intval($rs->fields["swbs_group"]);
        $swbs                   = intval($rs->fields["swbs"]);
        $wp                     = $rs->fields["wp"];
        $spn                    = $rs->fields["spn"];
        $item                   = processDescription($rs->fields["item"]);
        $item_group             = $rs->fields["item_group"];
        $description            = $rs->fields["description"];
        $unit                   = $rs->fields["unit"];
        $noun1                  = $rs->fields["noun1"];
        $transfers              = formatNumber4decNoComma($rs->fields["transfers"]);
        $c_amt                  = formatNumber4decNoComma($rs->fields["c_amt"]);
        $c_unit_price           = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $last_unit_price        = formatNumber4decNoComma($rs->fields["last_unit_price"]);
        $gl_int_amt             = formatNumber4decNoComma($rs->fields["gl_int_amt"]);
        $ebom                   = formatNumber4decNoComma($rs->fields["ebom"]);
        $ebom_on_hand           = formatNumber4decNoComma($rs->fields["ebom_on_hand"]);
        $ebom_issued            = formatNumber4decNoComma($rs->fields["ebom_issued"]);
        $last_unit_price_ship   = $rs->fields["last_unit_price_ship"];
        $open_po_pending_amt    = formatNumber4decNoComma($rs->fields["open_po_pending_amt"]);
        $open_buy_item_shortage = formatNumber4decNoComma($rs->fields["open_buy_item_shortage"]);
        $etc                    = formatNumber4decNoComma($rs->fields["etc"]);
        $eac                    = formatNumber4decNoComma($rs->fields["eac"]);
        $uncommitted            = formatNumber4decNoComma($rs->fields["uncommitted"]);
        $target_qty             = formatNumber4decNoComma($rs->fields["target_qty"]);
        $target_unit_price      = formatNumber4decNoComma($rs->fields["target_unit_price"]);
        $target_ext_cost        = formatNumber4decNoComma($rs->fields["target_ext_cost"]);
        $vendor_name            = $rs->fields["vendor_name"];
        $vendor_id              = intval($rs->fields["vendor_id"]);
        $var_target_cost        = formatNumber4decNoComma($rs->fields["var_target_cost"]);
        $c_qty                  = formatNumber4decNoComma($rs->fields["c_qty"]);
        $var_target_qty         = formatNumber4decNoComma($rs->fields["var_target_qty"]);
        $buyer                  = str_replace("
        ","",trim($rs->fields["buyer"]));
        $gl_qty                 = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $var_ebom               = formatNumber4decNoComma($rs->fields["var_ebom"]);
        $document               = $rs->fields["document"];
        $clin                   = $rs->fields["clin"];
        $effort                 = $rs->fields["effort"];
        $ecp_rea                = $rs->fields["ecp_rea"];
        $po_data                = $rs->fields["po_data"];
        $tc                     = $rs->fields["tc"];
        $item_group_description = processDescription($rs->fields["item_group_description"]);
        $change_date            = fixExcelDateMySQL($rs->fields["change_date"]);
        $change_reason          = processDescription($rs->fields["change_reason"]);
        $sql.=createInsertValuesString($program, $ship_code,$category,$swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name,
            $vendor_id, $buyer,$c_qty, $var_target_qty,
            $var_target_cost, $gl_qty, $var_ebom,
            $c_unit_price, $document, $ecp_rea,$clin,
            $effort, $po_data, $tc,$item_group_description, $change_date, $change_reason);

        if($i == 250)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            //print $sql;

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();

    }
    if($i != 250)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }

}
function correctShockOpenBuyItemShortageRptPeriod($ship_code, $rpt_period){
    $sql = "
        update mars.".$rpt_period."_open_buy
            set item_shortage = 0
        where
          ship_code = $ship_code and
          item in (
                select
                  item
                from
                    (
                      select
                        proj,
                        item,
                        sum(pending_qty) sum
                      from mars.".$rpt_period."_open_po
                      where item in
                        (select item from mars.".$rpt_period."_open_buy where spn = '-S00')
                        and proj = $ship_code
                    group by proj, item) s
                where s.sum =0 and s.proj = $ship_code)
    ";
    $junk = dbCall($sql, "mars");
}

function colorWISheet($sheet){
    $i = 1;
    $sheet->SetCellValue("B".$i++, "New Open PO Value");
    $sheet->SetCellValue("B".$i++, "New Open PO Value NOT APPROVED IN FORTIS");
    $sheet->SetCellValue("B".$i++, "New Acutals This month");
    $sheet->SetCellValue("B".$i++, "Fortis Status Is run Live");
    $sheet->SetCellValue("B".$i++, "Open PO Value is from the first day after month end");
    $sheet->SetCellValue("B".$i++, "Notes Field consists of PO Approval Log Notes, and if it did not exist then it looks for a GL INV transfer.");
    $red= array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => "e20909")
        )
    );
    $blue= array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => "0a34db")
        )
    );
    $sheet->getStyle("A1")->applyFromArray($red);
    $sheet->getStyle("A2")->applyFromArray($blue);
    $sheet->getStyle("A3")->applyFromArray($red);

}
function getPONumLogNotesFortisStatus($item, $ship_code,$rpt_period){
    $sql = "
        select
            s.po po,
            s.po_log_notes notes,
            coalesce((select status from ".$rpt_period."_po_data po where s.po = po.po order by modified_date desc LIMIT 1), '') as fortis_status,
            coalesce((select buyer from ".$rpt_period."_po_data po where s.po = po.po order by modified_date desc LIMIT 1), '') as buyer
        from (
        SELECT
          GROUP_CONCAT(DISTINCT po) as po,
          GROUP_CONCAT(DISTINCT CONCAT(`reason_for_change`, '-', funding_source, '-', other_notes)) as po_log_notes
        
        FROM po_approval_log
        WHERE item = '$item' AND ship_code = $ship_code) s
    ";
    $rs = dbCall($sql, "meac");
    $po             = $rs->fields["po"];
    $po_log_notes   = $rs->fields["notes"];
    $fortis_status  = $rs->fields["fortis_status"];
    $buyer          = $rs->fields["buyer"];
    $data = array();
    $data["po"]             = $po;
    $data["fortis_status"]  = $fortis_status;
    $data["notes"]          = $po_log_notes;
    $data["buyer"]          = $buyer;
    return $data;
}
function getECPREA($rpt_period, $item, $ship_code){
    $sql     = "select ecp_rea from meac." . $rpt_period."_wp_open_po where item = '$item' and ship_code = $ship_code limit 1";
    $rs      = dbCall($sql, "meac");
    $ecp_rea = $rs->fields["ecp_rea"];
    if($ecp_rea==""){
        $sql     = "select ecp_rea from meac." . $rpt_period . "_wp_gl_detail where item = '$item' and ship_code = $ship_code order by date DESC ";
        $rs      = dbCall($sql, "meac");
        $ecp_rea = $rs->fields["ecp_rea"];
    }
    return $ecp_rea;
}
function getFortisNotes($ship_code, $item, $rpt_period){
    $po = getPONumFromItem($item,$ship_code, $rpt_period);
    $fortis_notes = "";
    $sql = "select purchasing_manager_notes notes  from `".$rpt_period."_po_data` where ship_code = $ship_code and po = $po and purchasing_manager_notes <> ''";
    $rs = dbCall($sql, "meac");
    if($rs){
        while (!$rs->EOF) {
            $notes= trim($rs->fields["notes"]);
            $fortis_notes.=$notes."--NEXT--";
            $rs->MoveNext();
        }
        $fortis_notes = substr($fortis_notes, 0, -8);
        return $fortis_notes;
    }
    else{
        $fortis_notes = "NO FORTIS NOTES";
        return $fortis_notes;
    }
}
function getGlDoc($rpt_period, $item, $ship_code, $wp){
    $sql = "select document from ".$rpt_period."_wp_gl_detail 
    where item = '$item' and ship_code = $ship_code 
    and wp = '$wp'
    order by `date` desc limit 1";
    //print $sql;
    $rs = dbCall($sql, "meac");
    $doc= $rs->fields["document"];
    return $doc;
}
function getPONumFromItem($item,$ship_code, $rpt_period){
    $sql = "select po as po from `".$rpt_period."_wp_open_po` WHERE  item = '$item' and ship_code = $ship_code order by po desc limit 1";
    $rs = dbCall($sql,"meac");
    $po = $rs->fields["po"];
    if($po==""){
        $sql = "select `order` po from `".$rpt_period."_wp_gl_detail` WHERE  item = '$item' and ship_code = $ship_code";

        $rs = dbCall($sql,"meac");
        $po = $rs->fields["po"];
    }
    return $po;
}
function checkPOStatus($ship_code, $item,$rpt_period){
    $po     = getPONumFromItem($item, $ship_code,$rpt_period);
    $sql    = "select status stat, buyer from ".$rpt_period."_po_data WHERE po = $po order by modified_date desc limit 1";
    $rs     = dbCall($sql, "meac");
    $status = $rs->fields["stat"];
    $buyer = $rs->fields["buyer"];
    if($status==""){
        $status = "NOT IN FORTIS";
    }
    $data           = array();
    $data["status"] = $status;
    $data["po"]     = $po;
    $data["buyer"]     = $buyer;
    return $data;
}
function getALlWPS($ship_code, $rpt_period)
{
    $wp_array =array();
    $sql = "select wp from 201707_swbs_gl_summary WHERE ship_code = $ship_code and wp like '%matl%' 
  and wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
group by wp
union
select wp from ".$rpt_period."_swbs_gl_summary WHERE ship_code = $ship_code and wp like '%matl%'
  and wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
group by wp
union
select wp from reest3 WHERE ship_code = $ship_code and wp like '%matl%'
  and wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
group by wp
";
    $rs  = dbCall($sql, "meac");
    while (!$rs->EOF) {

        $wp= trim($rs->fields["wp"]);
        $wp_array[] = $wp;
        $rs->MoveNext();
    }
    return $wp_array;
}
function calcNewETC($ebom, $prev_etc, $diff_a,$diff_open_po, $category){
    $change = $diff_a+$diff_open_po;
    if($category == "Vendor Service"){
        if($change <= 0){
            $new_etc = $prev_etc+abs($change);
        }
        else{
            $new_etc = floatval($prev_etc)-floatval($change);
            if($new_etc <1){
                $new_etc = 0;
            }
        }
        return $new_etc;
    }
    if($category != "Vendor Service"){
        if($change <= 0){
            $new_etc = $prev_etc+abs($change);
            return $new_etc;
        }
        else if($ebom <1){
            $new_etc = 0;
            return $new_etc;
        }
        else{
            $new_etc = floatval($prev_etc)-floatval($change);
            return $new_etc;
        }
    }
    return $new_etc;
}
function getFrozenEACWP($ship_code){
    $wp_array = array();
    $sql = "select wp from frozen_eac_wp where ship_code = '$ship_code'
    union 
    select wp from frozen_eac_wp where ship_code = 'All'
    ";
    $rs= dbCall($sql,"meac");
    while (!$rs->EOF)
    {
        $wp= $rs->fields["wp"];

        $wp_array[] = $wp;
        $rs->MoveNext();
    }
    return $wp_array;
}
function calcNewEAC2($wp_freeze_array, $new_etc, $cur_gl, $cur_open_po, $wp, $prev_eac, $diff_a, $diff_open_po){
    $change = $diff_a+$diff_open_po;
    $freeze = in_array($wp, $wp_freeze_array);
    if($freeze==true){
        /*        if($prev_eac< ($cur_gl+$cur_open_po)){
                    $new_eac = $cur_gl+$cur_open_po;
                }
                else{
                    $new_eac = $prev_eac;
                    //die("mad eit freeze else");
                }*/
        $new_eac = $prev_eac;
        return $new_eac;
    }
    else{
        if($new_etc<=0){
            $new_eac = $cur_gl+$cur_open_po;
            return $new_eac;
        }
        else if(intval($change)==0){
            $new_eac = $prev_eac;
            return $new_eac;
        }
        else{
            $new_eac = $cur_gl+$cur_open_po+ abs($new_etc);
            return $new_eac;
        }
        return $new_eac;
    }
}
function returnHeadersMEAC($cur_month_letters, $prev_month_letters){
    $header_array[] = "Hull";
    $header_array[] = "SWBS GROUP";
    $header_array[] = "SWBS";
    $header_array[] = "WP";
    $header_array[] = "Item";
    $header_array[] = "PREV ETC";
    $header_array[] = "PREV EAC";
    $header_array[] = "$prev_month_letters ACTUALS";
    $header_array[] = "$cur_month_letters ACTUALS";
    $header_array[] = "$prev_month_letters OPEN PO";
    $header_array[] = "$cur_month_letters OPEN PO";
    $header_array[] = "NEW ACTUALS THIS MONTH";
    $header_array[] = "NEW OPEN PO THIS MONTH";
    $header_array[] = "ETC DIFF";
    $header_array[] = "EAC DIFF";
    $header_array[] = "NEW EAC";
    $header_array[] = "NEW ETC";
    $header_array[] = "EBOM";
    $header_array[] = "PO";
    $header_array[] = "Buyer";
    $header_array[] = "Log Comments";
    $header_array[] = "Fortis Notes";
    $header_array[] = "Gl Doc";
    $header_array[] = "ECP REA";
    $header_array[] = "Fortis Status";
    $header_array[] = "Proposed EAC";
    $header_array[] = "CHANGE";
    $header_array[] = "Comment";
    $header_array[] = "Bucket";
    return $header_array;
}
function returnHeadersMEACWP($cur_month_letters, $prev_month_letters){
    $header_array[] = "Hull";
    $header_array[] = "SWBS GROUP";
    $header_array[] = "SWBS";
    $header_array[] = "WP";
    $header_array[] = "PREV ETC";
    $header_array[] = "PREV EAC";
    $header_array[] = "$prev_month_letters ACTUALS";
    $header_array[] = "$cur_month_letters ACTUALS";
    $header_array[] = "$prev_month_letters OPEN PO";
    $header_array[] = "$cur_month_letters OPEN PO";
    $header_array[] = "NEW ACTUALS THIS MONTH";
    $header_array[] = "NEW OPEN PO THIS MONTH";
    $header_array[] = "ETC DIFF";
    $header_array[] = "EAC DIFF";
    $header_array[] = "NEW EAC";
    $header_array[] = "NEW ETC";
    return $header_array;
}
function rtnMEACDeltaSQL($rpt_period, $prev_rpt_period, $ship_code, $wp=""){
    if($ship_code>= 477){
        $wp1_exclude = "and prev.wp not in ('MATL-900-999')";
        $wp2_exclude  = "and cur.wp not in ('MATL-900-999')";
        $wp3_exclude  = "and reest.wp not in ('MATL-900-999')";
    }
    else{
        $wp1_exclude = "";
        $wp2_exclude = "";
        $wp3_exclude = "";
    }
    if($wp=="")
    {
        $wp1_wc = $wp1_exclude;
        $wp2_wc = $wp2_exclude;
        $wp3_wc = $wp3_exclude;
    }
    else{
        $wp1_wc = "AND prev.wp = '$wp' $wp1_exclude";
        $wp2_wc = "and cur.wp = '$wp' $wp2_exclude";
        $wp3_wc = "and reest.wp = '$wp' $wp3_exclude";
    }

    $sql = "
SELECT
      ship_code,
      concat(right(left(wp, 6), 1), '00') swbs_group,
              left(right(wp, 7),3) swbs,
      wp,
      category,
      var_ebom,
      item,
      prev_etc,
      prev_eac,
      prev_a,
      CUR_ACTUALS,
      prev_OPENPO,
      CUR_OPENPO,
      (CUR_ACTUALS- prev_a) AS     NEW_ACTUALS_THIS_MONTH,
      (CUR_OPENPO - prev_OPENPO)   AS     new_OPEN_PO_THIS_MONTH
    from (
        SELECT
    s2.ship_code,
      s2.wp,
      s2.item,
      s2.category,
      s2.var_ebom,
      (prev_eac - (prev_OPENPO+prev_a)) as prev_etc,
      s2.prev_eac,
      s2.prev_a,
      s2.CUR_ACTUALS,
      s2.prev_OPENPO,
      s2.CUR_OPENPO
    FROM (
            SELECT
                     prev.ship_code,
                     prev.wp,
                     prev.item,
                     prev.category,
                     coalesce((SELECT sum(var_ebom)
                               FROM `".$rpt_period."_swbs_gl_summary` meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS var_ebom,
                     coalesce((SELECT sum(inflation_eac)
                               FROM reest3 meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS prev_eac,
                     coalesce(sum(gl_int_amt), 0)                                                                            prev_a,
                     coalesce((SELECT sum(gl_int_amt)
                               FROM `".$rpt_period."_swbs_gl_summary` meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS CUR_ACTUALS,
                     coalesce(sum(open_po_pending_amt),
                              0)                                                                                             prev_OPENPO,
                     coalesce((SELECT sum(open_po_pending_amt)
                               FROM `".$rpt_period."_swbs_gl_summary` meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS CUR_OPENPO
                   FROM `".$prev_rpt_period."_swbs_gl_summary` prev 
                   WHERE prev.ship_code = $ship_code 
                   and prev.wp like '%matl%' 
                   and prev.wp <> 'matl-825-999' 
                   and prev.wp <> 'MATL-829-999' 
                   and prev.wp <> 'MATL-828-999'
                        $wp1_wc
                   GROUP BY ship_code, wp, item)  s2
    union
      /*
      ITEMS that are not in PREV but are in CUR
      */

    SELECT
      cur.ship_code,
      cur.wp,
      cur.item,
      cur.category,
      cur.var_ebom,
      coalesce((SELECT sum(inflation_etc)
       FROM `reest3` re
       WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item),0) AS prev_etc,
      coalesce((SELECT sum(inflation_eac)
       FROM `reest3` re
       WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item),0) AS prev_eac,
      0                                                                                 prev_a,
      coalesce(sum(cur.gl_int_amt),0)                                                            AS CUR_ACTUALS,
      0                                                                                 prev_OPENPO,
      coalesce(sum(cur.open_po_pending_amt),0)                                                   AS CUR_OPENPO


    from `".$rpt_period."_swbs_gl_summary` cur
    left join `".$prev_rpt_period."_swbs_gl_summary` prev
      on cur.ship_code = prev.ship_code
    and cur.wp = prev.wp
    and cur.item = prev.item
    where
      cur.ship_code = $ship_code 
      and cur.wp like '%matl%'
      and cur.wp <> 'matl-825-999' 
      and cur.wp <> 'MATL-829-999' 
      and cur.wp <> 'MATL-828-999'
     $wp2_wc
    and prev.ship_code is null AND
      prev.ship_code is null
    and prev.item is null
    GROUP BY ship_code, wp, item
   UNION
      /*ITEMS THAT ARE NOT IN THE 201709 PERIOD OR IN 201710 PERIOD*/
    SELECT
          reest.ship_code,
          reest.wp,
          reest.item,
          ''             category,
          0             var_ebom,
          coalesce((SELECT sum(reest.inflation_etc)
                    FROM reest3 meac
                    WHERE meac.ship_code =
                          reest.ship_code AND
                          meac.wp = reest.wp AND
                          meac.item = reest.item AND
                          remaining = 'yes' limit 1),
                   0 ) AS prev_etc,
          coalesce(sum(inflation_eac),0) AS prev_eac,
          0              prev_a,
          0       AS CUR_ACTUALS,
          0              prev_OPENPO,
          0           AS CUR_OPENPO
        FROM reest3 reest LEFT JOIN
          ".$prev_rpt_period."_swbs_gl_summary prev ON
prev.ship_code = reest.ship_code 
AND prev.wp =reest.wp 
AND prev.item = reest.item
          LEFT JOIN `".$rpt_period."_swbs_gl_summary` cur ON
cur.ship_code=reest.ship_code AND
cur.wp = reest.wp
AND cur.item =reest.item
    WHERE reest.ship_code = $ship_code  
        and reest.wp like '%matl%'
        and reest.wp <> 'matl-825-999' 
        and reest.wp <> 'MATL-829-999' 
        and reest.wp <> 'MATL-828-999'
        and prev.ship_code IS NULL 
        AND cur.ship_code IS NULL 
         $wp3_wc
         group by reest.ship_code, reest.wp, reest.item
    ) s where s.ship_code is not NULL 
    ";
    return $sql;
}
function buildMEACFile($ship_code, $prev_rpt_period,$rpt_period, $g_path_to_util){
    $wp_freeze_array = getFrozenEACWP($ship_code);

    $data  = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);
    $red   = "e20909";
    $white = "ffffff";

    $cur_year           = $data["cur_year"];
    $prev_month_letters = $data["prev_month_letters"];
    $cur_month_letters  = $data["cur_month_letters"];
    $header_array = returnHeadersMEACWP($cur_month_letters, $prev_month_letters);

    $objPHPExcel = new PHPExcel();
// Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("WP Summary");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->SetCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sheet->freezePane('G2');

    $wp_array= getALlWPS($ship_code, $rpt_period);

    $wp_array2[] ="MATL-330-999";
    $data_start = 2;

    foreach ($wp_array as $wp){
        $wp_data = array();
        $wp_data = null;
        $sql = rtnMEACDeltaSQL($rpt_period,$prev_rpt_period, $ship_code, $wp);

        $rs = dbCall($sql,"meac");

        while (!$rs->EOF)
        {

            $swbs_group                     = trim($rs->fields["swbs_group"]);
            $category                       = trim($rs->fields["category"]);
            $swbs                           = trim($rs->fields["swbs"]);
            $item                           = trim($rs->fields["item"]);
            $ebom                           = $rs->fields["var_ebom"];
            $wp_data[$item]["prev_etc"]     = formatNumber4decNoComma($rs->fields["prev_etc"]);
            $wp_data[$item]["prev_a"]       = formatNumber4decNoComma($rs->fields["prev_a"]);
            $wp_data[$item]["cur_a"]        = formatNumber4decNoComma($rs->fields["CUR_ACTUALS"]);
            $wp_data[$item]["prev_open_po"] = formatNumber4decNoComma($rs->fields["prev_OPENPO"]);
            $wp_data[$item]["cur_open_po"]  = formatNumber4decNoComma($rs->fields["CUR_OPENPO"]);
            $wp_data[$item]["diff_a"]       = formatNumber4decNoComma($rs->fields["NEW_ACTUALS_THIS_MONTH"]);
            $wp_data[$item]["diff_open_po"] = formatNumber4decNoComma($rs->fields["new_OPEN_PO_THIS_MONTH"]);
            $wp_data[$item]["prev_eac"]     = formatNumber4decNoComma($rs->fields["prev_eac"]);
            /*
             * if eac was reduced last period, and there was no activity.  the etc = eac*/
            if($wp_data[$item]["prev_etc"]>$wp_data[$item]["prev_eac"]){
                $wp_data[$item]["prev_etc"] =  $wp_data[$item]["prev_eac"];
            }

            $wp_data[$item]["new_etc"]      = calcNewETC($ebom, $wp_data[$item]["prev_etc"], $wp_data[$item]["diff_a"], $wp_data[$item]["diff_open_po"], $category);
            $wp_data[$item]["new_eac"]      = calcNewEAC2($wp_freeze_array, $wp_data[$item]["new_etc"], $wp_data[$item]["cur_a"], $wp_data[$item]["cur_open_po"], $wp, $wp_data[$item]["prev_eac"],$wp_data[$item]["diff_a"], $wp_data[$item]["diff_open_po"]);
            if($wp_data[$item]["new_etc"]<0){
                //$etc_diff = $new_etc;
                $wp_data[$item]["etc_diff"]= $wp_data[$item]["new_etc"] ;
            }
            else{
                //$etc_diff     = ($new_etc - $prev_etc);
                $wp_data[$item]["etc_diff"]     = ($wp_data[$item]["new_etc"] - $wp_data[$item]["prev_etc"]);

            }

            $wp_data[$item]["eac_diff"]= ($wp_data[$item]["new_eac"] - $wp_data[$item]["prev_eac"]);
            if($wp_data[$item]["diff_open_po"]!=0){
                $status_array  = checkPOStatus($ship_code, $item, $rpt_period);
                $fortis_status = $status_array["status"];
                $po            = $status_array["po"];
                if($fortis_status=="Denied"){
                    $wp_data[$item]["diff_open_po"] = 0;
                }
            }
            $header_col = "A";
            $rs->MoveNext();
        }

        $res = array();
        foreach($wp_data as $value) {
            foreach($value as $key => $number) {
                (!isset($res[$key])) ?
                    $res[$key] = $number :
                    $res[$key] += $number;
            }
        }
        $prev_etc     = $res["prev_etc"];
        $prev_a       = $res["prev_a"];
        $cur_a        = $res["cur_a"];
        $prev_open_po = $res["prev_open_po"];
        $cur_open_po  = $res["cur_open_po"];
        $diff_a       = $res["diff_a"];
        $diff_open_po = $res["diff_open_po"];
        $prev_eac     = $res["prev_eac"];
        $new_etc      = $res["new_etc"];
        $new_eac      = $res["new_eac"];
        $eac_diff     = $res["eac_diff"];
        $etc_diff     = $res["etc_diff"];

        $freeze = in_array($wp, $wp_freeze_array);
        if($freeze==true){

            if(($cur_a+$cur_open_po)>$prev_eac){
                $new_eac  = $cur_a + $cur_open_po;
                $eac_diff = ($prev_eac - $new_eac);
            }
        }

        //die("made it");

        $sheet->SetCellValue($header_col++.$data_start, $ship_code);
        $sheet->SetCellValue($header_col++.$data_start, $swbs_group);
        $sheet->SetCellValue($header_col++.$data_start, $swbs);
        $sheet->SetCellValue($header_col++.$data_start, $wp);
        $sheet->SetCellValue($header_col.$data_start, $prev_etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $diff_a);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_a, "Approved");

        $sheet->SetCellValue($header_col.$data_start, $diff_open_po);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_open_po, "Approved");


        $sheet->SetCellValue($header_col.$data_start, $etc_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$etc_diff);

        $sheet->SetCellValue($header_col.$data_start, $eac_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$eac_diff);

        $sheet->SetCellValue($header_col.$data_start, $new_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);


        $sheet->SetCellValue($header_col.$data_start, $new_etc);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet, $etc_diff);

        $data_start++;
    }


    /**
     *
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     */

    $objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating
    $objPHPExcel->setActiveSheetIndex(1);
    $objWorkSheet->setTitle("Detail");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $sheet      = $objPHPExcel->getActiveSheet();

    $header_array = returnHeadersMEAC($cur_month_letters, $prev_month_letters);
    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->SetCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }

//$ship_code = "0481";
    $sheet->freezePane('F2');
    $sql = rtnMEACDeltaSQL($rpt_period,$prev_rpt_period,$ship_code);

    $rs = dbCall($sql,"meac");
    $data_start = 2;
    while (!$rs->EOF)
    {

        $swbs_group = trim($rs->fields["swbs_group"]);
        $swbs       = trim($rs->fields["swbs"]);
        $wp         = trim($rs->fields["wp"]);
        $item       = trim($rs->fields["item"]);
        $prev_etc   = trim($rs->fields["prev_etc"]);
        $prev_a       = formatNumber4decNoComma($rs->fields["prev_a"]);
        $cur_a        = formatNumber4decNoComma($rs->fields["CUR_ACTUALS"]);
        $prev_open_po = formatNumber4decNoComma($rs->fields["prev_OPENPO"]);
        $cur_open_po  = formatNumber4decNoComma($rs->fields["CUR_OPENPO"]);
        $diff_a       = formatNumber4decNoComma($rs->fields["NEW_ACTUALS_THIS_MONTH"]);
        $diff_open_po = formatNumber4decNoComma($rs->fields["new_OPEN_PO_THIS_MONTH"]);
        $ebom         = formatNumber4decNoComma($rs->fields["var_ebom"]);
        $prev_eac     = formatNumber4decNoComma($rs->fields["prev_eac"]);
        if($prev_etc>$prev_eac){
            $prev_etc =  $prev_eac;
        }
        $new_etc      = calcNewETC($ebom, $prev_etc, $diff_a,$diff_open_po, $category);
        $new_eac      = calcNewEAC2($wp_freeze_array, $new_etc, $cur_a, $cur_open_po, $wp, $prev_eac,$diff_a,$diff_open_po);

        if($new_etc<0){
            $etc_diff = $new_etc;
        }
        else{
            $etc_diff     = $new_etc - $prev_etc;

        }
        $eac_diff = formatNumber4decNoComma($new_eac - $prev_eac);
        $fortis_notes  = "";
        $notes         = "";
        $po            = "";
        $gl_doc        = "";
        $ecp_rea       = "";
        $fortis_status = "";
        $buyer         = "";
        if(intval($eac_diff)!=0){
            $po_data = array();
            $po_data = getPONumLogNotesFortisStatus($item, $ship_code,$rpt_period);

            $gl_doc        = getGlDoc($rpt_period, $item, $ship_code, $wp);
            $ecp_rea       = getECPREA($rpt_period, $item, $ship_code);
            $notes         = $po_data["notes"];
            $fortis_status = $po_data["fortis_status"];
            $po            = $po_data["po"];
            $buyer         = $po_data["buyer"];
        }

        if($diff_open_po!=0){
            $status_array  = checkPOStatus($ship_code, $item, $rpt_period);
            $fortis_status = $status_array["status"];
            /*do not overrite a PO in the LOG.
            */
            $fortis_notes  = getFortisNotes($ship_code, $item, $rpt_period);
            if($po=="" ){
                $po    = $status_array["po"];
                $buyer = $status_array["buyer"];
            }
            if($fortis_status=="Denied"){
                $diff_open_po.=" NOT INCLUDED";
            }
        }
        $header_col = "A";
        $sheet->SetCellValue($header_col++.$data_start, $ship_code);
        $sheet->SetCellValue($header_col++.$data_start, $swbs_group);
        $sheet->SetCellValue($header_col++.$data_start, $swbs);
        $sheet->SetCellValue($header_col++.$data_start, $wp);
        $sheet->SetCellValue($header_col++.$data_start, $item);
        $sheet->SetCellValue($header_col.$data_start, $prev_etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $diff_a);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_a, "Approved");

        $sheet->SetCellValue($header_col.$data_start, $diff_open_po);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_open_po, $fortis_status);


        $sheet->SetCellValue($header_col.$data_start, $etc_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$etc_diff);

        $sheet->SetCellValue($header_col.$data_start, $eac_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$eac_diff);

        $sheet->SetCellValue($header_col.$data_start, $new_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);


        $sheet->SetCellValue($header_col.$data_start, $new_etc);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet, $etc_diff);

        $sheet->SetCellValue($header_col++.$data_start, $ebom);
        $sheet->SetCellValue($header_col++.$data_start, $po);
        $sheet->SetCellValue($header_col++.$data_start, $buyer);
        $sheet->SetCellValue($header_col++.$data_start, $notes);
        $sheet->SetCellValue($header_col++.$data_start, $fortis_notes);
        $sheet->SetCellValue($header_col++.$data_start, $gl_doc);
        $sheet->SetCellValue($header_col++.$data_start, $ecp_rea);
        $sheet->SetCellValue($header_col++.$data_start, $fortis_status);


        $data_start++;

        $rs->MoveNext();
    }
    /*Work instructions
     *
     * */
    $objWorkSheet = $objPHPExcel->createSheet(2); //Setting index when creating
    $objPHPExcel->setActiveSheetIndex(2);
    $objWorkSheet->setTitle("Work Instructions");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $sheet      = $objPHPExcel->getActiveSheet();
    colorWISheet($sheet);

    $i= 1;
    foreach ($wp_freeze_array as $wp){
        $sheet->SetCellValue("C".$i, "FROZEN EAC");
        $sheet->SetCellValue("D".$i++, $wp);
    }
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/".$ship_code."- Tool ".$cur_month_letters." ".$cur_year.$token." MEAC Prelim.xlsx");
    $path = "../util/excel_exports/".$ship_code."- Tool ".$cur_month_letters." ".$cur_year.$token." MEAC Prelim.xlsx";
    return $path;

    //$objWriter->save("C:/evms/meac_delta/".$ship_code."- Tool ".$cur_month_letters." ".$cur_year.$token." MEAC Prelim.xlsx");
    //die("C:/evms/meac_delta/".$ship_code."- Tool ".$cur_month_letters." ".$cur_year.$token." MEAC Prelim.xlsx");
}
