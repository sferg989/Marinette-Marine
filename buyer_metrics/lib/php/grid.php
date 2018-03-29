<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
session_write_close();
function getReasonForChange(){
    $reason_array = array();
    $sql = "select reason_for_change from po_approval_log 
              where reason_for_change NOT IN ('Cost Savings', 'Price Increase') 
              and reason_for_change not like '%HPR%'
              group by reason_for_change";
    $rs =dbCall($sql, "meac");
    while (!$rs->EOF)
    {
        $reason = $rs->fields["reason_for_change"];
        $reason_array[] = $reason;
        $rs->MoveNext();
    }
    return $reason_array;
}
function countFortisEntries($po){
    $sql = "select count(*) count FROM FMM_Purchase_Order where PO_Number = $po";
    $rs = dbCallFortis($sql);
    $count = $rs->fields["count"];
    return $count;
}
function returnHeaders(){
    $header_array[] = "BUYER";
    $header_array[] = "TOTAL Spend";
    $header_array[] = "TOTAL BUDGET";
    $header_array[] = "Variance";
    $header_array[] = "PO's ISSUED";
    $header_array[] = "ADDENDUMS";
    return $header_array;
}
function returnBuyerHeaders(){
    $header_array[] = "BUYER";
    $header_array[] = "ITEM";
    $header_array[] = "COST";
    $header_array[] = "BUDGET";
    $header_array[] = "VARIANCE";
    $header_array[] = "REASON";
    $header_array[] = "PO NUM";
    return $header_array;
}

function getActiveBuyersPOApproval(){
    $buyers_array = array();
    $sql = "select buyer from meac.po_approval_log group by buyer";
    $rs = dbCall($sql,"mars");
    while (!$rs->EOF)
    {
        $buyer = $rs->fields["buyer"];
        $buyers_array[] = $buyer;
        $rs->MoveNext();
    }
    return $buyers_array;
}
function getNumPOSIssued($buyer){
    $sql ="select count(*) po_count from (select po from po_approval_log where buyer like '%$buyer%' and reason_for_change IN
              ('Cost Savings', 'Price Increase') group by po) as x";
    $rs = dbCall($sql,"meac");
    //print $sql;
    $po_count = intval($rs->fields["po_count"]);
    return $po_count;
}
function getAddendumCount($buyer, $po_nums){
    $buyer_array = explode(" ", $buyer);
    $sql = "select count(*)-$po_nums add_on_count from po_data where buyer like '%$buyer_array[1]%' 
                    and po in (select po from po_approval_log 
                    where buyer like '%$buyer_array[1]%' 
                    and reason_for_change IN ('Cost Savings', 'Price Increase') 
                    group by po)";
    $rs = dbCall($sql,"meac");
    $add_on_count = intval($rs->fields["add_on_count"]);
    return $add_on_count;
}
function writeBuyerData($sheet,$buyer){
    $header_array = returnBuyerHeaders();
    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sql = "
        SELECT
          buyer,
          item,
          sum(val) cost,
          GROUP_CONCAT(DISTINCT CONCAT(`reason_for_change`, ' - ')) reason,
          GROUP_CONCAT(DISTINCT CONCAT(`po`, ' - '))                PO_NUM,
          coalesce(sum(etc), 0)                                     budget
        FROM
          meac.po_approval_log po
        WHERE reason_for_change IN
              ('Cost Savings', 'Price Increase')
              and buyer like '%$buyer%'
        GROUP BY buyer,
          ship_code,
          item";
    $rs = dbCall($sql, "meac");
    $header_col = "A";
    $data_start = 2;
    while (!$rs->EOF)
    {
        $header_col = "A";

        $buyer       = trim($rs->fields["buyer"]);
        $item = trim($rs->fields["item"]);
        $cost        = $rs->fields["cost"];
        $reason      = $rs->fields["reason"];
        $PO_NUM      = $rs->fields["PO_NUM"];
        $budget      = $rs->fields["budget"];
        $variance = formatNumber4decNoComma($budget-$cost);

        $sheet->setCellValue($header_col++.$data_start, $buyer);

        $sheet->setCellValue($header_col++.$data_start, $item);

        $sheet->setCellValue($header_col.$data_start, $cost);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $budget);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $variance);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col++.$data_start, $reason);
        $sheet->setCellValue($header_col++.$data_start, $PO_NUM);

        $data_start++;
        $rs->MoveNext();
    }

}
if($control=="project_grid")
{
    $data  = "[";
    $id    = 1;
    $sql   = "
    select
          buyer,
          sum(cost) total_spend,
          sum(budget) as total_budget
           from (
        SELECT
          buyer,
          item,
          sum(val) cost,
          GROUP_CONCAT(DISTINCT CONCAT(`reason_for_change`, ' - ')) reason,
          GROUP_CONCAT(DISTINCT CONCAT(`po`, ' - '))                PO_NUM,
          coalesce(sum(etc), 0)                                     budget
        FROM
          meac.po_approval_log po
        WHERE reason_for_change IN
              ('Cost Savings', 'Price Increase')
        GROUP BY buyer,
          ship_code,
          item) s group by s.buyer
    ";
    $rs  = dbCall($sql);
    while (!$rs->EOF)
    {
        $buyer          = trim($rs->fields["buyer"]);
        $total_spend    = trim(formatNumber4decNoComma($rs->fields["total_spend"]));
        $total_budget   = trim(formatNumber4decNoComma($rs->fields["total_budget"]));
        $variance       = formatNumber4decNoComma($total_budget - $total_spend);
        $po_count       = getNumPOSIssued($buyer);
        $addeddum_count = getAddendumCount($buyer, $po_count);

        $data.="{
        \"id\"               : $id,
        \"buyer\"            :\"$buyer\",
        \"total_spend\"      :$total_spend,
        \"total_budget\"     :$total_budget,
        \"variance\"         :$variance,
        \"po_count\"         :$po_count,
        \"addeddum_count\"   :$addeddum_count
    },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="excel_export"){
    $header_array = returnHeaders();
    $objPHPExcel  = new PHPExcel();
// Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("Buyer Metrics");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sql   = "
    select
          buyer,
          sum(cost) total_spend,
          sum(budget) as total_budget
           from (
        SELECT
          buyer,
          item,
          sum(val) cost,
          GROUP_CONCAT(DISTINCT CONCAT(`reason_for_change`, ' - ')) reason,
          GROUP_CONCAT(DISTINCT CONCAT(`po`, ' - '))                PO_NUM,
          coalesce(sum(etc), 0)                                     budget
        FROM
          meac.po_approval_log po
        WHERE reason_for_change IN
              ('Cost Savings', 'Price Increase')
        GROUP BY buyer,
          ship_code,
          item) s group by s.buyer
    ";
    $rs = dbCall($sql,"meac");
    $header_col = "A";
    $data_start = 2;
    while (!$rs->EOF)
    {
        $header_col = "A";

        $buyer          = trim($rs->fields["buyer"]);
        $total_spend    = trim($rs->fields["total_spend"]);
        $total_budget   = trim($rs->fields["total_budget"]);
        $variance       = $total_budget - $total_spend;
        $po_count       = getNumPOSIssued($buyer);
        $addeddum_count = getAddendumCount($buyer, $po_count);

        $sheet->setCellValue($header_col++.$data_start, $buyer);

        $sheet->setCellValue($header_col.$data_start, $total_spend);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $total_budget);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col.$data_start, $variance);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->setCellValue($header_col++.$data_start, $po_count);
        $sheet->setCellValue($header_col++.$data_start, $addeddum_count);

        $data_start++;
        $rs->MoveNext();
    }
    $sheet_index = 1;
    $buyer_array = getActiveBuyersPOApproval();
    foreach ($buyer_array as $buyer){
        $objWorkSheet = $objPHPExcel->createSheet($sheet_index); //Setting index when creating
        $objWorkSheet->setTitle($buyer);
        $objWorkSheet->getTabColor()->setARGB('FF0094FF');
        $objPHPExcel->setActiveSheetIndex($sheet_index);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet_index++;
        writeBuyerData($sheet,$buyer);
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $path = "../util/excel_exports/".$token."export.xls";
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/buyer_metrics_".$token.".xlsx");
    $path = "../util/excel_exports/buyer_metrics_".$token.".xlsx";
    die($path);
}
if($control=="excel_export_charts"){

    $objPHPExcel = new PHPExcel();
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $objWorksheet->fromArray(
        array(
            array('',	2010,	2011,	2012),
            array('Q1',   12,   15,		21),
            array('Q2',   56,   73,		86),
            array('Q3',   52,   61,		69),
            array('Q4',   30,   32,		0),
        )
    );


//	Set the Labels for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $dataSeriesLabels1 = array(
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$1', NULL, 1),	//	2010
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$1', NULL, 1),	//	2011
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$D$1', NULL, 1),	//	2012
    );
//	Set the X-Axis Labels
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $xAxisTickValues1 = array(
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$A$2:$A$5', NULL, 4),	//	Q1 to Q4
    );
//	Set the Data values for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $dataSeriesValues1 = array(
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2:$B$5', NULL, 4),
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$C$2:$C$5', NULL, 4),
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$D$2:$D$5', NULL, 4),
    );

//	Build the dataseries
    $series1 = new PHPExcel_Chart_DataSeries(
        PHPExcel_Chart_DataSeries::TYPE_AREACHART,				// plotType
        PHPExcel_Chart_DataSeries::GROUPING_PERCENT_STACKED,	// plotGrouping
        range(0, count($dataSeriesValues1)-1),					// plotOrder
        $dataSeriesLabels1,										// plotLabel
        $xAxisTickValues1,										// plotCategory
        $dataSeriesValues1										// plotValues
    );

//	Set the series in the plot area
    $plotArea1 = new PHPExcel_Chart_PlotArea(NULL, array($series1));
//	Set the chart legend
    $legend1 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOPRIGHT, NULL, false);

    $title1 = new PHPExcel_Chart_Title('Test %age-Stacked Area Chart');
    $yAxisLabel1 = new PHPExcel_Chart_Title('Value ($k)');


//	Create the chart
    $chart1 = new PHPExcel_Chart(
        'chart1',		// name
        $title1,		// title
        $legend1,		// legend
        $plotArea1,		// plotArea
        true,			// plotVisibleOnly
        0,				// displayBlanksAs
        NULL,			// xAxisLabel
        $yAxisLabel1	// yAxisLabel
    );

//	Set the position where the chart should appear in the worksheet
    $chart1->setTopLeftPosition('A7');
    $chart1->setBottomRightPosition('H20');

//	Add the chart to the worksheet
    $objWorksheet->addChart($chart1);


//	Set the Labels for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $dataSeriesLabels2 = array(
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$1', NULL, 1),	//	2010
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$1', NULL, 1),	//	2011
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$D$1', NULL, 1),	//	2012
    );
//	Set the X-Axis Labels
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $xAxisTickValues2 = array(
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$A$2:$A$5', NULL, 4),	//	Q1 to Q4
    );
//	Set the Data values for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $dataSeriesValues2 = array(
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2:$B$5', NULL, 4),
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$C$2:$C$5', NULL, 4),
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$D$2:$D$5', NULL, 4),
    );

//	Build the dataseries
    $series2 = new PHPExcel_Chart_DataSeries(
        PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
        PHPExcel_Chart_DataSeries::GROUPING_STANDARD,	// plotGrouping
        range(0, count($dataSeriesValues2)-1),			// plotOrder
        $dataSeriesLabels2,								// plotLabel
        $xAxisTickValues2,								// plotCategory
        $dataSeriesValues2								// plotValues
    );
//	Set additional dataseries parameters
//		Make it a vertical column rather than a horizontal bar graph
    $series2->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);

//	Set the series in the plot area
    $plotArea2 = new PHPExcel_Chart_PlotArea(NULL, array($series2));
//	Set the chart legend
    $legend2 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, NULL, false);

    $title2 = new PHPExcel_Chart_Title('Test Column Chart');
    $yAxisLabel2 = new PHPExcel_Chart_Title('Value ($k)');


//	Create the chart
    $chart2 = new PHPExcel_Chart(
        'chart2',		// name
        $title2,		// title
        $legend2,		// legend
        $plotArea2,		// plotArea
        true,			// plotVisibleOnly
        0,				// displayBlanksAs
        NULL,			// xAxisLabel
        $yAxisLabel2	// yAxisLabel
    );

//	Set the position where the chart should appear in the worksheet
    $chart2->setTopLeftPosition('I7');
    $chart2->setBottomRightPosition('P20');

//	Add the chart to the worksheet
    $objWorksheet->addChart($chart2);


// Save Excel 2007 file
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->setIncludeCharts(TRUE);
    $objWriter->save("$g_path_to_util/excel_exports/buyer_metrics_".$token.".xlsx");
    $path = "../util/excel_exports/buyer_metrics_".$token.".xlsx";
    die($path);
}
if($control =="excel_export_line"){
    $objPHPExcel = new PHPExcel();
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $objWorksheet->fromArray(
        array(
            array('',	2010,	2011,	2012),
            array('Q1',   12,   15,		21),
            array('Q2',   56,   73,		86),
            array('Q3',   52,   61,		69),
            array('Q4',   30,   32,		0),
        )
    );

    $data_array =         array(
        array('',	2010,	2011,	2012),
        array('Q1',   12,   15,		21),
        array('Q2',   56,   73,		86),
        array('Q3',   52,   61,		69),
        array('Q4',   30,   32,		0),
    );
    array_debug($data_array);

//	Set the Labels for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $dataSeriesLabels = array(
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$1', NULL, 1),	//	2010
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$1', NULL, 1),	//	2011
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$D$1', NULL, 1),	//	2012
    );
//	Set the X-Axis Labels
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $xAxisTickValues = array(
        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$A$2:$A$5', NULL, 4),	//	Q1 to Q4
    );
//	Set the Data values for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
    $dataSeriesValues = array(
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2:$B$5', NULL, 4),
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$C$2:$C$5', NULL, 4),
        new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$D$2:$D$5', NULL, 4),
    );

//	Build the dataseries
    $series = new PHPExcel_Chart_DataSeries(
        PHPExcel_Chart_DataSeries::TYPE_LINECHART,		// plotType
        PHPExcel_Chart_DataSeries::GROUPING_STACKED,	// plotGrouping
        range(0, count($dataSeriesValues)-1),			// plotOrder
        $dataSeriesLabels,								// plotLabel
        $xAxisTickValues,								// plotCategory
        $dataSeriesValues								// plotValues
    );

//	Set the series in the plot area
    $plotArea = new PHPExcel_Chart_PlotArea(NULL, array($series));
//	Set the chart legend
    $legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOPRIGHT, NULL, false);

    $title = new PHPExcel_Chart_Title('Test Stacked Line Chart');
    $yAxisLabel = new PHPExcel_Chart_Title('Value ($k)');


//	Create the chart
    $chart = new PHPExcel_Chart(
        'chart1',		// name
        $title,			// title
        $legend,		// legend
        $plotArea,		// plotArea
        true,			// plotVisibleOnly
        0,				// displayBlanksAs
        NULL,			// xAxisLabel
        $yAxisLabel		// yAxisLabel
    );

//	Set the position where the chart should appear in the worksheet
    $chart->setTopLeftPosition('A7');
    $chart->setBottomRightPosition('H20');

//	Add the chart to the worksheet
    $objWorksheet->addChart($chart);


// Save Excel 2007 file
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->setIncludeCharts(TRUE);
    //clearDirectory($g_path_to_util."/excel_exports");
    //$token = getToken();
    $objWriter->save("$g_path_to_util/excel_exports/buyer_metrics_".$token.".xlsx");
    $path = "../util/excel_exports/buyer_metrics_".$token.".xlsx";
    die($path);

}
if($control =="system_stacked_bar_sql"){
    $reason_for_change_array = getReasonForChange();
    $reason_for_change_sql = "";
    foreach ($reason_for_change_array as $value){
        $reason_for_change_sql.= "
        (select 
                sum(val) 
                    from po_approval_log po2 
        where po2.reason_for_change = '$value' 
        and  left(po2.item, 1)= left(po_log.item, 1)) as '$value',";

    }
    $reason_for_change_sql= substr($reason_for_change_sql, 0, -1);

    $sql = "select
  case when left(item, 1) = 1 then 'STRUCTURE'
    when left(item, 1) = 2 then 'PROPULSION'
    when left(item, 1) = 3 then 'ELECTRICAL'
    when left(item, 1) = 4 then 'COMMAND AND SURVEILLANCE'
    when left(item, 1) = 5 then 'AUXILIARY'
    when left(item, 1) = 6 then 'OUTFIT'
    when left(item, 1) = 7 then 'ARMAMENT'
    when left(item, 1) = 8 then 'ENGINEERING'
    when left(item, 1) = 9 then 'PRODUCTION SUPPORT'
    when left(item, 1) = 0 then 'PROGRAM MANAGEMENT'
      end as system,
  $reason_for_change_sql
  from
meac.po_approval_log po_log
WHERE reason_for_change NOT IN
      ('Cost Savings', 'Price Increase')
    and reason_for_change not like '%HPR%'
GROUP BY case when left(item, 1) = 1 then 'STRUCTURE'
    when left(item, 1) = 2 then 'PROPULSION'
    when left(item, 1) = 3 then 'ELECTRICAL'
    when left(item, 1) = 4 then 'COMMAND AND SURVEILLANCE'
    when left(item, 1) = 5 then 'AUXILIARY'
    when left(item, 1) = 6 then 'OUTFIT'
    when left(item, 1) = 7 then 'ARMAMENT'
    when left(item, 1) = 8 then 'ENGINEERING'
    when left(item, 1) = 9 then 'PRODUCTION SUPPORT'
    when left(item, 1) = 0 then 'PROGRAM MANAGEMENT'
      end";
    die($sql);

}
if($control=="po_add_ons"){

    /*TOTAL APPROVED POS*/
    $sql = "select count(*) total_po from (
SELECT
  PO_Number/*,
  CONVERT(VARCHAR(8), Created_Date, 1)*/
FROM FMM_Purchase_Order
  LEFT OUTER JOIN FTBContainer _cont ON _cont.Container_ID = F_ParentID
WHERE (CASE
       WHEN _cont.Container = 'Project Approved' OR _cont.Container = 'Approved MRO'
         THEN 'Approved'
       WHEN _cont.Container = 'Purchase Orders Disapproved'
         THEN 'Denied'
       WHEN _cont.Container LIKE '%Pending%' OR _cont.Container LIKE '%New PO%'
         THEN 'Pending'
       WHEN _cont.Container = 'No Approval'
         THEN 'Approved'
       WHEN _cont.Container LIKE '%Complete%'
         THEN 'Approved'
       WHEN _cont.Container LIKE '%Denied%'
         THEN 'Denied'
       WHEN _cont.Container LIKE '%Pending%'
         THEN 'Pending'
       WHEN _cont.Container = 'New PO'
         THEN 'New'
       ELSE '' END) = 'Approved'
      AND Created_Date BETWEEN '2017-01-01' AND '2017-01-31' and Project_Number <> ''
  GROUP BY PO_Number ) s";
    $rs = dbCallFortis($sql);
    $po_count = $rs->fields["total_po"];


    /*HOW MANY POS ISSUED BY MONTH*/
    /*HOW MANY POS ISSUED BY MONTH*/
    /*HOW MANY POS ISSUED BY MONTH*/

    $sql = "select DATEPART(Year, created),DATEPART(Month, created),count(*) total_po from (
SELECT
  PO_Number,
max(Created_Date) as created
/*,
  CONVERT(VARCHAR(8), Created_Date, 1)*/
FROM FMM_Purchase_Order
  LEFT OUTER JOIN FTBContainer _cont ON _cont.Container_ID = F_ParentID
WHERE (CASE
       WHEN _cont.Container = 'Project Approved' OR _cont.Container = 'Approved MRO'
         THEN 'Approved'
       WHEN _cont.Container = 'Purchase Orders Disapproved'
         THEN 'Denied'
       WHEN _cont.Container LIKE '%Pending%' OR _cont.Container LIKE '%New PO%'
         THEN 'Pending'
       WHEN _cont.Container = 'No Approval'
         THEN 'Approved'
       WHEN _cont.Container LIKE '%Complete%'
         THEN 'Approved'
       WHEN _cont.Container LIKE '%Denied%'
         THEN 'Denied'
       WHEN _cont.Container LIKE '%Pending%'
         THEN 'Pending'
       WHEN _cont.Container = 'New PO'
         THEN 'New'
       ELSE '' END) = 'Approved'
      AND Created_Date BETWEEN '2017-01-01' AND '2018-01-31' and Project_Number <> ''
  GROUP BY PO_Number ) s GROUP BY DATEPART(Year, created), DATEPART(Month, created) order by DATEPART(Month, created)";
    while (!$rs->EOF)
    {
        $po      = $rs->fields["PO_Number"];
        $add_ons = countFortisEntries($po)-1;
        $addeddum_count +=$add_ons;
        $rs->MoveNext();
    }


    /*HOW MANY TIMES THE APPROVED PO'S WERE TOUCHED*/
    /*1. get approved po 's in the year*/
    /*1. loop through that list and count how many entries in fortis for that po there is*/

        $sql = "
        SELECT
          PO_Number/*,
          CONVERT(VARCHAR(8), Created_Date, 1)*/
        FROM FMM_Purchase_Order
          LEFT OUTER JOIN FTBContainer _cont ON _cont.Container_ID = F_ParentID
        WHERE (CASE
               WHEN _cont.Container = 'Project Approved' OR _cont.Container = 'Approved MRO'
                 THEN 'Approved'
               WHEN _cont.Container = 'Purchase Orders Disapproved'
                 THEN 'Denied'
               WHEN _cont.Container LIKE '%Pending%' OR _cont.Container LIKE '%New PO%'
                 THEN 'Pending'
               WHEN _cont.Container = 'No Approval'
                 THEN 'Approved'
               WHEN _cont.Container LIKE '%Complete%'
                 THEN 'Approved'
               WHEN _cont.Container LIKE '%Denied%'
                 THEN 'Denied'
               WHEN _cont.Container LIKE '%Pending%'
                 THEN 'Pending'
               WHEN _cont.Container = 'New PO'
                 THEN 'New'
               ELSE '' END) = 'Approved'
              AND Created_Date BETWEEN '2017-01-01' AND '2018-01-31' and Project_Number <> ''
          GROUP BY PO_Number
        ";
        $rs = dbCallFortis($sql);
        $addeddum_count = 0;
        while (!$rs->EOF)
        {
            $po      = $rs->fields["PO_Number"];
            $add_ons = countFortisEntries($po)-1;
            $addeddum_count +=$add_ons;
            $rs->MoveNext();
        }


}