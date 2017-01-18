<?php
include("../../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
$debug = true;
function checkifArray($variable){
    if(is_array($variable)==false){
        return $variable;
    }
    else{
        $variable = "";
        return $variable;
    }
}
function fixExcelDateTime($date){
    //10/19/2016 1:42:47 pm
    //2016-19-20 13:42:47
    $data_date  = substr(trim($date), 0, 10);
    $time_part  = substr(trim($date), -10);
    $data_date  = fixExcelDate($data_date);
    $time       = date("H:i:s", strtotime($time_part));
    $final      = $data_date . " " . $time;
    return $final;
}
function insertFortisXML($xml_array){
    $insert_sql = "
        insert into fortis_xml (
         date,
         rpt_period,
         bcr,
         part,
         rev,
         ship_code,
         project_name,
         change_type,
         change_no,
         auth_status,
         auth_no,
         initiator,
         department,
         impact,
         justification,
         baan_implementation,
         cost,
         cost_approval,
         chng,
         chng_ctrl_approval,
         pmng,
         pm_approval,
         vppm,
         vp_approval,
         ctrl,
         controller_approval,
         peng,
         pe_approval,
         plan,
         pn_approval,
         purc,
         purch_approval,
         cam_1,
         cam_1_approval,
         cam_2,
         cam_2_approval,
         cam_3,
         cam_3_approval,
         cam_4,
         cam_4_approval,
         cam_5,
         cam_5_approval,
         deng,
         de_approval,
         dmfg,
         mfg_approval,
         ceo,
         ceo_approval,
         mr,
         ub,
         labor_total,
         db,
         sent_on,
         notes,
         approval_log,
         approval_level,
         modified_by,
         modified_date,
         created_by,
         created_date,
         document_id,
         denial_rationale,
         denial_confirmed,
         volume_name,
         file_name) 
            VALUES";
    $i = 0;
    $sql = $insert_sql;
    foreach ($xml_array as $key=>$value)
    {
        if($i == 200)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql,"processing_status");
            //print $sql;
            $sql = $insert_sql;
            $i=0;
        }
        $date                = fixExcelDate($value["DATE"]);
        $rpt_period          = createRPTfromDate($date);
        $bcr                 = $value["BCR"];
        $part                = checkifArray($value["PART"]);
        $rev                 = checkifArray($value["REV"]);
        $ship_code           = $value["PROJECT"];
        $project_name        = $value["PROJECT_NAME"];
        $change_type         = $value["CHANGE_TYPE"];
        $change_no           = checkifArray($value["CHANGE_NO"]);
        $auth_status         = $value["AUTH_STATUS"];
        $auth_no             = checkifArray($value["AUTH_NO"]);
        $initiator           = $value["INITIATOR"];
        $department          = $value["DEPARTMENT"];
        $impact              = $value["IMPACT"];
        $justification       = $snip = str_replace("\r", '', addslashes($value["JUSTIFICATION"]));
        $baan_implementation = checkifArray($value["BAAN_IMPLEMENTATION"]);
        $cost                = $value["COST"];
        $cost_approval       = $value["COST_APPROVAL"];
        $chng                = $value["CHNG"];
        $chng_ctrl_approval  = $value["CHNG_CTRL_APPROVAL"];
        $pmng                = $value["PMNG"];
        $pm_approval         = $value["PM_APPROVAL"];
        $vppm                = $value["VPPM"];
        $vp_approval         = $value["VP_APPROVAL"];
        $ctrl                = $value["CTRL"];
        $controller_approval = $value["CONTROLLER_APPROVAL"];
        $peng                = $value["PENG"];
        $pe_approval         = $value["PE_APPROVAL"];
        $plan                = $value["PLAN"];
        $pn_approval         = $value["PN_APPROVAL"];
        $purc                = $value["PURC"];
        $purch_approval      = $value["PURCH_APPROVAL"];
        $cam_1               = checkifArray($value["CAM_1"]);
        $cam_1_approval      = checkifArray($value["CAM1_Approval"]);
        $cam_2               = checkifArray($value["CAM_2"]);
        $cam_2_approval      = checkifArray($value["CAM2_Approval"]);
        $cam_3               = checkifArray($value["CAM_3"]);
        $cam_3_approval      = checkifArray($value["CAM3_Approval"]);
        $cam_4               = checkifArray($value["CAM_4"]);
        $cam_4_approval      = checkifArray($value["CAM4_Approval"]);
        $cam_5               = checkifArray($value["CAM_5"]);
        $cam_5_approval      = checkifArray($value["CAM5_Approval"]);
        $deng                = $value["DENG"];
        $de_approval         = $value["DE_APPROVAL"];
        $dmfg                = $value["DMFG"];
        $mfg_approval        = $value["MFG_APPROVAL"];
        $ceo                 = $value["CEO"];
        $ceo_approval        = $value["CEO_APPROVAL"];
        $mr                  = $value["MR"];
        $ub                  = $value["UB"];
        $labor_total         = $value["LABOR_TOTAL"];
        $db                  = $value["MATL_TOTAL"];
        $sent_on             = checkifArray($value["SENT_ON"]);
        $notes               = checkifArray($value["NOTES"]);
        $approval_log        = $value["APPROVAL_LOG"];
        $approval_level      = $value["Approval_Level"];
        $modified_by         = $value["Modified_By"];
        $modified_date       = fixExcelDateTime($value["Modified_Date"]);
        $created_by          = $value["Created_By"];
        $created_date        = fixExcelDateTime($value["Created_Date"]);
        $document_id         = $value["Document_ID"];
        $denial_rationale    = checkifArray($value["DENIAL_RATIONALE"]);
        $denial_confirmed    = checkifArray($value["DENIAL_CONFIRMED"]);
        $volume_name         = $value["Volume-Name"];
        $file_name           = $value["File-Name"];
        //print $initiator ."\r";

        $sql.="
         (
             '$date',
             $rpt_period,
             $bcr,
             '$part',
             '$rev',
             $ship_code,
             '$project_name',
             '$change_type',
             '$change_no',
             '$auth_status',
             '$auth_no',
             '$initiator',
             '$department',
             '$impact',
             '$justification',
             '$baan_implementation',
             '$cost',
             '$cost_approval',
             '$chng',
             '$chng_ctrl_approval',
             '$pmng',
             '$pm_approval',
             '$vppm',
             '$vp_approval',
             '$ctrl',
             '$controller_approval',
             '$peng',
             '$pe_approval',
             '$plan',
             '$pn_approval',
             '$purc',
             '$purch_approval',
             '$cam_1',
             '$cam_1_approval',
             '$cam_2',
             '$cam_2_approval',
             '$cam_3',
             '$cam_3_approval',
             '$cam_4',
             '$cam_4_approval',
             '$cam_5',
             '$cam_5_approval',
             '$deng',
             '$de_approval',
             '$dmfg',
             '$mfg_approval',
             '$ceo',
             '$ceo_approval',
             '$mr',
             '$ub',
             '$labor_total',
             '$db',
             '$sent_on',
             '$notes',
             '$approval_log',
             '$approval_level',
             '$modified_by',
             '$modified_date',
             '$created_by',
             '$created_date',
             '$document_id',
             '$denial_rationale',
             '$denial_confirmed',
             '$volume_name',
             '$file_name'
             ),";
        $i++;
    }
    if($i!=200)
    {
        $sql = substr($sql, 0, -1);
        print $sql;
        $junk = dbCall($sql,"processing_status");
    }
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
$array_of_dirs_to_change = array();


$path2_cobra_dir    = $base_path."".$ship_name."/".$ship_code ;

if($control=="step_grid")
{
    $data = "[";
    $sql = "select id,name, action from processing_status.update_log_analysis order by order_id";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $id = $rs->fields["id"];
        $name = $rs->fields["name"];
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
if($control=="load_xml"){
    $path2_fortis_export = $path2_cobra_dir . "/" . $ship_code . " Fortis Export/" . $ship_code . "Fortis.xml";

    $xml        = simplexml_load_file($path2_fortis_export);
    $json       = json_encode($xml);
    $xml_array  = json_decode($json, TRUE);
    truncateTable("processing_status", "fortis_xml");
    insertFortisXML($xml_array["NEW_Baseline_Change_Request"]);

    die("made it");
    //die($path2_fortis_export);
}