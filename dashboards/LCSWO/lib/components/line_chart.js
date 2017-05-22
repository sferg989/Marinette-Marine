define(["./btn_group", "./get_url","./data","./title_update","jsCharts"], function(btnGroup, getUrl, data,updateTitle){
    var test = function (){
        console.log("thiw is a test Method");
    }
    var numberWithCommas = function(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

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

            var myLineChart = new Chart(ctx, {
                type                : "line",
                data                : chartData,
                options: {
                    tooltips: {
                        mode: 'label',
                        callbacks: {
                            title:function(tooltipItems, data) {
                                var xlabelIndex = tooltipItems[0].index;
                                var title = data.labels[xlabelIndex]

                                return title + ' ';
                            },
                            label: function(tooltipItem, data) {
                                return data.datasets[tooltipItem.datasetIndex].label + ": " + numberWithCommas(tooltipItem.yLabel);
                                },
                            }
                        },
                    scales: {
                        yAxes: [{
                            ticks: {
                                callback: function(value, index, values) {
                                    if(chartType=="ev_index"){
                                        return value.toFixed(2);
                                    }
                                    else{
                                        if(parseInt(value) >= 1000 ||parseInt(value) <= 1000){
                                            return '$' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                        } else {
                                            return '$' + value;
                                        }
                                    }
                                }
                            }
                        }]
                    }
                }
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