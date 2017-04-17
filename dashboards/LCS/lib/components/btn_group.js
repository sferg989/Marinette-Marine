/**
 * Created by fs11239 on 4/3/2017.
 */
define(["./line_chart","./pie_chart"], function(lineChart, pieCharts){
    var lineChartPeriodValue = function () {
        var num_periods= '';
        $('#time_period .active').each(function(){
            num_periods= $(this).attr('id');
        });
        return num_periods;
    }
    var lineChartMetric = function () {
        var num_periods= '';
        $('#chart_type .active').each(function(){
            num_periods= $(this).attr('id');
        });
        return num_periods;
    }
    var pieChartMetric = function () {
        var num_periods= '';
        $('#var_type .active').each(function(){
            num_periods= $(this).attr('id');
        });
        return num_periods;
    }
    var pieChartPeriod = function () {
        var num_periods= '';
        $('#var_trend .active').each(function(){
            num_periods= $(this).attr('id');
        });
        return num_periods;
    }
    $(".btn-group").on('click', '[name="metric_options"]', function(e, lineChart) {
        $(this).toggleClass('active')
            .siblings().not(this).removeClass('active');

        //lineChart.drawLineChart();
    });

    $(".btn-group").on('click', '[name="tp_options"]', function(e) {
        // add class active to current button and remove it from the siblings
        $(this).toggleClass('active')
            .siblings().not(this).removeClass('active');
    });
    $(".btn-group").on('click', '[name="var_options"]', function(e) {
        var control = $(this).text();
        $('#var_trend .active').each(function(){
            metric= $(this).attr('id');
        });
        //pieCharts.getTrendPieDate("var_trend","6");
        // add class active to current button and remove it from the siblings
        $(this).toggleClass('active')
            .siblings().not(this).removeClass('active');

        //pieCharts.getCurPieChart(control);
        //pieCharts.drawTrendPieChart(metric,"6");

    });
    $(".btn-group").on('click', '[name="period_options"]', function(e) {
        var control = $(this).attr('id');
        $(this).toggleClass('active')
            .siblings().not(this).removeClass('active');
        pieCharts.drawCurPieChart();

        //pieCharts.drawTrendPieChart("var_trend",num_periods_var_tp);
    });
    var test = function (){
        console.log("thiw is a test Method");
    }

    return {
        getLineChartPeriod: lineChartPeriodValue,
        getLineChartMetric: lineChartMetric,
        getPieChartMetric : pieChartMetric,
        getPieChartPeriod : pieChartPeriod,
        testMethod         : test
    }
});
