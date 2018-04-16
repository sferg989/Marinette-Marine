<?php
include('../../../inc/inc.php');
include('../../../inc/inc.cobra.php');
include('../../../inc/lib/php/PHPWord-develop/bootstrap.php');
//include('../../../inc/lib/php/PhpSpreadsheet-develop/src/Bootstrap.php');
require('C:\xampp\htdocs\fmg\inc\inc.PHPExcel.php');
session_write_close();


function getAllCAVarNeeded($ship_code,$rpt_period){
    $data_array = array();
    $sql = "
        select wbs,ca,cam 
        from ".$rpt_period."_top5_ca 
        where ship_code = $ship_code
        group by ca";
    $rs = dbCall($sql,"format_5");
    while(!$rs->EOF) {
        $wbs = $rs->fields["wbs"];
        $ca  = $rs->fields["ca"];
        $cam = $rs->fields["cam"];
        $data_array[$ca]["cam"] = $cam;
        $data_array[$ca]["wbs"] = $wbs;
        $rs->MoveNext();
    }
    return $data_array;
}
function getVarsAndValNeededForCA($ship_code, $ca, $rpt_period){
    $data_array = array();
    $sql = "
        select var, val
        from ".$rpt_period."_top5_ca 
        where ship_code = $ship_code
        and ca = '$ca'";
    //print $sql;
    $rs = dbCall($sql,"format_5");
    while(!$rs->EOF) {
        $var              = $rs->fields["var"];
        $val              = $rs->fields["val"];
        $data_array[$var] = $val;
        $rs->MoveNext();
    }
    return $data_array;
}
function returnHeaders(){
    $header_array[] = "Hull";
    $header_array[] = "WBS";
    $header_array[] = "CA";
    $header_array[] = "CAM";
    $header_array[] = "SUM OF CUR SV";
    $header_array[] = "SUM OF CUR CV";
    $header_array[] = "SUM OF CUM SV";
    $header_array[] = "SUM OF CUM CV";
    $header_array[] = "SUM OF VAC";
    return $header_array;
}
function getAllLengthsFromWBS($ship_code){
    $sql = "select length(wbs) len from format_5.wbs_17af WHERE  ship_code = $ship_code group by length(wbs) order by length(wbs) desc";
    //print $sql;
    $rs = dbCall($sql,"format_5");
    $data_array = array();
    while(!$rs->EOF) {
        $len = $rs->fields["len"];
        $data_array[] = $len;
        $rs->MoveNext();
    }
    //array_debug($data_array);
    return $data_array;
}

function getAllWBSWithLength($ship_code, $length){
    $sql = "select  wbs from format_5.wbs_17af WHERE  ship_code = $ship_code and length(wbs) = $length";
    $rs = dbCall($sql,"format_5");
    $data_array = array();
    while(!$rs->EOF) {
        $wbs = $rs->fields["wbs"];
        $data_array[] = $wbs;
        $rs->MoveNext();
    }
    return $data_array;
}
function returnCorbaWbsSQL($ship_code, $wbs=""){
    $ship_code = returnCobraProgram($ship_code);
    if($wbs!=""){
        $wbs_wc = "and CODE = '$wbs'";
    }
    else{
        $wbs_wc = "";
    }
    if($ship_code<477){
        $sql=" SELECT 
      p.PROGRAM,
      code wbs_id,
      codedesc,
     right(codedesc, len(codedesc) - charindex(':', codedesc)) as description,

      (select sum(BCWSCP) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cur_s,
      (select sum(bac) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as bac,
      (select sum(BCWpCP) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cur_p,
      (select sum(ACWPCP) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cur_a,
      (select sum(bcws) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as s,
      (select sum(bcwp) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as p,
      (select sum(acwp) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as a,
      (select sum(c.EAC) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as eac,
      (select (sum(c.BCWPCP) - sum(c.BCWSCP)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cur_sv,
      (select (sum(c.BCWPCP) - sum(c.ACWPCP)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cur_cv,
      (select (sum(c.BCWP) - sum(c.BCWS)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cum_sv,
      (select (sum(c.BCWP) - sum(c.ACWP)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as cum_cv,
      (select (sum(c.BAC) - sum(c.EAC)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, 8) = code and c.wp <> '' ) as vac
      FROM BDNDETL wbs LEFT JOIN program p
        ON p.CA_BD1 = wbs.BREAKFILE
    WHERE p.PROGRAM = '$ship_code' and LEN(code) = 8 
    $wbs_wc
    ";

        return $sql;
    }
    else{
        $sql = "";
        $wbs_inc_start = "and code in (";
        $length_array = getAllLengthsFromWBS($ship_code);
        foreach ($length_array as $length){
            $wbs_data_array = getAllWBSWithLength($ship_code, $length);
            $wbs_inc = $wbs_inc_start;
            foreach ($wbs_data_array as $wbs){

                $wbs_inc.="'$wbs',";
            }
            $wbs_inc = substr($wbs_inc, 0,-1);
            $wbs_inc.=")";
        $sql.=" SELECT 
                      p.PROGRAM,
                      code wbs_id,
                      codedesc,
                       right(codedesc, len(codedesc) - charindex(':', codedesc)) as description,
                      (select sum(BCWSCP) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cur_s,
                      (select sum(bac) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as bac,
                      (select sum(BCWpCP) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cur_p,
                      (select sum(ACWPCP) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cur_a,
                      (select sum(bcws) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as s,
                      (select sum(bcwp) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as p,
                      (select sum(acwp) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as a,
                      (select sum(c.EAC) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as eac,
                      (select (sum(c.BCWPCP) - sum(c.BCWSCP)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cur_sv,
                      (select (sum(c.BCWPCP) - sum(c.ACWPCP)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cur_cv,
                      (select (sum(c.BCWP) - sum(c.BCWS)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cum_sv,
                      (select (sum(c.BCWP) - sum(c.ACWP)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as cum_cv,
                      (select (sum(c.BAC) - sum(c.EAC)) from CAWP c where  p.PROGRAM = c.PROGRAM and left(c.ca1, $length) = code and c.wp <> '' ) as vac
                      FROM BDNDETL wbs LEFT JOIN program p
                        ON p.CA_BD1 = wbs.BREAKFILE
                    WHERE p.PROGRAM = '$ship_code' and LEN(code) = $length 
                    $wbs_wc
                    $wbs_inc
                    union";
        }
        $sql = substr($sql, 0,-5);
        return $sql;
    }

}

function getCobraDataWBS($ship_code, $wbs){
    $data_array = array();
    $sql = returnCorbaWbsSQL($ship_code,$wbs);
    $rs = dbCallCobra($sql);
    $data_array["wbs"]    = $rs->fields["wbs_id"];
    $data_array["code"]   = $rs->fields["codedesc"];
    $data_array["description"]   = $rs->fields["description"];
    $data_array["cur_s"]  = $rs->fields["cur_s"];
    $data_array["bac"]    = $rs->fields["bac"];
    $data_array["s"]      = $rs->fields["s"];
    $data_array["p"]      = $rs->fields["p"];
    $data_array["cur_p"]  = $rs->fields["cur_p"];
    $data_array["cur_a"]  = $rs->fields["cur_a"];
    $data_array["a"]      = $rs->fields["a"];
    $data_array["eac"]    = $rs->fields["eac"];
    $data_array["cur_sv"] = $rs->fields["cur_sv"];
    $data_array["cur_cv"] = $rs->fields["cur_cv"];
    $data_array["cum_sv"] = $rs->fields["cum_sv"];
    $data_array["cum_cv"] = $rs->fields["cum_cv"];
    $data_array["vac"]    = $rs->fields["vac"];
    $data_array["cur_cpi"]    = formatNumber4decNoComma($rs->fields["cur_p"]/$rs->fields["cur_a"]);
    $data_array["cum_cpi"]    = formatNumber4decNoComma($rs->fields["p"]/$rs->fields["a"]);
    $data_array["cum_spi"]    = formatNumber4decNoComma($rs->fields["p"]/$rs->fields["s"]);
    $data_array["cur_spi"] = formatNumber4decNoComma(($data_array["cur_p"]/$data_array["cur_s"]));

    return $data_array;
}
function evalVariance($var, $ten_pc, $type){
    if($type == "cur_cv" or $type == "cur_sv"){
        //print "type ".$type."<br>";
        //print "var ".$var."<br>";
        //print "ten_pc ".$ten_pc."<br>";
        if(abs($var) > 100000 and abs($var) > abs($ten_pc)){
            return true;
        }
        else{
            return false;
        }        
    }
    else{
        if(abs($var) > 150000 and abs($var) > abs($ten_pc)){
            return true;
        }
        else{
            return false;
        }
    }
}
function getVarRank($wbs, $var_type,$ship_code,$rpt_period){
    $sql = "select rank from ".$rpt_period."_top5 where ship_code = $ship_code and wbs = '$wbs' and var = '$var_type'";
    $rs = dbCall($sql, "format_5");
    $rank = $rs->fields["rank"];
    return $rank;
}
function insertCAExplanations($ship_code,$rpt_period, $var_array,$var_type){
    $insert_sql = "insert into format_5.".$rpt_period."_top5_ca_explanations 
        (ship_code,
        ca,
        var,
        explanation,
        action,
        impact,
        text) values ";
    $ca         = $var_array["ca"];
    $var_type   = $var_array["var_type"];
    $link  = createLink2Db("format_5");

    $text        = mysqli_real_escape_string($link, $var_array["variance"]);
    $explanation = mysqli_real_escape_string($link, $var_array["explanation"]);
    $action      = mysqli_real_escape_string($link, $var_array["action"]);
    $impact      = mysqli_real_escape_string($link, $var_array["impact"]);

    $insert_sql.="
        ($ship_code,
        '$ca',
        '$var_type',
        '$explanation',
        '$action',
        '$impact',
        '$text')
    ";
    if (mysqli_query($link, $insert_sql)) {
        //printf("%d Row inserted.\n", mysqli_affected_rows($link));
    }
}
function returnFontStyle($var_type,$val){
    $ten_pc = .1* $val;
    $var_trip = evalVariance($val, $ten_pc, $var_type);
    if($var_trip==true){
        $font_style = array(
            'size'=>7,
            "color" =>"000000",
            "bold"=>true);
        return $font_style;
    }
    else{
        $font_style = array(
            'size'=>7,
            "color" =>"000000",
            "bold"=>true);
        return $font_style;
    }
}
function returnCellStyle($var_type,$val){
    $ten_pc = .1* $val;
    $var_trip = evalVariance($val, $ten_pc, $var_type);
    if($var_trip==true){
        $cell_style = array(
            'valign' => 'center',
            'bgColor' => 'FF8080');
        return $cell_style;
    }
    else{
        $cell_style = array(
            'valign' => 'center');
        return $cell_style;
    }
}
function formatIntVal($val){
    $val = intval($val);
    $val = number_format($val,0,".",",");
    if($val <0){
        $val = str_replace("-", "(", $val);
        $val.=")";
    }
    return $val;
}
function createWBSLevelVarTable($phpWord,$wbs_data_array){
    $var_wbs_table_row_hight        = 250;
    $section = $phpWord->addSection(array('breakType' => 'continuous'));
    $section->addTextBreak(1);
    //array_debug($wbs_data_array);
    $wbs_code = $wbs_data_array["wbs"]." ".$wbs_data_array["code"];

    $cur_s   = $wbs_data_array["cur_s"];
    $cur_p   = $wbs_data_array["cur_p"];
    $cur_a   = $wbs_data_array["cur_a"];
    $cur_spi = formatNumber($wbs_data_array["cur_spi"]);
    $cur_cpi = formatNumber($wbs_data_array["cur_cpi"]);
    $cur_sv  = $wbs_data_array["cur_sv"];
    $cur_cv  = $wbs_data_array["cur_cv"];
    $cum_s   = $wbs_data_array["s"];
    $cum_p   = $wbs_data_array["p"];
    $cum_a   = $wbs_data_array["a"];
    $cum_sv  = $wbs_data_array["cum_sv"];
    $cum_cv  = $wbs_data_array["cum_cv"];
    $cum_spi = formatNumber($wbs_data_array["cum_spi"]);
    $cum_cpi = formatNumber($wbs_data_array["cum_cpi"]);
    $vac     = $wbs_data_array["vac"];
    $bac     = $wbs_data_array["bac"];
    $eac     = $wbs_data_array["eac"];
    $cum_cv_pc = "";
    $cur_cv_pc = "";
    $cur_sv_pc = "";
    $cum_sv_pc = "";
    $vac_pc = "";
    $tcpi2_bac = "";
    $tcpi2_eac = "";
    if($cum_p<> 0 ){
        $cum_cv_pc = intval(($cum_cv/$cum_p)*100);
    }
    if($cur_p<> 0){
        $cur_cv_pc = intval(($cur_cv/$cur_p)*100);
    }
    if($cur_s<>0){
        $cur_sv_pc = intval(($cur_sv/$cur_s)*100);
    }
    if($cum_s<>0){
        $cum_sv_pc = intval(($cum_sv/$cum_s)*100);
    }
    if($bac<> 0 ){
        $vac_pc = intval(($vac/$bac)*100);
    }
    if(($eac-$cum_a)<> 0 ){
        $tcpi2_bac = (($bac-$cum_p)/($bac-$cum_a));

    }
    if(($bac-$cum_a)<> 0 ){
        $tcpi2_eac = (($bac-$cum_p)/($eac-$cum_a));
    }

    $fancyTableStyle = array('borderSize' => 12,
                             'borderColor' => '000000',
                             'cellMargin' => 0, 'cellMarginRight' => 0, 'cellMarginBottom' => 0, 'cellMarginLeft' => 0,
                             'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);

    $fancyTableFirstRowStyle = array(
        "cantSplit" =>true,
        "color"=>"ffffff");
    $font_style = array(
        'size'=>7,
        "color" =>"000000",
        "bold"=>false);
    $no_bold_font_time = array(
        'size'=>7,
        "color" =>"000000",
        "bold"=>false);

    $header_font_style = array(
        'valign' => 'center',
        "bold"=>true,
        'size'=>8
    );
    $header_font_style_long_text = array(
        'valign' => 'center',
        "bold"=>true,
        'size'=>6
    );
    $header_cell_style = array(
        'valign' => 'center');
    $align_left = array('align' => 'left');
    $align_right = array('align' => 'right');
    $align_center = array('align' => 'center');

    $cellColSpan = array('gridSpan' => 10);
    $cell_width = 1000;
    $phpWord->addTableStyle("this is it", $fancyTableStyle, $fancyTableFirstRowStyle);

    $table = $section->addTable("this is it");
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell(10000, $cellColSpan)->addText(htmlspecialchars($wbs_code), $header_font_style);

    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));

    $table->addCell($cell_width)->addText("");
    $table->addCell($cell_width)->addText("Budget",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("Earned",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("Actuals",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("SV in $",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("SV in %",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("CV in $",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("CV in %",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("SPI",$header_font_style,$align_left);
    $table->addCell($cell_width)->addText("CPI",$header_font_style,$align_left);

    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($cell_width)->addText("Current:", $no_bold_font_time);
    $table->addCell($cell_width)->addText(formatIntVal($cur_s), $font_style,$align_right);
    $table->addCell($cell_width)->addText(formatIntVal($cur_p), $font_style,$align_right);
    $table->addCell($cell_width)->addText(formatIntVal($cur_a), $font_style,$align_right);
    $table->addCell($cell_width,returnCellStyle("cur_sv",$cur_sv))->addText(formatIntVal($cur_sv), returnFontStyle("cur_sv",$cur_sv),$align_right);
    $table->addCell($cell_width,returnCellStyle("cur_sv",$cur_sv))->addText(formatIntVal($cur_sv_pc)."%", returnFontStyle("cur_sv",$cur_sv_pc),$align_right);
    $table->addCell($cell_width,returnCellStyle("cur_cv",$cur_cv))->addText(formatIntVal($cur_cv), returnFontStyle("cur_cv",$cur_cv),$align_right);
    $table->addCell($cell_width,returnCellStyle("cur_cv",$cur_cv))->addText(formatIntVal($cur_cv_pc)."%", returnFontStyle("cur_cv",$cur_cv_pc),$align_right);
    $table->addCell($cell_width)->addText($cur_spi, $font_style,$align_right);
    $table->addCell($cell_width)->addText($cur_cpi, $font_style,$align_right);

    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($cell_width)->addText("Cumulative:", $no_bold_font_time);
    $table->addCell($cell_width)->addtext(formatIntVal($cum_s), $font_style,$align_right);
    $table->addCell($cell_width)->addtext(formatIntVal($cum_p), $font_style,$align_right);
    $table->addCell($cell_width)->addtext(formatIntVal($cum_a), $font_style,$align_right);
    $table->addCell($cell_width,returnCellStyle("cum_sv",$cum_sv))->addText(formatIntVal($cum_sv), returnFontStyle("cum_sv",$cum_sv),$align_right);
    $table->addCell($cell_width,returnCellStyle("cum_sv",$cum_sv))->addText(formatIntVal($cum_sv_pc)."%", returnFontStyle("cum_sv",$cum_sv_pc),$align_right);
    $table->addCell($cell_width,returnCellStyle("cum_cv",$cum_cv))->addText(formatIntVal($cum_cv), returnFontStyle("cum_cv",$cum_cv),$align_right);
    $table->addCell($cell_width,returnCellStyle("cum_cv",$cum_cv))->addText(formatIntVal($cum_cv_pc)."%", returnFontStyle("cum_cv",$cum_cv_pc),$align_right);
    $table->addCell($cell_width)->addtext($cum_spi, $font_style,$align_right);
    $table->addCell($cell_width)->addtext($cum_cpi, $font_style,$align_right);
    
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($cell_width)->addText("", $header_font_style);
    $table->addCell($cell_width)->addtext("BAC", $header_font_style,$align_left);
    $table->addCell($cell_width)->addtext("EAC", $header_font_style,$align_left);
    $table->addCell($cell_width)->addtext("VAC IN $", $header_font_style,$align_left);
    $table->addCell($cell_width)->addtext("VAC IN %", $header_font_style,$align_left);
    $table->addCell($cell_width, $header_cell_style)->addtext("TCPI TO BAC", $header_font_style_long_text,$align_center);
    $table->addCell($cell_width, $header_cell_style)->addtext("TCPI TO EAC", $header_font_style_long_text,$align_center);
    $table->addCell(3000, array('gridSpan' => 3))->addText("");
    
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($cell_width)->addText("At Complete:", $no_bold_font_time);
    $table->addCell($cell_width)->addtext(formatIntVal($bac), $font_style,$align_right);
    $table->addCell($cell_width)->addtext(formatIntVal($eac), $font_style,$align_right);
    $table->addCell($cell_width,returnCellStyle("vac",$vac))->addText(formatIntVal($vac), returnFontStyle("vac",$vac),$align_right);
    $table->addCell($cell_width,returnCellStyle("vac",$vac))->addText($vac_pc."%", returnFontStyle("vac",$vac_pc),$align_right);
    $table->addCell($cell_width)->addtext(formatNumber($tcpi2_bac), $font_style,$align_right);
    $table->addCell($cell_width)->addtext(formatNumber($tcpi2_eac), $font_style,$align_right);
    $table->addCell(3000, array('gridSpan' => 3))->addText("");

    return $phpWord;
}
function getExplanationsByWbsVarType($ship_code, $wbs,$var_type, $rpt_period){


    $sql = "
    SELECT
  e.ship_code,
  (select cam from cam_code m where m.code = c.cam limit 1) as cam,
  e.ca,
  e.var,
  explanation,
  action,
  impact,
  text
FROM ".$rpt_period."_top5_ca_explanations e
  left join `".$rpt_period."_top5_ca` c on
    c.ship_code = e.ship_code
  and e.var = c.var
  and trim(left(e.ca, locate(' / ', e.ca))) = trim(left(c.ca_long, locate(' / ', c.ca_long)))
WHERE e.ship_code = $ship_code
      AND e.ca LIKE '$wbs%'
      AND e.var = '$var_type'
    ";
    $rs = dbCall($sql, "format_5");
    return $rs;
}
function returnVarTypeCellStyle($var_type, $header=""){
    if($var_type=="cur_sv" or $var_type =="cum_sv"){
        if($header!=""){
            $data_style = array(
                "cantSplit" =>true,
                'valign' => 'center',
                'bgColor' => 'E36C0A');
        return $data_style;
        }
        else{
            $data_style = array(
                'valign' => 'center',
                "cantSplit" =>true,
                'bgColor' => 'FBD4B4'
            );

        }
        return $data_style;
    }
    if($var_type=="cur_cv" or $var_type =="cum_cv"){
        if($header!=""){
            $data_style = array(
                "cantSplit" =>true,
                'valign' => 'center',
                'bgColor' => '943634');
        return $data_style;
        }
        else{
            $data_style = array(
                'valign' => 'center',
                "cantSplit" =>true,
                'bgColor' => 'F2DBDB'
            );

        }
        return $data_style;
    }
    if($var_type=="vac" ){
        if($header!=""){
            $data_style = array(
                "cantSplit" =>true,
                'valign' => 'center',
                'bgColor' => '76933C');
            return $data_style;
        }
        else{
            $data_style = array(
                'valign' => 'center',
                "cantSplit" =>true,
                'bgColor' => 'D8E4BC'
            );

        }
        return $data_style;
    }
}

function createExplanationTables($phpWord, $wbs_data_array, $var_type, $ship_code,$rpt_period){
    $wbs = $wbs_data_array["wbs"];
    $rs = getExplanationsByWbsVarType($ship_code, $wbs,$var_type, $rpt_period);

    $header_font_style = array(
        "bold"=>true,
        'size'=>8,
        "color"=>"000000"
    );
    $bold_black = array(
        "bold"=>true,
        'size'=>8,
        "color"=>"000000"
    );
    $no_bold_black = array(
        "bold"=>false,
        'size'=>8,
        "color"=>"000000"
    );
    $bold_white= array(
        "bold"=>true,
        'size'=>8,
        "color"=>"FFFFFF"
    );
    $sv_data_cell_style = array(
        'bgColor' => 'E36C0A',
        "color" =>"000000"
    );
    $cv_table_style = array('borderSize' => 12,
                            'borderColor' => '000000',
                            'cellMargin' => 0, 'cellMarginRight' => 0, 'cellMarginBottom' => 0, 'cellMarginLeft' => 0,
                            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
    //array_debug($rs);
    $text_break_array = array(
        "lineHeight" =>.5
    );
    while(!$rs->EOF){
        $cam         = $rs->fields["cam"];
        $ca         = $rs->fields["ca"];

        $explantion = $rs->fields["explanation"];
        $action     = $rs->fields["action"];
        $impact     = $rs->fields["impact"];
        $text       = $rs->fields["text"];

        $var_wbs_table_row_hight        = 500;
        $section = $phpWord->addSection(array('breakType' => 'continuous'));
        $section->addTextBreak(1, array(),$text_break_array);
        $first_col= 1500;
        $second_col= 8000;
        $fancyTableStyle = array('borderSize' => 12,
                                 'borderColor' => '000000',
                                 'cellMargin' => 0, 'cellMarginRight' => 0, 'cellMarginBottom' => 0, 'cellMarginLeft' => 0,
                                 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);

        $fancyTableFirstRowStyle = array(
            "cantSplit" =>true,
            "color"=>"ffffff");
        
        $phpWord->addTableStyle("this is it", $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable("this is it");
        $no_space = array('spaceAfter' => 0);
        $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
        $table->addCell($first_col, returnVarTypeCellStyle($var_type, "header"))->addText("Control Account", $bold_white,$no_space);
        $table->addCell($second_col, returnVarTypeCellStyle($var_type))->addText(htmlspecialchars($ca), $bold_black,$no_space);

        $table->addRow($var_wbs_table_row_hight,array("cantSplit" =>true));
        $table->addCell($first_col, returnVarTypeCellStyle($var_type, "header"))->addText("CAM", $bold_white,$no_space);
        $table->addCell($second_col)->addText($cam, $no_bold_black,$no_space);

        $table->addRow($var_wbs_table_row_hight,array("cantSplit" =>true));
        $table->addCell($first_col, returnVarTypeCellStyle($var_type, "header"))->addText("Variance", $bold_white,$no_space);
        $table->addCell($second_col)->addText(htmlspecialchars($text), $no_bold_black,$no_space);

        $table->addRow($var_wbs_table_row_hight,array("cantSplit" =>true));
        $table->addCell($first_col, returnVarTypeCellStyle($var_type, "header"))->addText("Cause", $bold_white,$no_space);
        $table->addCell($second_col)->addText(htmlspecialchars($explantion), $no_bold_black,$no_space);

        $table->addRow($var_wbs_table_row_hight,array("cantSplit" =>true));
        $table->addCell($first_col, returnVarTypeCellStyle($var_type, "header"))->addText("Corrective action", $bold_white,$no_space);
        $table->addCell($second_col)->addText(htmlspecialchars($action), $no_bold_black,$no_space);

        $table->addRow($var_wbs_table_row_hight,array("cantSplit" =>true));
        $table->addCell($first_col, returnVarTypeCellStyle($var_type, "header"))->addText("Impact", $bold_white,$no_space);
        $table->addCell($second_col)->addText(htmlspecialchars($impact), $no_bold_black,$no_space);

        $rs->MoveNext();
    }
    
    return $phpWord;
}
function checkFormat5TablesAndDelete($rpt_period,$ship_code){
    $table_name = $rpt_period."_top5_ca";
    checkIfTableExists("fmm_evms", $table_name);
    $create_table = checkIfTableExists("format_5", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("format_5","template_top5_ca", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "format_5");

    $table_name = $rpt_period."_top5_ca_explanations";
    checkIfTableExists("fmm_evms", $table_name);
    $create_table = checkIfTableExists("format_5", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("format_5","template_top5_ca_explanations", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "format_5");

    $table_name = $rpt_period."_top5";
    checkIfTableExists("fmm_evms", $table_name);
    $create_table = checkIfTableExists("format_5", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("format_5","template_top5", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name, "format_5");
}
function determineVarSQL($var_type){
    if($var_type =="cur_sv"){
        $sql = "(BCWPCP - BCWsCP) ";
        return $sql;
    }
    if($var_type =="cur_cv"){
        $sql = "(BCWPCP - ACWPCP) ";
        return $sql;
    }
    if($var_type =="cum_sv"){
        $sql = "(BCWP - BCWS) ";
        return $sql;
    }
    if($var_type =="cum_cv"){
        $sql = "(BCWP - ACWP) ";
        return $sql;
    }
    if($var_type =="vac"){
        $sql = "(BAC - EAC) ";
        return $sql;
    }

}
function getCobraCANeedVars($var_amt , $ship_code, $wbs_id, $var_type,$rpt_period, $wbs){
    $table_name = $rpt_period."_top5_ca";
    $insert_sql = "insert into $table_name (ship_code, wbs,ca, var, val, cam, ca_long) VALUES ";

    $eighty = abs(.8*$var_amt);
    $ship_code = returnCobraProgram($ship_code);
    $ob = "";
    if($var_amt > 0){
        $ob = "desc";
    }
    $var_sql = determineVarSQL($var_type);
    $sql = "
        SELECT
          CA1 ca ,
          $var_sql AS var,
          manager manager,
          CONCAT(CA1,' / ',DESCRIP) as ca_long
        FROM CAWP
        WHERE CA1 LIKE '$wbs_id%' AND wp = '' AND PROGRAM = '$ship_code'
          ORDER BY $var_sql $ob";
    //print $sql;
    $rs = dbCallCobra($sql);
    $total = 0;
    $link = createLink2Db("format_5");
    while (!$rs->EOF)
    {
        $value   = $rs->fields["var"];
        $ca      = $rs->fields["ca"];
        $cam     = $rs->fields["manager"];
        $ca_long        = mysqli_real_escape_string($link, $rs->fields["ca_long"]);
        $total        += abs($value);
        //print "this is the running total ".$total."<br>";
        $insert_sql.=" ($ship_code, '$wbs','$ca', '$var_type', $value, '$cam','$ca_long'),";
        if(abs($total) >= $eighty){
            $insert_sql  = substr($insert_sql, 0, -1);
            mysqli_query($link, $insert_sql);
            return true;
        }
        $rs->MoveNext();
    }
}
function getNeededCASNeeded($ship_code, $rpt_period){
    $ca_array = array();
    $sql = "
    select ca,var from format_5.".$rpt_period."_top5_ca where ship_code = $ship_code
    ";
    $rs = dbCall($sql,"format_5");
    $i = 0;
    while (!$rs->EOF) {
        $ca = $rs->fields["ca"];
        $var= $rs->fields["var"];
        $ca_array[$var][] = $ca;
        $i++;
        $rs->MoveNext();
    }
    return $ca_array;
}
function getVarValue($ship_code, $var_type,$wbs){
    $sql = returnCorbaWbsSQL($ship_code, $wbs);

    $rs = dbCallCobra($sql);
    $cur_sv = $rs->fields["cur_sv"];
    $cur_cv = $rs->fields["cur_cv"];
    $cum_sv = $rs->fields["cum_sv"];
    $cum_cv = $rs->fields["cum_cv"];
    $vac    = $rs->fields["vac"];
    if($var_type=="cur_sv"){
        return $cur_sv;
    }
    if($var_type=="cur_cv"){
        return $cur_cv;
    }
    if($var_type=="cum_sv"){
        return $cum_sv;
    }
    if($var_type=="cum_cv"){
        return $cum_cv;
    }
    if($var_type=="vac"){
        return $vac;
    }

}
function getTop5NeededVarsInsert($ship_code, $rpt_period){
    $sql = returnCorbaWbsSQL($ship_code);
    $rs = dbCallCobra($sql);
    $top_5_array = array();
    while (!$rs->EOF)
    {
        $wbs    = $rs->fields["wbs_id"];
        $code   = $rs->fields["codedesc"];
        $cur_s  = $rs->fields["cur_s"];
        $bac    = $rs->fields["bac"];
        $s      = $rs->fields["s"];
        $p      = $rs->fields["p"];
        $cur_p  = $rs->fields["cur_p"];
        $cur_a  = $rs->fields["cur_a"];
        $a      = $rs->fields["a"];
        $eac    = $rs->fields["eac"];
        $cur_sv = $rs->fields["cur_sv"];
        $cur_cv = $rs->fields["cur_cv"];
        $cum_sv = $rs->fields["cum_sv"];
        $cum_cv = $rs->fields["cum_cv"];
        $vac    = $rs->fields["vac"];
        //print $cur_s."<br>";
        $cur_sv_ten_pc = .1* $cur_s;
        $cur_cv_ten_pc = .1* $cur_p;
        $cum_sv_ten_pc = .1* $s;
        $cum_cv_ten_pc = .1* $p;
        $vac_ten_pc    = .1* $bac;

        $cur_sv_var_trip = evalVariance($cur_sv, $cur_sv_ten_pc, "cur_sv");
        $cur_cv_var_trip = evalVariance($cur_cv, $cur_cv_ten_pc, "cur_cv");
        $cum_sv_var_trip = evalVariance($cum_sv, $cum_sv_ten_pc, "cum_sv");
        $cum_cv_var_trip = evalVariance($cum_cv, $cum_cv_ten_pc, "cum_cv");
        $vac_var_trip    = evalVariance($vac, $vac_ten_pc, "vac");

        if($cur_sv_var_trip== true){
            $top_5_array["cur_sv"][$wbs] = abs($cur_sv);
        }
        if($cur_cv_var_trip== true){
            $top_5_array["cur_cv"][$wbs] = abs($cur_cv);
        }
        if($cum_sv_var_trip== true){
            //print
            $top_5_array["cum_sv"][$wbs] = abs($cum_sv);
        }
        if($cum_cv_var_trip== true){
            $top_5_array["cum_cv"][$wbs] = abs($cum_cv);

        }
        if($vac_var_trip== true){
            $top_5_array["vac"][$wbs]    = abs($vac);
        }

        $rs->MoveNext();
    }
    //array_debug($top_5_array);
    //die();
    $final_top5_array = array();
    foreach ($top_5_array as $var_type=>$val_array){
        arsort($val_array);
        $counter = 1;
        foreach ($val_array as $wbs=>$var_amt){
            if($ship_code>475){
                if($counter <= 10){
                    $final_top5_array[$var_type][$wbs] = $var_amt;
                }
            }
            else{
                if($counter <= 5){
                    $final_top5_array[$var_type][$wbs] = $var_amt;
                }
            }
            $counter++;
        }
    }
    //array_debug($final_top5_array);
    $insert_sql = "insert into  ".$rpt_period."_top5 (ship_code, wbs, var, rank,val) values";
    foreach ($final_top5_array as $var_type =>$var_array){
        $i= 1;
        foreach ($var_array as $wbs_id=>$value){
            $val = getVarValue($ship_code, $var_type,$wbs_id);
            $insert_sql.="(
                $ship_code,
                '$wbs_id',
                '$var_type',
                $i,
                $val
            ),";
            $i++;
        }
    }

    $insert_sql  = substr($insert_sql, 0, -1);
    //print $insert_sql;
    $junk = dbCall($insert_sql, "format_5");
}
function getRanksWBSCode($ship_code,$rpt_period, $var_type){
    $sql = "select wbs from ".$rpt_period."_top5 where ship_code = $ship_code and var = '$var_type' order by rank ";
    $rs = dbCall($sql,"format_5");
    return $rs;
}
function determineExplanation($var_type,$sheet_var_type_explanation, $sheet, $row_num){
    $data = array();
    $variance_cell_location = $row_num + 2;
    $explanation_location   = $row_num + 3;
    $action                 = $row_num + 4;
    $impact                 = $row_num + 5;
    $ca                     = $row_num;

    if($sheet_var_type_explanation == "CURRENT PERIOD SCHEDULE VARIANCE" and $var_type=="cur_sv"){
        if(strlen($sheet->getCell("B" . $explanation_location)->getFormattedValue())>54){
            $data["var_type"] = $var_type;
            $data["variance"] = $sheet->getCell("B" . $variance_cell_location)->getFormattedValue();
            $data["explanation"] = $sheet->getCell("B" . $explanation_location)->getFormattedValue();
            $data["action"] = $sheet->getCell("B" . $action)->getFormattedValue();
            $data["impact"] = $sheet->getCell("B" . $impact)->getFormattedValue();
            $data["ca"] = $sheet->getCell("B" . $ca)->getFormattedValue();
            //$data["cam"] = $sheet->getCell("B" . $cam)->getFormattedValue();
        }
        else{
            return false;
        }
    }
    if($sheet_var_type_explanation == "CURRENT PERIOD COST VARIANCE" and $var_type=="cur_cv"){
        if(strlen($sheet->getCell("B" . $explanation_location)->getFormattedValue())>54){
            $data["var_type"] = $var_type;
            $data["variance"] = $sheet->getCell("B" . $variance_cell_location)->getFormattedValue();
            $data["explanation"] = $sheet->getCell("B" . $explanation_location)->getFormattedValue();
            $data["action"] = $sheet->getCell("B" . $action)->getFormattedValue();
            $data["impact"] = $sheet->getCell("B" . $impact)->getFormattedValue();
            $data["ca"] = $sheet->getCell("B" . $ca)->getFormattedValue();
            //$data["cam"] = $sheet->getCell("B" . $cam)->getFormattedValue();
        }
        else{
            return false;
        }
    }
    if($sheet_var_type_explanation == "CUMULATIVE SCHEDULE VARIANCE" and $var_type=="cum_sv"){
        if(strlen($sheet->getCell("B" . $explanation_location)->getFormattedValue())>54) {
            $data["var_type"]    = $var_type;
            $data["variance"]    = $sheet->getCell("B" . $variance_cell_location)->getFormattedValue();
            $data["explanation"] = $sheet->getCell("B" . $explanation_location)->getFormattedValue();
            $data["action"]      = $sheet->getCell("B" . $action)->getFormattedValue();
            $data["impact"]      = $sheet->getCell("B" . $impact)->getFormattedValue();
            $data["ca"]          = $sheet->getCell("B" . $ca)->getFormattedValue();
            //$data["cam"] = $sheet->getCell("B" . $cam)->getFormattedValue();
        }
        else{
                return false;
            }

    }
    if($sheet_var_type_explanation == "CUMULATIVE COST VARIANCE" and $var_type=="cum_cv"){
        if(strlen($sheet->getCell("B" . $explanation_location)->getFormattedValue())>54) {
            $data["var_type"]    = $var_type;
            $data["variance"]    = $sheet->getCell("B" . $variance_cell_location)->getFormattedValue();
            $data["explanation"] = $sheet->getCell("B" . $explanation_location)->getFormattedValue();
            $data["action"]      = $sheet->getCell("B" . $action)->getFormattedValue();
            $data["impact"]      = $sheet->getCell("B" . $impact)->getFormattedValue();
            $data["ca"]          = $sheet->getCell("B" . $ca)->getFormattedValue();
            //$data["cam"] = $sheet->getCell("B" . $cam)->getFormattedValue();
        }
    else{
            return false;
        }

    }
    if($sheet_var_type_explanation == "VARIANCE AT COMPLETION (VAC)" and $var_type=="vac"){
        if(strlen($sheet->getCell("B" . $explanation_location)->getFormattedValue())>54) {
            $data["var_type"] = $var_type;
            $data["variance"] = $sheet->getCell("B" . $variance_cell_location)->getFormattedValue();
            $data["explanation"] = $sheet->getCell("B" . $explanation_location)->getFormattedValue();
            $data["action"] = $sheet->getCell("B" . $action)->getFormattedValue();
            $data["impact"] = $sheet->getCell("B" . $impact)->getFormattedValue();
            $data["ca"] = $sheet->getCell("B" . $ca)->getFormattedValue();
            //$data["cam"] = $sheet->getCell("B" . $cam)->getCalculatedValue();
        }
        else{
            return false;
        }

    }

    return $data;
}
if($control =="ship_code")
{
    if($q!="")
    {
        $wc = "where period like '%$q%'";
    }
    else
    {
        $wc = "";
    }

    $data ="[";

    $sql = "select code from fmm_evms.master_project where active = 'true' ORDER BY code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF) {
        $code = $rs->fields["code"];
        $data.="{
            \"id\": $code,
            \"text\": \"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
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

    $data ="[";

    $sql = "select rpt_period from fmm_evms.calendar ORDER BY rpt_period";
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
    $data.="]";
    die($data);
}


if($control =="format5"){

    $year  = substr($rpt_period, 0, 4);
    $month = month2digit(substr($rpt_period, -2));

    $var_directory  = "Z:/Stephen Ferguson/vars/$year/$month/$ship_code";
    //mkdir($var_directory);
    if (!file_exists($var_directory)) {
        mkdir($var_directory, 0777, true);
    }
    deleteFromTable("format_5", $rpt_period."_top5_ca_explanations", "ship_code", $ship_code);
    $file_name_array = getListOfFileNamesInDirectory($var_directory);
    foreach ($file_name_array as $file_name){

        $objReader   = PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($var_directory."\\$file_name");


        /*$objPHPExcel->setActiveSheetIndex(0);
        $sheet       = $objPHPExcel->getActiveSheet();
        $highest_row = $sheet->getHighestRow();
        $ca_array = getNeededCASNeeded($ship_code, $rpt_period);

        for ($i = 2; $i <= $highest_row; $i++) {
            $col = "A";

            $ca_row = $sheet->getCell($col . $i)->getFormattedValue();
            $pos = strpos($ca_row , "Control Account");

            //echo "The string Control Account was not found in the string $ca_row<br>";
            if($pos!==false){
                //found it the ca row
                //found it the ca row
                $ca_sheet_val = $sheet->getCell("B" . $i)->getFormattedValue();
                foreach ($ca_array as $var_type=>$value){
                    //array_debug($value);
                    foreach ($value as $ca){
                        //print "this is the val in array ".$ca."<br>";
                        $ca_pos = strpos($ca_sheet_val, $ca);
                        if($ca_pos!== false ){
                            $z = intval($i-1);
                            $sheet_var_type_explanation = trim($sheet->getCell("A" .$z)->getFormattedValue());
                            $explanation_array = determineExplanation($var_type,$sheet_var_type_explanation, $sheet, $i);
                            if(count($explanation_array)>0){
                                //add the explanation to the word DOC.
                                //print "needed explanations $ca $var_type <br>";
                                //array_debug($explanation_array);
                                insertCAExplanations($ship_code,$rpt_period, $explanation_array,$var_type);
                            }

                            //found the needed ca section.
                            //print $variance."<br>";
                            //print $explanation."<br>";
                        }
                    }

                }

            }
        }*/
    }
    die("true");

}
if($control =="build_required_vars"){
    checkFormat5TablesAndDelete($rpt_period,$ship_code);
    getTop5NeededVarsInsert($ship_code, $rpt_period);
    $sql = "select wbs, var, rank, val from ".$rpt_period."_top5 where ship_code = $ship_code";
    $rs = dbCall($sql, "format_5");
    while (!$rs->EOF)
    {
        $wbs      = $rs->fields["wbs"];
        $var_type = $rs->fields["var"];
        $rank     = $rs->fields["rank"];
        $val      = $rs->fields["val"];
        $ca_array = getCobraCANeedVars($val, $ship_code, $wbs, $var_type, $rpt_period, $wbs);
        $table_name = $rpt_period."_top5_ca";
        $rs->MoveNext();
    }


    $header_array = returnHeaders();
    $objPHPExcel = new PHPExcel();
    // Set the active Excel worksheet to sheet 0
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle("CA Selection");
    $sheet->getTabColor()->setARGB('FF0094FF');
    $header_row= 1;
    $header_col = "A";

    foreach ($header_array as $header){
        $header = strtoupper($header);
        $sheet->setCellValue($header_col.$header_row, $header);
        colorCellHeaderTitleSheet($header_col++.$header_row, $sheet);
    }
    $data_start = 2;
    $ca_needed_array = getAllCAVarNeeded($ship_code,$rpt_period);
    foreach ($ca_needed_array as $ca=>$value){
        $cam = $value["cam"];
        $wbs = $value["wbs"];
        $header_col = "A";
        $sheet->setCellValue($header_col++.$data_start, $ship_code);
        $sheet->setCellValue($header_col++.$data_start, $wbs);
        $sheet->setCellValue($header_col++.$data_start, $ca);
        $sheet->setCellValue($header_col++.$data_start, $cam);
        $ca_var_array = getVarsAndValNeededForCA($ship_code, $ca, $rpt_period);
        foreach ($ca_var_array as $var=>$val){
            if($var == "cur_sv"){
                $col = "E";
            }if($var == "cur_cv"){
                $col = "F";
            }if($var == "cum_sv"){
                $col = "G";
            }if($var == "cum_cv"){
                $col = "H";
            }if($var == "vac"){
                $col = "I";
            }
            $sheet->setCellValue($col.$data_start, $val);
            phpExcelCurrencySheet($col.$data_start, $sheet);
        }
        $data_start++;
    }
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $token         = rand (0,1000);
    $objWriter->save("$g_path_to_util/excel_exports/format5_top5".$token.".xlsx");
    $path = "../util/excel_exports/format5_top5".$token.".xlsx";
    die($path);

}
if($control =="word_doc"){
    /*STEP 1 BUILD TOP 5 TABLE*/
    /*STEP 1 BUILD TOP 5 TABLE*/
    /*STEP 1 BUILD TOP 5 TABLE*/

    $var_wbs_table_header_row_hight = 300;
    $var_wbs_table_row_hight        = 200;
    $var_wbs_table_cell_width       = 1500;
    $var_wbs_table_wbs_cell_width   = 7500;
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    $section->addTextBreak(1);
    $fancyTableStyleName = 'Fancy Table';
    $fancyTableStyle = array('borderSize' => 12,
                             'borderColor' => '000000',
                             'cellMargin' => 0, 'cellMarginRight' => 0, 'cellMarginBottom' => 0, 'cellMarginLeft' => 0,
                             'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
    $cv_table_style = array('borderSize' => 12,
                             'borderColor' => '000000',
                             'cellMargin' => 0, 'cellMarginRight' => 0, 'cellMarginBottom' => 0, 'cellMarginLeft' => 0,
                             'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);

    $fancyTableFirstRowStyle = array(
         "cantSplit" =>true,
         'bgColor' => 'E36C0A',
        "color"=>"ffffff");
    $header_cell_style = array(
        'valign' => 'center',
        'bgColor' => 'E36C0A',
        "color" =>"ffffff");
    $sv_data_cell_style = array(
        'valign' => 'center',
        'bgColor' => 'FBD4B4',
        "color" =>"000000"
        );
    $cv_data_cell_style = array(
        'valign' => 'center',
        'bgColor' => 'F2DBDB',
        "color" =>"000000"
        );

    $wbs_data_cell_style = array(
        'valign' => 'center',
        'bgColor' => '404040',
        "border"=>1,
        "color" =>"ffffff");
    $rank_data_cell = array(
        'valign' => 'center',
        'bgColor' => '984806',
        "color" =>"ffffff");
    $rank_cv_data_cell = array(
        'valign' => 'center',
        'bgColor' => '632423',
        "color" =>"ffffff");
    $rank_vac_data_cell = array(
        'valign' => 'center',
        'bgColor' => '4F6228',
        "color" =>"ffffff");
    $cv_style = array(
        'valign' => 'center',
        'bgColor' => '943634',
        "color" =>"ffffff");
    $vac_style = array(
        'valign' => 'center',
        'bgColor' => '76923C',
        "color" =>"ffffff");


    $fancyTableFontStyle = array(
        'size'=>7,
        "color" =>"ffffff",
        "bold"=>true);
    $data_font = array(
        'size'=>5,
        "color" =>"000000",
        'align' => 'right');
    $align_left = array('align' => 'left');
    $align_right = array('align' => 'right');
    $align_center = array('align' => 'center');

    $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
    $phpWord->addTableStyle($fancyTableStyleName, $cv_table_style);
    $table = $section->addTable($fancyTableStyleName);
    $table->addRow($var_wbs_table_header_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $header_cell_style)->addText("TOP 5 Current Schedule Variances", $fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('Budget ', $fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('Earned ', $fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('Variance', $fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('SPI', $fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('TOP 5', $fancyTableFontStyle,$align_center);
    $total_cur_s = 0;
    $total_cur_p = 0;
    $total_cur_sv = 0;

    $sql = returnCorbaWbsSQL($ship_code);
    $rs  = dbCallCobra($sql);
    while (!$rs->EOF)
    {

        $wbs         = $rs->fields["wbs_id"];
        $code        = $rs->fields["codedesc"];
        $description = $rs->fields["description"];
        $cur_s       = $rs->fields["cur_s"];
        $bac         = $rs->fields["bac"];
        $s           = $rs->fields["s"];
        $p           = $rs->fields["p"];
        $cur_p       = $rs->fields["cur_p"];
        $cur_a       = $rs->fields["cur_a"];
        $a           = $rs->fields["a"];
        $eac         = $rs->fields["eac"];
        $cur_sv      = $rs->fields["cur_sv"];
        $cur_cv      = $rs->fields["cur_cv"];
        $cum_sv      = $rs->fields["cum_sv"];
        $cum_cv      = $rs->fields["cum_cv"];
        $vac         = $rs->fields["vac"];
        $code = $wbs." ".trim($description);
        $total_cur_s +=$cur_s;
        $total_cur_p +=$cur_p;
        $total_cur_sv +=$cur_sv;
        
        if($cur_s> 0 ){
            $cur_spi = formatNumber($cur_p/$cur_s);

        }else{
            $cur_spi = 1;
        }
        $cur_sv_rank = getVarRank($wbs, "cur_sv",$ship_code,$rpt_period);

        $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));

        $table->addCell($var_wbs_table_wbs_cell_width, $wbs_data_cell_style)->addText(htmlspecialchars($code),$fancyTableFontStyle);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatIntVal($cur_s/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatIntVal($cur_p/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatIntVal($cur_sv/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatNumber($cur_spi),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $rank_data_cell)->addText($cur_sv_rank,$fancyTableFontStyle,$align_right);
        $rs->MoveNext();
    }

    $total_cur_spi = number_format($total_cur_p/$total_cur_s,2);
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $header_cell_style)->addText( "Total",$fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("$".formatIntVal($total_cur_s/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("$".formatIntVal($total_cur_p/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("$".formatIntVal($total_cur_sv/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText(formatNumber($total_cur_spi),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("",$fancyTableFontStyle,$align_center);

    /*INDIVIDUAL WBS BREAKDOWN*/
    /*INDIVIDUAL WBS BREAKDOWN*/
    /*INDIVIDUAL WBS BREAKDOWN*/
    $rs  = getRanksWBSCode($ship_code,$rpt_period, "cur_sv");
    while (!$rs->EOF){
        $wbs_code = $rs->fields["wbs"];
        $wbs_data_array = getCobraDataWBS($ship_code, $wbs_code);
        $phpWord = createWBSLevelVarTable($phpWord, $wbs_data_array);
        $phpWord = createExplanationTables($phpWord, $wbs_data_array, "cur_sv", $ship_code, $rpt_period);

        $rs->MoveNext();
    }



    /*CUR CV*/
    /*CUR CV*/
    /*CUR CV*/
    /*CUR CV*/
    $sql = returnCorbaWbsSQL($ship_code);
    $rs = dbCallCobra($sql);
    $section = $phpWord->addSection(array('breakType' => 'continuous'));
    $section->addTextBreak(1);
    $phpWord->addTableStyle("CUR COST VAR", $cv_table_style);

    $table = $section->addTable("CUR COST VAR");
    $table->addRow($var_wbs_table_header_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $cv_style)->addText("TOP 5 Current Cost Variances", $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('ACWP ', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('BCWP', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('Variance', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('CPI', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('TOP 5', $fancyTableFontStyle);
    $total_cur_a  = 0;
    $total_cur_p  = 0;
    $total_cur_cv = 0;
    while (!$rs->EOF)
    {

        $wbs    = $rs->fields["wbs_id"];
        //print $wbs."<br>";
        $code   = $rs->fields["codedesc"];
        $description = $rs->fields["description"];

        $cur_s  = $rs->fields["cur_s"];
        $bac    = $rs->fields["bac"];
        $s      = $rs->fields["s"];
        $p      = $rs->fields["p"];
        $cur_p  = $rs->fields["cur_p"];
        $cur_a  = $rs->fields["cur_a"];
        $a      = $rs->fields["a"];
        $eac    = $rs->fields["eac"];
        $cur_sv = $rs->fields["cur_sv"];
        $cur_cv = $rs->fields["cur_cv"];
        $cum_sv = $rs->fields["cum_sv"];
        $cum_cv = $rs->fields["cum_cv"];
        $vac    = $rs->fields["vac"];
        $code = $wbs." ".trim($description);

        $total_cur_a +=$cur_a;
        $total_cur_p +=$cur_p;
        $total_cur_cv +=$cur_cv;

        if($cur_a> 0 ){
            $cur_cpi = formatNumber($cur_p/$cur_a);

        }else{
            $cur_cpi = 1;
        }
        $cur_cv_rank = getVarRank($wbs, "cur_cv",$ship_code,$rpt_period);

        $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));

        $table->addCell($var_wbs_table_wbs_cell_width, $wbs_data_cell_style)->addText( htmlspecialchars($code),$fancyTableFontStyle);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($cur_a/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($cur_p/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($cur_cv/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatNumber($cur_cpi),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $rank_cv_data_cell)->addText($cur_cv_rank,$fancyTableFontStyle,$align_right);
        $rs->MoveNext();
    }

    $total_cur_cpi = number_format($total_cur_p/$total_cur_a,2);
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $cv_style)->addText("Total",$fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("$".formatIntVal($total_cur_a/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("$".formatIntVal($total_cur_p/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("$".formatIntVal($total_cur_cv/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText(formatNumber($total_cur_cpi),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("",$fancyTableFontStyle,$align_center);

    $rs  = getRanksWBSCode($ship_code,$rpt_period, "cur_cv");
    while (!$rs->EOF){
        $wbs_code       = $rs->fields["wbs"];
        $wbs_data_array = getCobraDataWBS($ship_code, $wbs_code);
        $phpWord        = createWBSLevelVarTable($phpWord, $wbs_data_array);
        $phpWord        = createExplanationTables($phpWord, $wbs_data_array, "cur_cv", $ship_code, $rpt_period);
        $rs->MoveNext();
    }

    /*CUM SV*/
    /*CUM SV*/
    /*CUM SV*/
    /*CUM SV*/
    $section = $phpWord->addSection(array('breakType' => 'continuous'));
    $section->addTextBreak(1);
    $phpWord->addTableStyle("CUM SV", $fancyTableStyle, $fancyTableFirstRowStyle);
    $table = $section->addTable($fancyTableStyleName);
    //$table->addRow();
    $table->addRow($var_wbs_table_header_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $header_cell_style)->addText("TOP 5 Cumulative Schedule Variances", $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('Budget ', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('Earned ', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('Variance', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('SPI', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText('TOP 5', $fancyTableFontStyle);
    $total_cum_s = 0;
    $total_cum_p = 0;
    $total_cum_sv = 0;
    $sql = returnCorbaWbsSQL($ship_code);
    $rs  = dbCallCobra($sql);
    while (!$rs->EOF)
    {

        $wbs    = $rs->fields["wbs_id"];
        $code   = $rs->fields["codedesc"];
        $cur_s  = $rs->fields["cur_s"];
        $bac    = $rs->fields["bac"];
        $s      = $rs->fields["s"];
        $p      = $rs->fields["p"];
        $a      = $rs->fields["a"];
        $cur_p  = $rs->fields["cur_p"];
        $cur_a  = $rs->fields["cur_a"];
        $eac    = $rs->fields["eac"];
        $cur_sv = $rs->fields["cur_sv"];
        $cur_cv = $rs->fields["cur_cv"];
        $cum_sv = $rs->fields["cum_sv"];
        $cum_cv = $rs->fields["cum_cv"];
        $vac    = $rs->fields["vac"];
        $description = $rs->fields["description"];
        $code = $wbs." ".trim($description);
        //print "$codeThis is the cum sv".$cum_sv."<br>";
        $total_cum_s +=$s;
        $total_cum_p +=$p;
        $total_cum_sv +=$cum_sv;

        if($s> 0 ){
            $spi = formatNumber4decNoComma($p/$s);

        }else{
            $spi = 1;
        }
        $sv_rank = getVarRank($wbs, "cum_sv",$ship_code,$rpt_period);

        $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));

        $table->addCell($var_wbs_table_wbs_cell_width, $wbs_data_cell_style)->addText( htmlspecialchars($code),$fancyTableFontStyle);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatIntVal($s/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatIntVal($p/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatIntVal($cum_sv/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $sv_data_cell_style)->addText(formatNumber($spi),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $rank_data_cell)->addText($sv_rank,$fancyTableFontStyle,$align_right);
        $rs->MoveNext();
    }

    $spi = number_format($total_cum_p/$total_cum_s,2);
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $header_cell_style)->addText( "Total",$fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("$".formatIntVal($total_cum_s/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("$".formatIntVal($total_cum_p/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("$".formatIntVal($total_cum_sv/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText(formatNumber($spi),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $header_cell_style)->addText("",$fancyTableFontStyle,$align_center);




    /*INDIVIDUAL WBS BREAKDOWN*/
    /*INDIVIDUAL WBS BREAKDOWN*/
    /*INDIVIDUAL WBS BREAKDOWN*/
    $rs  = getRanksWBSCode($ship_code,$rpt_period, "cum_sv");
    while (!$rs->EOF){
        $wbs_code = $rs->fields["wbs"];
        $wbs_data_array = getCobraDataWBS($ship_code,$wbs_code);
        $phpWord = createWBSLevelVarTable($phpWord, $wbs_data_array);
        $phpWord = createExplanationTables($phpWord, $wbs_data_array, "cum_sv", $ship_code,$rpt_period);

        $rs->MoveNext();
    }

/*CUM COST VAR*/
/*CUM COST VAR*/
/*CUM COST VAR*/
/*CUM COST VAR*/
    $sql = returnCorbaWbsSQL($ship_code);
    $rs = dbCallCobra($sql);
    $section = $phpWord->addSection(array('breakType' => 'continuous'));
    $section->addTextBreak(1);
    $phpWord->addTableStyle("CUM COST VAR", $cv_table_style);

    $table = $section->addTable("CUM COST VAR");
    $table->addRow($var_wbs_table_header_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $cv_style)->addText("TOP 5 Cumulative Cost Variances", $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('ACWP ', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('BCWP', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('Variance', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('CPI', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText('TOP 5', $fancyTableFontStyle);

    $total_a  = 0;
    $total_p  = 0;
    $total_cv = 0;
    while (!$rs->EOF)
    {
        $wbs    = $rs->fields["wbs_id"];
        //print $wbs."<br>";
        $code   = $rs->fields["codedesc"];
        $cur_s  = $rs->fields["cur_s"];
        $bac    = $rs->fields["bac"];
        $s      = $rs->fields["s"];
        $p      = $rs->fields["p"];
        $cur_p  = $rs->fields["cur_p"];
        $cur_a  = $rs->fields["cur_a"];
        $a      = $rs->fields["a"];
        $eac    = $rs->fields["eac"];
        $cur_sv = $rs->fields["cur_sv"];
        $cur_cv = $rs->fields["cur_cv"];
        $cum_sv = $rs->fields["cum_sv"];
        $cum_cv = $rs->fields["cum_cv"];
        $vac    = $rs->fields["vac"];
        $description = $rs->fields["description"];
        $code = $wbs." ".trim($description);
        $total_a +=$a;
        $total_p +=$p;
        $total_cv +=$cum_cv;

        if($a> 0 ){
            $cpi = formatNumber4decNoComma($p/$a);

        }else{
            $cpi = 1;
        }
        $cv_rank = getVarRank($wbs, "cum_cv",$ship_code,$rpt_period);

        $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));

        $table->addCell($var_wbs_table_wbs_cell_width, $wbs_data_cell_style)->addText(htmlspecialchars($code),$fancyTableFontStyle);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($a/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($p/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($cum_cv/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatNumber($cpi),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $rank_cv_data_cell)->addText($cv_rank,$fancyTableFontStyle,$align_right);
        $rs->MoveNext();
    }
    $total_cpi = number_format($total_p/$total_a,2);
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $cv_style)->addText( "Total",$fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("$".formatIntVal($total_a/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("$".formatIntVal($total_p/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("$".formatIntVal($total_cv/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText(formatNumber($total_cpi),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $cv_style)->addText("",$fancyTableFontStyle,$align_center);
    $rs  = getRanksWBSCode($ship_code,$rpt_period, "cum_cv");
    while (!$rs->EOF){
        $wbs_code       = $rs->fields["wbs"];
        $wbs_data_array = getCobraDataWBS($ship_code, $wbs_code);
        //array_debug($wbs_data_array);

        $phpWord        = createWBSLevelVarTable($phpWord, $wbs_data_array);
        $phpWord        = createExplanationTables($phpWord, $wbs_data_array, "cum_cv", $ship_code, $rpt_period);
        $rs->MoveNext();
    }



    /*VAC COST VAR*/
    /*VAC COST VAR*/
    /*VAC COST VAR*/
    /*VAC COST VAR*/
    $sql = returnCorbaWbsSQL($ship_code);
    $rs = dbCallCobra($sql);
    $section = $phpWord->addSection(array('breakType' => 'continuous'));
    $section->addTextBreak(1);
    $phpWord->addTableStyle("VACVAR", $fancyTableStyle);
    $table = $section->addTable("VACVAR");

    $table->addRow($var_wbs_table_header_row_hight);
    $table->addCell($var_wbs_table_wbs_cell_width, $vac_style)->addText("TOP 5 Variance AT COMPLETE", $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText('Budget ', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText('EAC', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText('Variance', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText('TCPI', $fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText('TOP 5', $fancyTableFontStyle);
    $total_bac  = 0;
    $total_eac  = 0;
    $total_vac = 0;
    while (!$rs->EOF)
    {
        $wbs    = $rs->fields["wbs_id"];
        //print $wbs."<br>";
        $code   = $rs->fields["codedesc"];
        $cur_s  = $rs->fields["cur_s"];
        $bac    = $rs->fields["bac"];
        $s      = $rs->fields["s"];
        $p      = $rs->fields["p"];
        $cur_p  = $rs->fields["cur_p"];
        $cur_a  = $rs->fields["cur_a"];
        $a      = $rs->fields["a"];
        $eac    = $rs->fields["eac"];
        $cur_sv = $rs->fields["cur_sv"];
        $cur_cv = $rs->fields["cur_cv"];
        $cum_sv = $rs->fields["cum_sv"];
        $cum_cv = $rs->fields["cum_cv"];
        $vac    = $rs->fields["vac"];
        $description = $rs->fields["description"];
        $code = $wbs." ".trim($description);

        $total_bac +=$bac;
        $total_eac+=$eac;
        $total_vac +=$vac;
        $total_p +=$p;
        $total_a +=$a;

        if($a> 0 ){
            $tcpi = (($bac-$p))/(($bac-$a));

        }else{
            $tcpi = 1;
        }
        $vac_rank = getVarRank($wbs, "vac",$ship_code,$rpt_period);

        $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));

        $table->addCell($var_wbs_table_wbs_cell_width, $wbs_data_cell_style)->addText( htmlspecialchars($code),$fancyTableFontStyle);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($bac/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($eac/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatIntVal($vac/1000),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $cv_data_cell_style)->addText(formatNumber($tcpi),$data_font,$align_right);
        $table->addCell($var_wbs_table_cell_width, $rank_vac_data_cell)->addText($vac_rank,$fancyTableFontStyle,$align_right);
        $rs->MoveNext();
    }
    $total_tcpi = formatNumber(($total_bac-$total_p)/($total_bac-$total_a));
    $table->addRow($var_wbs_table_row_hight, array('exactHeight' => true));
    $table->addCell($var_wbs_table_wbs_cell_width, $vac_style)->addText( "Total",$fancyTableFontStyle);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText("$".formatIntVal($total_bac/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText("$".formatIntVal($total_eac/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText("$".formatIntVal($total_vac/1000),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText("".formatNumber($total_cpi),$fancyTableFontStyle,$align_center);
    $table->addCell($var_wbs_table_cell_width, $vac_style)->addText("",$fancyTableFontStyle,$align_center);
    $rs  = getRanksWBSCode($ship_code,$rpt_period, "vac");
    while (!$rs->EOF){
        $wbs_code       = $rs->fields["wbs"];
        $wbs_data_array = getCobraDataWBS($ship_code, $wbs_code);
        $phpWord        = createWBSLevelVarTable($phpWord, $wbs_data_array);
        $phpWord        = createExplanationTables($phpWord, $wbs_data_array, "vac", $ship_code, $rpt_period);
        $rs->MoveNext();
    }

    $token         = rand (0,1000);
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    //$objWriter->save("hello-".$token."World.docx");

    $objWriter->save("$g_path_to_util/excel_exports/format5_word_".$token.".docx");
    $path = "../util/excel_exports/format5_word_".$token.".docx";
    die($path);
}