<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/13/2017
 * Time: 2:01 PM
 */
function processDescription($desc){
    $desc = str_replace(",", " and ", trim($desc));
    $desc = str_replace("'", "", $desc);
    $desc = str_replace("/", "", $desc);
    return $desc;
}
function checkSWBSLength($swbs){
    $numzeros = 3-strlen($swbs);
    if($numzeros==2){
        $code = "00".$swbs;
    }
    else if($numzeros==1){
        $code = "0".$swbs;
    }
    else{
        $code = $swbs;
    }
    return $code;
}
function insertData($table_name, $path2file){
    if($table_name=="open_po"){
        insertOpenPO($path2file);
    }
    if($table_name=="committed_po"){
        insertCommittedPO($path2file);
    }
    if($table_name=="gl_detail"){
        insertGLdetail($path2file);
    }
}
function insertOpenPO($path2file){
    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
        insert into mars.open_po (
            proj,
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
            pending_qty,
            pending_amnt,
            delv_date,
            payment_terms,
            ledger_acct,
            clin,
            effort
    ) VALUES 
       ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $proj          = intval($data[0]);
        $swbs          = intval($data[1]);
        $item          = addslashes(trim($data[2]));
        $description   = addslashes(trim($data[3]));
        $noun_1        = addslashes(trim($data[4]));
        $noun_2        = addslashes(trim($data[5]));
        $nre           = addslashes(trim($data[6]));
        $vendor        = intval($data[7]);
        $po            = intval($data[8]);
        $line          = intval($data[9]);
        $unit_price    = formatNumber4decNoComma($data[10]);
        $order_qty     = formatNumber4decNoComma($data[11]);
        $delivered_qty = formatNumber4decNoComma($data[12]);
        $pending_qty   = formatNumber4decNoComma($data[13]);
        $pending_amnt  = formatNumber4decNoComma($data[14]);
        $delv_date     = fixExcelDateMySQL($data[15]);
        $payment_terms = intval($data[16]);
        $ledger_acct   = intval($data[17]);
        $clin          = addslashes(trim($data[18]));
        $effort        = addslashes(trim($data[19]));

        $sql.=
            "(
                $proj,
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
                $pending_qty,
                $pending_amnt,
                '$delv_date',
                $payment_terms,
                $ledger_acct,
                '$clin',
                '$effort'
                ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            print $sql;
            print "<br> break";
            print "<br>";
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function insertCommittedPO($path2file){
    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
        insert into mars.committed_po (
            proj,
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
            effort
    ) VALUES 
       ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $proj          = intval($data[0]);
        $swbs          = intval($data[1]);
        $item          = addslashes(trim($data[2]));
        $description   = addslashes(trim($data[3]));
        $noun_1        = addslashes(trim($data[4]));
        $noun_2        = addslashes(trim($data[5]));
        $nre           = addslashes(trim($data[6]));
        $vendor        = intval($data[7]);
        $po            = intval($data[8]);
        $line          = intval($data[9]);
        $unit_price    = formatNumber4decNoComma($data[10]);
        $order_qty     = formatNumber4decNoComma($data[11]);
        $delivered_qty = formatNumber4decNoComma($data[12]);
        $committed_qty   = formatNumber4decNoComma($data[13]);
        $commit_amnt  = formatNumber4decNoComma($data[14]);
        $delv_date     = fixExcelDateMySQL($data[15]);
        $acct_proj_dept = addslashes(trim($data[16]));
        $clin          = addslashes(trim($data[17]));
        $effort        = addslashes(trim($data[18]));

        $sql.=
            "(
                $proj,
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
                '$effort'
                ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            print $sql;
            print "<br> break";
            print "<br>";
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function insertGLdetail($path2file){
    print $path2file;
    $path_exploded_array = explode("\\", $path2file);
    $file_name           = $path_exploded_array[1];
    $proj = substr($file_name, 0,4);


    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
INSERT  INTO mars.gl_detail (
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
        proj) 
        values
       ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ldger_acct  = intval($data[0]);
        $document    = addslashes(trim($data[1]));
        $line        = intval($data[2]);
        $item        = addslashes(trim($data[3]));
        $description = addslashes(trim($data[4]));
        $order       = intval($data[5]);
        $pos         = intval($data[6]);
        $cust_supp   = addslashes(trim($data[7]));
        $qty         = formatNumber4decNoComma($data[8]);
        $unit        = addslashes(trim($data[9]));
        $amt         = formatNumber4decNoComma($data[10]);
        $date        = fixExcelDateMySQL($data[11]);
        $integr_amt  = formatNumber4decNoComma($data[12]);
        $clin        = addslashes(trim($data[13]));
        $effort      = addslashes(trim($data[14]));
        $proj        = intval($proj);

        $sql.=
            "(
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
                $proj
                ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function insertEBOM($path2file){

    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "

        insert into meac.ebom (program,
            ship_code,
            material,
            spn,
            uom,
            item_group,
            swbs,
            ebom,
            ord_qty,
            on_hand,
            issued,
            unit_cost,
            supplier,
            noun1,
            noun2,
            noun3) 
        values
       ";
    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $program    = "LCS";
        $ship_code  = trim($data[0]);
        $material   = trim($data[1]);
        $spn        = trim($data[2]);
        $uom        = trim($data[4]);
        $item_group = trim($data[7]);
        $swbs       = trim($data[9]);
        $ebom       = formatNumber4decNoComma(trim($data[10]));
        $ord_qty    = formatNumber4decNoComma(trim($data[11]));
        $on_hand    = formatNumber4decNoComma(trim($data[12]));
        $issued     = formatNumber4decNoComma(trim($data[13]));
        $unit_cost  = formatNumber4decNoComma(trim($data[14]));
        $supplier   = formatNumber4decNoComma(trim($data[15]));
        $noun1      = processDescription(trim($data[16]));
        $noun2      = processDescription(trim($data[17]));
        $noun3      = processDescription(trim($data[18]));

        $sql.=
            "(
                '$program',
                $ship_code,
                '$material',
                '$spn',
                '$uom',
                '$item_group',
                '$swbs',
                $ebom,
                $ord_qty,
                $on_hand,
                $issued,
                $unit_cost,
                '$supplier',
                '$noun1',
                '$noun2',
                '$noun3'
                ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            print $sql;
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function insertCBM($path2file){
    print $path2file;
    $path_exploded_array = explode("\\", $path2file);
    $file_name           = $path_exploded_array[1];
    $program             = "LCS";
    $ship_code           = substr($file_name, 5, 4);

    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "insert into meac.cbm (program, ship_code, wp, material) values ";

    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $wp         = trim($data[0]);
        $material   = trim($data[9]);

        $sql.= "(                                                 
        '$program',
        $ship_code,
        '$wp',
        '$material'),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            print $sql;
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}
function insertOpenBuy($path2file){
    $program             = "LCS";
    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "
    insert into mars.open_buy (
        program,
        ship_code,
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
        expected_amt) VALUES ";

    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $buyer              = trim($data[0]);
        $ship_code          = trim($data[1]);
        $swbs               = trim($data[2]);
        $item               = trim($data[4]);
        $spn                = trim($data[5]);
        $description        = addslashes(str_replace("'", " ",trim($data[6])));
        $origrinal_smos_qty = formatNumber4decNoComma($data[7]);
        $remain_smos_qty    = formatNumber4decNoComma($data[8]);
        $yard_due_date      = fixExcelDateMySQL($data[9]);
        $lead_time          = $data[10];
        $plan_order_date    = fixExcelDateMySQL($data[11]);
        $uom                = trim($data[12]);
        $item_on_hand       = formatNumber4decNoComma($data[13]);
        $item_on_order      = formatNumber4decNoComma($data[14]);
        $item_shortage      = formatNumber4decNoComma($data[15]);
        $on_hold            = $data[16];
        $entered_on         = fixExcelDateMySQL($data[17]);
        $last_mod           = fixExcelDateMySQL($data[18]);
        $last_price         = formatNumber4decNoComma($data[19]);
        $expected_amt       = formatNumber4decNoComma($data[20]);

        $sql.= " (
    '$program',
    $ship_code,
    '$buyer',
    '$swbs',
    '$item',
    '$spn',
    '$description',
    '$origrinal_smos_qty',
    '$remain_smos_qty',
    '$yard_due_date',
    '$lead_time',
    '$plan_order_date',
    '$uom',
    '$item_on_hand',
    '$item_on_order',
    '$item_shortage',
    '$on_hold',
    '$entered_on',
    '$last_mod',
    '$last_price',
    '$expected_amt'
),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "mars");
            print $sql;
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "mars");
    }
}
function insertGLdetailWITHWP(){
    /*
     * insert the GL with the matching WP and swbs.  So we can sum everything together properly.
     * items that dont match in the CBM get defaulted to the Commodity.
     * */
    $insert_sql = "
    INSERT  INTO meac.wp_gl_detail (
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
        effort
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
        pos,
        cust_supp,
        qty,
        unit,
        amt,
        date,
        integr_amt,
        clin,
        effort
    FROM mars.gl_detail gl LEFT JOIN meac.cbm cbm
      ON cbm.ship_code = gl.proj
      AND cbm.material = gl.item
      where cbm.wp is not null and gl.description not like '%total%'
      
    )";
    $junk = dbCall($insert_sql,"meac");
    $insert_sql = "
    INSERT  INTO meac.wp_gl_detail (
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
        effort
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
        pos,
        cust_supp,
        qty,
        unit,
        amt,
        date,
        integr_amt,
        clin,
        effort
    FROM mars.gl_detail gl LEFT JOIN meac.cbm cbm
      ON cbm.ship_code = gl.proj
      AND cbm.material = gl.item
      where cbm.wp is  null and gl.description not like '%total%'
    )";
    $junk = dbCall($insert_sql,"meac");
}
function insertOpenPOWithWP(){
    $insert_sql = "
    insert into meac.wp_open_po (
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
            effort) VALUES ";
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
            effort
        from mars.open_po po left join meac.cbm cbm on
        po.proj = cbm.ship_code
        and po.item = cbm.material
        where cbm.wp is not null
    ";
    $i=0;
    print $sql;
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
                '$effort'
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
    insert into meac.wp_open_po (
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
            effort) VALUES ";
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
            effort
from mars.open_po po left join meac.cbm cbm on
  po.proj = cbm.ship_code
  and po.item = cbm.material
    where cbm.wp is null
    ";
    $i=0;

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
                '$effort'
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
function insertOpenBuyWithWP(){
    $sql = "
    insert into meac.wp_open_buy(
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
        )(
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
        from mars.open_buy ob left join meac.cbm cbm
        on ob.ship_code = cbm.ship_code
        and ob.item = cbm.material
        where wp is not null
        )
";
    $junk = dbCall($sql,"meac");
    $sql = "
    insert into meac.wp_open_buy(
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
        )(
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
        from mars.open_buy ob left join meac.cbm cbm
        on ob.ship_code = cbm.ship_code
        and ob.item = cbm.material
        where wp is null
        )
";
    $junk = dbCall($sql,"meac");
}
function insertEBOMWP(){
    $insert_sql = "

        insert into meac.wp_ebom (
            program,
            ship_code,
            wp,
            material,
            cbm_material,
            spn,
            uom,
            item_group,
            swbs,
            ebom,
            ord_qty,
            on_hand,
            issued,
            unit_cost,
            supplier,
            noun1,
            noun2,
            noun3) 
        VALUES 
       ";
    $sql = "
        select
            ebom.program,
            ebom.ship_code,
            swbs,
            wp,
            ebom.material,
            cbm.material cbm_material,
            spn,
            uom,
            item_group,
            swbs,
            ebom,
            ord_qty,
            on_hand,
            issued,
            unit_cost,
            supplier,
            noun1,
            noun2,
            noun3
        from meac.ebom ebom left join meac.cbm cbm on
        ebom.ship_code = cbm.ship_code
        and ebom.material= cbm.material
        where cbm.wp is not null
    ";
    $i=0;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program      = $rs->fields["program"]  ;
        $ship_code    = intval($rs->fields["ship_code"]);
        $wp           = $rs->fields["wp"];
        $material     = $rs->fields["material"];
        $cbm_material = $rs->fields["cbm_material"];
        $spn          = $rs->fields["spn"];
        $uom          = $rs->fields["uom"];
        $item_group   = $rs->fields["item_group"];
        $swbs         = checkSWBSLength($rs->fields["swbs"]);
        $ebom         = formatNumber4decNoComma($rs->fields["ebom"]);
        $ord_qty      = formatNumber4decNoComma($rs->fields["ord_qty"]);
        $on_hand      = formatNumber4decNoComma($rs->fields["on_hand"]);
        $issued       = formatNumber4decNoComma($rs->fields["issued"]);
        $unit_cost    = formatNumber4decNoComma($rs->fields["unit_cost"]);
        $supplier     = $rs->fields["supplier"];
        $noun1        = $rs->fields["noun1"];
        $noun2        = $rs->fields["noun2"];
        $noun3        = $rs->fields["noun3"];

        $sql.=
            "(
                '$program',
                $ship_code,
                '$wp',
                '$material',
                '$cbm_material',
                '$spn',
                '$uom',
                '$item_group',
                '$swbs',
                '$ebom',
                $ord_qty,
                $on_hand,
                $issued,
                $unit_cost,
                '$supplier',
                '$noun1',
                '$noun2',
                '$noun3'
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

    $sql = "
        select
            ebom.program,
            ebom.ship_code,
            swbs,
            wp,
            ebom.material,
            cbm.material cbm_material,
            spn,
            uom,
            item_group,
            swbs,
            ebom,
            ord_qty,
            on_hand,
            issued,
            unit_cost,
            supplier,
            noun1,
            noun2,
            noun3
        from meac.ebom ebom left join meac.cbm cbm on
        ebom.ship_code = cbm.ship_code
        and ebom.material= cbm.material
        where cbm.wp is null
    ";
    $i=0;

    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program      = $rs->fields["program"];
        $ship_code    = intval($rs->fields["ship_code"]);
        $swbs          = checkSWBSLength($rs->fields["swbs"]);
        $wp            = "MATL-".$swbs."-999";
        $material     = $rs->fields["material"];
        $cbm_material = $rs->fields["cbm_material"];
        $spn          = $rs->fields["spn"];
        $uom          = $rs->fields["uom"];
        $item_group   = $rs->fields["item_group"];
        $swbs         = checkSWBSLength($rs->fields["swbs"]);
        $ebom         = formatNumber4decNoComma($rs->fields["ebom"]);
        $ord_qty      = formatNumber4decNoComma($rs->fields["ord_qty"]);
        $on_hand      = formatNumber4decNoComma($rs->fields["on_hand"]);
        $issued       = formatNumber4decNoComma($rs->fields["issued"]);
        $unit_cost    = formatNumber4decNoComma($rs->fields["unit_cost"]);
        $supplier     = $rs->fields["supplier"];
        $noun1        = $rs->fields["noun1"];
        $noun2        = $rs->fields["noun2"];
        $noun3        = $rs->fields["noun3"];

        $sql.=
            "(
                '$program',
                $ship_code,
                '$wp',
                '$material',
                '$cbm_material',
                '$spn',
                '$uom',
                '$item_group',
                '$swbs',
                '$ebom',
                $ord_qty,
                $on_hand,
                $issued,
                $unit_cost,
                '$supplier',
                '$noun1',
                '$noun2',
                '$noun3'
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
function insertCommittedPOWP(){
    $insert_sql = "
        insert into meac.wp_committed_po (
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
        from mars.committed_po po
        left join meac.cbm cbm on
        po.proj = cbm.ship_code
        and po.item= cbm.material
    where cbm.wp is not null
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
        from mars.committed_po po
        left join meac.cbm cbm on
        po.proj = cbm.ship_code
        and po.item= cbm.material
    where cbm.wp is null
    ";
    $i=0;

    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program      = "LCS";
        $ship_code    = intval($rs->fields["ship_code"]);
        $swbs          = checkSWBSLength($rs->fields["swbs"]);
        $wp            = "MATL-".$swbs."-999";
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
function insertMEACDataNoCommodity($table_name){
    $i= 0;
    $insert_sql = "
        INSERT  INTO  $table_name 
            (program,
            ship_code,
            ca,
            wp,
            cam,
            swbs,
            description,
            bac,
            eac,
            a,
            gl_a,
            open_po,
            open_buy_qty,
            open_buy_cost
            ) VALUES 
        ";
    $program   = "LCS";
    $sql = "
    select
        cost.ship_code,
        cost.wp,
        (select c1 from cost2.`201705_cost` s where ship_code = 481 and cost.ca = s.ca and s.wp = '') cam ,
        cost.ca as ca,
        gl.swbs,
        cost.descr,
        cost.bac,
        cost.eac eac,
        cost.a,
        sum(integr_amt) as gl_a,
        (select sum(pending_amnt) from wp_open_po po where po.wp = cost.wp and ship_code = 481) as open_po,
        (select sum(item_shortage) from wp_open_buy buy where buy.wp = cost.wp and ship_code = 481) as open_buy_qty,
        (select sum(expected_amt) from wp_open_buy buy where buy.wp = cost.wp and ship_code = 481) as open_buy_cost
    from 
      cost2.`201705_cost` cost 
      left join meac.wp_gl_detail gl
        on gl.ship_code = cost.ship_code
        and gl.wp = cost.wp
    where cost.wp <> '' and cost.wp like 'matl%'
    group by cost.ship_code, cost.wp
";
    print $sql;
    $rs = dbCall($sql,"meac");
    //die("made it");
    $sql = $insert_sql;

    while (!$rs->EOF)
    {
        $ship_code     = $rs->fields["ship_code"];
        $wp            = $rs->fields["wp"];
        $cam           = $rs->fields["cam"];
        $ca            = $rs->fields["ca"];
        $swbs          = $rs->fields["swbs"];

        $description   = addslashes(trim($rs->fields["descr"]));
        $bac           = formatNumber4decNoComma($rs->fields["bac"]);
        $eac           = formatNumber4decNoComma($rs->fields["eac"]);
        $a             = formatNumber4decNoComma($rs->fields["a"]);
        $gl_a          = formatNumber4decNoComma($rs->fields["gl_a"]);
        $open_po       = formatNumber4decNoComma($rs->fields["open_po"]);
        $open_buy_qty  = formatNumber4decNoComma($rs->fields["open_buy_qty"]);
        $open_buy_cost = formatNumber4decNoComma($rs->fields["open_buy_cost"]);
        if($swbs==""){
            $swbs = substr($wp, 5, 3);
        }

        $sql.=
            "(
            '$program',
            $ship_code,
            '$ca',
            '$wp',
            '$cam',
            $swbs,
            '$description',     
            $bac,
            $eac,
            $a,
            $gl_a,
            $open_po,
            $open_buy_qty,
            $open_buy_cost
            ),";
        if($i == 500)
        {
            print $sql;
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    print $sql;
//only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}
function returnInsertSQLSWBSSum(){
    $insert_sql = "
    insert into meac.swbs_gl_summary (
    program,
    ship_code,
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
    c_unit_price) VALUES 
";
    return $insert_sql;
}

function createInsertValuesString($program, $ship_code, $swbs_group, $swbs,
                                  $wp, $spn, $item, $item_group, $description,
                                  $unit, $noun1, $transfers, $c_amt, $last_unit_price,
                                  $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
                                  $last_unit_price_ship, $open_po_pending_amt,
                                  $open_buy_item_shortage, $etc, $eac, $uncommitted,
                                  $target_qty, $target_unit_price, $target_ext_cost,
                                  $vendor_name, $vendor_id, $buyer, $c_qty,
                                  $var_target_qty, $var_target_cost, $gl_qty, $var_ebom, $c_unit_price){
    $sql =
        "(
            '$program',
            $ship_code,
            $swbs_group,
            $swbs,
            '$wp',
            '$spn',
            '$item',
            '$item_group',
            '$description',
            '$unit',
            '$noun1',
            $transfers,
            $c_amt,
            $last_unit_price,
            $gl_int_amt,
            $ebom,
            $ebom_on_hand,
            $ebom_issued,
            $open_po_pending_amt,
            $open_buy_item_shortage,
            '$last_unit_price_ship',
            $etc,
            $eac,
            $uncommitted,
            $target_qty,
            $target_unit_price,
            $target_ext_cost,
            '$vendor_name',
            '$vendor_id',
            '$buyer',
            $c_qty,
            $var_target_qty,
            $var_target_cost,
            $gl_qty,
            $var_ebom,
            $c_unit_price),";
    return $sql;
}
function insertSWBSSummaryOPENPO($ship_code)
{
    $insert_sql = returnInsertSQLSWBSSum();
    $sql = "
        select
            open_po.ship_code,
            open_po.wp,
            case when CHAR_LENGTH(open_po.swbs) = 3 then concat(left(open_po.swbs,1),'00') 
              ELSE '000' end as swbs_group,
            open_po.swbs,
            e.spn,f
            open_po.item,
            e.item_group,
            e.noun1,
            open_po.description,
            e.uom as unit,
            e.ebom,
            e.issued ebom_issued,
            e.on_hand ebom_onhand,
            tc.ext_cost target_ext_cost,
            tc.qty target_qty,
            tc.unit_cost target_unit_cost,
            gl.cust_supp as vendor_name,
            open_po.vendor as vendor_id,
            (select item_shortage from wp_open_buy ob where ob.ship_code=open_po.ship_code and ob.item=open_po.item  limit 1) open_buy_item_shortage,
            (select sum(pending_amnt) from wp_open_po opo where opo.ship_code=open_po.ship_code and opo.item=open_po.item) open_po_pending_amt,
            (select sum(integr_amt) from wp_gl_detail gl2 where gl2.ship_code=open_po.ship_code and gl2.item=open_po.item and gl2.document like '%INV%') transfers,
            (select sum(commit_amnt) from wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) commit_amt,
            (select avg(unit_price) from wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) c_unit_price,
            (select unit_price from wp_committed_po c where c.item=open_po.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
            (select concat(unit_price,' - ',c.ship_code) from wp_committed_po c where c.item=open_po.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
            (select sum(committed_qty) from wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_qty,
            0 as gl_qty,
            0 as int_amt,
            case when open_buy.buyer is null
              then left(gl.`order`,2)
            else NULL end as buyer
        from wp_open_po open_po
        left join wp_ebom e
          on e.ship_code = open_po.ship_code and e.material =open_po.item
        left join wp_gl_detail gl
          on open_po.ship_code = gl.ship_code and open_po.item= gl.item
        left join meac.target_cost tc 
          on tc.item=open_po.item
        left join wp_open_buy open_buy
            on open_po.ship_code = open_buy.ship_code and open_po.item= open_buy.item
        where gl.ship_code is null
          and open_po.ship_code = $ship_code
";
    $i=0;

    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                = "LCS";
        $ship_code              = $rs->fields["ship_code"];
        $wp                     = $rs->fields["wp"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $noun1                  = $rs->fields["noun1"];
        $description            = processDescription($rs->fields["description"]);
        $unit                   = $rs->fields["uom"];
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
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);
        $c_unit_price           = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name            = $rs->fields["vendor_name"];
        $vendor_id              = $rs->fields["vendor_id"];
        $buyer                  = $rs->fields["buyer"];
        $c_qty                  = formatNumber4decNoComma($rs->fields["commit_qty"]);
        $gl_qty                 = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $var_target_qty         = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost        = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom               = formatNumber4decNoComma($ebom - $ebom_issued);

        $sql.=createInsertValuesString($program, $ship_code, $swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name, $vendor_id, $buyer,
            $c_qty, $var_target_qty, $var_target_cost, $gl_qty, $var_ebom,$c_unit_price);
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
function insertSWBSGLSUM($ship_code){
    $insert_sql = returnInsertSQLSWBSSum();

    $sql = "
   select
            gl.ship_code,
            gl.wp,
            case when CHAR_LENGTH(gl.swbs) = 3 then concat(left(gl.swbs,1),'00') ELSE
              '000' end as swbs_group,
            gl.swbs,
            e.spn,
            gl.item,
            e.item_group,
            e.noun1,
            gl.description,
            gl.unit,
            e.ebom,
            e.issued ebom_issued,
            e.on_hand ebom_onhand,
            tc.ext_cost target_ext_cost,
            tc.qty target_qty,
            tc.unit_cost target_unit_cost,
            gl.cust_supp as vendor_name,
            wpc.vendor as vendor_id,
            (select item_shortage from wp_open_buy ob where ob.ship_code=gl.ship_code and ob.item=gl.item  limit 1) open_buy_item_shortage,
            (select sum(pending_amnt) from wp_open_po opo where opo.ship_code=gl.ship_code and opo.item=gl.item) open_po_pending_amt,
            (select sum(integr_amt) from wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%') transfers,
            (select sum(commit_amnt) from wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_amt,
            (select avg(unit_price) from wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) c_unit_price,
            (select unit_price from wp_committed_po c where c.item=gl.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
            (select concat(unit_price,' - ',c.ship_code) from wp_committed_po c where c.item=gl.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
            (select sum(committed_qty) from wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_qty,
            (select sum(qty) from wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.integr_amt > 0) gl_qty,
            (select sum(qty) from wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt < 0) gl_qty_transfers_off,
            sum(gl.integr_amt) int_amt,
            case when open_buy.buyer is null
              then left(gl.`order`,2)
            else left(wpc.po, 2) end as buyer
        from wp_gl_detail gl
        left join wp_ebom e
                on e.ship_code = gl.ship_code and e.material= gl.item
        left join wp_committed_po wpc
                on wpc.ship_code = gl.ship_code and wpc.item= gl.item
        left join meac.target_cost tc on
            tc.item=gl.item
        left join wp_open_buy open_buy
            on gl.ship_code = open_buy.ship_code and gl.item= open_buy.item
         where gl.ship_code = $ship_code
        group by gl.ship_code, gl.item
";
    $i=0;
    //print $sql;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                 = "LCS";
        $ship_code               = $rs->fields["ship_code"];
        $wp                      = $rs->fields["wp"];
        $swbs_group              = $rs->fields["swbs_group"];
        $swbs                    = $rs->fields["swbs"];
        $spn                     = $rs->fields["spn"];
        $item                    = $rs->fields["item"];
        $item_group              = $rs->fields["item_group"];
        $noun1                   = $rs->fields["noun1"];
        $description             = processDescription($rs->fields["description"]);
        $unit                    = $rs->fields["unit"];
        $ebom                    = formatNumber4decNoComma($rs->fields["ebom"]);
        $ebom_issued             = formatNumber4decNoComma($rs->fields["ebom_issued"]);
        $ebom_on_hand            = formatNumber4decNoComma($rs->fields["ebom_onhand"]);
        $transfers               = formatNumber4decNoComma($rs->fields["transfers"]);
        $last_unit_price_ship    = $rs->fields["last_unit_price_ship"];
        $open_buy_item_shortage  = formatNumber4decNoComma($rs->fields["open_buy_item_shortage"]);
        $open_po_pending_amt     = formatNumber4decNoComma($rs->fields["open_po_pending_amt"]);
        $c_amt                   = formatNumber4decNoComma($rs->fields["commit_amt"]);
        $last_unit_price         = formatNumber4decNoComma($rs->fields["last_unit_price"]);
        $gl_int_amt              = formatNumber4decNoComma($rs->fields["int_amt"]);
        $target_qty              = formatNumber4decNoComma($rs->fields["target_qty"]);
        $target_unit_price       = formatNumber4decNoComma($rs->fields["target_unit_cost"]);
        $target_ext_cost         = formatNumber4decNoComma($rs->fields["target_ext_cost"]);
        $etc                     = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage);
        $eac                     = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted             = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);
        $c_unit_price            = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name             = $rs->fields["vendor_name"];
        $vendor_id               = $rs->fields["vendor_id"];
        $buyer                   = $rs->fields["buyer"];
        $c_qty                   = formatNumber4decNoComma($rs->fields["commit_qty"]);
        $gl_qty_withtransfers_on = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $gl_qty_transfers_off    = formatNumber4decNoComma($rs->fields["gl_qty_transfers_ff"]);
        $gl_qty                  = $gl_qty_withtransfers_on - $gl_qty_transfers_off;
        $var_target_qty          = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost         = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom                = formatNumber4decNoComma($ebom - $ebom_issued);

        $sql.=createInsertValuesString($program, $ship_code, $swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name,
            $vendor_id, $buyer,$c_qty, $var_target_qty,
            $var_target_cost, $gl_qty, $var_ebom, $c_unit_price);
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
function calcETC($last_unit_price, $bid_price, $open_buy_item_shortage){
    if($last_unit_price==0){
        $etc = formatNumber4decNoComma($open_buy_item_shortage * $bid_price);
    }
    else{
        $etc = formatNumber4decNoComma($open_buy_item_shortage * $last_unit_price);
    }
    return $etc;
}
function insertSWBSSummaryOPENBUY($ship_code)
{
    $insert_sql = returnInsertSQLSWBSSum();

    $sql = "
            select open_buy.ship_code,
                open_buy.wp,
                case when CHAR_LENGTH(open_buy.swbs) = 3 then concat(left(open_buy.swbs,1),'00') ELSE
                  '000' end as swbs_group,
                open_buy.swbs,
                e.spn,
                open_buy.item,
                e.item_group,
                e.noun1,
                open_buy.description,
                e.uom as unit,
                e.ebom,
                e.issued ebom_issued,
                e.on_hand ebom_onhand,
                open_buy.item_shortage open_buy_item_shortage,
                tc.ext_cost target_ext_cost,
                tc.qty target_qty,
                tc.unit_cost target_unit_cost,
                0 as open_po_pending_amt,
                0 as num_transfers,
                0 commit_amt,
                0 c_unit_price,
                '' as vendor_name,
                '' as vendor_id,
                (select unit_price from wp_committed_po c where c.item=open_buy.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
                (select concat(unit_price,' - ',c.ship_code) from wp_committed_po c where c.item=open_buy.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
                0 as  commit_qty,
                0 as gl_qty,
                0 as int_amt,
                open_buy.buyer buyer
            from wp_open_buy open_buy
            left join wp_ebom e
              on e.ship_code = open_buy.ship_code and e.material =open_buy.item
            left join wp_gl_detail gl
              on open_buy.ship_code = gl.ship_code and open_buy.item= gl.item
            left join wp_open_po open_po
              on open_po.ship_code = open_buy.ship_code and open_po.item= open_buy.item
            left join meac.target_cost tc on
                tc.item=open_buy.item
            where
              gl.ship_code is null and
                open_po.ship_code is null
              -- and open_buy.wp = 'MATL-551-011'
              and open_buy.ship_code = $ship_code
";
    $i=0;
    //print $sql;
    $rs= dbCall($sql, "meac");
    $sql = $insert_sql;
    while (!$rs->EOF) {
        $program                = "LCS";
        $ship_code              = $rs->fields["ship_code"];
        $wp                     = $rs->fields["wp"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $noun1                  = $rs->fields["noun1"];
        $description            = processDescription($rs->fields["description"]);
        $unit                   = $rs->fields["unit"];
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
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc +$open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);
        $c_unit_price           = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $vendor_name            = $rs->fields["vendor_name"];
        $vendor_id              = $rs->fields["vendor_id"];
        $buyer                  = $rs->fields["buyer"];
        $c_qty                  = formatNumber4decNoComma($rs->fields["commit_qty"]);
        $gl_qty                 = formatNumber4decNoComma($rs->fields["gl_qty"]);
        $var_target_qty         = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost        = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom               = formatNumber4decNoComma($ebom - $ebom_issued);

        $sql.=createInsertValuesString($program, $ship_code, $swbs_group, $swbs,
            $wp, $spn, $item, $item_group, $description,
            $unit, $noun1, $transfers, $c_amt, $last_unit_price,
            $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
            $last_unit_price_ship, $open_po_pending_amt,
            $open_buy_item_shortage, $etc, $eac, $uncommitted,
            $target_qty, $target_unit_price, $target_ext_cost,$vendor_name,
            $vendor_id, $buyer,$c_qty, $var_target_qty,
            $var_target_cost, $gl_qty, $var_ebom, $c_unit_price);
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
function insertSWBSSummary($ship_code){
    insertSWBSSummaryOPENPO($ship_code);
    insertSWBSGLSUM($ship_code);
    insertSWBSSummaryOPENBUY($ship_code);
}

function insertTargetCost($path2file){
    print $path2file;
    $path_exploded_array = explode("\\", $path2file);
    $file_name           = $path_exploded_array[1];
    $ship           = substr($file_name, -22, 6);

    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "INSERT into meac.target_cost (swbs, item,description, uom, qty, unit_cost, ext_cost, ship) VALUES ";

    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $swbs        = trim($data[0]);
        $item        = trim($data[1]);
        $description = processDescription(trim($data[2]));
        $uom         = trim($data[3]);
        $qty         = formatNumber4decNoComma(trim($data[4]));
        $unit_cost   = formatNumber4decNoComma(trim($data[5]));
        $ext_cost    = formatNumber4decNoComma(trim($data[6]));

        $sql.= "(
            '$swbs',
            '$item',
            '$description',
            '$uom',
            $qty,
            $unit_cost,
            $ext_cost,
            '$ship'),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            print $sql;
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "meac");
    }
}
