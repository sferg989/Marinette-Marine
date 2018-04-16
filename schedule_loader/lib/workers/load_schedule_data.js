/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {

    var code      = e.data.code;
    var action    = e.data.action;
    var ppm_ap_id = e.data.ppm_ap;
    var ppm_bl_id = e.data.ppm_bl;
    var http            = new XMLHttpRequest();

    var url = "../php/schedule_loader.php";
    var params = "control="+action+"&ppm_ap_id="+ppm_ap_id+"&code="+code+"&ppm_bl_id="+ppm_bl_id+"";
    http.open("POST", url, true);

//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var data = {};
            data.id= code;
            postMessage(data);
        }
    }
    http.send(params);
};
