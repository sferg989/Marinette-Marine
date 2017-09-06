<?php
include('../../../inc/inc.php');
include('../../../inc/inc.bac_eac.php');
function getShipValByGROUPandDataType($ship_code, $group, $rpt_period,$field="s_vac", $data_type= "_cpr2h_obs"){
    $table_suffix = getCorrespondingTable($ship_code, $data_type);
    $table_name = $rpt_period."".$table_suffix;
    $sql = "select $field from $table_name where ship_code = $ship_code and item like '%$group%'";
    //print $sql;
    $rs = dbCall($sql, "bac_eac");
    $val = $rs->fields["$field"];
    return $val;
}

if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$rpt_period = currentRPTPeriod();
$rpt_period = getPreviousRPTPeriod($rpt_period);
die($rpt_period);
if($control=="project_grid")
{
    $field      = "s_cur";
    if($stucture=="obs_h")
    {
        $table_type = "_cpr2h_obs";
        $data_type = "hours";
        $item = "obs";
    }
    elseif ($stucture=="obs_d"){
        $table_type = "_cpr2l_obs";
        $data_type = "dollars";
        $item = "obs";
    }
    elseif ($stucture=="wbs_h"){
        $table_type = "_cpr2h_wbs";
        $data_type = "hours";
        $item = "wbs";
    }
    else {
        //$stucture=="wbs_d";
        $table_type = "_cpr2d_wbs";
        $data_type = "dollars";
        $item = "wbs";
    }
    $_SESSION["table_type"] = $table_type;
    $_SESSION["data_type"]  = $data_type;
    $_SESSION["item"]       = $item;
    $_SESSION["field"]      = $field;

    $data = "[";
    $sql = "select `group` from fmm_evms.cross_hull_group where type = '$item'";
    $rs = dbCall($sql);
    $id = 1;
    while (!$rs->EOF)
    {
        $item   = addslashes($rs->fields["group"]);
        $lcs17val = formatNumber4decNoComma(getShipValByGROUPandDataType("0477", $item, $rpt_period, $field, $table_type));
        $lcs19val = formatNumber4decNoComma(getShipValByGROUPandDataType("0479", $item, $rpt_period, $field, $table_type));
        $lcs21val = formatNumber4decNoComma(getShipValByGROUPandDataType("0481", $item, $rpt_period, $field, $table_type));
        $lcs23val = formatNumber4decNoComma(getShipValByGROUPandDataType("0483", $item, $rpt_period, $field, $table_type));
        $lcs25val = formatNumber4decNoComma(getShipValByGROUPandDataType("0485", $item, $rpt_period, $field, $table_type));
        $id++;
        $data.="{
            \"id\"   :$id,
            \"group\"   :\"$item\",
            \"data_type\"   :\"$data_type\",
            \"lcs17\"   : $lcs17val,
            \"lcs19\"   : $lcs19val,
            \"lcs21\"   : $lcs21val,
            \"lcs23\"   : $lcs23val,
            \"lcs25\"   : $lcs25val
        },";

        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control =="excel_export"){

    $table_type = $_SESSION["table_type"];
    $data_type  = $_SESSION["data_type"];
    $item       = $_SESSION["item"];
    $field      = $_SESSION["field"];

    $html = "
    <table>
    <tr>
        <th>Group</th>
        <th>LCS 17</th>
        <th>LCS 19</th>
        <th>LCS 21</th>
        <th>LCS 23</th>
        <th>LCS 25</th>
    </tr>
    ";
    $sql = "select `group` from fmm_evms.cross_hull_group where type = '$item'";
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $item = addslashes($rs->fields["group"]);
        $lcs17val = getShipValByGROUPandDataType("0477", $item, $rpt_period, $field, $table_type);
        $lcs19val = getShipValByGROUPandDataType("0479", $item, $rpt_period, $field, $table_type);
        $lcs21val = getShipValByGROUPandDataType("0481", $item, $rpt_period, $field, $table_type);
        $lcs23val = getShipValByGROUPandDataType("0483", $item, $rpt_period, $field, $table_type);
        $lcs25val = getShipValByGROUPandDataType("0485", $item, $rpt_period, $field, $table_type);
        $html.="
        <tr>
            <td>$item</td>
            <td>".formatNumber($lcs17val)."</td>
            <td>".formatNumber($lcs19val)."</td>
            <td>".formatNumber($lcs21val)."</td>
            <td>".formatNumber($lcs23val)."</td>
            <td>".formatNumber($lcs25val)."</td>
        </tr>
        ";

        $rs->MoveNext();
    }
    $html.="</table>";
    $token         = rand (0,1000);
    $path2_export = $g_path_to_util."excel_exports/"."$token"."export.xls";
    $path = "../util/excel_exports/".$token."export.xls";
    file_put_contents($path2_export,$html);
    die($path);
}