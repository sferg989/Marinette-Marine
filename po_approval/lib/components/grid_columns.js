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
            if(value <0){
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

    function mycolorFormatter(row, cell, value, columnDef, dataContext) {
        var round = parseFloat(value).toFixed(0);
        if(round > 0){
            var link_paran = "<p style='font-weight:bold; background-color: red; color: white;'><a href='#' onclick=window.open('test_drill.html?ship_code="+dataContext.ship_code+"&test_id="+dataContext.test_step+"')>"+value+"</a></p>";
            //return "<p style='font-weight:bold; background-color: red; color: white;'>"+round+"</p>";
            return link_paran;

        }
        else{
            return "<p style='font-weight:bold; background-color: lime; color: black;'>"+round+"</p>";

        }
    }
    function myPCFormatter(row, cell, value, columnDef, dataContext) {
        var round = parseFloat(value).toFixed(0);
        return "<p style='font-weight:bold; '>"+round+"%</p>";


    }
    function myLinkFormatter(row, cell, value, columnDef, dataContext) {
        var round = parseFloat(value).toFixed(0);
        if(round > 0) {
            var link_paran = "<p style='font-weight:bold; background-color: red; color: white;'><a href='#' onclick=window.open('test_drill.html?ship_code="+dataContext.ship_code+"&wp="+dataContext.wp+"')>"+value+"</a></p>";
            return link_paran;
        }
        else{
            return "<p style='font-weight:bold; background-color: lime; color: black;'>"+round+"</p>";

        }

    }

    var cols = [
        {
            id       : "ship_code",
            name     : "HULL",
            field    : "ship_code"
        },{
            id       : "wp",
            name     : "WP",
            field    : "wp"
        },{
            id       : "item",
            name     : "item",
            field    : "item"
        },{
            id       : "desc",
            name     : "desc",
            field    : "desc"
        },{
            id       : "po",
            name     : "po",
            field    : "po"
        },{
            id       : "line",
            name     : "line",
            field    : "line"
        },{
            id       : "vendor",
            name     : "vendor",
            field    : "vendor"
        },{
            id       : "c_order_qty",
            name     : "c_order_qty",
            field    : "c_order_qty",
            formatter : myFormatter
        },{
            id       : "c_unit_price",
            name     : "c_unit_price",
            field    : "c_unit_price",
            formatter : myFormatterCurrency
        },{
            id       : "c_amnt",
            name     : "c_amnt",
            field    : "c_amnt",
            formatter : myFormatterCurrency
        },{
            id       : "meac_c_qty",
            name     : "meac_c_qty",
            field    : "meac_c_qty",
            formatter : myFormatter
        },{
            id       : "meac_ebom",
            name     : "meac_ebom",
            field    : "meac_ebom",
            formatter : myFormatter
        },{
            id       : "meac_var_ebom",
            name     : "meac_var_ebom",
            field    : "meac_var_ebom",
            formatter : myFormatter
        },{
            id       : "meac_last_price",
            name     : "meac_last_price",
            field    : "meac_last_price",
            formatter : myFormatterCurrency
        },{
            id       : "meac_re_est_etc",
            name     : "meac_re_est_etc",
            field    : "meac_re_est_etc",
            formatter : myFormatterCurrency
        },{
            id       : "meac_re_est_eac",
            name     : "meac_re_est_eac",
            field    : "meac_re_est_eac",
            formatter : myFormatterCurrency
        },{
            id       : "fortis_status",
            name     : "fortis_status",
            field    : "fortis_status"
        },{
            id       : "etc_diff",
            name     : "etc_diff",
            field    : "etc_diff",
            formatter : myFormatterCurrency
        }];
    return {
        cols   : cols
    };
})