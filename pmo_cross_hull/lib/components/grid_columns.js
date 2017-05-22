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
    function createDefaultRPTPeriod(){
        var dd, mm,month,year,yyyy,rpt_period;
        var today = new Date();
        dd        = today.getDate();
        mm        = today.getMonth() + 1; //January is 0!
        yyyy      = today.getFullYear();
        if(dd<22){
            mm = mm-1;
            if(mm==0){
                mm = 12;
                yyyy = yyyy-1;
            }
        }
        if(mm<10) {
            mm='0'+mm
        }
        yyyy = yyyy.toString();

        rpt_period = yyyy + mm;
        return rpt_period;
    }
    function getNextRPTPeriod(rptPeriod){
        var month,year, new_month,rpt_period;
        year = parseInt(rptPeriod.substring(0, 4));
        month = parseInt(rptPeriod.substring(4, 6));
        new_month = parseInt(month + 1);


        if(new_month<10) {

            new_month='0'+new_month

        }
        else{
            if(new_month == 13){
                new_month='0'+1;
                year = year+1;
            }
        }
        year = year.toString();
        rpt_period = year + new_month;
        console.log(rpt_period);

        return rpt_period;
    }
    var col1 = createDefaultRPTPeriod();
    var col2 = getNextRPTPeriod(col1);
    var col3 = getNextRPTPeriod(col2);
    var col4 = getNextRPTPeriod(col3);
    var col5 = getNextRPTPeriod(col4);
    var col6 = getNextRPTPeriod(col5);

    var cols = [
       {
           id   : "ship_code",
           field: "ship_code",
           name : "HUll"
       },{
            id   : "hours",
            field: "hours",
            name : "HOURS"
       },{
            id   : col1,
            field: col1,
            name : col1
       },{
            id   : col2,
            field: col2,
            name : col2
       },{
            id   : col3,
            field: col3,
            name : col3
       },{
            id   : col4,
            field: col4,
            name : col4
       },{
            id   : col5,
            field: col5,
            name : col5
       },{
            id   : col6,
            field: col6,
            name : col6
       }];

    return {
        cols: cols,
        col1: col1,
        col2: col2,
        col3: col3,
        col4: col4,
        col5: col5,
        col6: col6
    };
})