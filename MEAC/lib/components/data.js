/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    var getData = function (url, ajaxDataObj,gridDataViewCallBack) {
        $.ajax({
            dataType: "json",
            url     : url,
            data : ajaxDataObj,
            success: function(data) {
                return data;
            },
        }).done(function (data){
            gridDataViewCallBack(data);
            return data;
        });

    }
    function getLastLayoutUsed(gridDataViewCallBack, getNewGridColsDefinitionCB){
        var ajaxDataObjLayout     = {};
        ajaxDataObjLayout.control = "last_layout_used";
        $.ajax({
            url     : "lib/php/meac_grid.php",
            data    : ajaxDataObjLayout,
            success: function(json) {
                var parts, val, text;
                parts = json.split(",");
                val = parts[0];
                text  = parts[1];
                //console.log(val);
                getNewGridColsDefinitionCB(text);
                $('select option[value="'+val+'"]').attr("selected",true);
                $('#layout_list').show();
                var url = "lib/php/meac_grid.php";
                var ajaxDataObj           = {};
                ajaxDataObj.control       = "part_level_MEAC";
                ajaxDataObj.udf_layout_id = val;
                getData(url, ajaxDataObj,gridDataViewCallBack);
            }
        });
    }
    var getLayoutList = function (ajaxDataObj,gridDataViewCallBack, getNewGridColsDefinitionCB){
        $.ajax({
            url     : "lib/php/meac_grid.php",
            data    : ajaxDataObj,
            dataType: 'json',
            success: function(json) {
                $.each(json.data, function(i, value) {
                    $('#layout_list').append($('<option>').text(value.text).attr('value', value.value));
                });
                getLastLayoutUsed(gridDataViewCallBack, getNewGridColsDefinitionCB)
            }
        });
    }
    var excelProjectExport = function (url, ajaxDataObj,excelExportCallBack) {
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
        getData           : getData,
        getLayoutList     : getLayoutList,
        excelProjectExport: excelProjectExport
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
