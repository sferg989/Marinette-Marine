
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "lib/components/selectBox",
    "bootbox",
    "lib/components/file_upload",
    "lib/components/wp_tables",
    "bootstrap33",
    "slickAutoToolTips",
    "slickHeaderBtn",
    "slickPager"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, selectBox, bootbox) {
$( document ).ready(function() {

    var loadingIndicator = null;
    var url              = "lib/php/grid.php";

    function gridDataViewCallBack(data){
        //disable BUTTON;
        $('#build_meac').prop('disabled', true);
        var ship_code    = $("#ship_code_status_grid").val().toString();
        var result_multi = ship_code.indexOf(",");
        //if it does not find it at all
        if(result_multi===-1) {
            var status = "";
            for (i = 0; i < data.length; i++) {
                status += data[i].valid + ",";
            }
            var status_result = status.indexOf("Not ready");
            //if it finds one
            //console.log("IS IT READY"+status_result);
            if (status_result === -1) {
                //enable BTN
                $('#build_meac').prop('disabled', false);

            }
        }
        shipGridObj.dataView.beginUpdate();
        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
    }
    function clearAllRows(LoadGridCB){
        shipGridObj.grid.invalidateAllRows();
        if(LoadGridCB){
            LoadGridCB();
        }
    }
    function loadCBMCB(data){
        //console.log("this is the CB"+data);
        bootbox.alert(data);
        loadingIndicator.fadeOut();
    }
    function loadMEACFileCB(data){
        console.log(data);
        loadingIndicator.fadeOut();
        window.open(data);
    }

    var height = $(window).height();

    selectBox.createSelectBox("rpt_period",url);
    selectBox.createSelectBox("ship_code",url);
    selectBox.createSelectBox("ship_code_cbm",url, 200);
    selectBox.createSelectBox("ship_code_wp_table",url, 200);
    selectBox.createSelectBox("ship_code_status_grid",url, 200);
    selectBox.createSelectBox("ship_code_swbs_summary",url, 200);

    selectBox.defaultRPTPeriod();

    $('#ship_code_status_grid').on('select2:select', function (e) {

        var url                = "lib/php/grid.php";
        var ajaxDataObj        = {};
        var rpt_period         = $("#rpt_period").val();
        ajaxDataObj.control    = "status_grid";
        ajaxDataObj.rpt_period = rpt_period;
        ajaxDataObj.ship_code  = $("#ship_code_status_grid").val();

        clearAllRows(function (){
            dataService.getData(url, ajaxDataObj, gridDataViewCallBack)
        });
    });

    $("#load_cbm").click(function(){
        var ship_code         = $("#ship_code_cbm").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control   = "load_cbm";
        ajaxDataObj.ship_code = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });

    $("#load_buyer_responsible").click(function(){
        var ship_code         = $("#ship_code_cbm").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control   = "item_2buyer";
        ajaxDataObj.ship_code = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });

    $("#inv_trans").click(function(){
        var ship_code         = $("#ship_code_cbm").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control   = "inv_trans";
        ajaxDataObj.ship_code = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#cobra_codes").click(function(){
        var ship_code         = $("#ship_code_cbm").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control   = "load_cobra_codes";
        ajaxDataObj.ship_code = ship_code;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#build_swbs_table").click(function(){
        var ship_code         = $("#ship_code_swbs_summary").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control   = "build_swbs_summary_table";
        ajaxDataObj.ship_code = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading SWBS Summary...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#build_meac").click(function(){
        var ship_code    = $("#ship_code_status_grid").val().toString();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control    = "build_meac_file";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>BUILDING MEAC FILE...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadMEAC(ajaxDataObj,loadMEACFileCB);
    });
    var shipCols      = gridColumns.cols;
    var grid1_options = gridOptions.gridOptions;
    var shipGridObj   = grid.createGrid("shipGrid", shipCols, grid1_options);
    $('#build_meac').prop('disabled', true);

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
