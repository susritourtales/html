$(document).ready(function ()
{  
    $("body").on("click","#editTbe",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var tbeName=$.trim($('#tbeName').val());
        var tbeMobile=$.trim($('#tbeMobile').val());
        var tbeEmail=$.trim($('#tbeEmail').val());

        if(tbeName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Name");
            return  false;
        }
        if(tbeMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Mobile");
            return  false;
        }
        /* if(tbeEmail == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Email");
            return  false;
        } */
                   
        var formData=new FormData();
        var tbeid=$('#htbeId').val();
        var taid=$('#htaId').val();
        formData.append("name",tbeName);
        formData.append("mobile",tbeMobile);
        formData.append("email",tbeEmail);
        
        ajaxData('/a_dMin/edit-tbe/' + tbeid,formData,function(response){
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