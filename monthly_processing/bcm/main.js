
require([
    "lib/components/createSlickGrid",
    "lib/components/grid_options",
    "lib/components/get_url",
    "lib/components/grid_columns",
    "lib/components/title_update",
    "bootbox",
    "bootstrap33",
    "slickCheckColumn"], function(grid,gridOptions,getUrl, gridColumns,titleUpdate,bootbox) {
    $( document ).ready(function() {
        var height = $(window).height();

        $("#log_analysis_grid").height(height*.9);
        function goBack() {
            window.history.back();
        }
        $("#back_btn").click(function(){
            goBack();
        });

        var bcm_cols      = gridColumns.cols;
        var bcr_cols      = gridColumns.bcr_cols;
        var logExcelCols  = gridColumns.logExcelCols;
        var grid1_options = gridOptions.gridOptions;
        var rpt_period    = getUrl.getUrlParam("rpt_period");
        var code          = getUrl.getUrlParam("ship_code");
        titleUpdate.updateTitle(code, rpt_period);

        var ajax_data_options        = {};
        ajax_data_options.control    = "bcm";
        ajax_data_options.rpt_period = rpt_period;
        ajax_data_options.code  = code;

        var bcrAjaxData              = {};
        bcrAjaxData.control          = "bcr";
        bcrAjaxData.rpt_period       = rpt_period;
        bcrAjaxData.code        = code;

        var logExcelAjaxData              = {};
        logExcelAjaxData.control          = "log_analysis";
        logExcelAjaxData.rpt_period       = rpt_period;
        logExcelAjaxData.code        = code;

        var excelGrid = grid.createLogExcelGrid("log_excel_grid",logExcelAjaxData);
        $("#mr_log_trans").click(function(){

            var selectedIndexes = excelGrid.getSelectedRows();
            var i= 0;
            //var jObj = {"color":"red","shape":"square"}
/*            var urlParam = []

            for (var i in jObj){
                urlParam.push(encodeURI(i) + "=" + encodeURI(jObj[i]));
            }*/
            var metaData = [];

            $.each(selectedIndexes, function(index, value ) {
                //dataItemArray.push(excelGrid.getDataItem(value).mr);
                metaData[i] = excelGrid.getDataItem(value).bcr;
                i++
            });
            console.log(metaData);
            var bcr_list    = metaData.join();
            var step        = {};
            step.code       = code;
            step.action     = "log_trans";
            step.name       = "Perform Log Transactions";
            step.rpt_period = rpt_period;
            step.logTrans   = bcr_list;
            var worker;
            if($("#img_"+step.action.length))
            {
                $("#img_"+step.action).empty();
            }
            $("#status_log_trans").append("<div id = \"img_"+step.action+"\"><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");
            workers     = new Worker("lib/workers/log_trans.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);
            function workerDone(e) {
                console.log(e.data.id+" has completed");
                $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
            }
        });
        $("a[href='#a']").on('show.bs.tab', function(e) {
            console.log('show - before the new tab has been shown');
            $("#bcr_bcm").empty();
            grid.createBCRGrid("bcr_bcm",bcrAjaxData ,bcr_cols, grid1_options);

        });
        $("a[href='#b']").on('show.bs.tab', function(e) {
            console.log('EXCEL');
            //$("#bcm").empty();
            grid.createGrid("bcm",ajax_data_options ,bcm_cols, grid1_options);

        });
        //$("#my_grid_div").hide();
    });


    //bootbox.alert("this is really cool");
});/**
 * Created by fs11239 on 4/3/2017.
 */
