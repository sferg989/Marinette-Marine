/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){

    var loadRequiredVars = function (ajaxDataObj,loadRequiredVarsFileCB) {
        var worker;
        workers     = new Worker("lib/workers/build_required_vars.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
        function workerDone(e) {
            if(e.data.id == undefined){
                return false;
            }
            else{
                loadRequiredVarsFileCB(e.data.response)
            }
        }

    }
    return {
        loadRequiredVars: loadRequiredVars
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
