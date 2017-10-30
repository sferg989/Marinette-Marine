
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "bootbox",
    "../inc/custom_components/jsonexcelexport",
    "slickAutoToolTips",
    "slickHeaderBtn"
    ], function(grid,gridOptions,getUrl, gridColumns,dataService, bootbox,jsonExporter) {
$( document ).ready(function() {
    function copyTextToClipboard(text) {
        var textArea = document.createElement("textarea");

        //
        // *** This styling is an extra step which is likely not required. ***
        //
        // Why is it here? To ensure:
        // 1. the element is able to have focus and selection.
        // 2. if element was to flash render it has minimal visual impact.
        // 3. less flakyness with selection and copying which **might** occur if
        //    the textarea element is not visible.
        //
        // The likelihood is the element won't even render, not even a flash,
        // so some of these are just precautions. However in IE the element
        // is visible whilst the popup box asking the user for permission for
        // the web page to copy to the clipboard.
        //

        // Place in top-left corner of screen regardless of scroll position.
        textArea.style.position = 'fixed';
        textArea.style.top = 0;
        textArea.style.left = 0;

        // Ensure it has a small width and height. Setting to 1px / 1em
        // doesn't work as this gives a negative w/h on some browsers.
        textArea.style.width = '2em';
        textArea.style.height = '2em';

        // We don't need padding, reducing the size if it does flash render.
        textArea.style.padding = 0;

        // Clean up any borders.
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';

        // Avoid flash of white box if rendered for any reason.
        textArea.style.background = 'transparent';


        textArea.value = text;

        document.body.appendChild(textArea);

        textArea.select();

        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            console.log('Copying text command was ' + msg);
        } catch (err) {
            console.log('Oops, unable to copy');
        }

        document.body.removeChild(textArea);
    }

    var loadingIndicator = null;
    function gridDataViewCallBack(data){
        loadingIndicator.fadeOut();
        shipGridObj.dataView.beginUpdate();
        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
        jsonExporter.jsonExporter(data,"PO APPROVAL", "YES");
    }
    function clearAllRows(){
        shipGridObj.grid.invalidateAllRows();
    }
    var height = $(window).height();
    function excelExportCallBack(data){
        jsonExporter.jsonExporter(data,"PO APPROVAL", "YES");
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
            clearAllRows();
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
    $("#btn_excel_export").click(function(){
        var po_num         = $("#po_num").val();
        if(po_num=="" || po_num==undefined){
            bootbox.alert("Please input a PO!");
            return false;
        }
        var url             = "lib/php/grid.php";
        var ajaxDataObj     = {};
        ajaxDataObj.control = "project_grid";
        ajaxDataObj.po      = po_num;
        dataService.getData(url, ajaxDataObj, excelExportCallBack);
    });

/*    $("#btn_copy_paste").click(function(){
        var table = $('<table id = "bar"></table>').addClass('foo');
        for(i=0; i<3; i++){
            var row = $('<tr></tr>').addClass('bar').text('result ' + i);
            table.append(row);
        }

        copyTextToClipboard("this workde<br>hello");
        bootbox.alert("this worked");
    });*/


    var shipCols      = gridColumns.cols;

    var grid1_options       = gridOptions.gridOptions;

    var shipGridObj         = grid.createGrid("shipGrid", shipCols, grid1_options);
    shipGridObj.grid.registerPlugin(new Slick.AutoTooltips({
        enableForCells      : true,
        enableForHeaderCells: false,
        maxToolTipLength    : null
    }));
    function doc_keyUp(e) {

        // this would test for whichever key is 40 and the ctrl key at the same time
        if (e.ctrlKey && e.keyCode == 40) {
            var selectedIndexes = shipGridObj.grid.getSelectedRows();
            var data            = shipGridObj.grid.getDataItem(selectedIndexes);
            var wp = data.wp
            copyTextToClipboard(wp);
        }
    }
    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel());
// register the handler
    document.addEventListener('keyup', doc_keyUp, false);

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
