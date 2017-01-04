/**
 * Created by fs11239 on 11/21/2016.
 */
$( document ).ready(function() {

    var dataURL = "dashboard_grid.php";
    var win_width = $(window).width();
    var column_width = ((win_width*.90)/12);
    var column_width = 20;
    function commaSeparateNumber(val){
        while (/(\d+)(\d{3})/.test(val.toString())){
            val = val.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
        }
        var symbol = "";
        if($(".rpt_type").val() == "dollars")
        {
            symbol = "$";
        }
        return "<td class = 'font_class'>"+symbol+val+"</td>";
    }
    function roundandcolor(val){
        var numb = val;
        numb = numb.toFixed(2);
        return "<td class = 'font_class'>"+numb+"</td>";
    }

    var columnData = [
        {
            name        : "s_cum",
            title       : "BCWS<sub>Cum</sub>",
            css         : "grid_header",
            type        : "number",
            width       : column_width,
            cellRenderer: commaSeparateNumber
        },{
            name        : "p_cum",
            title       : "BCWP<sub>Cum</sub>",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber

        },{
            name        : "a_cum",
            title       : "ACWP<sub>Cum</sub>",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber

    },{
            name     : "sv",
            title    : "SV",
            type     : "number",
            width    : column_width,
            css: "grid_header",
            cellRenderer: commaSeparateNumber

    },{
            name        : "cv",
            title       : "CV",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber

    },{
            name : "bac",
            title: "BAC",
            type : "number",
            width: column_width,
            css: "grid_header",
        cellRenderer: commaSeparateNumber

    },{
            name        : "eac",
            title       : "EAC",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber

    },{
            name        : "bcwr",
            title       : "BCWR",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber

    },{
            name        : "etc",
            title       : "ETC",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber

    },{
            name        : "cbb",
            title       : "CBB",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber
    },{
            name        : "vac_bac",
            title       : "VAC<sub>bac</sub>",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber
    },{
            name        : "vac_cbb",
            title       : "VAC<sub>cbb</sub>",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: commaSeparateNumber
        }]
    var columnCostIndicesData = [
        {
            name        : "cpi",
            title       : "CPI",
            css         : "grid_header",
            type        : "number",
            width       : column_width,
            cellRenderer: roundandcolor
        },{
            name        : "spi",
            title       : "SPI",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: roundandcolor

        },{
            name        : "spi_loe",
            title       : "SPI<sub>loe</sub>",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: roundandcolor

        },{
            name     : "loe",
            title    : "% loe",
            type     : "number",
            width    : column_width,
            css: "grid_header",
            cellRenderer: roundandcolor

        },{
            name        : "tcpi",
            title       : "TCPI",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: roundandcolor

        },{
            name : "tcpi_cpi",
            title: "TCPI-CPI",
            type : "number",
            width: column_width,
            css: "grid_header",
            cellRenderer: roundandcolor

        },{
            name        : "mr",
            title       : "Mgt Reserve",
            type        : "number",
            width       : column_width,
            css         : "grid_header",
            cellRenderer: roundandcolor

        }]
    $("#costData").jsGrid({
        sorting : false,
        height  : 60,
        width : "100%",
        paging  : false,
        autoload: false,
        controller: {
            loadData: function() {
                var d = $.Deferred();

                $.ajax({
                    url : dataURL+"/cost_data",
                    dataType: "json",
                    data : {
                        grid    : "cost_data",
                        project : $(".project").val(),
                        cam     : $(".cam").val(),
                        ca      : $(".ca").val(),
                        period  : $(".period").val(),
                        rpt_type: $(".rpt_type").val()
                    }
                }).done(function(response) {
                    d.resolve(response.value);
                });

                return d.promise();
            }
        },

        fields: columnData
    });

    $("#costIndices").jsGrid({
        sorting : false,
        height  : 60,
        width : "100%",
        paging  : false,
        autoload: false,
        controller: {
            loadData: function() {
                var d = $.Deferred();

                $.ajax({
                    url : dataURL+"/cost_indices",
                    dataType: "json",
                    data : {
                        grid    : "cost_indices",
                        project : $(".project").val(),
                        cam     : $(".cam").val(),
                        ca      : $(".ca").val(),
                        period  : $(".period").val(),
                        rpt_type: $(".rpt_type").val()
                    }
                }).done(function(response) {
                    d.resolve(response.value);
                });

                return d.promise();
            }
        },

        fields: columnCostIndicesData
    });
//$("#costIndices").hide();
});