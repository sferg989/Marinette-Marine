
require([
    "lib/components/createSlickGrid",
    "lib/components/grid_options",
    "lib/components/get_url",
    "lib/components/grid_columns",
    "lib/components/title_update",
    "lib/components/btn_group",
    "lib/components/line_chart",
    "bootbox"
    ], function(grid,gridOptions,getUrl, gridColumns, titleUpdate,btnGroup,lineChart,bootbox) {
    //select2.createFilter("ca");
    $( document ).ready(function() {

        function goBack() {
            window.history.back();
        }
        $("#back_btn").click(function(){
            goBack();
        });
        var lcs_grid_cols = gridColumns.dataCols;
        var woCols        = gridColumns.wo_cols;

        var lcs_grid_options = gridOptions.gridOptions;
        var woGridOptions    = gridOptions.woGridOptions;

        var rpt_period = getUrl.getUrlParam("rpt_period");
        var code       = getUrl.getUrlParam("code");
        titleUpdate.updateTitle(code, rpt_period);

        var ajax_data_options        = {};
        ajax_data_options.control    = "lcs_grid";
        ajax_data_options.rpt_period = rpt_period;

        var ajax_WOData_options        = {};
        ajax_WOData_options.control    = "wo_grid";

        grid.createGrid("lcs_grid",ajax_data_options ,lcs_grid_cols, lcs_grid_options);

    });


    //bootbox.alert("this is really cool");
});/**
 * Created by fs11239 on 4/3/2017.
 */
