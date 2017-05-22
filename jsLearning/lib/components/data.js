/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){

    var getData = function (ajaxDataObj,gridDataViewCallBack) {
        $.ajax({
            dataType: "json",
            url     : "lib/php/grid.php",
            data : ajaxDataObj,
            success: function(returnData) {
                return returnData;
            },
        }).done(function (returnData){
            gridDataViewCallBack(returnData);
            return returnData;
        });

    }
    var excelExport = function (url, ajaxDataObj,excelExportCallBack) {
        $.ajax({
            url : url,
            data: ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            excelExportCallBack(data);
            return data;
        });

    }
    return {
        getData    : getData,
        excelExport: excelExport
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
