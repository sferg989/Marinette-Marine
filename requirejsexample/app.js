/**
 * Created by fs11239 on 12/14/2016.
 */

require.config({
    paths: {
        // jQuery & jQuery UI
        jquery       : "https://ajax.googleapis.com/ajax/libs/jquery/1.12.1/jquery.min",

        jqueryui     : '../inc/lib/js/SlickGrid-master/lib/jquery-ui-1.8.16.custom.min',
        dragevent    : '../inc/lib/js/SlickGrid-master/lib/jquery.event.drag-2.2',
        dropevent    : '../inc/lib/js/SlickGrid-master/lib/jquery.event.drop-2.2',
        select2      : '../inc/lib/js/select2-4.0.3/dist/js/select2.full',
        bootstrapJS  : '../inc/lib/css/bootstrap/js/bootstrap',
        bootbox      : '../inc/lib/css/bootbox.min',
        tether       : '../inc/lib/js/tether-1.3.3/dist/js/tether.min',
        //select2      : "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full",
        // SlickGrid
        slickcore             : "../inc/lib/js/SlickGrid-master/slick.core",
        slickgrid             : "../inc/lib/js/SlickGrid-master/slick.grid",
        slickdataview         : "../inc/lib/js/SlickGrid-master/slick.dataview",
        slickCheckColumn      : "../inc/lib/js/SlickGrid-master/plugins/slick.checkboxselectcolumn",
        slickRowSelection     : "../inc/lib/js/SlickGrid-master/plugins/slick.rowselectionmodel",
        slickFormatters       : "../inc/lib/js/SlickGrid-master/slick.formatters",
        slickEditors          : "../inc/lib/js/SlickGrid-master/slick.editors",
        firebugx              : "../inc/lib/js/SlickGrid-master/lib/firebugx",
        slickCellrangeSelector: "../inc/lib/js/SlickGrid-master/plugins/slick.cellrangeselector",
        slickColumnPicker: "../inc/lib/js/SlickGrid-master/controls/slick.columnpicker"
    },
    shim: {
        jquery          : {exports: '$'},
        jqueryui        : ['jquery'],
        dragevent       : ['jquery'],
        dropevent       : ['jquery'],
        slickCheckColumn: ['slickColumnPicker'],
        slickEditors    : ['jquery'],
        slickFormatters    : ['jquery'],
        bootstrapJS     : ['tether', 'jquery'],
        bootbox         : ["tether", 'bootstrapJS'],
        slickcore       : ['jqueryui'],
        slickgrid       : ['slickcore', 'dragevent', 'dropevent'],
        slickdataview   : ['slickgrid']
    }
});

require([
    'lib/modules/select2box',
    "lib/components/createSlickGrid",
    "lib/components/grid_options",
    "lib/components/grid_columns", "bootbox"], function(select2,grid,gridOptions, gridColumns, bootbox) {
    //select2.createFilter("ca");
    var grid1_columns = gridColumns.columnData;
    var grid1_options = gridOptions.gridOptions;

    var ajax_data_options        = {};
    ajax_data_options.control    = "status_grid";
    ajax_data_options.rpt_period = 201612;
    ajax_data_options.ship_code  = 0473;
    var url = "lib/php/requirejs.php";

    grid.createGrid("coolgrid",url, ajax_data_options ,grid1_columns, grid1_options);
    var grid_object = $("#coolgrid").val();
    grid_object.onCellChange .subscribe(function(e, args){
    var status     = args.item.step_status;
    var step_id    = args.item.id;
    var code       = args.item.code;
    var rpt_period = args.item.rpt_period;
    var comment_id = args.item.comment_id;
    var pfa_notes  = args.item.pfa_notes;

});
});