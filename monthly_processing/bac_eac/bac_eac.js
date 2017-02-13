/**
 * Created by fs11239 on 12/14/2016.
 */

$(document).ready(function() {

    var url          = "bac_eac.php";
    var dataCheckUrl = "data_check.php";
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
    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });
    function checkifCPRDataisLoaded(ship_code, rpt_period)
    {
        var data_check = {};
        data_check = $.ajax({
            type    : "POST",
            url     : dataCheckUrl,
            async : false,
            data: {
                control         : "data_check",
                rpt_period      : rpt_period,
                code            : ship_code
            }
        }).responseText;
        return data_check
    }
    var rpt_period = getUrlParameter('rpt_period');
    var ship_code       = getUrlParameter('ship_code');
    $("#rpt_period_div").append(rpt_period);
    $("#title").append(ship_code);

    $("#compare_eac_bac").click(function() {
        var data_check;
        var step = {};
        data_check = checkifCPRDataisLoaded(ship_code, rpt_period);
        var result, prev_results, cur_results, cur, prev;
        result       = data_check.split(",");
        cur_results  = result[0];
        prev_results = result[1];
        cur          = cur_results.split(":");
        prev         = prev_results.split(":");
        if(cur[1]=="false")
        {
            bootbox.alert("please load Current Period Data "+rpt_period);
            return false;
        }
        if(prev[1]=="false")
        {
            bootbox.alert("Pleasae Load Previous Period Data "+rpt_period);
            return false;
        }

        step.code       = ship_code;
        step.rpt_period = rpt_period;
        step.action = "beac_eac_detail_chart";
        step.name   = "Compare EAC BAC "
        var worker;
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).remove();
        }
        $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");

        workers     = new Worker("workers/worker_build_report.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        function workerDone(e) {
            if(e.data.status =="finished"){
                id = e.data.action;
                if ($("#detail_grid").length) {
                    $("#detail_grid").empty();
                    $("#excel_export_btn_div").empty();
                }
                html_table = e.data.html;
                file_name = escape(e.data.excel_file);
                $("#detail_grid").append(html_table);
                $("#excel_export_btn_div").append("<br><br><button id='excel_export' type='button' class='btn btn-success' onclick='window.open(\""+file_name+"\");'>Export to Excel &nbsp&nbsp<img src='../../inc/images/Excel-icon.png' height='24' width='24'/></button>");
                $("#img_"+id+" img").attr("src", "../images/tick.png");
            }

        }

    });
    $("#load_data").click(function() {
        var step        = {};
        step.code       = ship_code;
        step.rpt_period = rpt_period;
        var n, worker;

        step.action = "load_data";
        step.name   = "Load Data";
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).empty();
        }
        $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");

        workers     = new Worker("workers/worker_bac_eac.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);
        function workerDone(e) {
            if(e.data.id =="finished"){
                console.log(e.data.action);
                $("#img_"+e.data.action+" img").attr("src", "../images/tick.png");
            }
        }
    });

})