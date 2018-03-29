<?php
include("inc/inc.php");
include("inc/inc.PHPExcel.php");
include("inc/inc.cobra.php");


$sql = "select ship_code, cur_ca, wp, new_ca, new_obs from project_restructure";
$rs = dbCall($sql,"fmm_evms");
while (!$rs->EOF)
{
    $ship_code = $rs->fields["ship_code"];
    $new_ca    = $rs->fields["new_ca"];
    $cur_ca    = $rs->fields["cur_ca"];
    $new_obs    = $rs->fields["new_obs"];
    $wp        = $rs->fields["wp"];


    $sql_updateCAWP = " Update baselog set ca1 = '$new_ca', ca2 = '$new_obs' where PROGRAM = '0485DRAFTRestructure' and ca1 = '$cur_ca' and wp = '$wp'";
    $sql_array[]= $sql_updateCAWP;

    $sql_updateTPHASE= "Update  link set  ca1 = '$new_ca',  ca2 = '$new_obs'  where PROGRAM = '0485DRAFTRestructure' and ca1 = '$cur_ca'  and wp = '$wp'";
    $sql_array[] = $sql_updateTPHASE;

    $rs->MoveNext();
}

$split_array = array_chunk($sql_array, 200);
foreach ($split_array as $sql_chunks){
    $sql_implode = implode(";", $sql_chunks);

    array_debug($sql_chunks);
    //print $sql_implode;
    runSQLCommandUtil($ship_code,$sql_implode, $g_path2CobraAPI,$g_path2CMDSQLUtil,$g_path2BATSQLUtil);
}
die("made it");
