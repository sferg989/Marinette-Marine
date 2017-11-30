<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');
include('../../../meac/lib/php/inc.meac.excel.export.php');
//include('../../../inc/lib/php/simplexlsx-master/simplexlsx.class.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
//$user = $_SESSION["user_name"];
$user = "fs11239";
function returnReEstPendingInsert(){
    $sql = "insert into reest_pending (ship_code, wp, item, eac_delta, user, rpt_period) VALUES";
    return $sql;
}
function shipCodeWC($ship_code,$view){
    if($ship_code =="All"){
        if($view=="all"){
            $ship_code_wc = "";
        }
        else{

        }
    }
    else{
     $ship_code_wc = "re.ship_code = $ship_code and ";
    }
    return $ship_code_wc;
}
function returnHeaders(){
    $header_array[] = "Hull";
    $header_array[] = "WP";
    $header_array[] = "ITEM";
    $header_array[] = "EAC (Including Adjustments)";
    $header_array[] = "RPT Period";

    return $header_array;
}
if($control =="rpt_period")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="{\"items\": [";

    $sql = "select rpt_period from fmm_evms.calendar;";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $rpt_period = $rs->fields["rpt_period"];
        $data.="      
        {
            \"id\": $rpt_period,
            \"text\": \"$rpt_period\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="],
    \"more\": false
    }";
    die($data);
}

if($control=="upload_v2"){

    $currentDir = getcwd();
    $uploadDirectory = "\uploads\\";
    $uploadDirectory = $g_path_to_util."uploads\\";

    clearDirectory($uploadDirectory);
    $errors = []; // Store all foreseen and unforseen errors here
    $fileExtensions = ['xlsx','jpg','png']; // Get all the file extensions

    $fileName    = $_FILES['myfile']['name'];
    $fileSize    = $_FILES['myfile']['size'];
    $fileTmpName = $_FILES['myfile']['tmp_name'];
    $fileType    = $_FILES['myfile']['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));

    $uploadPath = $uploadDirectory . basename($fileName);



        if (isset($fileName)) {

            if (! in_array($fileExtension,$fileExtensions)) {
                $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
            }

            if ($fileSize > 5000000) {
                $errors[] = "This file is more than 20MB. Sorry, it has to be less than or equal to 2MB";
            }

            if (empty($errors)) {
                $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

                if ($didUpload) {
                    //echo "The file " . basename($fileName) . " has been uploaded";
                } else {
                    //echo "An error occurred somewhere. Try again or contact the admin";
                }
            } else {
                foreach ($errors as $error) {
                    //echo $error . "These are the errors" . "\n";
                }
            }
        }

/*
    $zip = new ZipArchive();
    $zip->open($uploadPath);
    array_debug($zip);
    $sheet_xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $sheet_array = json_decode(json_encode($sheet_xml), true);
    $values = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
    $values_array = json_decode(json_encode($values), true);
    array_debug($sheet_array);
    die("made");

    $end_result = array();
    if ($sheet_array['sheetData']) {
        foreach ($sheet_array['sheetData']['row'] as $r => $row) {
            $end_result[$r] = array();
            foreach ($row['c'] as $c => $cell) {
                if (isset($cell['@attributes']['t'])) {
                    if ($cell['@attributes']['t'] == 's') {
                        $end_result[$r][] = $values_array['si'][$cell['v']]['t'];
                    } else if ($cell['@attributes']['t'] == 'e') {
                        $end_result[$r][] = '';
                    }
                } else {
                    $end_result[$r][] = $cell['v'];
                }
            }
        }
    }*/

    //$inputFileName = $upload_path . $filename;
    $objReader   = PHPExcel_IOFactory::createReader('Excel2007');
    $objPHPExcel = $objReader->load($uploadPath);

    $insert_sql  = returnReEstPendingInsert();
    $sql         = $insert_sql;
    $objPHPExcel->setActiveSheetIndex(1);
    $sheet = $objPHPExcel->getActiveSheet();
    //now do whatever you want with the active sheet

    $highest_row = $sheet->getHighestRow();
    $last_column = $sheet->getHighestColumn();
    $ship_code     = intval($sheet->getCell("A2")->getFormattedValue());
    deleteFromTable("meac", "reest_pending","ship_code", $ship_code);
    for ($i = 2; $i <= $highest_row; $i++) {
        $col = "A";

        $ship_code     = intval($sheet->getCell("A". $i)->getFormattedValue());
        $wp            = trim($sheet->getCell("D" . $i)->getFormattedValue());
        $item          = trim($sheet->getCell("E". $i)->getFormattedValue());
        $delta         = formatNumber4decNoComma($sheet->getCell("X". $i)->getFormattedValue());
        if($delta==0){
            continue;
        }
        if($ship_code==""){
            continue;
        }
        $sql .="
            (
            $ship_code,
            '$wp',
            '$item',
            $delta,
            '$user',
            $rpt_period),";
    }
    $sql = substr($sql, 0, -1);
    print $sql;
    $junk = dbCall($sql,"meac");
    die();
}
if($control=="accept_changes"){
    $today = date("Ymd");
    $destination_table = "z_".$today."_reest3";
    dropTable("z_meac", $destination_table);

    $source_table       = "reest3";
    $source_schema      = "meac";
    $destination_schema = "z_meac";
    duplicateTable($source_table, $source_schema, $destination_table, $destination_schema);

    $sql ="select ship_code, wp, item, eac_delta from reest_pending where ship_code = $ship_code";
    $insert_sql = "insert into reest3  (ship_code, wp,item, eac, inflation_eac, rpt_period) values ";
    $rs = dbCall($sql,"meac");
    $sql = $insert_sql;
    while (!$rs->EOF)
    {
        $ship_code = $rs->fields["ship_code"];
        $wp        = trim($rs->fields["wp"]);
        $item      = trim($rs->fields["item"]);
        $delta     = formatNumber4decNoComma($rs->fields["eac_delta"]);
        $sql.="
        (
            $ship_code,
            '$wp',
            '$item',
            $delta,
            $delta,
            $rpt_period
        ),";
        $rs->MoveNext();
    }

    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql,"meac");
    deleteFromTable("meac", "reest_pending","ship_code", $ship_code);
    die("made it");
}
if($control=="meac_eac_change_grid"){

    if($view =="all"){

        $sql = "
    select
            ship_code,
            wp,
            (select sum(inflation_eac) eac from reest3 re2 where re2.ship_code = re.ship_code and re.wp = re2.wp group by re.ship_code, wp)as prev_EAC,
            sum(eac_delta) delta
        from reest_pending re
         where re.ship_code = $ship_code
         and re.wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        group by ship_code, wp
union
  select
            re.ship_code,
            re.wp,
            sum(inflation_eac) prev_EAC,
            0 delta
        from reest3 re
          left join reest_pending pending
         on re.ship_code = pending.ship_code and re.wp= pending.wp
        where  re.ship_code = $ship_code 
        and  re.wp like '%matl%'
        and re.wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
        and pending.ship_code is null
        group by ship_code, wp";

    }
    elseif ($view =="pending"){


        $sql = "    
        select
            ship_code,
            wp,
            (select sum(inflation_eac) eac from reest3 re2 where re2.ship_code = re.ship_code and re.wp = re2.wp group by re.ship_code, wp)as prev_EAC,
            sum(eac_delta) delta
        from reest_pending re
        where ship_code = $ship_code
        and re.wp not in ('MATL-825-999','MATL-829-999', 'MATL-828-999')
        group by ship_code, wp
        ";


    }

    $rs = dbCall($sql,"meac");
    $data = "[";
    $id = 1;
    $total_diff = 0;
    $total_prev = 0;
    $total_new = 0;
    while (!$rs->EOF)
    {
        $ship_code  = $rs->fields["ship_code"];
        $wp         = $rs->fields["wp"];
        $prev_EAC   = formatNumber4decNoComma($rs->fields["prev_EAC"]);
        $delta      = $rs->fields["delta"];
        $new_eac    = formatNumber4decNoComma($prev_EAC + $delta);
        $total_diff += $delta;
        $total_prev += $prev_EAC;
        $total_new  += $new_eac;
        $data.="{
            \"id\"         : $id,
            \"ship_code\"  : \"$ship_code\",
            \"wp\"         : \"$wp\",
            \"prev_eac\"   : $prev_EAC,
            \"new_eac\"    : $new_eac,
            \"delta\"      : $delta
        },";
        $id++;
        $rs->MoveNext();
    }
    $data.="{
            \"id\"         : $id,
            \"ship_code\"  : \"$ship_code\",
            \"wp\"         : \"TOTAL \",
            \"prev_eac\"   : $total_prev,
            \"new_eac\"    : $total_new, 
            \"delta\"      : $total_diff
        }";
    $data.="]";
    die($data);
}
if($control=="excel_export"){
    $header_array = returnHeaders();
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("MEAC ITEM Level EAC");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);

        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sql = "
    SELECT
          ship_code,
          wp,
          item,
          inflation_eac eac,
          rpt_period
        FROM reest3
        WHERE ship_code = $ship_code
                and wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        and wp like '%MATL%'
        union ALL
        SELECT
          ship_code,
          wp,
          item,
          eac_delta eac,
          rpt_period
        FROM reest_pending
        WHERE ship_code = $ship_code
        and wp not in ('matl-825-999','MATL-829-999', 'MATL-828-999')
        and wp like '%MATL%'
    ";
    $rs = dbCall($sql,"meac");
    $data_start = 2;
    while (!$rs->EOF)
    {
        $header_col = "A";

        $ship_code  = $rs->fields["ship_code"];
        $wp         = $rs->fields["wp"];
        $item       = $rs->fields["item"];
        $eac        = $rs->fields["eac"];
        $rpt_period = $rs->fields["rpt_period"];


        $sheet->setCellValue($header_col++.$data_start, $ship_code);

        $sheet->setCellValue($header_col++.$data_start, $wp);
        $sheet->setCellValue($header_col++.$data_start, $item);
        $sheet->setCellValue($header_col.$data_start, $eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);
        $sheet->setCellValue($header_col++.$data_start, $rpt_period);

        $data_start++;
        $rs->MoveNext();
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/meac_eac_log".$token.".xlsx");
    $path = "../util/excel_exports/meac_eac_log".$token.".xlsx";
    die($path);
}
