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
function getCAWPID($ship_code, $wp){
    $sql = "select CAWPID from cawp where program = '$ship_code' and wp = '$wp'";
    $rs = dbCallCobra($sql);
    $cawpid = $rs->fields["CAWPID"];
    return $cawpid;
}

$path2file =  "C:/evms/matl_updates/0483matlupdates.xlsx";
require('inc/lib/php/spreadsheet-reader-master/spreadsheet-reader-master/SpreadsheetReader.php');

$ship_code       = '0483spfMATL';
$Reader = new SpreadsheetReader($path2file);
$i = 0;
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
    $sql_updateCAWP = "update cawp set eac = $new_eac, EAC_NONLAB = $new_eac where PROGRAM = '$ship_code' and wp = '$wp'";

    $sql_updateTPHASE= "update tphase set DIRECT = $spread_val where program = '$ship_code' and CAWPID in ($cawpid) and CLASS = 'Forecast'";

    runSQLCommandUtil($ship_code,$sql_updateCAWP, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
    runSQLCommandUtil($ship_code,$sql_updateTPHASE, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);

}
die("made it");
