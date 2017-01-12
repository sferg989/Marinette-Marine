/**
 * Created by fs11239 on 1/10/2017.
 */
console.log("yes this worked");
var url = "decision_tree.php";
var answer_tracker = {};
(function () {
    $.ajax({
        url     : url,
        data: {
            control   : "cv",
            id        : 1
        },
        success: function (json) {
            var data   = json.split(",");
            var q      = data[0];
            var yes_id = data[1];
            var no_id  = data[2];
            console.log(q);
            $("#question_div").append(q+"");
            createBTN("yes","YES");
            createBTN("no","NO");
            updateBTN("yes",yes_id,1);
            updateBTN("no",no_id,1);
        }
    });
})();

function nextStep(id_clicked,btn_name,answer_tracker)
{
    var html = "";
    if(btn_name=="yes")
    {
        html = "<img src='../monthly_processing/images/tick.png' height='25' width='25'/>";
    }
    if(btn_name=="no")
    {
        html = "<img src='../inc/images/Delete.png' height='25' width='25'/>";
    }

    $("#question_div").append("&nbsp&nbsp"+btn_name+"&nbsp&nbsp"+html+"<br><br>");
    destroyBTN("yes");
    destroyBTN("no");
    var myJSON = JSON.stringify(answer_tracker);
    $.ajax({
        url     : url,
        data: {
            control       : "cv",
            id            : id_clicked,
            answer_tracker: myJSON
        },
        success: function (json) {
            if(id_clicked=="end")
            {
                $("#question_div").append(json);
                createBTN("rtn","Return to Beginning.");
                updateRTNBTN("rtn",no_id);
            }
            else{
                var data   = json.split(",");
                var q      = data[0];
                var yes_id = data[1];
                var no_id  = data[2];
                console.log(q);
                $("#question_div").append(q+"");
                createBTN("yes","YES");
                createBTN("no","NO");
                updateBTN("yes",yes_id,id_clicked);
                updateBTN("no",no_id,id_clicked);
            }
        }
    });
}
function destroyBTN(id)
{
    $("#"+id+"_btn_div").empty();
}
function createBTN(name,saying) {
    var btn_css= "";
    switch(name) {
        case "yes":
            btn_css = "success"
            break;
        case "no":
            btn_css = "warning"
            break;
        case "rtn":
            btn_css = "primary"
            break;
    }
    var r= $("<button class ='btn-lg btn-"+btn_css+" btn-block' type='button' id='"+name+"_button'>"+saying+"</button>");
    $("#"+name+"_btn_div").append(r);
}

function updateBTN(btn_name,new_id,prev_id)
{
    $("#"+btn_name+"_button").click(function(){
        //alert("Yes this worked "+new_id);
        answer_tracker[prev_id] = btn_name;
        nextStep(new_id,btn_name,answer_tracker)
    });
}
function updateRTNBTN(btn_name,new_id)
{
    $("#"+btn_name+"_button").click(function(){
        destroyBTN("rtn");
        $("#question_div").empty();
        (function () {
            $.ajax({
                url     : url,
                data: {
                    control   : "cv",
                    id        : 1
                },
                success: function (json) {
                    answer_tracker = {};
                    var data   = json.split(",");
                    var q      = data[0];
                    var yes_id = data[1];
                    var no_id  = data[2];
                    console.log(q);
                    $("#question_div").append(q+"");

                    createBTN("yes","YES");
                    createBTN("no","NO");
                    updateBTN("yes",yes_id,1);
                    updateBTN("no",no_id,1);
                }
            });
        })();
    });
}
