/**
 * Created by fs11239 on 4/3/2017.
 */
define(["./line_chart"], function(linechartJS){
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

    $(".btn-group").on('click', '[name="metric_options"]', function(e, lineChart) {
        $(this).toggleClass('active')
            .siblings().not(this).removeClass('active');
        //linechartJS.drawLineChart();

    });

    $(".btn-group").on('click', '[name="tp_options"]', function(e) {
        // add class active to current button and remove it from the siblings
        $(this).toggleClass('active')
            .siblings().not(this).removeClass('active');
        //linechartJS.drawLineChart();

    });

    return {
        getLineChartPeriod: lineChartPeriodValue,
        getLineChartMetric: lineChartMetric
    }
});
