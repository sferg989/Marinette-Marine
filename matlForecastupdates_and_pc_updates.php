<?php
include("inc/inc.php");
include("inc/inc.cobra.php");

function getTotalActuals($ship_code, $cawpid){
    $sql = "
    select 
        sum(DIRECT) as sum 
        from tphase 
        where PROGRAM = '$ship_code' 
        and CAWPID =$cawpid   
        and CLASS = 'Actual' 
    group by CAWPID
";
    //print $sql;
    $rs = dbCallCobra($sql);
    $a         = $rs->fields["sum"];
    return $a;
}
function getTotalActualsODC($ship_code, $cawpid){
    $sql = "
    select
  (sum(DIRECT) +
 sum(GANDA)+
 sum(SYSGA) +
 sum(ODCNOGA)) sum
        from tphase
        where PROGRAM = '$ship_code'
        and CAWPID =$cawpid
        and CLASS in('Actual', 'EA', 'CA')
    group by CAWPID
";
    //print $sql;
    $rs = dbCallCobra($sql);
    $a         = $rs->fields["sum"];
    return $a;
}
function getCUREAC($ship_code, $cawpid){
    $sql = "select eac from CAWP where PROGRAM = '$ship_code' and CAWPID = $cawpid";
    //print $sql;
    $rs = dbCallCobra($sql);
    $eac         = $rs->fields["eac"];
    return $eac;
}
function getCURLFD($ship_code, $cawpid){
    $sql = "select lfd from CAWP where PROGRAM = '$ship_code' and CAWPID = $cawpid";
    //print $sql;
    $rs = dbCallCobra($sql);
    $lfd         = $rs->fields["lfd"];
    return $lfd;
}
function getTotalNumberofForecastRecords($ship_code, $cawpid){
    $sql = "
        select count(*) as count 
        from tphase 
        where 
        PROGRAM = '$ship_code' 
        and CAWPID =$cawpid 
        and CLASS = 'Forecast'
";
    $rs = dbCallCobra($sql);
    $count         = $rs->fields["count"];
    return $count;
}
function checkIFForecastRecordExists($ship_code, $cawpid){
    $sql = "
    select count(*) count 
    from TPHASE 
    where PROGRAM = '$ship_code' 
    and CLASS = 'Forecast' 
    and CAWPID = $cawpid 
    GROUP BY PROGRAM, CAWPID;
    ";
    $rs     = dbCallCobra($sql);
    $count  = $rs->fields["count"];
    return $count;
}

$path2file =  "C:/evms/matl_updates/0483.xlsx";
require('inc/lib/php/spreadsheet-reader-master/spreadsheet-reader-master/SpreadsheetReader.php');

$ship_code          = '0483';
$Reader             = new SpreadsheetReader($path2file);
$sql_array          = array();
$rpt_period         = 201710;
$next_rpt_period    = getNextRPTPeriod($rpt_period);
$year               = intval(substr($next_rpt_period, 0, 4));
$month              = month2digit(substr($next_rpt_period, -2));
$day                = getMonthEndDay($next_rpt_period);
if($day<5){
    $month = $month+1;
}

foreach ($Reader as $Row)
{
    if($i<1){
        $i++;
        continue;
    }

    $wp      = $Row[0];
    $pc      = formatNumber4decNoComma($Row[1]*100);

    if($pc > 100 ){
        $pc = 100;
    }

    $new_eac         = formatNumber4decNoComma($Row[2]);
    $cawpid          = getCAWPID($ship_code, $wp);
    if($cawpid==""){
        $i++;
        continue;
    }
    //$a               = formatNumber4decNoComma(getTotalActuals($ship_code, $cawpid));
    //only ODC's
    $a               = formatNumber4decNoComma(getTotalActualsODC($ship_code, $cawpid));
    $new_etc         = formatNumber4decNoComma($new_eac - $a);
    $forcast_records = getTotalNumberofForecastRecords($ship_code, $cawpid);
    if($forcast_records<1){
        $forcast_records = 1;
    }
    $spread_val      = formatNumber4decNoComma($new_etc / $forcast_records);


    $record_count =checkIFForecastRecordExists($ship_code, $cawpid);
    if($record_count<1){
        //get the rest of the periods for this WP
        //insert all the records for each period between the next month, and the end of the last month.
        //print $wp ." DOES NOT EXIST IN THE TPHASE TABLE <br>";
        $sql = "insert into TPHASE (PROGRAM, CAWPID, CECODE, CLASS, DF_DATE, DIRECT) values ('$ship_code', $cawpid, 'MATL', 'Forecast', '$year-$month-$day 00:00:00.000',$new_etc)";
        //runSQLCommandUtil($ship_code,$sql, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
        $sql_array[]= $sql;
        //print $sql."<br>";

    }
    $sql_updateCAWP = "update cawp set eac = $new_eac, EAC_NONLAB = $new_eac, PC_COMP = $pc where PROGRAM = '$ship_code' and wp = '$wp'";
    $sql_array[]= $sql_updateCAWP;
    //runSQLCommandUtil($ship_code,$sql_updateCAWP, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);

    $sql_updateTPHASE= "update tphase set DIRECT = $spread_val where program = '$ship_code' and CAWPID in ($cawpid) and CLASS = 'Forecast'";
    $sql_array[] = $sql_updateTPHASE;
    //runSQLCommandUtil($ship_code,$sql_updateTPHASE, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);

    $i++;
}
$split_array = array_chunk($sql_array, 99);
foreach ($split_array as $sql_chunks){
    $sql_implode = implode(";", $sql_chunks);

    array_debug($sql_chunks);
    //print $sql_implode;
    runSQLCommandUtil($ship_code,$sql_implode, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
}
die("made it");
