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
    var approvePO = function (url, ajaxDataObj) {
        $.ajax({
            url     : url,
            data    : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            console.log("This is the call back", ajaxDataObj);
            return data;
        });

    }
    var deletePO = function (url, ajaxDataObj, insertApprovedPO) {
        $.ajax({
            url     : url,
            data    : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            insertApprovedPO(data);
            return data;
        });

    }
    var excelExport = function (url, ajaxDataObj,excelExportCallBack) {
        $.ajax({
            url     : url,
            data    : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            excelExportCallBack(data);
            return data;
        });

    }
    var reloadFortis = function (url, ajaxDataObj, fortisCB) {
        $.ajax({
            url     : url,
            data    : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            fortisCB(data);
            return data;
        });

    }

    return {
        getData     : getData,
        approvePO   : approvePO,
        reloadFortis: reloadFortis,
        deletePO    : deletePO,
        excelExport : excelExport
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
