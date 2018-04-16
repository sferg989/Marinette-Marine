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
    function myFormatterCurrencyDIFF(row, cell, value, columnDef, dataContext) {
            if(value <0){
                var round = parseFloat(value).toFixed(2);
                var withcommas = addCommas(round);
                return "<p  style='text-align: right; font-weight:bold; background-color: limegreen; '>($" + withcommas+")</p>";
            }
            else{
                var round = parseFloat(value).toFixed(2);
                var withcommas = addCommas(round);
                return "<p  style='text-align: right; font-weight:bold; background-color: red;color: white '>$" + withcommas+"</p>";
            }

    }
    function myFormatterCurrencyReEST(row, cell, value, columnDef, dataContext) {
        if(dataContext.item=="TOTAL DIFF "){
            return "<p></p>";
        }
                var round = parseFloat(value).toFixed(2);
                var withcommas = addCommas(round);
                return "<p  style='text-align: right; font-weight:bold; background-color: yellow; '>$" + withcommas+"</p>";
    }
    var cols = [
        {
            id      : "ship_code",
            name    : "HULL",
            minWidth: 20,
            maxWidth: 30,
            field   : "ship_code"
        },{
            id       : "wp",
            name     : "WP",
            field    : "wp",
            minWidth: 80,
            maxWidth: 120
        },{
            id       : "item",
            name     : "item",
            field    : "item",
            minWidth: 80,
            maxWidth: 130
        },{
            id       : "desc",
            name     : "desc",
            field    : "desc",
            minWidth: 120,
            maxWidth: 400
        },{
            id       : "po",
            name     : "po",
            field    : "po",
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "line",
            name     : "line",
            field    : "line",
            minWidth: 10,
            maxWidth: 30
        },{
            id       : "vendor",
            name     : "vendor",
            field    : "vendor",
            minWidth: 120,
            maxWidth: 200
        },{
            id       : "NEW PO qty",
            name     : "C QTY",
            field    : "c_order_qty",
            formatter : myFormatter,
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "c_unit_price",
            name     : "C Unit Price",
            field    : "c_unit_price",
            formatter : myFormatterCurrency,
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "c_amnt",
            name     : "C AMT",
            field    : "c_amnt",
            formatter : myFormatterCurrency,
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "meac_c_qty",
            name     : "MEAC QTY",
            field    : "meac_c_qty",
            formatter : myFormatter,
            minWidth: 10,
            maxWidth: 30
        },{
            id       : "meac_ebom",
            name     : "EBOM",
            field    : "meac_ebom",
            formatter : myFormatter,
            minWidth: 10,
            maxWidth: 30
        },{
            id       : "meac_var_ebom",
            name     : "VAR",
            field    : "meac_var_ebom",
            formatter : myFormatter,
            minWidth: 10,
            maxWidth: 30
        },{
            id       : "meac_last_price",
            name     : "Last price",
            field    : "meac_last_price",
            formatter : myFormatterCurrency,
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "meac_re_est_etc",
            name     : "RE EST ETC",
            field    : "meac_re_est_etc",
            formatter : myFormatterCurrencyReEST,
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "meac_re_est_eac",
            name     : "RE EST EAC",
            field    : "meac_re_est_eac",
            formatter : myFormatterCurrency,
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "fortis_status",
            name     : "fortis_status",
            field    : "fortis_status",
            minWidth: 40,
            maxWidth: 80
        },{
            id       : "etc_diff",
            name     : "etc_diff",
            field    : "etc_diff",
            formatter : myFormatterCurrencyDIFF,
            minWidth: 40,
            maxWidth: 80
        }];
    return {
        cols   : cols
    };
})