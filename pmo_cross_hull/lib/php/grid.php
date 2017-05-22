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


$rpt_period = currentRPTPeriod();

if($control=="project_grid")
{

    $data = "[";
    $table_name = "`".$rpt_period."_tp_check`";
    $sql = "  
        select ship_code, ca, wp, date, sum(val) hours from bl_validation.$table_name where ca like '%PMO%' group by ship_code, ca,wp

  ";
    //print $sql;
    $rs = dbCall($sql);
    $id = 1;
    while (!$rs->EOF)
    {
        $ship_code = addslashes($rs->fields["ship_code"]);
        $ca        = $rs->fields["ca"];
        $wp        = $rs->fields["wp"];
        $date      = $rs->fields["date"];
        $hours     = formatNumber4decNoComma($rs->fields["hours"]);
        $id++;
        $data.="{
            \"id\"          :$id,
            \"ship_code\"   :\"$ship_code\",
            \"ca\"          :\"$ca\",
            \"wp\"          :\"$wp\",
            \"rpt_period\"  : $date,
            \"hours\"       : $hours
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