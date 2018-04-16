
require([
    "lib/components/grid_options",
    "lib/components/selectBox",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "../inc/custom_components/jsonexcelexport",
    "slickGridFrozen",
    "slickdataview",
    "slickAutoToolTipsFrozen",
    "slickHeaderBtn",
    "slickRowSelectionFrozen",
    "dragevent",
    "slickColumnPicker",
    "slickPagerFrozen",
    "jQueryMouseWheel",
    ], function(gridOptions,selectBox,getUrl, gridColumns,dataService, bootbox,jsonExporter) {
$( document ).ready(function() {

    var url = "lib/php/grid.php";
    selectBox.createSelectBox("cobra_proj",url,150);
    selectBox.createSelectBox("p6_proj",url, 400, true, false);
    $('#cobra_proj').on("select2:select", function (e) {
        $('#p6_proj').empty();
        //$("#p6_proj").select2('data', null)
        var selected_element = $(e.currentTarget);
        var cobra_proj = selected_element.val();
        selectBox.getP6ProjData(url,"p6_proj",400,cobra_proj);

    });
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
    function clearAllRows(){
        shipGridObj.grid.invalidateAllRows();
    }
    $("#btn_hc").click(function(){
        var ship_code         = $("#cobra_proj").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control   = "hc";
        ajaxDataObj.ship_code = ship_code;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#hc_grid");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.getData(url,ajaxDataObj,gridDataViewCallBack);
    });
    var height = $(window).height();
    function excelExportCallBack(data){
        console.log(data);
        window.open(data);
    }

    var shipCols      = gridColumns.cols;

    var grid1_options = gridOptions.gridOptions;
    var shipGridObj= {};
    shipGridObj.dataView = {};
    shipGridObj.grid = {};
    shipGridObj.dataView = new Slick.Data.DataView();
    shipGridObj.grid     = new Slick.Grid('#hc_grid', shipGridObj.dataView, shipCols, grid1_options);


    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel());

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
