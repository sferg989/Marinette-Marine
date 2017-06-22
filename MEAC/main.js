
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "slickPager"
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

    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });

    var shipCols           = gridColumns.cols;
    var projectGridOptions = gridOptions.projectGridOptions;
    var shipGridObj        = grid.createGrid("meacGrid", shipCols, projectGridOptions);
    shipGridObj.dataView.setPagingOptions({
        inlineFilters            : true,
        pageSize: 25
    });
    var url = "lib/php/meac_grid.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "meac_grid";
    ajaxDataObj.ship_code= 0481;

    dataService.getData(url,ajaxDataObj,gridDataViewCallBack);
    var pager = new Slick.Controls.Pager(shipGridObj.dataView, shipGridObj.grid, $("#my_pager"));

    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));
    shipGridObj.dataView.onRowCountChanged.subscribe(function (e, args) {
        shipGridObj.grid.updateRowCount();
        shipGridObj.grid.render();
    });
    shipGridObj.dataView.onRowsChanged.subscribe(function (e, args) {
        shipGridObj.grid.invalidateRows(args.rows);
        shipGridObj.grid.render();
    });
    shipGridObj.grid.onCellChange.subscribe(function (e,args) {
        console.log(args);

    });
    shipGridObj.dataView.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
        var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
        var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
        var options = shipGridObj.grid.getOptions();
        if (options.enableAddRow != enableAddRow) {
            shipGridObj.grid.setOptions({enableAddRow: enableAddRow});
        }
    });



});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
