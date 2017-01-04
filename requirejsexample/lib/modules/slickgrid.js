define(['slickgrid', 'slickdataview'], function(){
    var createSlickGrid = function(text) {
        console.log("hello from the other side" +text);
        var options = {
            enableCellNavigation: true,
            editable            : true,
            forceFitColumns     : true,
            autoHeight          : true,
            sort                : false,
            autoEdit            : true,
            asyncEditorLoading  : false
        };

        var columns = [];

        columns.push({
            id    : "project_name",
            name  : "Project Name",
            field : "project_name"
        },{
            id   : "code",
            name : "Ship Code",
            field: "code"
        });
        var url      = "processing_status.php";
        var dataView = new Slick.Data.DataView();
        project_grid = new Slick.Grid("#project_grid", dataView, columns, options);
        var columnpicker = new Slick.Controls.ColumnPicker(columns, project_grid, options);
        $.ajax({
            dataType: "json",
            url     : url,
            data: {
                control   : "project_grid",
                ship_code : '0473',
                rpt_period: 201611
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
    };
    return {
        test: createSlickGrid
    };
})/**
 * Created by fs11239 on 12/14/2016.
 */
