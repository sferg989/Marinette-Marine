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


function getLastPeriodForHull($ship_code){
    $sql = "select max(start_date) as end_date 
            from tphase_hull_date 
            where ship_code = $ship_code ";
    $rs = dbCall($sql,"meac");
    $end_date = $rs->fields["end_date"];
    $year = substr($end_date, 0, 4);
    $month = month2digit(substr($end_date, 5, 2));
    //$day = substr($end_date,8,2);
    $rpt_period ="$year"."$month";
    return $rpt_period;
}
function getCurrentStage($stage_dates, $date){

    foreach ($stage_dates as $key=>$value){
        $start_date = date('Y-m-d', strtotime($value["start_date"]));
        $end_date   = date('Y-m-d', strtotime($value["end_date"]));
        if (($date >= $start_date) && ($date < $end_date))
        {
            $stage       = $value["stage"];
            $stage_start = $value["start_date"];
            $stage_end   = $value["end_date"];
            break;
        }
    }
    $data["stage"] = $stage;
    $data["start"] = $stage_start;
    $data["end"]   = $stage_end;
    return $data;
}
function createTPSQL($ship_code, $start_rpt_period,$end_rpt_period){

    $array_of_rpts = getRptPeriods($start_rpt_period, $end_rpt_period);
    $sql = "";
    foreach ($array_of_rpts as $rpt_period){
        $sql .=" (select sum(cost) from tphase_step1 tp2  where ship_code = $ship_code and rpt_period  = $rpt_period  and tp.swbs_group =tp2.swbs_group ) cost_".$rpt_period.",";
    }
    return $sql;
}
function getSWBSEAC($swbs, $ship_code){
    $sql = "
        select
          sum(inflation_eac) eac
            from (
            select est.ship_code,
            est.wp,
            concat(right(left(wp, 6), 1), '00') swbs_group,
            inflation_eac
            
            from est3 est
            where est.ship_code = $ship_code ) s where s.swbs_group = '$swbs' GROUP BY ship_code, swbs_group;
    ";
    $rs = dbCall($sql,"meac");
    $eac = $rs->fields["eac"];
    return $eac;
}
function getMonthDiff($start_date, $end_date){
    $date1 = new DateTime($start_date);
    $date2 = new DateTime($end_date);
    $interval = date_diff($date1, $date2);
    return $interval->m + ($interval->y * 12) . ' months';

}
function getPCSpendByStage($swbs_group, $stage){
    $sql = "select weight from tphase_weight where swbs_group = '$swbs_group' and stage = '$stage'";
    $rs = dbCall($sql, "meac");
    $weight = $rs->fields["weight"];
    return $weight;
}

$ship_code = "0481";
$rpt_period_sql = createTPSQL($ship_code, 201601,201707);
$sql = "
    select
      $rpt_period_sql"."
       swbs_group
from tphase_step1 tp where ship_code = $ship_code group by swbs_group
";

$rs = dbCall($sql, "meac");
$last_rpt_period = getLastPeriodForHull($ship_code);
$tp_rpt_periods = getRPTPeriodsgreaterThanYear(201708, $last_rpt_period);
$stage_dates    = getStageDates($ship_code);

$swbs_array = array();
$swbs_array[] = "000";
$swbs_array[] = "100";
$swbs_array[] = "200";
$swbs_array[] = "300";
$swbs_array[] = "400";
$swbs_array[] = "500";
$swbs_array[] = "600";
$swbs_array[] = "700";
$swbs_array[] = "800";
$swbs_array[] = "900";


$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$header_col = "A";
$data_start = 2;

while (!$rs->EOF)
{
    $header_col = "A";

    $swbs_group = $rs->fields["swbs_group"];
    $cost_201607 = $rs->fields["cost_201607"];
    $cost_201608 = $rs->fields["cost_201608"];
    $cost_201609 = $rs->fields["cost_201609"];
    $cost_201610 = $rs->fields["cost_201610"];
    $cost_201611 = $rs->fields["cost_201611"];
    $cost_201612 = $rs->fields["cost_201612"];
    $cost_201701 = $rs->fields["cost_201701"];
    $cost_201702 = $rs->fields["cost_201702"];
    $cost_201703 = $rs->fields["cost_201703"];
    $cost_201704 = $rs->fields["cost_201704"];
    $cost_201705 = $rs->fields["cost_201705"];
    $cost_201706 = $rs->fields["cost_201706"];
    $cost_201707 = $rs->fields["cost_201707"];

    $sheet->SetCellValue($header_col++.$data_start, $swbs_group);

    $sheet->SetCellValue($header_col.$data_start, $cost_201607);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201608);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201609);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201610);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201611);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201612);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201701);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201702);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201703);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201704);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201705);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201706);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);

    $sheet->SetCellValue($header_col.$data_start, $cost_201707);
    phpExcelCurrencySheet($header_col++.$data_start, $sheet);
    $data_start++;
    $rs->MoveNext();
}
$data_start = 2;

/*
 *
 * */


$header_col++;
$header_col++;

foreach ($tp_rpt_periods as $period){
    $data_start = 2;
    $sheet->SetCellValue($header_col."1", $period);

    $year = substr($period, 0,4);
    $month= substr($period, 4,2);
    $date = date("$year-$month-01");
    $stage_data     = getCurrentStage($stage_dates, $date);
    $stage          = $stage_data["stage"];
    $stage_start    = $stage_data["start"];
    $stage_end      = $stage_data["end"];
    $num_months_in_stage = getMonthDiff($stage_start, $stage_end);


    print "THis is the period ".$period." and this is the stage".$stage." <br>
                The STage is $num_months_in_stage long <br>";

    foreach ($swbs_array as $sbws){
        $swbs_eac = getSWBSEAC($sbws, $ship_code);

        $monthly_swbs_rate = (getPCSpendByStage($sbws,$stage)*$swbs_eac)/($num_months_in_stage);
        print "This is the SWBS ".$sbws." This is the EAC = ".formatCurrencyNumber($swbs_eac)
            ." This is the monthly rate = ".formatCurrencyNumber($monthly_swbs_rate)."<br>";
        $sheet->SetCellValue($header_col.$data_start, $monthly_swbs_rate);
        phpExcelCurrencySheet($header_col.$data_start, $sheet);
        $data_start++;
    }
    $header_col++;
}

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$token         = rand (0,1000);
$objWriter->save("C:/evms/excel/".$ship_code."_".$token."EXECSUMMARYexport.xlsx");
die("made it");
