/**
 * Created by fs11239 on 4/4/2017.
 */
define(['jquery'], function($){
    var updateTitle = function (ship_code, rpt_period)
    {
        $("#rpt_period_div").append("Reporting Period- 201703");
        $//("#title").append(ship_code);

        $("#title").addClass("title_font");
        $("#rpt_period_div").addClass("title_font");
        var height = $(window).height();
        var width = $(window).width();

        //$("#top_row_charts").height(height*.2);

        //$("#var_chart").height(height*.4);
        //$("#var_line").width(width*.9);

    }
    var updateWODate = function (date){
        $("#wo_date").empty();
        $("#wo_date").append("WO AS OF "+date);
        $("#wo_label").addClass("title_font");
    }
    return {
        updateTitle : updateTitle,
        updateWODate: updateWODate
    };
})