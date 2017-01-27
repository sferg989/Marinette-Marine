/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {
    var action     = e.data.action;
    var name       = e.data.name;
    var code       = e.data.code;
    var rpt_period = e.data.rpt_period;
    var data     = escape(e.data.p6Data2);
    var http = new XMLHttpRequest();

    var url = "../load_baseline.php";
    var params = "control="+action+"&p6data="+data+"&rpt_period="+rpt_period+"&code="+code+"";
    http.open("POST", url, true);

//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            postMessage({id: action});
        }
    }
    http.send(params);
};
