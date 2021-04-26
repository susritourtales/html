$(document).ready(function ()
{
    $("body").on("click","#editNotify",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var subText=$.trim($('#subTxt').val());
        var smsText=$.trim($('#smsTxt').val());
        var emailText=$.trim($('#emailTxt').val());

        if(subText == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter subject");
            return  false;
        }

        if(smsText == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter sms text");
            return  false;
        }
        
        if(emailText=='')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter city name");
            return  false;
        }
        
        var formData=new FormData();
        var uid=$('#huid').val();
        formData.append("sub",subText);
        formData.append("sms",smsText);
        formData.append("email",emailText);

        ajaxData('/a_dMin/edit-notify/' + uid,formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/sponsors-list";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});