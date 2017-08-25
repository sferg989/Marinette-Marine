
require([
    "../inc/custom_components/createSlickGrid",
    "lib/components/grid_options",
    "../inc/custom_components/get_url",
    "lib/components/grid_columns",
    "lib/components/data",
    "lib/components/filterBTN",
    "bootstrapJS",
    "slickPager"
], function(grid,gridOptions,getUrl, gridColumns,dataService, udf) {
$( document ).ready(function() {

    function gridDataViewCallBack(data){

        shipGridObj.dataView.beginUpdate();

        shipGridObj.dataView.setItems(data);
        shipGridObj.dataView.endUpdate();
        shipGridObj.dataView.refresh();
        shipGridObj.grid.render();
        shipGridObj.grid.updateRowCount();
        $('#my_pager').show();
        $('#meacGrid').show();
    }

    function goBack() {
        window.history.back();
    }
    $("#back_btn").click(function(){
        goBack();
    });
    $("#btn_filter").click(function(){
        udf.setUDF();
    });
    $("#btn_clear").click(function(){
        $("#status").empty();
    });

    $("#btn_group").click(function(){
        var layout_id = $("#layout_list").val();
        udf.getGroupableFields(layout_id);
        udf.getLayoutGroups(layout_id);
    });
    $("#btn_excel_export").click(function(){
        udf.getExcelExportProjectList();

    });
    $("#btn_excel_export_all_fields").click(function(){
        udf.getExcelExportAllFields();
    });
    $("#btn_excel_export_all_projs").click(function(){
        udf.getExcelExportAllFieldsAllProjs();
    });
    $("#btn_excel_exec_summary").click(function(){
        udf.getExcelExexSum();

    });
    $("#btn_excel_export_custom").click(function(){
        udf.getExcelExportCustom();

    });
    $("#btn_cbm_loader").click(function(){
        udf.getCBMLoaderProjectList();

    });
    $("#btn_load_cbm").click(function(){
        udf.loadCBMByProject();

    });
    $("#btn_load_ebom").click(function(){
        udf.loadEBOMByProject();

    });
    function setGridColsCB(columnDefinitions){

        //console.log("this worked");
        //console.log(columnDefinitions);
        shipGridObj.grid.setColumns(columnDefinitions)
    }
    function getNewGridColsDefinitionCB(field_set_name){
        //var columns = shipGridObj.grid.getColumns();
        var columns = gridColumns.GenerateColsFromFieldList(field_set_name, setGridColsCB);
    }
    $('#field_layout_name_save').click(function () {
        var udf_field_set = udf.saveFieldSet(getNewGridColsDefinitionCB);
    });
    $("#layout_list").change(function() {
        getNewGridColsDefinitionCB($(this).find(":selected").text());
        $("#grouped_list").empty();
        /*
        * Call New Data for Grid
        * */
    });
    var shipCols           = gridColumns.cols;
    var projectGridOptions = gridOptions.projectGridOptions;

    var shipGridObj        = grid.createGrid("meacGrid", shipCols, projectGridOptions);
    shipGridObj.dataView.onRowsChanged.subscribe(function (e, args) {
        shipGridObj.grid.invalidateRows(args.rows);
        shipGridObj.grid.render();
    });
    shipGridObj.grid.onDblClick.subscribe(function (e, args){

        var cell                = shipGridObj.grid.getCellFromEvent(e)
        var cols                = shipGridObj.grid.getColumns();
        var wc_string = "where ";
        var gb_string = "group by ";

        var dataItem            = shipGridObj.dataView.getItem(args.row);
        var drill_level    = gridColumns.findDrillLevel(cols[cell.cell].field);

        for (i=0;i<=cell.cell; i++){
            var field_name = cols[i].field;
            var field_val  = dataItem[field_name];
            wc_string += field_name + "='" + field_val + "' and ";
        }
        wc_string          = wc_string.substring(0, wc_string.length - 4);
        for (i=0;i<=cell.cell; i++){
            var field_name = cols[i].field;
            var field_val  = dataItem[field_name];
            gb_string += field_name + ",";
        }
        gb_string          += drill_level.name;
        var url = "lib/php/meac_grid.php";
        var ajaxDataObj     = {};
        ajaxDataObj.control = "part_level_MEAC";
        ajaxDataObj.wc = wc_string;
        ajaxDataObj.gb = gb_string;
        dataService.getData(url, ajaxDataObj,gridDataViewCallBack)


        var newColsOBJ     = gridColumns.getColModelfromDrillLevel(drill_level.index);
        var field_set_name = $("#layout_list").find(":selected").text();
        var dataCols       = gridColumns.GenerateDataColsFromFieldList(field_set_name, newColsOBJ, setGridColsCB);

        /*get the next level*/
        //console.log(wc_string);
    });

    shipGridObj.dataView.setPagingOptions({
        inlineFilters            : true,
        pageSize: 25
    });
    var ajaxDataObjLayout     = {};
    ajaxDataObjLayout.control = "layout_list";

    dataService.getLayoutList(ajaxDataObjLayout, gridDataViewCallBack, getNewGridColsDefinitionCB);


    var pager = new Slick.Controls.Pager(shipGridObj.dataView, shipGridObj.grid, $("#my_pager"));

    shipGridObj.grid.setSelectionModel(new Slick.RowSelectionModel({
        selectActiveRow: true
    }));
    shipGridObj.dataView.onRowCountChanged.subscribe(function (e, args) {
        shipGridObj.grid.updateRowCount();
        shipGridObj.grid.render();
    });
    shipGridObj.dataView.onRowsChanged.subscribe(function (e, args) {
        shipGridObj.grid.invalidateRows(args.rows);
        shipGridObj.grid.render();
    });
    shipGridObj.grid.onCellChange.subscribe(function (e,args) {
        console.log(args);

    });
    shipGridObj.dataView.onPagingInfoChanged.subscribe(function (e, pagingInfo) {
        var isLastPage = pagingInfo.pageNum == pagingInfo.totalPages - 1;
        var enableAddRow = isLastPage || pagingInfo.pageSize == 0;
        var options = shipGridObj.grid.getOptions();
        if (options.enableAddRow != enableAddRow) {
            shipGridObj.grid.setOptions({enableAddRow: enableAddRow});
        }
    });


});
});
    /**
 * Created by fs11239 on 4/3/2017.
 */
