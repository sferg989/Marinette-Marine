/**
 * Created by fs11239 on 2/23/2017.
 */
/**
 * Created by fs11239 on 2/23/2017.
 */
define(["slickColumnPicker"], function(){

    var options = {
        enableCellNavigation: true,
        editable            : true,
        forceFitColumns     : true,
        autoHeight          : true,
        sort                : false,
        topPanelHeight      : 25
    }
    var woGridOptions = {
        enableCellNavigation: true,
        editable            : true,
        forceFitColumns     : true,
        autoHeight          : true,
        sort                : true,
        topPanelHeight      : 25
    };

    return {
        gridOptions : options,
        woGridOptions: woGridOptions
    };
})