
require([
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "../inc/custom_components/jsonexcelexport",
    "slickGridFrozen",
    "slickdataview",
    "slickAutoToolTipsFrozen",
    "slickHeaderBtn",
    "slickRowSelectionFrozen",
    "dragevent",
    "slickColumnPicker",
    "slickPagerFrozen",
    "jQueryMouseWheel",
    ], function(gridOptions,getUrl, gridColumns,dataService, bootbox,jsonExporter) {
$( document ).ready(function() {

    var loadingIndicator = null;
    function gridDataViewCallBack(data){
        loadingIndicator.fadeOut();
        shipGridObj.dataView.beginUpdate();
        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
        //jsonExporter.jsonExporter(data,"PO APPROVAL", "YES");
    }
    function clearAllRows(){
        shipGridObj.grid.invalidateAllRows();
    }

    var height = $(window).height();
    function excelExportCallBack(data){
        console.log(data);
        window.open(data);
    }

    $("#approve_PO").click(function(){
        var total_rows = shipGridObj.grid.getData().getPagingInfo().totalRows;
        //console.log("thiw worked", total_rows );
        if(total_rows<1){
            bootbox.alert("please Search for a PO!")
            return false;
        }
        var url             = "lib/php/grid.php";
        var ajaxDataObj     = {};
        ajaxDataObj.control = "approve_po";
        ajaxDataObj.rows = {};
        /*var i = 0;
        for (i = 0; i < total_rows; i++) {
            ajaxDataObj.rows[i]= shipGridObj.grid.getDataItem(i);
        }*/
        i = 0;

        var divide = Math.ceil(total_rows/10);

        for (i = 0; i <divide; i++) {
            ajaxDataObj.rows = {};
            var start_counter = i*10;
            var data_item_index  = start_counter;
            for (data_item_index = start_counter; data_item_index < start_counter+10; data_item_index++) {
                ajaxDataObj.rows[data_item_index]= shipGridObj.grid.getData().getItemById(data_item_index);
            }
            dataService.approvePO(url, ajaxDataObj);
        }
        bootbox.alert(" THE LOG has been Updated");
    });
    $("#submit").click(function(){
        var po_num         = $("#po_num").val();
        if(po_num=="" || po_num==undefined){
            bootbox.alert("Please input a PO!");
                return false;
        }
        else{
            if (!loadingIndicator) {
                loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
                var $g = $("#shipGrid");
                loadingIndicator
                    .css("position", "absolute")
                    .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                    .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
            }
            loadingIndicator.show();
            var url             = "lib/php/grid.php";
            var ajaxDataObj     = {};
            ajaxDataObj.control = "project_grid";
            ajaxDataObj.po      = po_num;
            clearAllRows();
            dataService.getData(url, ajaxDataObj, gridDataViewCallBack);
        }
    });

    var shipCols      = gridColumns.cols;

    shipCols.unshift(groupCols);
    var grid1_options = gridOptions.gridOptions;
    var shipGridObj= {};
    shipGridObj.dataView = {};
    shipGridObj.grid = {};
    shipGridObj.dataView = new Slick.Data.DataView();
    shipGridObj.grid     = new Slick.Grid('#shipGrid', shipGridObj.dataView, shipCols, grid1_options);


    shipGridObj.grid.registerPlugin(new Slick.AutoTooltips({
        enableForCells      : true,
        enableForHeaderCells: false,
        maxToolTipLength    : null
    }));


    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel());

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
