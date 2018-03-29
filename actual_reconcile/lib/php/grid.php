<?php
include('../../../inc/inc.php');
include('../../../inc/inc.cobra.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
session_write_close();

function returnInsertSqlPoApprovalLog(){
    $sql = "insert into baan_file ()  VALUES";
    return $sql;
}
if($control=="upload"){

    $currentDir = getcwd();
    $uploadDirectory = "\uploads\\";
    $uploadDirectory = $g_path_to_util."uploads\\";
    clearDirectory($uploadDirectory);
    $errors = []; // Store all foreseen and unforseen errors here

    $fileExtensions = ['xlsx','jpg','png']; // Get all the file extensions

    $fileName = $_FILES['myfile']['name'];
    $fileSize = $_FILES['myfile']['size'];
    $fileTmpName  = $_FILES['myfile']['tmp_name'];
    $fileType = $_FILES['myfile']['type'];
    $fileExtension = strtolower(end(explode('.',$fileName)));

    $uploadPath = $uploadDirectory . basename($fileName);

    if (isset($fileName)) {

        if (! in_array($fileExtension,$fileExtensions)) {
            $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
        }

        /*    if ($fileSize > 2000000) {
                $errors[] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
            }*/

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
    truncateTable("fmm_evms", "baan_file");

    //$inputFileName = $upload_path . $filename;
    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
    //$objReader->setReadDataOnly(true);
    $objPHPExcel = $objReader->load($uploadPath);
    $insert_sql = returnInsert();
    $sql = $insert_sql;
    for ($sheet_index = 0; $sheet_index <= 2; $sheet_index++) {
        $objPHPExcel->setActiveSheetIndex($sheet_index);
        $sheet = $objPHPExcel->getActiveSheet();
        //now do whatever you want with the active sheet

        $highest_row = $sheet->getHighestRow();
        $last_column = $sheet->getHighestColumn();
        for ($i = 2; $i <= $highest_row; $i++) {
            $col = "A";

            $ship_code         = intval($sheet->getCell($col++.$i)->getValue());
            $date              = fixExcelDateMySQL($sheet->getCell($col++.$i)->getFormattedValue());

            $week              = $sheet->getCell($col++.$i)->getFormattedValue();
            $po                = intval($sheet->getCell($col++.$i)->getValue());
            $buyer             = $sheet->getCell($col++.$i)->getValue();
            $wp                = $sheet->getCell($col++.$i)->getValue();
            $swbs              = intval($sheet->getCell($col++.$i)->getFormattedValue());
            $item              = $sheet->getCell($col++.$i)->getValue();
            $val               = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $etc               = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $change            = formatNumber4decNoComma($sheet->getCell($col++.$i)->getFormattedValue());
            $qty               = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $ebom              = formatNumber4decNoComma($sheet->getCell($col++.$i)->getValue());
            $remaining         = formatNumber4decNoComma($sheet->getCell($col++.$i)->getFormattedValue());
            $cam               = $sheet->getCell($col++.$i)->getValue();
            $reason_for_change = $sheet->getCell($col++.$i)->getValue();
            $funding_source    = $sheet->getCell($col++.$i)->getValue();
            $other_notes       = processDescription($sheet->getCell($col++.$i)->getFormattedValue());
            $sql .="
                (
                $ship_code,
                '$date',
                '$week',
                $po,
                '$buyer',
                '$wp',
                $swbs,
                '$item',
                $val,
                $etc,
               $change,
                $qty,
                $ebom,
                $remaining,
                '$cam',
                '$reason_for_change',
                '$funding_source',
                '$other_notes'),";
            if($i % 500==0)
            {
                $sql = substr($sql, 0, -1);
                $junk = dbCall($sql,"meac");
                $insert_sql = returnInsertSqlPoApprovalLog();
                $sql = $insert_sql;
            }

        }
        if($i!=500){
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,"meac");
            $insert_sql = returnInsertSqlPoApprovalLog();
            $sql = $insert_sql;
        }
    }
    die();
}
function getCobraTphaseData($program, $cawpid, $class,$rsrc){
    $sql = "
        SELECT
      t.program,
    t.CLASS class,
      c.ca1,
      c.WP wp,
      t.cecode rsrc,
      sum(HOURS) hours,
      sum(DIRECT) dir,
      sum(OH) oh,
      sum(COM) com,
      sum(GANDA) sga,
      sum(SYSGA) sgacom
    
    FROM tphase t
      left join CAWP c
      on t.PROGRAM = c.PROGRAM
      and t.CAWPID = c.CAWPID
    WHERE t.PROGRAM = '$program'
          and t.CAWPID = $cawpid
          and t.class = '$class'
          and t.cecode = '$rsrc'
    group by t.PROGRAM,t.CLASS,c.CA1,c.wp,t.CECODE;
    ";
    $rs = dbCallCobra($sql);
    $data_array = array();
    while (!$rs->EOF)
    {
        $wp     = $rs->fields["wp"];
        $class  = $rs->fields["class"];
        $rsrc   = $rs->fields["rsrc"];
        $hours  = $rs->fields["hours"];
        $dir    = $rs->fields["dir"];
        $oh     = $rs->fields["oh"];
        $com    = $rs->fields["com"];
        $sga    = $rs->fields["sga"];
        $sgacom = $rs->fields["sgacom"];

        $data_array["dir"]    = $dir;
        $data_array["hours"]  = $hours;
        $data_array["oh"]     = $oh;
        $data_array["com"]    = $com;
        $data_array["sga"]    = $sga;
        $data_array["sgacom"] = $sgacom;

        $rs->MoveNext();
    }
    return $data_array;
}
if($control=="a_check"){
$sql = "     select * from a_load_file where wp = 'MATL-583-001'";
    $rs = dbCall($sql,"fmm_evms");

    while (!$rs->EOF)
    {
        $ca     = $rs->fields["ca"];
        $wp     = $rs->fields["wp"];
        $class  = $rs->fields["class"];
        $rsrc   = $rs->fields["rsrc"];
        $hours  = $rs->fields["hours"];
        $dir    = $rs->fields["dir"];
        $oh     = $rs->fields["oh"];
        $com    = $rs->fields["com"];
        $sga    = $rs->fields["sga"];
        $sgacom = $rs->fields["sgacom"];
        $cawpid = getCAWPID("0473", $wp);
        if($cawpid==null or $cawpid ==""){
            print "This does not exist in COBRA $wp";
        }
        $data_array = getCobraTphaseData("0473", $cawpid, $class,$rsrc );
        array_debug($data_array);
        $diff1 = intval($data_array["dir"]- $dir);
        $diff2 = intval($data_array["hours"]- $hours);
        $diff3 = intval($data_array["oh"]- $oh);
        $diff4 = intval($data_array["com"]- $com);
        $diff5 = intval($data_array["sga"]- $sga);
        $diff6 = intval($data_array["sgacom"]- $sgacom);
        if( $diff1 != 0 or  $diff2 != 0 or  $diff3 != 0 or  $diff4 != 0 or  $diff5 != 0 or  $diff6 != 0 ){
            print "THis is a problem : <br>";
            print $wp."<br>";
            print $class."<br>";
            print $rsrc."<br>";
        }

        $rs->MoveNext();
    }

    die("I made it");
}

