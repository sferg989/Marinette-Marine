/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    var getData = function (url, ajaxDataObj,gridDataViewCallBack) {
        $.ajax({
            dataType: "json",
            url     : url,
            data : ajaxDataObj,
            success: function(returnData) {
                return returnData;
            },
        }).done(function (returnData){
            var groups = _(returnData).groupBy('ship_code');
            //console.log(groups);
            var out = _(groups).map(function(g, key) {
                return {
                    id          : key,
                    ship_code   : key,
                    ca          : "",
                    s           : _(g).reduce(function (m, x) {return m + x.s;}, 0),
                    p           : _(g).reduce(function (m, x) {return m + x.p;}, 0),
                    a           : _(g).reduce(function (m, x) {return m + x.a;}, 0)
                };
            });
            console.log(out);
            gridDataViewCallBack(out);
            return out;
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
    return {
        getData    : getData,
        excelExport: excelExport
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
