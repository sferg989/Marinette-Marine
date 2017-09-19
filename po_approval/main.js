
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "slickHeaderBtn"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, bootbox) {
$( document ).ready(function() {
    var loadingIndicator = null;
    function gridDataViewCallBack(data){
        loadingIndicator.fadeOut();
        shipGridObj.dataView.beginUpdate();

        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }

    var height = $(window).height();
    function excelExportCallBack(d){
        window.open(d);
    }

    $("#btn_clear").click(function(){
        $("#status").empty();
    });

    $("#submit").click(function(){
        var po_num         = $("#po_num").val();
        if(po_num=="" || po_num==undefined){
            bootbox.alert("Please input a PO!");
                return false;
        }
        else{
            $("#ship_grid").show();
            if (!loadingIndicator) {
                loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
                var $g = $("#shipGrid");
                loadingIndicator
                    .css("position", "absolute")
                    .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                    .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
            }
            loadingIndicator.show();
            var url             = "lib/php/grid.php";
            var ajaxDataObj     = {};
            ajaxDataObj.control = "project_grid";
            ajaxDataObj.po      = po_num;
            dataService.getData(url, ajaxDataObj, gridDataViewCallBack);
        }
    });
    $("#btn_comitted_rpt_loader").click(function(){
        var ship_code         = $("#ship_code_list").val();
        var worker;
        var ajaxDataObj       = {};
        ajaxDataObj.control   = "load_comitted_rpt";
        ajaxDataObj.code      = ship_code;
        ajaxDataObj.name      = "Loading "+ship_code+" Comitted Report From Baan";
        $("#status").append("<div id = \"img_"+ajaxDataObj.control+"\"><br><img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+ajaxDataObj.name+"<br></div>");
        workers     = new Worker("lib/workers/comitted_rpt_loader.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
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

    var shipCols      = gridColumns.cols;

    var grid1_options       = gridOptions.gridOptions;

    var shipGridObj         = grid.createGrid("shipGrid", shipCols, grid1_options);
    var headerButtonsPlugin = new Slick.Plugins.HeaderButtons();
    shipGridObj.grid.registerPlugin(headerButtonsPlugin);

    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));
});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
