
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "lib/components/title_update",
    "slickCheckColumn"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, titleUpdate) {
$( document ).ready(function() {
    function gridDataViewCallBack(data){
        shipGridObj.dataView.beginUpdate();

        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }
    function rptGridDataViewCallBack(data){
        rptGridObj.dataView.beginUpdate();
        rptGridObj.dataView.setItems(data);
        rptGridObj.dataView.endUpdate();
        rptGridObj.dataView.refresh();
        rptGridObj.grid.render();
        rptGridObj.grid.updateRowCount();
    }
    var rpt_period                = getUrl.getUrlParam("rpt_period");
    var code                      = getUrl.getUrlParam("ship_code");
    var height = $(window).height();
    titleUpdate.updateTitle(code, rpt_period);
    $("#rptGrid").height(height*.9);
    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });
    $("#load_cobra_data").click(function(){
        var step = {};
        var selectedIndexes = shipGridObj.grid.getSelectedRows(),count = selectedIndexes.length;
        var n, worker;
        $.each(selectedIndexes, function(index, value ) {
            step.action = "load_cobra_data";
            step.code = shipGridObj.grid.getDataItem(value).ship_code;
            step.name   = shipGridObj.grid.getDataItem(value).name;
            //console.log(step.action,step.name);
            var selectedRPTS = rptGridObj.grid.getSelectedRows(),count = selectedRPTS.length;
            $.each(selectedRPTS,function(i, val){
                step.rpt_period = rptGridObj.grid.getDataItem(val).rpt_period;
                console.log(step);
                workers     = new Worker("lib/workers/load_cobra_data.js");
                workers.onmessage = workerDone;
                workers.postMessage(step);
            });
            $("#status").append("<br><div class=\"row\"><div class=\"col-md-1\" id = \"img_"+step.code+"\"><img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/></div><div class=\"col-md-2\" id = \""+step.action+"\">"+step.name+"</div></div><br>");

            function workerDone(e) {
                if(e.data.id == undefined){
                    return false;
                }
                else{
                    console.log(e.data.id+" has completed");
                    $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
                }
            }
        });
    });
    $("#load_cobra_data_tp").click(function(){
        var step = {};
        var selectedIndexes = shipGridObj.grid.getSelectedRows(),count = selectedIndexes.length;
        var n, worker;
        $.each(selectedIndexes, function(index, value ) {
            step.action = "load_cur_period_tp";
            step.code   = shipGridObj.grid.getDataItem(value).ship_code;
            step.name   = shipGridObj.grid.getDataItem(value).name;
            //console.log(step.action,step.name);
            workers     = new Worker("lib/workers/load_cobra_data_tp.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);

            $("#status").append("<br><div class=\"row\"><div class=\"col-md-1\" id = \"img_"+step.code+"\"><img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/></div><div class=\"col-md-2\" id = \""+step.action+"\">"+step.name+"</div></div><br>");

            function workerDone(e) {
                if(e.data.id == undefined){
                    return false;
                }
                else{
                    console.log(e.data.id+" has completed");
                    $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
                }
            }
        });
    });
    var shipCols      = gridColumns.cols;
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "slick-cell-checkboxsel"
    });
    shipCols.unshift(checkboxSelector.getColumnDefinition());

    var rptCols       = gridColumns.rptCols;
    var checkboxSelectorRPT = new Slick.CheckboxSelectColumn({
        cssClass: "slick-cell-checkboxsel"
    });
    rptCols.unshift(checkboxSelectorRPT.getColumnDefinition());

    var projectGridOptions = gridOptions.projectGridOptions;
    var rptPeriodOptions = gridOptions.RPTPeriodOptions;
    var shipGridObj   = grid.createGrid("shipGrid", shipCols, projectGridOptions);
    var rptGridObj    = grid.createGrid("rptGrid", rptCols, rptPeriodOptions);
    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
    rptGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));

    shipGridObj.grid.registerPlugin(checkboxSelector);
    rptGridObj.grid.registerPlugin(checkboxSelectorRPT);
    new Slick.Controls.ColumnPicker(shipCols, shipGridObj.grid,projectGridOptions);
    new Slick.Controls.ColumnPicker(rptCols, rptGridObj.grid,rptPeriodOptions);


    var url = "../cost_loader/lib/php/cost_loader.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "project_grid";
    var rptAjaxObj      = {};
    rptAjaxObj.control  = "rpt_periods";
    dataService.getData(url,ajaxDataObj,gridDataViewCallBack);
    dataService.getData(url,rptAjaxObj,rptGridDataViewCallBack);



});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
