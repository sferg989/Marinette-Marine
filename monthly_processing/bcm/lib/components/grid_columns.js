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
            var absval = Math.abs(value).toFixed(2)
            var withcommas = addCommas(absval);
            return "<p class='showNegRed'>$(" + withcommas+")</p>";
        }
        else{

            var withcommas = addCommas(Math.round(parseFloat(value)*100)/100);
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
    function sumTotalsFormatter(totals, columnDef) {
        //var val = dataContext.curbac;
        var val = totals.sum[columnDef.field];
        if (val != null) {
            return "total: " + ((addCommas(Math.round(parseFloat(val)*100)/100)));
        }
        return "";
    }
    cols = [
        {
            id      : "desc",
            minWidth: 125,
            maxWidth: 180,
            name    : "BCR",
            field   : "desc"
        },{
            id      : "ca",
            name    : "CA",
            minWidth: 125,
            maxWidth: 400,
            field   : "ca"
        },{
            id      : "wp",
            name    : "WP",
            minWidth: 40,
            maxWidth: 400,
            field   : "wp"
        },{
            id                  : "prevh",
            formatter           : myNumberFormatter,
            name                : "Prev H",
            minWidth            : 15,
            maxWidth            : 120,
            field               : "prevh"
        },{
            id                  : "curh",
            name                : "CUR H",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myNumberFormatter,
            field               : "curh"
        },{
            id                  : "change_h",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myNumberFormatter,
            groupTotalsFormatter: sumTotalsFormatter,

            name                : "Hours Delta",
            minWidth            : 15,
            maxWidth            : 180,
            field               : "change_h"
        },{
            id                  : "prevbac",
            name                : "Prev $",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myNumberFormatter,
            field               : "prevbac"
        },{
            id                  : "curbac",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myCurrencyFormatter,
            name                : "Cur $",
            field               : "curbac"
        },{
            id                  : "change_d",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myCurrencyFormatter,
            name                : "$ Delta ",
            groupTotalsFormatter: sumTotalsFormatter,
            field               : "change_d"
        },{
            id                  : "bcrh",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myNumberFormatter,
            groupTotalsFormatter: sumTotalsFormatter,
            name                : "BCR H",
            field               : "bcrh"
        },{
            id                  : "bcrd",
            minWidth            : 15,
            maxWidth            : 180,
            groupTotalsFormatter: sumTotalsFormatter,
            formatter           : myCurrencyFormatter,
            name                : "BCR $",
            field               : "bcrd"
        },{
            id                  : "bcrh_change",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myNumberFormatter,
            groupTotalsFormatter: sumTotalsFormatter,
            name                : "BCR H DIFF",
            field               : "bcrh_change"
        },{
            id                  : "bcrd_change",
            minWidth            : 15,
            maxWidth            : 180,
            formatter           : myCurrencyFormatter,
            groupTotalsFormatter: sumTotalsFormatter,
            name : "BCR $ DIFF",
            field: "bcrd_change"
        }];
    bcr_cols = [
        {
            id                  : "bcr",
            name                : "BCR",
            field               : "bcr"
        },{
            id                  : "change",
            name                : "Sum of Change",
            formatter           : myCurrencyFormatter,
            field               : "change"
        },{
            id                  : "db",
            name                : "DB",
            field               : "db",
            formatter           : myCurrencyFormatter

        },{
            id                  : "fortis_db",
            name                : "Fortis DB",
            field               : "fortis_db",
            formatter           : myCurrencyFormatter

        },{
            id                  : "mr",
            name                : "Planned MR Draw- Fortis Value",
            field               : "mr",
            formatter           : myCurrencyFormatter
        },{
            id                  : "ub",
            name                : "Integrated UB",
            field               : "ub",
            formatter           : myCurrencyFormatter
        },{
            id                  : "fortis_ub",
            name                : "Fortis UB",
            field               : "fortis_ub",
            formatter           : myCurrencyFormatter
        },{
            id                  : "bcr_delta",
            formatter           : myCurrencyFormatter,
            name                : "RED LINE VALUE",
            field               : "bcr_delta"
        }];
    logExcelCols= [
        {
            id                  : "rpt_period",
            name                : "rpt_period",
            field               : "rpt_period",
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "bcr",
            name                : "bcr",
            field               : "bcr",
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "pcw",
            name                : "pcw",
            field               : "pcw",
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "mod",
            name                : "mod",
            field               : "mod",
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "desc",
            name                : "desc",
            field               : "desc",
            minWidth            : 200,
            maxWidth            : 400
        },{
            id                  : "auw",
            name                : "auw",
            field               : "auw",
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "auw_fee",
            name                : "auw_fee",
            field               : "auw_fee",
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "db",
            name                : "db",
            field               : "db",
            formatter           : myCurrencyFormatter,
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "mr",
            name                : "mr",
            field               : "mr",
            formatter           : myCurrencyFormatter,
            minWidth            : 15,
            maxWidth            : 120
        },{
            id                  : "ub",
            name                : "ub",
            field               : "ub",
            formatter           : myCurrencyFormatter,
            minWidth            : 15,
            maxWidth            : 120
        }];
    return {
        cols        : cols,
        bcr_cols    : bcr_cols,
        logExcelCols: logExcelCols
    };
})