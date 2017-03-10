define(['jquery',"select2"], function($,select2) {
var createFilterBox = function (filter_name)
{
    var filterDataURL = "dashboard_filter.php";
    $("."+filter_name).select2({
        //minimumResultsForSearch: -1,
        width      : 150,
        allowClear : true,
        placeholder: "Select " + filter_name,
        ajax: {
            url     : filterDataURL + "/" + filter_name,
            type    : 'GET',
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
                return {
                    results: data.items
                };
            },
            cache: true
        }
    });
}
return {
    createFilter: createFilterBox
};
});