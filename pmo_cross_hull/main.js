
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
        var cols1 = gridColumns.col1.toString();

        shipGridObj.dataView.beginUpdate();
        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }

    function excelExportCallBack(d){
        window.open(d);
    }
    var height = $(window).height();
    $("#rptGrid").height(height*.9);
    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });

    var shipCols            = gridColumns.cols;
    var grid1_options       = gridOptions.gridOptions;

    var shipGridObj         = grid.createGrid("shipGrid", shipCols, grid1_options);
    var headerButtonsPlugin = new Slick.Plugins.HeaderButtons();

    shipGridObj.grid.registerPlugin(headerButtonsPlugin);
    var url             = "lib/php/grid.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "project_grid";
    dataService.getData(url, ajaxDataObj, gridDataViewCallBack);
    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
