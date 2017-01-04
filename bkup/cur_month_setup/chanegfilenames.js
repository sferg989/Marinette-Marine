/**
 * Created by fs11239 on 12/6/2016.
 */
$(document).ready(function() {
    var myURL = "cur_month_setup.php/";

    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results==null){
            return null;
        }
        else{
            return results[1] || 0;
        }
    }
    console.log("this worked"+$.urlParam('ship_code'));
    function moveDirs()
    {
        var ship_code_list = $(".project").val().join();
        $.ajax({
            url: myURL,
            data : {
                control       : "move_directories",
                ship_codes    : ship_code_list,
                previousperiod: $("#start_period").val(),
                curperiod     : $("#end_period").val()
            },
            dataType: "json"
        })
    }
    $(".project").select2({
        //minimumResultsForSearch: -1,
        width : 250,
        allowClear : true,
        placeholder: "Select Project",
        ajax: {
            url: myURL+"/project",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    control: "project",
                    q      : params.term, // search term
                    page   : params.page
                };
            },
            processResults: function (data, page) {
                // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data
                return {
                    results: data.items
                };
            },
            cache: true
        }
    });

    $("#submit").click(function() {
        var start_val  = $("#start_period").val();
        var end_val    = $("#end_period").val();
        var ship_codes = $(".project").val();
        if(ship_codes=="")
        {
            alert("Please Select a Ship to Copy");
            return false;
        }
        if(start_val>end_val)
        {
            alert("The End Period Cannot be later than the Current Period")
            return false;
        }
        if(start_val=="" || end_val== "")
        {
            alert("Please make a Starting Period or Ending selection");
            return false;
        }
        moveDirs()
    });
    $(".project").select2('data', { id:0473, text: "LCS13"});
});
