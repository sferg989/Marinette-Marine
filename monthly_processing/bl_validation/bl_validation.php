<?php
include("../../inc/inc.php");
include("../../inc/inc.PHPExcel.php");

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;
$schema = "bl_validation";
function checkDateValues($start, $finish){
    $test = true;
    if($start=="0000-00-00" and $finish=="0000-00-00"){
        $test = true;
        return $test;
    }
    if($start=="0000-00-00" and $finish!="0000-00-00"){

        $test = false;
        return $test;
    }
    if($start!="0000-00-00" and $finish=="0000-00-00"){
        $test = false;
        return $test;
    }
    return $test;
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
$hc_file_name          = "04-09 LM Reconcile.xls";
$time_phased_file_name = "Time-phased Future Chk_Labor Only.xls";

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
        print $sql;
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }
}

if($control=="load_p6_time"){

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
        print $sql;
        $junk = dbCall($sql,$schema);
        $sql = $insert_sql;
    }
}
if($control=="load_pcs_bl_data"){

    $table_name   = $rpt_period . "_pcs_bl_labor";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_p6_bl_labor", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code, ca, wp, type, bl_labor) values ";

    $batch_rpt_name = "csv".$ship_code."BLValid";
    //runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);

    $new_csv_file_name = $ship_code."pcs_bl_validation";
    $path2_source_xls = $path2xlsfile."/$pcs_bl_file_name";

    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);
    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_bl_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ca       = addslashes(trim($data[0]));
        $wp       = addslashes(trim($data[1]));
        $type     = addslashes(trim($data[2]));
        $bl_labor = formatNumber4decNoComma($data[5]);
        $proj     = intval($proj);

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
        print $sql;

        $junk = dbCall($sql, $schema);
    }
}
if($control=="p6Data_check"){

}
if($control=="cobra_data_check"){

}
if($control=="load_cobra_data"){

}
if($control=="p6_bl_validation"){


}
if($control=="time_phased_budget_check"){


}
if($control=="history_check"){


}

