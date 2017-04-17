define(["./btn_group", "jsCharts"], function(btnGroup){


    //console.log(data);
    //var varSet = btnGroup.getPieChartMetric();
    //btnGroup.testMethod();

    var getCurPieData = function(){
        //btnGroup.testMethod();
        //var varSet = btnGroup.getPieChartMetric();
        $.ajax({
            url: "lib/php/lcs.charts.php",
            method: 'GET',
            dataType: 'json',
            data : {
                control    : "VASR"
            },
            success: function (d) {
                chartData = {
                    //labels: d.AxisLabels,
                    labels: d.labels,
                    datasets:d.datasets
                };
                var ctx = $("#pi_var_chart");
                new Chart(ctx,{
                    type: 'pie',
                    data: chartData
                });
            }
        });
    }
    var getTrendPieData = function(){
        var varSet     = btnGroup.getPieChartMetric();
        var numPeriods = btnGroup.getPieChartPeriod();
        $.ajax({
            url: "lib/php/lcs.charts.php",
            method: 'GET',
            dataType: 'json',
            data : {
                num_periods: numPeriods,
                control    : varSet
            },
            success: function (d) {
                chartData = {
                    //labels: d.AxisLabels,
                    labels: d.labels,
                    datasets:d.datasets
                };
                //var c = $('#summary');
                var ctx = $("#pi_var_trend_chart");
                new Chart(ctx, {
                    type: "pie",
                    data : chartData
                });
            }
        });
    }

    // Set a callback to run when the Google Visualization API is loaded.
    return {
        drawCurPieChart   : getCurPieData,
        drawTrendPieChart: getTrendPieData
    };
})