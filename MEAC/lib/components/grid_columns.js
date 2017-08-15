/**
 * Created by fs11239 on 2/23/2017.
 */
define(["slickEditors"],
    function(){
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
    function defaultFormat(row, cell, value, columnDef, dataContext){
        return value;
    }
    function myCurrencyFormatter(row, cell, value, columnDef, dataContext) {
        if(value <0){
            var absval = Math.abs(value).toFixed(2)
            var withcommas = addCommas(absval);
            return "<p class='showNegRed'>$(" + withcommas+")</p>";
        }
        else if(value>0){

            var withcommas = addCommas(Math.round(parseFloat(value)*100)/100);
            return "<p class='showPosBlack'>$ " + withcommas+"</p>";
        }
        else{
            return "<p class='showPosBlack'>$ 0</p>";
        }
    }
    function myNumFormatter(row, cell, value, columnDef, dataContext) {
        if(value <0){
            var absval = Math.abs(value).toFixed(2)
            return "<p class='showNegRed'>(" + absval+")</p>";
        }
        else{

            var withcommas = addCommas(Math.round(parseFloat(value)*100)/100);
            return "<p class='showPosBlack'>" + withcommas+"</p>";
        }
    }
    function glLink(row, cell, value, columnDef, dataContext) {

        var link_paran = "<a href='#' onclick=window.open('gl_detail.html?ship_code="+dataContext.ship_code+"&wp="+dataContext.wp+"')>"+value+"</a>";
        return link_paran;
    }
    function determineFormatter(field){
        switch (field)
        {
            case "gl_int_amt":
            case "c_unit_price":
            case "open_po_pending_amt":
            case "etc":
            case "eac":
            case "uncommitted":
            case "target_unit_price":
            case "target_ext_cost":
            case "var_target_cost":
                return myCurrencyFormatter
                break;
            case "ebom":
            case "ebom_on_hand":
            case "ebom_issued":
            case "open_buy_item_shortage":
            case "target_qty":
            case "target_qty":
            case "c_qty":
            case "var_target_qty":
            case "gl_qty":
            case "var_ebom":
                return myNumFormatter
                break;
            case "wp":
                return glLink;
                break;
            default:
                return defaultFormat;
                break;
        }
    }
    var findDrillLevel = function (cur_level) {
        var colObj = {};

        if(cur_level =="program"){
            colObj.index = 1;
            colObj.name = "ship_code";
            return colObj;
        }
        if(cur_level =="ship_code"){
            colObj.index = 2
            colObj.name = "category";;
            return colObj;
        }
        if(cur_level =="category"){
            colObj.index = 3
            colObj.name = "swbs_group";;
            return colObj;
        }
        if(cur_level =="swbs_group"){
            colObj.index = 4
            colObj.name = "swbs";;
            return colObj;
        }
        if(cur_level =="swbs"){
            colObj.index = 5
            colObj.name = "wp";;
            return colObj;
        }
        if(cur_level =="wp"){
            colObj.index = 6
            colObj.name = "item";;
            return colObj;
        }
        else{
            return "undefined";
        }
    }
    var getColModelfromDrillLevel = function (drill_level) {
        switch (drill_level) {
            case 0:
                var lcs_meta_cols = [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                }];
                break;
            case 1:
                var lcs_meta_cols= [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                },{
                    id      : "ship_code",
                    name    : "Hull",
                    field   : "ship_code"
                }]
                break;
            case 2:
                var lcs_meta_cols= [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                },{
                    id      : "ship_code",
                    name    : "Hull",
                    field   : "ship_code"
                },{
                    id      : "category",
                    name    : "category",
                    field   : "category"
                }]
                break;
            case 3:
                var lcs_meta_cols= [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                },{
                    id      : "ship_code",
                    name    : "ship_code",
                    field   : "ship_code"
                },{
                    id      : "category",
                    name    : "category",
                    field   : "category"
                },{
                    id      : "swbs_group",
                    name    : "SWBS Group",
                    field   : "swbs_group"
                }]
                break;
            case 4:
                var lcs_meta_cols= [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                },{
                    id      : "ship_code",
                    name    : "Hull",
                    field   : "ship_code"
                },{
                    id      : "category",
                    name    : "category",
                    field   : "category"
                },{
                    id      : "swbs_group",
                    name    : "SWBS Group",
                    field   : "swbs_group"
                },{
                    id      : "swbs",
                    name    : "SWBS",
                    field   : "swbs"
                }]
                break;
            case 5:
                var lcs_meta_cols= [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                },{
                    id      : "ship_code",
                    name    : "Hull",
                    field   : "ship_code"
                },{
                    id      : "category",
                    name    : "category",
                    field   : "category"
                },{
                    id      : "swbs_group",
                    name    : "SWBS Group",
                    field   : "swbs_group"
                },{
                    id      : "swbs",
                    name    : "SWBS",
                    field   : "swbs"
                },{
                    id      : "wp",
                    name    : "WP",
                    field   : "wp"
                }]
                break;
            case 6:
                var lcs_meta_cols= [{
                    id      : "program",
                    name    : "Program",
                    field   : "program"
                },{
                    id      : "ship_code",
                    name    : "Hull",
                    field   : "ship_code"
                },{
                    id      : "category",
                    name    : "category",
                    field   : "category"
                },{
                    id      : "swbs_group",
                    name    : "SWBS Group",
                    field   : "swbs_group"
                },{
                    id      : "swbs",
                    name    : "SWBS",
                    field   : "swbs"
                },{
                    id      : "wp",
                    name    : "WP",
                    field   : "wp"
                },{
                    id      : "item",
                    name    : "Item",
                    field   : "item"
                }]
                break;
        }
        return lcs_meta_cols;
    }
    var GenerateColsFromFieldList = function (field_set_name, setGridColsCB){
        $.ajax({
            url     : "lib/php/meac_grid.php",
            data: {
                control       : "get_col_definition",
                field_set_name: field_set_name
            },
            success : function (data) {
                //console.log(data);
                var list = data.split(",")
                //console.log(list);
                var columnModal = [];
                for (i = 0; i < list.length; i++) {
                    var parts = list[i].split("-");
                    var formater = determineFormatter(parts[0]);
                    var col =
                        {
                            id      : parts[0],
                            name    : parts[1],
                            field   : parts[0],
                            formatter : formater
                        }
                    columnModal.push(col);
                }
                setGridColsCB(columnModal);
            }
        });
    }
    var GenerateDataColsFromFieldList = function (field_set_name, newColsOBJ, setGridColsCB){
        $.ajax({
            url     : "lib/php/meac_grid.php",
            data: {
                control       : "get_data_col_definition",
                field_set_name: field_set_name
            },
            success : function (data) {
                //console.log(data);
                var list = data.split(",")
                //console.log(list);
                for (i = 0; i < list.length; i++) {
                    var parts = list[i].split("-");
                    var formater = determineFormatter(parts[0]);
                    var col = {
                            id      : parts[0],
                            name    : parts[1],
                            field   : parts[0],
                            formatter : formater
                    }
                    newColsOBJ.push(col);
                }
                //console.log(newColsOBJ);
                setGridColsCB(newColsOBJ);
            }
        });
    }
    var cols = [
    {
        id      : "wp",
        name    : "wp",
        field   : "wp",
        formatter : glLink
    },{
        id      : "cam",
        name    : "cam",
        field   : "cam"
    },{
        id      : "swbs",
        name    : "swbs",
        field   : "swbs"
    },{
        id      : "desc",
        name    : "desc",
        field   : "descr"
    },{
        id       : "bac",
        name     : "bac",
        formatter: myCurrencyFormatter,
        field    : "bac"
    },{
        id       : "eac",
        formatter: myCurrencyFormatter,
        name     : "eac",
        field    : "eac"
    },{
        id       : "a",
        formatter: myCurrencyFormatter,
        name     : "a",
        field    : "a"
    },{
        id       : "gl_a",
        formatter: myCurrencyFormatter,
        name     : "gl_a",
        field    : "gl_a"
    },{
        id       : "open_po",
        formatter: myCurrencyFormatter,
        name     : "open_po",
        field    : "open_po"
    },{
        id       : "open_buy_qty",
        formatter: myCurrencyFormatter,
        name     : "open_buy_qty",
        field    : "open_buy_qty"
    },{
        id       : "open_buy",
        formatter: myCurrencyFormatter,
        name     : "open_buy",
        field    : "open_buy"
    },{
        id       : "manual_adj",
        editor: Slick.Editors.Text,
        name     : "manual_adj",
        field    : "manual_adj"
    }];
    var glCols= [
    {
        id      : "wp",
        name    : "wp",
        field   : "wp"
    },{
        id      : "acct",
        name    : "acct",
        field   : "acct"
    },{
        id      : "doc",
        name    : "doc",
        field   : "doc"
    },{
        id      : "line",
        name    : "line",
        field   : "line"
    },{
        id      : "item",
        name    : "item",
        field   : "item"
    },{
        id       : "descr",
        name     : "descr",
        field    : "descr"
    },{
        id       : "ord",
        name     : "ord",
        field    : "ord"
    },{
        id       : "pos",
        name     : "pos",
        field    : "pos"
    },{
        id       : "cust_supp",
        name     : "cust_supp",
        field    : "cust_supp"
    },{
        id       : "qty",
        name     : "qty",
        field    : "qty"
    },{
        id       : "uom",
        name     : "uom",
        field    : "uom"
    },{
        id       : "amt",
        formatter: myCurrencyFormatter,
        name     : "amt",
        field    : "amt"
    },{
        id       : "date",
        name     : "date",
        field    : "date"
    },{
        id       : "i_amt",
        formatter: myCurrencyFormatter,
        name     : "i_amt",
        field    : "i_amt"
    }];
    return {
        cols                     : cols,
        glCols                   : glCols,
        GenerateColsFromFieldList: GenerateColsFromFieldList,
        findDrillLevel           : findDrillLevel,
        getColModelfromDrillLevel : getColModelfromDrillLevel,
        GenerateDataColsFromFieldList : GenerateDataColsFromFieldList
    };
})