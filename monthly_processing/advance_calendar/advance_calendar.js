/**
 * Created by fs11239 on 12/14/2016.
 */
$(document).ready(function() {
    var url      = "advance_calendar.php";
    var start_rpt_period_val = $(".start_rpt_period").val();
    var to_rpt_period_val    = $(".to_rpt_period").val();

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
    }
    function performStep(action)
    {
        $.ajax({
            type : "POST",
            url  : url,
            async: false,
                data: {
                    control         : action,
                    rpt_period      : getUrlParameter('rpt_period'),
                    code            : code.toString()
                },
            success: function (json) {
                $("#status").append(json+"<br><br>");
                $("#status").addClass( "status_font" );
            }
        });
    }

    $("#rpt_period_div").append(getUrlParameter('rpt_period'));
    $("#title").append(getUrlParameter('ship_code'));

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
            rpt_period  : getUrlParameter('rpt_period')
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
        selectedIndexes = step_grid.getSelectedRows();

        $.each(selectedIndexes, function( index, value ) {
            var action = step_grid.getDataItem(value).action;
            performStep(action);
        });

    });
})