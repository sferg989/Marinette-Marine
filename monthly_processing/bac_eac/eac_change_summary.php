<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 2/15/2017
 * Time: 3:58 PM
 */
function eachChangeSummaryTable($prev_full_month,$cur_full_month){
    $html = returnBACEACTableParts("EAC", $prev_full_month, $cur_full_month, "DIFF", "% Change");
    return $html;
}