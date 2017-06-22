<?php
include('../../../../inc/inc.php');
include('../../../../inc/inc.cobra.php');
require("../../../../inc/lib/php/".$g_path2_spreadsheetReader);

$user = $_SESSION["user_name"];

function getToken()
{
    //@ This function returns a random token 32 chars in length
    return substr(md5(uniqid(rand())), -22);
}

if(strlen($code)==3)
{
    $ship_code = "0".$code;
}
$prev_rpt_period    = getPreviousRPTPeriod($rpt_period);
$data               = returnPeriodData($ship_code, $prev_rpt_period,$rpt_period);
$cur_year           = $data["cur_year"];
$cur_year_last2     = $data["cur_year_last2"];
$cur_month          = $data["cur_month"];
$cur_month_letters  = $data["cur_month_letters"];
$ship_name          = $data["ship_name"];

$path2_cobra_dir    = $base_path . "" . $ship_name . "/" . $ship_code ;
$cur_month_dir      = $path2_cobra_dir."/".$ship_code." ".$cur_year."/".$ship_code." ".$cur_month.".".$cur_year_last2." Cobra Processing";
$path2CobraBkup     = $cur_month_dir."/".$ship_code." ".$cur_month_letters." ". $cur_year." Cobra Backups";

if($control =="log_trans"){
    /*step 1.Make Cobra Backup
    Step 2.  create insert STMT.
    2A Status Dat, row_UID, Refno, TransUID.
    3.  perform SQL Utility.
    4.  verify every insert.
    */
    copyProjectFromCobra($ship_code,$path2CobraBkup, $g_path2CobraAPI,$g_path2CMD,$g_path2BAT, "before_MR_Log_transactions",$debug);
    //$path2file           = "C:/program_management_test/Log_analysis_test201703.xlsx";
    $path2file           = "$cur_month_dir/".$ship_code."LogAnalysis.xlsx";
    $Reader = new SpreadsheetReader($path2file);
    $sql ="INSERT  into BASELOG (PROGRAM,BBL_DATE,DEBIT,CREDIT,AMOUNT,LOGCOMMENT,USR_ID,CPR3,TSTAMP,CCN,STATUSDATE,SIG,HOURS,TRANS_UID,REFNO,ROW_UID) values";
    //$ship_code = "0469 B4 Log Trans";
    foreach ($Reader as $Row)
    {
        $excel_rpt_period = addslashes(trim($Row[0]));
        $bcr              = addslashes(trim($Row[2]));
        $db               = formatNumber4decNoComma(trim($Row[10]));
        $mr               = formatNumber4decNoComma(trim($Row[11]));
        $ub               = formatNumber4decNoComma(trim($Row[12]));
        $refno            = getNextRefno($ship_code);
        if($rpt_period==$excel_rpt_period  and $mr!=0){
            $year       = intval(substr($rpt_period, 0, 4));
            $month      = month2digit(substr($rpt_period, -2));
            $day        = getMonthEndDay($rpt_period);

            $month3digit = date("M");
            if($day<5){
                $month = $month+1;
            }
            $hours        = date("H");
            $minutes      = date("i");
            $seconds      = date("s");
            $timestamp    = date("Y-m-d H:m:s");
            $bbl_date     = date("Y-m-d");
            $bbl_date     = "$bbl_date 00:00:00.000";
            $status_date  = "$year-$month-$day 00:00:00.000";
            $log_comment  = "$ship_code $month3digit $year BCR $bcr";
            $cpr3         = 0;
            $hours        = 0;
            $sig          = 0;
            $ccn          = "BCR $bcr";
            $debit_field  = "MR";
            $credit_field = "UB";
            $amt          = -$mr;
            $trans_uid    = getToken();
            $row_uid      = getToken();
            //$trans_uid  = rand(100000000,999999999)."-".rand(100000000,999999999);
            //$row_uid    = rand(1000000000000000000,99999999999999999999);
            $sql.="('$ship_code','$bbl_date','$debit_field','$credit_field',$amt,'$log_comment','$user',$cpr3,'$timestamp','$ccn','$status_date',$sig,$hours,'$trans_uid',$refno,'$row_uid'),";

        }
        else{
            continue;
        }
    }
    $sql = substr($sql, 0, -1);

    runSQLCommandUtil($ship_code,$sql, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
    die();
}


