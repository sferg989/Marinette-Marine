/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    var loadingIndicator = null;

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
    var loadBaan = function (ajaxDataObj,loadCBMCB) {
        var worker;
        workers     = new Worker("lib/workers/load_baan.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
        function workerDone(e) {
            if(e.data.id == undefined){
                return false;
            }
            else{
                loadCBMCB(e.data.response)
            }
        }

    }
    var loadMEAC = function (ajaxDataObj,loadMEACFileCB) {
        var worker;
        workers     = new Worker("lib/workers/load_meac.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
        function workerDone(e) {
            if(e.data.id == undefined){
                return false;
            }
            else{
                loadMEACFileCB(e.data.response)
            }
        }

    }
    var buildMEACTables = function (ajaxDataObj,buildMeacTablesCB) {
        var worker;
        workers     = new Worker("lib/workers/build_meac_tables.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
        function workerDone(e) {
            if(e.data.id == undefined){
                return false;
            }
            else{
                buildMeacTablesCB(e.data.response)
            }
        }

    }

    return {
        getData        : getData,
        loadBaan       : loadBaan,
        buildMEACTables: buildMEACTables,
        loadMEAC       : loadMEAC
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
