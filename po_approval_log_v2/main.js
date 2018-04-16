
require([
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "slickGridFrozen",
    "slickdataviewFrozen",
    "slickAutoToolTipsFrozen",
    "slickHeaderBtnFrozen",
    "slickRowSelectionFrozen",
    "dragevent",
    "slickColumnPicker",
    "slickPagerFrozen"
    ], function(gridOptions,getUrl, gridColumns,dataService, bootbox) {
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
    function fortisCB(data){
        loadingIndicator.fadeOut();
        if(data=="yes"){
            bootbox.alert("Fortis Reload Sucessfull!");
        }
    }
    var height = $(window).height();
    function excelExportCallBack(data){
        //console.log(data);
        window.open(data);
    }
    function insertApprovedPO(po_num){
        console.log(po_num);

        var url             = "lib/php/grid.php";
        var ajaxDataObj     = {};
        ajaxDataObj.control = "approve_po";
        ajaxDataObj.po      = po_num;
        var total_rows      = shipGridObj.grid.getData().getPagingInfo().totalRows;
        if(total_rows<1){
            bootbox.alert("please Search for a PO!")
            return false;
        }
        console.log("this was a call back", total_rows);
        console.log(ajaxDataObj);
        ajaxDataObj.rows = {};
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
    }
    $("#approve_PO").click(function(){
        var po_num          = $("#po_num").val();
        var url             = "lib/php/grid.php";
        var ajaxDataObj     = {};
        ajaxDataObj.control = "delete_po_before_approve";
        ajaxDataObj.po      = po_num;
        dataService.deletePO(url,ajaxDataObj, insertApprovedPO);
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

            var ajaxDataObj     = {};
            ajaxDataObj.control = "excel_export_po";
            ajaxDataObj.po      = po_num;

            dataService.excelExport(url,ajaxDataObj,excelExportCallBack);
        }
    });

    var shipCols      = gridColumns.cols;
    var groupCols = {
        id      : "ship_code",
        name    : "Hull",
        width   : 85,
        field   : "ship_code",
        sortable: true,
        header : {
            buttons: [
                {
                    image: "../inc/images/excel-icon.png",
                    showOnHover: true,
                    tooltip: "This button only appears on hover.",
                    handler: function (e) {
                        //console.log("this worked");
                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "excel_export";
                        dataService.excelExport(url,ajaxDataObj,excelExportCallBack);
                    }
                },{
                    image: "../images/wizard-icon-16.png",
                    showOnHover: true,
                    tooltip: "Press this to reload Fortis!",
                    handler: function (e) {
                        if (!loadingIndicator) {
                            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
                            var $g = $("#shipGrid");
                            loadingIndicator
                                .css("position", "absolute")
                                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
                        }
                        loadingIndicator.show();                        var url = "lib/php/grid.php";
                        var ajaxDataObj     = {};
                        ajaxDataObj.control = "reload_fortis";
                        dataService.reloadFortis(url,ajaxDataObj,fortisCB);
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

    shipGridObj.grid.onMouseEnter.subscribe(function(e) {
        var cell = this.getCellFromEvent(e);
        this.setSelectedRows([cell.row]);
        e.preventDefault();
    });

    shipGridObj.grid.onMouseLeave.subscribe(function(e) {
        this.setSelectedRows([]);
        e.preventDefault();
    });
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
    //shipGridObj.grid.setSelectionModel(new Slick.CellSelectionModel());
    shipGridObj.grid.onSort.subscribe(function(e, args) {
        // args.multiColumnSort indicates whether or not this is a multi-column sort.
        // If it is, args.sortCols will have an array of {sortCol:..., sortAsc:...} objects.
        // If not, the sort column and direction will be in args.sortCol & args.sortAsc.

        // We'll use a simple comparer function here.
        var comparer = function(a, b) {
            return (a[args.sortCol.field] > b[args.sortCol.field]) ? 1 : -1;
        }

        // Delegate the sorting to DataView.
        // This will fire the change events and update the grid.
        shipGridObj.dataView.sort(comparer, args.sortAsc);
    })

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
