/**
 * Created by fs11239 on 2/23/2017.
 */
define(['bootbox'], function(){
    var bootBoxAlert = function (message)
    {
        bootbox.alert(message);
    }
    return {
        bootalert: bootBoxAlert
    };
})