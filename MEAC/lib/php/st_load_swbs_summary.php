<?php
require('C:\xampp\htdocs\fmg\inc\inc.php');
require('C:\xampp\htdocs\fmg\inc\inc.cobra.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.insert_data.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\inc.baan.fortis.php');
require('C:\xampp\htdocs\fmg\MEAC\lib\php\functiobuilder.php');
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 3/9/2017
 * Time: 4:02 PM
 */
function saveListOfFileNamesPHPExcelAndInsertETCLoadFile($file_name_array,$directory2Copy,$rel_path2_desitnation)
{
    foreach ($file_name_array as $value) {
        $path2XLSX    = "$directory2Copy/$value";
        print $path2XLSX;
        $csv_filename = savePHPEXCELCSV($value, $path2XLSX, $rel_path2_desitnation);
        $path2file    = "$rel_path2_desitnation\\$csv_filename";
        insertETCLOADFILE($path2file);
        flush();
    }
}
function copyListOfDirectoryToCSV($directory2Copy,$rel_path2_reports){
    $file_name_array = getListOfFileNamesInDirectory($directory2Copy);
    saveListOfFileNamesPHPExcelAndInsertETCLoadFile($file_name_array,$directory2Copy,$rel_path2_reports);
}
$rpt_period = 201708;
buildMEACTablesforRptPeriod($rpt_period);


//duplicateTable("committed_po", "mars", "201708_committed_po", "mars");
//duplicateTable("ebom", "meac", "201708_ebom", "mars");
//duplicateTable("gl_detail", "mars", "201708_gl_detail", "mars");
//duplicateTable("open_buy", "mars", "201708_open_buy", "mars");
//duplicateTable("open_po", "mars", "201708_open_po", "mars");
//duplicateTable("201708_swbs_gl_summary", "meac", "201708_swbs_gl_summary_bkup", "bkup");
//duplicateTable("201708_swbs_gl_summary_bkup", "bkup", "201708_swbs_gl_summary_stage", "meac");
//die("made it");

//die("made it");

$array = array();
//$array[] = 465;
//$array[] = 467;
$array[] = 469;
$array[] = 471;
$array[] = 473;
$array[] = 475;
$array[] = 477;
$array[] = 479;
$array[] = 481;
$array[] = 483;
$array[] = 485;

/*
 * CBM
 * CBM
 * CBM
 * CBM
 * CBM
 * */

foreach ($array as $value){
    if(strlen($value)==3)
    {
        $ship_code = "0".$value;
    }
    //deleteFromTable("MEAC", $rpt_period."_cbm", "ship_code", $ship_code);
    print $ship_code;
    //insertCBMFromBaanRptPeriod($ship_code,$rpt_period);
}
//deleteFromTable("meac", $rpt_period."_cbm", "material", "");

/*
 *
 * BUYER RESPONSIBLE
 * BUYER RESPONSIBLE
 * BUYER RESPONSIBLE
 * BUYER RESPONSIBLE
 *
 * */

//truncateTable("meac", $rpt_period."_master_buyer");
//loadBaanBuyerIDListRptPeriod($rpt_period);

foreach ($array as $value){
    //deleteFromTable("MEAC", $rpt_period."_buyer_reponsible", "ship_code", $value);
    //loadResponsibleBuyerRptPeriod($value, $rpt_period);
}
//loaditem2buyerRptPeriod($rpt_period);

/*
 * EFDB
 * EFDB
 * EFDB
 * EFDB
 * EFDB
 * */

foreach($array as $value){
    //deleteFromTable("meac", $rpt_period."_change_item", "ship_code", $value);
    //loadEFDBChangeBAANRptPeriod($value, $rpt_period);

}


/*
 *INV Transfers
 *INV Transfers
 *INV Transfers
 *INV Transfers
 *INV Transfers
 *
 * */

foreach ($array as $ship_code){
    //deleteFromTable("meac", $rpt_period."_inv_transfers", "ship_code", $ship_code);
    //loadINVTranserfersRptPeriod($ship_code, $rpt_period);
}



/*
 *FORTIS PO LOADER
 *FORTIS PO LOADER
 *FORTIS PO LOADER
 *FORTIS PO LOADER
 *FORTIS PO LOADER
 *
 * */
foreach ($array as $ship_code){
    //deleteFromTable("meac", $rpt_period."_po_data", "ship_code", $ship_code);
    //loadFortisPODataRptPeriod($ship_code, $rpt_period);
}


/*END
/*END
/*END
/*END
 *
 * */

/*
 * CORRECT OPEN BUY SHOCK
 * CORRECT OPEN BUY SHOCK
 * CORRECT OPEN BUY SHOCK
 * CORRECT OPEN BUY SHOCK
 *
 * */
foreach ($array as $ship_code){
    correctShockOpenBuyItemShortageRptPeriod($ship_code, $rpt_period);
}

/*
 * WP TABLE LOADERS
 * WP TABLE LOADERS
 * WP TABLE LOADERS
 * WP TABLE LOADERS
 * */


/*
 * GL DETAIL
 * GL DETAIL
 * GL DETAIL
 * GL DETAIL
 * GL DETAIL */
//truncateTable("meac", $rpt_period."_wp_gl_detail");
//insertGLdetailWITHWPRptPeriod($rpt_period);

/*
 * OPEN PO
 * OPEN PO
 * OPEN PO
 * OPEN PO
 * ".$rpt_period."_
 * */
//truncateTable("meac", $rpt_period."_wp_open_po");
//insertOpenPOWithWPRptPeriod($rpt_period);


/*
 * WP COMITTED po
 * WP COMITTED po
 * WP COMITTED po
 * WP COMITTED po
 * ".$rpt_period."_
 * */
//truncateTable("meac", $rpt_period."_wp_committed_po");
//insertCommittedPOWPRptPeriod($rpt_period);

/*
 * WP OPEN BUY
 * WP OPEN BUY
 * WP OPEN BUY
 * WP OPEN BUY
 * WP OPEN BUY
 * */
//truncateTable("meac", $rpt_period."_wp_open_buy");
//insertOpenBuyWithWPRptPeriod($rpt_period);

/*
 * WP EBOM
 * WP EBOM
 * WP EBOM
 * WP EBOM
 * WP EBOM
 * */
//truncateTable("meac", $rpt_period."_wp_ebom");
//insertEBOMWPRptPeriod($rpt_period);
foreach ($array as $value){

    //deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
    //insertSWBSSummaryStagingRptPeriod($value,$rpt_period);
    print "finished Staging".$value;
}

$rel_path2_reports =    "../../../util/csv_etc_load_file";
$directory2Copy ="C:/evms/etc_load_file";
print $directory2Copy;
print $rel_path2_reports;
//deleteFromTable("meac", "swbs_gl_summary_stage", "category", "Load File Entry");
//clearDirectory($rel_path2_reports);
//copyListOfDirectoryToCSV($directory2Copy,$rel_path2_reports);

foreach ($array as $value){
    deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);
    insertSWBSSummaryRptPeriod($value, $rpt_period);
    print "finished Staging".$value;

}
deleteFromTableNotLike("MEAC", "swbs_gl_summary", "wp", "matl");