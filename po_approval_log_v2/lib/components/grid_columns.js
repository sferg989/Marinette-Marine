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
    function explanationFormatter(row, cell, value, columnDef, dataContext){

        if(value === "IN MEAC"){
            return "<p  style='text-align: center; font-weight:bold; background-color: limegreen;'>"+value+"</p>";
        }
        else if(value ==="HPR" ){
            return "<p  style='text-align: center; font-weight:bold; background-color: yellow;'>"+value+"</p>";
        }

        else if(value === "NOT IN MEAC"){
            return "<p  style='text-align: center; font-weight:bold; background-color: red; color: white'>"+value+"</p>";
        }

        else if(value === "Shock"){
            return "<p  style='text-align: center; font-weight:bold; background-color: yellow; '>"+value+"</p>";
        }
        else{
            return "<p  style='text-align: center; font-weight:bold; '>"+value+"</p>";

        }
    }
    function reasonForChangeFormatter(row, cell, value, columnDef, dataContext){
        var returnVal;

        switch(value) {
            case "EFDB Change":
            case "HPR":
            case "CLAIM":
            case "Shock":
            case "Rework":
            case "Vendor Claim":
            case "Certifications":
            case "Paint":
            case "ILS":
                return "<p  style='text-align: center; font-weight:bold; background-color: yellow;'>"+value+"</p>";
                break;
            case "Error":
            case "Price Increase":
                return "<p  style='text-align: center; font-weight:bold; background-color: red; color: white'>"+value+"</p>";
                break;
            case "Price Decrease":
            case "Cost Savings":
                return "<p  style='text-align: center; font-weight:bold; background-color: limegreen;'>"+value+"</p>";
                break;
            default:
                return "<p  style='text-align: center; font-weight:bold; '>"+value+"</p>";
        }
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
            id       : "wp",
            name     : "WP",
            field    : "wp",
            sortable: true,
            width: 200

        },{
            id       : "item",
            name     : "item",
            field    : "item",
            width: 200,
            sortable: true
        },{
            id       : "ecp_rea",
            name     : "ECP REA",
            field    : "ecp_rea",
            sortable: true,
            width: 120
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
        },{
            id       : "po",
            name     : "po",
            field    : "po",
            width: 65
        },{
            id       : "line",
            name     : "line",
            field    : "line",
            width: 65,
            sortable: true
        },{
            id       : "vendor",
            name     : "vendor",
            field    : "vendor",
            width: 250,
            sortable: true
        },{
            id       : "NEW PO qty",
            name     : "ORDER QTY",
            field    : "order_qty",
            formatter : myFormatter,
            width: 65,
            sortable: true
        },{
            id       : "c_unit_price",
            name     : "Unit Price",
            field    : "c_unit_price",
            formatter : myFormatterCurrency,
            width: 80,
            sortable: true
        },{
            id       : "c_amnt",
            name     : "ORDER $",
            field    : "c_amnt",
            formatter : myFormatterCurrency,
            width: 80,
            sortable: true
        },{
            id       : "ebom",
            name     : "EBOM",
            field    : "ebom",
            formatter : myFormatter,
            width: 65,
            sortable: true
        },{
            id       : "c_qty",
            name     : "QTY (w TRANS)",
            field    : "c_qty",
            formatter : myFormatter,
            width: 80,
            sortable: true
        },{
            id       : "meac_re_est_etc",
            name     : "Target",
            field    : "meac_re_est_etc",
            formatter : myFormatterCurrencyReEST,
            width: 80,
            sortable: true
        },{
            id       : "etc_diff",
            name     : "DIFF",
            field    : "etc_diff",
            formatter : myFormatterCurrencyDIFF,
            width: 80,
            sortable: true
        }, {
            id      : "reason_for_change",
            name    : "Reason",
            field   : "reason_for_change",
            width: 250,
            formatter : reasonForChangeFormatter,
            editor  : Slick.Editors.Text,
            sortable: true
        }, {
            id      : "explanation",
            name    : "Explanation",
            field   : "explanation",
            width: 250,
            formatter : explanationFormatter,
            editor  : Slick.Editors.Text,
            sortable: true
        }, {
            id      : "other_notes",
            name    : "OTHER NOTES",
            field   : "other_notes",
            width: 250,
            formatter : explanationFormatter,
            editor  : Slick.Editors.Text,
            sortable: true
        }];
    return {
        cols   : cols
    };
})