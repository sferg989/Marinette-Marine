/**
 * Created by fs11239 on 12/14/2016.
 */

$(document).ready(function() {

    var url      = "load_baseline.php";
    function goBack() {
        window.history.back();
    }
    function checkifBCRAndBaselineLoaded(ship_code, rpt_period)
    {
        var data_check = {};
        data_check = $.ajax({
            type    : "POST",
            url     : url,
            async : false,
            data: {
                control         : "data_check",
                rpt_period      : rpt_period,
                code            : ship_code
            }
        }).responseText;
        return data_check
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

    $("#rpt_period_div").append(rpt_period);
    $("#title").append(code);

    $("#rpt_period_div").addClass("title_font");
    $("#title").addClass("title_font");


    $("#back_btn").click(function(){
        goBack();
    });

    $("#mybutton").click(function() {
        var step        = {};
        var p6Dataval   = $('#p6data').val();

        step.code       = code;
        step.action     = "load_p6_data";
        step.name       = "Load P6 Data";
        step.rpt_period = rpt_period;
        step.p6Data2    = p6Dataval;

        if(p6Dataval=="" || p6Dataval ==undefined){
            alert("Please Insert some data tp upload!");
            return false;
        }
        var worker;
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).empty();
        }
        $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");
        workers     = new Worker("workers/load_p6.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        function workerDone(e) {
            console.log(e.data.id+" has completed");
            $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
        }
    });
    $("#load_bcrs").click(function() {
        var step = {};
        var bcrDataval  = $("#bcr_data").val();
        if(bcrDataval=="" || bcrDataval ==undefined){
            alert("Please Insert some data tp upload!");
            return false;
        }

        step.code       = code;
        step.rpt_period = rpt_period;
        step.bcrData2    = bcrDataval;
        step.action = "load_bcr";
        step.name   = "Load BCR's"
        var worker;
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).remove();
        }
        $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");

        workers     = new Worker("workers/load_bcr.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);

        function workerDone(e) {
            console.log(e.data.id+" has completed");
            $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
        }
    });

    $("#ims_dc").click(function() {
        var step = {};
        var data_check;
        step.code       = code;
        step.rpt_period = rpt_period;
        step.action = "compare_ca";
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).remove();
        }
        step.name   = "Compare Baseline to BCR's"
        data_check = checkifBCRAndBaselineLoaded(code, rpt_period);
        var bcr, baseline, result, bcr_result,
            baseline_result,response_data,id, html_table, file_name;

        result          = data_check.split(",");
        bcr             = result[0];
        baseline        = result[1];
        bcr_result      = bcr.split(":");
        baseline_result = baseline.split(":");

        if(bcr_result[1]=="false")
        {
            alert("please loac BCR's for "+rpt_period);
            return false;
        }
        if(baseline_result[1]=="false")
        {
            alert("please loac Baseline for "+rpt_period);
            return false;
        }
        var worker;
        $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");

        workers           = new Worker("workers/ca_compare.js");
        workers.onmessage = workerDone;

        workers.postMessage(step);
        function workerDone(e) {
            e.data = e.data+"";
            if(e.data!= ""){
                response_data = e.data.split("<>");
                id = response_data[0];
                //$("#text_"+id).addClass("color : red");
                if ($("#bcr_grid").length) {
                    $("#bcr_grid").remove();
                    $("#btn_div").remove();
                }
                html_table = response_data[1];
                file_name = escape(response_data[2]);
                $("#result").append(html_table);
                $("#btn_div").append("<div class = 'col-md-4'><br><br><button id='excel_export' type='button' class='btn btn-success' onclick='window.open(\""+file_name+"\");'>Export to Excel &nbsp&nbsp<img src='../../inc/images/Excel-icon.png' height='24' width='24'/></button></div>");
                //$("#btn_div").append("<div class = 'col-md-4'><br><br><button id='excel_export' type='button' class='btn btn-success' onclick='window.open(\"../../util/export.xls\");'>Export to Excel &nbsp&nbsp<img src='../../inc/images/Excel-icon.png' height='24' width='24'/></button></div>");

                $("#img_"+id+" img").attr("src", "../images/tick.png");
            }
            //console.log(e.data);

        }
    });


})