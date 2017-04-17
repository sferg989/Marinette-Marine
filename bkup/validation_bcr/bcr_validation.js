$(document).ready(function() {
    var url      = "bcr_validation.php";
    function goBack() {
        window.history.back();
    }
    function createSelect2Box(filter_name) {
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
                        filter   : filter_name,
                        q        : params.term, // search term
                        page     : params.page,
                        code: code
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
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };
    var rpt_period = getUrlParameter('rpt_period');
    var code       = getUrlParameter('ship_code');

    createSelect2Box("rpt_period");

    $("#rpt_period_div").append(rpt_period);
    $("#title").append(code);
    $("#rpt_period_div").addClass("title_font");
    $("#title").addClass("title_font");
    $("#back_btn").click(function(){
        goBack();
    });

    $("#load_cobra_data").click(function(){

        var step        = {};
        step.code       = code;
        step.action     = "load_cobra_data";
        step.name       = "Load Cobra Data";
        step.rpt_period = rpt_period;
        var worker;
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).empty();
        }
        $("#status_cobra").append("<div id = \"img_"+step.action+"\"><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");
        workers     = new Worker("workers/load_cobra_data.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        function workerDone(e) {
            console.log(e.data.id+" has completed");
            $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
        }
    });
})