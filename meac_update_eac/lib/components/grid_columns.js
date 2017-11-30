/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
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
    function deltaCurrencyFormatter(row, cell, value, columnDef, dataContext) {
        var round, withcommas;
        if(dataContext.item=="TOTAL DIFF "){
            return "<p></p>";
        }
            if(value >0){
                round = parseFloat(value).toFixed(2);
                withcommas = addCommas(round);
                return "<p  style='text-align: right; font-weight:bold; background-color: red;color: white '>$"+withcommas+"</p>";
            }
            else if(value <0){
                round = parseFloat(value).toFixed(2);
                withcommas = addCommas(round);
                return "<p  style='text-align: right; font-weight:bold; background-color: limegreen; color: white '>$"+withcommas+"</p>";

            }
            else if(value ==0){
                round = parseFloat(value).toFixed(2);
                withcommas = addCommas(round);
                return "<p class='showPosBlack'>$" + withcommas+"</p>";

            }

    }

    function myFormatterCurrency(row, cell, value, columnDef, dataContext) {
        var round, withcommas;
        if(dataContext.item=="TOTAL DIFF "){
            return "<p></p>";
        }
        if(value <0){
            round = parseFloat(value).toFixed(2);
            withcommas = addCommas(round);
            return "<p class='showNegRed'>($" + withcommas+")</p>";
        }
        else{
            round = parseFloat(value).toFixed(2);
            withcommas = addCommas(round);
            return "<p class='showPosBlack'>$" + withcommas+"</p>";
        }

    }
    function shipFormatter(row, cell, value, columnDef, dataContext){
        return "<p  style='text-align: center; font-weight:bold;'>" + value+"</p>";
    }
    var cols = [
        {
            id      : "ship_code",
            name    : "HULL",
            field   : "ship_code",
            formatter : shipFormatter
        },{
            id       : "wp",
            name     : "wp",
            field    : "wp"

        },{
            id       : "prev_eac",
            name     : "PREV EAC",
            field    : "prev_eac",
            formatter : myFormatterCurrency

        },{
            id       : "new_eac",
            name     : "Proposed EAC",
            field    : "new_eac",
            formatter : myFormatterCurrency

        },{
            id       : "delta",
            name     : "DELTA",
            field    : "delta",
            formatter : deltaCurrencyFormatter

        }];
    return {
        cols   : cols
    };
})