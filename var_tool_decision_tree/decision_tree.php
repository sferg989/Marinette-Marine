<?php
include("../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 12/1/2016
 * Time: 2:22 PM
 */
$user = "fs11239";
if($control =="cv")
{
    if($id=="end"){
        //print $answer_tracker;
        $answer_array = json_decode($answer_tracker,true);
        $counts = array_count_values($answer_array);

        $yes_wc = "criteria_yes_id ='";
        $no_wc = "criteria_no_id ='";
        unset($answer_array["end"]);

        foreach ($answer_array as $key=>$value){
            switch ($value) {
                case "yes":
                    $yes_wc.="$key,";
                    break;
                case "no":
                    $no_wc.="$key,";
                    break;
            }
        }
        $yes_wc = substr($yes_wc, 0, -1);
        $no_wc  = substr($no_wc, 0, -1);
        if($counts["yes"]>0 and $counts["no"]>0)
        {
            $yes_wc.="'";
            $no_wc.="'";
            $wc = "where $yes_wc and $no_wc";
        }
        if($counts["yes"]<1)
        {
            $no_wc.="'";
            $wc = "where $no_wc";
        }
        if($counts["no"]<1)
        {
            $yes_wc.="'";
            $wc = "where $yes_wc";
        }
        $sql    = "select car from decision_tree.cv_car $wc";
        //print $sql;

        $rs     = dbCall($sql, "decision_tree");
        $car      = $rs->fields["car"];
        die($car);
    }
    $sql    = "select question, yes_id, no_id from decision_tree.cv where id = $id";
    //print $sql;

    $rs     = dbCall($sql, "decision_tree");
    $q      = $rs->fields["question"];
    $yes_id = $rs->fields["yes_id"];
    $no_id  = $rs->fields["no_id"];
    die($q.",".$yes_id.",".$no_id);
}

