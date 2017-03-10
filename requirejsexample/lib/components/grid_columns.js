/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    shipStatusCols= [
        {
            id                 : "step_status",
            name               : "Done",
            minWidth           : 30,
            maxWidth           : 40,
            field              : "step_status",
            sortable           : true
        },{
            id       : "wi",
            name     : "Work Instruction",
            field    : "wi"
        },{
            id   : "timeline",
            name : "Time Line 2",
            field: "timeline"
        },{
            id    : "pfa_notes",
            name  : "PFA Notes",
            field : "pfa_notes"
        }];
    return {
            columnData : shipStatusCols
    };
})