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
    function glLink(row, cell, value, columnDef, dataContext) {

        var link_paran = "<a href='#' onclick=window.open('gl_detail.html?ship_code="+dataContext.ship_code+"&wp="+dataContext.wp+"')>"+value+"</a>";
        //var link_paran = "<a href=gl_detail.html?ship_code="+dataContext.ship_code+"&wp="+dataContext.wp+">"+value+"</a>";
        return link_paran;
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
        cols    : cols,
        glCols  : glCols
    };
})