<?php
include('../../../../inc/inc.php');
function processJustification2($justification){
    $justification = trim($justification);
    $justification = str_replace("&", " and ", $justification);
    $justification = str_replace("\\", " and ", $justification);
    $justification = str_replace("\"", "'", $justification);
    $justification = str_replace("\t", '', $justification); // remove tabs
    $justification = str_replace("\n", '', $justification); // remove new lines
    $justification = str_replace("\r", '', $justification);
    return $justification;
}
function getFirstBCRByHulLWC($hull){
    switch ($hull) {
        case "0469":
            $wc = "bcr > 927";
        break;
        case "0471":
            $wc = "bcr > 778";
        break;
        case "0473":
            $wc = "bcr > 519";
        break;
        case "0475":
            $wc = "bcr > 482";
        break;
        case "0477":
            $wc = "bcr > 415";
        break;
        case "0479":
            $wc = "bcr > 349";
        break;
        case "0481":
            $wc = "bcr > 229";
        break;
        case "0483":
            $wc = "bcr > 79";
        break;
        case "0485":
            $wc = "bcr >37";
        break;

    }
    return $wc;
}
function checkVal($bcrh_change, $bcrd_change){
    $printval = true;
    if($bcrh_change>1){
        $printval = true;
    }
    if($bcrh_change<-1){
        $printval = true;
    }
    if($bcrd_change>1){
        $printval = true;
    }
    if($bcrd_change<-1){
        $printval = true;
    }
    return $printval;
}
function getActualBCRVALIntegrated($cur_bcr_table, $ship_code, $bcr){
    $sql = "
select sum(amount) amt,
            debit,
            credit
from (
      select
            ship_code,
            debit,
            credit,
            `desc`,
            amount
            from $cur_bcr_table bcr 
            where ship_code = $ship_code
                  and bcr.ca <> '' order by `desc`
      ) 
     s where s.desc like '%$bcr%' group by s.ship_code, s.debit
";
    //print $sql;
    $rs         = dbCall($sql);
    $count      = $rs->RecordCount();
    $type_array = array();
    if($count>1)
    {
        $i=0;
        while (!$rs->EOF){
            $actual_amt             = $rs->fields["amt"];
            $debit                  = $rs->fields["debit"];
            $credit                 = $rs->fields["credit"];
            $type_array[$i]["debit"]  = $debit;
            $type_array[$i]["credit"] = $credit;
            $type_array[$i]["amt"]    = $actual_amt;
            $i++;
            $rs->MoveNext();
        }

        //  var_dump($type_array[0]);
        $debit_field1    = $type_array[0]["debit"];
        $credit_field1   = $type_array[0]["credit"];
        $debit_field2    = $type_array[1]["debit"];
        $credit_field2   = $type_array[1]["credit"];
        $debit_fieldval  = $type_array[0]["amt"];
        $credit_fieldval = $type_array[1]["amt"];
        /*
        print $debit_fieldval;
        print $credit_fieldval;*/
        /*which value is bigger?*/
        $highes_val = max($debit_fieldval, $credit_fieldval);

        if($highes_val==$debit_fieldval){

            $amt = $debit_fieldval-$credit_fieldval;
            $data_array["debit"]    = $debit_field1;
            $data_array["credit"]   = $credit_field1;
            $data_array["amt"]      = $amt;
        }
        if($highes_val==$credit_fieldval){

            $amt = $credit_fieldval-$debit_fieldval;
          //  print $amt;
            $data_array["credit"]  = $credit_field2;
            $data_array["debit"]   = $debit_field2;
            $data_array["amt"]      = $amt;
        }
        //var_dump($data_array);
        return $data_array;

/*        if($debit_field1==$credit_field2){
            if($debit_fieldval == $credit_fieldval){
                return 0;
            }
            $data_array["amt"] = $debit_fieldval- $credit_fieldval;
        }
        else{

        }*/


    }
    $actual_amt = $rs->fields["amt"];
    $debit      = $rs->fields["debit"];
    $credit     = $rs->fields["credit"];

    $data_array["debit"]    = $debit;
    $data_array["credit"]   = $credit;
    $data_array["amt"]      = $actual_amt;
    //print $actual_amt;
    return $data_array;

}
function returnSQL($filter_val,$ship_code, $prev_table,$cur_table, $cur_bcr_table){
    if($filter_val=="all"){
        $sql = "
        select s.ship_code, s.ca, s.wp, prevbac, curbac, prevh, curh, `desc`, sum(hours) hours, sum(bcr.amount) as amount 
            from (
            select
                prev.ship_code,
                prev.ca,
                prev.wp,
                prev.bac prevbac,
                cur.bac curbac,
                prev.bac_hours prevh,
                cur.bac_hours curh
            from $prev_table prev
            INNER JOIN  $cur_table cur
                on prev.ship_code = cur.ship_code
                and prev.ca = cur.ca
                and prev.wp = cur.wp
            where
            prev.ship_code = $ship_code and cur.ship_code = $ship_code
            union
            /*
            Just CUR PERIOD
            */
            select
                cur.ship_code,
                cur.ca,
                cur.wp,
                0 prevbac,
                cur.bac curbac,
                0 prevh,
                cur.bac_hours curh
            from $cur_table cur
            left JOIN  $prev_table prev
                on prev.ship_code = cur.ship_code
                and prev.ca = cur.ca
                and prev.wp = cur.wp
            where
            prev.ship_code is null
            union
            /*
            Just PREV PERIOD
            */
            select
                prev.ship_code,
                prev.ca,
                prev.wp,
                prev.bac prevbac,
                0 curbac,
                prev.bac_hours prevh,
                0 curh
            from $prev_table prev
            left JOIN  $cur_table cur
                on prev.ship_code = cur.ship_code
                and prev.ca = cur.ca
                and prev.wp = cur.wp
            where
            cur.ship_code is null) s
        left join $cur_bcr_table bcr
            on bcr.ship_code = s.ship_code
            AND bcr.ca = s.ca
            AND bcr.wp = s.wp
            where s.ship_code = $ship_code
            and s.wp <> ''
        group by 
        s.ship_code, s.ca, s.wp order by s.`desc`
        ";
    }
    if($filter_val=="all_bcrs"){
        $sql = "
            select
            ship_code,
            ca,
            wp,
            (select bac from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevbac,
            (select bac from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curbac,
            (select bac_hours from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevh,
            (select bac_hours from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curh,
            `desc`,
            debit,
            credit,
            hours,
            amount
            from $cur_bcr_table bcr where ship_code = $ship_code and bcr.ca <> '' order by `desc`
        ";
    //print $sql;
    }
    if($filter_val=="no_ca"){
        $sql = "
            select
                ship_code,
                ca,
                wp,
                (select bac from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevbac,
                (select bac from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curbac,
                (select bac_hours from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevh,
                (select bac_hours from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curh,
                `desc`,
                debit,
                credit,
                hours,
                amount
            from $cur_bcr_table bcr where ship_code = $ship_code 
            and bcr.ca = ''
            order by `desc`
        ";
        //print $sql;
    }
    if($filter_val=="multiple_ca"){
        $sql = "
            select
            ship_code,
            ca,
            wp,
            (select bac from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevbac,
            (select bac from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curbac,
            (select bac_hours from $prev_table prev where prev.ca = bcr.ca and prev.ship_code= bcr.ship_code and prev.wp = bcr.wp ) prevh,
            (select bac_hours from $cur_table cur where cur.ca = bcr.ca and cur.ship_code= bcr.ship_code and cur.wp = bcr.wp ) curh,
            `desc`,
            debit,
            credit,
            hours,
            amount
            from $cur_bcr_table bcr where bcr.ship_code = $ship_code 
            and `desc` like '%and%' order by `desc`
        ";
        //print $sql;
    }
    return $sql;

}

if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$prev_rpt_period    = getPreviousRPTPeriod($rpt_period);
$data               = returnPeriodData($ship_code, $prev_rpt_period,$rpt_period);
$cur_year           = $data["cur_year"];
$cur_year_last2     = $data["cur_year_last2"];
$cur_month          = $data["cur_month"];
$cur_month_letters  = $data["cur_month_letters"];
$ship_name          = $data["ship_name"];

$path2_cobra_dir    = $base_path . "" . $ship_name . "/" . $ship_code ;
$cur_month_dir      = $path2_cobra_dir."/".$ship_code." ".$cur_year."/".$ship_code." ".$cur_month.".".$cur_year_last2." Cobra Processing";

if($control=="bcm")
{
    $prev_rpt_period = getPreviousRPTPeriod($rpt_period);
    $prev_table      = "cost2.`".$prev_rpt_period . "_cost`";
    $cur_table       = "cost2.`".$rpt_period . "_cost`";
    $cur_bcr_table   = "bcr_log.`".$rpt_period . "_bcr`";
    if(isset($filter_val)==false){
        $filter_val = "all_bcrs";
    }
    $sql = returnSQL($filter_val, $ship_code, $prev_table, $cur_table, $cur_bcr_table);
    //print $sql;
    $data = "[";

    $rs = dbCall($sql, "cost2");

    $id = 1;
    while (!$rs->EOF)
    {
        //die("made it");


        $ship_code   = $rs->fields["ship_code"];
        $ca          = $rs->fields["ca"];
        $wp          = $rs->fields["wp"];
        $desc        = $rs->fields["desc"];
        $prevbac     = formatNumberNoComma($rs->fields["prevbac"]);
        $curbac      = formatNumberNoComma($rs->fields["curbac"]);
        $prevh       = formatNumberNoComma($rs->fields["prevh"]);
        $curh        = formatNumberNoComma($rs->fields["curh"]);
        $bcrh        = formatNumberNoComma($rs->fields["hours"]);
        $bcrd        = formatNumberNoComma($rs->fields["amount"]);
        $change_h    = formatNumberNoComma($curh - $prevh);
        $change_d    = formatNumberNoComma($curbac - $prevbac);
        $bcrh_change = formatNumberNoComma(abs($change_h) - $bcrh);
        $bcrd_change = formatNumberNoComma(abs($change_d) - $bcrd);
        $printval = checkVal($bcrh_change, $bcrd_change);
        if($printval==true){

            $data .= "{
            \"id\"          :$id,
            \"ship_code\"   :\"$ship_code\",
            \"ca\"          :\"$ca\",
            \"wp\"          :\"$wp\",
            \"desc\"        :\"$desc\",
            \"prevh\"       :$prevh,
            \"curh\"        :$curh,
            \"prevbac\"     :$prevbac,
            \"curbac\"      :$curbac,
            \"change_h\"    :$change_h,
            \"bcrh\"        :$bcrh,
            \"bcrd\"        :$bcrd,
            \"change_d\"    :$change_d,
            \"bcrd_change\" :$bcrd_change,
            \"bcrh_change\" :$bcrh_change
        },";
            $id++;
        }
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}

if($control=="bcr"){
    $bcr_wc = getFirstBCRByHulLWC($ship_code);
    $cur_bcr_table   = "bcr_log.`".$rpt_period . "_bcr`";
    $sql = "
     select 
     ship_code,
     bcr, 
     mr,
     ub,
     db 
     from 
     processing_status.fortis_xml where ship_code = $ship_code  and $bcr_wc order by bcr";
    $data = "[";

    $rs = dbCall($sql, "processing_status");
    //var_dump($rs);
    $id = 1;
    while (!$rs->EOF)
    {
        //die("made it");
        $fortismr   = 0;
        $ub         = 0;
        $db         = 0;
        $fortis_db  = 0;
        $actual_val = 0;
        $bcr_val    = 0;
        $delta      = 0;
        $bcr            = $rs->fields["bcr"];
        $data_array     = getActualBCRVALIntegrated($cur_bcr_table, $ship_code, $bcr);
        $actual_val     = $data_array["amt"];

        $debit_field    = $data_array["debit"];
        $credit_field   = $data_array["credit"];
        //print $debit_field;
        $debit_field_val = -$actual_val;
        $credit_field_val= $actual_val;

        $fortismr         = $rs->fields["mr"];
        $fortis_ub         = $rs->fields["ub"];
        $fortis_db         = $rs->fields["db"];

        if($debit_field=="UB"){

            if($fortis_ub==0 and $fortismr!=0){
                $fortis_ub = $debit_field_val;
            }
            $ub_diff =$fortis_ub- $debit_field_val;
            $delta = $ub_diff;
            $ub =$debit_field_val;
            if($credit_field =="DB"){
                $db = $credit_field_val;
            }
        }
        if($debit_field=="DB"){
            //print "this worked".$debit_field_val;
            //print " Yess $credit_field ";
            if($credit_field =="UB"){
                $db = $debit_field_val;
                $ub = $credit_field_val;
            }
            if($fortis_ub ==0 and $fortis_db==0){
                $delta= $actual_val-$fortismr;
            }
        }
        //print $db;
        $data .= "{
            \"id\"             :$id,
            \"bcr\"            :\"$bcr\",
            \"mr\"             :\"$fortismr\",
            \"ub\"             :\"$ub\",
            \"fortis_ub\"      :\"$fortis_ub\",
            \"db\"             :\"$db\",
            \"fortis_db\"      :\"$fortis_db\",
            \"change\"         :\"$actual_val\",
            \"bcr_val\"        :\"$bcr_val\",
            \"bcr_delta\"      :\"$delta\"
        },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}

if($control=="log_analysis"){

    $path2file           = "$cur_month_dir/".$ship_code."LogAnalysis.xlsx";

    require('../../../../inc/lib/php/spreadsheet-reader-master/spreadsheet-reader-master/SpreadsheetReader.php');
    $i = 0;
    $sql = $insert_sql;
    $Reader = new SpreadsheetReader($path2file);
    $data = "[";
    //var_dump($Reader);
    //die("made it");
    foreach ($Reader as $Row)
    {
        $log_rpt_period = addslashes(trim($Row[0]));
        $bcr        = addslashes(trim($Row[2]));
        $pcw        = addslashes(trim($Row[4]));
        $mod        = addslashes(trim($Row[5]));
        $desc       = processJustification2(addslashes(trim($Row[7])));
        $auw        = addslashes(trim($Row[8]));
        $auw_fee    = addslashes(trim($Row[9]));
        $db         = formatNumber4decNoComma(trim($Row[10]));
        $mr         = formatNumber4decNoComma(trim($Row[11]));
        $ub         = formatNumber4decNoComma(trim($Row[12]));
        if(intval($rpt_period)==intval($log_rpt_period) and $mr!=0){
            $data .= "{
            \"id\"             :$i,
            \"rpt_period\"     :\"$rpt_period\",
            \"pcw\"            :\"$pcw\",
            \"mod\"            :\"$mod\",
            \"desc\"           :\"$desc\",
            \"auw\"            :\"$auw\",
            \"auw_fee\"        :\"$auw_fee\",
            \"bcr\"            :\"$bcr\",
            \"mr\"             :$mr,
            \"ub\"             :$ub,
            \"db\"             :$db
        },";
        }
        else{
            continue;
        }
        $i++;
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}


