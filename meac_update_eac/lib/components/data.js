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
    var updateEAC = function (url, ajaxDataObj, updateEACCB) {
        $.ajax({
            url     : url,
            data    : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            updateEACCB()
        });

    }
    var excelExport= function (url, ajaxDataObj, excelExportCallBack) {
        var worker;
        workers     = new Worker("lib/workers/excel_export.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
        function workerDone(e) {
            if(e.data.id == undefined){
                return false;
            }
            else{
                excelExportCallBack(e.data.response)

            }
        }
    }

    return {
        getData    : getData,
        updateEAC  : updateEAC,
        excelExport: excelExport
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
