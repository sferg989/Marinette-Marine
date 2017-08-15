<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/13/2017
 * Time: 2:01 PM
 */
function processDescription($desc){
    $desc = str_replace(",", " and ", trim($desc));
    $desc = str_replace("\"", " and ", trim($desc));
    $desc = str_replace('"', "", $desc);
    $desc = str_replace('
    ', "", $desc);
    $desc = str_replace("'", "", $desc);
    $desc = str_replace(array("\n\r", "\n", "\r"), " ", $desc);
    $desc = str_replace("", "", $desc);
    $desc = str_replace("/", "", $desc);
    $desc = str_replace("#", "Num ", $desc);
    return $desc;
}
function processDescriptionAgain($desc){
    $desc = str_replace("\"", " and ", trim($desc));
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
            effort,
            ecp_rea 
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
        $ecp_rea       = trim($data[20]);

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
                '$effort',
                '$ecp_rea'
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
    //$proj = substr($file_name, 0,4);
    $proj = substr($file_name, 5,4);


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
        proj, 
        ecp_rea) 
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
        $ecp_rea      = addslashes(trim($data[15]));
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
                $proj,
                '$ecp_rea'
                ),";
        if($i == 1000)
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
    if($i !=1000)
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
function insertETCLOADFILE($path2file){
    print $path2file;

    $handle = fopen($path2file,"r");
    //remove headers from the file.
    //loop through the csv file and insert into database
    $insert_sql = "insert into meac.swbs_gl_summary_stage (ship_code, wp, swbs, swbs_group, item, etc, category) values ";

    $sql = $insert_sql;
    /*create counter so insert 1000 rows at a time.*/
    $i=0;
    /*skip header*/
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ship_code  = intval($data[0]);
        $item       = trim($data[1]);
        $swbs       = substr(trim($data[3]), 5, 3);
        $swbs       = checkSWBSLength($swbs);
        $swbs_group = substr($swbs, 0, 1);
        $swbs_group .="00";
        $etc       = formatNumber4decNoComma($data[2]);
        $wp        = trim($data[3]);

        $sql.= "(                                                 
        $ship_code,
        '$wp',
        '$swbs',
        '$swbs_group',
        '$item',
        $etc,
        'Load File Entry'),";
        if($i == 1000)
        {
            $sql  = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            print $sql;
            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=1000)
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
        if($ship_code=="Total"){
            continue;
        }
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
        effort,
        ecp_rea
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
        ecp_rea
    FROM mars.gl_detail gl LEFT JOIN meac.cbm cbm
      ON cbm.ship_code = gl.proj
      AND cbm.material = gl.item
      where cbm.wp is not null 
      
    )";
    //$sql = "and gl.description not like '%total%'";

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
        effort,
        ecp_rea
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
        ecp_rea
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

function returnOpenBuyInsert(){
    $insert_sql = "insert into meac.wp_open_buy(
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
function insertOpenBuyValues($program,$ship_code,$wp,$cbm_material,$buyer,$swbs,
                             $item,$spn,$description,$origrinal_smos_qty,$remain_smos_qty,
                             $yard_due_date,$lead_time,$plan_order_date,$uom,$item_on_hand,
                             $item_on_order,$item_shortage,$on_hold,$entered_on,$last_mod,
                             $last_price,$expected_amt){
    $sql =
        "(
                '$program',
                $ship_code,
                '$wp',
                '$cbm_material',
                '$buyer',
                '$swbs',
                '$item',
                '$spn',
                '$description',
                $origrinal_smos_qty,
                $remain_smos_qty,
                '$yard_due_date',
                $lead_time,
                '$plan_order_date',
                '$uom',
                $item_on_hand,
                $item_on_order,
                $item_shortage,
                '$on_hold',
                '$entered_on',
                '$last_mod',
                $last_price,
                $expected_amt
            ),";
    return $sql;
}
function insertOpenBuyWithWP(){
    //$ship_code_wc = "and ob.ship_code = 485";
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
        from mars.open_buy ob left join meac.cbm cbm
        on ob.ship_code = cbm.ship_code
        and ob.item = cbm.material 
        where wp is not null  $ship_code_wc
        $gb $ob
    ";
    $rs= dbCall($sql, "meac");
    print $sql;
    $sql = returnOpenBuyInsert();
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
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = returnOpenBuyInsert();
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
    //print $sql;
    //die("made it");
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
        from mars.open_buy ob left join meac.cbm cbm
        on ob.ship_code = cbm.ship_code
        and ob.item = cbm.material
        where wp is null  $ship_code_wc
        $gb $ob
    ";
    $rs= dbCall($sql, "meac");
    $sql = returnOpenBuyInsert();
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
        if($i == 1000)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "meac");
            $i=0;
            //clear out the sql stmt.
            $sql = returnOpenBuyInsert();
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
function returnInsertSQLSWBSSum($table_name){
    $insert_sql = "
    insert into meac.$table_name(
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

function createInsertValuesString($program, $ship_code, $category,$swbs_group, $swbs,
                                  $wp, $spn, $item, $item_group, $description,
                                  $unit, $noun1, $transfers, $c_amt, $last_unit_price,
                                  $gl_int_amt, $ebom, $ebom_on_hand, $ebom_issued,
                                  $last_unit_price_ship, $open_po_pending_amt,
                                  $open_buy_item_shortage, $etc, $eac, $uncommitted,
                                  $target_qty, $target_unit_price, $target_ext_cost,
                                  $vendor_name, $vendor_id, $buyer, $c_qty,
                                  $var_target_qty, $var_target_cost, $gl_qty, $var_ebom,
                                  $c_unit_price, $document, $ecp_rea,$clin, $effort, $po_data, $tc,
                                  $item_group_description, $change_date,$change_reason){
    $sql =
        "(
            '$program',
            $ship_code,
            '$category',
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
            $vendor_id,
            '$buyer',
            $c_qty,
            $var_target_qty,
            $var_target_cost,
            $gl_qty,
            $var_ebom,
            $c_unit_price,
            '$document',
            '$clin',
            '$effort',
            '$ecp_rea',
            '$po_data',
            '$tc',
            '$item_group_description',
            '$change_date',
            '$change_reason'
            ),";
    return $sql;
}
function calcVarEBOM($ebom, $c_qty, $category, $description){

    $var_ebom  = formatNumber4decNoComma($ebom - $c_qty);

    if($category=="Vendor Service"){
        $var_ebom = 0;
    }
    if(stripos($description, "PROGRESS PAY")!==false){
        $var_ebom = 0;
    }
    return $var_ebom;
}
function insertSWBSSummaryOPENPO($ship_code)
{
    $ob = "open_po.ship_code, open_po.wp, open_po.item";
    $gb = "open_po.ship_code, open_po.wp, open_po.item";
    $insert_sql = returnInsertSQLSWBSSum("swbs_gl_summary_stage");
    $sql = "
                select
                    open_po.ship_code,
                    open_po.wp,
                    cat.category,
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
                    (select ci.date from meac.change_item ci where ci.ship_code=open_po.ship_code and ci.item=open_po.item  order by ci.date DESC limit 1) change_date,
                    (select ci.description from meac.change_item ci where ci.ship_code=open_po.ship_code and ci.item=open_po.item  order by ci.date DESC limit 1) change_reason,
                    (select qty from target_cost tc where tc.item=open_po.item  limit 1) target_qty,
                    (select unit_cost  from target_cost tc where tc.item=open_po.item  limit 1) target_unit_cost,
                    (SELECT vendor FROM meac.po_data po_data where open_po.vendor=po_data.vendor_id  limit 1) vendor_name,
                    gl.document as document,
                    open_po.vendor as vendor_id,
                    (select item_shortage from wp_open_buy ob where ob.ship_code=open_po.ship_code and ob.item=open_po.item  limit 1) open_buy_item_shortage,
                    (select sum(pending_amnt) from wp_open_po opo where opo.ship_code=open_po.ship_code and opo.item=open_po.item) open_po_pending_amt,
                    (select sum(integr_amt) from wp_gl_detail gl2 where gl2.ship_code=open_po.ship_code and gl2.item=open_po.item and gl2.document like '%INV%') transfers,
                    (select sum(commit_amnt) from wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) commit_amt,
                    (select avg(unit_price) from wp_committed_po c where c.ship_code=open_po.ship_code and c.item=open_po.item group by c.ship_code, c.item) c_unit_price,
                    (select unit_price from wp_committed_po c where c.item=open_po.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
                    (select c.ship_code from wp_committed_po c where c.item=open_po.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
                    (select sum(committed_qty) from wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_qty,
                    0 as gl_qty,
                    0 as int_amt,
                    open_po.ecp_rea,
                    open_po.clin,
                    open_po.effort,
                    (SELECT GROUP_CONCAT(DISTINCT CONCAT(`buyer`)) FROM meac.po_data po_data where po_data.po= open_po.po) buyer
                from wp_open_po open_po
                left join wp_ebom e
                  on e.ship_code = open_po.ship_code and e.material =open_po.item
                left join wp_gl_detail gl
                  on open_po.ship_code = gl.ship_code and open_po.item= gl.item
                left join wp_open_buy open_buy
                    on open_po.ship_code = open_buy.ship_code and open_po.item= open_buy.item
                left join meac.category cat
                    on open_po.item= cat.item
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
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);
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
function insertSWBSGLSUM($ship_code){
    $insert_sql = returnInsertSQLSWBSSum("swbs_gl_summary_stage");
    $ob = "gl.ship_code, gl.wp, gl.item";
    $gb = "gl.ship_code, gl.wp, gl.item";
    $sql = "
    select
            gl.ship_code,
            gl.wp,
            cat.category,
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
            (SELECT GROUP_CONCAT(DISTINCT CONCAT(`order`, '-', pos)) FROM meac.wp_gl_detail gl2 where gl2.item= gl.item and gl2.ship_code= gl.ship_code ) po_data,
            (SELECT GROUP_CONCAT(DISTINCT CONCAT(`origins`, ' - ')) FROM meac.k2_efdb k2 where k2.item= gl.item and k2.ship_code= gl.ship_code ) tc,
            (select ci.date from meac.change_item ci where ci.ship_code=gl.ship_code and ci.item=gl.item  order by ci.date DESC limit 1) change_date,
            (select ci.description from meac.change_item ci where ci.ship_code=gl.ship_code and ci.item=gl.item  order by ci.date DESC limit 1) change_reason,
            (select vendor from meac.wp_committed_po wpc where wpc.ship_code=gl.ship_code and wpc.item = gl.item limit 1) vendor_id,
            (select item_shortage from meac.wp_open_buy ob where ob.ship_code=gl.ship_code and ob.item=gl.item  limit 1) open_buy_item_shortage,
            (select sum(pending_amnt) from meac.wp_open_po opo where opo.ship_code=gl.ship_code and opo.item=gl.item) open_po_pending_amt,
            (select sum(integr_amt) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%') transfers,
            (select sum(commit_amnt) from meac.wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_amt,
            (select avg(unit_price) from meac.wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) c_unit_price,
            (select unit_price from meac.wp_committed_po c where c.item=gl.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
            (select c.ship_code from meac.wp_committed_po c where c.item=gl.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
            (select sum(committed_qty) from meac.wp_committed_po c where c.ship_code=gl.ship_code and c.item=gl.item group by c.ship_code, c.item) commit_qty,
            (select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%PUR%' and gl2.integr_amt < 0) gl_pur_qty_off,
            (select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%PUR%' and gl2.integr_amt > 0) gl_pur_qty_on,
            (select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt < 0) gl_qty_transfers_off,
            (select sum(qty) from meac.wp_gl_detail gl2 where gl2.ship_code=gl.ship_code and gl2.item=gl.item and gl2.document like '%INV%' and gl2.integr_amt > 0) gl_qty_transfers_on,
            sum(gl.integr_amt) int_amt,
            (SELECT GROUP_CONCAT(DISTINCT CONCAT(`buyer`)) FROM meac.po_data po_data where po_data.po= gl.`order`) buyer,
            gl.ecp_rea as ecp_rea,
            gl.clin as clin,
            gl.effort as effort
        from meac.wp_gl_detail gl
        left join meac.wp_ebom e
                on e.ship_code = gl.ship_code and e.material= gl.item
        left join meac.wp_open_buy open_buy
            on gl.ship_code = open_buy.ship_code and gl.item= open_buy.item
        left join meac.category cat
            on gl.item= cat.item
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
        $etc                     = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage);
        $eac                     = formatNumber4decNoComma($gl_int_amt + $etc + $open_po_pending_amt);
        $uncommitted             = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);
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

        $gl_qty          = $gl_pur_qty_on - $gl_pur_qty_off + $gl_qty_transfers_on - $gl_qty_transfers_off;
        $c_qty           = $c_qty + $gl_qty_transfers_on - $gl_qty_transfers_off;

        $var_target_qty  = formatNumber4decNoComma($ebom - $target_qty);
        $var_target_cost = formatNumber4decNoComma($c_unit_price - $target_unit_price);
        $var_ebom        = calcVarEBOM($ebom, $c_qty, $category, $description);

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
    $insert_sql = returnInsertSQLSWBSSum("swbs_gl_summary_stage");
    $ob = "open_buy.ship_code, open_buy.wp, open_buy.item";
    $sql = "
            select open_buy.ship_code,
                open_buy.wp,
                cat.category,
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
                (select unit_price from wp_committed_po c where c.item=open_buy.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price,
                (select ci.date from meac.change_item ci where ci.ship_code=open_buy.ship_code and ci.item=open_buy.item  order by ci.date DESC limit 1) change_date,
                (select ci.description from meac.change_item ci where ci.ship_code=open_buy.ship_code and ci.item=open_buy.item  order by ci.date DESC limit 1) change_reason,
                (select c.ship_code from wp_committed_po c where c.item=open_buy.item and unit_price > 0 order by c.ship_code desc limit 1) last_unit_price_ship,
                0 as  commit_qty,
                0 as gl_qty,
                0 as int_amt,
                (select buyer from master_buyer mb where open_buy.buyer = mb.id) buyer
            from wp_open_buy open_buy
            left join wp_ebom e
              on e.ship_code = open_buy.ship_code and e.material =open_buy.item
            left join wp_gl_detail gl
              on open_buy.ship_code = gl.ship_code and open_buy.item= gl.item
            left join wp_open_po open_po
              on open_po.ship_code = open_buy.ship_code and open_po.item= open_buy.item
            left join meac.category cat
              on open_buy.item= cat.item
            where
              gl.ship_code is null and
                open_po.ship_code is null
              -- and open_buy.wp = 'MATL-551-011'
              and open_buy.ship_code = $ship_code
              order by $ob
";
    $i=0;
    //print $sql;
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
        $etc                    = calcETC($last_unit_price, $target_unit_price, $open_buy_item_shortage);
        $eac                    = formatNumber4decNoComma($gl_int_amt + $etc +$open_po_pending_amt);
        $uncommitted            = formatNumber4decNoComma($eac - $gl_int_amt - $open_po_pending_amt);
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

function insertJournalEntries($ship_code){
    if(strlen($ship_code)==3)
    {
        $program = "0".$ship_code;
    }
    $insert_sql = "
        insert into meac.swbs_gl_summary_stage (program, ship_code, category, swbs_group, swbs, wp, gl_int_amt) VALUES 
        ";
    $sql = "
select CAWPID, ea from
  (select
     cawpid,
     sum(DIRECT) ea
   from TPHASE where
     PROGRAM = '$program'
     and CLASS = 'ea'
     and CAWPID in (select CAWPID from CAWP where CAWP.PROGRAM = '$program' and wp LIKE '%matl%')
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
function insertSWBSSummaryStaging($ship_code){
    insertSWBSSummaryOPENPO($ship_code);
    insertSWBSGLSUM($ship_code);
    insertSWBSSummaryOPENBUY($ship_code);
    insertGlChargesNoPartNumAlloc($ship_code);
    insertGlChargesNoPartNum($ship_code);
    insertJournalEntries($ship_code);
}

function insertSWBSSummary($value){
    $insert_sql= returnInsertSQLSWBSSum("swbs_gl_summary");
    $sql = "
    select
        program,
        ship_code,
        category,
        max(swbs_group) swbs_group,
        max(swbs) swbs,
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
        sum(etc) etc,
        eac,
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
        from swbs_gl_summary_stage 
        where ship_code = $value
        group  by ship_code, wp, item -- limit 4500, 500
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
        $item                   = $rs->fields["item"];
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
        $change_reason          = $rs->fields["change_reason"];

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
    print $sql;
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

function returnAllocInsertSQL(){
    $sql = "
insert into swbs_gl_summary_stage (ship_code, wp, category, swbs_group, swbs, item, description, unit,vendor_name, gl_qty,gl_int_amt) VALUES";
    return $sql;
}

function createInsertValuesStringAllocations($program, $ship_code, $category,$swbs_group, $swbs,
                                             $wp, $item, $description,$unit, $gl_int_amt,$vendor_name, $gl_qty){
    $sql =
        "(
        '$program',
        $ship_code,
        '$wp',
        '$category',
        '$swbs_group',
        '$swbs',
        '$item',
        '$description',
        '$unit',
        '$vendor_name',
        $gl_qty,
        $gl_int_amt
),";
    return $sql;
}
function insertGlChargesNoPartNumAlloc($ship_code)
{
    $insert_sql = returnAllocInsertSQL();
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
        from wp_gl_detail gl
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

function returnOtherNOPartNumInsertSQL(){
    $sql = "
    insert into swbs_gl_summary_stage (ship_code, wp, category, swbs_group, swbs, item, description, unit,vendor_name, gl_qty,gl_int_amt) VALUES";
    return $sql;
}

function createInsertValuesStringNOPartNum($program, $ship_code, $category,$swbs_group, $swbs,
                                           $wp, $item, $description,$unit, $gl_int_amt,$vendor_name, $gl_qty){
    $sql =
        "(
        '$program',
        $ship_code,
        '$wp',
        '$category',
        '$swbs_group',
        '$swbs',
        '$item',
        '$description',
        '$unit',
        '$vendor_name',
        $gl_qty,
        $gl_int_amt
),";
    return $sql;
}
function insertGlChargesNoPartNum($ship_code)
{
    $insert_sql = returnOtherNOPartNumInsertSQL();
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
        from wp_gl_detail gl
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
function returnMeacSQL($wc="", $gb=""){
    $sql = "
    select 
        program,
        ship_code,
        swbs_group,
        category,
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
        c_unit_price,
        last_unit_price,
        sum(gl_int_amt) gl_int_amt,
        sum(ebom) ebom,
        sum(ebom_on_hand) ebom_on_hand,
        sum(ebom_issued) ebom_issued,
        last_unit_price_ship,
        sum(open_po_pending_amt) open_po_pending_amt,
        sum(open_buy_item_shortage) open_buy_item_shortage,
        sum(etc) etc,
        sum(eac) eac,
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
        sum(gl_qty) gl_qty,
        sum(var_ebom) var_ebom
    from meac.swbs_gl_summary $wc $gb 
    ";
    return $sql;
}

function getFieldNamesForSQL($wc="", $gb=""){
    $sql = "select field_name  from field_list order by default_order";
    $rs = dbCall($sql,"meac");
    $sql = "select ";
    while (!$rs->EOF)
    {
        $field_name = $rs->fields["field_name"];
        $sql.="$field_name,";
        $rs->MoveNext();
    }

    $sql = substr($sql, 0, -1);
    $limit  = "";
    $sql .= " from meac.swbs_gl_summary $wc $gb $limit";
    return $sql;
}
function correctShockOpenBuyItemShortage($ship_code){
    $sql = "
        update mars.open_buy
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
                      from mars.open_po
                      where item in
                        (select item from mars.open_buy where spn = '-S00')
                        and proj = $ship_code
                    group by proj, item) s
                where s.sum =0 and s.proj = $ship_code)
    ";
    $junk = dbCall($sql, "mars");
}