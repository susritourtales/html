$(document).ready(function ()
{  
    $("body").on("click","#buyPlan",function(){
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var ta_ids=$('#selTAC').val();
        var tp_id=$('#selTP').val(); 
       
        if(ta_ids == '0'){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please select TAC");
            return  false;
        }
        if(tp_id == '0'){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please select Plan");
            return  false;
        }
                   
        var formData=new FormData();

        formData.append("ids",ta_ids);
        formData.append("ta_plan_id",$('#hpid').val());
        formData.append("tac",$('#htac').val());
        formData.append("ta_plan_cost",$('#tbCost').val());
        formData.append("mtc",$('#tbMTC').val());

        ajaxData('/a_dMin/buy-plan',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/ta-list";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});
function tpChanged(sel){
    var valArr = sel.value.split(",");
    $('#tbMTC').val(valArr[0]);
    $('#tbCost').val(valArr[1]);
    $('#hpid').val(valArr[2]);
}
function tacChanged(sel){
    $('#htac').val($( "#selTAC option:selected" ).text());
    var vlArr = sel.value.split(",");
    $('#selSE').val(vlArr[2]);
}