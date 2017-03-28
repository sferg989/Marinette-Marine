define(['slickgrid', 'slickdataview'], function(){

    var createGrid = function (div_name,url,ajax_data_object,shipStatusCols, options)
    {
        var dataView = new Slick.Data.DataView();
        grid = new Slick.Grid('#'+div_name, dataView, shipStatusCols, options);
        $.ajax({
            dataType: "json",
            url     : url,
            data: ajax_data_object,
            success: function(data) {
                dataView.beginUpdate();
                dataView.setItems(data);
                dataView.endUpdate();
                dataView.refresh();
                grid.render();
                grid.updateRowCount();
            }
        });
    }
    return {
        createGrid: createGrid
    };
})