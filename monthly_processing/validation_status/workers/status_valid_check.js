/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {

    var code       = e.data.code;
    var action     = e.data.action;
    var rpt_period = e.data.rpt_period;
    var http       = new XMLHttpRequest();

    var url = "../status_validation.php";
    var params = "control="+action+"&rpt_period="+rpt_period+"&code="+code+"";
    http.open("POST", url, true);

//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var data = {};
            var data_response = this.responseText.split("<>");
            data.bl_table = data_response[0];
            data.tp_table = data_response[1];
            data.hc_table = data_response[2];
            data.id= action;

            postMessage(data);
        }
    }
    http.send(params);
};
