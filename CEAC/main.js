
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "lib/components/title_update",
    "slickCheckColumn"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, titleUpdate) {
$( document ).ready(function() {
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
    $("#load_ceac_data").click(function(){
        var step = {};
        var n, worker;
        step.action = "load_ceac_updates";
        step.name = "LOAD CEAC Data";

        workers     = new Worker("lib/workers/load_ceac_data.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        $("#status").append("<div class=\"col-md-1\" id = \"img_"+step.action+"\">" +
            "<img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>" +
            "</div><div class=\"col-md-2\" id = \""+step.action+"\">"+step.name+"</div>" +
            "<br>");

        function workerDone(e) {
            if(e.data.id == undefined){
                return false;
            }
            else{
                console.log(e.data.id+" has completed");
                $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
            }
        }

    });
        var step = {};
        //var selectedIndexes = shipGridObj.grid.getSelectedRows(),count = selectedIndexes.length;
        var n, worker;

    //var shipCols      = gridColumns.cols;

    //var projectGridOptions = gridOptions.projectGridOptions;
    //var shipGridObj   = grid.createGrid("shipGrid", shipCols, projectGridOptions);


    var url = "../cost_loader/lib/php/ceac_loader.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "project_grid";

    //dataService.getData(url,ajaxDataObj,gridDataViewCallBack);



});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
