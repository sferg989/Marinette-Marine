define(["./line_chart","./grid_columns","./data",
    'slickgrid',
    'slickdataview',
    "slickAutoToolTips",
    "slickRowSelection"], function(lineChart, gridCols, gridData){

    var createGrid = function (div_name,url,ajax_data_object,shipStatusCols, options)
    {
        var dataView = new Slick.Data.DataView();
        var grid = new Slick.Grid('#'+div_name, dataView, shipStatusCols, options);
        function gridDataView(data){
            dataView.beginUpdate();
            dataView.setItems(data);
            dataView.endUpdate();
            dataView.refresh();
            grid.render();
            grid.updateRowCount();
        }
        gridData.getGridData(url, ajax_data_object, gridDataView);

        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));

        var selectedIndexes = grid.getSelectedRows();

        var data = grid.getDataItem(selectedIndexes);
        console.log(grid.getDataItem(selectedIndexes));
        grid.onSelectedRowsChanged.subscribe(function() {
            var selectedIndexes = grid.getSelectedRows();
            //console.log(selectedIndexes);
            console.log("yes");
            //console.log(selectedIndexes);
            lineChart.drawLineChart(data);
        });

    }
    return {
        createGrid: createGrid
    };
})