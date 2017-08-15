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

$path2file =  "C:/evms/matl_updates/0481matlupdates.xlsx";
require('inc/lib/php/spreadsheet-reader-master/spreadsheet-reader-master/SpreadsheetReader.php');

$ship_code       = '0481';
$Reader = new SpreadsheetReader($path2file);

foreach ($Reader as $Row)
{
    if($i<2){
        $i++;
        continue;
    }

    $wp              = $Row[0];
    $new_eac         = $Row[1];
    $cawpid          = getCAWPID($ship_code, $wp);
    $a               = formatNumber4decNoComma(getTotalActuals($ship_code, $cawpid));
    $etc             = formatNumber4decNoComma($new_eac - $a);
    $forcast_records = getTotalNumberofForecastRecords($ship_code, $cawpid);
    $spread_val      = formatNumber4decNoComma($etc / $forcast_records);
    print "new WP".$wp."<br>";
    print "new EAC".$new_eac."<br>";
    print "new CAWPID".$cawpid."<br>";
    print "new EAC".$a."<br>";
    print "new EAC".$etc."<br>";
    print "new count".$forcast_records."<br>";
    print "new EAC".$spread_val."<br>";
    $i++;
    print "Next item <br>";
    $record_count =checkIFForecastRecordExists($ship_code, $cawpid);
    if($record_count<1){
        //get the rest of the periods for this WP
        //insert all the records for each period between the next month, and the end of the last month.



    }
    $sql_updateCAWP = "update cawp set eac = $new_eac, EAC_NONLAB = $new_eac where PROGRAM = '$ship_code' and wp = '$wp'";
    //check for Forecast record exists
    $sql_updateTPHASE= "update tphase set DIRECT = $spread_val where program = '$ship_code' and CAWPID in ($cawpid) and CLASS = 'Forecast'";

    runSQLCommandUtil($ship_code,$sql_updateCAWP, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
    runSQLCommandUtil($ship_code,$sql_updateTPHASE, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);

}

//$sql = "update tphase set DIRECT = 11872.6 where program = '0481' and CAWPID in (-115375244) and CLASS = 'Forecast'";
//runSQLCommandUtil($ship_code,$sql, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);

//$sql = "insert into TPHASE (PROGRAM, CAWPID, CECODE, CLASS, DF_DATE, DIRECT) values ('0481', -115375244, 'MATL', 'Forecast', '2017-07-29 00:00:00.000',9574)";
//runSQLCommandUtil($ship_code,$sql, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);

die("made it");
