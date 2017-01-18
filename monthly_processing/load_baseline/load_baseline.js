/**
 * Created by fs11239 on 12/14/2016.
 */
$(document).ready(function() {
    var url      = "load_baseline.php";
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

    var rpt_period = getUrlParameter('rpt_period');
    var code       = getUrlParameter('ship_code');

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
        var userName =window.btoa("Steve");
        var password =window.btoa("jan17");
        var hostName = "PMDB";
        var portNumber = "4444";
        url = hostName+":"+portNumber+"";
        $.ajax({
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
        $.each(selectedIndexes, function(index, value ) {

        });


    });
})