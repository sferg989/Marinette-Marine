<?php
include("../../inc/inc.php");
include("inc.bl_validation.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;
$schema = "bl_validation";
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
function threeLetterMonth2Number($month){
    //print $month."<br>";
    $date_array["Jan"] = "01";
    $date_array["Feb"] = "02";
    $date_array["Mar"] = "03";
    $date_array["Apr"] = "04";
    $date_array["May"] = "05";
    $date_array["Jun"] = "06";
    $date_array["Jul"] = "07";
    $date_array["Aug"] = "08";
    $date_array["Sep"] = "09";
    $date_array["Oct"] = "10";
    $date_array["Nov"] = "11";
    $date_array["Dec"] = "12";

    return $date_array[$month];
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
$path2_destination     = "../../util/csv_bl_validation";
$path2xlsfile          = $base_path . "" . $ship_name . "/" . $ship_code . "/csv_reports/bl_validation/" . $ship_code;

$pcs_bl_file_name      = "csvReconcileProjectCos_Labor Only.xls";
$time_phased_file_name = "spfTimePhaseFutureChK_Labor Only.xls";
$hc_file_name          = "04-09 LM Reconcile.xls";

if($control=="load_p6_bl_data"){

    $table_name   = $rpt_period . "_p6_bl_labor";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_p6_bl_labor", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code, ca, wp, bl_labor, date) values ";

    $p6data = trim($p6data);
    $lines = explode("\n",$p6data);
    $sql = $insert_sql;
    $i = 0;
    foreach ($lines as $key=>$value){
        if($key ==0){
            continue;
        }
        $fields   = explode("	", $value);
        $ca       = addslashes(trim($fields[0]));
        $wp       = addslashes(trim($fields[1]));
        $bl_labor = formatNumber4decNoComma($fields[2]);

        $ship_code = intval($ship_code);
        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            '$bl_labor'
        ),";
            if($i==500){
                $sql = substr($sql, 0, -1);
                $junk = dbCall($sql,$schema);
                $sql = $insert_sql;
                $i=0;
            }
        $i++;
    }
    if($i!=200){
        $sql = substr($sql, 0, -1);
        //print $sql;
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
        $result = array_filter($date_array);
        $result = array_values($result);

        $month = threeLetterMonth2Number($result[0]);
        $year  = "20" . $result[1];

        $rpt_period = $year."".$month;

        findBudgetValuesByColumnIndex($ship_code, $lines, $i,$rpt_period, $schema, $table_name);
    }
    die("made it");
}
if($control=="load_cobra_data"){
    loadPCSBL($rpt_period, $schema, $ship_code, $pcs_bl_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug);
    loadTimePhaseFutureCheck($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug);
    loadHistoryCheck($rpt_period, $schema, $ship_code, $hc_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug);
}
if($control=="bl_valid_check"){
    $bl_labor_table = validatePCS2P6BLLabor($schema, $rpt_period, $ship_code);
    $tp_data_table  = validateTPP6TP($schema, $rpt_period, $ship_code);
    $hc_data_table  = validateHistoryCheck($schema, $prev_rpt_period, $rpt_period, $ship_code);
    die($bl_labor_table."<>".$tp_data_table."<>".$hc_data_table);

}

