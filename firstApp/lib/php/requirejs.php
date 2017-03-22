<?php
include('../../../inc/inc.php');
if($control=="status_grid")
{
    $data = "[";
    $sql = "

        select 
          ship.id as comment_id,
          st.id step_id,
          st.url url,
          ship.ship_code ship_code,
          ship.status as status,
          ship.pfa_notes as pfa_notes,
          st.wi,
          st.step,
          timeline
        from processing_status.steps  st left join processing_status.ship ship
          on st.id = ship.step_id and ship.ship_code = $ship_code and ship.period = $rpt_period   
        where st.url is not NULL and st.url <> ''
        order by step_id
  ";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        //die("made it");
        $comment_id = $rs->fields["comment_id"];
        $step_id    = $rs->fields["step_id"];
        $status     = $rs->fields["status"];
        $pfa_notes  = addslashes($rs->fields["pfa_notes"]);
        $wi         = htmlentities($rs->fields["wi"]);
        $url        = addslashes($rs->fields["url"]);
        $step       = addslashes($rs->fields["step"]);
        $timeline   = addslashes($rs->fields["timeline"]);
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
            \"url\"          :\"$url\",
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


