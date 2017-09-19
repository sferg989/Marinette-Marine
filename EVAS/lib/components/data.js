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
        $.ajax({
            url     : url,
            data : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            excelExportCallBack(data);
            return data;
        });

    }
    var loadTestResults = function (url, ajaxDataObj,reloadGridData) {
        $.ajax({
            url : url,
            data: ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            reloadGridData();
    });

    }

    return {
        getData        : getData,
        excelExport    : excelExport,
        loadTestResults: loadTestResults
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
