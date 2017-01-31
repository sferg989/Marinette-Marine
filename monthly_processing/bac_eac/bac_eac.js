/**
 * Created by fs11239 on 12/14/2016.
 */

$(document).ready(function() {

    var url      = "bac_eac.php";
    function goBack() {
        window.history.back();
    }
    function checkifDataLoaded(ship_code, rpt_period)
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
    var rpt_list = {};
    rpt_list.cpr2ho = {};
    rpt_list.cpr2m = {};
    rpt_list.cpr2l = {};
    rpt_list.cpr2d = {};
    rpt_list.cpr2h = {};
    rpt_list.cpr1m = {};
    rpt_list.cpr1l = {};
    rpt_list.cpr1h = {};
    rpt_list.cpr1d = {};

    rpt_list.cpr2ho.title   = "02-02H CPR 2 OutSource";
    rpt_list.cpr2ho.action = "02-02H_CPR_2_OutSource";
    rpt_list.cpr2m.title   = "02-02M CPR 2 Material";
    rpt_list.cpr2m.action = "02-02m_cpr_2_material";
    rpt_list.cpr2l.title   = "02-02L CPR 2 Labor";
    rpt_list.cpr2l.action = "02-02L_CPR_2_Labor";
    rpt_list.cpr2d.title   = "02-02D CPR 2 Dollars";
    rpt_list.cpr2d.action = "02-02D_CPR_2_Dollars";
    rpt_list.cpr2h.title   = "02-02H CPR 2 Hours";
    rpt_list.cpr2h.action = "02-02H_CPR_2_Hours";
    rpt_list.cpr1m.title   = "02-01M CPR 1 Material";
    rpt_list.cpr1m.action = "02-01M_CPR_1_Material";
    rpt_list.cpr1l.title   = "02-01L CPR 1 Labor";
    rpt_list.cpr1l.action = "02-01L_CPR_1_Labor";
    rpt_list.cpr1h.title   = "02-01H CPR 1 Hours";
    rpt_list.cpr1h.action = "02-01H_CPR_1_Hours";
    rpt_list.cpr1d.title   = "02-01D CPR 1 Dollars";
    rpt_list.cpr1d.action = "02-01D_CPR_1_Dollars";
    $("#load_data").click(function() {
        var step        = {};
        step.code       = code;
        step.rpt_period = rpt_period;
        var n, worker;
        $.each( rpt_list, function( key, value ) {
            step.action = value.action;
            step.name   = value.title;
            if($("#img_"+step.action.length))
            {
                $("#img_"+step.action).empty();
            }
            $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");

            workers     = new Worker("workers/worker_bac_eac.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);
            function workerDone(e) {
                console.log(e.data+" has completed");
                //$("#img_"+e.data+" img").attr("src", "../images/tick.png");
            }
        });
    });
    $("#compare_eac_bac").click(function() {
        var step = {};
        step.code       = code;
        step.rpt_period = rpt_period;
        step.bcrData2    = bcrDataval;
        step.action = "compare_data";
        step.name   = "Compare EAC BAC "
        var worker;
        if($("#img_"+step.action.length))
        {
            $("#img_"+step.action).remove();
        }
        $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");

        workers     = new Worker("workers/compare_data.js");
        workers.onmessage = workerDone;
        workers.postMessage(step);

        function workerDone(e) {
            e.data = e.data+"";
            if(e.data!= ""){
                response_data = e.data.split("<>");
                id = response_data[0];
                //$("#text_"+id).addClass("color : red");
                if ($("#bac_eac_grid").length) {
                    $("#bac_eac_grid").remove();
                    $("#excel_export_btn_div").empty();
                }
                html_table = response_data[1];
                file_name = escape(response_data[2]);
                $("#result").append(html_table);
                $("#excel_export_btn_div").append("<br><br><button id='excel_export' type='button' class='btn btn-success' onclick='window.open(\""+file_name+"\");'>Export to Excel &nbsp&nbsp<img src='../../inc/images/Excel-icon.png' height='24' width='24'/></button>");

                $("#img_"+id+" img").attr("src", "../images/tick.png");
            }
        }
    });
})