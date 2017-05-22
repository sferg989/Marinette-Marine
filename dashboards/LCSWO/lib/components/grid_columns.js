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
            if(value==undefined){
                return "<p></p>";
            }
            var withcommas = addCommas(value.toFixed(2));
            return "<p class='showPosBlack'>$ " + withcommas+"</p>";
        }
    }
    function myFormatterDollarsAndHours(row, cell, value, columnDef, dataContext) {

        if(dataContext.data_type=="dollars"){
            if(value <0){
                var absval = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showNegRed'>$(" + withcommas+")</p>";
            }
            else{

                var absval     = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showPosBlack'>$ " + withcommas+"</p>";
            }
        }
        else {
            if(value <0){
                var absval = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showNegRed'>(" + withcommas+")</p>";
            }
            else{
                var absval     = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showPosBlack'>" + withcommas+"</p>";
            }
        }

    }
    function myPercentBarPC(row, cell, value, columnDef, dataContext) {
        if (value == null || value === "") {
            return "";
        }

        var bkground = dataContext.spi_color;
        var font = dataContext.spi_font;
        var bar = "<span class='percent-complete-bar' style='font-weight:bold; background:" + bkground + ";width:" + value + "%'>" +
            "<div></div>" +
            "%"+value+"</span>"
        return bar;
    }
    function myPercentBarPS(row, cell, value, columnDef, dataContext) {
        if (value == null || value === "") {
            return "";
        }

        var bkground = dataContext.cpi_color;
        var font = dataContext.cpi_font;
        var bar = "<span class='percent-complete-bar' style='font-weight:bold; background:" + bkground + ";width:" + value + "%'>" +
            "<div></div>" +
            "%"+value+"</span>"
        return bar;
    }
    function cellSPIFormatter(row, cell, value, columnDef, dataContext) {
        var bkgrnd = dataContext.spi_color;
        var font = dataContext.spi_font;
        var round = parseFloat(value).toFixed(2);
        return "<p style='font-weight:bold; background-color:"+bkgrnd+"; color: "+font+";'>"+round+"</p>";
    }
    function cellCPIFormatter(row, cell, value, columnDef, dataContext) {
        var bkgrnd = dataContext.cpi_color;
        var font = dataContext.cpi_font;
        var round = parseFloat(value).toFixed(2);
        return "<p style='font-weight:bold; background-color:"+bkgrnd+"; color: "+font+";'>"+round+"</p>";
    }
    function cellTCPIFormatter(row, cell, value, columnDef, dataContext) {
        var bkgrnd = dataContext.tcpi_color;
        var font = dataContext.tcpi_font;
        var round = parseFloat(value).toFixed(2);
        return "<p style='font-weight:bold; background-color:"+bkgrnd+";color: "+font+";'>"+round+"</p>";
    }
    function cellFormatter(row, cell, value, columnDef, dataContext) {
        var bkgrnd = dataContext.eac_color;
        var font = dataContext.eac_font;
        var round = parseFloat(value).toFixed(2);
        var withcommas = addCommas(round);
        return "<p style='font-weight:bold; background-color:"+bkgrnd+"; color: "+font+";'>$"+withcommas+"</p>";

    }
    function myhourFormatter(row, cell, value, columnDef, dataContext) {
        if(value <0){
            var absval = Math.abs(value).toFixed(2);
            var withcommas = addCommas(absval);
            return "<p class='showNegRed'>(" + withcommas+")</p>";
        }
        else{
            if(value==undefined){
                return "<p></p>";
            }
            var withcommas = addCommas(value.toFixed(2));
            return "<p class='showPosBlack'> " + withcommas+"</p>";
        }
    }
    function sumTotalsFormatter(totals, columnDef) {
        var val = totals.sum && totals.sum[columnDef.field];
        if (val != null) {
            return "total: " + ((addCommas(Math.round(parseFloat(val)*100)/100)));
        }
        return "";
    }
    function updateColumnHeaders(grid, top5Type)
    {
        var top5_cols, valCol, cols;
        top5_cols = [{
            id      : "ca",
            name    : "CA",
            field   : "ca"
        },{
            id       : "cam",
            name     : "CAM",
            field    : "cam"
        }];
        valCol = returnColVal(top5Type);
        cols = top5_cols.push(valCol[0]);
        grid.setColumns(top5_cols);
    }
    function returnColVal(top5Type){
        var colVal;
        switch(top5Type) {
            case "cv_cum_fav"  :
            case "cv_cum_unfav":
                colVal =
                    [{
                        id       : "val",
                        name     : "CUM CV",
                        formatter: myFormatterDollarsAndHours,
                        field    : "val"
                    }]
                break;
            case "sv_cum_unfav":
            case "sv_cum_fav":
                colVal =
                    [{
                        id       : "val",
                        name     : "CUM SV",
                        formatter: myFormatterDollarsAndHours,
                        field    : "val"
                    }]
                break;
            case "cv_cur_unfav":
            case "cv_cur_fav":
                colVal =
                    [{
                        id       : "val",
                        name     : "CUR CV",
                        formatter: myFormatterDollarsAndHours,
                        field    : "val"
                    }]
                break;
            case "sv_cur_unfav":
            case "sv_cur_fav":
                colVal =
                    [{
                        id       : "val",
                        name     : "CUR SV",
                        formatter: myFormatterDollarsAndHours,
                        field    : "val"
                    }]
                break;
        }
        return colVal;
    }
    var lcs_cols =
            [{
                id      : "Hull",
                id      : "Hull",
                name    : "Hull",
                minWidth: 150,
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
                formatter: cellFormatter,
                name     : "EAC",
                field    : "eac"
            }, {
                id       : "spi",
                minWidth : 150,
                maxWidth : 400,
                formatter: cellSPIFormatter,
                name     : "SPI",
                field    : "spi"
            }, {
                id       : "cpi",
                minWidth : 150,
                maxWidth : 400,
                formatter: cellCPIFormatter,
                name     : "CPI",
                field    : "cpi"
            }, {
                id       : "tcpi",
                minWidth : 150,
                maxWidth : 400,
                formatter: cellTCPIFormatter,
                name     : "TCPI",
                field    : "tcpi"
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
            },{
                id      : "pc",
                formatter: myPercentBarPC,
                name    : "% Complete",
                minWidth: 150,
                maxWidth: 400,
                field   : "pc"
            },{
                id      : "ps",
                formatter: myPercentBarPS,
                name    : "% Spent",
                minWidth: 150,
                maxWidth: 400,
                field   : "ps"
            }];
    var wo_cols =
            [{
                id      : "wo",
                name    : "WO",
                field   : "wo"
            },{
                id       : "ca",
                name     : "ca",
                field    : "ca"
            },{
                id       : "wp",
                name     : "wp",
                field    : "wp"
            }, {
                id       : "item",
                name     : "item",
                field    : "item"
            }, {
                id       : "scope",
                name     : "scope",
                field    : "scope"
            }, {
                id       : "rsrc",
                name     : "rsrc",
                field    : "rsrc"
            }, {
                id                  : "a",
                name                : "Acwp",
                formatter           : myhourFormatter,
                groupTotalsFormatter: sumTotalsFormatter,
                field               : "a"
            },{
                id                  : "p2bac",
                name                : "BCWP",
                field               : "p2bac",
                formatter           : myhourFormatter,
                groupTotalsFormatter: sumTotalsFormatter

            },{
                id                  : "eac",
                formatter           : myhourFormatter,
                name                : "EAC",
                groupTotalsFormatter: sumTotalsFormatter,
                field               : "eac"
            },{
                id                  : "bac",
                formatter           : myhourFormatter,
                name                : "bac",
                groupTotalsFormatter: sumTotalsFormatter,
                field               : "bac"
            },{
                id       : "pc",
                //formatter: myCurrencyFormatter,
                name     : "pc",
                field    : "pc"
            },{
                id                  : "eac_growth",
                name                : "eac_growth",
                formatter           : myhourFormatter,
                groupTotalsFormatter: sumTotalsFormatter,
                field               : "eac_growth"
            },{
                id       : "bac_cpi",
                name     : "bac_cpi",
                field    : "bac_cpi"
            }];
    var top5_cols = [{
        id      : "ca",
        name    : "CA",
        field   : "ca"
    },{
        id       : "cam",
        name     : "CAM",
        field    : "cam"
    },{
        id       : "val",
        name     : "CUM CV",
        formatter: myFormatterDollarsAndHours,
        field    : "val"
    }]

    return {
        dataCols : lcs_cols,
        wo_cols  : wo_cols,
        top5_cols: top5_cols,
        updateColumnHeaders: updateColumnHeaders,
        addCommas: addCommas
    };
})