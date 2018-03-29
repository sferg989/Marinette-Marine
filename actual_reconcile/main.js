
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "slickAutoToolTips",
    "slickHeaderBtn",
    "slickPager"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, bootbox) {
$( document ).ready(function() {

    var loadingIndicator = null;
    function gridDataViewCallBack(data){
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
    var height = $(window).height();

    $("#submit").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('myfile');
        var uploadButton = document.getElementById('submit');
        var statusDiv    = document.getElementById('status');
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
        // Open the connection.
        xhr.open('POST', 'lib/php/grid.php?control=upload', true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                $("#div_file_upload_div").hide();
                $("#po_approval_grid").show();
                console.log("this is the call back");
            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };

        // Send the Data.
        xhr.send(formData, function (){
            console.log("this process finished");
            var url = "../php/grid.php";
            var aja
            var url             = "lib/php/grid.php";
            var ajaxDataObj     = {};
            ajaxDataObj.control = "po_approval_grid";
            ajaxDataObj.po      = po_num;
            dataService.getData(url,ajaxDataObj, gridDataViewCallBack)
        });

    });

    var shipCols      = gridColumns.cols;
    var grid1_options = gridOptions.gridOptions;
    var shipGridObj   = grid.createGrid("po_approval_grid", shipCols, grid1_options);
    shipGridObj.dataView.setPagingOptions({
        inlineFilters: true,
        pageSize     : 25
    });
    var url             = "lib/php/grid.php";
    var ajaxDataObj     = {};
    ajaxDataObj.control = "po_approval_grid";

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


});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
