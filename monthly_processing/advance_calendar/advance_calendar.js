/**
 * Created by fs11239 on 12/14/2016.
 */
$(document).ready(function() {

    function goBack() {
        window.location.href = '../processing_status/index.html';
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
    function performStep(action)
    {
        $.ajax({
            type : "POST",
            url  : url,
            async: false,
                data: {
                    control         : action,
                    rpt_period      : rpt_period_val,
                    code            : code.toString()
                },
            success: function (json) {
                $("#status").append(json+"<br><br>");
                $("#status").addClass( "status_font" );
            }
        });
    }
    var url, rpt_period_val, ship_code_val;
    url            = "advance_calendar.php";
    rpt_period_val = getUrlParameter("rpt_period");
    ship_code_val  = getUrlParameter("ship_code");

    $("#rpt_period_div").append(rpt_period_val);
    $("#title").append(ship_code_val);

    $("#title").addClass("title_font");
    $("#rpt_period_div").addClass("title_font");
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
    step_grid = new Slick.Grid("#step_grid", dataView, columns, options);
    step_grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
    step_grid.registerPlugin(checkboxSelector);
    var columnpicker = new Slick.Controls.ColumnPicker(columns, step_grid, options);
    var code = getUrlParameter('ship_code');

    $.ajax({
        dataType: "json",
        url     : url,
        data: {
            control     : "step_grid",
            rpt_period  : rpt_period_val
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
    $("#submit_btn").click(function() {
        var step        = {};

        selectedIndexes = step_grid.getSelectedRows();
        if(selectedIndexes.length){
            //build an object that contains all the work that needs to be done.
            //then send that work to a worker.
            var i = 0;
            $.each(selectedIndexes, function( index, value ) {
                var action          = step_grid.getDataItem(value).action;
                var name            = step_grid.getDataItem(value).name;
                step.ship_code      = ship_code_val;
                step.rpt_period     = rpt_period_val;
                step.action         = action;
                step.name           = name;
                step["action_" + i] = action;

                //performStep(action);
                if($("#img_"+step.action.length))
                {
                    $("#img_"+step.action).empty();
                }
                $("#status").append("<div id = \"img_"+step.action+"\"><br><img src=\"../../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+step.name+"<br></div>");
                i++;
            });
            //send the count of actions to worker, because e.data.length causes errors.
            step.count = i;
            var worker;
            workers     = new Worker("workers/advance_calendar.js");
            workers.onmessage = workerDone;
            workers.postMessage(step);
            function workerDone(e) {
                console.log(e.data.id+" has completed");
                $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
            }
        }
        else{
            bootbox.alert("<h6>Please make a selection!</h6>");
        }


    });
})