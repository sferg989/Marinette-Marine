$(function() {
    function navigate(url){
        window.location.href = url;
    }
    var url = "login.php";
    $('#login-form-link').click(function(e) {
        $("#login-form").delay(100).fadeIn(100);
        $("#register-form").fadeOut(100);
        $('#register-form-link').removeClass('active');
        $(this).addClass('active');
        e.preventDefault();
    });
    $('#register-form-link').click(function(e) {
        $("#register-form").delay(100).fadeIn(100);
        $("#login-form").fadeOut(100);
        $('#login-form-link').removeClass('active');
        $(this).addClass('active');
        e.preventDefault();
    });
    $("#submit").click(function(){
        var user_name, password
        user_name = $("#username").val();
        password = $("#password").val();
        $.ajax({
            type : "POST",
            url  : url,
            data: {
                control  : "login",
                user_name: user_name,
                password : password
            },
            success: function (response) {
                if(response=="pw_incorrect"){
                    bootbox.alert("Password was incorrect.");
                }
                if(response=="fail"){
                    bootbox.alert("Username was incorrect.");
                }
                if(response=="login"){
                    navigate("../header/header.html");
                }
            }
        });
    });
    $("#register").click(function(){
        var user_name, password, con_password, email, role;
        user_name    = $("#r_username").val();
        password     = $("#r_password").val();
        con_password = $("#password").val();
        email        = $("#r_email").val();
        role         = $("#role").val();
        if(password!=con_password)
        {
            bootbox.alert("The Passwords do not match!");
            return false;
        }
        if(email==undefined || email == "")
        {
            bootbox.alert("Please provide an Email!");
            return false;
        }
        if(role==undefined || role == "")
        {
            bootbox.alert("Please provide a Role!");
            return false;
        }
        $.ajax({
            type : "POST",
            url  : url,
            data: {
                control  : "register",
                user_name: user_name,
                password : password,
                email    : email,
                role     : role
            },
            success: function (response) {
                if(response=="user_name_exists"){
                    bootbox.alert("That Username already exists");
                }
                if(response=="login"){
                    navigate("header/header.html");
                }
            }
        });
    });

});
/**
 * Created by fs11239 on 1/26/2017.
 */
