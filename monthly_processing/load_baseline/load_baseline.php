<?php
include("../../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = false;
function determineBCRTableClass($reason){
    if($reason=="Not in Current Month Baseline"){
        $class = "bg-warning";
    }if($reason=="Not in Previous Month Baseline"){
        $class = "bg-warning";
    }if($reason=="Baseline Values Changed"){
        $class = "bg-danger";
    }if($reason=="BCR is WRONG"){
        $class = "bg-danger";
    }
    return $class;
}
function createTableFromBase($schema,$base_table, $new_table_name){
    $sql = "show create table $schema.$base_table";
    $rs = dbcall($sql);
    $create_table_stmt = $rs->fields["Create Table"];

    $sql = str_replace($base_table, $new_table_name, $create_table_stmt);
    $junk = dbCall($sql, "ims_data_check");
}
function checkIfTableExists($schema, $table_name){
    $sql = "select table_name from information_schema.tables where table_schema = '$schema' and table_name = '$table_name'";
    $rs = dbcall($sql, "information_schema");
    $val = $rs->fields["table_name"];
    if($val==""){
        return "create_table";
    }else{
        return true;
    }
}
function getBCRs($table_name, $ship_code){
    $sql = "select ship_code, ca, wp, hours, matl_dollars, bcr from ims_data_check.$table_name where ship_code = $ship_code";
    $rs = dbCall($sql,"ims_data_check");
    $data_array = array();
    while (!$rs->EOF)
    {
        $ca           = $rs->fields["ca"];
        $wp           = $rs->fields["wp"];
        $bcr          = $rs->fields["bcr"];
        $hours        = $rs->fields["hours"];
        $matl_dollars = $rs->fields["matl_dollars"];

        $data_array[$ca][$wp]["bcr"]          = $bcr;
        $data_array[$ca][$wp]["hours"]        = $hours;
        $data_array[$ca][$wp]["matl_dollars"] = $matl_dollars;

        $rs->MoveNext();
    }
    return $data_array;
}
function getPeriodBaseline($ship_code,$table_name)
{
    $wc= "";
    //$wc = "and wbs = '1.16.1.9.992.620'";

    $sql = "select ship_code,wbs,activity_id,s_labor_units,s_material_cost,bl_project_start,bl_project_finish from $table_name where ship_code = $ship_code
     $wc";

    $rs = dbCall($sql,"ims_data_check");
    $data_array = array();
    while (!$rs->EOF)
    {
        $wbs            = $rs->fields["wbs"];
        $activity_id    = $rs->fields["activity_id"];
        $data_array[$wbs][$activity_id]["s_labor_units"]        = $rs->fields["s_labor_units"];
        $data_array[$wbs][$activity_id]["s_material_cost"]      = $rs->fields["s_material_cost"];
        $data_array[$wbs][$activity_id]["bl_project_start"]     = date("n/j/Y",strtotime($rs->fields["bl_project_start"]));
        $data_array[$wbs][$activity_id]["bl_project_finish"]    = date("n/j/Y",strtotime($rs->fields["bl_project_finish"]));
        $rs->MoveNext();
    }
    return $data_array;
}

function deleteShipFromTable($ship_code,$table_name)
{
    $sql = "delete from $table_name where ship_code = $ship_code";
    //print $sql;
    $junk = dbCall($sql,"ims_data_check");
}
if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$ship_name       = getProjectNameFromCode($ship_code);
$prev_rpt_period = getPreviousRPTPeriod($rpt_period);
$data            = returnPeriodData($ship_code, $prev_rpt_period, $rpt_period);

$prev_year          = $data["prev_year"];
$cur_year           = $data["cur_year"];
$prev_year_last2    = $data["prev_year_last2"];
$cur_year_last2     = $data["cur_year_last2"];
$prev_month         = $data["prev_month"];
$cur_month          = $data["cur_month"];
$prev_month_letters = $data["prev_month_letters"];
$cur_month_letters  = $data["cur_month_letters"];
$ship_name          = $data["ship_name"];

$path2_cobra_dir    = $base_path."".$ship_name."/".$ship_code ;

if($control=="step_grid")
{
    $data = "[";
    $sql = "select id,name, action from processing_status.load_baseline order by `order`";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $id     = $rs->fields["id"];
        $name   = $rs->fields["name"];
        $action = $rs->fields["action"];
        $data.="{
            \"id\"      :$id,
            \"name\"    :\"$name\",
            \"action\"  :\"$action\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="ims_data_check"){

    $table_name   = $rpt_period . "_baseline";
    $create_table = checkIfTableExists("ims_data_check", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("ims_data_check","template_baseline", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name);
    $insert_sql = "INSERT INTO ims_data_check.`".$rpt_period."_baseline` (ship_code, wbs, activity_id, activity_name, s_labor_units, s_material_cost, bl_project_start, bl_project_finish) VALUES";
    $p6data = trim($p6data);
    $lines = explode("\n",$p6data);
    $sql = $insert_sql;
    $i = 0;
    foreach ($lines as $key=>$value){
        if($key ==0){
            continue;
        }
        $fields = explode("	",$value);
        $matl_dollars = str_replace("$","",$fields[4]);

        $no_comma = str_replace(",","",$matl_dollars);
        $wbs               = $fields[0];
        $activity_id       = $fields[1];
        $activity_name     = addslashes($fields[2]);
        $s_labor_units     = floatval($fields[3]);
        $s_material_cost   = floatval($no_comma);
        $bl_project_start  = fixExcelDate2DigitYear($fields[5]);
        $bl_project_finish = fixExcelDate2DigitYear($fields[6]);
        
        $sql.="(
            '$ship_code',
            '$wbs',
            '$activity_id',
            '$activity_name',
            $s_labor_units,
            $s_material_cost,
            '$bl_project_start',
            '$bl_project_finish'
        ),";
            if($i==200){
                $sql = substr($sql, 0, -1);
                $junk = dbCall($sql,"ims_data_check");
                print $sql;
                //die("made it");
                $sql = $insert_sql;
                $i=0;
            }
        $i++;
    }
    if($i!=200){
        $sql = substr($sql, 0, -1);
        //print $sql;
        $junk = dbCall($sql,"ims_data_check");
        $sql = $insert_sql;
    }
}
if($control=="load_bcr"){
    $table_name   = $rpt_period . "_bcr";
    $create_table = checkIfTableExists("ims_data_check", $table_name);
    if($create_table== "create_table"){
        createTableFromBase("ims_data_check","template_bcr", $table_name);
    }
    deleteShipFromTable($ship_code,$table_name);
    $insert_sql = "INSERT into ims_data_check.$table_name (ship_code, ca, wp,hours, matl_dollars, bcr) VALUES";
    $bcr_data = trim($bcr_data);
    $lines = explode("\n",$bcr_data);
    $sql = $insert_sql;
    $i = 0;

    foreach ($lines as $key=>$value){
        $fields = explode("	",$value);
        $matl_dollars = str_replace("$","",$fields[3]);

        $no_comma = str_replace(",","",$matl_dollars);
        //print "\r".$no_comma."\r";
        $ca           = $fields[0];
        $wp           = $fields[1];
        $hours        = floatval($fields[2]);
        $matl_dollars = floatval($no_comma);
        $bcr          = $fields[4];

        $sql.="(
            '$ship_code',
            '$ca',
            '$wp',
            $hours,
            $matl_dollars,
            '$bcr'
        ),";
        if($i==200){
            $sql = substr(trim($sql), 0, -1);
            $junk = dbCall($sql,"ims_data_check");
            //die("made it");
            $sql = $insert_sql;
            $i=0;
        }
        $i++;
    }
    if($i!=200){
        $sql  = substr(trim($sql), 0, -1);
        $junk = dbCall($sql, "ims_data_check");
        print $sql;
        $sql  = $insert_sql;
    }
    die("made it");
}
if($control=="data_check")
{
    $baseline = "false";
    $bcr = "false";
    $table_name   = $rpt_period . "_baseline";
    $create_table = checkIfTableExists("ims_data_check", $table_name);
    if($create_table==true)
    {
        $sql = "select count(*) as count from $table_name where ship_code = $ship_code";

        $rs = dbCall($sql,"ims_data_check");
        $count = $rs->fields["count"];
        if($count>0){
            $baseline = "true";
        }
    }
    $table_name   = $rpt_period . "_bcr";
    if($create_table==true)
    {
        $sql = "select count(*) as count from $table_name where ship_code = $ship_code";
        $rs = dbCall($sql,"ims_data_check");
        $count = $rs->fields["count"];
        if($count>0){
            $bcr = "true";
        }
    }
    $checks = "bcr:$bcr,baseline:$baseline";
    print $checks;
    return $checks;
}
if($control=="compare_ca"){

    $table_name      = $rpt_period . "_baseline";
    $prev_table_name = $prev_rpt_period . "_baseline";

    $current_period_baseline    = getPeriodBaseline($ship_code, $table_name);
    $prev_period_baseline       = getPeriodBaseline($ship_code, $prev_table_name);
    $not_in_cur     = array();
    $not_in_prev    = array();
    $no_bcr         = array();
    $bcr_wrong      = array();

    //check if all CA's are in Current Baseline
    foreach ($prev_period_baseline as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $cur  = isset($current_period_baseline[$key][$act]);
            if($cur==false){
                $not_in_cur[$key][$act]["prev_labor_units"] = $values["s_labor_units"];
                $not_in_cur[$key][$act]["prev_matl_cost"]   = $values["s_material_cost"];
                $not_in_cur[$key][$act]["prev_start"]       = $values["bl_project_start"];
                $not_in_cur[$key][$act]["prev_finish"]      = $values["bl_project_finish"];
            }
        }
    }
    //check if all CA's are in Previous Baseline
    foreach ($current_period_baseline as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $prev  = isset($prev_period_baseline[$key][$act]);
            //$prev = $values["bl_project_finish"];
            if($prev==false){
                $not_in_prev[$key][$act]["cur_labor_units"] = $values["s_labor_units"];
                $not_in_prev[$key][$act]["cur_matl_cost"]   = $values["s_material_cost"];
                $not_in_prev[$key][$act]["cur_start"]       = $values["bl_project_start"];
                $not_in_prev[$key][$act]["cur_finish"]      = $values["bl_project_finish"];
            }
        }
    }
    
    //remove elements from previous so the values can be checked.
    foreach ($not_in_cur as $key =>$value) {
        foreach ($value as $act_id =>$text){
            unset($prev_period_baseline[$key][$act_id]);
        }
    }

    //of the CA's that match are the values the same?
    foreach ($prev_period_baseline as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){

            $cur_finish = $current_period_baseline[$key][$act]["bl_project_finish"];
            $prev_finish = $values["bl_project_finish"];
            if($cur_finish!=$prev_finish){
                $diffs[$key][$act]["cur_finish"]  = $cur_finish;
                $diffs[$key][$act]["prev_finish"] = $prev_finish;
            }
            $cur_start = $current_period_baseline[$key][$act]["bl_project_start"];
            $prev_start= $values["bl_project_start"];
            if($cur_start!=$prev_start){
                $diffs[$key][$act]["cur_start"]  = $cur_start;
                $diffs[$key][$act]["prev_start"] = $prev_start;            
            }

            $cur_labor = $current_period_baseline[$key][$act]["s_labor_units"];
            $prev_labor = $values["s_labor_units"];
            if($cur_labor!=$prev_labor){
                $diffs[$key][$act]["cur_labor"]  = $cur_labor;
                $diffs[$key][$act]["prev_labor"] = $prev_labor;            
            }
            $cur_matl = $current_period_baseline[$key][$act]["s_material_cost"];
            $prev_matl = $values["s_material_cost"];
            //var_dump($current_period_baseline[$key][$act]);
            //print $key."\r".$act."\r";
            //die()
            if($cur_matl!=$prev_matl){
                $diffs[$key][$act]["cur_matl"]    = $cur_matl;
                $diffs[$key][$act]["prev_matl"] = $prev_matl;
            }
        }
    }

    //var_dump($not_in_cur);
    //var_dump($not_in_prev);

    $table_name      = $rpt_period . "_bcr";
    $bcr_data        = getBCRs($table_name, $ship_code);
    foreach ($not_in_cur as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $bcr_test  = isset($bcr_data[$key][$act]);
            if($bcr_test==false){
                $no_bcr[$key][$act]["reason"]      = "Not in Current Month Baseline";
                $no_bcr[$key][$act]["cur_labor"]   = $values["cur_labor"];
                $no_bcr[$key][$act]["cur_matl"]    = $values["cur_matl"];
                $no_bcr[$key][$act]["cur_start"]   = $values["cur_start"];
                $no_bcr[$key][$act]["cur_finish"]  = $values["cur_finish"];
                $no_bcr[$key][$act]["prev_labor"]  = $values["prev_labor"];
                $no_bcr[$key][$act]["prev_matl"]   = $values["prev_matl"];
                $no_bcr[$key][$act]["prev_start"]  = $values["prev_start"];
                $no_bcr[$key][$act]["prev_finish"] = $values["prev_finish"];
            }
        }
    }

    foreach ($not_in_prev as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $bcr_test  = isset($bcr_data[$key][$act]);
            if($bcr_test==false){
                $no_bcr[$key][$act]["reason"]      = "Not in Previous Month Baseline";
                $no_bcr[$key][$act]["cur_labor"]   = $values["cur_labor"];
                $no_bcr[$key][$act]["cur_matl"]    = $values["cur_matl"];
                $no_bcr[$key][$act]["cur_start"]   = $values["cur_start"];
                $no_bcr[$key][$act]["cur_finish"]  = $values["cur_finish"];
                $no_bcr[$key][$act]["prev_labor"]  = $values["prev_labor"];
                $no_bcr[$key][$act]["prev_matl"]   = $values["prev_matl"];
                $no_bcr[$key][$act]["prev_start"]  = $values["prev_start"];
                $no_bcr[$key][$act]["prev_finish"] = $values["prev_finish"];
            }
        }
    }
    //var_dump($diffs);
    foreach ($diffs as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $bcr_test  = isset($bcr_data[$key][$act]);
            if($bcr_test==false){
                $no_bcr[$key][$act]["reason"]      = "Baseline Values Changed";
                $no_bcr[$key][$act]["cur_labor"]   = $values["cur_labor"];
                $no_bcr[$key][$act]["cur_matl"]    = $values["cur_matl"];
                $no_bcr[$key][$act]["cur_start"]   = $values["cur_start"];
                $no_bcr[$key][$act]["cur_finish"]  = $values["cur_finish"];
                $no_bcr[$key][$act]["prev_labor"]  = $values["prev_labor"];
                $no_bcr[$key][$act]["prev_matl"]   = $values["prev_matl"];
                $no_bcr[$key][$act]["prev_start"]  = $values["prev_start"];
                $no_bcr[$key][$act]["prev_finish"] = $values["prev_finish"];
                continue;
            }
            //if there is a BCR are the changes the values the same
            $wp = (string)$act;
            $type = stripos($wp, "MATL");

            if($type>=0){
                $type = "matl";
            }
            else{
                $type = "labor";
            }
            if($type =="matl")
            {
                $matl_diff = abs($values["prev_matl"] - $values["cur_matl"]);
                $bcr_val   = abs($bcr_data[$key][$act]["matl_dollars"]);

                if($matl_diff!=$bcr_val){
                    $bcr_is_wrong[$key][$act]["reason"]      = "BCR is WRONG";
                    $bcr_is_wrong[$key][$act]["cur_labor"]   = $values["cur_labor"];
                    $bcr_is_wrong[$key][$act]["cur_matl"]    = $values["cur_matl"];
                    $bcr_is_wrong[$key][$act]["cur_start"]   = $values["cur_start"];
                    $bcr_is_wrong[$key][$act]["cur_finish"]  = $values["cur_finish"];
                    $bcr_is_wrong[$key][$act]["prev_labor"]  = $values["prev_labor"];
                    $bcr_is_wrong[$key][$act]["prev_matl"]   = $values["prev_matl"];
                    $bcr_is_wrong[$key][$act]["prev_start"]  = $values["prev_start"];
                    $bcr_is_wrong[$key][$act]["prev_finish"] = $values["prev_finish"];
                    $bcr_is_wrong[$key][$act]["bcr_val"]     = $bcr_val;
                }
            }
            if($type =="labor")
            {
                $labor_diff = abs($values["prev_labor"] - $values["cur_labor"]);
                $bcr_val    = abs($bcr_data[$key][$act]["hours"]);
                if($labor_diff!=$bcr_val){
                    $bcr_is_wrong[$key][$act]["reason"]      = "BCR is WRONG";
                    $bcr_is_wrong[$key][$act]["cur_labor"]   = $values["cur_labor"];
                    $bcr_is_wrong[$key][$act]["cur_matl"]    = $values["cur_matl"];
                    $bcr_is_wrong[$key][$act]["cur_start"]   = $values["cur_start"];
                    $bcr_is_wrong[$key][$act]["cur_finish"]  = $values["cur_finish"];
                    $bcr_is_wrong[$key][$act]["prev_labor"]  = $values["prev_labor"];
                    $bcr_is_wrong[$key][$act]["prev_matl"]   = $values["prev_matl"];
                    $bcr_is_wrong[$key][$act]["prev_start"]  = $values["prev_start"];
                    $bcr_is_wrong[$key][$act]["prev_finish"] = $values["prev_finish"];
                }
            }
        }
    }
    //var_dump($bcr_is_wrong);
    //die();
    $data = "<div id = \"bcr_grid\" class = \"col-md-8\">
    <table  class=\"table \">
        <tr>
            <th class = \"table_headers\">WBS Path</th>
            <th class = \"table_headers\">ActID</th>
            <th class = \"table_headers\">Reason</th>
            <th class = \"table_headers\">Cur Labor Units</th>	
            <th class = \"table_headers\">Cur Material Cost</th>	
            <th class = \"table_headers\">Cur Baseline Start</th>
            <th class = \"table_headers\">Cur Baseline Finish</th>
            <th class = \"table_headers\">Prev Labor Units</th>	
            <th class = \"table_headers\">Prev Material Cost</th>	
            <th class = \"table_headers\">Prev Baseline Start</th>	
            <th class = \"table_headers\">Prev Baseline Finish</th>
        </tr>
        ";
    foreach ($no_bcr as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $row_class = determineBCRTableClass($values["reason"]);
            $data.="
                 <tr class=\"$row_class\">
                    <td class = \"table_data\">$key</td>
                    <td class = \"table_data\">$act</td>
                    <td class = \"table_data\">".$values["reason"]."</td>
                    <td class = \"table_data\">".$values["cur_labor"]."</td>	
                    <td class = \"table_data\">".$values["cur_matl"]."</td>	
                    <td class = \"table_data\">".$values["cur_start"]."</td>
                    <td class = \"table_data\">".$values["cur_finish"]."</td>
                    <td class = \"table_data\">".$values["prev_labor"]."</td>	
                    <td class = \"table_data\">".$values["prev_matl"]."</td>	
                    <td class = \"table_data\">".$values["prev_start"]."</td>	
                    <td class = \"table_data\">".$values["prev_finish"]."</td>
                </tr>
                ";
        }
    }
    foreach ($bcr_is_wrong as $key=>$value){
        //  print "This is the WBS ".$key."\r";
        foreach ($value as $act=>$values){
            $row_class = determineBCRTableClass($values["reason"]);
            $data.="
                 <tr class=\"$row_class\">
                    <td class = \"table_data\">$key</td>
                    <td class = \"table_data\">$act</td>
                    <td class = \"table_data\">".$values["reason"]."</td>
                    <td class = \"table_data\">".$values["cur_labor"]."</td>	
                    <td class = \"table_data\">".$values["cur_matl"]."</td>	
                    <td class = \"table_data\">".$values["cur_start"]."</td>
                    <td class = \"table_data\">".$values["cur_finish"]."</td>
                    <td class = \"table_data\">".$values["prev_labor"]."</td>	
                    <td class = \"table_data\">".$values["prev_matl"]."</td>	
                    <td class = \"table_data\">".$values["prev_start"]."</td>	
                    <td class = \"table_data\">".$values["prev_finish"]."</td>
                </tr>
                ";
        }
    }
    $data.="
    
        
    </table>
    </div>
    ";

    die($data);

}

