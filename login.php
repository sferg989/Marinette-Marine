<?php
include('inc/inc.php');
session_start();
function insertNewUser($username,$password){
    $pw = base64_encode($password);
    $sql = "INSERT  INTO fmm_evms.user (user_name,pw) VALUES  ($username, $pw)";
    $junk = dbcall($sql,"fmm_evms");
    return false;
}
function checkUser($username,$password){
    $sql    = "select role, pw from fmm.evms where username = '$username'";
    $rs     = dbCall($sql, "fmm_evms");
    $msg = "";
    if($rs!="")
    {
        $role   = $rs->fields["role"];
        $pw     = $rs->fields["pw"];

        $decode = base64_decode($pw);
        if($decode!=$password){
            $msg = "pw_incorrect";
        }
        else{
            $msg = "login";
        }
    }
    else{
        $msg = "first_time";

    }
    return $msg;
}
print $username;
print "<br>";
print $password;
print "this worked";
$result = checkUser($username,$password);
print "
    <script>
    function navigate(url){
        window.location.href = url;
    }
    
    
    ";
switch ($result) {
    case "pw_incorrect":

        print "navigate('login.html')</script>";
        break;
    case "login":
        $_SESSION["user_name"] = $username;
        print "navigate('login.html')</script>";
        break;
    case "first_time":
        insertNewUser($username,$password);
        print "
            function myFunction() {
                var person = prompt(\"Please enter your name\", \"Harry Potter\");
                
                if (person != null) {
                    document.getElementById(\"demo\").innerHTML =
                    \"Hello \" + person + \"! How are you today?\";
                }
            }</script>
            ";
        break;
}

