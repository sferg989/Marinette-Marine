define(["../../../../inc/custom_components/createSlickGrid",
"./grid_cols", "./data_repo", "./grid_opt"], function(grid, gridCols, dataService, grid_opt){

    var createGrid = function (div_name,url,ajaxDataObj,shipStatusCols, options)
    {

        var grid1_options = grid_opt.gridOptions;
        var lcs_grid_cols = gridCols.getMetaCols(0);

        var dataCols      = gridCols.dataCols;
        for (i = 0; i < dataCols.length; i++) {
            lcs_grid_cols.push(dataCols[i]);
        }
        var shipGridObj   = grid.createGrid("shipGrid", lcs_grid_cols, grid1_options);
        var lcsGrid       = shipGridObj.grid;
        var lcsDV         = shipGridObj.dataView;

        function gridDataViewCallBack(data){
            console.log("this is call back");
            lcsDV.beginUpdate();
            lcsDV.setItems(data);
            lcsDV.endUpdate();
            lcsDV.refresh();
            lcsGrid.render();
            lcsGrid.updateRowCount();
        }

        var url = "lib/php/lcs.grid.php";
        var ajaxDataObj     = {};
        ajaxDataObj.control = "lcs_grid";
        dataService.getGridData(url,ajaxDataObj,gridDataViewCallBack);

        lcsGrid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
        lcsGrid.onSelectedRowsChanged.subscribe(function() {
            var selectedIndexes = lcsGrid.getSelectedRows();
            //console.log(selectedIndexes);
            var data = lcsGrid.getDataItem(selectedIndexes);
            //conso le.log(grid.getDataItem(selectedIndexes));
            //lineChart.drawLineChart(data);
        });
        lcsGrid.onDblClick.subscribe(function (e, args){

            var cell                = lcsGrid.getCellFromEvent(e)
            var row                 = cell.row;
            var cols                = lcsGrid.getColumns();
            //console.log("name of the Col" + cols[cell.cell].name);
            var level               = cols[cell.cell].name;
            console.log(level);
            var cur_level           = gridCols.findCurLevel(level);
            var drill_level         = gridCols.findDrillLevel(level);
            var metaColsBeforeDrill = gridCols.getMetaCols(cur_level);
            
            var dataItem = lcsDV.getItem(args.row);
            //console.log("this is the Data ITEm ", dataItem);
            ajaxDataObj.metaData = {};
            _.each(metaColsBeforeDrill,function(val){
                var metaDataKey = val.name;
                ajaxDataObj.metaData[val.name] = dataItem[metaDataKey];
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
            //console.log(ajaxDataObj);
            var url = "lib/php/lcs.grid.php";
            lcsGrid.setColumns(metaColsAfterDrill);
            dataService.getGridData(url, ajaxDataObj, lcsDV);
        });

    }
    var createDeliqGrid = function (div_name,url,ajaxDataObj,shipStatusCols, options)
    {

        var grid1_options = grid_opt.gridOptions;
        var deliqCols     = gridCols.getMetaCols(1);
        var deliqGrid     = grid.createGrid("deliqGrid", deliqCols, grid1_options);
        deliqGrid         = deliqGrid.grid;
        var deliqDV       = deliqGrid.dataView;

        function gridDataViewCallBack(data){
            deliqDV.beginUpdate();
            deliqDV.setItems(data);
            deliqDV.endUpdate();
            deliqDV.refresh();
            deliqGrid.render();
            deliqGrid.updateRowCount();
        }
        var ajaxDataObj     = {};
        ajaxDataObj.control = "deliq_grid";
        dataService.getGridData(url,ajaxDataObj,gridDataViewCallBack);
    }

    return {
        createGrid     : createGrid(),
        createDeliqGrid: createDeliqGrid()
    };
})