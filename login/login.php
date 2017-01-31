<?php
include("../inc/inc.php");
/**
 * Created by PhpStorm.
 * User: fs11239
 * Date: 1/26/2017
 * Time: 1:16 PM
 */

function insertNewUser($username,$password, $email,$role,$citizen){
    $pw = base64_encode($password);
    if($citizen =="USA Citizen"){
        $citizen = "yes";
    }
else{
        $citizen = "no";
    }
    $sql = "INSERT  INTO fmm_evms.user (user_name,pw, email, role, us_citizen) 
              VALUES  ('$username', '$pw','$email', '$role', '$citizen')";
    //print $sql;
    $junk = dbcall($sql,"fmm_evms");
    return false;
}
function checkifUserExists($username){
    $sql    = "select user_name from fmm_evms.user where user_name = '$username'";
    $rs     = dbCall($sql, "fmm_evms");
    $count = $rs->RecordCount();
    if($count=="0"){
        $msg = "user_name_does_not_exist";
        return $msg;
    }
    else{
        $msg = "user_name_exists";
        return $msg;
    }

}
function checkUser($username,$password){
    $sql    = "select user_name, role, pw, us_citizen from fmm_evms.user where user_name = '$username'";

    $rs     = dbCall($sql, "fmm_evms");
    $msg = "";
    $user_name  = $rs->fields["user_name"];
    $role       = $rs->fields["role"];
    $pw         = $rs->fields["pw"];
    $us_citizen = $rs->fields["us_citizen"];

    if($user_name ==""){
        $msg = "fail";
        return $msg;
    }
    $decode = base64_decode($pw);
    if($decode!=$password){
        $msg = "pw_incorrect";
        return $msg;
    }
    else
    {
        session_start();
        $_SESSION["logged_in"] = "true";
        $_SESSION["user_name"] = $username;
        $_SESSION["us_citizen"] = $us_citizen;
        $_SESSION["role"] = $role;
        $_SESSION["hulls"] = "17<>19<>21";
        $msg = "login";
        return $msg;
    }
}
if($control =="login"){
    $msg = checkUser($user_name,$password);
    //var_dump($_SESSION);
    die($msg);
}
if($control=="register"){
    $msg = "";
    $msg = checkifUserExists($user_name);
    if($msg == "user_name_exists"){
        die("user_name_exists");
    }
    if($msg == "user_name_does_not_exist")
    {
        insertNewUser($user_name,$password,$email,$role, $citizen);
        $msg = checkUser($user_name,$password);
        die($msg);
    }

}