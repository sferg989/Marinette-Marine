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

    var url = "../load_bac_eac.php";
    var params = "control="+action+"&rpt_period="+rpt_period+"&code="+code+"";
    http.open("POST", url, true);

//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var response_data = {};
            if(this.responseText== undefined)
            {
                this.responseText = "we donts gots to know";
            }
            response_data.id        = "finished";
            response_data.action    = action;

            //postMessage({id: action, reponse : this.responseText});
            postMessage(response_data);
        }
    }
    http.send(params);
};
