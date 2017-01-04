$(document).ready(function(){
    $("#hiding_div").hide();
    var chartDataURL = "dashboard_chart.php";
    var filterDataURL = "dashboard_filter.php";
    function createSelect2Box(filter_name) {
        $("."+filter_name).select2({
            //minimumResultsForSearch: -1,
            width : 150,
            allowClear : true,
            placeholder: "Select "+filter_name,
            ajax: {
                url: filterDataURL+"/"+filter_name,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        filter : filter_name,
                        q: params.term, // search term
                        page: params.page
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
    function createGaugeChart(id, colorRangesObject, result){
        new RGraph.Meter({
            id: id,
            min: .75,
            max: 1.25,
            value: result,
            options: {
                gutterTop     : -10,
                gutterBottom  : 0,
                gutterLeft    : 15,
                gutterRight   : 15,
                labelsCount   : 3,
                textSize      : 10,
                scaleDecimals : 2,
                //valueTextBoundingStroke: '#aaa',
                colorsRanges  : colorRangesObject,
                textAccessible: true,
                greenColor    : '#afa'
            }
        }).grow({frames: 60});
    }
    function createThermoChart(id, result){
        new RGraph.Thermometer({
            id: id,
            min: 0,
            max: 100,
            value: result,
            options: {
                valueLabel : true,
                labelsCount : 4,
                scaleVisible : true,
                gutterRight: 45,
                gutterLeft: 45,
                colors: ['rgba(255,0,0,1)']
            }
        }).grow({frames: 60});
    }
    createSelect2Box("project");
    createSelect2Box("cam");
    createSelect2Box("ca");
    createSelect2Box("period");
    createSelect2Box("rpt_type");    

    var colorRangesObject = [[.75,.88, 'red'],[.89,.95, 'yellow'],[.96,1.1, 'green'],[1.101,1.25, 'blue']];
    var chartValues = {
        getGaugeChart : function (chart_type) {
            $.ajax({
                url: chartDataURL,
                data : {
                    chart_type: chart_type,
                    project   : $(".project").val(),
                    cam       : $(".cam").val(),
                    ca        : $(".ca").val(),
                    period    : $(".period").val(),
                    rpt_type  : $(".rpt_type").val()
                },
                success:function(data)
                {
                    createGaugeChart(chart_type,colorRangesObject, data);
                }
            });
        },
        getThermoChart : function (chart_type) {
            $.ajax({
                url: chartDataURL,
                data : {
                    chart_type: chart_type,
                    project   : $(".project").val(),
                    cam       : $(".cam").val(),
                    ca        : $(".ca").val(),
                    period    : $(".period").val(),
                    rpt_type  : $(".rpt_type").val()
                },
                success:function(data)
                {
                    createThermoChart(chart_type, data);
                }
            });
        }

    };
    $(".ca").on("select2:select", function (e) {
        $(".cam").val("","CD");
    });

    $( "#submit" ).click(function() {
        var project_val  = $("#project").val();
        var period_val   = $("#period").val();
        var rpt_type_val = $("#rpt_type").val();
        if(project_val=="" || project_val == undefined)
        {
            alert("Please Select a Project!");
            return false;
        }
        if(period_val=="" || period_val == undefined)
        {
            alert("Please Select a Reporting Period!");
            return false;
        }
        if(rpt_type_val=="" || rpt_type_val == undefined)
        {
            alert("Please Select a Report Type!");
            return false;
        }
        $("#costData").jsGrid("loadData");
        $("#costIndices").jsGrid("loadData");
        //$("#hiding_div").empty();
        $("#hiding_div").show();
        //$("#costIndices").show();
        chartValues.getGaugeChart("spi");
        chartValues.getGaugeChart("cpi");
        chartValues.getGaugeChart("tcpi");
        chartValues.getGaugeChart("bei_start");
        chartValues.getGaugeChart("bei_finish");
        chartValues.getThermoChart("ps");
        chartValues.getThermoChart("pc");
        chartValues.getThermoChart("actuals_spent");
    });

});