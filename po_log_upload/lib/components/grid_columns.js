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
    function fundingFormatter(row, cell, value, columnDef, dataContext){
        if(value =="Not in MEAC"){
            return "<p  style='text-align: center; font-weight:bold; background-color: red;color: white '>"+value+"</p>";
        }
        else if (value=="In MEAC"){
            return "<p  style='text-align: center; font-weight:bold; background-color: limegreen;'>"+value+"</p>";
        }
        else{
            return "<p  style='text-align: center; font-weight:bold; background-color: yellow;'>"+value+"</p>";

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
    function weekFormatter(row, cell, value, columnDef, dataContext){
        if(value % 2 == 0)
        {
            return "<p  style='text-align: center; font-weight:bold; background-color: lightblue; '>"+value+"</p>";
        }
        else
        {
            return "<p  style='text-align: center; font-weight:bold; background-color: lightpink; '>"+value+"</p>";
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
            id       : "date",
            name     : "date",
            field    : "date"
        },{
            id       : "week",
            name     : "week",
            field    : "week",
            formatter : weekFormatter
        },{
            id       : "po",
            name     : "po",
            field    : "po",

        },{
            id       : "buyer",
            name     : "buyer",
            field    : "buyer",

        },{
            id       : "wp",
            name     : "wp",
            field    : "wp",

        },{
            id       : "swbs",
            name     : "swbs",
            field    : "swbs",

        },{
            id       : "item",
            name     : "item",
            field    : "item"
        },{
            id       : "val",
            name     : "val",
            field    : "val",
            formatter : myFormatterCurrency
        },{
            id       : "etc",
            name     : "etc",
            field    : "etc",
            formatter: myFormatterCurrency
        },{
            id       : "line",
            name     : "line",
            field    : "line"
        },{
            id       : "change",
            name     : "change",
            field    : "change",
            formatter : myFormatterCurrency

        },{
            id       : "qty",
            name     : "QTY",
            field    : "qty",
            formatter : myFormatter

        },{
            id       : "ebom",
            name     : "ebom",
            field    : "ebom",
            formatter : myFormatter

        },{
            id       : "remaining",
            name     : "remaining",
            field    : "remaining",
            formatter : myFormatterCurrency,

        },{
            id       : "cam",
            name     : "cam",
            field    : "cam"

        },{
            id       : "reason_for_change",
            name     : "reason_for_change",
            field    : "reason_for_change"
        },{
            id       : "funding_source",
            name     : "funding_source",
            field    : "funding_source",
            formatter : fundingFormatter
        }];
    return {
        cols   : cols
    };
})