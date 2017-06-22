<?php
include('../../../inc/inc.php');
include('../../../inc/inc.PHPExcel.php');
include('inc.cost_loader.php');
$debug     = false;

if($control=="project_grid")
{
    $data = "[";
    $sql = "select id, name, code from fmm_evms.master_project order by code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $pmid = $rs->fields["id"];
        $name = $rs->fields["name"];
        $code = $rs->fields["code"];
        $data.="{
            \"id\"          :$pmid,
            \"name\"        :\"$name\",
            \"ship_code\"   :\"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="rpt_periods")
{
    $cur_rpt_period = currentRPTPeriod();
    $data = "[";
    $sql = "select id, rpt_period from fmm_evms.calendar rpt where rpt_period <= $cur_rpt_period order by rpt_period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $id         = $rs->fields["id"];
        $rpt_period = $rs->fields["rpt_period"];
        $data.="{
            \"id\"          :$id,
            \"rpt_period\"        :$rpt_period
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="load_cobra_data"){
    $rpt_period_array = explode(",", $rpt_period_list);
    if(strlen($code)==3)
    {
        $ship_code = "0".$code;
    }
    foreach ($rpt_period_array as $rpt_period){
        $year = intval(substr($rpt_period, 2, 2));
        $month = month2digit(substr($rpt_period, -2));
        $ship_code_wc = "$ship_code$month$year";

        $schema = "cost2";
        $cur_rpt_period = currentRPTPeriod();
        if($rpt_period==$cur_rpt_period){
            $ship_code_wc = $ship_code;
            if($ship_code=="0471"){
                $ship_code_wc = "0471-";
            }
        }
        if($rpt_period>$cur_rpt_period){
            return false;
        }
        insertCobraCostData($code, $schema, $rpt_period,$ship_code_wc);
    }
}
if($control=="load_cur_period_tp"){
    if(strlen($code)==3)
    {
        $ship_code = "0".$code;
    }
    $ship_name = getProjectNameFromCode($ship_code);

    $rpt_period = currentRPTPeriod();
    $path2_cobra_dir       = $base_path . "" . $ship_name . "/" . $ship_code;
    $path2_destination     = "../../../util/csv_bl_validation";
    $path2xlsfile          = $base_path . "" . $ship_name . "/" . $ship_code . "/csv_reports/bl_validation/" . $ship_code;
    $schema = "bl_validation";
    $time_phased_file_name = "spfTimePhaseFutureChK_Labor Only.xls";
    loadTimePhaseFutureCheck($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug);
    die(" made it".$ship_code);

}