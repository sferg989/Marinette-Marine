
require([
    "lib/components/createSlickGrid",
    "lib/components/grid_options",
    "lib/components/get_url",
    "lib/components/grid_columns",
    "lib/components/title_update",
    "bootbox"], function(grid,gridOptions,getUrl, gridColumns,titleUpdate,bootbox) {
    $( document ).ready(function() {
        var height = $(window).height();

        $("#prepare_actuals").height(height*.9);
        function goBack() {
            window.history.back();
        }
        $("#back_btn").click(function(){
            goBack();
        });

        var log_analysis_grid_columns = gridColumns.cols;
        var grid1_options             = gridOptions.gridOptions;

        var rpt_period                = getUrl.getUrlParam("rpt_period");
        var code                      = getUrl.getUrlParam("ship_code");
        titleUpdate.updateTitle(code, rpt_period);

        var ajax_data_options        = {};
        ajax_data_options.control    = "prepare_actuals";
        ajax_data_options.rpt_period = rpt_period;
        ajax_data_options.ship_code  = code;
        var url                      = "lib/php/bcm.php";
        //grid.createGrid("bcm",url, ajax_data_options ,log_analysis_grid_columns, grid1_options);

    });

    //bootbox.alert("this is really cool");
});/**
 * Created by fs11239 on 4/3/2017.
 */
