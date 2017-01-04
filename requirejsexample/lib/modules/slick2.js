define(['jquery',"select2"], function($,select2) {
    var createFilterBox = function (filter_name, url) {
        $("."+filter_name).select2({
            //minimumResultsForSearch: -1,
            width:       150,
            allowClear:  true,
            placeholder: "Select " + filter_name,
            ajax: {
                url     : url,
                dataType: 'json',
                delay   : 250,
                data: function (params) {
                    return {
                        filter: filter_name,
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
    var createMeth = function(text) {
        //console.log("hello from the other side" +text);
        console.log(" as" +text);
        //alert("yes");
        //$(".rid").append("I love JQuery");
    };
    return {
        test           : createMeth,
        createFilterBox: createFilterBox
    };
});/**
 * Created by fs11239 on 12/14/2016.
 */
