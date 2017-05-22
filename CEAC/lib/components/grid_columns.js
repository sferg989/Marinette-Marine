/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){

    var cols = [
       {
           id      : "name",
           name    : "Hull",
           field   : "name"
        },{
            id      : "ship_code",
            name    : "ship-code",
            field   : "ship_code"
        }];
    var rptCols = [
       {
           id      : "rpt_period",
           name    : "RPT period",
           field   : "rpt_period"
        }];
    return {
        cols   : cols,
        rptCols: rptCols
    };
})