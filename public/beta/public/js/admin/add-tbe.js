$(document).ready(function ()
{  
    $("body").on("click","#addTbe",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var tbeName=$.trim($('#tbeName').val());
        var tbeMobile=$.trim($('#tbeMobile').val());
        var tbeCMobile=$.trim($('#tbeCMobile').val());
        var tbeEmail=$.trim($('#tbeEmail').val());
        var taid=$.trim($('#htaid').val());
        var gtaid=$.trim($('#hgtaid').val());
       
        if(tbeName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Name");
            return  false;
        }
        if(tbeCMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Mobile");
            return  false;
        }
        if(tbeMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please Confirm Mobile");
            return  false;
        }
        if(tbeMobile != tbeCMobile){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Mobile number confirmation do not match");
            return  false;
        }
        /* if(tbeEmail == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Email");
            return  false;
        } */
                   
        var formData=new FormData();

        formData.append("name",tbeName);
        formData.append("mobile",tbeMobile);
        formData.append("email",tbeEmail);
        formData.append("ta_id",gtaid);

        ajaxData('/a_dMin/add-tbe',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/tbe-list/"+taid;
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});