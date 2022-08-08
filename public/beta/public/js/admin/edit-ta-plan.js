$(document).ready(function ()
{  
    $("body").on("click","#editTaPlan",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var planName=$.trim($('#planName').val());
        var mtc=$.trim($('#mtc').val());
        var cost=$.trim($('#cost').val());
        var duration=$.trim($('#duration').val());
        var active="1";
        if(!$("#active").is(":checked")){
            active="0";
        }

        if(planName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Plan name");
            return  false;
        }
        if(mtc == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter MTC");
            return  false;
        }
        if(cost == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Cost");
            return  false;
        }
        if(duration == '')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Duration");
            return  false;
        }
           
        var formData=new FormData();
        var tpid=$('#htpid').val();
        formData.append("plan_name",planName);
        formData.append("mtc",mtc);
        formData.append("duration",duration);
        formData.append("cost",cost);
        formData.append("active",active);
        
        ajaxData('/a_dMin/edit-ta-plan/' + tpid,formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/ta-plans";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});