define(["./data",
    'slickgrid',
    'slickdataview',
    "slickRowSelection",
    "slickAutoToolTips"], function(dataRepo){

    var dataView = new Slick.Data.DataView();

    var createGrid = function (div_name,ajax_data_object,shipStatusCols, options)
    {
        var grid = new Slick.Grid('#'+div_name, dataView, shipStatusCols, options);

        grid.registerPlugin(new Slick.AutoTooltips({
            enableForCells      : true,
            enableForHeaderCells: false,
            maxToolTipLength    : null
        }));

        function gridDataView(data){
            dataView.beginUpdate();
            dataView.setItems(data);
            dataView.endUpdate();
            dataView.refresh();
            grid.render();
            grid.updateRowCount();
        }

        dataRepo.getGridData(ajax_data_object, gridDataView);
        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));

    }

    return {
        createGrid: createGrid
    };
})