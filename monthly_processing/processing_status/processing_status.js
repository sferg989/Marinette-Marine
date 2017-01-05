/**
 * Created by fs11239 on 12/14/2016.
 */
$(document).ready(function() {
    var arrayOfGrids  =[];
    var url      = "processing_status.php";
    var rpt_period = $(".rpt_period").val();
    function openRowDivs()
    {
        $("#status_grids").append("<div class=\"row\">");
    }
    function closeRowDiv()
    {
        $("#status_grids").append("</div>");
    }
    function createRowElement(element_name, ship_name)
    {
        $("#status_grids").append("<div class=\"col-md-6\"><br><h6>"+ship_name+"</h6><div id = \""+element_name+"\"></div></div>");
    }
    function updateShipProcessStatus(code,rpt_period, status,step_id,comment_id,pfa_notes)
    {

        $.ajax({
            dataType: "json",
            url     : url,
            data: {
                control   : "update_status",
                ship_code : code,
                rpt_period: rpt_period,
                status    : status,
                step_id   : step_id,
                comment_id: comment_id,
                pfa_notes : pfa_notes
            }
        });
    }
    function initGrid(index, control, ship_code, rptPeriod)
    {
        var dataView = new Slick.Data.DataView();
        var grid = new Slick.Grid('#' + index, dataView, shipStatusCols, options);
        grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
        $.ajax({
            dataType: "json",
            url     : url,
            data: {
                control   : control,
                ship_code : ship_code,
                rpt_period: rptPeriod
            },
            success: function(data) {
                dataView.beginUpdate();
                dataView.setItems(data);
                dataView.endUpdate();
                dataView.refresh();
                grid.render();
                grid.updateRowCount();
            }
        });
        grid.onCellChange .subscribe(function(e, args){
            var status     = args.item.step_status;
            var step_id    = args.item.id;
            var code       = args.item.code;
            var rpt_period = args.item.rpt_period;
            var comment_id = args.item.comment_id;
            var pfa_notes  = args.item.pfa_notes;
            updateShipProcessStatus(code,rpt_period, status,step_id,comment_id,pfa_notes);
        });
        arrayOfGrids.push(grid);

    }
    function myStepLink(row, cell, value, columnDef, dataContext) {
        var link_paran = "<a href="+dataContext.url+"?ship_code="+dataContext.code+"&rpt_period="+rpt_period+">"+value+"</a>";
        return link_paran;
    }
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
    createSelect2Box("rpt_period");
    var shipStatusCols = [];
    shipStatusCols= [
        {
            id                 : "step_status",
            name               : "Done",
            minWidth           : 30,
            maxWidth           : 40,
            cssClass           : "cell-effort-driven",
            field              : "step_status",
            formatter          : Slick.Formatters.Checkmark,
            editor             : Slick.Editors.Checkbox,
            cannotTriggerInsert: true,
            sortable           : true
        },{
            id       : "wi",
            name     : "WOrk Instruction",
            formatter: myStepLink,
            field    : "wi"
        },{
            id   : "timeline",
            name : "timeline",
            field: "timeline"
        },{
            id    : "pfa_notes",
            name  : "PFA Notes",
            editor: Slick.Editors.Text,
            field : "pfa_notes"
        }];
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
        id    : "project_name",
        name  : "Project Name",
        field : "project_name"
    },{
        id   : "code",
        name : "Ship Code",
        field: "code"
    });

    var dataView = new Slick.Data.DataView();
    project_grid = new Slick.Grid("#project_grid", dataView, columns, options);
    project_grid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
    project_grid.registerPlugin(checkboxSelector);
    var columnpicker = new Slick.Controls.ColumnPicker(columns, project_grid, options);

    $.ajax({
        dataType: "json",
        url     : url,
        data: {
            control   : "project_grid"
        },
        success: function(data) {
            dataView.beginUpdate();
            dataView.setItems(data);
            dataView.endUpdate();
            dataView.refresh();
            project_grid.render();
            project_grid.updateRowCount();
        }
    });

    $("#mybutton").click(function() {
        $("#status_grids").empty();
        selectedIndexes = project_grid.getSelectedRows();
        var i = 1;
        rpt_period = $(".rpt_period").val();

        $.each(selectedIndexes, function( index, value ) {
            var ship_chode = project_grid.getDataItem(value).code;
            var ship_name  = project_grid.getDataItem(value).project_name;
            //createDivs();
            if(i & 1)
            {
                openRowDivs();
                createRowElement(ship_chode, ship_name);
            }
            else
            {
                createRowElement(ship_chode,ship_name);
                closeRowDiv();
            }
            initGrid(ship_chode, "status_grid", ship_chode, rpt_period);
            i++;
        });

    });
})