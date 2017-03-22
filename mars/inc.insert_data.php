<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/13/2017
 * Time: 2:01 PM
 */
function insertOpenPO($path2file, $period){
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
            period
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
                '$effort',
                $period
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
function insertCommittedPO($path2file, $period){
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
            effort,
            period
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
                '$effort',
                $period
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
function insertGLdetail($path2file, $period){
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
        period,
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
                $period,
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