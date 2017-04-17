
require([
    "lib/components/createSlickGrid",
    "lib/components/grid_options",
    "lib/components/get_url",
    "lib/components/grid_columns",
    "lib/components/title_update",
    "bootbox"], function(grid,gridOptions,getUrl, gridColumns,titleUpdate,bootbox) {
    $( document ).ready(function() {
        var height = $(window).height();

        $("#log_analysis_grid").height(height*.9);
        function goBack() {
            window.history.back();
        }
        $("#back_btn").click(function(){
            goBack();
        });
        $("#load_cobra_data").click(function(){

            var step        = {};
            step.code       = code;
            step.action     = "load_cobra_data";
            step.name       = "Load Cobra Data";
            step.rpt_period = rpt_period;
            var worker;
            if($("#img_"+step.action.length))
            {
                $("#img_"+step.action).empty();
            }
            $("#status_cobra").append("<div id = \"img_"+step.action+"\"><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");
            workers     = new Worker("lib/workers/load_cobra_data.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);
            function workerDone(e) {
                console.log(e.data.id+" has completed");
                $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
            }

        });
        var log_analysis_grid_columns = gridColumns.cols;
        var grid1_options             = gridOptions.gridOptions;

        var rpt_period                = getUrl.getUrlParam("rpt_period");
        var code                      = getUrl.getUrlParam("ship_code");
        titleUpdate.updateTitle(code, rpt_period);

        var ajax_data_options        = {};
        ajax_data_options.control    = "bcm";
        ajax_data_options.rpt_period = rpt_period;
        ajax_data_options.ship_code  = code;
        var url                      = "lib/php/bcm.php";
        grid.createGrid("bcm",url, ajax_data_options ,log_analysis_grid_columns, grid1_options);

    });


    //bootbox.alert("this is really cool");
});/**
 * Created by fs11239 on 4/3/2017.
 */
