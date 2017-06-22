<?php
include('../../../inc/inc.php');
include('../../../inc/inc.PHPExcel.php');
$debug             = false;
$path2_destination = "../util/csv_ceac";
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

if($control=="load_ceac_updates")
{

    //truncateTable("ceac", "`20170429`");`
    //$ceac_file_name_array["other_clins"] = "CEAC-2017-20170429 - Other Clins.xlsx";
    //$ceac_file_name_array["less_50"]     = "CEAC-2017-20170429 - Production - less than 50.xlsx";
    $ceac_file_name_array["prod"]        = "CEAC-2017-20170429 - Production.xlsx";

    foreach ($ceac_file_name_array as $new_file_name=>$path2xlsxfile){
        $path2file = $g_path2_ceac."/".$path2xlsxfile;
        require('../../../inc/lib/php/spreadsheet-reader-master/spreadsheet-reader-master/SpreadsheetReader.php');
        $insert_sql = "
            INSERT into CEAC.`20170429` 
            (ship_code, ca, wp, wo, item, scope, new_etc, new_eac, prev_etc, prev_eac, justification, adjustment)
            values ";
        $i = 0;
        $sql = $insert_sql;
        $Reader = new SpreadsheetReader($path2file);
        foreach ($Reader as $Row)
        {
            $ship_code     = addslashes(trim($Row[1]));
            if($ship_code == ""){
                continue;
            }
            $ca            = addslashes(trim($Row[8]));
            $wp            = addslashes(trim($Row[7]));
            $wo            = addslashes(trim($Row[18]));
            $item          = addslashes(trim($Row[19]));
            $scope         = addslashes(trim($Row[21]));
            $adjustment    = formatNumber4decNoComma($Row[50]);
            $new_etc       = formatNumber4decNoComma($Row[51]);
            $justification = addslashes(trim($Row[56]));
            $new_eac       = formatNumber4decNoComma($Row[52]);
            $prev_etc      = formatNumber4decNoComma($Row[43]);
            $prev_eac      = formatNumber4decNoComma($Row[33]);
            $sql.= "(                                                 
                '$ship_code',
                '$ca',
                '$wp',
                '$wo',
                '$item',
                '$scope',
                $new_etc,
                $new_eac,
                $prev_etc,
                $prev_eac,
                '$justification',
                $adjustment),";
            if($i==200){
                $sql = substr($sql, 0, -1);
                $junk = dbCall($sql,"ceac");
                $sql = $insert_sql;
                $i=0;
            }
            $i++;
        }
        if($i!=200){
            $sql = substr($sql, 0, -1);
            //print $sql;
            $junk = dbCall($sql,"ceac");
            $sql = $insert_sql;
        }
    }
}

if($control=="ceac_adj_analysis")
{
    $cur_rpt_period = currentRPTPeriod();
    $data = "[";
    $sql = "select id, rpt_period from fmm_evms.calendar rpt where rpt_period <= $cur_rpt_period order by rpt_period";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $id         = $rs->fields["id"];
        $rpt_period = $rs->fields["rpt_period"];
        $data.="{
            \"id\"          :$id,
            \"rpt_period\"        :$rpt_period
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}