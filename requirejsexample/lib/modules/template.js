define(['jquery'], function($) {
    var createMeth = function(text) {
        console.log("hello from the other side" +text);

        $('project').on('select2:select', function (evt) {
        });
    };
    return {
        test: createMeth
    };
});/**
 * Created by fs11239 on 12/14/2016.
 */

