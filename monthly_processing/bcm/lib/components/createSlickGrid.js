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

        function gridDataView(data){
            var sorted, desc, filterVal, count, abs;
            filterVal = $("#bcm_filter");
            if(filterVal =="no_ca"){
                sorted = _.sortBy(data, function(obj) {
                    return obj.desc;
                });
            }
            else{
                sorted = _.sortBy(data, function(obj) {
                    abs = Math.abs(obj.bcrd_change);
                    return abs;
                });

            }
            desc = sorted.reverse();
            dataView.beginUpdate();
            dataView.setItems(desc);
            dataView.endUpdate();
            dataView.refresh();
            grid.render();
            grid.updateRowCount();
            count = grid.getData().getPagingInfo().totalRows;
            console.log(count);
            loadingIndicator.fadeOut();
        }
        $("#bcm_filter").change(function() {

            ajax_data_object.filter_val = $(this).val();
            if (!loadingIndicator) {
                loadingIndicator = $("<span class='loading-indicator'><label>Buffering...</label></span>").appendTo($( "#bcm" ));
                var $g = $("#bcm");
                loadingIndicator
                    .css("position", "absolute")
                    .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                    .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
            }
            loadingIndicator.show();
            dataRepo.getGridData(ajax_data_object, gridDataView)
        });
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Buffering...</label></span>").appendTo($("#bcm"));
            var $g = $("#bcm");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataRepo.getGridData(ajax_data_object, gridDataView);

        var pager = new Slick.Controls.Pager(dataView, grid, $("#my_pager"));

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