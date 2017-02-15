<?php
include("../../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$debug = false;
$schema = "bac_eac";
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
$prev_full_month    = $data["prev_full_month"];
$cur_full_month     = $data["cur_full_month"];
$ship_name          = $data["ship_name"];
//0485
//$g_path2_bac_eac_reports = $base_path.$ship_name."/BAC-EAC Compare/".$ship_code."/";
//everything else
$g_path2_bac_eac_reports = $base_path.$ship_name."/".$ship_code."/EAC-BAC Compare/".$ship_code."/";

$cpr_file_array = array();

if($control =="load_data") {
    //$batch_rpt_name = $ship_code." Class";

    $batch_rpt_name = "csv".$ship_code."BACEAC";
    //runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);

    $ship_code = intval($ship_code);
    if($ship_code>=477)
    {
        $cpr_file_array["02-01 FY14AF CPR 1 LBR_Labor Only"]       = "_cpr1l";
        $cpr_file_array["02-01 FY14AF CPR 1 MAT_Material and ODC"] = "_cpr1m";
        $cpr_file_array["02-01 FY14AF CPR1 DOL"]                   = "_cpr1d";
        $cpr_file_array["02-01 FY14AF CPR1 HRS"]                   = "_cpr1h";
        $cpr_file_array["02-02 FY14AF CPR2D WBS"]                  = "_cpr2d_wbs";
        $cpr_file_array["02-02FY14AFCPR2DOBSBAT"]                  = "_cpr2d_obs";
        $cpr_file_array["02-02 FY14AF CPR2H OBS"]                  = "_cpr2h_obs";
        $cpr_file_array["02-02 FY14AF CPR2H WBS"]                  = "_cpr2h_wbs";
        $cpr_file_array["02-02 FY14AF CPR2L OBS_Labor Only"]       = "_cpr2l_obs";
        $cpr_file_array["02-02 FY14AF CPR2L WBS_Labor Only"]       = "_cpr2l_wbs";
        $cpr_file_array["02-02 FY14AF CPR2M OBS_Material and ODC"] = "_cpr2m_obs";
        $cpr_file_array["02-02 FY14AF CPR2M WBS_Material and ODC"] = "_cpr2m_wbs";
        $cpr_file_array["02-02H CPR 2 OutSource_Outsource Only"]   = "_cpr2o";
    }
    else{
        $cpr_file_array["02-02H CPR 2 OutSource"]                 = "_pre17_cpr2o";
        $cpr_file_array["02-02M CPR 2 Material_Material and ODC"] = "_pre17_cpr2m";
        $cpr_file_array["02-02L CPR 2 Labor_Labor Only"]          = "_pre17_cpr2l";
        $cpr_file_array["02-02D CPR 2 Dollars"]                   = "_pre17_cpr2d";
        $cpr_file_array["02-02H CPR 2 Hours"]                     = "_pre17_cpr2h";
        $cpr_file_array["02-01M CPR 1 Material_Material and ODC"] = "_pre17_cpr1m";
        $cpr_file_array["02-01L CPR 1 Labor_Labor Only"]          = "_pre17_cpr1l";
        $cpr_file_array["02-01H CPR 1 Hours"]                     = "_pre17_cpr1h";
        $cpr_file_array["02-01D CPR 1 Dollars"]                   = "_pre17_cpr1d";
        $cpr_file_array["02-02D CPR 2 Dollars_Outsource Only"]    = "_pre17_cpr2do";
    }
    if(strlen($code)==3)
    {
        $ship_code = "0".$code;
    }

    foreach ($cpr_file_array as $file_name => $table_name_short_name) {

        if($table_name_short_name=="_cpr2o" or $table_name_short_name=="_pre17_cpr2o"){
            $data_type_field = "data_type,";
        }
        else{
            $data_type_field = "";
            $data_type_value = "";
        }
        $have_we_reached_item = false;
        $table_name           = $rpt_period . $table_name_short_name;
        $create_table         = checkIfTableExists($schema, $table_name);

        if ($create_table == "create_table") {
            createTableFromBase($schema, "template" . $table_name_short_name, $table_name);
            sleep(.5);
        }
        deleteShipFromTable($ship_code, $table_name, $schema);
        $path2_input = $g_path2_bac_eac_reports . $file_name . ".csv";
        if(file_exists ($path2_input)==false){
            print "could not find $path2_input\r";
            continue;
        }
        $handle     = fopen($path2_input, "r");

        $insert_sql = "
            insert into $schema.$table_name (
                ship_code,
                item,
                $data_type_field
                s_cur,
                p_cur,
                a_cur,
                sv_cur,
                cv_cur,
                s_cum,
                p_cum,
                a_cum,
                sv_cum,
                cv_cum,
                adj_cv,
                adj_sv,
                adj_s,
                s_vac,
                est_vac,
                vac) 
                values
            ";
        //loop through the csv file and insert into database
        $sql = $insert_sql;
        /*create counter so insert 1000 rows at a time.*/

        $i = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $item       = trim($data[0]);
            $s_cur      = removeCommanDollarSignParan($data[2]);
            $p_cur      = removeCommanDollarSignParan($data[3]);
            $a_cur      = removeCommanDollarSignParan($data[4]);
            $sv_cur     = removeCommanDollarSignParan($data[5]);
            $cv_cur     = removeCommanDollarSignParan($data[6]);
            $s_cum      = removeCommanDollarSignParan($data[7]);
            $p_cum      = removeCommanDollarSignParan($data[8]);
            $a_cum      = removeCommanDollarSignParan($data[9]);
            $sv_cum     = removeCommanDollarSignParan($data[11]);
            $cv_cum     = removeCommanDollarSignParan($data[13]);
            $adj_cv     = removeCommanDollarSignParan($data[14]);
            $adj_sv     = removeCommanDollarSignParan($data[14]);
            $adj_s      = removeCommanDollarSignParan($data[16]);
            $s_vac      = removeCommanDollarSignParan($data[17]);
            $est_vac    = removeCommanDollarSignParan($data[18]);
            $vac        = removeCommanDollarSignParan($data[19]);

            $total_test = strpos($item, "TOTAL");
            if ($item == "ITEM") {
                $i++;
                $have_we_reached_item = true;
                continue;
            }
            if ($have_we_reached_item == false) {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "(1)") {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "DOLLARS" and $table_name_short_name=="_cpr2o") {
                print "we made it! Dollars";
                $data_type = "dollars";
                $data_type_value = "'".$data_type."',";

                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "HOURS") {
                if($table_name_short_name=="_cpr2o" or $table_name_short_name=="_pre17_cpr2o"){
                    $data_type = "hours";
                    /*pre 17 outsource and MMC are co mingled, so the first number is MMC,
                    the 2nd is OUTsouce*/
                    if($table_name_short_name=="_pre17_cpr2o")  {
                        $data_type = "MMC";
                    }
                    $data_type_value = "'".$data_type."',";
                }
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "LABOR Labor") {
                $i++;
                continue;
            }
            if ($have_we_reached_item = true and $item == "OUT") {
                if($table_name_short_name=="_pre17_cpr2o")  {
                    $data_type = "OUT";
                }
                $data_type_value = "'".$data_type."',";                $i++;
                continue;
            }
            if ($total_test !== false) {
                $i++;
                continue;
            }
            if ($item == "") {
                $i++;
                continue;
            }
            //print "this is the $item and the count $i\r";
            $sql .=
                "(
                $ship_code,
                    '$item',
                    $data_type_value
                    $s_cur,
                    $p_cur,
                    $a_cur,
                    $sv_cur,
                    $cv_cur,             
                    $s_cum,
                    $p_cum,
                    $a_cum,
                    $sv_cum,
                    $cv_cum,
                    $adj_cv,
                    $adj_sv,
                    $adj_s,
                    $s_vac,
                    $est_vac,
                    $vac
                ),";
            $i++;
        }
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, $schema);

    }
    die();
}
