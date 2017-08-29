<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/15/2017
 * Time: 9:01 AM
 */
include("../../inc/inc.PHPExcel.php");

function loadPCSStatus($rpt_period, $schema, $ship_code, $pcs_bl_file_name, $path2_destination, $path2xlsfile, $g_path_to_util){

    $table_name   = $rpt_period."_pcs_status_labor";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_pcs_status_labor", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code, ca, wp, type, val, cost_set) values ";

    $new_csv_file_name = $ship_code."pcs_status_validation";
    $path2_source_xls = $path2xlsfile."/$pcs_bl_file_name";
    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);

    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_status_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    $i=0;
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ca         = addslashes(trim($data[0]));
        $wp         = addslashes(trim($data[1]));
        $type       = addslashes(trim($data[2]));
        $cost_set   = addslashes(trim($data[3]));
        $val        = formatNumber4decNoComma($data[5]);

        $sql.=
            "(
                $ship_code,
                '$ca',
                '$wp',
                '$type',
                $val,
                '$cost_set'
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
function loadTimePhaseETC($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util){
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
    $real_path2_new_CSV = $g_path_to_util."\\csv_status_validation\\".$new_csv_file_name.".csv";
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
            //print $sql;
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
        //print $sql;
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, $schema);
    }
}

function validatePCS2P6StatusLabor($schema, $rpt_period, $ship_code, $cost_set){
    $cobra_table_name   = $rpt_period."_pcs_status_labor";
    $p6_table_name      = $rpt_period."_p6_status_labor";
    $sql = "
        select
          p6.cost_set,
          p6.ca,
          p6.wp,
          p6.val as p6val,
          sum(cobra.val) as cobraval
        from status_validation.$p6_table_name p6
        left JOIN status_validation.$cobra_table_name cobra
            on p6.ca = cobra.ca and p6.wp = cobra.wp and p6.cost_set = cobra.cost_set
        where p6.ship_code= $ship_code and p6.cost_set = '$cost_set' group by p6.ca, p6.wp
  ";

    $rs = dbCall($sql,$schema);
    //print $sql;
    $data_table = "<table class = 'table table-sm'>
            <tr ><th colspan = 6 class = 'table_headers'>$cost_set Differences</th></tr>
            <tr class = 'table_headers'>
                <th>Cost Set</th>
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
        $cost_set    = $rs->fields["cost_set"];
        $ca    = $rs->fields["ca"];
        $wp    = $rs->fields["wp"];

        $p6    = formatNumber4decNoComma($rs->fields["p6val"]);
        $cobra = formatNumber4decNoComma($rs->fields["cobraval"]);
        $diff  = formatNumber4decNoComma($p6 - $cobra);

        if($diff>.5){
            $data_table.="
            <tr align=\"center\" class = 'table_data'>
                <td>$cost_set</td>
                <td>$ca</td>
                <td>$wp</td>
                <td>".formatNumber($p6)."</td>
                <td>".formatNumber($cobra)."</td>
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
                <td colspan='6' class = 'table_headers'>There are no Records to Display</td>
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
            cob.val cobra_labor,
            p6.val p6_labor
        from $schema.`$p6_table_name` p6
        LEFT JOIN $schema.`$cobra_table_name` cob
        on p6.ca=cob.ca and p6.wp = cob.wp and p6.date = cob.date
        where p6.ship_code=$ship_code order by p6.date
  ";
    $rs = dbCall($sql,$schema);
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
        $rpt_period  = $rs->fields["date"];
        $p6_labor    = formatNumber4decNoComma($rs->fields["p6_labor"]);
        $cobra_labor = formatNumber4decNoComma($rs->fields["cobra_labor"]);
        $diff        = formatNumber4decNoComma($p6_labor - $cobra_labor);

        if($diff>.5){
            $data_table.="
            <tr class = 'table_data'>
                <td>$rpt_period</td>
                <td>$ca</td>
                <td>$wp</td>
                <td>".formatNumber($p6_labor)."</td>
                <td>".formatNumber($cobra_labor)."</td>
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
                <td colspan='6' class = 'table_headers'>There are no Records to Display</td>
            </tr>
            ";
    }
    $data_table.= "</table>";
    return $data_table;
}