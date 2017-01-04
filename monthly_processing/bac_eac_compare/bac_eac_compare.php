<?php
include("../../inc/inc.php");
include("../../inc/lib/php/phpexcel-1.8/classes/phpexcel.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = true;
/*
 * 1.  Run BAC/EAC Batch report from Cobra for the Ship.
 * 2.  Cut the Cur sheets into the Prev Sheets in the BAC EAC Workbook.
 * 3.  Copy one sheet CPR 1 DOllars workbook to the cur cpr 1 dollars in the BAC EAC WORKbook
 *
 *
 * */
//$g_path2_cpr     = "C:\\program_management_test\\cobra processing\\LCS13\\0473\\EAC-BAC Compare\\0473\\02-01D CPR 1 Dollars.xls";
//$g_path2_bac_eac = "C:\\program_management_test\\cobra processing\\LCS13\\0473\\0473 2016\\0473 11.16 Cobra Processing\\0473 Nov 2016 Reconciliations\\0473 Nov 2016 BAC and EAC Comparison-real.xlsx";




/*    foreach($objPHPExcel1->getSheetNames() as $sheetName)
    {
        print $sheetName;
        /*$sheet = $objPHPExcel1->getSheetByName($sheetName);
        $sheet->setTitle('Sheet'.$k);
        $objPHPExcel->addExternalSheet($sheet);
        unset($sheet);*/

/*

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objPHPExcel->setActiveSheetIndex(0);
    $file='x';

    $filename = $file."_".@date("Y-m-d_H-i",time()).'.xlsx';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
    $objWriter->save('php://output');  //send it to user, of course you can save it to disk also!
    exit; //done.. exiting!*/

if($control=="project_grid")
{

    //runCobraBatchReportProcess($ship_code,"test0473EACBACCompare", $g_path2CobraAPI,$g_path2BatrptCMD,$g_path2BatrptBAT,$debug);

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
            \"id\":$pmid,
            \"project_select\":0,
            \"project_name\":\"$name\",
            \"code\":\"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="status_grid")
{

    $data = "[";
    $sql = "

        select 
          ship.id as comment_id,
          st.id step_id,
          ship.ship_code ship_code,
          ship.status as status,
          ship.pfa_notes as pfa_notes,
          st.wi,
          st.step,
          timeline
        from processing_status.steps  st left join processing_status.ship ship
          on st.id = ship.step_id and ship.ship_code = $ship_code and ship.period = $rpt_period   
        order by step_id
  ";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        //die("made it");
        $comment_id   = $rs->fields["comment_id"];
        $step_id   = $rs->fields["step_id"];
        $status    = $rs->fields["status"];
        $pfa_notes = addslashes($rs->fields["pfa_notes"]);
        $wi        = addslashes($rs->fields["wi"]);
        $step      = addslashes($rs->fields["step"]);
        $timeline  = addslashes($rs->fields["timeline"]);
        if($status !=1)
        {
            $status = 0;
        }
        $data .= "{
            \"id\"          :$step_id,
            \"step_status\" :$status,
            \"code\"        :$ship_code,
            \"rpt_period\"  :$rpt_period,
            \"comment_id\"  :\"$comment_id\",
            \"wi\"          :\"$wi\",
            \"step\"        :\"$step\",
            \"timeline\"    :\"$timeline\",
            \"pfa_notes\"   :\"$pfa_notes\"
        },";
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}

