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
                dataView.setItems(data);
                dataView.endUpdate();
                dataView.refresh();
                grid.render();
                grid.updateRowCount();
                /*                var groups = _(data).groupBy('ship_code');

                 var out = _(groups).map(function(g, key) {
                 return { id: key,
                 ca : data.ca,
                 bcrd: _(g).reduce(function(m,x) { return m + x.bcrd; }, 0) };
                 });*/
            }
        });

        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
        grid.setOptions({ 'frozenColumn': 2});
    }

    return {
        createGrid: createGrid
    };
})