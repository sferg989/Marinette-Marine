<?php
include('../../../inc/inc.php');
include('../../../meac/lib/php/inc.baan.fortis.php ');


$rpt_period = currentRPTPeriod();

if($control=="project_grid")
{
    $sql = "
        SELECT
          *,
          (s.c_amnt - s.meac_re_est_etc) etc_diff
        FROM
          (SELECT
             ship_code,
             wp,
             item,
             description,
             po,
             line,
            (SELECT vendor_name
              FROM swbs_gl_summary swbs
              WHERE c.vendor= swbs.vendor_id limit 1) vendor,
             order_qty                                                                      c_order_qty,
             c.unit_price                                                                   c_unit_price,
             coalesce(c.commit_amnt, 0) AS                                                  c_amnt,
             (SELECT c_qty
              FROM swbs_gl_summary swbs
              WHERE c.ship_code = swbs.ship_code AND c.wp = swbs.wp AND c.item = swbs.item) meac_c_qty,
             (SELECT ebom
              FROM swbs_gl_summary swbs
              WHERE c.ship_code = swbs.ship_code AND c.wp = swbs.wp AND c.item = swbs.item) meac_ebom,
             (SELECT var_ebom
              FROM swbs_gl_summary swbs
              WHERE c.ship_code = swbs.ship_code AND c.wp = swbs.wp AND c.item = swbs.item) meac_var_ebom,
             (SELECT last_unit_price
              FROM swbs_gl_summary swbs
              WHERE c.ship_code = swbs.ship_code AND c.wp = swbs.wp AND c.item = swbs.item) meac_last_price,
             (SELECT last_unit_price_ship
              FROM swbs_gl_summary swbs
              WHERE c.ship_code = swbs.ship_code AND c.wp = swbs.wp AND c.item = swbs.item) last_price_hull_from_meac_tool,
             coalesce((SELECT etc
                       FROM 201708_swbs_gl_summary meac
                       WHERE c.ship_code = meac.ship_code AND c.wp = meac.wp AND c.item = meac.item),
                      0)                                                                    meac_re_est_etc,
             (SELECT eac
              FROM 201708_swbs_gl_summary meac
              WHERE c.ship_code = meac.ship_code AND c.wp = meac.wp AND c.item = meac.item) meac_re_est_eac,
             (SELECT status
              FROM po_data po
              WHERE c.ship_code = po.ship_code AND c.po = po.po
              ORDER BY modified_date DESC
              LIMIT 1)                                                                      fortis_status
        
           FROM wp_baan_committed_po c) s where po = $po
        
        ORDER BY po, line";
    $rs  = dbCall($sql, "meac");

    $count = $rs->RecordCount();
    if($count==0){
        $data = "
            [{
                \"id\"                  : 1,
                \"ship_code\"           : \"NO RECORDS\"
            }]
        ";
    die($data);
    }
    $data = "[";
    $id = 0;
    while (!$rs->EOF)
    {
        $ship_code       = $rs->fields["ship_code"];
        $wp              = $rs->fields["wp"];
        $item            = $rs->fields["item"];
        $description     = processDescription($rs->fields["description"]);
        $po              = $rs->fields["po"];
        $line            = $rs->fields["line"];
        $vendor          = $rs->fields["vendor"];
        $c_order_qty     = formatNumber4decNoComma($rs->fields["c_order_qty"]);
        $c_unit_price    = formatNumber4decNoComma($rs->fields["c_unit_price"]);
        $c_amnt          = formatNumber4decNoComma($rs->fields["c_amnt"]);
        $meac_c_qty      = formatNumber4decNoComma($rs->fields["meac_c_qty"]);
        $meac_ebom       = formatNumber4decNoComma($rs->fields["meac_ebom"]);
        $meac_var_ebom   = formatNumber4decNoComma($rs->fields["meac_var_ebom"]);
        $meac_last_price = formatNumber4decNoComma($rs->fields["meac_last_price"]);
        $meac_re_est_etc = formatNumber4decNoComma($rs->fields["meac_re_est_etc"]);
        $meac_re_est_eac = formatNumber4decNoComma($rs->fields["meac_re_est_eac"]);
        $fortis_status   = $rs->fields["fortis_status"];
        $etc_diff        = formatNumber4decNoComma($rs->fields["etc_diff"]);
        $data.="{
            \"id\"                  : $id,
            \"ship_code\"           :\"$ship_code\",
            \"wp\"                  :\"$wp\",
            \"item\"                :\"$item\",
            \"desc\"                :\"$description\",
            \"po\"                 :\"$po\",
            \"line\"               :\"$line\",
            \"vendor\"             :\"$vendor\",
            \"c_order_qty\"        :\"$c_order_qty\",
            \"c_unit_price\"       :\"$c_unit_price\",
            \"c_amnt\"             :\"$c_amnt\",
            \"meac_c_qty\"         :\"$meac_c_qty\",
            \"meac_ebom\"          :\"$meac_ebom\",
            \"meac_var_ebom\"     :\"$meac_var_ebom\",
            \"meac_last_price\"   :\"$meac_last_price\",
            \"meac_re_est_etc\"   :\"$meac_re_est_etc\",
            \"meac_re_est_eac\"   :\"$meac_re_est_eac\",
            \"fortis_status\"     :\"$fortis_status\",
            \"etc_diff\"          :\"$etc_diff\",
            \"meac_ebom\"         :\"$meac_ebom\"
        },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="load_comitted_rpt"){

    deleteFromTable("meac", "wp_baan_committed_po", "ship_code", $ship_code);
    loadBaanCommittedPO($ship_code);
    truncateTable("meac", "po_data");
    loadFortisPOData();
}