/**
 * Created by fs11239 on 2/23/2017.
 */
define([],function(){
    function addCommas(nStr)
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
            var absval = Math.abs(value)
            var withcommas = addCommas(absval);
            return "<p class='showNegRed'>$(" + withcommas+")</p>";
        }
        else{
            var withcommas = addCommas(value);
            return "<p class='showPosBlack'>$ " + withcommas+"</p>";
        }
    }
    function myNumberFormatter(row, cell, value, columnDef, dataContext){
        if(value <0){
            var absval = Math.abs(value)
            var withcommas = addCommas(absval);
            return "<p class='showNegRed'>(" + withcommas+")</p>";
        }
        else{
            var withcommas = addCommas(value);
            return "<p class='showPosBlack'>" + withcommas+"</p>";
        }
    }
    cols = [
       {
           id      : "ca",
           name    : "CA",
           minWidth: 60,
           maxWidth: 400,
           field   : "ca"
        },{
            id      : "wp",
            name    : "WP",
            minWidth: 30,
            maxWidth: 40,
            field   : "wp"
        },{
            id       : "prevh",
            formatter: myNumberFormatter,
            name     : "Prev H",
            minWidth: 60,
            maxWidth: 120,
            field    : "prevh"
        },{
            id       : "curh",
            name     : "CUR H",
            minWidth: 60,
            maxWidth: 180,
            formatter: myNumberFormatter,
            field    : "curh"
        },{
            id       : "change_h",
            formatter: myNumberFormatter,
            name     : "Hours Delta",
            minWidth: 60,
            maxWidth: 180,
            field    : "change_h"
        },{
            id      : "prevbac",
            name    : "Prev $",
            minWidth: 60,
            maxWidth: 180,
            formatter: myCurrencyFormatter,
            field   : "prevbac"
        },{
            id      : "curbac",
            minWidth: 60,
            maxWidth: 180,
            formatter: myCurrencyFormatter,
            name    : "Cur $",
            field : "curbac"
        },{
            id      : "change_d",
            minWidth: 60,
            maxWidth: 180,
            formatter: myCurrencyFormatter,
            name    : "$ Delta ",
            field   : "change_d"
        },{
            id      : "desc",
            minWidth: 60,
            maxWidth: 180,
            name    : "BCR",
            field   : "desc"
        },{
            id      : "bcrh",
            minWidth: 60,
            maxWidth: 180,
            formatter: myNumberFormatter,
            name    : "BCR H",
            field   : "bcrh",
            hasTotal: true
        },{
            id      : "bcrd",
            minWidth: 60,
            maxWidth: 180,
            formatter: myCurrencyFormatter,
            name    : "BCR $",
            field   : "bcrd",
            hasTotal: true
        },{
            id      : "bcrh_change",
            minWidth: 60,
            maxWidth: 180,
            formatter: myNumberFormatter,
            name    : "BCR H DIFF",
            field   : "bcrh_change"
        },{
            id      : "bcrd_change",
            minWidth: 60,
            maxWidth: 180,
            formatter: myCurrencyFormatter,
            name    : "BCR $ DIFF",
            field   : "bcrd_change"
        }];
    return {
        cols : cols
    };
})