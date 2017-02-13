<?php
include("../../inc/inc.php");
include("../../inc/inc.bac_eac.php");
include("change_summary.php");


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
$g_path2_bac_eac_reports = $base_path.$ship_name."/BAC-EAC Compare/".$ship_code."/";
$cpr_file_array = array();

if($control=="beac_eac_detail_chart"){
    $table_headers = "
<table class=\"table table-sm \">
    <tr>
        <td>BAC EAC Comparison</td>
        <td  colspan=\"8\">Estimate at Complete</td>
    </tr>
    <tr>
        <td>Month End</td>
        <td colspan=\"4\">Hours</td>
        <td colspan=\"4\">Dollars</td>             
    </tr>
    <tr>
        <td>By Oragization</td>
        <td>$cur_full_month</td>
        <td>$prev_full_month</td>
        <td>Diff</td>
        <td>% Change</td>            
        <td>$cur_full_month</td>
        <td>$prev_full_month</td>
        <td>Diff</td>
        <td>% Change</td>
       
    </tr>    

    ";

    $table_array = returnTableArray($ship_code);

    foreach ($table_array as $key =>$value) {
        $html.= "$table_headers";
        $eac_data = getBACEACData($rpt_period, $prev_rpt_period, $ship_code,$value, "est_vac");

        foreach ($eac_data as $item=>$value){

            $html.="
            <tr>
                <td>$item</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"]-$value["prev_est_vach"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vach"]-$value["prev_est_vach"])/$value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"]-$value["prev_est_vacd"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vacd"]-$value["prev_est_vacd"])/$value["cur_est_vacd"])."</td>
                
            </tr>";
        }
        $html.= "<tr></tr><tr></tr></table>";
    }

    $table_headers = "
    <table class=\"table table-sm \">
        <tr>
            <td>BAC EAC Comparison</td>
            <td  colspan=\"8\">Budget at Complete</td>
        </tr>
        <tr>
            <td>Month End</td>
            <td colspan=\"4\">Hours</td>
            <td colspan=\"4\">Dollars</td>             
        </tr>
        <tr>
            <td>By Oragization</td>
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>
           
        </tr>    

    ";

    foreach ($table_array as $key =>$value) {
        $html.= "$table_headers";
        $eac_data = getBACEACData($rpt_period, $prev_rpt_period, $ship_code,$value, "s_vac");
        //var_dump($eac_data);
        foreach ($eac_data as $item=>$value){
            $html.="
            <tr>
                <td>$item</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"]-$value["prev_est_vach"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vach"]-$value["prev_est_vach"])/$value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"]-$value["prev_est_vacd"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vacd"]-$value["prev_est_vacd"])/$value["cur_est_vacd"])."</td>
                
            </tr>";
        }
        $html.= "<tr></tr><tr></tr></table>";
    }
    $table_headers = "
    <table class=\"table table-sm \">
        <tr>
            <td>BAC EAC Comparison</td>
            <td  colspan=\"8\">Actual Cost Increment</td>
        </tr>
        <tr>
            <td>Month End</td>
            <td colspan=\"4\">Hours</td>
            <td colspan=\"4\">Dollars</td>             
        </tr>
        <tr>
            <td>By Oragization</td>
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>
           
        </tr>    

    ";

    foreach ($table_array as $key =>$value) {
        $html.= "$table_headers";
        $eac_data = getBACEACData($rpt_period, $prev_rpt_period, $ship_code,$value, "a_cur");

        foreach ($eac_data as $item=>$value){
            $html.="
            <tr>
                <td>$item</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vach"]-$value["prev_est_vach"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vach"]-$value["prev_est_vach"])/$value["cur_est_vach"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["prev_est_vacd"])."</td>
                <td>".formatNumberPHPEXCEL($value["cur_est_vacd"]-$value["prev_est_vacd"])."</td>
                <td>".formatPercentPHPEXCEL(($value["cur_est_vacd"]-$value["prev_est_vacd"])/$value["prev_est_vacd"])."</td>
                
            </tr>";
        }
        $html.= "<tr></tr><tr></tr></table>";
    }
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."export.xls";
    $path = "../../util/excel_exports/".$token."export.xls";
    file_put_contents($path2_export,$html);

    /*BUILD SECOND  worksheet
    */
    $matl_html = "";
    $table_headers = "
    <table class=\"table table-sm \">
        <tr>
            <td>BAC EAC Comparison</td>
            <td  colspan=\"12\">ESTIMATE AT COMPLETE</td>
        </tr>
        <tr>
            <td>Month End</td>
            <td colspan=\"4\">LABOR DOLLARS</td>
            <td colspan=\"4\">MATERIAL Dollars</td>             
            <td colspan=\"4\">TOTAL Dollars</td>             
        </tr>
        <tr>
            <td>By Oragization</td>
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>            
            <td>$cur_full_month</td>
            <td>$prev_full_month</td>
            <td>Diff</td>
            <td>% Change</td>
        </tr>    

    ";
    $table_array = returnTableArraylABORMATLDOLLARS($ship_code);
    foreach ($table_array as $key =>$value) {
        $matl_html.= "$table_headers";
        $eac_data = getBACEACDataLABORMTL($rpt_period, $prev_rpt_period, $ship_code,$value, "est_vac");

        foreach ($eac_data as $item=>$value){
            $cur_labor_dollars  = formatNumberPHPEXCEL($value["cur_est_vacl"]);
            $prev_labor_dollars = formatNumberPHPEXCEL($value["prev_est_vacl"]);
            $labor_diff         = $cur_labor_dollars - $prev_labor_dollars;
            $labor_pc           = formatPercentPHPEXCEL($labor_diff / $cur_labor_dollars);
     
            $cur_matl_dollars  = formatNumberPHPEXCEL($value["cur_est_vacm"]);
            $prev_matl_dollars = formatNumberPHPEXCEL($value["prev_est_vacm"]);
            $matl_diff         = $cur_matl_dollars - $prev_matl_dollars;
            $matl_pc           = formatPercentPHPEXCEL($matl_diff / $cur_matl_dollars);

            $cur_td = $cur_labor_dollars + $cur_matl_dollars;
            $prev_td = $prev_labor_dollars + $prev_matl_dollars;
            $td_diff = $cur_td- $prev_td;
            $td_pc  = formatPercentPHPEXCEL($td_diff/$cur_td);
            $matl_html.="
            <tr>
                <td>$item</td>
                <td>$cur_labor_dollars</td>
                <td>$prev_labor_dollars</td>
                <td>$labor_diff</td>
                <td>$labor_pc</td>
                <td>$cur_matl_dollars</td>
                <td>$prev_matl_dollars</td>
                <td>$matl_diff</td>
                <td>$matl_pc</td>
                <td>$cur_td</td>
                <td>$prev_td</td>
                <td>$td_diff</td>
                <td>$td_pc</td>
                
            </tr>";
        }
        $matl_html.= "<tr></tr><tr></tr></table>";
    }
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."matl_export.xls";
    $path2matlandLabor = "../../util/excel_exports/".$token."matl_export.xls";
    file_put_contents($path2_export,$matl_html);

    /*3.  Make Summary Page

     * */

    $category_array[] = "Negotiated Cost";
    $category_array[] = "Target Profit";
    $category_array[] = "Target Price";
    $category_array[] = "Authorized Unpriced Work";
    $category_array[] = "EAC Best Case";
    $category_array[] = "EAC Worst Case";
    $category_array[] = "EAC Most Likely";
    $category_array[] = "Management Reserve";


    $summary_html = "<table>
        <tr>
            <th colspan='5'>Summary of Changes</th>    
        </tr>
        <tr>
            <td></td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>
    ";
    foreach ($category_array as $value){
        $summary_html.="
        <tr>
            <td>$value</td>    
            <td></td>    
            <td></td>    
            <td></td>    
            <td></td>    
        </tr>
        ";
    }
    $summary_html.= "<tr></tr>";
    $summary_html.="        
        <tr>
            <td>EAC</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    $hours_data = getTotalRow($prev_rpt_period, $rpt_period, "_cpr1h", $ship_code, "est_vac");
    $prev_eac_hours       = $hours_data["prev"];
    $cur_eac_hours        = $hours_data["cur"];
    $diff       = $cur_eac_hours-$prev_eac_hours;
    $pc         = $diff/$cur_eac_hours;
    $summary_html.="
        <tr>
            <td>Hours</td>    
            <td>$cur_eac_hours</td>    
            <td>$prev_eac_hours</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $labor_d = getSubTotalRow($prev_rpt_period,$rpt_period, "_cpr2l_wbs", $ship_code, "est_vac");
    $prev_ld       = $labor_d["prev"];
    $cur_ld        = $labor_d["cur"];
    $diff       = $cur_ld-$prev_ld;
    $pc         = $diff/$cur_ld;
    $summary_html.="
        <tr>
            <td>Labor Dollars</td>    
            <td>$cur_ld</td>    
            <td>$prev_ld</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $matl_d = getSubTotalRow($prev_rpt_period,$rpt_period, "_cpr2m_wbs", $ship_code, "est_vac");
    $prev_md       = $matl_d["prev"];
    $cur_md        = $matl_d["cur"];
    $diff       = $cur_md-$prev_md;
    $pc         = $diff/$cur_md;
    $summary_html.="
        <tr>
            <td>Material Dollars</td>    
            <td>$cur_md</td>    
            <td>$prev_md</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";

    $ub = getUB($prev_rpt_period,$rpt_period, "_cpr2d_obs", $ship_code, "est_vac");
    $prev_ub       = $ub["prev"];
    $cur_ub        = $ub["cur"];
    $diff       = $cur_ub-$prev_ub;
    $pc         = $diff/$cur_ub;
    $summary_html.="
        <tr>
            <td>Undistributed Budget</td>    
            <td>$cur_ub</td>    
            <td>$prev_ub</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $prev_eac_td = $prev_ld + $prev_md + $prev_ub;
    $cur_eac_td  = $cur_ld + $cur_md + $cur_ub;
    $diff    = $cur_eac_td - $prev_eac_td;
    $pc      = $diff / $cur_eac_td;
    $summary_html.="
        <tr>
            <td>Total Dollars</td>    
            <td>$cur_eac_td</td>    
            <td>$prev_eac_td</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        <tr></tr>
        ";

    /*
 * NEXT TABLE
 * NEXT TABLE
 * NEXT TABLE
 * NEXT TABLE
 * NEXT TABLE
 * */
    $summary_html.="        
        <tr>
            <td>BAC</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    $hours_data = getSubTotalRow($prev_rpt_period, $rpt_period, "_cpr1h", $ship_code, "s_vac");
    $prev_bac_hours       = $hours_data["prev"];
    $cur_bac_hours        = $hours_data["cur"];
    $diff       = $cur_bac_hours-$prev_bac_hours;
    $pc         = $diff/$cur_bac_hours;
    $summary_html.="
        <tr>
            <td>Hours</td>    
            <td>$cur_bac_hours</td>    
            <td>$prev_bac_hours</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $sub_total_d = getSubTotalRow($prev_rpt_period,$rpt_period, "_cpr1d", $ship_code, "s_vac");
    $prev_sub_d       = $sub_total_d["prev"];
    $cur_sub_d        = $sub_total_d["cur"];
    $diff       = $cur_sub_d-$prev_sub_d;
    $pc         = $diff/$cur_sub_d;
    $summary_html.="
        <tr>
            <td>Subtotal Dollars</td>    
            <td>$cur_sub_d</td>    
            <td>$prev_sub_d</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";

    $ub = getUB($prev_rpt_period,$rpt_period, "_cpr1d", $ship_code, "s_vac");
    $prev_ub       = $ub["prev"];
    $cur_ub        = $ub["cur"];
    $diff       = $cur_ub-$prev_ub;
    $pc         = $diff/$cur_ub;
    $summary_html.="
        <tr>
            <td>Undistributed Budget</td>    
            <td>$cur_ub</td>    
            <td>$prev_ub</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";

    $prev_bac_td = $prev_sub_d + $prev_ub;
    $cur_bac_td  = $cur_sub_d + $cur_ub;
    $diff    = $cur_bac_td - $prev_bac_td;
    $pc      = $diff / $cur_td;
    $summary_html.="
        <tr>
            <td>Total Dollars</td>    
            <td>$cur_bac_td</td>    
            <td>$prev_bac_td</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        <tr></tr>
        ";
    /*
 * NEXT TABLE
 * NEXT TABLE
 * NEXT TABLE
 * NEXT TABLE
 * NEXT TABLE
 * */
    $summary_html.="        
        <tr>
            <td>VAC</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    $cur_vac_hours  = $cur_bac_hours - $cur_eac_hours;
    $prev_vac_hours = $prev_bac_hours - $prev_eac_hours;
    $diff           = $cur_vac_hours - $prev_vac_hours;
    $pc             = $diff / $cur_vac_hours;
    $summary_html.="
        <tr>
            <td>HOURS</td>    
            <td>$cur_vac_hours</td>    
            <td>$prev_vac_hours</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $cur_vac_td  = $cur_bac_td - $cur_eac_td;
    $prev_vac_td = $prev_bac_td - $prev_eac_td;
    $diff        = $cur_vac_td - $prev_vac_td;
    $pc          = $diff / $cur_vac_td;
    $summary_html.="
        <tr>
            <td>Dollars</td>    
            <td>$cur_vac_td</td>    
            <td>$prev_vac_td</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr><tr></tr>
        ";
    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="        
        <tr>
            <td>ACWP Increment</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    $h_data   = getTotalRow($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cur");
    $prev_a_h = $h_data["prev"];
    $cur_a_h  = $h_data["cur"];
    $diff     = $cur_a_h - $prev_a_h;
    $pc       = $diff / $cur_a_h;
    $summary_html.="
        <tr>
            <td>Hours</td>    
            <td>$cur_a_h</td>    
            <td>$prev_a_h</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";    
    $a_data   = getTotalRow($prev_rpt_period, $rpt_period, "_cpr2d_obs", $ship_code, "a_cur");
    $prev_a_d = $a_data["prev"];
    $cur_a_d  = $a_data["cur"];
    $diff     = $cur_a_d - $prev_a_d;
    $pc       = $diff / $cur_a_d;
    $summary_html.="
        <tr>
            <td>Total Dollars</td>    
            <td>$cur_a_d</td>    
            <td>$prev_a_d</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        <tr></tr>
        ";

    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="
    <tr>
            <td>Opportunity and Risk</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    $summary_html.="
        <tr>
            <td>Risk</td>    
            <td></td>    
            <td></td>    
            <td></td>    
            <td></td>    
        </tr>
        ";
    $summary_html.="
        <tr>
            <td>Factored Risk</td>    
            <td></td>    
            <td></td>    
            <td></td>    
            <td></td>    
        </tr>
        ";
    $summary_html.="
        <tr>
            <td>Opportunity</td>    
            <td></td>    
            <td></td>    
            <td></td>    
            <td></td>    
        </tr>
        ";
    $summary_html.="
        <tr>
            <td>Factored Opportunity</td>    
            <td></td>    
            <td></td>    
            <td></td>    
            <td></td>    
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        ";

    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="
    <tr>
            <td>EAC </td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";


    $hours_out = getTotalOutsourceHours($prev_rpt_period,$rpt_period, "_cpr2o", $ship_code, "est_vac", "hours");
    $prev_h_out       = $hours_out["prev"];
    $cur_h_out        = $hours_out["cur"];

    $hours_mmc = getSubTotalRow($prev_rpt_period,$rpt_period, "_cpr1h", $ship_code, "est_vac");
    $prev_h_mmc       = $hours_mmc["prev"]-$prev_h_out;
    $cur_h_mmc        = $hours_mmc["cur"]- $cur_h_out;

    $diff       = $cur_h_mmc-$prev_h_mmc;
    $pc         = $diff/$cur_h_mmc;

    $summary_html.="
        <tr>
            <td>HOURS (MMC Only)</td>    
            <td>$cur_h_mmc</td>    
            <td>$prev_h_mmc</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    
    $dollars_out = getTotalOutsourceHours($prev_rpt_period,$rpt_period, "_cpr2o", $ship_code, "est_vac", "dollars");
    $prev_d_out       = $dollars_out["prev"];
    $cur_d_out        = $dollars_out["cur"];
    
    $labor_d = getSubTotalRowNOCOM($prev_rpt_period, $rpt_period, "_cpr2l_obs", $ship_code, "est_vac");
    $prev_ld = $labor_d["prev"]-$prev_d_out;
    $cur_ld  = $labor_d["cur"]-$cur_d_out;

    $diff       = $cur_ld-$prev_ld;
    $pc         = $diff/$cur_ld;

    $summary_html.="
        <tr>
            <td>Labor Dollars (NO COM)</td>    
            <td>$cur_ld</td>    
            <td>$prev_ld</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $matl_mmc = getSubTotalRow($prev_rpt_period, $rpt_period, "_cpr2m_obs", $ship_code, "est_vac");
    $prev_mtl = $matl_mmc["prev"];
    $cur_mtml = $matl_mmc["cur"];

    $diff = $cur_mtml - $prev_mtl;
    $pc   = $diff / $cur_mtml;

    $summary_html.="
        <tr>
            <td>Material Dollars </td>    
            <td>$cur_mtml</td>    
            <td>$prev_mtl</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";


    $diff = $cur_d_out - $prev_d_out;
    $pc   = $diff / $cur_d_out;

    $summary_html.="
        <tr>
            <td>Outsourcing</td>    
            <td>$cur_d_out</td>    
            <td>$prev_d_out</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $cur_matl_d  = $cur_d_out + $cur_mtml;
    $prev_matl_d = $prev_d_out + $prev_mtl;
    $diff        = $cur_matl_d - $prev_matl_d;
    $pc          = $diff / $cur_matl_d;

    $summary_html.="
        <tr>
            <td>Total \"Material\"</td>    
            <td>$cur_matl_d</td>    
            <td>$prev_matl_d</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        <tr></tr>
        ";

    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="
    <tr>
            <td>EAC HOURS</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";

    $manufacturing_mmc       = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "est_vac", "600 Manufacturing");
    $prev_600mmc             = $manufacturing_mmc["prev"];
    $cur_600mmc              = $manufacturing_mmc["cur"];
    $cur_mmc_manufacuring_h  = $cur_600mmc - $cur_h_out;
    $prev_mmc_manufacuring_h = $prev_600mmc - $prev_h_out;


    $diff = $cur_mmc_manufacuring_h - $prev_mmc_manufacuring_h;
    $pc   = $diff / $cur_mmc_manufacuring_h;

    $summary_html.="
        <tr>
            <td>Manufacturing (MMC Only)</td>    
            <td>$cur_mmc_manufacuring_h</td>    
            <td>$prev_mmc_manufacuring_h</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $manufacturing_mmc       = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "est_vac", "620 Manufacturing Supervision");
    $prev_620mmc_h             = $manufacturing_mmc["prev"];
    $cur_620mmc_h              = $manufacturing_mmc["cur"];

    $diff = $cur_620mmc_h - $prev_620mmc_h;
    $pc   = $diff / $cur_620mmc_h;

    $summary_html.="
        <tr>
            <td>Supervision (MMC Only)</td>    
            <td>$cur_620mmc_h</td>    
            <td>$prev_620mmc_h</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $manufacturing_mmc         = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "est_vac", "500 Engineering");
    $prev_500mmc_h             = $manufacturing_mmc["prev"];
    $cur_500mmc_h              = $manufacturing_mmc["cur"];

    $diff = $cur_500mmc_h - $prev_500mmc_h;
    $pc   = $diff / $cur_500mmc_h;

    $summary_html.="
        <tr>
            <td>Engineering (MMC Only)</td>    
            <td>$cur_500mmc_h</td>    
            <td>$prev_500mmc_h</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $other_mmc_h         = getTotalHoursByMMCOtherSalary($prev_rpt_period,$rpt_period,"_cpr2h_obs", $ship_code, "est_vac");
    $prev_other_mmc_h             = $other_mmc_h["prev"];
    $cur_other_mmc_h              = $other_mmc_h["cur"];

    $diff = $cur_other_mmc_h - $prev_other_mmc_h;
    $pc   = $diff / $cur_other_mmc_h;

    $summary_html.="
        <tr>
            <td>Other Salary (MMC Only)</td>    
            <td>$cur_other_mmc_h</td>    
            <td>$prev_other_mmc_h</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";

    $diff = $cur_h_out-$prev_h_out;
    $pc = $diff/$cur_h_out;
    $summary_html.="
        <tr>
            <td>Outsourcing </td>    
            <td>$cur_h_out</td>    
            <td>$prev_h_out</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr><tr></tr>
        ";
    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="
        <tr>
            <td>EAC Dollars</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>
        ";
    $diff        = $cur_ld - $prev_ld;
    $pc          = $diff / $cur_ld;
    $summary_html.="
        <tr>
            <td>Labor Dollars (NO COM)</td>    
            <td>$cur_ld</td>    
            <td>$prev_ld</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $diff        = $cur_md - $prev_md;
    $pc          = $diff / $cur_md;
    $summary_html.="
        <tr>
            <td>Material Dollars</td>    
            <td>$cur_md</td>    
            <td>$prev_md</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $diff        = $cur_d_out - $prev_d_out;
    $pc          = $diff / $cur_d_out;
    $summary_html.="
        <tr>
            <td>Outsourcing</td>    
            <td>$cur_d_out</td>    
            <td>$prev_d_out</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $diff        = $cur_matl_d - $prev_matl_d;
    $pc          = $diff / $cur_matl_d;
    $summary_html.="
        <tr>
            <td>Total \"Material\"</td>    
            <td>$cur_matl_d</td>    
            <td>$prev_matl_d</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        <tr></tr>
        ";
    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="
    <tr>
            <td>ACWP Increment</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    $manufacturing_mmc = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cur", "600 Manufacturing");
    $prev_600mmc_ah    = $manufacturing_mmc["prev"];
    $cur_600mmc_ah     = $manufacturing_mmc["cur"];
    $diff       = $cur_600mmc_ah-$prev_600mmc_ah;
    $pc         = $diff/$cur_600mmc_ah;
    $summary_html.="
        <tr>
            <td>Manufacturing (MMC ONLY)</td>    
            <td>$cur_600mmc_ah</td>    
            <td>$prev_600mmc_ah</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";

    $manufacturing_mmc = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cur", "620 Manufacturing Supervision");
    $prev_620mmc_ah     = $manufacturing_mmc["prev"];
    $cur_620mmc_ah      = $manufacturing_mmc["cur"];
    $diff              = $cur_620mmc_ah - $prev_620mmc_ah;
    $pc                = $diff / $cur_620mmc_ah;
    $summary_html.="
        <tr>
            <td>Supervision (MMC ONLY)</td>    
            <td>$cur_620mmc_ah</td>    
            <td>$prev_620mmc_ah</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    
    $manufacturing_mmc = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cur", "500 Engineering");
    $prev_500mmc_ah     = $manufacturing_mmc["prev"];
    $cur_500mmc_ah      = $manufacturing_mmc["cur"];
    $diff              = $cur_500mmc_ah - $prev_500mmc_ah;
    $pc                = $diff / $cur_500mmc_ah;
    $summary_html.="
        <tr>
            <td>Engineering (MMC ONLY)</td>    
            <td>$cur_500mmc_ah</td>    
            <td>$prev_500mmc_ah</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $other_mmc_ah         = getTotalHoursByMMCOtherSalary($prev_rpt_period,$rpt_period,"_cpr2h_obs", $ship_code, "a_cur");
    $prev_other_mmc_ah             = $other_mmc_ah["prev"];
    $cur_other_mmc_ah              = $other_mmc_ah["cur"];

    $diff = $cur_other_mmc_ah - $prev_other_mmc_ah;
    $pc   = $diff / $cur_other_mmc_ah;

    $summary_html.="
        <tr>
            <td>Other Salary (MMC Only)</td>    
            <td>$cur_other_mmc_ah</td>    
            <td>$prev_other_mmc_ah</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $summary_html.="</table>";
    //echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br />';

    /*
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* NEXT TABLE
* */
    $summary_html.="
    <tr>
            <td>ETC CHANGE</td>    
            <td>$cur_full_month</td>    
            <td>$prev_full_month</td>    
            <td>DIFF</td>    
            <td>% Change</td>    
        </tr>";
    /*MANUFACTURING ETC*/
    $manufacturing_mmc_acum = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cum", "600 Manufacturing");
    $prev_600mmc_acum       = $manufacturing_mmc_acum["prev"];
    $cur_600mmc_acum        = $manufacturing_mmc_acum["cur"];

    $cur_mmc_manufacuring_etc  = $cur_600mmc - $cur_600mmc_acum- $cur_h_out;
    $prev_mmc_manufacuring_etc = $prev_600mmc - $prev_600mmc_acum- $prev_h_out;

    $diff              = $cur_mmc_manufacuring_etc - $prev_mmc_manufacuring_etc;
    $pc                = $diff / $cur_mmc_manufacuring_etc;
    $summary_html.="
        <tr>
            <td>Manufacturing (MMC ONLY)</td>    
            <td>$cur_mmc_manufacuring_etc</td>    
            <td>$prev_mmc_manufacuring_etc</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $sup_mmc_acum = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cum", "620 Manufacturing Supervision");
    $prev_620mmc_acum       = $sup_mmc_acum["prev"];
    $cur_620mmc_acum        = $sup_mmc_acum["cur"];

    $cur_mmc_sup_etc = $cur_620mmc_h - $cur_620mmc_acum;
    $prev_mmc_sup_etc= $prev_620mmc_h- $prev_620mmc_acum;

    $diff              = $cur_mmc_sup_etc - $prev_mmc_sup_etc;
    $pc                = $diff / $cur_mmc_sup_etc;
    $summary_html.="
        <tr>
            <td>SUPERVISION (MMC ONLY)</td>    
            <td>$cur_mmc_sup_etc</td>    
            <td>$prev_mmc_sup_etc</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $eng_mmc_acum = getTotalHoursByOBS($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cum", "500 Engineering");
    $prev_500mmc_acum       = $eng_mmc_acum["prev"];
    $cur_500mmc_acum        = $eng_mmc_acum["cur"];

    $cur_mmc_sup_etc = $cur_500mmc_h - $cur_500mmc_acum;
    $prev_mmc_sup_etc= $prev_500mmc_h- $prev_500mmc_acum;

    $diff              = $cur_mmc_sup_etc - $prev_mmc_sup_etc;
    $pc                = $diff / $cur_mmc_sup_etc;
    $summary_html.="
        <tr>
            <td>ENGINEERING (MMC ONLY)</td>    
            <td>$cur_mmc_sup_etc</td>    
            <td>$prev_mmc_sup_etc</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $other_mmc_acum      = getTotalHoursByMMCOtherSalary($prev_rpt_period, $rpt_period, "_cpr2h_obs", $ship_code, "a_cum");
    $prev_other_mmc_acum = $other_mmc_acum["prev"];
    $cur_other_mmc_acum  = $other_mmc_acum["cur"];
    $cur_etc_other       = $cur_other_mmc_h - $cur_other_mmc_acum;
    $prev_etc_other      = $prev_other_mmc_h - $prev_other_mmc_acum;
    $diff              = $cur_etc_other - $prev_etc_other;
    $pc                = $diff / $cur_etc_other;
    $summary_html.="
        <tr>
            <td>OTHER SALARY (MMC ONLY)</td>    
            <td>$cur_etc_other</td>    
            <td>$prev_etc_other</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $diff = $cur_h_out - $prev_h_out;
    $pc   = $diff / $cur_h_out;
    $summary_html.="
        <tr>
            <td>OUTSOURCING </td>    
            <td>$cur_h_out</td>    
            <td>$prev_h_out</td>    
            <td>$diff</td>    
            <td>$pc</td>    
        </tr>
        ";
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."summary.xls";
    $path2summary= "../../util/excel_exports/".$token."summary.xls";
    file_put_contents($path2_export,$summary_html);

    $change_summary_html = produceChangeSummaryHTML($ship_code, $rpt_period,$prev_rpt_period);
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."change_sum.xls";
    $path2Change_summary= "../../util/excel_exports/".$token."change_sum.xls";
    file_put_contents($path2_export,$change_summary_html);

    $objPHPExcel        = formatExcelSheet($path, "Cost Growth");
    $matlLaborSheet     = formatExcelSheetLBR($path2matlandLabor, "Labor and MATL Dollars");
    $summarySheet       = formatExcelSheet($path2summary, "Summary");
    $changeSummarySheet = formatExcelSheet($path2Change_summary, "Change Summary");

    $allsheets = $matlLaborSheet->getAllSheets();
    foreach ($allsheets as $sheet) {
        $objPHPExcel->addExternalSheet($sheet);
    }
    $summaryAllSheets = $summarySheet->getAllSheets();
    foreach ($summaryAllSheets as $sheet) {
        $objPHPExcel->addExternalSheet($sheet);
    }
    $changesummaryAllSheets = $changeSummarySheet->getAllSheets();
    foreach ($changesummaryAllSheets as $sheet) {
        $objPHPExcel->addExternalSheet($sheet);
    }
    $objPHPExcel->setActiveSheetIndex(3);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

    $objWriter->save($path);
    die($html."<>".$path);
}