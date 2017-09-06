<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 7/19/2017
 * Time: 9:26 AM
*/
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
require('C:\xampp\htdocs\fmg\inc\lib\php\phpExcel-1.8\classes\phpexcel\IOFactory.php');

$user = $_SESSION["user_name"];
$user = "fs11239";
function createTPSQL($ship_code, $start_rpt_period,$end_rpt_period){

    $array_of_rpts = getRptPeriods($start_rpt_period, $end_rpt_period);
    var_dump($array_of_rpts);
    $sql = "";
    foreach ($array_of_rpts as $rpt_period){
        $sql .=" (select sum(cost) from tphase_step1 tp2  where ship_code = $ship_code and rpt_period  = $rpt_period  and tp.swbs_group =tp2.swbs_group ) cost_".$rpt_period.",";
    }
    return $sql;
}
$ship_code = "0475";
$rpt_period_sql = createTPSQL($ship_code, 201601,201708);
$sql = "
    select
      $rpt_period_sql"."\r
       swbs_group
from tphase_step1 tp where ship_code = 469 group by swbs_group
";
print $sql;
//$objWriter->save("../../../util/excel_exports/$ship_code".$token."export.xlsx");
//$path = "../util/excel_exports/$ship_code".$token."export.xlsx";
die("made it");
