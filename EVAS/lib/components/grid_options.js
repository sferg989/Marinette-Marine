/**
 * Created by fs11239 on 2/23/2017.
 */
/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){

    var options = {
        enableCellNavigation: true,
        editable            : true,
        forceFitColumns     : true,
        autoHeight          : false,
        sort                : false
    };
    var glOptions = {
        enableCellNavigation: true,
        editable            : false,
        forceFitColumns     : true,
        autoHeight          : true,
        sort                : false
    }
    return {
        gridOptions : options,
        glOptions: glOptions
    };
})