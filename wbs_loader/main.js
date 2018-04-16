
require([
    "lib/components/data",
    "bootbox"
    ], function(dataService, bootbox) {
$( document ).ready(function() {

    var loadingIndicator = null;

    var height = $(window).height();

    $("#submit").click(function(){
        var form         = document.getElementById('file-form');
        var fileSelect   = document.getElementById('myfile');
        var uploadButton = document.getElementById('submit');
        var statusDiv    = document.getElementById('status');
        var ship_code_select = $('#ship_code').val();
        event.preventDefault();
        // Get the files from the input
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        //Grab just one file, since we are not allowing multiple file uploads
        console.log(files);
        var file = files[0];
        console.log(file);
        //Check the file type

/*        if (file.size >= 2000000 ) {
            statusDiv.innerHTML = 'This file is larger than 2MB. Sorry, it cannot be uploaded.';
            return;
        }*/
        // Add the file to the request.
        formData.append('myfile', file, file.name);
        // Set up the AJAX request.
        var xhr = new XMLHttpRequest();
        // Open the connection.
        xhr.open('POST', "lib/php/grid.php?control=upload&ship_code="+ship_code_select, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {

                console.log("this is the call back");
            } else {
                statusDiv.innerHTML = 'An error occurred while uploading the file. Try again';
            }
        };

        // Send the Data.
        xhr.send(formData);

    });

});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
