<?php
include("../inc/inc.php");
extract($_SESSION);

/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 1/30/2017
 * Time: 3:20 PM
 */
if($control =="login"){
    $login = $_SESSION["logged_in"];
    $user  = $_SESSION["user_name"];
    $hulls = $_SESSION["hulls"];
    $role = $_SESSION["role"];
    $data = "$login, $role, $user, $hulls";
    die($data);
}


