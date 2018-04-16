/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    var getData = function (url, ajaxDataObj,gridDataViewCallBack) {
        $.ajax({
            dataType: "json",
            url     : url,
            data    : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            gridDataViewCallBack(data);
            return data;
        });

    }
    var excelExport = function (url, ajaxDataObj,excelExportCallBack) {
        var step        = {};
        step.control = "excel_export"
        workers     = new Worker("lib/workers/excel_export.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        function workerDone(e) {
            //console.log("this is the reponse"+e.data.response);
            if(e.data.response===undefined){
             return;
            }
            else{
                //console.log(e.data.response+" has completed");
                excelExportCallBack(e.data.response);
            }
        }

    }

    return {
        getData     : getData,
        excelExport : excelExport
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
