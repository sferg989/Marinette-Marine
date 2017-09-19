/**
 * Created by fs11239 on 2/23/2017.
 */
define(["./data"],function(dataRepo){
    var addCommas = function (nStr)
    {
        nStr += '';
        var x = nStr.split('.');
        var x1 = x[0];
        var x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }
    function myFormatter(row, cell, value, columnDef, dataContext) {
            if(value <0){
                var absval = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showNegRed'>(" + withcommas+")</p>";
            }
            else{
                var withcommas = addCommas(value.toFixed(2));
                return "<p class='showPosBlack'>" + withcommas+"</p>";
            }

    }

    function mycolorFormatter(row, cell, value, columnDef, dataContext) {
        var round = parseFloat(value).toFixed(0);
        if(round > 0){
            var link_paran = "<p style='font-weight:bold; background-color: red; color: white;'><a href='#' onclick=window.open('test_drill.html?ship_code="+dataContext.ship_code+"&test_id="+dataContext.test_step+"')>"+value+"</a></p>";
            //return "<p style='font-weight:bold; background-color: red; color: white;'>"+round+"</p>";
            return link_paran;

        }
        else{
            return "<p style='font-weight:bold; background-color: lime; color: black;'>"+round+"</p>";

        }
    }
    function myPCFormatter(row, cell, value, columnDef, dataContext) {
        var round = parseFloat(value).toFixed(0);
        return "<p style='font-weight:bold; '>"+round+"%</p>";


    }
    function myLinkFormatter(row, cell, value, columnDef, dataContext) {
        var round = parseFloat(value).toFixed(0);
        if(round > 0) {
            var link_paran = "<p style='font-weight:bold; background-color: red; color: white;'><a href='#' onclick=window.open('test_drill.html?ship_code="+dataContext.ship_code+"&wp="+dataContext.wp+"')>"+value+"</a></p>";
            return link_paran;
        }
        else{
            return "<p style='font-weight:bold; background-color: lime; color: black;'>"+round+"</p>";

        }

    }

    var cols = [
        {
            id       : "test_step",
            name     : "TEST STEPS",
            field    : "test_step"
        },{
            id       : "threshold",
            name     : "THRESHOLD",
            field    : "threshold"
        },{
            id       : "count",
            name     : "count",
            formatter: myLinkFormatter,
            field    : "count"
        }];
    var drill_cols = [
        {
            id       : "ship_code",
            name     : "HULL",
            field    : "ship_code"
        },{
            id       : "wp",
            name     : "WP",
            field    : "wp"
        },{
            id       : "p6_pc",
            name     : "P6 % Complete",
            formatter : myPCFormatter,
            field    : "p6_pc"
        },{
            id       : "c_pc",
            name     : "Cobra % Complete",
            formatter : myPCFormatter,
            field    : "c_pc"
        },{
            id       : "result",
            name     : "result",
            formatter:mycolorFormatter,
            field    : "result"
        }];
    return {
        cols   : cols,
        drill_cols: drill_cols
    };
})