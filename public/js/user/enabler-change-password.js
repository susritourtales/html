$(document).ready(function(){
    var ajaxCall=null;
    $("body").on("click","#Y2hhbmdlUGFzc3dvcmQ",function(){

          var currentPassword=$("#Y3VycmVudFBhc3N3b3Jk").val();
          var newPassword=$("#bmV3UGFzc3dvcmQ").val();
          var confirmPassword=$("#Y29uZmlybVBhc3N3b3Jk").val();

          if(currentPassword=="")
          {
              messageDisplay("Please enter the current password");
              return false;
          }
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
        formData.append('current_password',currentPassword);
        formData.append('new_password',newPassword);
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/twistt/enabler/reset-password',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(data.message, 2000);
                ajaxCall=null;
                setTimeout(function(){
                    window.location.href=BASE_URL+"/twistt/enabler/buy-plans";
                },2000);
            },
            error: function()
            {
                ajaxCall=null;
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(data.message, 2000);
            }
        });
    });

});