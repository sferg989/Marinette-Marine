/**
 * Created by fs11239 on 2/23/2017.
 */
/**
 * Created by fs11239 on 2/23/2017.
 */
define(["select2"], function(){
    var createSelectBox = function (filter_name, url, width, disabled, autoLoad) {
        if(disabled==undefined){
            disabled = false;
        }
        if(autoLoad==undefined){
            autoLoad = true;
        }
        var place_holder = filter_name.replace("_", "  ");
        if(width==undefined){
            width = 154;
        }
        if(autoLoad== false){
            $("#"+filter_name).select2({
                //minimumResultsForSearch: -1,
                width          : width,
                allowClear     : true,
                disabled       : disabled
            });
            return true;
        }
        else{
            $.ajax({
                url: url+"/"+filter_name,
                dataType: 'json',
                data: {
                    control: filter_name
                },
                success: function(data) {
                    return data;
                },
            }).done(function (data){
                $("#"+filter_name).select2({
                    //minimumResultsForSearch: -1,
                    width          : width,
                    allowClear     : true,
                    disabled       : disabled,
                    data: $.map(data, function (obj) {
                        //console.log(obj.id);
                        return {
                            id: obj.id,
                            text: obj.text
                        };
                    })
                });
                return data;
            });
        }

    }

    var getP6ProjData = function (url, filter_name, width,cobra_proj) {

        $.ajax({
            url: url+"/"+filter_name,
            dataType: 'json',
            data: {
                cobra_proj: cobra_proj,
                control: filter_name
            },
            success: function(data) {
                return data;
            },
        }).done(function (data){
            $("#"+filter_name).select2({
                //minimumResultsForSearch: -1,
                width          : 400,
                allowClear     : true,
                data: $.map(data, function (obj) {
                    //console.log(obj.id);
                    return {
                        id: obj.id,
                        text: obj.text
                    };
                })
            });
            $("#"+filter_name).select2({
                disabled: false,
                width   : 400
            });
            return data;
        });

    }
    var rpt_period, defaultRPTPeriod;
    var defaultRPTPeriod = function (){
        var dd, mm,month,year,yyyy;
        var today = new Date();
        dd = today.getDate();
        mm = today.getMonth()+1; //January is 0!
        yyyy = today.getFullYear();
        if(dd<25){
            mm = mm-1;
            if(mm==0){
                mm = 12;
                yyyy = yyyy-1;
            }
        }
        if(mm<10) {
            mm='0'+mm
        }
        yyyy = yyyy.toString();

        rpt_period = yyyy + mm;
        $("#rpt_period").append("<option value="+ rpt_period +">"+rpt_period+"</option>");
    }
    return {
        createSelectBox : createSelectBox,
        defaultRPTPeriod: defaultRPTPeriod,
        getP6ProjData   : getP6ProjData
    };
});




