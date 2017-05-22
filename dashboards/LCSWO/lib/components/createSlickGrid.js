define(["./line_chart","./grid_columns",
"./data",
"./grid_options",
    'slickgrid',
    'slickdataview',
    "slickAutoToolTips",
    "slickRowSelection", "slickPager", "slickGroup"], function(lineChart, gridCols, gridData, gridOptions){

    var dataViewProj              = new Slick.Data.DataView();
    var groupItemMetadataProvider = new Slick.Data.GroupItemMetadataProvider();

    var dataViewWO = new Slick.Data.DataView({
/*        groupItemMetadataProvider: groupItemMetadataProvider,
        inlineFilters            : true*/
    });


/*    function groupBYWP() {
        dataViewWO.setGrouping({
            getter: "soc",
            formatter: function (g) {
                return "SOC :  " + g.value + "  <span style='color:green'>(" + g.count + " items)</span>";
            },
            aggregators: [
                new Slick.Data.Aggregators.Sum("a"),
                new Slick.Data.Aggregators.Sum("eac"),
                new Slick.Data.Aggregators.Sum("bac"),
                new Slick.Data.Aggregators.Sum("p2bac"),
                new Slick.Data.Aggregators.Sum("eac_growth")
            ],
            aggregateCollapsed: false,
            lazyTotalsCalculation: true
        });
    }*/

    var createGrid = function (div_name,ajax_data_object,shipStatusCols, options)
    {
        var grid = new Slick.Grid('#'+div_name, dataViewProj, shipStatusCols, options);
        $('#selectBox').change(function() {
            var selectedIndexes  = grid.getSelectedRows();
            var data             = grid.getDataItem(selectedIndexes);
            var data_type_select = $("#selectBox_hours").val();
            gridCols.updateColumnHeaders(woGrid, $(this).val());
            gridData.getWOData(data.Hull, $(this).val(),data_type_select,woGridDataView);
        });

        $('#selectBox_hours').change(function() {
            var top5_type       = $("#selectBox").val();
            var selectedIndexes = grid.getSelectedRows();
            var data            = grid.getDataItem(selectedIndexes);
            gridData.getWOData(data.Hull, top5_type, $(this).val(),woGridDataView);
        });

        function gridDataView(data){
            dataViewProj.beginUpdate();
            dataViewProj.setItems(data);
            dataViewProj.endUpdate();
            dataViewProj.refresh();
            grid.render();
            grid.updateRowCount();
        }
        function woGridDataView(data){
            woGrid.invalidateAllRows();
            dataViewWO.beginUpdate();
            dataViewWO.setItems(data);
            dataViewWO.endUpdate();
            dataViewWO.refresh();
            woGrid.render();
            woGrid.updateRowCount();
        }
        if(!woGrid){
            var woGridCols = gridCols.top5_cols;
            var wo_options = gridOptions.woGridOptions;
            var woGrid = new Slick.Grid('#wo_grid', dataViewWO, woGridCols, wo_options);
/*            dataViewWO.setPagingOptions({
                pageSize: 10,
            });
            var pager = new Slick.Controls.Pager(dataViewWO, woGrid, $("#my_pager"));
            woGrid.setSelectionModel(new Slick.RowSelectionModel({
                selectActiveRow: true
            }));
            dataViewWO.onRowCountChanged.subscribe(function (e, args) {
                woGrid.updateRowCount();
                woGrid.render();
            });
            dataViewWO.onRowsChanged.subscribe(function (e, args) {
                woGrid.invalidateRows(args.rows);
                woGrid.render();
            });
            dataViewWO.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
                var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
                var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
                var options = woGrid.getOptions();
                if (options.enableAddRow != enableAddRow) {
                    woGrid.setOptions({enableAddRow: enableAddRow});
                }
            });
            woGrid.registerPlugin(new Slick.AutoTooltips({
                enableForCells      : true,
                enableForHeaderCells: false,
                maxToolTipLength    : null
            }));

            woGrid.registerPlugin(groupItemMetadataProvider);*/

        }
        gridData.getGridData(ajax_data_object, gridDataView);

        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
        grid.onSelectedRowsChanged.subscribe(function() {
            var selectedIndexes = grid.getSelectedRows();
            var data            = grid.getDataItem(selectedIndexes);
            var top5_type        = $("#selectBox").val();
            var data_type_select = $("#selectBox_hours").val();
            lineChart.drawLineChart(data);
            gridData.getWOData(data.Hull, top5_type,data_type_select, woGridDataView);
            //groupBYWP();
        });
    }
    return {
        createGrid  : createGrid
    };
})