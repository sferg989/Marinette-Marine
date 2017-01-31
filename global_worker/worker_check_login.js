/**
 * Created by fs11239 on 1/30/2017.
 */
/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {
    var action     = e.data;
    var http = new XMLHttpRequest();

    var url = "check_login.php";
    var params = "control="+action+"";
    http.open("POST", url, true);
//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var info = {};
            var data_reponse, user, role, hulls,login;
            var data_response = this.responseText.split(",");
            login          = data_response[0];
            role          = data_response[1];
            user          = data_response[2];
            hulls          = data_response[3];
            info.login      = login;
            info.user      = user;
            info.role      = role;
            info.hulls     = hulls;
            postMessage(info);
        }
    }
    http.send(params);
};
