define([
    'slickgrid',
    'slickdataview',
    "slickAutoToolTips",
    "slickRowSelection"], function(){


    var createGrid = function (div_name,cols, options)
    {
        var gridObject = {};
        gridObject.dataView = new Slick.Data.DataView();
        gridObject.grid     = new Slick.Grid('#' + div_name, gridObject.dataView, cols, options);
        return gridObject
    }

    return {
        createGrid: createGrid
    };
})