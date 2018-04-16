/**
 * Created by fs11239 on 2/23/2017.
 */
/**
 * Created by fs11239 on 2/23/2017.
 */
define(["select2"], function(){
    var createSelectBox = function (filter_name, url) {
        var place_holder = filter_name.replace("_", "  ");
        $("#"+filter_name).select2({
            //minimumResultsForSearch: -1,
            width : 154,
            allowClear : true,
            placeholder: "Select "+place_holder,
            ajax: {
                url: url+"/"+filter_name,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        control: filter_name,
                        q     : params.term, // search term
                        page  : params.page
                    };
                },
                processResults: function (data, page) {
                    // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data
                    return {
                        results: data.items
                    };
                },
                cache: true
            }
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
        createSelectBox  : createSelectBox,
        defaultRPTPeriod : defaultRPTPeriod
    };
});




