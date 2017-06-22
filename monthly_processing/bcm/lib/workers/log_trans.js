/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {

    var code         = e.data.code;
    var action       = e.data.action;
    var rpt_period   = e.data.rpt_period;
    var logTransData = e.data.logTrans;
    var http         = new XMLHttpRequest();

    var url = "../php/log_trans.php";
    var params = "control="+action+"&rpt_period="+rpt_period+"&code="+code+"&logTransData="+logTransData+"";
    http.open("POST", url, true);

//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var data = {};
            data.id= action;

            postMessage(data);
        }
    }
    http.send(params);
};
