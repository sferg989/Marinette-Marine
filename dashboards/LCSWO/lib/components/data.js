/**
 * Created by fs11239 on 2/23/2017.
 */
define(["./title_update"],function(updateTitle){
    var getGridData = function (ajax_data_object, gridDataView) {
        $.ajax({
            dataType: "json",
            url     : "lib/php/lcs.grid.php",
            data    : ajax_data_object,
            success: function(data) {
                //console.log(data);
                //_.sortBy(data, function(o) { return o.start.dateTime; })
                //console.log("from the Ajax",data);
                return data;

            },

        }).done(function (data){
            gridDataView(data);
            return data;
        });

    }

    var getWOData = function (ship_code, selectBoxVal, datTypeSelectBox, gridDataView) {
        $.ajax({
            dataType: "json",
            url     : "lib/php/lcs.grid.php",
            data    : {
                control      : "top_5_grid",
                top_five_type: selectBoxVal,
                data_type    : datTypeSelectBox,
                ship_code    : ship_code
            },
            success: function(data) {
                var latestrelease = data[0].latestDate
                updateTitle.updateWODate(latestrelease);
                return data;
            },

        }).done(function (data){
            gridDataView(data);

            return data;
        });

    }

    var getChartData = function (numPeriods, chartType, rpt_period, dataItem, updateChartCallBack){
        $.ajax({
            url     : "lib/php/lcs.charts.php",
            method  : 'GET',
            dataType: 'json',
            data : {
                num_periods: numPeriods,
                control    : chartType,
                rpt_period : rpt_period,
                filter     : dataItem
            },
            success: function (data) {

                return data;
            }
        }).done(function (data){
            updateChartCallBack(data);
            return data;
        });;
    }
    return {
        getGridData     : getGridData,
        getWOData     : getWOData,
        getChartData: getChartData
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
