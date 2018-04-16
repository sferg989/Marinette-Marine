<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 9/7/2017
 * Time: 3:07 PM
 */

function loadScheduleData($cid,$db_server='localhost',$debug=false)
{
    //global $log_file;

    updatePPMAPDataDate($cid,$debug,$db_server);

    $sql  = "delete from schedule where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    //$sql  = "delete from schedule_zrio where proj_id=$cid";
    //$junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_resources where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_steps where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql  = "delete from schedule_taskpred where proj_id=$cid";
    $junk = dbCall($sql,$debug,'schedule_data',$db_server);

    $sql = "select program_group,ppm_bl_id from project_master where ppm_ap_id=$cid";
    $rs = dbCall($sql,$debug,'tools_data',$db_server);
    $bid = $rs->fields['ppm_bl_id'];
    $program_group = $rs->fields['program_group'];

    //if($bid=='') die('Baseline Info is missing from the Project Master table.  Please contact Scott Hathaway (x6687).');
    if($bid=='')
    {
        $bid = $cid;
        /*
        //string2file($log_file,"\nBaseline Info is missing from the Project Master table for ppm_ap_id $cid.  Please contact Tim Allums (x6687). - ".date("Y-m-d H:i:s")."\n",'a');
        $to   = "SHathaway@bh.com;PBell@bh.com;tallums@bh.com;";
        $sub  = "Baseline Info Missing";
        $msg  = "Baseline Info is missing from the Project Master table for ppm_ap_id $cid
                ";
        myMail($to,$sub,$msg);
        */
    }

    if($total_recs=='')
    {
        $sql = "SELECT count(*) as recs FROM admuser.task where proj_id=$cid";
        $rs = dbCall_Oracle($sql,$debug,'A019PROD');
        $total_recs = $rs->fields['RECS'];
        //print " count=$total_recs\n";
    }
    //exit();
    $recs_per_page = 4000;
    $total_pages   = $total_recs / $recs_per_page;
    $temp          = explode(".","$total_pages");
    $total_pages   = (int)$temp[0];
    if((int)$temp[1]>0) $total_pages++;

    $cur_page = 1;

    while($cur_page<=$total_pages)
    {
        $l = ($recs_per_page*$cur_page)-$recs_per_page+1;
        $u = $l + $recs_per_page-1;
        if($u>$total_recs) $u=$total_recs;

        if($debug) print "l=$l|u=$u|bid=$bid|cur_page=$cur_page|total_pages=$total_pages\n";
        //exit();

        //print "\n      Page $cur_page\n";

        /*
        $sql = "
        SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275 FROM
        (SELECT PARTCODE_201, PARTNO_201, MDCNNO_275, RQDATE8_275, ROWNUM r FROM FRH_MRP.PSK02275_OPEN)
        WHERE r BETWEEN $l AND $u
        ";
        //print "<hr>$sql<hr>";
        ///*
        $rs = dbCall_Oracle($sql,true,'DWPROD');
        */


        $sql = "
select a2.*


                      ,(SELECT count(task_code) FROM privuser.TASK, privuser.TASKPRED
                WHERE pred_task_id=privuser.TASK.task_id AND privuser.TASKPRED.task_id=
                  (SELECT task_id FROM privuser.TASK WHERE task_code=a2.task_code AND proj_id=a2.proj_id)) as num_pred

                        ,(select count(task_code) from privuser.TASK,privuser.TASKPRED where
                privuser.TASK.task_id=privuser.TASKPRED.task_id AND privuser.TASKPRED.pred_task_id=
                 (select task_id from privuser.TASK where task_code=a2.task_code and proj_id=a2.proj_id)) as num_succ

  from ( select a.*, rownum rnum
           from (
with d as (
SELECT t.proj_id     ,p.proj_short_name
                      ,t.wbs_id
                      ,t.clndr_id
                      ,t.rsrc_id
                      ,t.phys_complete_pct AS pc
                      ,t.COMPLETE_PCT_TYPE
                      ,t.task_id
                      ,t.task_code
                      ,t.task_name
                      ,t.task_type
                      ,t.total_float_hr_cnt
                      ,t.status_code
                      ,t.free_float_hr_cnt
                      ,t.remain_drtn_hr_cnt
                      ,t.act_work_qty
                      ,t.remain_work_qty
                      ,t.target_work_qty
                      ,t.target_drtn_hr_cnt
                      ,t.target_equip_qty
                      ,t.act_equip_qty
                      ,t.remain_equip_qty
                      ,t.cstr_date
                      ,t.act_start_date
                      ,t.act_end_date
                      ,t.late_start_date
                      ,t.late_end_date
                      ,t.expect_end_date
                      ,t.early_start_date
                      ,t.early_end_date
                      ,t.restart_date
                      ,t.reend_date
                      ,t.target_start_date
                      ,t.target_end_date
                      ,t.review_end_date
                      ,t.rem_late_start_date
                      ,t.rem_late_end_date
                      ,t.cstr_type
                      ,t.priority_type
                      ,t.cstr_date2
                      ,t.cstr_type2
                      ,t.act_this_per_work_qty
                      ,t.act_this_per_equip_qty
                      ,t.driving_path_flag
                      ,t.float_path
                      ,t.float_path_order
                      ,t.suspend_date
                      ,t.resume_date
                      ,t.external_early_start_date
                      ,t.external_late_end_date
                      ,t.delete_date
                      ,nvl(nvl(t.act_start_date,t.restart_date),t.target_start_date) startx  -- AP
                      ,nvl(nvl(t.act_end_date,t.reend_date),t.target_end_date) finishx       -- AP
                      ,nvl(nvl(bl.act_start_date,bl.restart_date),bl.target_start_date) bl_startx -- BL
                      ,nvl(nvl(bl.act_end_date,bl.reend_date),bl.target_end_date) bl_finishx      -- BL
                  FROM privuser.project p   
                      ,privuser.task    t
                      ,privuser.task bl
                 WHERE p.proj_id = $cid
                   AND t.proj_id = p.proj_id
                   and bl.proj_id (+) = $bid
                   and bl.task_code (+) = t.task_code)
  select * FROM (SELECT d.proj_id
              ,d.proj_short_name
              ,d.wbs_id
              ,d.clndr_id
              ,d.rsrc_id
              ,d.pc
              ,d.COMPLETE_PCT_TYPE
              ,d.task_id x
              ,d.task_code
              ,d.task_name
              ,d.task_type
              ,d.total_float_hr_cnt
              ,d.status_code
              ,d.free_float_hr_cnt
              ,d.remain_drtn_hr_cnt
              ,d.act_work_qty
              ,d.remain_work_qty
              ,d.target_work_qty
              ,d.target_drtn_hr_cnt
              ,d.target_equip_qty
              ,d.act_equip_qty
              ,d.remain_equip_qty
              ,d.cstr_date
              ,d.act_start_date
              ,d.act_end_date
              ,d.late_start_date
              ,d.late_end_date
              ,d.expect_end_date
              ,d.early_start_date
              ,d.early_end_date
              ,d.restart_date
              ,d.reend_date
              ,d.target_start_date
              ,d.target_end_date
              ,d.review_end_date
              ,d.rem_late_start_date
              ,d.rem_late_end_date
              ,d.cstr_type
              ,d.priority_type
              ,d.cstr_date2
              ,d.cstr_type2
              ,d.act_this_per_work_qty
              ,d.act_this_per_equip_qty
              ,d.driving_path_flag
              ,d.float_path
              ,d.float_path_order
              ,d.suspend_date
              ,d.resume_date
              ,d.external_early_start_date
              ,d.external_late_end_date
              ,d.delete_date
              ,d.startx
              ,d.finishx
              ,d.bl_startx
              ,d.bl_finishx
              ,ta.*
              ,wpkg
              ,ipt_level_3
              ,ipt_level_4
              ,csa
              ,program_code
              ,team_code
          FROM (SELECT d.*
                  FROM d) d
              ,(SELECT /*+ ORDERED use_hash(c) */ ta.task_id
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.CAM' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS cam
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.CA #' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ca
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'A/C#' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS aircraft
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'Watch Part (APS)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS aps
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.EV Method' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ev_method
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'ICP' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS icp
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'IPS' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ips
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.IPT' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS ipt
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.Work Package Lead' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_lead
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.Work Package Status' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_status
                      ,MAX(CASE
                             WHEN at.actv_code_type = '.Work Package Type' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS work_package_type
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO ID' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_id
                       ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO PHASE' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_status
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO CATEGORY' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_type
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO LEVEL' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_visibility_level
                      ,MAX(CASE
                             WHEN at.actv_code_type =
                                  'RIO ASSESSMENT (AP)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_severity_assessment_ap
                      ,MAX(CASE
                             WHEN at.actv_code_type =
                                  'RIO ASSESSMENT (BL)' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_severity_assessment_bl
                      ,MAX(CASE
                             WHEN at.actv_code_type = 'RIO OWNER' THEN
                              c.short_name
                             ELSE
                              NULL
                           END) AS rio_owner
                  FROM privuser.actvtype at
                      ,privuser.taskactv ta
                      ,privuser.actvcode c
                 WHERE at.actv_code_type_scope = 'AS_Global'
                   AND at.actv_code_type IN
                       ('.CAM', '.CA #', 'A/C#', 'Watch Part (APS)',
                        '.EV Method', 'ICP', 'IPS', '.IPT',
                        '.Work Package Lead', '.Work Package Status',
                        '.Work Package Type', 'RIO ID', 'RIO PHASE','RIO STATUS',
                        'RIO LEVEL', 'RIO CATEGORY',
                        'RIO ASSESSMENT (AP)',
                        'RIO ASSESSMENT (BL)', 'RIO OWNER')
                   AND ta.proj_id = $cid
                   AND ta.actv_code_id = c.actv_code_id
                   AND ta.actv_code_type_id = at.actv_code_type_id
                 GROUP BY ta.task_id) ta ,(SELECT /*+ USE_HASH (u) */ fk_id wbs_id
      ,max(CASE WHEN udf_type_label = '06-Charge #' then udf_text else null end) wpkg
      ,max(CASE WHEN udf_type_label = '12-CM Task User 6' then udf_text else null end) ipt_level_3
      ,max(CASE WHEN udf_type_label = '13-CM Task User 7' then udf_text else null end) ipt_level_4
      ,max(CASE WHEN udf_type_label = '15-CM Task User 9' then udf_text else null end) csa
      ,max(CASE WHEN udf_type_label = '10-CM Task User 3' then udf_text else null end) program_code
      ,max(CASE WHEN udf_type_label = '16-CM Task User 10' then udf_text else null end) team_code
  FROM (SELECT DISTINCT wbs_id
                       ,udf_type_id, udf_type_label
          FROM d
              ,privuser.udftype ut
         WHERE udf_type_label IN ('06-Charge #', '12-CM Task User 6',
                '13-CM Task User 7', '15-CM Task User 9',
                '10-CM Task User 3', '16-CM Task User 10')
           AND table_name = 'PROJWBS') x
      ,privuser.udfvalue u
 WHERE x.wbs_id  = u.fk_id (+)
   and x.udf_type_id  = u.udf_type_id (+)
   and u.proj_id (+) = $cid
   group by fk_id) uv
       WHERE d.task_id = ta.task_id(+)
         and uv.wbs_id (+) = d.wbs_id )
) a
          where rownum <= $u ) a2
 where rnum >= $l

";

        //string2file("e:/sql.txt",$sql,'w');
        //print "done";
        //exit();
        $rs = null;
        $rs = dbCall_Oracle($sql,false,'A019PROD');
        //exit();
        //array_debug($rs);
        //print "rc = " . $rs->RecordCount() . "\n";
        //exit();
        //$rs=false;
        if($rs)
        {
            $i=1;
            while(!$rs->EOF)
            {
                $db                            = array();
                $db['program_group']     = $program_group;
                $db['proj_id']            = $rs->fields['PROJ_ID'];
                $db['proj_short_name']   = $rs->fields['PROJ_SHORT_NAME'];
                $db['controlaccount']      = $rs->fields['CA'];
                $db['cam']                   = addslashes($rs->fields['CAM']);
                $db['csa']                 = addslashes($rs->fields['CSA']);
                $db['ips']                 = addslashes($rs->fields['IPS']);
                $db['workpackage']         = addslashes($rs->fields['WPKG']);
                $db['task_id']               = $rs->fields['TASK_ID'];
                $db['task_code']               = $rs->fields['TASK_CODE'];
                $db['task_name']             = addslashes($rs->fields['TASK_NAME']);
                if(strlen($db['task_name'])>119) $db['task_name']=left($db['task_name'],120);
                $db['num_pred']              = $rs->fields['NUM_PRED'];
                $db['num_succ']            = $rs->fields['NUM_SUCC'];
                $db['ev_method']          = $rs->fields['EV_METHOD'];
                $db['phys_complete_pct']    = (float)$rs->fields['PC'];
                $db['remain_drtn_hr_cnt'] = $rs->fields['REMAIN_DRTN_HR_CNT'];
                $db['remain_work_qty']   = $rs->fields['REMAIN_WORK_QTY'];
                $db['total_float_hr_cnt']    = (float)$rs->fields['TOTAL_FLOAT_HR_CNT'];
                $db['start']                = fd($rs->fields['STARTX']);
                $db['finish']            = fd($rs->fields['FINISHX']);
                $db['baseline_start']       = fd($rs->fields['BL_STARTX']);
                $db['baseline_finish']      = fd($rs->fields['BL_FINISHX']);
                $db['ipt_level_3']          = addslashes($rs->fields['IPT_LEVEL_3']);
                $db['ipt_level_4']         = addslashes($rs->fields['IPT_LEVEL_4']);
                $db['program_code']         = $rs->fields['PROGRAM_CODE'];
                $db['team_code']            = $rs->fields['TEAM_CODE'];
                $db['wbs_id']            = $rs->fields['WBS_ID'];
                $db['clndr_id']            = $rs->fields['CLNDR_ID'];
                $db['complete_pct_type']    = $rs->fields['COMPLETE_PCT_TYPE'];
                $db['act_start_date']       = fd($rs->fields['ACT_START_DATE']);
                $db['act_end_date']      = fd($rs->fields['ACT_END_DATE']);
                $db['cstr_date']            = fd($rs->fields['CSTR_DATE']);
                $db['remain_equip_qty']    = $rs->fields['REMAIN_EQUIP_QTY'];
                $db['target_work_qty']    = $rs->fields['TARGET_WORK_QTY'];
                $db['target_equip_qty']    = $rs->fields['TARGET_EQUIP_QTY'];
                $db['active']               = 1;
                $db['status_code']         = $rs->fields['STATUS_CODE'];
                $db['driving_path_flag']    = $rs->fields['DRIVING_PATH_FLAG'];
                $db['float_path']        = $rs->fields['FLOAT_PATH'];
                $db['float_path_order']    = $rs->fields['FLOAT_PATH_ORDER'];
                $db['expect_end_date']    = fd($rs->fields['EXPECT_END_DATE']);
                $db['cstr_type']         = $rs->fields['CSTR_TYPE'];
                $db['priority_type']        = $rs->fields['PRIORITY_TYPE'];
                $db['aps_code']             = $rs->fields['APS'];
                $db['aircraft']           = $rs->fields['AIRCRAFT'];
                $db['icp']                 = $rs->fields['ICP'];
                $db['ipt']                 = $rs->fields['IPT'];
                $db['work_package_type']    = $rs->fields['WORK_PACKAGE_TYPE'];
                $db['early_start_date']    = fd($rs->fields['EARLY_START_DATE']);
                $db['early_end_date']    = fd($rs->fields['EARLY_END_DATE']);
                $db['work_package_status'] = $rs->fields['WORK_PACKAGE_STATUS'];
                $db['free_float_hr_cnt']    = (float)$rs->fields['FREE_FLOAT_HR_CNT'];
                $db['budgeted_hours']    = 0;
                $db['budgeted_dollars']    = 0;
                $db['work_package_lead']    = addslashes($rs->fields['WORK_PACKAGE_LEAD']);
                $db['rio_id']               = $rs->fields['RIO_ID'];
                $db['rio_type']             = $rs->fields['RIO_TYPE'];
                $db['rio_visibility_level']    = $rs->fields['RIO_VISIBILITY_LEVEL'];
                $db['rio_severity_assessment']    = $rs->fields['RIO_SEVERITY_ASSESSMENT_AP'];
                $db['rio_owner']                    = $rs->fields['RIO_OWNER'];
                $db['rio_status']                  = $rs->fields['RIO_STATUS'];
                $db['rio_severity_assessment_bl']    = $rs->fields['RIO_SEVERITY_ASSESSMENT_BL'];
                $db['task_type']                        = $rs->fields['TASK_TYPE'];

                if(trim($db['controlaccount'])==''or trim($db['controlaccount'])=='na' or trim($db['controlaccount'])=='NA' or trim($db['controlaccount'])=='N/A' or trim($db['controlaccount'])=='n/a') $db['controlaccount']='NOCA';
                if(trim($db['workpackage'])==''or trim($db['workpackage'])=='na' or trim($db['workpackage'])=='NA' or trim($db['workpackage'])=='N/A' or trim($db['workpackage'])=='n/a') $db['workpackage']='NOWP';

                $db['num_pred'] = (int)$db['num_pred'];
                $db['num_succ'] = (int)$db['num_succ'];
                $db['float_path'] = (int)$db['float_path'];
                $db['float_path_order'] = (int)$db['float_path_order'];


                $debug=false;
                //if($db['task_code']=='KT5LR14369' or $db['task_code']=='L510910' or $db['task_code']=='L510510' or $db['task_code']=='KT5LH0689' or $db['task_code']=='KT5LD0212') $debug=true;
                $result = getReplaceSQL('schedule',$db,'id',$debug,'schedule_data',false,$db_server);
                //print "result=".(int)$result."\n";
                //if((int)$result<>1 and (int)$result<>2) print $db['task_code'] . "\n";
                //if($debug) exit();
                $debug=false;
                //if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',false,'schedule_data',true);
                //if($rs->fields['RIO_ID']!='') $result = getReplaceSQL('schedule_zrio',$db,'id',$debug,'schedule_data',false,$db_server);

                //exit();
                //print "l=$l|u=$u<br>";
                if($debug and ($i%1000)==0) print ".";

                $i++;
                $rs->MoveNext();
            }
        }
        $cur_page++;
    }
    //exit();


    // RESOURCES
    $sql = "
        select
            tr.*,
            (select rsrc_short_name from privuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_short_name,
            (select rsrc_name from privuser.rsrc where rsrc_id=tr.rsrc_id) as rsrc_name
        from
            privuser.taskrsrc tr
        where
            tr.proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,false,'A019PROD');

    if($rs)
    {
        while(!$rs->EOF)
        {
            $db                                = array();
            $db['taskrsrc_id']            = $rs->fields['TASKRSRC_ID'];
            $db['task_id']               = $rs->fields['TASK_ID'];
            $db['proj_id']                  = $rs->fields['PROJ_ID'];
            $db['cost_qty_link_flag']    = $rs->fields['COST_QTY_LINK_FLAG'];
            $db['role_id']                   = $rs->fields['ROLE_ID'];
            $db['acct_id']                    = $rs->fields['ACCT_ID'];
            $db['rsrc_id']                  = $rs->fields['RSRC_ID'];
            $db['skill_level']                = $rs->fields['SKILL_LEVEL'];
            $db['pend_complete_pct']     = $rs->fields['PEND_COMPLETE_PCT'];
            $db['remain_qty']               = $rs->fields['REMAIN_QTY'];
            $db['pend_remain_qty']         = $rs->fields['PEND_REMAIN_QTY'];
            $db['target_qty']             = $rs->fields['TARGET_QTY'];
            $db['remain_qty_per_hr']        = $rs->fields['REMAIN_QTY_PER_HR'];
            $db['pend_act_reg_qty']        = $rs->fields['PEND_ACT_REG_QTY'];
            $db['target_lag_drtn_hr_cnt']= $rs->fields['TARGET_LAG_DRTN_HR_CNT'];
            $db['target_qty_per_hr']       = $rs->fields['TARGET_QTY_PER_HR'];
            $db['act_ot_qty']              = $rs->fields['ACT_OT_QTY'];
            $db['pend_act_ot_qty']          = $rs->fields['PEND_ACT_OT_QTY'];
            $db['act_reg_qty']           = $rs->fields['ACT_REG_QTY'];
            $db['relag_drtn_hr_cnt']     = $rs->fields['RELAG_DRTN_HR_CNT'];
            $db['ot_factor']                   = $rs->fields['OT_FACTOR'];
            $db['cost_per_qty']             = $rs->fields['COST_PER_QTY'];
            $db['target_cost']           = $rs->fields['TARGET_COST'];
            $db['act_reg_cost']             = $rs->fields['ACT_REG_COST'];
            $db['act_ot_cost']             = $rs->fields['ACT_OT_COST'];
            $db['remain_cost']               = $rs->fields['REMAIN_COST'];
            $db['act_start_date']         = $rs->fields['ACT_START_DATE'];
            $db['act_end_date']            = $rs->fields['ACT_END_DATE'];
            $db['restart_date']           = $rs->fields['RESTART_DATE'];
            $db['reend_date']              = $rs->fields['REEND_DATE'];
            $db['target_start_date']           = $rs->fields['TARGET_START_DATE'];
            $db['target_end_date']        = $rs->fields['TARGET_END_DATE'];
            $db['rem_late_start_date']     = $rs->fields['REM_LATE_START_DATE'];
            $db['rem_late_end_date']        = $rs->fields['REM_LATE_END_DATE'];
            $db['guid']                       = $rs->fields['GUID'];
            $db['rate_type']                 = $rs->fields['RATE_TYPE'];
            $db['act_this_per_cost']        = $rs->fields['ACT_THIS_PER_COST'];
            $db['act_this_per_qty']        = $rs->fields['ACT_THIS_PER_QTY'];
            $db['curv_id']                  = $rs->fields['CURV_ID'];
            $db['rsrc_request_data']     = $rs->fields['RSRC_REQUEST_DATA'];
            $db['rsrc_type']                  = $rs->fields['RSRC_TYPE'];
            $db['rollup_dates_flag']        = $rs->fields['ROLLUP_DATES_FLAG'];
            $db['cost_per_qty_source_type'] = $rs->fields['COST_PER_QTY_SOURCE_TYPE'];
            $db['update_date']               = $rs->fields['UPDATE_DATE'];
            $db['update_user']              = $rs->fields['UPDATE_USER'];
            $db['create_date']              = $rs->fields['CREATE_DATE'];
            $db['create_user']              = $rs->fields['CREATE_USER'];
            $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
            $db['delete_date']             = $rs->fields['DELETE_DATE'];
            $db['rsrc_short_name']          = $rs->fields['RSRC_SHORT_NAME'];
            $db['rsrc_name']              = $rs->fields['RSRC_NAME'];

            $result = getReplaceSQL('schedule_resources',$db,'id',$debug,'schedule_data',true,$db_server);

            $rs->MoveNext();
        }
    }

    // STEPS
    $sql = "
        select
            *
        from
            admuser.taskproc
        where
            proj_id=$cid
    ";

    $rs = dbCall_Oracle($sql,$debug,'A019PROD');

    if($rs)
    {
        while(!$rs->EOF)
        {
            $db                                = array();
            $db['proc_id']                = $rs->fields['PROC_ID'];
            $db['task_id']               = $rs->fields['TASK_ID'];
            $db['seq_num']                  = $rs->fields['SEQ_NUM'];
            $db['proj_id']                  = $rs->fields['PROJ_ID'];
            $db['complete_flag']            = $rs->fields['COMPLETE_FLAG'];
            $db['proc_name']                = $rs->fields['PROC_NAME'];
            $db['proc_wt']                  = $rs->fields['PROC_WT'];
            $db['complete_pct']                = $rs->fields['COMPLETE_PCT'];
            $db['proc_descr']             = $rs->fields['PROC_DESCR'];
            $db['update_date']               = $rs->fields['UPDATE_DATE'];
            $db['update_user']             = $rs->fields['UPDATE_USER'];
            $db['create_date']             = $rs->fields['CREATE_DATE'];
            $db['create_user']              = $rs->fields['CREATE_USER'];
            $db['delete_session_id']        = $rs->fields['DELETE_SESSION_ID'];
            $db['delete_date']            = $rs->fields['DELETE_DATE'];

            $result = getReplaceSQL('schedule_steps',$db,'id',$debug,'schedule_data',true,$db_server);

            $rs->MoveNext();
        }

    }



    // TASKPRED
    $sql = "SELECT * FROM ADMUSER.TASKPRED where proj_id=$cid";
    $rs = dbCall_Oracle($sql,$debug,'A019PROD');
    if($rs)
    {
        while(!$rs->EOF)
        {
            $db                             = array();
            $db['TASK_PRED_ID']             = $rs->fields['TASK_PRED_ID'];
            $db['TASK_ID']                  = $rs->fields['TASK_ID'];
            $db['PRED_TASK_ID']             = $rs->fields['PRED_TASK_ID'];
            $db['PROJ_ID']                  = $rs->fields['PROJ_ID'];
            $db['PRED_PROJ_ID']             = $rs->fields['PRED_PROJ_ID'];
            $db['PRED_TYPE']                = $rs->fields['PRED_TYPE'];
            $db['LAG_HR_CNT']               = $rs->fields['LAG_HR_CNT'];
            $db['UPDATE_DATE']              = $rs->fields['UPDATE_DATE'];
            $db['UPDATE_USER']              = $rs->fields['UPDATE_USER'];
            $db['CREATE_DATE']              = $rs->fields['CREATE_DATE'];
            $db['CREATE_USER']              = $rs->fields['CREATE_USER'];
            $db['DELETE_SESSION_ID']        = $rs->fields['DELETE_SESSION_ID'];
            $db['DELETE_DATE']              = $rs->fields['DELETE_DATE'];


            $result = getReplaceSQL('schedule_taskpred',$db,'id',$debug,'schedule_data',true,$db_server);

            $rs->MoveNext();
        }
    }


    return true;
}
