/**
 * Created by fs11239 on 12/14/2016.
 */

require.config({
    paths: {
        // jQuery & jQuery UI
        jquery   : "https://ajax.googleapis.com/ajax/libs/jquery/1.12.1/jquery.min",
        jqueryui : '../inc/lib/js/SlickGrid-master/lib/jquery-ui-1.8.16.custom.min',
        dragevent: '../inc/lib/js/SlickGrid-master/lib/jquery.event.drag-2.2',
        dropevent: '../inc/lib/js/SlickGrid-master/lib/jquery.event.drop-2.2',
        select2 : "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full",
        // SlickGrid
        slickcore    : "../inc/lib/js/SlickGrid-master/slick.core",
        slickgrid    : "../inc/lib/js/SlickGrid-master/slick.grid",
        slickdataview: "../inc/lib/js/SlickGrid-master/slick.dataview"
    },
    shim: {
        jquery       : {exports: '$'},
        jqueryui     : ['jquery'],
        dragevent    : ['jquery'],
        dropevent    : ['jquery'],
        slickcore    : ['jqueryui'],
        slickgrid    : ['slickcore', 'dragevent', 'dropevent'],
        slickdataview: ['slickgrid']
    }
});

require(['lib/modules/template','lib/modules/slick2'], function(template,slickgrid) {
    //console.log("sure did");
    template.test(" yes this worked");
    //slickgrid.createFilterBox("project");
    slickgrid.createFilterBox("project","../dashboards/main_ev/dashboard_filter.php");

});