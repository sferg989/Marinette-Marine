/**
 * Created by fs11239 on 11/30/2016.
 */
$(document).ready(function() {
    function navigate(url){
        window.location.href = url;
    }

    function buildMenu(){
        var worker,action;

        action            = "build_menu";
        workers           = new Worker("workers/menu.js");
        workers.onmessage = workerDone;
        workers.postMessage(action);
        function workerDone(e) {
            if(e.data){
                var menu;
                menu = e.data.menu;
                var url             = "menu.php";
                var ajaxDataObj     = {};
                ajaxDataObj.control = "get_home_page";
                $.ajax({
                    url     : url,
                    data    : ajaxDataObj
                }).done(function (data){
                    console.log("HTML");
                    $("#menu_target").html(menu).promise().done(function(){;
                    console.log("this is the IFRAME");
                    $("#iframe1").attr("src", data);
                    //buildMenu();
                    });
                });
            }
        }
    }


    //checkIfLoggedInandGetUserData();
    buildMenu();
    //getHomePage();
});

