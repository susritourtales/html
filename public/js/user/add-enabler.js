  $(document).ready(function() {
    $('#divOtp').hide();
    //$('#register').hide();
    $('#preview').hide();
    $('#divCM').hide();
    $('#divCEm').hide();
    $('#divEBtn').show();
    $('#divBtn').show();
    // Initialize intlTelInput
    var input = document.querySelector("#mobile");
    const errorMsg = document.querySelector("#error-msg");
    const validMsg = document.querySelector("#valid-msg");
    const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];

    var iti = window.intlTelInput(input, {
      strictMode:true,
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
      initialCountry: "in",
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
    // Update country code input field
    input.addEventListener("countrychange", function() {
      var countryCode = iti.getSelectedCountryData().dialCode;
      $("#country-code").text(countryCode);
    });

    $('#photo').change(function(event) {
        var file = event.target.files[0];
        if (file && file.type.match('image.*')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#preview').hide();
        }
    });

    const form = document.getElementById('registrationForm');
    form.addEventListener('submit', function(event) {
        if($('#bankAccountConf').val() != $('#bankAccount').val()){
            messageDisplay('Please check the Bank Account number you have entered..', 2000);
            event.preventDefault(); 
            event.stopPropagation(); 
            return;
        }
        const fileInput = document.querySelector('input[type="file"]');
        const maxFileSize = 2 * 1024 * 1024; // 2MB in bytes
        
        if (!form.checkValidity()) {
            event.preventDefault(); 
            event.stopPropagation(); 
        } else {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > maxFileSize) {
                    event.preventDefault(); 
                    alert('The selected file is too large. Please choose a file smaller than 2MB.');
                    return;
                }
            }
            
            event.preventDefault();
            
            var ccmobile=iti.getNumber();
            var countryData = iti.getSelectedCountryData();
            var countryCode = countryData.dialCode; 

            $("#mobile").prop('disabled', false);
            $("#lid").prop('disabled', false);
            const formData = new FormData(form);
            formData.append("ccmobile", ccmobile);
            formData.append("cc", countryCode);
            fetch('/twistt/executive/add', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if(countryCode !== "91"){
                    $("#mobile").prop('disabled', true);
                }
                $("#lid").prop('disabled', false);
                const contentType = response.headers.get('content-type');
                if (response.ok && contentType && contentType.includes('application/json')) {
                    return response.json(); 
                } else if (!response.ok) {
                    return response.json().then(json => {
                        throw new Error(json.message || 'Network response was not ok');
                    });
                } else {
                    return response.text().then(text => {
                        console.error('Unexpected response format:', text);
                        throw new Error('Unexpected response format: ' + text);
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    messageDisplay('Executive registration successfull!');
                    console.log('Executive registration successfull!', data);
                    setTimeout(function(){
                        window.location.href=BASE_URL+"/twistt/change-password";
                    },2000);
                } else {
                    messageDisplay('Error submitting form: ' + data.message);
                    console.error('Error submitting form:', data.message);
                }
            })
            .catch(error => {
                messageDisplay('Error submitting form: ' + error.message);
                console.error('Error submitting form:', error);
            });
        }
        form.classList.add('was-validated');
    });
    $("body").on("click","#btnOtp",function(){
        reset();
        if (iti.isValidNumber()) {
            validMsg.classList.remove("hide");
        } else {
            const errorCode = iti.getValidationError();
            const msg = errorMap[errorCode] || "Invalid number";
            showError(msg);
            return false;
        }
        var mobile=$("#mobile").val();
        var ccmobile=iti.getNumber();
        var countryData = iti.getSelectedCountryData();
        var countryCode = countryData.dialCode; 
        var email=$("#email").val();
        var formData=new FormData();
        formData.append("mobile", mobile);
        formData.append("email", email);
        formData.append("ccmobile", ccmobile);
        formData.append("countryData", countryData);
        formData.append("cc", countryCode);
        formData.append("otpType", "6");
        formData.append("rb", "3");
        if(countryCode == "91"){
            formData.append("vm", "1");
        }else{
            formData.append("vm", "2");
        }
        ajaxData('/twistt/enabler/send-otp',formData,function(response){
            if(response.success){
                $('#divOtp').show();
                messageDisplay(response.message, 2000);
            }else{
                messageDisplay(response.message,3000);
            }
        });
    }).on("click","#btnVerify",function(){
        var otp=$("#otp").val();
        var mobile=$("#mobile").val();
        var ccmobile=iti.getNumber();
        var countryData = iti.getSelectedCountryData();
        var countryCode = countryData.dialCode;
        var formData=new FormData();
        formData.append("mobile", mobile);
        formData.append("ccmobile", ccmobile);
        formData.append("countryData", countryData);
        formData.append("cc", countryCode);
        formData.append("otp", otp);
        formData.append("otp_type", '5');
        formData.append('rb','3');
        ajaxData('/twistt/executive/verify-otp',formData,function(response){
            if(response.success){
                $('#divOtp').hide();
                $('#divBtn').hide();
                $("#mobile").prop('disabled', true);
                $("#changeMobile").hide();
                $('.udetails').show();
                $('#register').prop('disabled',false);
                messageDisplay(response.message, 2000);
            }else{
                messageDisplay(response.message,3000);
            }
        });
    });
}); 