define(["./btn_group", "./get_url","jsCharts"], function(btnGroup, getUrl){
    var test = function (){
        console.log("thiw is a test Method");
    }
    var getChartData = function(dataItem){
        var numPeriods = btnGroup.getLineChartPeriod();
        var chartType  = btnGroup.getLineChartMetric();
        var rpt_period = getUrl.getUrlParam("rpt_period");
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
            success: function (d) {
                chartData = {
                    labels  : d.labels,
                    datasets: d.datasets
                };
                //var c = $('#summary');
                var ctx = $("#var_chart");
                new Chart(ctx, {
                    type: d.datasets[0].type,
                    data : chartData
                });
            }
        });
    }
    // Set a callback to run when the Google Visualization API is loaded.
    return {
                drawLineChart   : getChartData,
                testMethod      : test
    };
})