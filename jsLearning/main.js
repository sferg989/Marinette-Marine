
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "slickHeaderBtn"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService) {
$( document ).ready(function() {

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
    function gridDataViewCallBack(data){

        shipGridObj.dataView.beginUpdate();
        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }

    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });

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
    var shipCols      = gridColumns.cols;
    var grid1_options = gridOptions.gridOptions;
    var shipGridObj   = grid.createGrid("shipGrid", shipCols, grid1_options);
    var ajaxDataObj     = {};
    ajaxDataObj.control = "project_grid";
    dataService.getData(ajaxDataObj, gridDataViewCallBack);

    var observableGrid = function(data){
        shipGridObj.call(this, data);
        this.observers = new ObserverList();

    }
    observableGrid.prototype.addObserver = function (observer) {
        this.observers.add(observer);
    }
    observableGrid.prototype.notify  = function (context) {
        var obseverCount = this.observers.count();
        for(var i = 0;i<obseverCount;i++){
            this.observers.get(i)(context);
        }
    }
    var not = notificationService();

    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
