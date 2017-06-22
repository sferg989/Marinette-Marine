/**
 * Created by fs11239 on 12/14/2016.
 */

require.config({
    paths: {
        // jQuery & jQuery UI
        jquery       : "https://ajax.googleapis.com/ajax/libs/jquery/1.12.1/jquery.min",
        jqueryPlugin : "../inc/lib/js/jquery-migrate-1.4.1.min",
        googleCharts : "https://www.gstatic.com/charts/loader",
        jsCharts     : '../inc/lib/js/Chart',
        underscore   : '../inc/lib/js/underscore',
        jqueryui     : '../inc/lib/js/SlickGrid-master/lib/jquery-ui-1.8.16.custom.min',
        dragevent    : '../inc/lib/js/SlickGrid-master/lib/jquery.event.drag-2.2',
        dropevent    : '../inc/lib/js/SlickGrid-master/lib/jquery.event.drop-2.2',
        select2      : '../inc/lib/js/select2-4.0.3/dist/js/select2.full',
        bootstrapJS  : '../inc/lib/css/bootstrap/js/bootstrap',
        bootbox      : '../inc/lib/css/bootbox.min',
        orbJS       : '../inc/lib/JS/orbJS/orb',

        //select2      : "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full",
        // SlickGrid
        slickcore             : "../inc/lib/js/SlickGrid-master/slick.core",
        slickPager            : "../inc/lib/js/SlickGrid-master/controls/slick.pager",
        slickgrid             : "../inc/lib/js/SlickGrid-master/slick.grid",
        slickdataview         : "../inc/lib/js/SlickGrid-master/slick.dataview",
        slickCheckColumn      : "../inc/lib/js/SlickGrid-master/plugins/slick.checkboxselectcolumn",
        slickRowSelection     : "../inc/lib/js/SlickGrid-master/plugins/slick.rowselectionmodel",
        slickAutoToolTips     : "../inc/lib/js/SlickGrid-master/plugins/slick.autotooltips",
        slickFormatters       : "../inc/lib/js/SlickGrid-master/slick.formatters",
        slickEditors          : "../inc/lib/js/SlickGrid-master/slick.editors",
        firebugx              : "../inc/lib/js/SlickGrid-master/lib/firebugx",
        slickCellrangeSelector: "../inc/lib/js/SlickGrid-master/plugins/slick.cellrangeselector",
        slickColumnPicker     : "../inc/lib/js/SlickGrid-master/controls/slick.columnpicker",
        slickHeaderBtn        : "../inc/lib/js/SlickGrid-master/plugins/slick.headerbuttons"
        //totalDataView         : "../../inc/lib/js/slickgrid-totals-plugin-master/src/TotalsDataView",
        //totalPlugin         : "../../inc/lib/js/slickgrid-totals-plugin-master/src/TotalsPlugin"
    },
    shim: {
        jquery           : ['jqueryPlugin'],
        jquery           : {exports: '$'},
        underscore       : {exports: '_'},
        jqueryui         : ['jquery'],
        slickColumnPicker: ['jquery'],
        dragevent        : ['jquery'],
        dropevent        : ['jquery'],
        slickCheckColumn : ["slickgrid", 'slickColumnPicker'],
        slickPager       : ['jquery'],
        slickAutoToolTips: ['jquery'],
        slickEditors     : ['jquery'],
        slickRowSelection: ['jquery'],
        slickHeaderBtn   : ['jquery'],
        slickFormatters  : ['jquery'],
        bootstrapJS      : ['jquery'],
        bootbox          : ['bootstrapJS'],
        slickcore        : ['jqueryui'],
        slickgrid        : ['slickcore', 'dragevent', 'dropevent', 'underscore'],
        slickdataview    : ['slickgrid']
/*        totalDataView    : ['jquery'],
        totalPlugin     : ['jquery']*/
    }
});
