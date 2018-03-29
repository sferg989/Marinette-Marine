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
    function countFormatter(row, cell, value, columnDef, dataContext) {
        return "<p  style='text-align: right; font-weight:bold;'>" + value+"</p>";
    }

    function myFormatterCurrencyDIFF(row, cell, value, columnDef, dataContext) {
        if(value >0){
            var round = parseFloat(value).toFixed(2);
            var withcommas = addCommas(round);
            return "<p  style='text-align: right; font-weight:bold; background-color: limegreen; '>$" + withcommas+"</p>";
        }
        else{
            var round = parseFloat(value).toFixed(2);
            var withcommas = addCommas(round);
            return "<p  style='text-align: right; font-weight:bold; background-color: red;color: white '>$" + withcommas+"</p>";
        }

    }
    var cols = [
        {
            id       : "total_spend",
            name     : "total_spend",
            field    : "total_spend",
            formatter : myFormatterCurrency
        },{
            id       : "total_budget",
            name     : "total_budget",
            field    : "total_budget",
            formatter : myFormatterCurrency

        },{
            id       : "variance",
            name     : "variance",
            field    : "variance",
            formatter : myFormatterCurrencyDIFF

        },{
            id       : "po_count",
            name     : "po_count",
            field    : "po_count",
            formatter : countFormatter
        },{
            id       : "addeddum_count",
            name     : "addeddum_count",
            field    : "addeddum_count",
            formatter : countFormatter
        }];
    return {
        cols   : cols
    };
})