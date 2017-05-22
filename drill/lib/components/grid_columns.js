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
                var absval = Math.abs(value).toFixed(2);
                var withcommas = addCommas(absval);
                return "<p class='showNegRed'>$(" + withcommas+")</p>";
            }
            else{
                var withcommas = addCommas(value.toFixed(2));
                return "<p class='showPosBlack'>$ " + withcommas+"</p>";
            }
    }

    var  metaDataCols = function (level){
        switch (level) {

            case 0:
                var lcs_meta_cols = [{
                    id      : "ship_code",
                    name    : "ship_code",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "ship_code"
                }];
                break;
            case 1:
                var lcs_meta_cols= [{
                    id      : "ship_code",
                    name    : "ship_code",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "ship_code"
                },{
                    id      : "wbs",
                    name    : "WBS",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "wbs"
                }]
                break;
            case 2:
                var lcs_meta_cols= [{
                    id      : "ship_code",
                    name    : "ship_code",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "ship_code"
                },{
                    id      : "wbs",
                    name    : "WBS",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "wbs"
                },{
                    id      : "ca",
                    name    : "CA",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "ca"
                }]
                break;
            case 3:
                var lcs_meta_cols= [{
                    id      : "ship_code",
                    name    : "ship_code",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "ship_code"
                },{
                    id      : "wbs",
                    name    : "WBS",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "wbs"
                },{
                    id      : "ca",
                    name    : "CA",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "ca"
                },{
                    id      : "wp",
                    name    : "WP",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "wp"
                }]
                break;
        }
        return lcs_meta_cols;
    }
    var cols = [
       {
            id       : "s",
            name     : "S ",
            formatter: myFormatter,
            field    : "s"
        },{
            id       : "p",
            name     : "P ",
            formatter: myFormatter,
            field    : "p"
        },{
            id       : "a",
            name     : "A ",
            formatter: myFormatter,
            field    : "a"
        }];
    var findDrillLevel = function (level) {
        if(level =="Hull"){
            return 1;
        }
        else if(level =="WBS"){
            return 2;
        }
        else if(level =="CA"){
            return 3;
        }
        else if(level =="WP"){
            return 3;
        }
        else{
            return "undefined";
        }

    }
    var findCurLevel = function (level) {
        if(level =="Hull"){
            return 0;
        }
        else if(level =="WBS"){
            return 1;
        }
        else if(level =="CA"){
            return 2;
        }
        else if(level =="WP"){
            return 3;
        }
        else{
            return "undefined";
        }

    }
    return {
        cols          : cols,
        getMetaCols   : metaDataCols,
        findCurLevel  : findCurLevel,
        findDrillLevel: findDrillLevel
    };
})