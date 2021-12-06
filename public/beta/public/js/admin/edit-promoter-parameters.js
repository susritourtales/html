$(document).ready(function(){
    
    $("body").on("click","#updateParameters",function(){
        var pc=$.trim($("#pwd_ceiling").val());
       /*  var mpp=$.trim($("#min_pay_pwds").val()); */
        var app=$.trim($("#amt_per_pwd").val());
        var rc=$.trim($("#redeem_ceiling").val());
        var pap=$.trim($("#pay_after_pwds").val());

        if(pc=='')
        {
            messageDisplay("Please enter password ceiling");
            return false;
        }
        /* if(mpp=='')
        {
            messageDisplay("Please enter Min. pay passwords");
            return false;
        }  */
        
        if(app=='')
        {
            messageDisplay("Please enter Amount per password");
            return false;
        }

        if(rc=='')
        {
            messageDisplay("Please enter redeem ceiling");
            return false;
        }

        if(pap=='')
        {
            messageDisplay("Please enter Pay after passwords");
            return false;
        }

        var formData=new FormData();
        var pid=$("#ppid").val();
        formData.append("pc",pc);
        /* formData.append("mpp",mpp); */
        formData.append("app",app);
        formData.append("rc",rc);
        formData.append("pap",pap);
        formData.append("pid",pid);
                
        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
        ajaxData('/a_dMin/edit-promoter-parameters',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/promoter-parameters";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});