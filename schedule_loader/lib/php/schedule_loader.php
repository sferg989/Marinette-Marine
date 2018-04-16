<?php
include('../../../inc/inc.php');
include('../../../inc/inc.PHPExcel.php');
$debug     = false;
function returnP6ScheduleSQL($ppm_bl_id){
    $sql = "
            SELECT 
              t.proj_id,
              p.proj_short_name,
              t.wbs_id,
              t.clndr_id,
              t.rsrc_id,
              t.phys_complete_pct AS pc,
              t.COMPLETE_PCT_TYPE as complete_pct_type,
              t.task_id,
              t.task_code,
              t.task_name,
              t.task_type,
              t.total_float_hr_cnt,
              t.status_code,
              t.free_float_hr_cnt,
              t.remain_drtn_hr_cnt,
              t.act_work_qty,
              t.remain_work_qty,
              t.target_work_qty,
              t.target_drtn_hr_cnt,
              t.target_equip_qty,
              t.act_equip_qty,
              t.remain_equip_qty,
              t.cstr_date,
              t.act_start_date,
              t.act_end_date,
              t.late_start_date,
              t.late_end_date,
              t.expect_end_date,
              t.early_start_date,
              t.early_end_date,
              t.restart_date,
              t.reend_date,
              t.target_start_date,
              t.target_end_date,
              t.review_end_date,
              t.rem_late_start_date,
              t.rem_late_end_date,
              t.cstr_type,
              t.priority_type,
              t.cstr_date2,
              t.cstr_type2,
              t.act_this_per_work_qty,
              t.act_this_per_equip_qty,
              t.driving_path_flag,
              t.float_path,
              t.float_path_order,
              t.suspend_date,
              t.resume_date,
              t.external_early_start_date,
              t.external_late_end_date,
              t.delete_date,
              ISNULL(ISNULL(t.act_start_date, t.restart_date), t.target_start_date)    startx  /* AP */,
              ISNULL(ISNULL(t.act_end_date, t.reend_date), t.target_end_date)          finishx       /* AP */,
              ISNULL(ISNULL(bl.act_start_date, bl.restart_date), bl.target_start_date) bl_startx /* BL */,
              ISNULL(ISNULL(bl.act_end_date, bl.reend_date), bl.target_end_date)       bl_finishx /* BL */
            FROM
              project p
              INNER JOIN task t
                ON t.proj_id = p.proj_id
              LEFT JOIN task bl
                ON bl.proj_id = p.proj_id
                   AND bl.task_code = t.task_code
            WHERE
              bl.proj_id = $ppm_bl_id
        ";

    return $sql;
}
function returnDataScheduleInsertSQL(){
$insert_sql = "
        INSERT INTO schedule.data_schedule (
            ship_code,
            wbs_id,
            clndr_id,
            rsrc_id,
            pc,
            COMPLETE_PCT_TYPE,
            task_id,
            task_code,
            task_name,
            task_type,
            total_float_hr_cnt,
            status_code,
            free_float_hr_cnt,
            remain_drtn_hr_cnt,
            act_work_qty,
            remain_work_qty,
            target_work_qty,
            target_drtn_hr_cnt,
            target_equip_qty,
            act_equip_qty,
            remain_equip_qty,
            cstr_date,
            act_start_date,
            act_end_date,
            late_start_date,
            late_end_date,
            expect_end_date,
            early_start_date,
            early_end_date,
            restart_date,
            reend_date,
            target_start_date,
            target_end_date,
            review_end_date,
            rem_late_start_date,
            rem_late_end_date,
            cstr_type,
            priority_type,
            cstr_date2,
            cstr_type2,
            act_this_per_work_qty,
            act_this_per_equip_qty,
            driving_path_flag,
            float_path,
            float_path_order,
            suspend_date,
            resume_date,
            external_early_start_date,
            external_late_end_date,
            delete_date,
            startx,
            finishx,
            bl_startx,
            bl_finishx) VALUES 
    ";

    return $insert_sql;
}
function returnInsertValuesSQL($ship_code,$wbs_id,$clndr_id,$rsrc_id,$pc,$complete_pct_type,
                               $task_id,$task_code,$task_name,$task_type,$total_float_hr_cnt,
                               $status_code,$free_float_hr_cnt,$remain_drtn_hr_cnt,$act_work_qty,
                               $remain_work_qty,$target_work_qty,$target_drtn_hr_cnt,$target_equip_qty,
                               $act_equip_qty,$remain_equip_qty,$cstr_date,$act_start_date,
                               $act_end_date,$late_start_date,$late_end_date,$expect_end_date,
                               $early_start_date,$early_end_date,$restart_date,$reend_date,$target_start_date,
                               $target_end_date,$review_end_date,$rem_late_start_date,$rem_late_end_date,
                               $cstr_type,$priority_type,$cstr_date2,$cstr_type2,$act_this_per_work_qty,
                               $act_this_per_equip_qty,$driving_path_flag,$float_path,$float_path_order,
                               $suspend_date,$resume_date,$external_early_start_date,$external_late_end_date,
                               $delete_date,$startx,$finishx,$bl_startx,$bl_finishx){
    $sql = "
            ($ship_code,
            $wbs_id,
            $clndr_id,
            $rsrc_id,
            $pc,
            '$complete_pct_type',
            $task_id,
            '$task_code',
            '$task_name',
            '$task_type',
            $total_float_hr_cnt,
            '$status_code',
            $free_float_hr_cnt,
            $remain_drtn_hr_cnt,
            $act_work_qty,
            $remain_work_qty,
            $target_work_qty,
            $target_drtn_hr_cnt,
            $target_equip_qty,
            $act_equip_qty,
            $remain_equip_qty,
            '$cstr_date',
            '$act_start_date',
            '$act_end_date',
            '$late_start_date',
            '$late_end_date',
            '$expect_end_date',
            '$early_start_date',
            '$early_end_date',
            '$restart_date',
            '$reend_date',
            '$target_start_date',
            '$target_end_date',
            '$review_end_date',
            '$rem_late_start_date',
            '$rem_late_end_date',
            '$cstr_type',
            '$priority_type',
            '$cstr_date2',
            '$cstr_type2',
            $act_this_per_work_qty,
            $act_this_per_equip_qty,
            '$driving_path_flag',
            '$float_path',
            '$float_path_order',
            '$suspend_date',
            '$resume_date',
            '$external_early_start_date',
            '$external_late_end_date',
            '$delete_date',
            '$startx',
            '$finishx',
            '$bl_startx',
            '$bl_finishx'),";
    return $sql;
} 
if($control=="project_grid")
{
    $data = "[";
    $sql = "select id, name, code, ppm_ap_id, ppm_bl_id from fmm_evms.master_project where name not like '%27%' order by code";
    //print $sql;
    $rs = dbCall($sql);
    while (!$rs->EOF)
    {
        $pmid      = $rs->fields["id"];
        $name      = $rs->fields["name"];
        $code      = $rs->fields["code"];
        $ppm_ap_id = $rs->fields["ppm_ap_id"];
        $ppm_bl_id = $rs->fields["ppm_bl_id"];
        $data.="{
            \"id\"          :$pmid,
            \"ppm_ap_id\"   :$ppm_ap_id,
            \"ppm_bl_id\"   :$ppm_bl_id,
            \"name\"        :\"$name\",
            \"ship_code\"   :\"$code\"
        },";
        $rs->MoveNext();
    }
    $data = substr($data, 0, -1);
    $data.="]";
    die($data);
}
if($control=="load_schedule_data"){
    deleteFromTable("schedule", "data_schedule", "ship_code", $code);
    //die("m,dae it");
    $sql = returnP6ScheduleSQL($ppm_ap_id);

    $rs = dbCallP6($sql);
    $insert_sql = returnDataScheduleInsertSQL();
    $sql = $insert_sql;
    $i = 0;
    while (!$rs->EOF)
    {
        $ship_code         = intval($code);
        $wbs_id            = intval($rs->fields["wbs_id"]);
        $clndr_id          = intval($rs->fields["clndr_id"]);
        $rsrc_id           = intval($rs->fields["rsrc_id"]);
        $pc                = formatNumber4decNoComma($rs->fields["pc"]);
        $complete_pct_type = $rs->fields["complete_pct_type"];

        $task_id            = intval($rs->fields["task_id"]);
        $task_code          = $rs->fields["task_code"];
        $task_name          = processDescription($rs->fields["task_name"]);
        $task_type          = $rs->fields["task_type"];
        $total_float_hr_cnt = formatNumber4decNoComma($rs->fields["total_float_hr_cnt"]);

        $status_code        = $rs->fields["status_code"];
        $free_float_hr_cnt  = formatNumber4decNoComma($rs->fields["free_float_hr_cnt"]);
        $remain_drtn_hr_cnt = formatNumber4decNoComma($rs->fields["remain_drtn_hr_cnt"]);
        $act_work_qty       = formatNumber4decNoComma($rs->fields["act_work_qty"]);

        $remain_work_qty    = formatNumber4decNoComma($rs->fields["remain_work_qty"]);
        $target_work_qty    = formatNumber4decNoComma($rs->fields["target_work_qty"]);
        $target_drtn_hr_cnt = formatNumber4decNoComma($rs->fields["target_drtn_hr_cnt"]);
        $target_equip_qty   = formatNumber4decNoComma($rs->fields["target_equip_qty"]);

        $act_equip_qty    = formatNumber4decNoComma($rs->fields["act_equip_qty"]);
        $remain_equip_qty = formatNumber4decNoComma($rs->fields["remain_equip_qty"]);
        $cstr_date        = fixExcelDateMySQL($rs->fields["cstr_date"]);
        $act_start_date   = fixExcelDateMySQL($rs->fields["act_start_date"]);

        $act_end_date    = fixExcelDateMySQL($rs->fields["act_end_date"]);
        $late_start_date = fixExcelDateMySQL($rs->fields["late_start_date"]);
        $late_end_date   = fixExcelDateMySQL($rs->fields["late_end_date"]);
        $expect_end_date = fixExcelDateMySQL($rs->fields["expect_end_date"]);

        $early_start_date  = fixExcelDateMySQL($rs->fields["early_start_date"]);
        $early_end_date    = fixExcelDateMySQL($rs->fields["early_end_date"]);
        $restart_date      = fixExcelDateMySQL($rs->fields["restart_date"]);
        $reend_date        = fixExcelDateMySQL($rs->fields["reend_date"]);
        $target_start_date = fixExcelDateMySQL($rs->fields["target_start_date"]);

        $target_end_date     = fixExcelDateMySQL($rs->fields["target_end_date"]);
        $review_end_date     = fixExcelDateMySQL($rs->fields["review_end_date"]);
        $rem_late_start_date = fixExcelDateMySQL($rs->fields["rem_late_start_date"]);
        $rem_late_end_date   = fixExcelDateMySQL($rs->fields["rem_late_end_date"]);

        $cstr_type             = $rs->fields["cstr_type"];
        $priority_type         = $rs->fields["priority_type"];
        $cstr_date2            = fixExcelDateMySQL($rs->fields["cstr_date2"]);
        $cstr_type2            = fixExcelDateMySQL($rs->fields["cstr_type2"]);
        $act_this_per_work_qty = formatNumber4decNoComma($rs->fields["act_this_per_work_qty"]);

        $act_this_per_equip_qty = formatNumber4decNoComma($rs->fields["act_this_per_equip_qty"]);
        $driving_path_flag      = $rs->fields["driving_path_flag"];
        $float_path             = $rs->fields["float_path"];
        $float_path_order       = $rs->fields["float_path_order"];

        $suspend_date              = fixExcelDateMySQL($rs->fields["suspend_date"]);
        $resume_date               = fixExcelDateMySQL($rs->fields["resume_date"]);
        $external_early_start_date = fixExcelDateMySQL($rs->fields["external_early_start_date"]);
        $external_late_end_date    = fixExcelDateMySQL($rs->fields["external_late_end_date"]);

        $delete_date = fixExcelDateMySQL($rs->fields["delete_date"]);
        $startx      = fixExcelDateMySQL($rs->fields["startx"]);
        $finishx     = fixExcelDateMySQL($rs->fields["finishx"]);
        $bl_startx   = fixExcelDateMySQL($rs->fields["bl_startx"]);
        $bl_finishx  = fixExcelDateMySQL($rs->fields["bl_finishx"]);

        $sql .= returnInsertValuesSQL($ship_code,$wbs_id,$clndr_id,$rsrc_id,$pc,$complete_pct_type,
            $task_id,$task_code,$task_name,$task_type,$total_float_hr_cnt,
            $status_code,$free_float_hr_cnt,$remain_drtn_hr_cnt,$act_work_qty,
            $remain_work_qty,$target_work_qty,$target_drtn_hr_cnt,$target_equip_qty,
            $act_equip_qty,$remain_equip_qty,$cstr_date,$act_start_date,
            $act_end_date,$late_start_date,$late_end_date,$expect_end_date,
            $early_start_date,$early_end_date,$restart_date,$reend_date,$target_start_date,
            $target_end_date,$review_end_date,$rem_late_start_date,$rem_late_end_date,
            $cstr_type,$priority_type,$cstr_date2,$cstr_type2,$act_this_per_work_qty,
            $act_this_per_equip_qty,$driving_path_flag,$float_path,$float_path_order,
            $suspend_date,$resume_date,$external_early_start_date,$external_late_end_date,
            $delete_date,$startx,$finishx,$bl_startx,$bl_finishx);
        if($i == 500)
        {
            $sql = substr($sql, 0, -1);
            $junk = dbCall($sql, "schedule");

            $i=0;
            //clear out the sql stmt.
            $sql = $insert_sql;
        }
        $i++;
        $rs->MoveNext();
    }
    //only insert remaining lines if the total number is not divisble by 1000.
    if($i !=500)
    {
        $sql = substr($sql, 0, -1);
        $junk = dbCall($sql, "schedule");
    }
    print $sql;
}