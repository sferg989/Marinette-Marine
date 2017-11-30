
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "lib/components/selectBox",
    "bootbox",
    "slickAutoToolTips",
    "slickHeaderBtn",
    "slickPager"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, selectBox, bootbox) {
$( document ).ready(function() {

    var loadingIndicator = null;
    var url              = "lib/php/update_meac.php";

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
    function  updateEACCB() {
        loadingIndicator.fadeOut();
        console.log("t his is the finish insert");
        bootbox.alert("EAC has been Updated!");
    }
    function excelExportCallBack(data){
        loadingIndicator.fadeOut();
        window.open(data);
    }
    selectBox.createSelectBox("rpt_period","lib/php/update_meac.php");

    selectBox.defaultRPTPeriod();

    $('.ship_code').select2({
        width : 154
    });
    $('.view_updates').select2({
        width : 154
    });
    $('#view_updates').on('select2:close', function (e) {
        var ajaxDataObj        = {};
        ajaxDataObj.control    = "meac_eac_change_grid";
        var rpt_period         = $("#rpt_period").val()
        var view_updates       = $("#view_updates").val()
        var ship_code          = $("#ship_code").val()

        ajaxDataObj.rpt_period = rpt_period;
        ajaxDataObj.view       = view_updates;
        ajaxDataObj.ship_code  = ship_code;
        clearAllRows();
        dataService.getData(url,ajaxDataObj, gridDataViewCallBack);

    });
    $('#ship_code').on('select2:close', function (e) {
        var ajaxDataObj        = {};
        ajaxDataObj.control    = "meac_eac_change_grid";
        var rpt_period         = $("#rpt_period").val()
        var view_updates       = $("#view_updates").val()
        var ship_code          = $("#ship_code").val()

        ajaxDataObj.rpt_period = rpt_period;
        ajaxDataObj.view       = view_updates;
        ajaxDataObj.ship_code  = ship_code;
        clearAllRows();
        dataService.getData(url,ajaxDataObj, gridDataViewCallBack);

    });
    $("#update_eac").click(function() {
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#meac_eac_change_grid");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        var rpt_period   = $("#rpt_period").val();
        var ajaxDataObj     = {};

        ajaxDataObj.rpt_period = rpt_period;
        ajaxDataObj.ship_code  = $("#ship_code").val();
        ajaxDataObj.control    = "accept_changes";
        dataService.updateEAC(url,ajaxDataObj, updateEACCB)

    });
    $("#submit").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('myfile');
        var uploadButton = document.getElementById('submit');
        var statusDiv    = document.getElementById('status');
        var rpt_period   = $("#rpt_period").val();

        event.preventDefault();
        // Get the files from the input
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        //Grab just one file, since we are not allowing multiple file uploads

        var file = files[0];
        //Check the file type

/*        if (file.size >= 2000000 ) {
            statusDiv.innerHTML = 'This file is larger than 2MB. Sorry, it cannot be uploaded.';
            return;
        }*/
        // Add the file to the request.
        formData.append('myfile', file, file.name);
        // Set up the AJAX request.

        var xhr = new XMLHttpRequest();
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#meac_eac_change_grid");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        // Open the connection.
        xhr.open('POST', 'lib/php/update_meac.php?control=upload_v2&rpt_period='+rpt_period+'', true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                //$("#div_file_upload_div").hide();

                console.log("this is the call back");
                var ajaxDataObj        = {};
                ajaxDataObj.control    = "meac_eac_change_grid";
                ajaxDataObj.rpt_period = rpt_period;
                loadingIndicator.fadeOut();
                var rpt_period         = $("#rpt_period").val()
                var view_updates       = $("#view_updates").val()
                var ship_code          = $("#ship_code").val()

                ajaxDataObj.rpt_period = rpt_period;
                ajaxDataObj.view       = view_updates;
                ajaxDataObj.ship_code  = ship_code;
                dataService.getData(url,ajaxDataObj, gridDataViewCallBack);

            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };

        // Send the Data.
        xhr.send(formData, function (){
            console.log("this process finished");

        });

    });

    var shipCols      = gridColumns.cols;
    var groupCols = {
        id      : "ship_code",
        name    : "ship_code",
        header : {
            buttons: [
                {
                    image: "../inc/images/excel-icon.png",
                    showOnHover: true,
                    tooltip: "This button only appears on hover.",
                    handler: function (e) {
                        var url               = "lib/php/update_meac.php";
                        var ajaxDataObj       = {};
                        ajaxDataObj.control   = "excel_export";
                        var ship_code         = $("#ship_code").val();
                        ajaxDataObj.ship_code = ship_code;
                        if (!loadingIndicator) {
                            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
                            var $g = $("#meac_eac_change_grid");
                            loadingIndicator
                                .css("position", "absolute")
                                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
                        }
                        loadingIndicator.show();
                        dataService.excelExport(url,ajaxDataObj,excelExportCallBack);
                    }
                }
            ]
        },
        field   : "ship_code"
    };
    shipCols.unshift(groupCols);
    var grid1_options = gridOptions.gridOptions;
    var shipGridObj   = grid.createGrid("meac_eac_change_grid", shipCols, grid1_options);
    shipGridObj.dataView.setPagingOptions({
        inlineFilters: true,
        pageSize     : 25
    });
    var ajaxDataObj     = {};
    ajaxDataObj.control = "meac_eac_change_grid";
    var rpt_period         = $("#rpt_period").val()
    var view_updates       = $("#view_updates").val()
    var ship_code          = $("#ship_code").val()

    ajaxDataObj.rpt_period = rpt_period;
    ajaxDataObj.view       = view_updates;
    ajaxDataObj.ship_code  = ship_code;
    dataService.getData(url,ajaxDataObj, gridDataViewCallBack);

    var pager = new Slick.Controls.Pager(shipGridObj.dataView, shipGridObj.grid, $("#my_pager"));
    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));

    shipGridObj.dataView.onRowCountChanged.subscribe(function (e, args) {
        shipGridObj.grid.updateRowCount();
        shipGridObj.grid.render();
    });

    shipGridObj.dataView.onRowsChanged.subscribe(function (e, args) {
        shipGridObj.grid.invalidateRows(args.rows);
        shipGridObj.grid.render();
    });

    shipGridObj.dataView.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
        var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
        var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
        var options = shipGridObj.grid.getOptions();
        if (options.enableAddRow != enableAddRow) {
            shipGridObj.grid.setOptions({enableAddRow: enableAddRow});
        }
    });
    shipGridObj.grid.registerPlugin(new Slick.AutoTooltips({
        enableForCells      : true,
        enableForHeaderCells: false,
        maxToolTipLength    : null
    }));
    var headerButtonsPlugin = new Slick.Plugins.HeaderButtons();

    shipGridObj.grid.registerPlugin(headerButtonsPlugin);


});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
