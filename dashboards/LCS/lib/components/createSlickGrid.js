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

        gridData.getData(url, ajax_data_object, gridDataView);

        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
        grid.onSelectedRowsChanged.subscribe(function() {
            var selectedIndexes = grid.getSelectedRows();
            console.log(selectedIndexes);
            var data = grid.getDataItem(selectedIndexes);
            //console.log(grid.getDataItem(selectedIndexes));
            lineChart.drawLineChart(data);
        });
        grid.onDblClick.subscribe(function (e, args){

            var cell                = grid.getCellFromEvent(e)
            var row                 = cell.row;
            var cols                = grid.getColumns();
            //console.log("name of the Col" + cols[cell.cell].name);
            var level               = cols[cell.cell].name;
            var cur_level           = gridCols.findCurLevel(level);
            var drill_level         = gridCols.findDrillLevel(level);
            var metaColsBeforeDrill = gridCols.getMetaCols(cur_level);

            var dataItem = dataView.getItem(args.row);
            //console.log("this is the Data ITEm ", dataItem);
            ajax_data_object.metaData = {};
            _.each(metaColsBeforeDrill,function(val){
                var metaDataKey = val.name;
                ajax_data_object.metaData[val.name] = dataItem[metaDataKey];
            });
            if(drill_level=="undefined"){
                return false;
            }

            var metaColsAfterDrill      = gridCols.getMetaCols(drill_level);
            //console.log(dataItem);


            var dataCols      = gridCols.dataCols;
            for (i = 0; i < dataCols.length; i++) {
                metaColsAfterDrill.push(dataCols[i]);
            }

            //console.log(ajax_data_object);

            grid.setColumns(metaColsAfterDrill);
            gridData.getData(url, ajax_data_object, gridDataView);
        });

    }

    return {
        createGrid: createGrid
    };
})