/**
 * Created by fs11239 on 2/23/2017.
 */
define(["slickEditors"],function(){
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
        if(dataContext.item=="TOTAL DIFF "){
            return "<p></p>";
        }
        else if(value <0){
                var round = parseFloat(value).toFixed(0);
                var withcommas = addCommas(round);
                return "<p class='showNegRed'>(" + withcommas+")</p>";
            }
            else{
                var round = parseFloat(value).toFixed(0);
                var withcommas = addCommas(round);
                return "<p class='showPosBlack'>" + withcommas+"</p>";
            }

    }
    function poFormatter(row, cell, value, columnDef, dataContext){
        var prev_row = row-1;

        if(value % 2 == 0)
        {
            return "<p  style='text-align: center; font-weight:bold; background-color: lightblue; '>"+value+"</p>";
        }
        else
        {
            return "<p  style='text-align: center; font-weight:bold; background-color: lightpink; '>"+value+"</p>";
        }
    }
    function myFormatterCurrency(row, cell, value, columnDef, dataContext) {
        if(dataContext.item=="TOTAL DIFF "){
            return "<p></p>";
        }
            if(value <0){
                var round = parseFloat(value).toFixed(2);
                var withcommas = addCommas(round);
                return "<p class='showNegRed'>($" + withcommas+")</p>";
            }
            else{
                var round = parseFloat(value).toFixed(2);
                var withcommas = addCommas(round);
                return "<p class='showPosBlack'>$" + withcommas+"</p>";
            }

    }
    var cols = [
        {
            id       : "po",
            name     : "po",
            field    : "po",
            width    : 65,
            formatter: poFormatter
        },{
            id       : "line",
            name     : "line",
            field    : "line",
            width: 65
        },{
            id       : "wp",
            name     : "WP",
            field    : "wp",
            width: 200

        },{
            id       : "item",
            name     : "item",
            field    : "item",
            width: 200
        },{
            id       : "desc",
            name     : "desc",
            field    : "desc",
            width: 120
        },{
            id       : "buyer",
            name     : "buyer",
            field    : "buyer",
            width: 120
        },];
    return {
        cols   : cols
    };
})