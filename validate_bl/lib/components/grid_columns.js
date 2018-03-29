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
            id       : "rpt_period",
            name     : "rpt_period",
            field    : "rpt_period",
            width: 200
        },{
            id       : "ship_code",
            name     : "Program",
            field    : "ship_code",
            width: 200
        },{
            id   : "wp",
            name : "wp",
            field: "wp",
            width: 120
        },{
            id       : "prev_val",
            name     : "prev_val",
            field    : "prev_val",
            width: 120
        },{
            id       : "cur_val",
            name     : "cur_val",
            field    : "cur_val",
            width: 120
        },{
            id       : "diff",
            name     : "diff",
            field    : "diff",
            width: 65
        },{
            id       : "type",
            name     : "type",
            field    : "type",
            width: 65
        }];
    return {
        cols   : cols
    };
})