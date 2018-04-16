/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){

    function shipFormatter(row, cell, value, columnDef, dataContext){
        return "<p  style='text-align: center; font-weight:bold;'>" + value+"</p>";
    }

    function validFormatter(row, cell, value, columnDef, dataContext) {
        if(value=="READY!!"){
            return "<p  style='text-align: center; font-weight:bold; background-color: limegreen; '>"+ value+"</p>";
        }
        else{
            return "<p  style='text-align: center; font-weight:bold; color: white; background-color: red; '>"+ value+"</p>";
        }

    }
    var cols = [
        {
            id       : "ship_code",
            name     : "HULL",
            field    : "ship_code",
            formatter : shipFormatter

        },{
            id       : "check",
            name     : "check",
            field    : "check"

        },{
            id       : "valid",
            name     : "valid",
            field    : "valid",
            formatter : validFormatter

        }];
    return {
        cols   : cols
    };
})