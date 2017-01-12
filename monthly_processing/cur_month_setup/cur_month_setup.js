/**
 * Created by fs11239 on 12/14/2016.
 */
$(document).ready(function() {
    var url      = "cur_month_setup.php";
    function goBack() {
        window.history.back();
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
    function performStep(step)
    {
        $.ajax({
            type : "POST",
            url  : url,
            async: false,
            dataType: 'json',
            data: {
                control         : step.action,
                rpt_period      : getUrlParameter('rpt_period'),
                code            : code.toString()
            }
        });
    }
    var rpt_period = getUrlParameter('rpt_period');
    var code         = getUrlParameter('ship_code');

    $("#rpt_period_div").append(rpt_period);
    $("#title").append(code);

    $("#rpt_period_div").addClass("title_font");
    $("#title").addClass("title_font");
    var options = [];
    var options = {
        enableCellNavigation: true,
        editable            : true,
        forceFitColumns     : true,
        autoHeight          : true,
        sort                : false,
        autoEdit            : true,
        asyncEditorLoading  : false
    };
    var checkboxSelector = new Slick.CheckboxSelectColumn({
        cssClass: "slick-cell-checkboxsel"
    });
    var columns = [];

    columns.push(checkboxSelector.getColumnDefinition());
    columns.push({
        id    : "step",
        name  : "Step",
        field : "name"
    });

    var dataView = new Slick.Data.DataView();
    step_grid    = new Slick.Grid("#step_grid", dataView, columns, options);
    step_grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
    step_grid.registerPlugin(checkboxSelector);
    var columnpicker = new Slick.Controls.ColumnPicker(columns, step_grid, options);

    $.ajax({
        dataType: "json",
        url     : url,
        data: {
            control   : "step_grid",
            rpt_period : rpt_period
        },
        success: function(data) {
            dataView.beginUpdate();
            dataView.setItems(data);
            dataView.endUpdate();
            dataView.refresh();
            step_grid.render();
            step_grid.updateRowCount();
        }
    });

    $("#back_btn").click(function(){
        goBack();
    });


    $("#mybutton").click(function() {
        var step = {};
        step.code       = code;
        step.rpt_period = rpt_period;
        var selectedIndexes = step_grid.getSelectedRows(),count = selectedIndexes.length;
        var n, worker;
        $.each(selectedIndexes, function(index, value ) {
            step.action = step_grid.getDataItem(value).action;
            step.name   = step_grid.getDataItem(value).name;
            $("#status").append("<br><div class=\"row\"><div class=\"col-md-1\" id = \"img_"+step.action+"\"><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/></div><div class=\"col-md-2\" id = \""+step.action+"\">"+step.name+"</div></div><br>");

            workers     = new Worker("workers/cobra_bkup.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);

            function workerDone(e) {
                    console.log(e.data.id+" has completed");
                $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
            }
        });
        $.each(selectedIndexes, function( index, value ) {
/*            workers.action = new Worker('workers/cobra_bkup.js');

            //var worker = new Worker('workers/cobra_bkup.js');
            workers.action.postMessage(step);
            workers.action.onmessage = function(e) {
                msg = e.data;
                if(msg=="finished")
                {
                    $("#img_"+step.action+" img").attr("src", "../images/tick.png");
                }
            }*/

            //$("#"+step.action).addClass("complete");
            //performStep(step);
            //
        });

    });
})