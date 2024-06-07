function common(){}
common.NAME         = "common";
common.VERSION      = "1.2";
common.DESCRIPTION  = "Class common";

common.prototype.constructor = common;
common.prototype = {
    init:function(){
        $("#greatform_id").on("click", ".cost_cost", function(){
            var cost =  $(this).val();
            console.log("cost="+cost);
            var container_cost = 0;
            $("#commodityTable .container_cost").each(function(){
                alert();
                //container_cost = $(this).val();
                //console.log("contaner ="+container_cost);
            })

        });
    }
}
var cm = new common();
$(function(){
    cm.init();
});