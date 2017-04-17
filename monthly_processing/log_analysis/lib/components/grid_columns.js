/**
 * Created by fs11239 on 2/23/2017.
 */
define(["slickFormatters","slickEditors", "slickcore"],function(){
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
    log_analysis_cols= [
       {
           id      : "bcr",
           name    : "BCR",
           minWidth: 30,
           maxWidth: 40,
           field   : "bcr"
        },{
            id      : "pcw",
            minWidth: 30,
            maxWidth: 40,
            name    : "PCW",
            field   : "pcw"
        },{
            id      : "auth_no",
            minWidth: 50,
            maxWidth: 75,
            name    : "Contract MOD",
            field : "auth_no"
        },{
            id      : "justification",
            minWidth: 330,
            maxWidth: 1840,
            name    : "Change Description",
            field   : "justification"
        },{
            id      : "db",
            minWidth: 50,
            maxWidth: 150,
            formatter: myCurrencyFormatter,
            name    : "db",

            field : "db"
        },{
            id       : "mr",
            minWidth : 50,
            maxWidth : 150,
            formatter: myCurrencyFormatter,
            name     : "mr",
            field    : "mr"
        },{
            id      : "ub",
            minWidth: 50,
            maxWidth: 150,
            formatter: myCurrencyFormatter,
            name    : "ub",
            field   : "ub"
        },/*{
            id    : "neg_cost",
            name  : "neg_cost",
            field : "neg_cost"
        },{
            id    : "neg_fee",
            name  : "neg_fee",
            field : "neg_fee"
        },{
            id    : "bcr_db",
            name  : "bcr_db",
            field : "bcr_db"
        },{
            id    : "bcr_db_diff",
            name  : "bcr_db_diff",
            field : "change_no"
        },{
            id    : "type",
            name  : "type",
            field : "type"
        },{
            id    : "bcr_ub",
            name  : "bcr_ub",
            field : "bcr_ub"
        },{
            id    : "ub_diff",
            name  : "ub_diff",
            field : "ub_diff"
        }*/];
    return {
        log_analysis : log_analysis_cols
    };
})