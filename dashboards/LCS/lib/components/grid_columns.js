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
    lcs_cols=
       [{
            id      : "s",
            minWidth: 300,
            maxWidth: 400,
            name    : "s",
            formatter: myCurrencyFormatter,
            field   : "s"
        },{
            id      : "p",
            minWidth: 300,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            name    : "BCWP",
            field : "p"
        },{
            id      : "a",
            name    : "Acwp",
            minWidth: 300,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            field   : "a"
        },{
            id      : "sv",
            minWidth: 300,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            name    : "sv",
            field : "sv"
        },{
            id       : "cv",
            minWidth: 300,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            name     : "cv",
            field    : "cv"
        },{
            id      : "ub",
            minWidth: 300,
            maxWidth: 400,
            formatter: myCurrencyFormatter,
            name    : "ub",
            field   : "ub"
        }];

    var  metaDataCols = function (level){
        switch (level) {

            case 0:
                var lcs_meta_cols = [{
                    id      : "Hull",
                    name    : "Hull",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "Hull"
                }];
                break;
            case 1:
                var lcs_meta_cols= [{
                    id      : "Hull",
                    name    : "Hull",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "Hull"
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
                    id      : "Hull",
                    name    : "Hull",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "Hull"
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
                    id      : "Hull",
                    name    : "Hull",
                    minWidth: 300,
                    maxWidth: 400,
                    field   : "Hull"
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
        dataCols      : lcs_cols,
        getMetaCols   : metaDataCols,
        findDrillLevel: findDrillLevel,
        findCurLevel  : findCurLevel
    };
})