define(['slickgrid', 'slickdataview', "slickRowSelection", "slickColumnPicker"], function(){
    var createGrid = function (div_name,url,ajax_data_object,shipStatusCols, options)
    {
        var dataView = new Slick.Data.DataView();

        var grid = new Slick.Grid('#'+div_name, dataView, shipStatusCols, options);
        $.ajax({
            dataType: "json",
            url     : url,
            data: ajax_data_object,
            success: function(data) {

                console.log(data);
                dataView.beginUpdate();
                dataView.setItems(out);
                dataView.endUpdate();
                dataView.refresh();
                grid.render();
                grid.updateRowCount();

            }
        });

        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
    }

    return {
        createGrid: createGrid
    };
})