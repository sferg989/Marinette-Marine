define(["./get_url","./data_repo","./title_update","jsCharts"], function(getUrl, data,updateTitle){
    var test = function (){
        console.log("thiw is a test Method");
    }
    var getChartData = function(dataItem){

        var numPeriods = btnGroup.getLineChartPeriod();
        var chartType  = btnGroup.getLineChartMetric();
        var rpt_period = getUrl.getUrlParam("rpt_period");
        function updateChartCallBack(data){
            $("#var_line").empty();
            var newCanvas = "<canvas id = \"var_chart\"></canvas>";
            $("#var_line").append(newCanvas);
            chartData = {
                labels  : data.labels,
                datasets: data.datasets
            };
            var ctx = document.getElementById("var_chart").getContext("2d");
            //updateTitle.updateTitle();
            /*new Chart(ctx, {
                type: data.datasets[0].type,
                data : chartData
            });*/
            new Chart(ctx, {
                type: "line",
                data: data,
                responsive: true,
                showTooltips: true,
                multiTooltipTemplate: "<%= value %>",
            });

        }
        data.getChartData(numPeriods, chartType, rpt_period, dataItem, updateChartCallBack);

    }
    // Set a callback to run when the Google Visualization API is loaded.
    return {
                drawLineChart   : getChartData,
                testMethod      : test
    };
})