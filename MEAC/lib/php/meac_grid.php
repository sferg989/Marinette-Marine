<?php
include('../../../inc/inc.php');

if($control=="meac_grid")
{
    $data = "[";
    $sql = "
        select program,
            ship_code,
            ca,
            wp,
            cam,
            swbs,
            description,
            bac,
            eac,
            prev_eac,
            a,
            gl_a,
            open_po,
            open_buy_qty,
            open_buy_cost,
            manual_adj,
            eac_change2,
            comments
        from `201705_meac` where ship_code = $ship_code
";
    //print $sql;
    $rs = dbCall($sql, "meac");
    $id= 1;
    while (!$rs->EOF)
    {
        $ship_code     = $rs->fields["ship_code"];
        $ca            = $rs->fields["ca"];
        $wp            = $rs->fields["wp"];
        $cam           = $rs->fields["cam"];
        $swbs          = $rs->fields["swbs"];
        $descr         = $rs->fields["description"];
        $bac           = formatNumberNoComma($rs->fields["bac"]);
        $eac           = formatNumberNoComma($rs->fields["eac"]);
        $a             = formatNumberNoComma($rs->fields["a"]);
        $gl_a          = formatNumberNoComma($rs->fields["gl_a"]);
        $open_po       = formatNumberNoComma($rs->fields["open_po"]);
        $open_buy_qty  = formatNumberNoComma($rs->fields["open_buy_qty"]);
        $open_buy_cost = formatNumberNoComma($rs->fields["open_buy_cost"]);
        $data.="{
            \"id\"          :$id,
            \"ship_code\"   :\"$ship_code\",
            \"wp\"          :\"$wp\",
            \"descr\"       :\"$descr\",
            \"cam\"         :\"$cam\",
            \"swbs\"        :\"$swbs\",
            \"bac\"         :$bac,
            \"eac\"         :$eac,
            \"a\"           :$a,
            \"gl_a\"        :$gl_a,
            \"open_po\"     :$open_po,
            \"open_buy_qty\":$open_buy_qty,
            \"open_buy\"    :$open_buy_cost
        },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="gl_grid")
{
    $data = "[";
    $sql = "
        select
        ship_code,
        cbm_material,
        swbs,
        wp,
        ldger_acct,
        document,
        line,
        item,
        description,
        `order`,
        pos,
        cust_supp,
        qty,
        unit,
        amt,
        date,
        integr_amt,
        clin,
        effort
        from wp_gl_detail where wp = '$wp' and ship_code = $ship_code
        
";
    $rs = dbCall($sql, "meac");
    $id= 1;
    while (!$rs->EOF)
    {
        $ship_code    = $rs->fields["ship_code"];
        $cbm_material = $rs->fields["cbm_material"];
        $swbs         = $rs->fields["swbs"];
        $wp           = $rs->fields["wp"];
        $acct   = $rs->fields["ldger_acct"];
        $document     = $rs->fields["document"];
        $line         = $rs->fields["line"];
        $item         = $rs->fields["item"];
        $descr        = processJustification($rs->fields["description"]);
        $order        = $rs->fields["order"];
        $pos          = $rs->fields["pos"];
        $cust_supp    = $rs->fields["cust_supp"];
        $qty          = formatNumberNoComma($rs->fields["qty"]);
        $uom          = $rs->fields["unit"];
        $amt          = formatNumberNoComma($rs->fields["amt"]);
        $date         = $rs->fields["date"];
        $i_amt        = formatNumberNoComma($rs->fields["integr_amt"]);
        $clin         = $rs->fields["clin"];
        $effort       = $rs->fields["effort"];
        $data.="{
            \"id\"          :$id,
            \"ship_code\"   :\"$ship_code\",
            \"wp\"          :\"$wp\",
            \"descr\"       :\"$descr\",
            \"doc\"         :\"$document\",
            \"acct\"        :\"$acct\",
            \"item\"        :\"$item\",
            \"swbs\"        :\"$$swbs\",
            \"ord\"         :\"$order\",
            \"line\"        :\"$line\",
            \"pos\"         :\"$pos\",
            \"cust_supp\"   :\"$cust_supp\",
            \"uom\"         :\"$uom\",
            \"date\"        :\"$date\",
            \"clin\"        :\"$clin\",
            \"qty\"         :$qty,
            \"amt\"         :$amt,
            \"i_amt\"       :$i_amt
        },";
        $id++;
        $rs->MoveNext();
    }
    //print $data;
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
