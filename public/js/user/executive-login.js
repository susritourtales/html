$(document).ready(function() {
    $('#divMail').hide();
    $('#mobile').width($('#password').width());
    // Initialize intlTelInput
    var input = document.querySelector("#mobile");
    const errorMsg = document.querySelector("#error-msg");
    const validMsg = document.querySelector("#valid-msg");
    const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

    var iti = window.intlTelInput(input, {
      strictMode:true,
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
      initialCountry: "in",
      onlyCountries: ["in"],
      separateDialCode: true,
      hiddenInput: () => ({ phone: "full_phone", country: "country_code" }),
    });

    const reset = () => {
        input.classList.remove("error");
        errorMsg.innerHTML = "";
        errorMsg.classList.add("hide");
        validMsg.classList.add("hide");
    };
    const showError = (msg) => {
        input.classList.add("error");
        errorMsg.innerHTML = msg;
        errorMsg.classList.remove("hide");
    };

    document.getElementById('mobile').addEventListener('input', function(e) {
        var inputValue = e.target.value;
        e.target.value = inputValue.replace(/[^0-9]/g, '');
    });

    $("body").on("focusout","#mobile",function(){
        reset();
        if (!input.value.trim()) {
            showError("Please enter valid mobile number");
        } else if (iti.isValidNumber()) {
            validMsg.classList.remove("hide");
        } else {
            const errorCode = iti.getValidationError();
            const msg = errorMap[errorCode] || "Invalid number";
            showError(msg);
        }
    }).on("click", "#btnFP", function() {
        window.location.href = BASE_URL + '/twistt/executive/forgot-password';
    }).on("click", "#btnLogin", function() {
        var loginid = "";
        if($('#divMobile').is(':visible')){
            if(!$('#mobile').val()){
                messageDisplay("please enter valid mobile number", 2000);
                return;
            }else{
                loginid=iti.getNumber();
            }
        }else{
            if(!$('#email').val()){
                messageDisplay("please enter valid email id", 2000);
                return;
            }else{
                loginid=$("#email").val();
            }
        }
        if(!$('#password').val()){
            messageDisplay("please enter password", 2000);
            return;
        }
        var password = $("#password").val();
        var formData=new FormData();
        formData.append("loginid", loginid);
        formData.append("password", password);
        ajaxData('/twistt/executive/stt-auth',formData,function(response){
            if(response.success){
                messageDisplay(response.message, 2000);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/twistt/executive/buy-coupons";
                },3000);
            }else{
                messageDisplay(response.message,3000);
            }
        });
    }).on("click", "#loginType", function() {
        $('#divMobile').toggle();
        $('#divMail').toggle();
        if($('#divMobile').is(':visible')){
            $('#loginType').text('Login with Email id');
        }else{
            $('#loginType').text('Login with Mobile');
        }
    });
}); 