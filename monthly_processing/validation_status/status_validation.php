<?php
include("../../inc/inc.php");
include("inc.status_validation.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;
$schema = "status_validation";
function findBudgetValuesByColumnIndex($ship_code, $lines, $col_index,$col_title, $schema,$table_name){
    foreach ($lines as $key=>$value) {
        $fields   = explode("	", $value);
        $ca       = addslashes(trim($fields[0]));
        $wp       = addslashes(trim($fields[1]));
        $val    = intval($fields[$col_index]);
        if($val!=0){
            insertTimephasedRecord($ship_code,$ca,$wp,$val,$col_title, $schema,$table_name);
        }
    }
}

function insertTimephasedRecord($ship_code,$ca,$wp,$s_labor_units,$date,$schema,$table_name){
    $insert_sql = "
        insert into $table_name 
            (ship_code, ca, wp, date, val) 
            values($ship_code,'$ca','$wp',$date,$s_labor_units)
        ";
    $junk = dbCall($insert_sql,$schema);
    //print $insert_sql;

}
if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$ship_name       = getProjectNameFromCode($ship_code);
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
$data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);

$prev_year          = $data["prev_year"];
$cur_year           = $data["cur_year"];
$prev_year_last2    = $data["prev_year_last2"];
$cur_year_last2     = $data["cur_year_last2"];
$prev_month         = $data["prev_month"];
$cur_month          = $data["cur_month"];
$prev_month_letters = $data["prev_month_letters"];
$cur_month_letters  = $data["cur_month_letters"];
$ship_name          = $data["ship_name"];

$path2_cobra_dir       = $base_path . "" . $ship_name . "/" . $ship_code;
$path2_destination     = "../../util/csv_status_validation";
$path2xlsfile          = $base_path . "" . $ship_name . "/" . $ship_code . "/csv_reports/status_validation/" . $ship_code;

$pcs_status_file_name      = "spfStatusReconcile_Labor Only.xls";
$time_phased_file_name     = "spfTimePhaseFutureETC_Labor Only.xls";

if($control=="load_p6_status_data"){

    $table_name   = $rpt_period . "_p6_status_labor";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_p6_status_labor", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code, ca, wp, val, cost_set) values ";

    $p6data = trim($p6data);
    $lines = explode("\n",$p6data);
    $sql = $insert_sql;
    $i = 0;
    foreach ($lines as $key=>$value){
        if($key ==0){
            continue;
        }
        $fields = explode("	", $value);
        $ca     = addslashes(trim($fields[0]));
        $wp     = addslashes(trim($fields[1]));
        $p      = formatNumber4decNoComma($fields[2]);
        $a      = formatNumber4decNoComma($fields[3]);
        $etc    = formatNumber4decNoComma($fields[4]);
        $eac    = formatNumber4decNoComma($a + $etc);

        $ship_code = intval($ship_code);
        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$a', 
            'Actuals'
        ),";
        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$eac', 
            'EAC'
        ),";
        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$p', 
            'Earned'
        ),";
        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$etc', 
            'ETC'
        ),";
            if($i==500){
                $sql = substr($sql, 0, -1);
                $junk = dbCall($sql,$schema);
                $sql = $insert_sql;
                $i=0;
            }
        $i++;
    }
    if($i!=500){
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }
}
if($control=="load_p6_time"){

    $table_name   = $rpt_period . "_p6_tp_check";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_p6_tp_check", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);

    $p6data = trim($p6data);
    $lines = explode("\n",$p6data);
    /*
     * lines is the row.
     * fields = each row is an array
     * */
    $field_names   = explode("	", $lines[0]);
    $count = count($field_names);
    //print "this is the $count <br>";
    for ($i=4;$i<=$count;$i++){
        $col_title = $field_names[$i];

        $date_array = explode("-", $col_title);
        $result     = array_filter($date_array);
        $result     = array_values($result);

        $month = threeLetterMonth2Number($result[0]);
        $year  = "20" . $result[1];

        $rpt_period = $year."".$month;

        findBudgetValuesByColumnIndex($ship_code, $lines, $i,$rpt_period, $schema, $table_name);
    }
    die("made it");
}
if($control=="load_cobra_data"){

    $batch_rpt_name = "csv".$ship_code."StatusValid";
    runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);
    loadPCSStatus($rpt_period, $schema, $ship_code, $pcs_status_file_name, $path2_destination, $path2xlsfile, $g_path_to_util);
    loadTimePhaseETC($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util);
}
if($control=="status_valid_check"){
    $status_labor_table = "";
    $cost_set_list[] = "Actuals";
    $cost_set_list[] = "EAC";
    $cost_set_list[] = "Earned Value";
    $cost_set_list[] = "ETC";


    foreach ($cost_set_list as $value){
        $status_labor_table.= validatePCS2P6StatusLabor($schema, $rpt_period, $ship_code, $value);
    }
    $tp_data_table      = validateTPP6TP($schema, $rpt_period, $ship_code);
    die($status_labor_table."<>".$tp_data_table);
}
if($control=="data_check"){

    $table_array["p6_pcs_table_name"]      = $rpt_period . "_p6_status_labor";
    $table_array["p6_tp_table_name"]       = $rpt_period . "_p6_tp_check";
    $table_array["tp_cobra_table_name"]    = $rpt_period . "_tp_check";
    $table_array["pcs_cobra_table_name"]   = $rpt_period . "_pcs_status_labor";

    $msg = "";
    foreach ($table_array as $key=>$table_name){
        $create_table = checkIfTableExists($schema, $table_name);
        if($create_table==true)
        {
            $sql = "select count(*) as count from $table_name where ship_code = $ship_code";
            //print $sql;
            $rs = dbCall($sql,$schema);
            $count = $rs->fields["count"];
            if($count<1){
                if(strpos($key, "cobra")>0){
                    $msg = "Please Load Cobra Data!";
                }else{
                    $msg = "Please Load P6 Data!";
                }
            }
        }
        else{
            $msg= "Please Load Data";
        }
    }
    die($msg);
}
