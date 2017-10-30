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
function insertReESTADJ($rpt_period){
    $sql = "
    INSERT INTO meac.`201707_swbs_gl_summary_stage` (ship_code, buyer, wp, swbs, swbs_group, item, etc, category) (
  SELECT
    ship_code,
    CASE WHEN buyer_item IS NULL
      THEN buyer_ship
    ELSE buyer_item END AS buyer,
    wp,
    swbs,
    swbs_group,
    item,
    etc_diff,
    category
  FROM (
         SELECT
           s.ship_code,
           buyer_item,
           buyer_ship,
           wp,
           s.swbs,
           s.swbs_group,
           s.item,
           -(s.cur_etc - s.reEst_ETC) etc_diff,
           -(s.cur_eac - s.reEst_EAC) eac_diff,
           category
         FROM (
                SELECT
                  ship_code,
                  'ADJUSTMENTS in the TABLE' AS                             category,
                  (SELECT buyer
                   FROM 201707_item2buyer i
                   WHERE i.ship_code = cur.ship_code AND i.item = cur.item) buyer_item,
                  (SELECT buyer
                   FROM 201707_item2buyer i
                   WHERE I.item = cur.item
                   ORDER BY i.ship_code DESC
                   LIMIT 1)                                                 buyer_ship,
                  wp,
                  swbs,
                  swbs_group,
                  item,
                  COALESCE(sum(etc),0)                                                  cur_etc,

                  COALESCE((SELECT sum(etc)
                            FROM reest3 re
                            WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item and remaining = 'Yes'
                            GROUP BY re.ship_code, re.wp, re.item), 0)      reEst_ETC,
                  COALESCE((SELECT sum(eac)
                            FROM reest3 re
                            WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item
                            GROUP BY re.ship_code, re.wp, re.item), 0)      reEst_EAC,
                  COALESCE(sum(eac),0)                                                  cur_eac
                FROM meac.`201707_swbs_gl_summary_stage` cur
                -- WHERE
                -- ship_code = 473
                --   AND item = '986-01-DC100-001'
                GROUP BY ship_code, wp, item
                UNION
                SELECT
                  re.ship_code                           AS               ship_code,
                  'ADJUSTMENTS NOT IN TABLE'             AS               category,
                  (SELECT buyer
                   FROM item2buyer i
                   WHERE i.ship_code = re.ship_code AND i.item = re.item) buyer_item,
                  (SELECT buyer
                   FROM item2buyer i
                   WHERE I.item = re.item
                   ORDER BY i.ship_code DESC
                   LIMIT 1)                                               buyer_ship,
                  re.wp,
                  left(right(re.wp, 7), 3)               AS               swbs,
                  concat(left(right(re.wp, 7), 1), '00') AS               swbs_group,
                  re.item,
                  0                                      AS               cur_etc,
                  COALESCE(sum(re.etc),0)                                             reEst_ETC,
                  COALESCE(sum(re.eac),0)                                             reEst_EAC,
                  0                                      AS               cur_eac
                FROM reest3 re
                  LEFT JOIN `201707_swbs_gl_summary_stage` gl
                    ON re.ship_code = gl.ship_code
                       AND re.wp = gl.wp
                       AND re.item = gl.item
                WHERE
                  gl.ship_code IS NULL
                  AND gl.wp IS NULL
                  AND gl.item IS NULL
                  and remaining = 'yes'
                GROUP BY re.ship_code, re.wp, re.item
              ) s) final
  WHERE (final.etc_diff <> 0))";
    $junk = dbCall($sql,"meac");

    $sql= "INSERT INTO meac.`201707_swbs_gl_summary_stage` (ship_code, buyer, wp, swbs, swbs_group, item, eac, category) (
  SELECT
    ship_code,
    CASE WHEN buyer_item IS NULL
      THEN buyer_ship
    ELSE buyer_item END AS buyer,
    wp,
    swbs,
    swbs_group,
    item,
    eac_diff,
    category
  FROM (
         SELECT
           s.ship_code,
           buyer_item,
           buyer_ship,
           wp,
           s.swbs,
           s.swbs_group,
           s.item,
           -(s.cur_eac - s.reEst_EAC) eac_diff,
           category
         FROM (
                SELECT
                  ship_code,
                  'ADJUSTMENTS in the TABLE' AS                             category,
                  (SELECT buyer
                   FROM 201707_item2buyer i
                   WHERE i.ship_code = cur.ship_code AND i.item = cur.item) buyer_item,
                  (SELECT buyer
                   FROM 201707_item2buyer i
                   WHERE I.item = cur.item
                   ORDER BY i.ship_code DESC
                   LIMIT 1)                                                 buyer_ship,
                  wp,
                  swbs,
                  swbs_group,
                  item,
                  sum(etc)                                                  cur_etc,

                  COALESCE((SELECT sum(etc)
                            FROM reest3 re
                            WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item and remaining = 'Yes'
                            GROUP BY re.ship_code, re.wp, re.item), 0)      reEst_ETC,
                  COALESCE((SELECT sum(eac)
                            FROM reest3 re
                            WHERE re.ship_code = cur.ship_code AND re.wp = cur.wp AND re.item = cur.item
                            GROUP BY re.ship_code, re.wp, re.item), 0)      reEst_EAC,
                  COALESCE(sum(eac),0)                                                  cur_eac
                FROM meac.`201707_swbs_gl_summary_stage` cur
                -- WHERE
                -- ship_code = 473
                --   AND item = '986-01-DC100-001'
                GROUP BY ship_code, wp, item
             UNION
                SELECT
                  re.ship_code                           AS               ship_code,
                  'ADJUSTMENTS NOT IN TABLE'             AS               category,
                  (SELECT buyer
                   FROM item2buyer i
                   WHERE i.ship_code = re.ship_code AND i.item = re.item) buyer_item,
                  (SELECT buyer
                   FROM item2buyer i
                   WHERE I.item = re.item
                   ORDER BY i.ship_code DESC
                   LIMIT 1)                                               buyer_ship,
                  re.wp,
                  left(right(re.wp, 7), 3)               AS               swbs,
                  concat(left(right(re.wp, 7), 1), '00') AS               swbs_group,
                  re.item,
                  0                                      AS               cur_etc,
                  COALESCE(sum(re.etc),0)                                             reEst_ETC,
                  COALESCE(sum(re.eac),0)                                             reEst_EAC,
                  0                                      AS               cur_eac
                FROM reest3 re
                  LEFT JOIN `201707_swbs_gl_summary_stage` gl
                    ON re.ship_code = gl.ship_code
                       AND re.wp = gl.wp
                       AND re.item = gl.item
                WHERE
                  gl.ship_code IS NULL
                  AND gl.wp IS NULL
                  AND gl.item IS NULL
                GROUP BY re.ship_code, re.wp, re.item

              ) s) final
  WHERE (final.eac_diff <> 0));
    ";
    $junk = dbCall($sql,"meac");

}

$rpt_period = 201710;
buildMEACTablesforRptPeriod($rpt_period);


//duplicateTable("committed_po", "mars", "201708_committed_po", "mars");
//duplicateTable("ebom", "meac", "201708_ebom", "mars");
//duplicateTable("gl_detail", "mars", "201708_gl_detail", "mars");
//duplicateTable("open_buy", "mars", "201708_open_buy", "mars");
//duplicateTable("open_po", "mars", "201708_open_po", "mars");
//duplicateTable("reest3", "meac", "reest3_bkup20171002", "bkup");
//duplicateTable("reest3_bkup20171002", "bkup", "reest3", "meac");

//duplicateTable("201707_swbs_gl_summary", "meac", "201707_swbs_gl_summary_bkup", "bkup");
//die();
//duplicateTable("201708_swbs_gl_summary_bkup", "bkup", "201708_swbs_gl_summary_stage", "meac");
//die("made it");

$array = array();
//$array[] = 465;
//$array[] = 467;
//$array[] = 469;
$array[] = 471;
//$array[] = 473;
//$array[] = 475;
//$array[] = 477;
//$array[] = 479;
//$array[] = 481;
//$array[] = 483;
//$array[] = 485;

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
    deleteFromTable("MEAC", $rpt_period."_cbm", "ship_code", $ship_code);
    print $ship_code;
    insertCBMFromBaanRptPeriod($ship_code,$rpt_period);
}
deleteFromTable("meac", $rpt_period."_cbm", "material", "");

/*
 *
 * BUYER RESPONSIBLE
 * BUYER RESPONSIBLE
 * BUYER RESPONSIBLE
 * BUYER RESPONSIBLE
 *
 * */

truncateTable("meac", $rpt_period."_master_buyer");
loadBaanBuyerIDListRptPeriod($rpt_period);
print "finished Master Buyer";

foreach ($array as $value){
    deleteFromTable("MEAC", $rpt_period."_buyer_reponsible", "ship_code", $value);

    loadResponsibleBuyerRptPeriod($value, $rpt_period);
    print "finished Responsible Buyer $value";
}

loaditem2buyerRptPeriod($rpt_period);
/*
 * EFDB
 * EFDB
 * EFDB
 * EFDB
 * EFDB
 * */

foreach($array as $value){
    deleteFromTable("meac", $rpt_period."_change_item", "ship_code", $value);
    loadEFDBChangeBAANRptPeriod($value, $rpt_period);

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
    deleteFromTable("meac", $rpt_period."_inv_transfers", "ship_code", $ship_code);
    loadINVTranserfersRptPeriod($ship_code, $rpt_period);
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
    deleteFromTable("meac", $rpt_period."_po_data", "ship_code", $ship_code);
    loadFortisPODataRptPeriod($ship_code, $rpt_period);
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
truncateTable("meac", $rpt_period."_wp_gl_detail");
insertGLdetailWITHWPRptPeriod($rpt_period);

print "FINISHED GL DETAIL";
/*
 * OPEN PO
 * OPEN PO
 * OPEN PO
 * OPEN PO
 * ".$rpt_period."_
 * */
truncateTable("meac", $rpt_period."_wp_open_po");
insertOpenPOWithWPRptPeriod($rpt_period);

print "FINISHED OPEN PO";
/*
 * WP COMITTED po
 * WP COMITTED po
 * WP COMITTED po
 * WP COMITTED po
 * ".$rpt_period."_
 * */
truncateTable("meac", $rpt_period."_wp_committed_po");
insertCommittedPOWPRptPeriod($rpt_period);
print "FINISHED COMITTED PO";

/*
 * WP OPEN BUY
 * WP OPEN BUY
 * WP OPEN BUY
 * WP OPEN BUY
 * WP OPEN BUY
 * */
truncateTable("meac", $rpt_period."_wp_open_buy");
insertOpenBuyWithWPRptPeriod($rpt_period);

print "FINISHED OPEN BUY";

/*
 * WP EBOM
 * WP EBOM
 * WP EBOM
 * WP EBOM
 * WP EBOM
 * */
//truncateTable("meac", $rpt_period."_wp_ebom");
//insertEBOMWPRptPeriod($rpt_period);

print "FINISHED WP EBOM";


foreach ($array as $value){

    deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary_stage", "ship_code", $value);
    insertSWBSSummaryStagingRptPeriod($value,$rpt_period);
    print "finished Staging".$value;
}

//die("mdae it");
//insertReESTADJ($rpt_period);
foreach ($array as $value){
    deleteFromTable("MEAC", $rpt_period."_swbs_gl_summary", "ship_code", $value);
    insertSWBSSummaryRptPeriod($value, $rpt_period);
    print "finished Staging".$value;

}
deleteFromTableNotLike("MEAC", $rpt_period."_swbs_gl_summary", "wp", "matl");