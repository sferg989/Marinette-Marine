define(["./data",
    'slickgrid',
    'slickdataview',
    "slickRowSelection",
    "slickAutoToolTips",
    "slickPager"], function(dataRepo){

    var dataView = new Slick.Data.DataView();
    dataView.setPagingOptions({
        pageSize: 20,
    });
    var loadingIndicator = null;

    var createGrid = function (div_name,ajax_data_object,shipStatusCols, options)
    {
        var grid = new Slick.Grid('#'+div_name, dataView, shipStatusCols, options);

        grid.registerPlugin(new Slick.AutoTooltips({
            enableForCells      : true,
            enableForHeaderCells: false,
            maxToolTipLength    : null
        }));
        var pager = new Slick.Controls.Pager(dataView, grid, $("#my_pager"));

        function gridDataView(data){
            dataView.beginUpdate();
            dataView.setItems(data);
            dataView.endUpdate();
            dataView.refresh();
            grid.render();
            grid.updateRowCount();
            count = grid.getData().getPagingInfo().totalRows;

            loadingIndicator.fadeOut();
        }
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Buffering...</label></span>").appendTo($("#bcm"));
            var $g = $("#log_analysis_grid");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataRepo.getGridData(ajax_data_object, gridDataView);

        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
        dataView.onRowCountChanged.subscribe(function (e, args) {
            grid.updateRowCount();
            grid.render();
        });
        dataView.onRowsChanged.subscribe(function (e, args) {
            grid.invalidateRows(args.rows);
            grid.render();
        });
        dataView.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
            var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
            var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
            var options = grid.getOptions();
            if (options.enableAddRow != enableAddRow) {
                grid.setOptions({enableAddRow: enableAddRow});
            }
        });
    }

    return {
        createGrid: createGrid
    };
})