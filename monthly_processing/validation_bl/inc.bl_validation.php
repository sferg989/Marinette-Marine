<?php

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/15/2017`
 * Time: 9:01 AM
 */

function loadPCSBL($rpt_period, $schema, $ship_code, $pcs_bl_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug){
    $table_name   = $rpt_period."_pcs_bl_labor";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_pcs_bl_labor", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code, ca, wp, type, bl_labor) values ";

    $new_csv_file_name = $ship_code."pcs_bl_validation";
    $path2_source_xls = $path2xlsfile."/$pcs_bl_file_name";

    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);
    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_bl_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    $i=0;
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ca       = addslashes(trim($data[0]));
        $wp       = addslashes(trim($data[1]));
        $type     = addslashes(trim($data[2]));
        $bl_labor = formatNumber4decNoComma($data[5]);

        $sql.=
            "(
                $ship_code,
                '$ca',
                '$wp',
                '$type',
                $bl_labor
            ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, $schema);
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
        //print $sql;

        $junk = dbCall($sql, $schema);
    }
}
function loadHistoryCheck($rpt_period, $schema, $ship_code, $hc_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug){
    $table_name   = $rpt_period."_hc_check";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_hc_check", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code, type, cost_set, date, val) VALUES";

    $new_csv_file_name = $ship_code."hc_check";
    $path2_source_xls = $path2xlsfile."/$hc_file_name";

    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);
    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_bl_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    $i=0;
    while (($data = fgetcsv($handle)) !== FALSE)
    {

        $type       = addslashes(trim($data[0]));
        $cost_set   = addslashes(trim($data[2]));
        $date       = addslashes(trim($data[3]));
        $rpt_period = createRPTfromDateSlash($date);
        $val        = removeCommanDollarSignParan($data[4]);
        $sql.=
            "(
                $ship_code,
                '$type',
                '$cost_set',
                $rpt_period,
                $val
            ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, $schema);
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
        print $sql;
        $junk = dbCall($sql, $schema);
    }
}

function returnTableFromRS($rs, $table_name){
    $data_table = "<table class = 'table table-sm'>
            <tr ><th colspan = 5 class = 'table_headers'>$table_name</th></tr>
            <tr class = 'table_headers'>
                <th>CA</th>
                <th>WP</th>
                <th>P6</th>
                <th>Cobra</th>
                <th>DIFF</th>
            </tr>
    ";
    $i=0;
    while (!$rs->EOF)
    {
        $ca          = $rs->fields["ca"];
        $wp          = $rs->fields["wp"];
        $p6_labor    = formatNumber4decNoComma($rs->fields["p6_labor"]);
        $cobra_labor = formatNumber4decNoComma($rs->fields["cobra_labor"]);
        $diff        = formatNumber($p6_labor - $cobra_labor);

        if($diff>1 or $diff < -1){
            $data_table.="
            <tr align=\"center\" class = 'table_data'>
                <td>$ca</td>
                <td>$wp</td>
                <td>$p6_labor</td>
                <td>$cobra_labor</td>
                <td>$diff</td>
            </tr>
            ";
            $i++;
        }

        $rs->MoveNext();
    }
    if($i<1){
        $data_table.="
            <tr class = 'table_headers'>
                <td colspan='5'>There are no Records to Display</td>
            </tr>
            ";
    }
    $data_table.= "</table>";
    return $data_table;
}
function validatePCS2P6BLLabor($schema, $rpt_period, $ship_code){
    /*Material*/
    $data_table = "";
    if($ship_code<477 or $ship_code>485){

        $cobra_table_name = $rpt_period . "_cost";
        $p6_table_name    = $rpt_period . "_p6_bl_labor";
        $sql = "
        select 
            p6.ca,
            p6.wp,
            p6.bl_labor p6_labor,
            cob.bac cobra_labor
        from bl_validation.`$p6_table_name` p6
        LEFT join cost2.`$cobra_table_name` cob
        on p6.ca=cob.ca and p6.wp = cob.wp
        where p6.ship_code=$ship_code and cob.wp like '%matl%'group by p6.ca,p6.wp
        ";
        //print $sql;
        $rs = dbCall($sql,$schema);
        $data_table.= returnTableFromRS($rs, "Budgeted Material Cost");
        /*Labor*/
        $sql = "
        select 
            p6.ca,
            p6.wp,
            p6.bl_labor p6_labor,
            cob.bac_hours cobra_labor
        from bl_validation.`$p6_table_name` p6
        LEFT join cost2.`$cobra_table_name` cob
        on p6.ca=cob.ca and p6.wp = cob.wp
        where p6.ship_code=$ship_code and cob.wp not like '%matl%'group by p6.ca,p6.wp
      ";
        $rs = dbCall($sql,$schema);
        $data_table.= returnTableFromRS($rs, "Budgeted Labor Units");
        return $data_table;
    }
    else{
        $cobra_table_name = $rpt_period . "_pcs_bl_labor";
        $p6_table_name    = $rpt_period . "_p6_bl_labor";
        $sql = "
        select 
            p6.ca,
            p6.wp,
            p6.bl_labor p6_labor,
            sum(cob.bl_labor) cobra_labor
        from bl_validation.`$p6_table_name` p6
        LEFT join bl_validation.`$cobra_table_name` cob
        on p6.ca=cob.ca and p6.wp = cob.wp
        where p6.ship_code=$ship_code group by p6.ca,p6.wp
        ";

        $rs = dbCall($sql,"bl_validation");
        $data_table = returnTableFromRS($rs, "Budgeted Labor Units");
        return $data_table;
    }

}
function returnTPDataTableFromRs($rs, $rpt_period){
    $data_table = "<table class = 'table table-sm'>
            <tr class = 'table_headers'><th colspan = 6>Validate Timephase</th></tr>
            <tr class = 'table_headers'>
                <th >RPT Period</th>
                <th>CA</th>
                <th>WP</th>
                <th>P6</th>
                <th>Cobra</th>
                <th>DIFF</th>
            </tr>
";  $i=0;
    while (!$rs->EOF)
    {
        $ca          = $rs->fields["ca"];
        $wp          = $rs->fields["wp"];
        $date        = $rs->fields["date"];
        $p6_labor    = formatNumber4decNoComma($rs->fields["p6_labor"]);
        $cobra_labor = formatNumber4decNoComma($rs->fields["cobra_labor"]);
        $diff        = formatNumber4decNoComma($p6_labor - $cobra_labor);

        if($diff>.9 or $diff < -.9){
            if($date < $rpt_period){
                $data_table.="
            <tr class = 'table_data'>
                <td>$date</td>
                <td>$ca</td>
                <td>$wp</td>
                <td>$p6_labor</td>
                <td>$cobra_labor</td>
                <td>$diff</td>
            </tr>
            ";
                $i++;
            }
        }

        $rs->MoveNext();
    }
    if($i<1){
        $data_table.="
            <tr class = 'table_headers'>
                <td colspan='6'>There are no Records to Display</td>
            </tr>
            ";
    }
    $data_table.= "</table>";
    return $data_table;
}
function validateTPP6TP($schema, $rpt_period, $ship_code){
    $cobra_table_name   = $rpt_period."_tp_check";
    $p6_table_name      = $rpt_period."_p6_tp_check";
    $sql = "
        select
            p6.ca,
            p6.date,
            p6.wp,
            sum(cob.val) cobra_labor,
            sum(p6.val) p6_labor
        from $schema.`$p6_table_name` p6
        LEFT JOIN $schema.`$cobra_table_name` cob
        on p6.ship_code = cob.ship_code and p6.ca=cob.ca and p6.wp = cob.wp and p6.date = cob.date
        where p6.ship_code=$ship_code group by p6.ca, p6.wp, p6.date order by p6.date
  ";
    //print $sql;
    $rs = dbCall($sql,$schema);
    $data_table = returnTPDataTableFromRs($rs, $rpt_period);
    return $data_table;
}

function validateHistoryCheck($schema, $prev_rpt_period,$cur_rpt_period, $ship_code){
    $prev_table_name   = $prev_rpt_period."_hc_check";
    $cur_table_name   = $cur_rpt_period."_hc_check";
    $sql = "
        select
          cur.ship_code,
          cur.type `type`,
          cur.cost_set cost_set,
          cur.date `date`,
          sum(cur.val) cur,
          sum(prev.val) prev
        from `$cur_table_name` cur
          inner join `$prev_table_name` prev
            on cur.date = prev.date AND
            cur.cost_set = prev.cost_set AND
            cur.type = prev.type
            and cur.ship_code = prev.ship_code
        where cur.ship_code = $ship_code
          group by cur.date, cur.type, cur.cost_set
        order by cur.date

  ";
    $rs = dbCall($sql,$schema);
    $data_table = "
        <table class = 'table table-sm'>
            <tr class = 'table_headers'><th colspan = 6>History Check</th></tr>
            <tr class = 'table_headers'>
                <th>Type</th>
                <th>RPT Period</th>
                <th>Cost Set</th>
                <th>Cur</th>
                <th>Prev</th>
                <th>DIFF</th>
            </tr>
";  $i=0;
    while (!$rs->EOF)
    {
        $type     = $rs->fields["type"];
        $cost_set = $rs->fields["cost_set"];
        $date     = $rs->fields["date"];
        $cur      = formatNumber4decNoComma($rs->fields["cur"]);
        $prev     = formatNumber4decNoComma($rs->fields["prev"]);
        $diff     = formatNumber4decNoComma($cur - $prev);

        if($diff!=0 and $date<=$cur_rpt_period and $cost_set=="Budget"){
            $data_table.="
            <tr class = 'table_data'>
                <td>$type</td>
                <td>$date</td>
                <td>$cost_set</td>
                <td>".formatNumber($cur)."</td>
                <td>".formatNumber($prev)."</td>
                <td>".formatNumber($diff)."</td>
            </tr>
            ";
            $i++;
        }

        $rs->MoveNext();
    }
    if($i<1){
        $data_table.="
            <tr class = 'table_data'>
                <td colspan='6' class='table_headers'>There are no Records to Display</td>
            </tr>
            ";
    }
    $data_table.= "</table>";
    return $data_table;
}
function loadTimePhaseFutureCheckNoCobra($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util){
    $table_name   = $rpt_period."_tp_check";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_tp_check", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code,ca, wp,date, val) values ";

    $new_csv_file_name = $ship_code."tp_check";
    $path2_source_xls = $path2xlsfile."/$time_phased_file_name";

    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);
    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_bl_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    $i=0;
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ca         = addslashes(trim($data[0]));
        $wp         = addslashes(trim($data[1]));
        $date       = addslashes(trim($data[4]));
        $rpt_period = createRPTfromDateSlash($date);
        $val        = formatNumber4decNoComma($data[5]);

        $sql.=
            "(
                $ship_code,
                '$ca',
                '$wp',
                $rpt_period,
                $val
            ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, $schema);
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

        $junk = dbCall($sql, $schema);
    }
}
function loadTimePhaseFutureCheckNoCobraMaterialOnly($rpt_period, $schema, $ship_code, $path2_destination, $path2xlsfile, $g_path_to_util){
    $table_name   = $rpt_period."_tp_check";
    $insert_sql = "insert into $schema.$table_name (ship_code,ca, wp,date, val) values ";

    $new_csv_file_name = $ship_code."tp_check_material";
    $path2_source_xls = $path2xlsfile."/Time-phasedMaterialCHK_Material and ODC.xls";

    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);
    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_bl_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    $i=0;
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ca         = addslashes(trim($data[0]));
        $wp         = addslashes(trim($data[1]));
        $date       = addslashes(trim($data[3]));
        $rpt_period = createRPTfromDateSlash($date);
        $val        = formatNumber4decNoComma(trim($data[4]));

        $sql.=
            "(
                $ship_code,
                '$ca',
                '$wp',
                $rpt_period,
                $val
            ),";
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, $schema);
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

        $junk = dbCall($sql, $schema);
    }
}

