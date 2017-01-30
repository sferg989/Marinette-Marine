<?php
include('inc/inc.php');
if($action =="submit"){

    die("made it");
}
session_start();
function insertNewUser($username,$password){
    $pw = base64_encode($password);
    $sql = "INSERT  INTO fmm_evms.user (user_name,pw) VALUES  ($username, $pw)";
    $junk = dbcall($sql,"fmm_evms");
    return false;
}
function checkUser($username,$password){
    $sql    = "select user_name, role, pw from fmm_evms.user where user_name = '$username'";

    $rs     = dbCall($sql, "fmm_evms");
    $msg = "";
    $user_name = $rs->fields["user_name"];
    $role = $rs->fields["role"];
    $pw = $rs->fields["pw"];

    if($user_name ==""){
        $msg = "user_did_not_match";
        return $msg;
    }
    $decode = base64_decode($pw);
    if($decode!=$password){
        $msg = "pw_incorrect";
        return $msg;
    }
}
$z = "
<html>
<head>
";
$z.= ";
$z.="</head>";
$z.="<body>";
$result = checkUser($username,$password);

$z.= "
    <script>
    function navigate(url){
        window.location.href = url;
    }
    
    ";
switch ($result) {
    case "pw_incorrect":

        $z.="alert('Password Incorrect'); 
                navigate('login.html');</script>";
        break;
    case "user_did_not_match":
        //$_SESSION["user_name"] = $username;
        $z.="
bootbox.confirm({
    message : 'Is this the first time you are logging into EV?',
    buttons : {
        confirm: {
            label    : 'Yes',
            className: 'btn-success'
        },
        cancel : {
            label    : 'No',
            className: 'btn-danger'
        }
    },
    callback: function (result) {
        if (result == false) {
            bootbox.alert(\"Please try again\",
                function(){ navigate('login.html');});
        }
        if (result == true) {
            var form;
            form = $('<form action=\"login.php?action=submit\">ROLE :<br><select name=\"role\" id=\"role\"><option selected=\"selected\" value=\"PFA\">PFA</option><option value=\"admin\">admin</option><option value=\"cam\">CAM</option></select><br>Email :<br><input type=\"text\" name=\"email\"><br>First Name :<br> <input type=\"text\" name=\"first_name\"><br>Last Name :<br><input type=\"text\" name=\"last_name\"></form>');

        bootbox.confirm(form,function(){
        var e = document.getElementById(\"role\");
        var role = e.options[e.selectedIndex].value;
        var email = form.find('input[name=email]').val();
        var first_name = form.find('input[name=first_name]').val();
        var last_name = form.find('input[name=last_name]').val();
        console.log(email);

        });
        }
    }
});</script>";
        break;
    case "first_time":
        insertNewUser($username,$password);
        $z.="<script></script>
            ";
        break;
}
$z.="</body>
<html>";
print $z;
