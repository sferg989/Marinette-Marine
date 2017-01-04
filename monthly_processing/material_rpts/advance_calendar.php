<?php
include("../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";

function insertIntoProcessingStatusShips($step_id, $ship_code, $period, $status, $pfa_notes, $completed_by, $completed_on)
{
    $timestamp = now();
    $sql = "
        insert into processing_status.ship
          (step_id, ship_code, period, status, pfa_notes, completed_by, completed_on) 
        values 
        ($step_id, $ship_code, $period, '$status', '$pfa_notes', '$completed_by', '$timestamp')
    ";
    //print $sql;
    $junk = dbCall($sql,"processing_status");

}
function insertIntoProcessingStatusShipsLog($user, $status_change, $pfa_notes_change, $step_id, $ship_code, $period)
{
    $timestamp = now();
    $sql = "
    insert into 
      processing_status.log 
        (user, changed_on, status_change, pfa_notes_change, step_id, ship_code, period)
    VALUES
        ('$user','$timestamp','$status_change', '$pfa_notes_change',$step_id, $ship_code, $period) 
    ";
    //print $sql;
    $junk = dbCall($sql,"processing_status");
}
function deleteProcessingShipSteps($id)
{
    $sql = "delete from processing_status.ship where id =$id";
    $junk = dbCall($sql,"processing_status");
}
function getCurrentShipStatusData($id)
{
    $sql = "select status, pfa_notes from processing_status.ship where id = $id";
    $rs = dbCall($sql);
    $status         = $rs->fields["status"];
    $pfa_notes      = $rs->fields["pfa_notes"];

    $data["status_change"]      = $status;
    $data["pfa_notes_change"]   = $pfa_notes;
    return $data;
}
function updateProcessingStatusShips($id,$status,$pfa_notes,$user)
{
    $timestamp = now();
    $sql ="
        update processing_status.ship
          SET status = '$status',
          pfa_notes = '$pfa_notes',
          completed_by = '$user',
          completed_on = '$timestamp'
        WHERE id =$id
    ";
    //print $sql;
    $junk = dbCall($sql,"processing_status");
}
if($control=="project_grid")
{
    $data = "[";
    $sql = "select id, name, code from fmm_evms.master_project order by code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $pmid = $rs->fields["id"];
        $name = $rs->fields["name"];
        $code = $rs->fields["code"];
        $data.="{
            \"id\":$pmid,
            \"project_select\":0,
            \"project_name\":\"$name\",
            \"code\":\"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="status_grid")
{

    $data = "[";
    $sql = "

        select 
          ship.id as comment_id,
          st.id step_id,
          ship.ship_code ship_code,
          ship.status as status,
          ship.pfa_notes as pfa_notes,
          st.wi,
          st.step,
          timeline
        from processing_status.steps  st left join processing_status.ship ship
          on st.id = ship.step_id and ship.ship_code = $ship_code and ship.period = $rpt_period   
        order by step_id
  ";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        //die("made it");
        $comment_id   = $rs->fields["comment_id"];
        $step_id   = $rs->fields["step_id"];
        $status    = $rs->fields["status"];
        $pfa_notes = addslashes($rs->fields["pfa_notes"]);
        $wi        = addslashes($rs->fields["wi"]);
        $step      = addslashes($rs->fields["step"]);
        $timeline  = addslashes($rs->fields["timeline"]);
        if($status !=1)
        {
            $status = 0;
        }
        $data .= "{
            \"id\"          :$step_id,
            \"step_status\" :$status,
            \"code\"        :$ship_code,
            \"rpt_period\"  :$rpt_period,
            \"comment_id\"  :\"$comment_id\",
            \"wi\"          :\"$wi\",
            \"step\"        :\"$step\",
            \"timeline\"    :\"$timeline\",
            \"pfa_notes\"   :\"$pfa_notes\"
        },";
        $rs->MoveNext();
    }

    $data = substr($data, 0, -1);
    $data.="]";

    die($data);
}
if($control =="update_status")
{
    //this means that there is a record for this ship/step/reporting period.  we
    //need log it than update the record.
    if($status=="false")
    {
        $status = "0";
    }if($status=="true")
    {
        $status = "1";
    }
    if($comment_id!="")
    {
        $data             = getCurrentShipStatusData($comment_id);
        $status_change    = $data["status_change"];
        $pfa_notes_change = $data["pfa_notes_change"];
        insertIntoProcessingStatusShipsLog($user, $status_change, $pfa_notes_change, $step_id, $ship_code, $rpt_period);
        updateProcessingStatusShips($comment_id,$status,$pfa_notes,$user);
    }
    else{
        insertIntoProcessingStatusShips($step_id, $ship_code, $rpt_period, $status, $pfa_notes, $user);
        die("made it");
    }

}

