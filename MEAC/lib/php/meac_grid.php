<?php
include('inc.insert_data.php');
include('../../../inc/inc.php');
include('../../../inc/inc.PHPExcel.php');
include("../../../inc/lib/php/phpExcel-1.8/classes/phpexcel/IOFactory.php");
$user = $_SESSION["user_name"];
$user = "fs11239";
function logLastUsedLayout($user_id,$field_list_common_name, $ud_layout_id){
    $sql = "insert into layout_last_used (user_id, field_list_common_name, ud_layout_id) VALUES 
       ($user_id, $field_list_common_name, $ud_layout_id)";
    $junk = dbCall($sql, "MEAC");
}
function getUDFGroupBY($ud_layout_id){
    $sql = "select gb from ud_layout where id = $ud_layout_id limit 1";
    $rs = dbCall($sql,"MEAC");
    $gb = $rs->fields["gb"];
    $gb_array = explode("|", $gb);
    $gb = "group by ";
    foreach ($gb_array as $value){
        $gb.="$value,";
    }
    $gb = substr($gb, 0, -1);
    return $gb;
}
function saveUDFFeidlList($field_list, $user_id, $field_set_name){
    $ud_id = getLastId("MEAC", "ud_layout", "id");
    $ud_id = $ud_id+1;
    $insert_sql = "insert into ud_layout (id, field_list_id, user_id, field_list_common_name) Values";
    $field_list_array = explode(",", $field_list);
    $sql =$insert_sql;
    foreach ($field_list_array as $value){
        $sql.="($ud_id, $value, '$user_id', '$field_set_name'),";
    }
    $sql = substr($sql, 0, -1);
    $junk = dbCall($sql,"MEAC");
    return $ud_id;
}


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
if($control=="field_list"){
    $sql = "select id, field_name, common_name from field_list";
    $rs = dbCall($sql, "MEAC");
    $data = "<table id ='field_table'><tr>";
    $i=0;
    while (!$rs->EOF)
    {
        $field_id          = $rs->fields["id"];
        $field_name        = $rs->fields["field_name"];
        $field_common_name = trim($rs->fields["common_name"]);
        //print $i;
        if($i%3 == 0) {
            $data.= "</tr><tr><td><input type=\"checkbox\" name=\"$field_id\" > $field_common_name</td>";
        }
        else{
            $data.= "<td><input type=\"checkbox\" name=\"$field_id\" > $field_common_name</td>";
        }
        $i++;
        $rs->MoveNext();
    }
    $data.="</table>";
    die($data);
}

if($control =='field_layout_name_check'){
    $sql = "select id from ud_layout where user_id = '$user' and field_list_common_name = '$field_set_name'";
    //print $sql;
    $rs = dbCall($sql, "MEAC");
    $field_id          = $rs->fields["id"];
    if($field_id ==""){
        die("false");
    }else{
        die("true");
    }
}
if($control =='save_field_list'){
    $result = saveUDFFeidlList($field_list, $user, $field_set_name);
    die($result);
}
if($control=="get_col_definition"){
    $grid_cols = "";
    $sql = "
        select 
            field_name,
            common_name 
        from field_list fl 
        left join ud_layout ud
            on ud.field_list_id=fl.id 
        where user_id = '$user' 
        and  ud.field_list_common_name = '$field_set_name' order by fl.group desc";
    $rs=dbCall($sql,"MEAC");
    while (!$rs->EOF)
    {
        $field_name  = $rs->fields["field_name"];
        $common_name = $rs->fields["common_name"];

        $grid_cols.= "$field_name-$common_name,";
        $rs->MoveNext();
    }
    $grid_cols = substr($grid_cols, 0, -1);
    die($grid_cols);
}
if($control=="get_data_col_definition"){
    $grid_cols = "";
    $sql = "
        select 
            field_name,
            common_name 
        from field_list fl 
        left join ud_layout ud
            on ud.field_list_id=fl.id 
        where user_id = '$user' 
        and  ud.field_list_common_name = '$field_set_name' and fl.data = 1 order by fl.group desc";
    $rs=dbCall($sql,"MEAC");
    while (!$rs->EOF)
    {
        $field_name  = $rs->fields["field_name"];
        $common_name = $rs->fields["common_name"];

        $grid_cols.= "$field_name-$common_name,";
        $rs->MoveNext();
    }
    $grid_cols = substr($grid_cols, 0, -1);
    die($grid_cols);
}
if($control=="part_level_MEAC")
{
    //$data = "{\"Total\":3000,\"Rows\":[";
    $data = "[";
    if(isset($gb)==false){
        $gb = getUDFGroupBY($udf_layout_id);
    }
    if(isset($wc)==false){
        $wc = "where ship_code = 477";
    }
    $sql = returnMeacSQL($wc, $gb);
    //print $sql;
    $rs = dbCall($sql, "meac");
    $id= 1;
    while (!$rs->EOF)
    {
        $program                = $rs->fields["program"];
        $ship_code              = $rs->fields["ship_code"];
        $category               = $rs->fields["category"];
        $swbs_group             = $rs->fields["swbs_group"];
        $swbs                   = $rs->fields["swbs"];
        $wp                     = $rs->fields["wp"];
        $spn                    = $rs->fields["spn"];
        $item                   = $rs->fields["item"];
        $item_group             = $rs->fields["item_group"];
        $description            = processDescriptionAgain($rs->fields["description"]);
        $unit                   = $rs->fields["unit"];
        $noun1                  = $rs->fields["noun1"];
        $transfers              = $rs->fields["transfers"];
        $c_amt                  = $rs->fields["c_amt"];
        $c_unit_price           = $rs->fields["c_unit_price"];
        $last_unit_price        = $rs->fields["last_unit_price"];
        $gl_int_amt             = $rs->fields["gl_int_amt"];
        $ebom                   = $rs->fields["ebom"];
        $ebom_on_hand           = $rs->fields["ebom_on_hand"];
        $ebom_issued            = $rs->fields["ebom_issued"];
        $last_unit_price_ship   = $rs->fields["last_unit_price_ship"];
        $open_po_pending_amt    = $rs->fields["open_po_pending_amt"];
        $open_buy_item_shortage = $rs->fields["open_buy_item_shortage"];
        $etc                    = $rs->fields["etc"];
        $eac                    = $rs->fields["eac"];
        $uncommitted            = $rs->fields["uncommitted"];
        $target_qty             = $rs->fields["target_qty"];
        $target_unit_price      = $rs->fields["target_unit_price"];
        $target_ext_cost        = $rs->fields["target_ext_cost"];
        $vendor_name            = $rs->fields["vendor_name"];
        $vendor_id              = $rs->fields["vendor_id"];
        $var_target_cost        = $rs->fields["var_target_cost"];
        $c_qty                  = $rs->fields["c_qty"];
        $var_target_qty         = $rs->fields["var_target_qty"];
        $buyer                  = $rs->fields["buyer"];
        $gl_qty                 = $rs->fields["gl_qty"];
        $var_ebom               = $rs->fields["var_ebom"];
        $data.="{
            \"id\"                  : \"$id\",
            \"program\"             : \"$program\",
            \"ship_code\"           : \"$ship_code\",
            \"swbs_group\"          : \"$swbs_group\",
            \"category\"            : \"$category\",
            \"swbs\"                : \"$swbs\",
            \"wp\"                  : \"$wp\",
            \"spn\"                 : \"$spn\",
            \"item\"                : \"$item\",
            \"item_group\"          : \"$item_group\",
            \"description\"         : \"$description\",
            \"unit\"                : \"$unit\",
            \"noun1\"               : \"$noun1\",
            \"transfers\"           : \"$transfers\",
            \"c_amt\"               : \"$c_amt\",
            \"c_unit_price\"        : \"$c_unit_price\",
            \"last_unit_price\"     : \"$last_unit_price\",
            \"gl_int_amt\"          : \"$gl_int_amt\",
            \"ebom\"                : \"$ebom\",
            \"ebom_on_hand\"        : \"$ebom_on_hand\",
            \"ebom_issued\"         : \"$ebom_issued\",
            \"last_unit_price_ship\": \"$last_unit_price_ship\",
            \"open_po_pending_amt\" : \"$open_po_pending_amt\",
            \"open_buy_item_shortage\" : \"$open_buy_item_shortage\",
            \"etc\"                 : \"$etc\",
            \"eac\"                 : \"$eac\",
            \"uncommitted\"         : \"$uncommitted\",
            \"target_qty\"          : \"$target_qty\",
            \"target_unit_price\"   : \"$target_unit_price\",
            \"target_ext_cost\"     : \"$target_ext_cost\",
            \"vendor_name\"         : \"$vendor_name\",
            \"vendor_id\"           : \"$vendor_id\",
            \"var_target_cost\"     : \"$var_target_cost\",
            \"c_qty\"               : \"$c_qty\",
            \"var_target_qty\"      : \"$var_target_qty\",
            \"buyer\"               : \"$buyer\",
            \"gl_qty\"              : \"$gl_qty\",
            \"var_ebom\"              : \"$var_ebom\"
        },";
        $id++;
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    //$data.="]}";
    $data.="]";
    die($data);
}
if($control =="layout_list"){
    $data = "
    {
  \"success\": true,
  \"data\": [";
    $sql = "select id, field_list_common_name from ud_layout where user_id = '$user' group by id, field_list_common_name";
    $rs = dbCall($sql,"meac");
    while (!$rs->EOF)
    {
        $field_list_common_name = $rs->fields["field_list_common_name"];
        $id                     = $rs->fields["id"];

        $data.="{
            \"text\"    : \"$field_list_common_name\",
            \"value\"   : $id
        },";
        $rs->MoveNext();
    }
    //print $data;
    $data = substr($data, 0, -1);
    $data.="]}";

    die($data);
}
if($control =="last_layout_used"){

    $sql = "select ud_layout_id, field_list_common_name from layout_last_used where user_id = '$user' ";
    $rs = dbCall($sql,"meac");

    $ud_layout_id           = $rs->fields["ud_layout_id"];
    $field_list_common_name = $rs->fields["field_list_common_name"];

    $data ="$ud_layout_id,$field_list_common_name";
    die($data);
}
if($control =="layout_groups"){
    $sql = "select gb from ud_layout where id = $layout_id limit 1";
    //print $sql;
    $rs = dbCall($sql, "meac");
    $gb = $rs->fields["gb"];
    $gb_array = explode("|", $gb);
    $grouped_fields = "";
        //die("made it");

    foreach ($gb_array as $value){
        if($value==""){
            $grouped_fields ="<li  id='no_group' class=\"list-group-item\">No Grouping Elements</li>";
            continue;
        }
        $grouped_fields.=" <li class=\"list-group-item\">$value</li>";
    }
    die($grouped_fields);
}
