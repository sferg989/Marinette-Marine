/**
 * Created by fs11239 on 11/30/2016.
 */
$(document).ready(function() {
    function navigate(url){
        window.location.href = url;
    }
    function checkIfLoggedInandGetUserData (){
        var worker,action;
        action = "login";
        workers     = new Worker("../global_worker/worker_check_login.js");
        workers.onmessage = workerDone;
        workers.postMessage(action);
        function workerDone(e) {

            var user_name,role, hulls, login;
            user_name = e.data.user;
            role      = e.data.role;
            hulls     = e.data.hulls;
            login     = e.data.login;
            //console.log("this is status "+login);
            //console.log("thses are the hulls" + hulls);
            if(login ==false){
                bootbox.alert("please Login into FMM-EV", function (){
                    navigate("../login/login.html");
                });
            }
        }
    }
    checkIfLoggedInandGetUserData();
});

