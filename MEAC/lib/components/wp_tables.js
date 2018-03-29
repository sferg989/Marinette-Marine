/**
 * Created by fs11239 on 2/23/2017.
 */
define([
    "jquery",
    "bootbox",
    "lib/components/data"],function($, bootbox, dataService){
    var loadingIndicator = null;

    function loadCBMCB(data){
        console.log("this is the CB"+data);
        if(data!="true"){
            bootbox.alert(data);
        }
        loadingIndicator.fadeOut();
    }
    $("#load_gl").click(function(){
        var ship_code         = $("#ship_code_wp_table").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control    = "build_wp_gl_detail";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();

        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#load_open_po").click(function(){
        var ship_code         = $("#ship_code_wp_table").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control    = "build_wp_open_po";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#wp_committed_po").click(function(){
        var ship_code         = $("#ship_code_wp_table").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control    = "build_wp_committed_po";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#wp_open_buy").click(function(){
        var ship_code         = $("#ship_code_wp_table").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        ajaxDataObj.control    = "build_wp_open_buy";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });
    $("#wp_ebom").click(function(){
        var ship_code         = $("#ship_code_wp_table").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};
        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        if(rpt_period==null){
            bootbox.alert("Please Select A RPT PERIOD!");
            return false;
        }
        ajaxDataObj.control    = "build_wp_ebom";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#profile");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        dataService.loadBaan(ajaxDataObj,loadCBMCB);
    });

})/**
 * Created by fs11239 on 4/11/2017.
 */
