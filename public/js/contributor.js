$(document).ready(function () {
    $("body").on("click",'#submit',function () {
        var nameElement=$("#name");
        var mobileElement=$("#mobile");
        var emailElement=$("#email");
        var messageElement=$("#message");

        var name=nameElement.val();
        var mobile=mobileElement.val();
        var email=emailElement.val();
        var message=messageElement.val();
        var element=$(this);
        element.prop('disabled','disabled');
        element.html("Please Wait...");
        postData('/user/index/send-contributor',{"name":name,'email':email,'mobile':mobile,'message':message},function (response) {
            response=parseJsonData(response);
            console.log(response,response.message);
            messageDisplay(response.message);
            if(response.success)
            {
                setTimeout(function () {
                    element.html("submit");
                    window.location.reload();
                },2000);
            }else{
                element.prop('disabled','');
            }

        });
    });
});