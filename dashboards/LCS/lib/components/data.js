/**
 * Created by fs11239 on 2/23/2017.
 */
define(function(){
    var getData = function (url, ajax_data_object, gridDataView) {
        $.ajax({
            dataType: "json",
            url     : url,
            data    : ajax_data_object,
            success: function(data) {
                var groups = _(data).groupBy('level');
                var out = _(groups).map(function(g, key) {
                    return {
                        id   : "LCS ROLL up",
                        level: "Program",
                        s    : _(g).reduce(function (m, x) {return m + x.s;}, 0),
                        p    : _(g).reduce(function (m, x) {return m + x.p;}, 0),
                        a    : _(g).reduce(function (m, x) {return m + x.a;}, 0),
                        sv    : _(g).reduce(function (m, x) {return m + x.sv;}, 0),
                        cv    : _(g).reduce(function (m, x) {return m + x.cv;}, 0),
                        ub    : _(g).reduce(function (m, x) {return m + x.ub;}, 0)
                    };
                });
                data.push(out[0]);
                //console.log("from the Ajax",data);
                return data;

            },

        }).done(function (data){
            gridDataView(data);
            return data;
        });

    }
    return {
        getData  : getData
    };
})/**
 * Created by fs11239 on 4/11/2017.
 */
