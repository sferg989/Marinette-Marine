/**
 * Created by fs11239 on 2/23/2017.
 */
define(["jquery", "bootbox"],function($, bootbox){
    var loadingIndicator = null;
    var url = "lib/php/grid.php?control=upload_v2";
    $("#submit_open_po").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('open_po');
        var uploadButton = document.getElementById('submit_open_po');
        var statusDiv    = document.getElementById('status');
        var ship_code    = $("#ship_code").val();
        var rpt_period   = $("#rpt_period").val();

        event.preventDefault();
        // Get the files from the input
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        //Grab just one file, since we are not allowing multiple file uploads
        var file = files[0];
        //Check the file type

        if (ship_code == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (rpt_period == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (file.size >= 5000000 ) {
            bootbox.alert("The File is too Big!")
            return false;
        }
// Add the file to the request.
        formData.append('open_po', file, file.name);
        // Set up the AJAX request.
        var xhr = new XMLHttpRequest();
        // Open the connection.
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#div_file_upload_div");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        xhr.open('POST', url+'&ship_code='+ship_code+'&rpt_period='+rpt_period, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                loadingIndicator.fadeOut();
            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };

        // Send the Data.
        xhr.send(formData, function (){

        });
    });

    $("#submit_committed_po").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('committed_po');
        var uploadButton = document.getElementById('submit_open_po');
        var statusDiv    = document.getElementById('status');
        var ship_code    = $("#ship_code").val();
        var rpt_period   = $("#rpt_period").val();

        event.preventDefault();
        // Get the files from the input
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        //Grab just one file, since we are not allowing multiple file uploads
        var file = files[0];
        //Check the file type

        if (ship_code == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (rpt_period == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (file.size >= 5000000 ) {
            bootbox.alert("The File is too Big!")
            return false;
        }
// Add the file to the request.
        formData.append('committed_po', file, file.name);
        // Set up the AJAX request.
        var xhr = new XMLHttpRequest();
        // Open the connection.
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#div_file_upload_div");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        xhr.open('POST', url+'&ship_code='+ship_code+'&rpt_period='+rpt_period, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                loadingIndicator.fadeOut();
            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };
        // Send the Data.
        xhr.send(formData, function (){

        });
    });
    $("#submit_gl_detail").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('gl_detail');
        var uploadButton = document.getElementById('submit_gl_detail');
        var statusDiv    = document.getElementById('status');
        var ship_code    = $("#ship_code").val();
        var rpt_period   = $("#rpt_period").val();

        event.preventDefault();
        // Get the files from the input
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        //Grab just one file, since we are not allowing multiple file uploads
        var file = files[0];
        //Check the file type

        if (ship_code == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (rpt_period == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (file.size >= 5000000 ) {
            bootbox.alert("The File is too Big!")
            return false;
        }
// Add the file to the request.
        formData.append('gl_detail', file, file.name);
        // Set up the AJAX request.
        var xhr = new XMLHttpRequest();
        // Open the connection.
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#div_file_upload_div");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        xhr.open('POST', url+'&ship_code='+ship_code+'&rpt_period='+rpt_period, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                loadingIndicator.fadeOut();
            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };
        // Send the Data.
        xhr.send(formData, function (){

        });
    });
    $("#submit_open_buy").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('open_buy');
        var uploadButton = document.getElementById('submit_open_buy');
        var statusDiv    = document.getElementById('status');
        var ship_code    = $("#ship_code").val();
        var rpt_period   = $("#rpt_period").val();

        event.preventDefault();
        // Get the files from the input
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        //Grab just one file, since we are not allowing multiple file uploads
        var file = files[0];
        //Check the file type

        if (ship_code == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (rpt_period == null) {
            bootbox.alert("Please Select a Ship")
            return false;
        }
        if (file.size >= 5000000 ) {
            bootbox.alert("The File is too Big!")
            return false;
        }
// Add the file to the request.
        formData.append('open_buy', file, file.name);
        // Set up the AJAX request.
        var xhr = new XMLHttpRequest();
        // Open the connection.
        if (!loadingIndicator) {
            loadingIndicator = $("<span class='loading-indicator'><label>Loading...</label></span>").appendTo(document.body);
            var $g = $("#div_file_upload_div");
            loadingIndicator
                .css("position", "absolute")
                .css("top", $g.position().top + $g.height() / 2 - loadingIndicator.height() / 2)
                .css("left", $g.position().left + $g.width() / 2 - loadingIndicator.width() / 2);
        }
        loadingIndicator.show();
        xhr.open('POST', url+'&ship_code='+ship_code+'&rpt_period='+rpt_period, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                loadingIndicator.fadeOut();
            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };
        // Send the Data.
        xhr.send(formData, function (){

        });
    });

})/**
 * Created by fs11239 on 4/11/2017.
 */
