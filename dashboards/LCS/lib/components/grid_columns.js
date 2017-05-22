/**
 * Created by fs11239 on 2/23/2017.
 */
define(["slickFormatters","slickEditors", "slickcore"],function(){

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
    function myCurrencyFormatter(row, cell, value, columnDef, dataContext) {
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

    var lcs_cols =
        [{
            id      : "Hull",
            id      : "Hull",
            name    : "Hull",
            minWidth: 300,
            maxWidth: 400,
            field   : "Hull"
        },{
            id       : "s",
            minWidth : 150,
            maxWidth : 400,
            name     : "BCWS",
            formatter: myCurrencyFormatter,
            field    : "s"
        }, {
            id       : "p",
            minWidth : 150,
            maxWidth : 400,
            formatter: myCurrencyFormatter,
            name     : "BCWP",
            field    : "p"
        }, {
            id       : "a",
            name     : "Acwp",
            minWidth : 150,
            maxWidth : 400,
            formatter: myCurrencyFormatter,
            field    : "a"
        },{
            id       : "bac",
            minWidth : 150,
            maxWidth : 400,
            formatter: myCurrencyFormatter,
            name     : "BAC",
            field    : "bac"
        }, {
            id       : "eac",
            minWidth : 150,
            maxWidth : 400,
            formatter: myCurrencyFormatter,
            name     : "EAC",
            field    : "eac"
        }, {
            id       : "sv",
            minWidth : 150,
            maxWidth : 400,
            formatter: myCurrencyFormatter,
            name     : "SV",
            field    : "sv"
        },{
            id       : "cv",
            minWidth : 150,
            maxWidth : 400,
            formatter: myCurrencyFormatter,
            name     : "CV",
            field    : "cv"
        },{
            id      : "ub",
            minWidth: 150,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            name    : "UB",
            field   : "ub"
        },{
            id      : "mr",
            minWidth: 150,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            name    : "MR",
            field   : "mr"
        }];

    return {
        dataCols      : lcs_cols,
        addCommas : addCommas
    };
})