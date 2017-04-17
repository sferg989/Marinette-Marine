/**
 * Created by fs11239 on 4/4/2017.
 */
define(['jquery'], function($){
    var updateTitle = function (ship_code, rpt_period)
    {
        $("#rpt_period_div").append("Reporting Period- "+rpt_period);
        $("#title").append(ship_code);

        $("#title").addClass("title_font");
        $("#rpt_period_div").addClass("title_font");
        var height = $(window).height();
        var width = $(window).width();

        $("#top_row_charts").height(height*.4);

        $("#var_line").height(height*.4);
        $("#var_line").width(width*.9);

    }

    return {
        updateTitle: updateTitle
    };
})