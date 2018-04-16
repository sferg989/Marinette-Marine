
require(["lib/components/selectBox",
    "lib/components/data",
    "bootbox"
], function(selectBox,dataService,bootbox) {
$( document ).ready(function() {
    var loadingIndicator = null;

    var url = "lib/php/grid.php";
    selectBox.createSelectBox("ship_code",url);
    selectBox.createSelectBox("rpt_period",url);
    selectBox.defaultRPTPeriod();
    function loadRequiredVarsFileCB(data){
        if(data!="true"){
            loadingIndicator.fadeOut();
            window.open(data);
        }
        else{
            loadingIndicator.fadeOut();
        }
        console.log(data);
    }
    $("#build_format5_required_vars").click(function(){
        var ship_code         = $("#ship_code").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};

        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#center");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        ajaxDataObj.control    = "build_required_vars";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        loadingIndicator.show();
        dataService.loadRequiredVars(ajaxDataObj,loadRequiredVarsFileCB)
        console.log("This worked");
    });
    $("#upload_explanations").click(function(){
        var ship_code         = $("#ship_code").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};

        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#center");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        ajaxDataObj.control    = "format5";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        loadingIndicator.show();
        dataService.loadRequiredVars(ajaxDataObj,loadRequiredVarsFileCB)
        console.log("This worked");
    });
    $("#build_word_doc").click(function(){
        var ship_code         = $("#ship_code").val();
        var rpt_period        = $("#rpt_period").val();
        var ajaxDataObj       = {};

        if(ship_code==null){
            bootbox.alert("Please Select A Hull!");
            return false;
        }
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#center");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        ajaxDataObj.control    = "word_doc";
        ajaxDataObj.ship_code  = ship_code;
        ajaxDataObj.rpt_period = rpt_period;
        loadingIndicator.show();
        dataService.loadRequiredVars(ajaxDataObj,loadRequiredVarsFileCB)
        console.log("This worked");
    });
});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
