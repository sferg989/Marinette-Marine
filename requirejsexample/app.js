/**
 * Created by fs11239 on 12/14/2016.
 */

require.config({
    paths: {
        // jQuery & jQuery UI
        jquery       : "https://ajax.googleapis.com/ajax/libs/jquery/1.12.1/jquery.min",
        jqueryui     : '../inc/lib/js/SlickGrid-master/lib/jquery-ui-1.8.16.custom.min',
        dragevent    : '../inc/lib/js/SlickGrid-master/lib/jquery.event.drag-2.2',
        select2    : '../inc/lib/js/select2-4.0.3/dist/js/select2.full',
        //select2      : "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full",
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

require(['lib/modules/select2box'], function(testmod) {
    testmod.CreateEvent("this worked");
    testmod.createDIV();
    testmod.createFilter("filter_name","../monthly_processing/advance_calendar/advance_calendar.php");

});