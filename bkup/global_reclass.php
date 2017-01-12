<?php
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 1/6/2017
 * Time: 2:52 PM
 */
if($control =="reclass_PF2_ff")
{
    $ship_code           = "04731116-test";

    $source_class        = "PF";
    $target_class        = "FF";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Target";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Change PF class type From FF to Manual (retain ETC)-Complete");
}
if($control =="reclass_ff2PF")
{
    $ship_code           = "04731116-test";

    $source_class        = "Forecast";
    $target_class        = "PF";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Target";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Global Reclass - Copy Forecast to PF class-Complete");
}
if($control =="reclass_a2_pa")
{
    $ship_code           = "04731116-test";

    $source_class        = "Actual";
    $target_class        = "PA";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Actuals to PA-Complete");
}
if($control =="reclass_b2_pb")
{
    $ship_code           = "04731116-test";

    $source_class        = "Budget";
    $target_class        = "PB";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("Current Budget to PB-Complete");
}
if($control =="reclass_ra2_pb")
{
    $ship_code           = "04731116-test";

    $source_class        = "RA";
    $target_class        = "PB";
    $target_action       = "Add";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("RA Class to the PB Class-Complete");
}
if($control =="reclass_ea2_pe")
{
    $ship_code           = "04731116-test";

    $source_class        = "EA";
    $target_class        = "PE";
    $target_action       = "Replace";
    $source_action       = "Copy";
    $rsrc_code_from      = "Source";
    $allow_complete      = "1";
    $include_in_forecast = "0";

    reClassCobraProject($ship_code,$g_path2CobraAPI,$g_path2ReClassProjectCMD,$g_path2ReClassProjectBAT,$source_class,$target_class,$target_action,$source_action,$rsrc_code_from,$allow_complete,$include_in_forecast,$debug);
    die("EA Class to the PE Class-Complete");
}