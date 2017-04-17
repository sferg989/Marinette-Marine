define(['googleCharts'], function(){

    var line = function (div_name,ship_code, rpt_period)
    {
        var jsonData = $.ajax({
            url: "lib/php/log_analysis.charts.php",
            data : {
                control    : "mr_by_period",
                ship_code  : ship_code,
                rpt_period : rpt_period,
                num_periods: 9
            },
            dataType: "json",
            async: false
        }).responseText;
        // Create our data table out of JSON data loaded from server.
        var data  = new google.visualization.DataTable(jsonData);
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById(div_name));
        var options = {
            width     : 500,
            height    : 240,
            hAxis     : {
                title           : 'Reporting Period',
                direction       : -1,
                slantedText     : true,
                slantedTextAngle: 25
            },
            seriesType: 'line',
            series    : {1: {type: 'bars'}}
        };
        chart.draw(data, options);
    }
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(line);

    // Set a callback to run when the Google Visualization API is loaded.
    return {
        createLine: line
    };
})