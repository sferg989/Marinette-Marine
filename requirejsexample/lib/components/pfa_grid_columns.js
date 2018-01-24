/**
 * Created by fs11239 on 2/23/2017.
 */
define(['slickgrid', 'slickdataview'], function(){
    var PFAcolumns= function ()
    {
        shipStatusCols= [
            {
                id                 : "step_status",
                name               : "Done",
                minWidth           : 30,
                maxWidth           : 40,
                cssClass           : "cell-effort-driven",
                field              : "step_status",
                cannotTriggerInsert: true,
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
    }
    return {
        returnColumns: PFAcolumns
    };
})