$(document).ready(function(){
    // Load the Visualization API and the piechart package.
    google.charts.load('current', {'packages':['corechart', "table"]});
    // Set a callback to run when the Google Visualization API is loaded.
    var filterDataURL = "timephase_filter.php";
    var chartDataURL = "timephase.php";
    function createSelect2Box(filter_name) {
        $("."+filter_name).select2({
            //minimumResultsForSearch: -1,
            width : 154,
            allowClear : true,
            placeholder: "Select "+filter_name,
            ajax: {
                url: filterDataURL+"/"+filter_name,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        filter: filter_name,
                        q     : params.term, // search term
                        page  : params.page
                    };
                },
                processResults: function (data, page) {
                    // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data
                    return {
                        results: data.items
                    };
                },
                cache: true
            }
        });
    }
    function drawSPATable()
    {
        var jsonData = $.ajax({
            url: chartDataURL,
            data : {
                chart_type   : "spa_table",
                project     : $(".project").val(),
                cam         : $(".cam").val(),
                ca          : $(".ca").val(),
                start_period: $(".start_period").val(),
                end_period  : $(".end_period").val(),
                rpt_type    : $(".rpt_type").val()
            },
            dataType: "json",
            async: false
        }).responseText;
        var data  = new google.visualization.DataTable(jsonData);
        var table = new google.visualization.Table(document.getElementById('table_div'));
        var formatter = new google.visualization.NumberFormat({
            prefix: '$'
        });
        var num = parseInt(data.getNumberOfColumns());
        for (i = 1; i < num; i++)
        {
            formatter.format(data, i); // Apply formatter to second column
        }

        table.draw(data, {allowHtml: true, showRowNumber: false, width: '95%', height: '100%'});
    }
    function drawSPITable()
    {
        var jsonData = $.ajax({
            url: chartDataURL,
            data : {
                chart_type   : "spi_table",
                project     : $(".project").val(),
                cam         : $(".cam").val(),
                ca          : $(".ca").val(),
                start_period: $(".start_period").val(),
                end_period  : $(".end_period").val(),
                rpt_type    : $(".rpt_type").val()
            },
            dataType: "json",
            async: false
        }).responseText;
        var data  = new google.visualization.DataTable(jsonData);
        var table = new google.visualization.Table(document.getElementById('spi_cpi_table_div'));
        var formatter = new google.visualization.ColorFormat();
        formatter.addRange(0, .88, 'black', 'red');
        formatter.addRange(.89, .95, 'black', 'Yellow');
        formatter.addRange(.96, 1.1, 'black', 'green');
        formatter.addRange(1.01,2, 'white', 'blue');
        formatter.addRange(2.01,10, 'white', 'blue');
        var num = parseInt(data.getNumberOfColumns());
        for (i = 1; i < num; i++)
        {
            formatter.format(data, i); // Apply formatter to second column
        }
        table.draw(data, {allowHtml: true, showRowNumber: false, width: '95%', height: '100%'});
    }
    function drawChart() {
        var jsonData = $.ajax({
            url: chartDataURL,
            data : {
                chart_type   : "spa",
                project     : $(".project").val(),
                cam         : $(".cam").val(),
                ca          : $(".ca").val(),
                start_period: $(".start_period").val(),
                end_period  : $(".end_period").val(),
                rpt_type    : $(".rpt_type").val()
            },
            dataType: "json",
            async: false
        }).responseText;
        // Create our data table out of JSON data loaded from server.
        var data  = new google.visualization.DataTable(jsonData);
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, {width: 500, height: 240, hAxis: {title: "Reporting Period" , direction:-1, slantedText:true, slantedTextAngle:25 }});

    }

    function drawSPICPIChart() {
        var jsonData = $.ajax({
            url: chartDataURL,
            data : {
                chart_type   : "spi_cpi",
                project     : $(".project").val(),
                cam         : $(".cam").val(),
                ca          : $(".ca").val(),
                start_period: $(".start_period").val(),
                end_period  : $(".end_period").val(),
                rpt_type    : $(".rpt_type").val()
            },
            dataType: "json",
            async: false
        }).responseText;
        // Create our data table out of JSON data loaded from server.
        var data  = new google.visualization.DataTable(jsonData);
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('spicpi_chart_div'));
        chart.draw(data, {width: 500, height: 240, hAxis: {title: "Reporting Period" , direction:-1, slantedText:true, slantedTextAngle:25 }});

    }
    createSelect2Box("project");
    createSelect2Box("cam");
    createSelect2Box("ca");
    createSelect2Box("start_period");
    createSelect2Box("end_period");
    createSelect2Box("rpt_type");

    $( "#submit" ).click(function() {
        var project_val      = $("#project").val();
        var start_period_val = $("#start_period").val();
        var end_period_val   = $("#end_period").val();
        var rpt_type_val     = $("#rpt_type").val();
        if(project_val=="" || project_val == undefined)
        {
            alert("Please Select a Project!");
            return false;
        }
        if(start_period_val=="" || start_period_val == undefined || end_period_val== "" || end_period_val == undefined)
        {
            alert("Please Select a Starting or Ending Period");
            return false;
        }
        google.charts.setOnLoadCallback(drawSPITable);

        google.charts.setOnLoadCallback(drawChart);
        google.charts.setOnLoadCallback(drawSPICPIChart);
        google.charts.setOnLoadCallback(drawSPATable);

    });


});