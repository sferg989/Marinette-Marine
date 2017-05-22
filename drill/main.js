
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
        console.log(data);
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

    var shipCols      = gridColumns.cols;
    console.log(shipCols);
    var metaCols = gridColumns.getMetaCols(0)
    metaCols.push(shipCols);
    var grid1_options       = gridOptions.gridOptions;

    var shipGridObj         = grid.createGrid("shipGrid", metaCols, grid1_options);
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
    shipGridObj.grid.onDblClick.subscribe(function (e, args){

        var cell                = shipGridObj.grid.getCellFromEvent(e)
        var row                 = cell.row;
        var cols                = shipGridObj.grid.getColumns();
        //console.log("name of the Col" + cols[cell.cell].name);
        var level               = cols[cell.cell].name;
        var cur_level           = gridCols.findCurLevel(level);
        var drill_level         = gridCols.findDrillLevel(level);
        var metaColsBeforeDrill = gridCols.getMetaCols(cur_level);

        var dataItem = dataView.getItem(args.row);
        //console.log("this is the Data ITEm ", dataItem);

        if(drill_level=="undefined"){
            return false;
        }

        var metaColsAfterDrill      = gridCols.getMetaCols(drill_level);
        //console.log(dataItem);


        var dataCols      = gridCols.dataCols;
        for (i = 0; i < dataCols.length; i++) {
            metaColsAfterDrill.push(dataCols[i]);
        }

        //console.log(ajax_data_object);

        shipGridObj.grid.setColumns(metaColsAfterDrill);
    });

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
