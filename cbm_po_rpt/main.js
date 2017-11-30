
require([
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "../inc/custom_components/jsonexcelexport",
    "slickgrid",
    "slickdataview",
    "slickAutoToolTips",
    "slickHeaderBtn",
    "slickRowSelection",
    "dragevent",
    "slickColumnPicker",
    "slickPager"
    ], function(gridOptions,getUrl, gridColumns,dataService, bootbox,jsonExporter) {
$( document ).ready(function() {

    var loadingIndicator = null;
    function gridDataViewCallBack(data){
        //console.log(data);
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
        loadingIndicator.fadeOut();
        //console.log("this is the add"+data);
        window.open(data);
    }

    var shipCols      = gridColumns.cols;
    var groupCols = {
        id      : "ship_code",
        name    : "Hull",
        width   : 65,
        field   : "ship_code",
        header : {
            buttons: [
                {
                    image: "../inc/images/excel-icon.png",
                    showOnHover: true,
                    tooltip: "This button only appears on hover.",
                    handler: function (e) {
                        //console.log("this worked");
                        if (!loadingIndicator) {
                            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
                            var $g = $("#shipGrid");
                            loadingIndicator
                                .css("position", "absolute")
                                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
                        }
                        loadingIndicator.show();
                        dataService.excelExport(url,ajaxDataObj,excelExportCallBack);
                    }
                }
            ]
        }
    };
    shipCols.unshift(groupCols);
    var grid1_options    = gridOptions.gridOptions;
    var shipGridObj      = {};
    shipGridObj.dataView = {};
    shipGridObj.grid     = {};
    shipGridObj.dataView = new Slick.Data.DataView();
    shipGridObj.grid     = new Slick.Grid('#shipGrid', shipGridObj.dataView, shipCols, grid1_options);
    var pager            = new Slick.Controls.Pager(shipGridObj.dataView, shipGridObj.grid, $("#my_pager"));

    shipGridObj.dataView.setPagingOptions({
        inlineFilters: true,
        pageSize     : 18
    });
    shipGridObj.dataView.onRowCountChanged.subscribe(function (e, args) {
        shipGridObj.grid.updateRowCount();
        shipGridObj.grid.render();
    });

    shipGridObj.dataView.onRowsChanged.subscribe(function (e, args) {
        shipGridObj.grid.invalidateRows(args.rows);
        shipGridObj.grid.render();
    });

    shipGridObj.dataView.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
        var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
        var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
        var options = shipGridObj.grid.getOptions();
        if (options.enableAddRow != enableAddRow) {
            shipGridObj.grid.setOptions({enableAddRow: enableAddRow});
        }
    });

    shipGridObj.grid.registerPlugin(new Slick.AutoTooltips({
        enableForCells      : true,
        enableForHeaderCells: false,
        maxToolTipLength    : null
    }));
    var headerButtonsPlugin = new Slick.Plugins.HeaderButtons();

    shipGridObj.grid.registerPlugin(headerButtonsPlugin);

    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel());
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
    dataService.getData(url, ajaxDataObj, gridDataViewCallBack);
});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
