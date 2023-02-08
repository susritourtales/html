$(document).ready(function ()
{  
    $("body").on("click","#addTac",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var tacName=$.trim($('#tacName').val());
        var tacMobile=$.trim($('#tacMobile').val());
        var tacCMobile=$.trim($('#tacCMobile').val());
        var tacEmail=$.trim($('#tacEmail').val());
        var tacAdd=$.trim($('#tacAdd').val());
        var bName=$.trim($('#bName').val());
        var ifsc=$.trim($('#ifsc').val());
        var ban=$.trim($('#ban').val());
        var cban=$.trim($('#Cban').val());
        var pic=$.trim($('#pic').val());
        var comm=$.trim($('#comm').val());

        if(tacName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Name");
            return  false;
        }
        if(tacCMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Mobile");
            return  false;
        }
        if(tacMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please Confirm Mobile");
            return  false;
        }
        if(tacMobile != tacCMobile){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Mobile number confirmation do not match");
            return  false;
        }
        if(tacEmail == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Email");
            return  false;
        }
        if(tacAdd == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter address");
            return  false;
        }
        if(bName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Bank Name");
            return  false;
        }
        if(ifsc == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter IFSC Code");
            return  false;
        }
        if(cban == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter Bank Account No");
            return  false;
        }
        if(ban == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please confirm Bank Account No");
            return  false;
        }
        if(ban != cban){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Bank account number confirmation do not match");
            return  false;
        }
        if(pic == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter PIC");
            return  false;
        }
        if(comm == '')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter commission");
            return  false;
        }
           
        var formData=new FormData();

        formData.append("name",tacName);
        formData.append("mobile",tacMobile);
        formData.append("email",tacEmail);
        formData.append("address",tacAdd);
        formData.append("bank_name",bName);
        formData.append("ifsc_code",ifsc);
        formData.append("ban",ban);
        formData.append("pic",pic);
        formData.append("commission",comm);

        ajaxData('/a_dMin/add-ta-cons',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/ta-cons";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});