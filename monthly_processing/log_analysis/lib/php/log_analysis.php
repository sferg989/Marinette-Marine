<?php
include('../../../../inc/inc.php');
function processJustification($justification){
    $justification = trim($justification);
    $justification = str_replace("\"", "'", $justification);
    $justification = str_replace("\t", '', $justification); // remove tabs
    $justification = str_replace("\n", '', $justification); // remove new lines
    $justification = str_replace("\r", '', $justification);
    return $justification;
}
function getBCRSInAPeriod($rpt_period){
    $table_name = $rpt_period."_bcr";

    $create_table = checkIfTableExists("bcr_log", $table_name);
    if($create_table == "create_table"){
        //die("create_table");
    }
    $sql = "select `desc` from $table_name";
    $rs = dbCall($sql, "bcr_log");
    $bcr_array =array();
    while (!$rs->EOF)
    {
        $full_desc           = trim($rs->fields["desc"]);
        $bcr_comment_arraay = explode(" ",$full_desc);
        foreach ($bcr_comment_arraay as $value){
            if(ctype_digit($value)== true and strlen($value)<4){
                $bcr_array[]="$value";
            }
        }
        $rs->MoveNext();
    }
    $result = array_unique($bcr_array);
    $result2 = array_values($result);
    $bcr_list="";
    foreach($result2 as $value){
        $bcr_list.="$value,";
    }
    $bcr_list = substr($bcr_list, 0, -1);
    return $bcr_list;
}
if($control=="log_analysis")
{
    $bcr_wc  = getBCRSInAPeriod($rpt_period);
    if($bcr_wc !=""){
        $bcr_wc = "and bcr in ($bcr_wc)";
    }

    $data = "[";
    $sql = "
    select 
        id,
        bcr,
        case when change_no is not null
            then concat(change_type, ' - ', change_no) 
        else null end as pcw,        
        auth_no,
        justification,
        db, 
        mr,
        ub
    from processing_status.fortis_xml
    where ship_code = $ship_code  $bcr_wc
    order by bcr
  ";

    $rs = dbCall($sql, "processing_status");
    while (!$rs->EOF)
    {
        //die("made it");


        $id           = $rs->fields["id"];
        $bcr           = $rs->fields["bcr"];
        $pcw           = $rs->fields["pcw"];
        $auth_no       = addslashes($rs->fields["auth_no"]);
        $justification = processJustification($rs->fields["justification"]);
        $db            = formatNumber4decNoComma($rs->fields["db"]);
        $mr            = formatNumber4decNoComma($rs->fields["mr"]);
        $ub            = formatNumber4decNoComma($rs->fields["ub"]);

        $data .= "{
            \"id\"              :$id,
            \"bcr\"             :$bcr,
            \"pcw\"             :\"$pcw\",
            \"auth_no\"         :\"$auth_no\",
            \"justification\"   :\"$justification\",
            \"db\"              :$db,
            \"mr\"              :$mr,
            \"ub\"              :$ub
        },";
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}


