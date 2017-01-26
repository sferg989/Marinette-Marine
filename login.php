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
$z.= "
    <script
            src=\"https://code.jquery.com/jquery-2.2.4.min.js\"
            integrity=\"sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=\"
            crossorigin=\"anonymous\"></script>    
    <script src=\"https://npmcdn.com/tether@1.2.4/dist/js/tether.min.js\"></script>
    <script src=\"https://npmcdn.com/bootstrap@4.0.0-alpha.2/dist/js/bootstrap.js\"></script>
    <script src=\"inc/lib/css/bootbox.min.js\"></script>
    <link rel=\"stylesheet\" href=\"inc/lib/css/bootstrap/css/bootstrap.css\">
";
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
        $z.="bootbox.confirm({
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
        console.log('This was logged in the callback: ' + result);
        if (result == false) {
            bootbox.alert(\"Please try again\", 
            function(){ navigate('login.html');});
        }
        if (result == 'yes') {
            console.log('yesss');
            bootbox.form({
    title: 'User details',
    fields: {
        name: {
            label: 'Name',
            value: 'John Connor',
            type:  'text'
        },
        email: {
            label: 'E-mail',
            type:  'email',
            value: 'johnconnor@skynet.com'
        },
        type: {
            label: 'Type',
            type:  'select',
            options: [
                {value: 1, text: 'Human'},
                {value: 2, text: 'Robot'}
            ]
        },
        alive: {
            label: 'Is alive',
            type: 'checkbox',
            value: true
        },
        loves: {
            label: 'Loves',
            type: 'checkbox',
            value: ['bike','mom','vg'],
            options: [
                {value: 'bike', text: 'Motorbike'},
                {value: 'mom', text: 'His mom'},
                {value: 'vg', text: 'Video games'},
                {value: 'kill', text: 'Killing people'}
            ]
        },
        passwd: {
            label: 'Password',
            type: 'password'
        },
        desc: {
            label: 'Description',
            type: 'textarea'
        }
    },
    callback: function (values) {
        console.log(values)
    }
})
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