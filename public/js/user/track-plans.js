$(document).ready(function() {
    $('#divEmail').hide();
    var today = new Date();
    $('#datepicker').datepicker({
      startDate: today
    });
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
    input.addEventListener("countrychange", function() {
        var countryData = iti.getSelectedCountryData();
        console.log("Country changed to: " + countryData.name + " (+" + countryData.dialCode + ")");
        if(countryData.dialCode == "91"){
            $('#divEmail').hide();
        }else{
            $('#divEmail').show();
        }
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
    })

    const form = document.getElementById('sellForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault(); 
            event.stopPropagation(); 
        } else {
            event.preventDefault();
            var ccmobile=iti.getNumber();
            var countryData = iti.getSelectedCountryData();
            var countryCode = countryData.dialCode; 

            if($('#divEmail').is(':visible')){
                if(!$('#email').val()){
                    const msg = "Please enter email id";
                        showError(msg);
                        return false;
                }else{
                    if(!validateEmail($("#email").val())) {
                        const msg = "Invalid email id";
                        showError(msg);
                        return false;
                    }
                }
            }
            const formData = new FormData(form);
            formData.append("ccmobile", ccmobile);
            formData.append("cc", countryCode);
            fetch('/twistt/enabler/sell', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if(countryCode !== "91"){
                    $("#mobile").prop('disabled', true);
                    $("#email").prop('disabled', true);
                }
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
                    messageDisplay('TWISTT plan offer successfull!');
                    console.log('TWISTT plan offer successfull!', data);
                    setTimeout(function(){
                        window.location.href=BASE_URL+"/twistt/enabler/track-plans";
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
}); 