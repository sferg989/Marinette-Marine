/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {
    var action, code, rpt_period,url, count;
    url = "../advance_calendar.php";

    code       = e.data.ship_code;
    count      = e.data.count;
    rpt_period = e.data.rpt_period;
    //remove standard elements to the action sequance.
    delete e.data.ship_code;
    delete e.data.count;
    delete e.data.rpt_period;
    delete e.data.action;
    delete e.data.name;

for (i = 0; i < count; i++) {
    action =e.data["action_"+i];
    var http = new XMLHttpRequest();
    console.log("this is the action"+action);
    var params = "control="+action+"&rpt_period="+rpt_period+"&code="+code+"";
    http.open("POST", url, false);
    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //Call a function when the state changes.
    http.onreadystatechange = function() {
        if(http.readyState == 4 && http.status == 200) {
            postMessage({id: action});
        }
    }
    http.send(params);
}

    for(i=0;i<e.data.length;i++){


    }
};
