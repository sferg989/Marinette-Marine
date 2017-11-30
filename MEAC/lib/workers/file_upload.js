/**
 * Created by fs11239 on 1/5/2017.
 */
self.addEventListener('message', function(e) {
    self.postMessage(e.data);

}, false);
this.onmessage = function(e) {
    var control     = e.data.control;
    var ship_code   = e.data.ship_code;
    var file  = e.data.file;
    var files  = e.data.files;

    var formData    = new FormData();

    var file = files[0];
    console.log("this is the size "+ file.size);
    formData.append('open_po', file);
    formData.append("name",file.name);
    formData.append("size", file.size);

    var http      = new XMLHttpRequest();
    var url = "../php/grid.php?control="+control+"&ship_code="+ship_code;
    //var params = "control="+control+"&ship_code="+ship_code;
    http.open("POST", url, true);

//Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var data = {};
            data.id= control;
            data.response= this.responseText;
            postMessage(data);
        }
    }
    http.send(formData);
};
