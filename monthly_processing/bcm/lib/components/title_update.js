/**
 * Created by fs11239 on 4/4/2017.
 */
define(['jquery'], function($){
    var updateTitle = function (ship_code, rpt_period)
    {
        $("#rpt_period_div").append(rpt_period);
        $("#title").append(ship_code);

        $("#title").addClass("title_font");
        $("#rpt_period_div").addClass("title_font");
    }

    return {
        updateTitle: updateTitle
    };
})