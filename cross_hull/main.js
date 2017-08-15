
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "slickHeaderBtn"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService) {
$( document ).ready(function() {
    function gridDataViewCallBack(data){
        shipGridObj.dataView.beginUpdate();

        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }
    function gridDataViewCallBa2ck(data){
        shipGridObj.grid.invalidateAllRows();
        shipGridObj.dataView.setItems(data);
        shipGridObj.grid.render();

    }

    var height = $(window).height();
    function excelExportCallBack(d){
        window.open(d);
    }
    $("#rptGrid").height(height*.9);
    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });

    var shipCols      = gridColumns.cols;
    var groupCols = {
        id      : "group",
        minWidth : 190,
        name    : "group",
        header : {
            buttons: [
                {
                    image: "../inc/images/excel-icon.png",
                    showOnHover: true,
                    tooltip: "This button only appears on hover.",
                    handler: function (e) {
                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "excel_export";
                        dataService.excelExport(url,ajaxDataObj,excelExportCallBack);
                    }
                },{
                    image: "../inc/images/wbsh16.png",
                    tooltip: "WBS Hours",
                    handler: function (e) {
                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "project_grid";
                        ajaxDataObj.stucture = "wbs_h";
                        dataService.getData(url,ajaxDataObj, gridDataViewCallBa2ck);
                    }
                },{
                    image: "../inc/images/wbsd16.png",
                    tooltip: "WBS Dollars",
                    handler: function (e) {
                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "project_grid";
                        ajaxDataObj.stucture = "wbs_d";
                        dataService.getData(url,ajaxDataObj, gridDataViewCallBa2ck);
                    }
                },{
                    image: "../inc/images/obsh.png",
                    tooltip: "OBS Hours",
                    handler: function (e) {
                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "project_grid";
                        ajaxDataObj.stucture = "obs_h";
                        dataService.getData(url,ajaxDataObj, gridDataViewCallBa2ck);
                    }
                },{
                    image: "../inc/images/obsdgreen.png",
                    tooltip: "OBS Dollars",
                    handler: function (e) {
                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "project_grid";
                        ajaxDataObj.stucture = "obs_d";
                        dataService.getData(url,ajaxDataObj, gridDataViewCallBa2ck);
                    }
                }
            ]
        },
        field   : "group"
    };
    shipCols.unshift(groupCols);

    var grid1_options       = gridOptions.gridOptions;

    var shipGridObj         = grid.createGrid("shipGrid", shipCols, grid1_options);
    var headerButtonsPlugin = new Slick.Plugins.HeaderButtons();
    shipGridObj.grid.registerPlugin(headerButtonsPlugin);
    var url             = "lib/php/grid.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "project_grid";
    ajaxDataObj.stucture= "wbs_d";
    dataService.getData(url, ajaxDataObj, gridDataViewCallBack);
    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));


});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
