
require([
    "./lib/components/grid",
    "./lib/components/get_url",
    "./lib/components/title_update",
    "bootbox"], function(grid,getUrl, titleUpdate,bootbox) {
    //select2.createFilter("ca");
    $( document ).ready(function() {
        function goBack() {
            window.history.back();
        }
        $("#back_btn").click(function(){
            goBack();
        });

        var rpt_period = getUrl.getUrlParam("rpt_period");
        var code       = getUrl.getUrlParam("code");
        titleUpdate.updateTitle(code, rpt_period);



    });


    //bootbox.alert("this is really cool");
});/**
 * Created by fs11239 on 4/3/2017.
 */
