<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.insert_data.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.baan.fortis.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.meac.excel.export.php');
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
$rpt_period = 201710;
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);

//truncateTable("meac", "po_data");
//loadFortisPOData();$array = array();

//$array[] = 465;
//$array[] = 467;
$array[] = 469;
//$array[] = 471;
//$array[] = 473;
//$array[] = 475;
//$array[] = 477;
//$array[] = 479;
//$array[] = 481;
//$array[] = 483;
//$array[] = 485;

$red = "e20909";
$white = "ffffff";
function colorWISheet($sheet){
    $i = 1;
    $sheet->SetCellValue("B".$i++, "New Open PO Value");
    $sheet->SetCellValue("B".$i++, "New Open PO Value NOT APPROVED IN FORTIS");
    $sheet->SetCellValue("B".$i++, "New Acutals This month");
    $sheet->SetCellValue("B".$i++, "Fortis Status Is run Live");
    $sheet->SetCellValue("B".$i++, "Open PO Value is from the first day after month end");
    $sheet->SetCellValue("B".$i++, "Notes Field consists of PO Approval Log Notes, and if it did not exist then it looks for a GL INV transfer.");
    $red= array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => "e20909")
        )
    );
    $blue= array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => "0a34db")
        )
    );
    $sheet->getStyle("A1")->applyFromArray($red);
    $sheet->getStyle("A2")->applyFromArray($blue);
    $sheet->getStyle("A3")->applyFromArray($red);

}
function getPONumLogNotesFortisStatus($item, $ship_code){
    $sql = "
        select
            s.po po,
            s.po_log_notes notes,
            coalesce((select status from po_data po where s.po = po.po order by modified_date desc LIMIT 1), '') as fortis_status
        from (
        SELECT
          GROUP_CONCAT(DISTINCT po) as po,
          GROUP_CONCAT(DISTINCT CONCAT(`reason_for_change`, '-', funding_source, '-', other_notes)) as po_log_notes
        
        FROM po_approval_log
        WHERE item = '$item' AND ship_code = $ship_code) s;
    ";
    $rs = dbCall($sql, "meac");
    $po             = $rs->fields["po"];
    $po_log_notes   = $rs->fields["notes"];
    $fortis_status  = $rs->fields["fortis_status"];
    $data = array();
    $data["po"]             = $po;
    $data["fortis_status"]  = $fortis_status;
    $data["notes"]          = $po_log_notes;
    return $data;
}
function getGlDoc($rpt_period, $item, $ship_code){
    $sql = "select document from ".$rpt_period."_wp_gl_detail where item = '$item' and ship_code = $ship_code order by `date` desc limit 1";
    print $sql;
    $rs = dbCall($sql, "meac");
    $doc= $rs->fields["document"];
    return $doc;
}
function getPONumFromItem($item,$ship_code, $rpt_period){
    $sql = "select po  from `".$rpt_period."_wp_open_po` WHERE  item = '$item' and ship_code = $ship_code";
    $rs = dbCall($sql,"meac");
    $po = $rs->fields["po"];
    if($po==""){
        $sql = "select `order` po from `".$rpt_period."_wp_gl_detail` WHERE  item = '$item' and ship_code = $ship_code";

        $rs = dbCall($sql,"meac");
        $po = $rs->fields["po"];
    }
    return $po;
}
function checkPOStatus($ship_code, $item,$rpt_period){
    $po     = getPONumFromItem($item, $ship_code,$rpt_period);
    $sql    = "select status from po_data WHERE po = $po order by modified_date desc limit 1";
    $rs     = dbCall($sql, "meac");
    $status = $rs->fields["status"];
    if($status==""){
        $status = "NOT IN FORTIS";
    }
    $data           = array();
    $data["status"] = $status;
    $data["po"]     = $po;
    return $data;
}
function getALlWPS($ship_code, $rpt_period)
{
    $wp_array =array();
    $sql = "select wp from 201707_swbs_gl_summary WHERE ship_code = $ship_code and wp like '%matl%' 
  and wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
group by wp
union
select wp from ".$rpt_period."_swbs_gl_summary WHERE ship_code = $ship_code and wp like '%matl%'
  and wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
group by wp
union
select wp from reest3 WHERE ship_code = $ship_code and wp like '%matl%'
  and wp not in ('MATL-825-999', 'MATL-829-999', 'MATL-828-999')
group by wp
";
    $rs  = dbCall($sql, "meac");
    while (!$rs->EOF) {

        $wp= trim($rs->fields["wp"]);
        $wp_array[] = $wp;
        $rs->MoveNext();
    }
    return $wp_array;
}
function calcNewETC($ebom, $prev_etc, $diff_a,$diff_open_po, $category){
    $change = $diff_a+$diff_open_po;
    if($category == "Vendor Service"){
        if($change <= 0){
            $new_etc = $prev_etc+abs($change);
        }
        else{
            $new_etc = floatval($prev_etc)-floatval($change);
            if($new_etc <1){
                $new_etc = 0;
            }
        }
        return $new_etc;
    }
    if($category != "Vendor Service"){
        if($change <= 0){
            $new_etc = $prev_etc+abs($change);
            return $new_etc;
        }
        else if($ebom <1){
            $new_etc = 0;
            return $new_etc;
        }
        else{
            $new_etc = floatval($prev_etc)-floatval($change);
            return $new_etc;
        }
    }
    return $new_etc;
}
function getFrozenEACWP($ship_code){
    $wp_array = array();
    $sql = "select wp from frozen_eac_wp where ship_code = '$ship_code'
    union 
    select wp from frozen_eac_wp where ship_code = 'All'
    ";
    $rs= dbCall($sql,"meac");
    while (!$rs->EOF)
    {
        $wp= $rs->fields["wp"];

        $wp_array[] = $wp;
        $rs->MoveNext();
    }
    return $wp_array;
}
function calcNewEAC2($wp_freeze_array, $new_etc, $cur_gl, $cur_open_po, $wp, $prev_eac, $diff_a, $diff_open_po){
    $change = $diff_a+$diff_open_po;
    $freeze = in_array($wp, $wp_freeze_array);
    if($freeze==true){
/*        if($prev_eac< ($cur_gl+$cur_open_po)){
            $new_eac = $cur_gl+$cur_open_po;
        }
        else{
            $new_eac = $prev_eac;
            //die("mad eit freeze else");
        }*/
        $new_eac = $prev_eac;
        return $new_eac;
    }
    else{
        if($new_etc<=0){
            $new_eac = $cur_gl+$cur_open_po;
            return $new_eac;
        }
        else if(intval($change)==0){
            $new_eac = $prev_eac;
            return $new_eac;
        }
        else{
            $new_eac = $cur_gl+$cur_open_po+ abs($new_etc);
            return $new_eac;
        }
        return $new_eac;
    }
}
function returnHeadersMEAC($cur_month_letters, $prev_month_letters){
    $header_array[] = "Hull";
    $header_array[] = "SWBS GROUP";
    $header_array[] = "SWBS";
    $header_array[] = "WP";
    $header_array[] = "Item";
    $header_array[] = "PREV ETC";
    $header_array[] = "PREV EAC";
    $header_array[] = "$prev_month_letters ACTUALS";
    $header_array[] = "$cur_month_letters ACTUALS";
    $header_array[] = "$prev_month_letters OPEN PO";
    $header_array[] = "$cur_month_letters OPEN PO";
    $header_array[] = "NEW ACTUALS THIS MONTH";
    $header_array[] = "NEW OPEN PO THIS MONTH";
    $header_array[] = "ETC DIFF";
    $header_array[] = "EAC DIFF";
    $header_array[] = "NEW EAC";
    $header_array[] = "NEW ETC";
    $header_array[] = "EBOM";
    $header_array[] = "PO";
    $header_array[] = "Log Comments";
    $header_array[] = "Gl Doc";
    $header_array[] = "Fortis Status";
    $header_array[] = "Proposed EAC";
    $header_array[] = "CHANGE";
    $header_array[] = "Comment";
    $header_array[] = "Bucket";
    return $header_array;
}
function returnHeadersMEACWP($cur_month_letters, $prev_month_letters){
    $header_array[] = "Hull";
    $header_array[] = "SWBS GROUP";
    $header_array[] = "SWBS";
    $header_array[] = "WP";
    $header_array[] = "PREV ETC";
    $header_array[] = "PREV EAC";
    $header_array[] = "$prev_month_letters ACTUALS";
    $header_array[] = "$cur_month_letters ACTUALS";
    $header_array[] = "$prev_month_letters OPEN PO";
    $header_array[] = "$cur_month_letters OPEN PO";
    $header_array[] = "NEW ACTUALS THIS MONTH";
    $header_array[] = "NEW OPEN PO THIS MONTH";
    $header_array[] = "ETC DIFF";
    $header_array[] = "EAC DIFF";
    $header_array[] = "NEW EAC";
    $header_array[] = "NEW ETC";
    return $header_array;
}

function rtnMEACDeltaSQL($rpt_period, $prev_rpt_period, $ship_code, $wp=""){
    if($ship_code>= 477){
        $wp1_exclude = "and prev.wp not in ('MATL-900-999')";
        $wp2_exclude  = "and cur.wp not in ('MATL-900-999')";
        $wp3_exclude  = "and reest.wp not in ('MATL-900-999')";
    }
    else{
        $wp1_exclude = "";
        $wp2_exclude = "";
        $wp3_exclude = "";
    }
    if($wp=="")
    {
        $wp1_wc = $wp1_exclude;
        $wp2_wc = $wp2_exclude;
        $wp3_wc = $wp3_exclude;
    }
    else{
        $wp1_wc = "AND prev.wp = '$wp' $wp1_exclude";
        $wp2_wc = "and cur.wp = '$wp' $wp2_exclude";
        $wp3_wc = "and reest.wp = '$wp' $wp3_exclude";
    }

    $sql = "
SELECT
      ship_code,
      concat(right(left(wp, 6), 1), '00') swbs_group,
              left(right(wp, 7),3) swbs,
      wp,
      category,
      var_ebom,
      item,
      prev_etc,
      prev_eac,
      prev_a,
      CUR_ACTUALS,
      prev_OPENPO,
      CUR_OPENPO,
      (CUR_ACTUALS- prev_a) AS     NEW_ACTUALS_THIS_MONTH,
      (CUR_OPENPO - prev_OPENPO)   AS     new_OPEN_PO_THIS_MONTH
    from (
        SELECT
    s2.ship_code,
      s2.wp,
      s2.item,
      s2.category,
      s2.var_ebom,
      (prev_eac - (prev_OPENPO+prev_a)) as prev_etc,
      s2.prev_eac,
      s2.prev_a,
      s2.CUR_ACTUALS,
      s2.prev_OPENPO,
      s2.CUR_OPENPO
    FROM (
            SELECT
                     prev.ship_code,
                     prev.wp,
                     prev.item,
                     prev.category,
                     coalesce((SELECT sum(var_ebom)
                               FROM `201710_swbs_gl_summary` meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS var_ebom,
                     coalesce((SELECT sum(inflation_eac)
                               FROM reest3 meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS prev_eac,
                     coalesce(sum(gl_int_amt), 0)                                                                            prev_a,
                     coalesce((SELECT sum(gl_int_amt)
                               FROM `201710_swbs_gl_summary` meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS CUR_ACTUALS,
                     coalesce(sum(open_po_pending_amt),
                              0)                                                                                             prev_OPENPO,
                     coalesce((SELECT sum(open_po_pending_amt)
                               FROM `201710_swbs_gl_summary` meac
                               WHERE meac.ship_code = prev.ship_code AND meac.wp = prev.wp AND meac.item = prev.item),
                              0)                                                                                          AS CUR_OPENPO
                   FROM `201709_swbs_gl_summary` prev 
                   WHERE prev.ship_code = $ship_code 
                   and prev.wp like '%matl%' 
                   and prev.wp <> 'matl-825-999' 
                   and prev.wp <> 'MATL-829-999' 
                   and prev.wp <> 'MATL-828-999'
                        $wp1_wc
                   GROUP BY ship_code, wp, item)  s2
    union
      /*
      ITEMS that are not in PREV but are in CUR
      */

    SELECT
      cur.ship_code,
      cur.wp,
      cur.item,
      cur.category,
      cur.var_ebom,
      coalesce((SELECT sum(inflation_etc)
       FROM `reest3` re
       WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item),0) AS prev_etc,
      coalesce((SELECT sum(inflation_eac)
       FROM `reest3` re
       WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item),0) AS prev_eac,
      0                                                                                 prev_a,
      coalesce(sum(cur.gl_int_amt),0)                                                            AS CUR_ACTUALS,
      0                                                                                 prev_OPENPO,
      coalesce(sum(cur.open_po_pending_amt),0)                                                   AS CUR_OPENPO


    from `201710_swbs_gl_summary` cur
    left join `201709_swbs_gl_summary` prev
      on cur.ship_code = prev.ship_code
    and cur.wp = prev.wp
    and cur.item = prev.item
    where
      cur.ship_code = $ship_code 
      and cur.wp like '%matl%'
      and cur.wp <> 'matl-825-999' 
      and cur.wp <> 'MATL-829-999' 
      and cur.wp <> 'MATL-828-999'
     $wp2_wc
    and prev.ship_code is null AND
      prev.ship_code is null
    and prev.item is null
    GROUP BY ship_code, wp, item
   UNION
      /*ITEMS THAT ARE NOT IN THE 201709 PERIOD OR IN 201710 PERIOD*/
    SELECT
          reest.ship_code,
          reest.wp,
          reest.item,
          ''             category,
          0             var_ebom,
          coalesce((SELECT sum(reest.inflation_etc)
                    FROM reest3 meac
                    WHERE meac.ship_code =
                          reest.ship_code AND
                          meac.wp = reest.wp AND
                          meac.item = reest.item AND
                          remaining = 'yes' limit 1),
                   0 ) AS prev_etc,
          coalesce(sum(inflation_eac),0) AS prev_eac,
          0              prev_a,
          0       AS CUR_ACTUALS,
          0              prev_OPENPO,
          0           AS CUR_OPENPO
        FROM reest3 reest LEFT JOIN
          201709_swbs_gl_summary prev ON
prev.ship_code = reest.ship_code 
AND prev.wp =reest.wp 
AND prev.item = reest.item
          LEFT JOIN `201710_swbs_gl_summary` cur ON
cur.ship_code=reest.ship_code AND
cur.wp = reest.wp
AND cur.item =reest.item
    WHERE reest.ship_code = $ship_code  
        and reest.wp like '%matl%'
        and reest.wp <> 'matl-825-999' 
        and reest.wp <> 'MATL-829-999' 
        and reest.wp <> 'MATL-828-999'
        and prev.ship_code IS NULL 
        AND cur.ship_code IS NULL 
         $wp3_wc
         group by reest.ship_code, reest.wp, reest.item
    ) s where s.ship_code is not NULL 
    ";

    return $sql;
}

$path2directory = "C:\\evms\\meac_delta";

//clearDirectory($path2directory);
$wp_freeze_array = getFrozenEACWP($ship_code);
foreach ($array as $ship_code){
    $data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);

    $cur_year           = $data["cur_year"];
    $ship_name          = $data["ship_name"];
    $prev_month_letters = $data["prev_month_letters"];
    $cur_month_letters  = $data["cur_month_letters"];
    $header_array = returnHeadersMEACWP($cur_month_letters, $prev_month_letters);

    $objPHPExcel = new PHPExcel();
// Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("WP Summary");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->SetCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $sheet->freezePane('G2');
    $wp_array= getALlWPS($ship_code, $rpt_period);

    //$wp_array2[] ="MATL-549-001";
    $data_start = 2;

    foreach ($wp_array as $wp){
        $wp_data = array();
        $wp_data = null;
        $sql = rtnMEACDeltaSQL($rpt_period,$prev_rpt_period, $ship_code, $wp);

        $rs = dbCall($sql,"meac");

        while (!$rs->EOF)
        {

            $swbs_group                     = trim($rs->fields["swbs_group"]);
            $category                       = trim($rs->fields["category"]);
            $swbs                           = trim($rs->fields["swbs"]);
            $item                           = trim($rs->fields["item"]);
            $ebom                           = $rs->fields["var_ebom"];
            $wp_data[$item]["prev_etc"]     = formatNumber4decNoComma($rs->fields["prev_etc"]);
            $wp_data[$item]["prev_a"]       = formatNumber4decNoComma($rs->fields["prev_a"]);
            $wp_data[$item]["cur_a"]        = formatNumber4decNoComma($rs->fields["CUR_ACTUALS"]);
            $wp_data[$item]["prev_open_po"] = formatNumber4decNoComma($rs->fields["prev_OPENPO"]);
            $wp_data[$item]["cur_open_po"]  = formatNumber4decNoComma($rs->fields["CUR_OPENPO"]);
            $wp_data[$item]["diff_a"]       = formatNumber4decNoComma($rs->fields["NEW_ACTUALS_THIS_MONTH"]);
            $wp_data[$item]["diff_open_po"] = formatNumber4decNoComma($rs->fields["new_OPEN_PO_THIS_MONTH"]);
            $wp_data[$item]["prev_eac"]     = formatNumber4decNoComma($rs->fields["prev_eac"]);
            /*
             * if eac was reduced last period, and there was no activity.  the etc = eac*/
            if($wp_data[$item]["prev_etc"]>$wp_data[$item]["prev_eac"]){
                $wp_data[$item]["prev_etc"] =  $wp_data[$item]["prev_eac"];
            }

            $wp_data[$item]["new_etc"]      = calcNewETC($ebom, $wp_data[$item]["prev_etc"], $wp_data[$item]["diff_a"], $wp_data[$item]["diff_open_po"], $category);
            $wp_data[$item]["new_eac"]      = calcNewEAC2($wp_freeze_array, $wp_data[$item]["new_etc"], $wp_data[$item]["cur_a"], $wp_data[$item]["cur_open_po"], $wp, $wp_data[$item]["prev_eac"],$wp_data[$item]["diff_a"], $wp_data[$item]["diff_open_po"]);
            if($wp_data[$item]["new_etc"]<0){
                //$etc_diff = $new_etc;
                $wp_data[$item]["etc_diff"]= $wp_data[$item]["new_etc"] ;
            }
            else{
                //$etc_diff     = ($new_etc - $prev_etc);
                $wp_data[$item]["etc_diff"]     = ($wp_data[$item]["new_etc"] - $wp_data[$item]["prev_etc"]);

            }
            //$etc_diff     = ($wp_data[$item]["new_etc"] - $wp_data[$item]["prev_etc"]);

            //$eac_diff     = ($new_eac - $prev_eac);
            $wp_data[$item]["eac_diff"]= ($wp_data[$item]["new_eac"] - $wp_data[$item]["prev_eac"]);

            if($wp_data[$item]["diff_open_po"]!=0){
                $status_array  = checkPOStatus($ship_code, $item, $rpt_period);
                $fortis_status = $status_array["status"];
                $po            = $status_array["po"];
                if($fortis_status=="Denied"){
                    $wp_data[$item]["diff_open_po"] = 0;
                }
            }


            $header_col = "A";
            $rs->MoveNext();
        }
        //array_debug($wp_data);

        $res = array();
        foreach($wp_data as $value) {
            foreach($value as $key => $number) {
                (!isset($res[$key])) ?
                    $res[$key] = $number :
                    $res[$key] += $number;
            }
        }
        //$res["diff_open_po"] = $res["cur_open_po"] - $res["prev_open_po"];
        //$res["new_etc"] = calcNewEAC($ebom, $new_etc, $cur_gl, $cur_open_po, $wp, $prev_eac)

        $prev_etc     = $res["prev_etc"];
        $prev_a       = $res["prev_a"];
        $cur_a        = $res["cur_a"];
        $prev_open_po = $res["prev_open_po"];
        $cur_open_po  = $res["cur_open_po"];
        $diff_a       = $res["diff_a"];
        $diff_open_po = $res["diff_open_po"];
        $prev_eac     = $res["prev_eac"];
        $new_etc      = $res["new_etc"];
        $new_eac      = $res["new_eac"];
        $eac_diff     = $res["eac_diff"];
        $etc_diff     = $res["etc_diff"];
        $prev_a       = $res["prev_a"];

        $freeze = in_array($wp, $wp_freeze_array);
        if($freeze==true){

            if(($cur_a+$cur_open_po)>$prev_eac){
                $new_eac  = $cur_a + $cur_open_po;
                $eac_diff = ($prev_eac - $new_eac);
            }
        }

        //die("made it");

        $sheet->SetCellValue($header_col++.$data_start, $ship_code);
        $sheet->SetCellValue($header_col++.$data_start, $swbs_group);
        $sheet->SetCellValue($header_col++.$data_start, $swbs);
        $sheet->SetCellValue($header_col++.$data_start, $wp);
        $sheet->SetCellValue($header_col.$data_start, $prev_etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $diff_a);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_a, "Approved");

        $sheet->SetCellValue($header_col.$data_start, $diff_open_po);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_open_po, "Approved");


        $sheet->SetCellValue($header_col.$data_start, $etc_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$etc_diff);

        $sheet->SetCellValue($header_col.$data_start, $eac_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$eac_diff);

        $sheet->SetCellValue($header_col.$data_start, $new_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);


        $sheet->SetCellValue($header_col.$data_start, $new_etc);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet, $etc_diff);

        $data_start++;
    }


    /**
     *
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     **DETail tab
     */

    $objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating
    $objPHPExcel->setActiveSheetIndex(1);
    $objWorkSheet->setTitle("Detail");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $sheet      = $objPHPExcel->getActiveSheet();

    $header_array = returnHeadersMEAC($cur_month_letters, $prev_month_letters);
    $header_row= 1;
    $header_col = "A";
    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->SetCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }

//$ship_code = "0481";
    $sheet->freezePane('F2');
    $sql = rtnMEACDeltaSQL($rpt_period,$prev_rpt_period,$ship_code);
    print "<br>";
    print "<br>";
    print "<br>";
    $rs = dbCall($sql,"meac");
    $data_start = 2;
    while (!$rs->EOF)
    {

        $swbs_group   = trim($rs->fields["swbs_group"]);
        $swbs         = trim($rs->fields["swbs"]);
        $wp           = trim($rs->fields["wp"]);
        $item         = trim($rs->fields["item"]);
        $prev_etc     = trim($rs->fields["prev_etc"]);
        $prev_a       = formatNumber4decNoComma($rs->fields["prev_a"]);
        $cur_a        = formatNumber4decNoComma($rs->fields["CUR_ACTUALS"]);
        $prev_open_po = formatNumber4decNoComma($rs->fields["prev_OPENPO"]);
        $cur_open_po  = formatNumber4decNoComma($rs->fields["CUR_OPENPO"]);
        $diff_a       = formatNumber4decNoComma($rs->fields["NEW_ACTUALS_THIS_MONTH"]);
        $diff_open_po = formatNumber4decNoComma($rs->fields["new_OPEN_PO_THIS_MONTH"]);
        $ebom         = formatNumber4decNoComma($rs->fields["var_ebom"]);
        $prev_eac     = formatNumber4decNoComma($rs->fields["prev_eac"]);
        if($prev_etc>$prev_eac){
            $prev_etc =  $prev_eac;
        }
        $new_etc      = calcNewETC($ebom, $prev_etc, $diff_a,$diff_open_po, $category);
        $new_eac      = calcNewEAC2($wp_freeze_array, $new_etc, $cur_a, $cur_open_po, $wp, $prev_eac,$diff_a,$diff_open_po);

        if($new_etc<0){
            $etc_diff = $new_etc;
        }
        else{
            $etc_diff     = $new_etc - $prev_etc;

        }
        $eac_diff = $new_eac - $prev_eac;
        $notes         = "";
        $po            = "";
        $gl_doc        = "";
        $fortis_status = "";
        if(intval($eac_diff)!=0){
            $po_data = getPONumLogNotesFortisStatus($item, $ship_code);

            $notes         = $po_data["notes"];
            $fortis_status = $po_data["fortis_status"];
            $po            = $po_data["po"];
        }

        if($diff_open_po!=0){
            $status_array  = checkPOStatus($ship_code, $item, $rpt_period);
            $fortis_status = $status_array["status"];
            $po            = $status_array["po"];
            if($fortis_status=="Denied"){
                $diff_open_po.=" NOT INCLUDED";
            }
        }
        if($diff_a<0){
            $gl_doc         = getGlDoc($rpt_period, $item, $ship_code);
        }
        $header_col = "A";
        $sheet->SetCellValue($header_col++.$data_start, $ship_code);
        $sheet->SetCellValue($header_col++.$data_start, $swbs_group);
        $sheet->SetCellValue($header_col++.$data_start, $swbs);
        $sheet->SetCellValue($header_col++.$data_start, $wp);
        $sheet->SetCellValue($header_col++.$data_start, $item);
        $sheet->SetCellValue($header_col.$data_start, $prev_etc);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_a);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $prev_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $cur_open_po);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);

        $sheet->SetCellValue($header_col.$data_start, $diff_a);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_a, "Approved");

        $sheet->SetCellValue($header_col.$data_start, $diff_open_po);
        phpExcelCurrencySheetBOLDAndCustomCOLORIFNOT0($header_col++.$data_start, $sheet,$red, $white,$diff_open_po, $fortis_status);


        $sheet->SetCellValue($header_col.$data_start, $etc_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$etc_diff);

        $sheet->SetCellValue($header_col.$data_start, $eac_diff);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet,$eac_diff);

        $sheet->SetCellValue($header_col.$data_start, $new_eac);
        phpExcelCurrencySheet($header_col++.$data_start, $sheet);


        $sheet->SetCellValue($header_col.$data_start, $new_etc);
        phpExcelCurrencySheetBOLDDiff($header_col++.$data_start, $sheet, $etc_diff);

        $sheet->SetCellValue($header_col++.$data_start, $ebom);
        $sheet->SetCellValue($header_col++.$data_start, $po);
        $sheet->SetCellValue($header_col++.$data_start, $notes);
        $sheet->SetCellValue($header_col++.$data_start, $gl_doc);
        $sheet->SetCellValue($header_col++.$data_start, $fortis_status);


        $data_start++;

        $rs->MoveNext();
    }
/*Work instructions
 *
 * */
    $objWorkSheet = $objPHPExcel->createSheet(2); //Setting index when creating
    $objPHPExcel->setActiveSheetIndex(2);
    $objWorkSheet->setTitle("Work Instructions");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $sheet      = $objPHPExcel->getActiveSheet();
    colorWISheet($sheet);

    $i= 1;
    foreach ($wp_freeze_array as $wp){
        $sheet->SetCellValue("C".$i, "FROZEN EAC");
        $sheet->SetCellValue("D".$i++, $wp);
    }
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    print "finished ".$ship_code;
    $objWriter->save("C:/evms/meac_delta/".$ship_code."- Tool ".$cur_month_letters." ".$cur_year.$token." MEAC Prelim.xlsx");
}


print time();