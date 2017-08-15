/**
 * Created by fs11239 on 4/4/2017.
 */
define(["bootbox", "./data", "jQuerysortable"], function(bootbox, dataService){
/*    document.getElementById('btn_filter').onclick=function(){
        console.log("this is a BTN");
    }*/
function ajustamodal() {
    $(".ativa-scroll").css({"height":300,"overflow-y":"auto"});
}
function excelExportCallBack(d){
    console.log(d);
    window.open(d);
}
function filterFieldList() {
    // Declare variables
    var input, filter, table, tr, td, i;
    input  = document.getElementById("filterInput");
    filter = input.value.toUpperCase();
    table  = document.getElementById("field_table");
    tr     = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function createFieldSet(field_list, field_set_name){
    $.ajax({
        url     : "lib/php/meac_grid.php",
        data: {
            control       : "save_field_list",
            field_set_name: field_set_name,
            field_list    : field_list
        },
        success : function (data) {
            //console.log(data);
            $('#layout_list').append($('<option>').text(field_set_name).attr('value', data));
            $('select option[value="'+data+'"]').attr("selected",true);
        }
    });
    return field_list;
}
function checkIfFieldListNameExists(field_set_name, field_list, createFieldSet){
    return $.ajax({
        url     : "lib/php/meac_grid.php",
        data: {
            control       : "field_layout_name_check",
            field_set_name: field_set_name
        },
        success : function (data) {
            if(data=="false"){
                createFieldSet(field_list, field_set_name);
                bootbox.alert("The Layout was Created");
                return field_list;
            }
            else{
                bootbox.alert("Please Rename the Filter Set!");
                return false;
            }
        }
    });
}

var saveFieldSet = function(getNewGridColsDefinitionCB){
    var field_list = ""
    var field_set_name = $("#field_layout_name").val();
    if(field_set_name==""){
        bootbox.alert("Please enter a Valid Field Layout Name!");
        return false;
    }
    $('#field_table').find('input[type="checkbox"]:checked').each(function () {
        field_list += this.name+",";
    });
    field_list = field_list.slice(0, -1);
    if(field_list==""){
        bootbox.alert("Please Select Fields");
        return false;
    }
    var check = checkIfFieldListNameExists(field_set_name, field_list, createFieldSet)
    setTimeout(function(){
        if(check.responseText=="false"){
            getNewGridColsDefinitionCB(field_set_name);
        }
    else{
            return "false";
        }
    }, 1000);
}

$("#foo").sortable({
    group: 'no-drop',
    drop: true,
    drag: true
});
$("#grouped_list").sortable({
    group: 'no-drop2',
    drop: true,
    drag: true
});

$("#filterInput").keyup(function() {
    filterFieldList();
});

var getLayoutGroups = function (layout_id){
    var count = $('ul#grouped_list li').length;
    if(count >0){
        //var curr_list = grouped_list2.store
        var count = $('ul#grouped_list li').length;
        console.log(count);

        $("#grouped_list").each(function( index ) {
            console.log( index + ": " + $( this ).text() );
        });
        return true
    }else{
        $.ajax({
            url     : "lib/php/meac_grid.php",
            data: {
                control   : "layout_groups",
                layout_id : layout_id
            },
            success : function (data) {
                $("#grouped_list").append(data);

            }
        });
    }
}


var getGroupableField= function (layout_id){
    $.ajax({
        url     : "lib/php/meac_grid.php",
        data: {
            control   : "groupable_fields",
            layout_id : layout_id
        },
        success : function (data) {
            $("#grouped_list").append(data);

        }
    });
}
var getFieldList = function (){
    if($("#field_table").length > 0){
        return true;
    }
    else{
        $.ajax({
            url     : "lib/php/meac_grid.php",
            data: {
                control   : "field_list"
            },
            success : function (data) {
                $("#field_list").append(data);
            }
        });
    }
    ajustamodal();
}
var getExcelExportProjectList = function (){
    if($("#excel_export_table").length > 0){
        return true;
    }
    else{
        $.ajax({
            url     : "lib/php/meac_excel_export.php",
            data: {
                control   : "excel_export_list"
            },
            success : function (data) {
                $("#excel_export_list").append(data);
            }
        });
    }
    ajustamodal();
}
var getExcelExportAllFields = function (){

    $('#excel_export_table').find('input[type="checkbox"]:checked').each(function () {
        var worker;
        var ajaxDataObj     = {};
        ajaxDataObj.control = "excel_export";
        ajaxDataObj.wc     = "where ship_code = " + this.name;
        ajaxDataObj.name = "Exporting " + this.name;
        $("#status").append("<div id = \"img_"+ajaxDataObj.control+"\"><br><img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+ajaxDataObj.name+"<br></div>");
        workers     = new Worker("lib/workers/excel_export.js");
        workers.onmessage = workerDone;
        workers.postMessage(ajaxDataObj);
        function workerDone(e) {
            e.data +="";
            if(e.data!= ""){
                $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
                excelExportCallBack(e.data.response)
            }
            console.log(e.data+" has completed");
        }
    });
}
var getExcelExportAllFieldsAllProjs = function (){

    var worker;
    var ajaxDataObj     = {};
    ajaxDataObj.control = "excel_export";
    ajaxDataObj.name = "Exporting All Projects! ";
    $("#status").append("<div id = \"img_"+ajaxDataObj.control+"\"><br><img src=\"../inc/images/ajax-loader.gif\" height=\"32\" width=\"32\"/>"+ajaxDataObj.name+"<br></div>");
    workers     = new Worker("lib/workers/excel_export.js");
    workers.onmessage = workerDone;
    workers.postMessage(ajaxDataObj);
    function workerDone(e) {
        e.data +="";
        if(e.data!= ""){
            $("#img_"+e.data.id+" img").attr("src", "../images/tick.png");
            excelExportCallBack(e.data.response)
        }
        console.log(e.data+" has completed");
    }
}
var getExcelExexSum = function (){
    var ajaxDataObj     = {};
    ajaxDataObj.control = "excel_exec_sum";
    var url  =  "lib/php/meac_excel_export.php";

    $('#excel_export_table').find('input[type="checkbox"]:checked').each(function () {
        ajaxDataObj.wc        = "where ship_code = " + this.name;
        //ajaxDataObj.gb = "group by program, ship_code,swbs_group, swbs, wp,item";
        ajaxDataObj.ship_code = this.name;
        dataService.excelProjectExport(url,ajaxDataObj,excelExportCallBack);
    });

}
var getExcelExportCustom= function (){
    var ajaxDataObj     = {};
    var layout_id = $("#layout_list").val();
    console.log(layout_id)
    ajaxDataObj.control     = "excel_export_custom";
    ajaxDataObj.layout_id   = layout_id;
    var url  =  "lib/php/meac_excel_export.php";

    $('#excel_export_table').find('input[type="checkbox"]:checked').each(function () {
        ajaxDataObj.wc = "where ship_code = "+this.name;
        //ajaxDataObj.gb = "group by program, ship_code,wp,item";
        dataService.excelProjectExport(url,ajaxDataObj,excelExportCallBack);
    });

}

    return {
        setUDF                         : getFieldList,
        saveFieldSet                   : saveFieldSet,
        getLayoutGroups                : getLayoutGroups,
        getGroupableFields             : getGroupableField,
        getExcelExportProjectList      : getExcelExportProjectList,
        getExcelExportAllFields        : getExcelExportAllFields,
        getExcelExexSum                : getExcelExexSum,
        getExcelExportAllFieldsAllProjs: getExcelExportAllFieldsAllProjs,
        getExcelExportCustom           : getExcelExportCustom
    };
});