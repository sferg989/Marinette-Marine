/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
var url = "../load_baseline.php";
this.onmessage = function(e) {
    var action     = e.data.action;
    var name       = e.data.name;
    var code       = e.data.code;
    var rpt_period = e.data.rpt_period;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            postMessage({id: action});

        }
    };
    var params = "control="+action+"&rpt_period="+rpt_period+"&code="+code+"";
    xhttp.open("GET", url+"?"+params+"", true);
    xhttp.send();
};
