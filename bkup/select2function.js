function createSelect2Box(filter_name) {
    $("."+filter_name).select2({
        //minimumResultsForSearch: -1,
        width : 154,
        allowClear : true,
        placeholder: "Select "+filter_name,
        ajax: {
            url: url+"/"+filter_name,
            dataType: 'json',
            delay: 250,
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
}/**
 * Created by fs11239 on 1/4/2017.
 */
