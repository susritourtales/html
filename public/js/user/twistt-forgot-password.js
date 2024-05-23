$(document).ready(function(){
    var ajaxCall=null;
    var element=$(this);
    element.html('Please wait...');
    element.prop('disabled',true);

    $("body").on("click","#sendOtp",function(){
        var element=$(this);
      var mobile=$("#Y3VycmVudFBhc3N3b3Jk").val();
      if(mobile=="")
        {
            messageDisplay("Please enter registered mobile number");
            return false;
        }else{
          if (!(/^\d{10}$/.test(mobile)) && !(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mobile))) {
              element.html('Send Otp');
              element.prop('disabled',false);
              messageDisplay("Please enter valid mobile number or email id");
              return false;
          }
        }
      
      if(ajaxCall!=null)
      {
          ajaxCall.abort();
      }
      
      element.html('Please wait...');
      element.prop('disabled',true);
      var formData=new FormData();
      formData.append('mobile',mobile);
      ajaxCall=  $.ajax({
          type: "POST",
          url: BASE_URL+'/twistt/executive/send-otp',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success: function(data)
          {
              element.prop('disabled',false);
              element.html('Submit');
              messageDisplay(data.message);
              ajaxCall=null;
              if(data.success){
                setTimeout(function(){
                    $("#divSendOtp").addClass("d-none");
                    $("#divVerifyOtp").removeClass("d-none");
                },2000);
            }
          },
          error: function()
          {
              ajaxCall=null;
              element.prop('disabled',false);
              element.html('Submit');
          }
      });
    });
    $("body").on("click","#verifyOtp",function(){
        var element=$(this);
        var otp=$("#txtOtp").val();
        var mobile=$("#Y3VycmVudFBhc3N3b3Jk").val();
        var ccmobile="91" + mobile;;
        var countryCode = "91";
      if(otp=="")
        {
            messageDisplay("Please enter Otp received on your registered mobile");
            return false;
        }
      
      if(ajaxCall!=null)
      {
          ajaxCall.abort();
      }
      
      element.html('Please wait...');
      element.prop('disabled',true);
      var formData=new FormData();
      formData.append('otp',otp);
      formData.append('mobile',mobile);
      ajaxCall=  $.ajax({
          type: "POST",
          url: BASE_URL+'/twistt/executive/verify-otp',
          data: formData,
          cache: false,
          contentType: false,
          processData: false,
          success: function(data)
          {
              element.prop('disabled',false);
              element.html('Submit');
              messageDisplay(data.message);
              ajaxCall=null;
              if(data.success){
                setTimeout(function(){
                    $("#divVerifyOtp").addClass("d-none");
                    $("#divPwdReset").removeClass("d-none");
                },2000);
            }
          },
          error: function()
          {
              ajaxCall=null;
              element.prop('disabled',false);
              element.html('Submit');
          }
      });
    });

    
    $("body").on("click","#Y2hhbmdlUGFzc3dvcmQ",function(){

          var newPassword=$("#bmV3UGFzc3dvcmQ").val();
          var confirmPassword=$("#Y29uZmlybVBhc3N3b3Jk").val();
          var mobile=$("#Y3VycmVudFBhc3N3b3Jk").val();
          
        if(newPassword=="")
        {
            messageDisplay("Please enter the new password");
            return false;
        }

        if(confirmPassword=="")
        {
            messageDisplay("Please enter the confirm password");
            return false;
        }

        if(newPassword!=confirmPassword)
        {
            messageDisplay("new password and confirm password does not match");
            return false;
        }
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        var formData=new FormData();
        formData.append('mobile',mobile);
        formData.append('new_password',newPassword);
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/twistt-reset-password',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(data.message);
                ajaxCall=null;
                if(data.success){
                    setTimeout(function(){
                        window.location.href=BASE_URL+"/twistt";
                    },2000);
                }
            },
            error: function()
            {
                ajaxCall=null;
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });

});