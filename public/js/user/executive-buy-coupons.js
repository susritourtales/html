document.getElementById('btnBuy').onclick = function(e){
    var dc = $('#dc').val();
    var cc = $('#cc').val();
    var formData=new FormData();
    formData.append('dc',dc);
    formData.append('cc',cc);
    ajaxCall=  $.ajax({
        type: "POST",
        url: BASE_URL+'/twistt/executive/pay',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function(data)
        {
            messageDisplay(data.message);
            ajaxCall=null;
        },
        error: function()
        {
            messageDisplay(data.message);
            ajaxCall=null;
        }
    });
    /* var options = {
        "key": "rzp_test_dn58ZwDYwvA7U3", // Enter the Key ID generated from the Dashboard
        "amount": "50000", // Amount is in currency subunits. Default currency is INR. Hence, 50000 means 500 INR
        "currency": "INR",
        "name": "Susri Tour Tales",
        "description": "Coupons Purchase",
        "image": "http://susritourtales.com/img/susri.png",
        "handler": function (response){
            // Handle the response from Razorpay here
            alert(response.razorpay_payment_id);
            alert(response.razorpay_order_id);
            alert(response.razorpay_signature);
        },
        "prefill": {
            "name": $('#username').val(),
            "email": $('#email').val(),
            "contact": $('#mobile').val()
        },
        "notes": {
            "city": $('#city').val()
        },
        "theme": {
            "color": "#20437b"
        }
    };

    var rzp1 = new Razorpay(options);
    rzp1.open();
    e.preventDefault(); */
}