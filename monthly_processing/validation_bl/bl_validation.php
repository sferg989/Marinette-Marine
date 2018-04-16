<?php
include("../../inc/inc.php");
include("inc.bl_validation.php");
include("../../inc/inc.PHPExcel.php");
include("../../inc/inc.cobra.php");

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
        $val      = formatNumber4decNoComma(trim($fields[$col_index]));
        //print $col_title." ".$fields[$col_index]." Col Index ".$col_index." \r";
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
    $insert_sql = "insert into $schema.$table_name (ship_code, ca, wp, bl_labor) values ";

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
        if($ship_code>=477 and $ship_code<=485){
            $val = formatNumber4decNoComma(trim($fields[2]));
        }
        else{
            $type = stripos($wp, "MATL");
            if($type===false){
                $val= formatNumber4decNoComma(trim($fields[2]));
            }else{
                $val= formatNumber4decNoComma(trim($fields[3]));
            }
        }

        $ship_code = intval($ship_code);
        $sql.="(
            $ship_code,
            '$ca',
            '$wp',
            $val
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
    $field_names    = explode("	", $lines[0]);
    $count          = count($field_names);
    //print "this is the $count <br>";
    $col_count = 4;
    if($ship_code<477 or $ship_code>485){
        $col_count = 5;
    }
    for ($i=$col_count;$i<=$count;$i++){
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

    $batch_rpt_name = "csv".$ship_code."BLValid";
    runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);

    if($ship_code<477 or $ship_code>485){
        $table_name   = $rpt_period . "_cost";
        $create_table = checkIfTableExists("cost2", $table_name);
        if($create_table== "create_table"){
            createTableFromBase("cost2","template_cost", $table_name);
        }
        deleteShipFromTable($ship_code,$table_name, $schema);
        insertCobraCostData($ship_code, "cost2", $rpt_period);
    }
    else{
        loadPCSBL($rpt_period, $schema, $ship_code, $pcs_bl_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug);
    }
    loadTimePhaseFutureCheckNoCobra($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util);
    loadHistoryCheck($rpt_period, $schema, $ship_code, $hc_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug);
    if($ship_code<477 or $ship_code>485){
        loadTimePhaseFutureCheckNoCobraMaterialOnly($rpt_period, $schema, $ship_code, $path2_destination, $path2xlsfile, $g_path_to_util);
    }
}
if($control=="bl_valid_check"){
    $bl_labor_table = validatePCS2P6BLLabor($schema, $rpt_period, $ship_code);
    $tp_data_table  = validateTPP6TP($schema, $rpt_period, $ship_code);
    $hc_data_table  = validateHistoryCheck($schema, $prev_rpt_period, $rpt_period, $ship_code);
    die($bl_labor_table."<>".$tp_data_table."<>".$hc_data_table);

}
if($control=="data_check"){
    $baseline = "false";
    $bcr      = "false";
    $table_array["p6_pcs_table_name"]      = $rpt_period . "_p6_bl_labor";
    $table_array["p6_tp_table_name"]       = $rpt_period . "_p6_tp_check";
    $table_array["tp_cobra_table_name"]    = $rpt_period . "_tp_check";
    $table_array["hc_cobra_table_name"]    = $rpt_period . "_hc_check";
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

