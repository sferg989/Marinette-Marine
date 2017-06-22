
require([
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    'slickgrid',
    'slickdataview',
    "slickAutoToolTips",
    "slickRowSelection"
    ], function(gridOptions,getUrl, gridColumns,dataService) {
$( document ).ready(function() {
    $("#bcm_filter").change(function() {
        /*        ajax_data_object.filter_val = $(this).val();
         if (!loadingIndicator) {
         loadingIndicator = $("<span class='loading-indicator'><label>Buffering...</label></span>").appendTo($( "#bcm" ));
         var $g = $("#bcm");
         loadingIndicator
         .css("position", "absolute")
         .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
         .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
         }
         loadingIndicator.show();
         dataRepo.getGridData(ajax_data_object, gridDataView)*/

    });

    var notificationService = function () {
        var message = "notifying";
        this.update = function (selectValue){
            console.log("this is the val" +$(this).val());
        }
    }
    function ObserverList(){
        this.observerlist = [];
    }
    ObserverList.prototype.add = function (obj) {
        return this.observerlist.push(obj);
    }
    ObserverList.prototype.count= function (obj) {
        return this.observerlist.length;
    }

    ObserverList.prototype.get = function (index) {
    if(index<-1 && index < this.observerlist.length){
        return this.observerlist[index];
        }
    }
    ObserverList.prototype.removeAt = function (index) {
        this.observerList.splice(index, 1);
    };
    ObserverList.prototype.indexOf = function (obj, startIndex) {
        var i = startIndex;

        while (i < this.observerList.length) {
            if (this.observerList[i] === obj) {
                return i;
            }
            i++;
        }

        return -1;
    }

    var slickGrid = function (name){
        this.name = name;
        this.completed = false;
        var shipCols      = gridColumns.cols;
        var grid1_options = gridOptions.gridOptions;
        var dataView      = new Slick.Data.DataView();
        var grid          = new Slick.Grid('#'+name, dataView, shipCols, grid1_options);
        grid.setSelectionModel(new Slick.RowSelectionModel({
            selectActiveRow: true
        }));
        $.ajax({
            dataType: "json",
            url     : "lib/php/grid.php",
            data : {
                control : "project_grid"
            },
            success: function(returnData) {
                return returnData;
            },
        }).done(function (returnData){
            dataView.beginUpdate();
            dataView.setItems(returnData);
            dataView.endUpdate();
            dataView.refresh();
            grid.render();
            grid.updateRowCount();
        });
    }
    slickGrid.prototype.save = function () {
        console.log('saving Task: ' + this.name);
    };
    slickGrid.prototype.complete = function () {
        console.log('completing task: ' + this.name);
        this.completed = true;
    };
    //var grid1 = new slickGrid("shipGrid");


    var observableGrid = function(data){
        slickGrid.call(this, data);
        this.observers = new ObserverList();
    }
    observableGrid.prototype.addObserver = function (observer) {
        this.observers.add(observer);
    };

    observableGrid.prototype.removeObserver = function (observer) {
        this.observers.removeAt( this.observers.indexOf( observer, 0 ) );
    };

    observableGrid.prototype.notify = function (context) {
        var observerCount = this.observers.count();
        for (var i = 0; i < observerCount; i++) {
            this.observers.get(i)(context);
        }
    }

    observableGrid.prototype.save = function () {
        this.notify(this);
        slickGrid.prototype.save.call(this);
    };
    var task1 = new observableGrid("shipGrid2");
    task1.name = "this is a test";
    task1.save(this.name);
    $("#bcm_filter").change(function() {
        task1.name = $(this).val();
        task1.save(this.name);
    });
    //grid.name = "I LOVE JS";
/*    grid.onSelectedRowsChanged.subscribe(function() {
        var selectedIndexes = grid.getSelectedRows();
        var data            = grid.getDataItem(selectedIndexes);
        grid.save();
        //groupBYWP();
    });*/

/*    grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));*/

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
