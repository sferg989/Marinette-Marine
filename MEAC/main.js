
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "lib/components/selectBox",
    "bootbox",
    "lib/components/file_upload",
    "bootstrap33",
    "slickAutoToolTips",
    "slickHeaderBtn",
    "slickPager"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, selectBox, bootbox) {
$( document ).ready(function() {

    var loadingIndicator = null;
    var url              = "lib/php/grid.php";

    function gridDataViewCallBack(data){
        var newdata = _(data).sortBy(function(obj) { return obj.delta })
        shipGridObj.dataView.beginUpdate();
        shipGridObj.dataView.setItems(newdata);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }
    function clearAllRows(){
        shipGridObj.grid.invalidateAllRows();
    }
    var height = $(window).height();

    selectBox.createSelectBox("rpt_period",url);
    selectBox.createSelectBox("ship_code",url);
    selectBox.createSelectBox("ship_code_multi",url, 200);

    selectBox.defaultRPTPeriod();

    $('#ship_code').on('select2:close', function (e) {
    });


    $("#load_cbm").click(function(){

    });


});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
