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
        if(dataContext.data_type=="hours"){
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
        else{
            if(value <0){
                var absval = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showNegRed'>$(" + withcommas+")</p>";
            }
            else{
                var withcommas = addCommas(value.toFixed(2));
                return "<p class='showPosBlack'>$ " + withcommas+"</p>";
            }
        }

    }


    var cols = [
       {
            id       : "lcs17",
            name     : "LCS 17",
            formatter: myFormatter,
            field    : "lcs17"
        },{
            id       : "lcs19",
            name     : "LCS 19",
            formatter: myFormatter,
            field    : "lcs19"
        },{
            id       : "lcs21",
            name     : "LCS 21",
            formatter: myFormatter,
            field    : "lcs21"
        },{
            id       : "lcs23",
            name     : "LCS 23",
            formatter: myFormatter,
            field    : "lcs23"
        },{
            id       : "lcs25",
            name     : "LCS 25",
            formatter: myFormatter,
            field    : "lcs25"
        }];
    return {
        cols   : cols
    };
})