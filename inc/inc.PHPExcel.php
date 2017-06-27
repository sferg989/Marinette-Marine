<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/10/2017
 * Time: 1:51 PM
 */
include("lib/php/phpExcel-1.8/classes/PHPExcel.php");
//include("lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");

function loadPHPEXCELFile($path2xlsfile)
{
    print "<br>".$path2xlsfile."<br>";
    $inputFileType = PHPExcel_IOFactory::identify($path2xlsfile);
    print $inputFileType;
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($path2xlsfile);

    //$objPHPExcel = PHPExcel_IOFactory::load($path2xlsfile);
    return $objPHPExcel;
}

function loadPHPEXCELFile2007($path2xlsxfile)
{

    print $path2xlsxfile;
    if (!file_exists($path2xlsxfile)) {
        exit("Please run 05featuredemo.php first." . EOL);
    }
//    die("made it");

    echo date('H:i:s') , " Load from Excel2007 file" , EOL;
    $callStartTime = microtime(true);
    $objPHPExcel = PHPExcel_IOFactory::load($path2xlsxfile);
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;
    echo 'Call time to read Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
    echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
    echo date('H:i:s') , " Write to Excel2007 format" , EOL;
    $callStartTime = microtime(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save("../util/test_ses.xlsx");
    $callEndTime = microtime(true);
    $callTime = $callEndTime - $callStartTime;

    /*    $inputFileName = $path2xlsxfile;
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    $objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($inputFileName);*/
    print $path2xlsxfile;
    die();
    return $objPHPExcel;
}

function PHPExcelcheckifSheetNameExists($excelOBJ, $sheet_name){
    $sheet_exists = false;
    $sheetNames = $excelOBJ->getSheetNames();
    foreach ($sheetNames as $value) {
        if($value==$sheet_name){
            $sheet_exists = true;
        }
    }
    return $sheet_exists;
}

function savePHPEXCELCSV($file_name,$path2xlsfile,$path2_destination)
{
    print "<br>".$path2xlsfile."<br>";
    if(file_exists($path2xlsfile)==false){
        die("could not find the file");
        return null;
    }

    $objPHPExcel  = loadPHPEXCELFile($path2xlsfile);
    $objWriter    = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $sheet_exists = PHPExcelcheckifSheetNameExists($objPHPExcel, "Locked Data");
    if($sheet_exists==true){
        PHPExcelRemoveSheetByName($objPHPExcel,"Locked Data");
    }
    $index = 0;
    foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

        $objPHPExcel->setActiveSheetIndex($index);

        // write out each worksheet to it's name with CSV extension
        $outFile = str_replace(array("-"," "), "_", $worksheet->getTitle()) ."$file_name.csv";
        $objWriter->setDelimiter(',');
        $objWriter->setSheetIndex($index);
        $objWriter->save($path2_destination."/".$outFile);

        $index++;
    }
    $objPHPExcel = null;
    return $outFile;
}

function savePHPEXCELCSV1WorkSheetByIndex($file_name,$path2xlsfile,$path2_destination, $sheet_index)
{
    print $path2_destination."<br>";

    if(file_exists($path2xlsfile)==false){
        return null;
    }

    $objPHPExcel  = loadPHPEXCELFile($path2xlsfile);

    $objWriter    = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objPHPExcel->setActiveSheetIndex($sheet_index);

    // write out each worksheet to it's name with CSV extension
    $objWriter->setDelimiter(',');
    $objWriter->setSheetIndex($sheet_index);

    // write out each worksheet to it's name with CSV extension
    $outFile = "$file_name.csv";
    $objWriter->save($path2_destination."/".$outFile);
    $objPHPExcel = null;
    return $outFile;
}
function savePHPEXCELCSV1WorkSheetByIndex2007($file_name,$path2xlsfile,$path2_destination, $sheet_index)
{



    $objPHPExcel  = loadPHPEXCELFile2007($path2xlsfile);

    $objWriter    = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objPHPExcel->setActiveSheetIndex($sheet_index);

    // write out each worksheet to it's name with CSV extension
    $objWriter->setDelimiter(',');
    $objWriter->setSheetIndex($sheet_index);

    // write out each worksheet to it's name with CSV extension
    $outFile = "$file_name.csv";
    $objWriter->save($path2_destination."/".$outFile);
    $objPHPExcel = null;
    return $outFile;
}

function PHPExcelRemoveSheetByName($objWorkSheet,$sheet_name){
    $objWorkSheet->setActiveSheetIndexByName($sheet_name);

    $sheetIndex = $objWorkSheet->getActiveSheetIndex();
    $objWorkSheet->removeSheetByIndex($sheetIndex);
}
function loadTimePhaseFutureCheck($rpt_period, $schema, $ship_code, $time_phased_file_name, $path2_destination, $path2xlsfile, $g_path_to_util, $g_path2CobraAPI, $g_path2BatrptCMD, $g_path2BatrptBAT, $debug){
    $table_name   = $rpt_period."_tp_check";

    $create_table = checkIfTableExists($schema, $table_name);
    if($create_table== "create_table"){
        createTableFromBase($schema,"template_tp_check", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, $schema);
    $insert_sql = "insert into $schema.$table_name (ship_code,ca, wp,date, val) values ";

    $batch_rpt_name = "csv".$ship_code."BLValid";
    runCobraBatchReportProcess($ship_code,$batch_rpt_name, $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);

    $new_csv_file_name = $ship_code."tp_check";
    $path2_source_xls = $path2xlsfile."/$time_phased_file_name";

    savePHPEXCELCSV1WorkSheetByIndex($new_csv_file_name,$path2_source_xls,$path2_destination, 3);
    $sql = $insert_sql;
    $real_path2_new_CSV = $g_path_to_util."\\csv_bl_validation\\".$new_csv_file_name.".csv";
    $handle = fopen($real_path2_new_CSV,"r");
    fgetcsv($handle);
    $i=0;
    while (($data = fgetcsv($handle)) !== FALSE)
    {
        $ca         = addslashes(trim($data[0]));
        $wp         = addslashes(trim($data[1]));
        $date       = addslashes(trim($data[4]));
        $rpt_period = createRPTfromDateSlash($date);
        $val        = formatNumber4decNoComma($data[5]);

        $sql.=
            "(
                $ship_code,
                '$ca',
                '$wp',
                $rpt_period,
                $val
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

        $junk = dbCall($sql, $schema);
    }
}
