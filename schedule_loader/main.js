
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "slickCheckColumn",
    ], function(grid,gridOptions,getUrl, gridColumns,dataService,bootBox) {
$( document ).ready(function() {
    function gridDataViewCallBack(data){
        shipGridObj.dataView.beginUpdate();

        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }
    var height = $(window).height();
    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });
    $("#load_schedule_data").click(function(){
        var step = {};
        var selectedIndexes = shipGridObj.grid.getSelectedRows(),count = selectedIndexes.length;
        if(count<1){
            bootBox.alert("Please select a Hull or Period!");
        }
        var n, worker;
        $.each(selectedIndexes, function(index, value ) {
            step.action       = "load_schedule_data";
            step.code         = shipGridObj.grid.getDataItem(value).ship_code;
            step.name         = shipGridObj.grid.getDataItem(value).name;
            step.ppm_ap       = shipGridObj.grid.getDataItem(value).ppm_ap_id;
            step.ppm_bl       = shipGridObj.grid.getDataItem(value).ppm_bl_id;
            workers           = new Worker("lib/workers/load_schedule_data.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);

            $("#status").append("<br><div class=\"row\"><div class=\"col-md-1\" id = \"img_"+step.code+"\">" +
                "<img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>" +
                "</div><div class=\"col-md-2\" id = \""+step.action+"\">"+step.name+"</div></div><br>");

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
    });

    var shipCols      = gridColumns.cols;
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "slick-cell-checkboxsel"
    });
    shipCols.unshift(checkboxSelector.getColumnDefinition());;

    var projectGridOptions = gridOptions.projectGridOptions;
    var shipGridObj        = grid.createGrid("shipGrid", shipCols, projectGridOptions);

    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));

    shipGridObj.grid.registerPlugin(checkboxSelector);
    new Slick.Controls.ColumnPicker(shipCols, shipGridObj.grid,projectGridOptions);

    var url = "lib/php/schedule_loader.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "project_grid";
    dataService.getData(url,ajaxDataObj,gridDataViewCallBack);

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
