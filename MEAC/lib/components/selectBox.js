/**
 * Created by fs11239 on 2/23/2017.
 */
/**
 * Created by fs11239 on 2/23/2017.
 */
define(["select2"], function(){
    var createSelectBox = function (filter_name, url, width) {
        var place_holder = filter_name.replace("_", "  ");
        if(width==undefined){
            width = 154;
        }
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
        defaultRPTPeriod: defaultRPTPeriod
    };
});




