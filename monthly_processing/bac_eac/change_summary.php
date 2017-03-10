<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 2/9/2017
 * Time: 1:33 PM
 */

function produceChangeSummaryHTML($ship_code, $rpt_period,$prev_rpt_period, $ship_name){
    $html = "<table>
        <tr>
            <td colspan='4'>$ship_name</td>
        </tr>
        <tr>
            <td>EAC CHANGES</td>
            <td>Current</td>
            <td>Change</td>
            <td>NOTES</td>
        </tr>        
        <tr>
            <td>Best Case</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>        
        <tr>
            <td>Worst Case</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Most Likely</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>        
";
    $table_name = getCorrespondingTable($ship_code, "_cpr2d_obs");
    $mr         = getMR($prev_rpt_period, $rpt_period, $table_name, $ship_code, "s_vac");
    $prev_mr    = $mr["prev"];
    $cur_mr     = $mr["cur"];
    $diff_mr    = $cur_mr - $prev_mr;
    $html.= "
        <tr>
            <td>Management Reserve</td>
            <td>$cur_mr</td>
            <td>$diff_mr</td>
            <td></td>
        </tr>
";
    $html.="<tr></tr>";
    $html.="
    <tr>
            <td>UB Changes</td>
            <td>Dollars</td>
            <td>PCW/MOD</td>
            <td>NOTES</td>
    </tr>";
    $table_name = getCorrespondingTable($ship_code, "_cpr2d_obs");
    $ub         = getUB($prev_rpt_period, $rpt_period, $table_name, $ship_code, "est_vac");
    $prev_ub    = $ub["prev"];
    $cur_ub     = $ub["cur"];
    $diff       = $cur_ub - $prev_ub;

    $html.="
        <tr>
            <td>Beginning UB</td>    
            <td>$prev_ub</td>    
            <td></td>    
            <td></td>    
        </tr>
        ";
    $html.="<tr></tr>";
    $html.="
        <tr>
            <td>Ending UB</td>    
            <td>$cur_ub</td>    
            <td></td>    
            <td></td>    
        </tr>
        ";
    $html.="<tr></tr>";
    $html.="
    <tr>
            <td>LABOR EAC Changes</td>
            <td>HOURS</td>
            <td>BAC HOURS</td>
            <td>NOTES</td>
    </tr>";
    $diff_html = getLABOREACDIFF($prev_rpt_period,$rpt_period,$ship_code);
    $html.=$diff_html;
    $html.="<tr></tr>";
    $html.="<tr></tr>";
    $html.="
    <tr>
            <td>Material Changes</td>
            <td>EAC Change</td>
            <td>Budget Change</td>
            <td>NOTES</td>
    </tr>";
    $html.="<tr></tr>";
    $diff_html = getMATLEACDIFF($prev_rpt_period,$rpt_period,$ship_code);
    $html.=$diff_html;
    $html.="<tr></tr>";
    $html.="
    <tr>
            <td>MR Walk Down </td>
            <td>Dollars</td>
            <td></td>
            <td>NOTES</td>
    </tr>";
    $html.="<tr></tr>";
    $html.="<tr></tr>";
    $html.="<tr></tr>";
    $html.="
    <tr>
            <td>TOTAL</td>
            <td>$diff_mr</td>
            <td></td>
            <td></td>
    </tr>";

    $html.="<tr></tr>";
    $html.="<tr></tr>";
    $ev_html = buildCurCumSpiCpi($ship_code, $rpt_period, $prev_rpt_period);
    $html.=$ev_html;
    $html.="</table>";
    return $html;
}