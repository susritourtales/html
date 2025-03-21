
$(document).ready(function(){
    $("body").on("click", "#btnCheckout", function() {
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        var prid = $('#prid').val();
        var formData=new FormData();
        formData.append('prid',prid);
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/twistt/enabler/pay',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                if(data.rid){
                    messageDisplay(data.message, 3000);
                    setTimeout(function(){
                        window.location.href=BASE_URL+"/twistt/enabler/receipt/" + data.rid;
                    },2000);
                }
                if(!data.success){
                    messageDisplay(data.message, 3000);
                    element.html('Check out');
                    element.prop('disabled',false);
                    ajaxCall=null;
                    return false;
                }

                ajaxCall=null;
                var options = {
                    "key": "rzp_test_dn58ZwDYwvA7U3",
                    "amount": data.order['amount'], 
                    "currency": data.order['currency'],
                    "name": "Susri Tour Tales",
                    "description": "Enabler Plan Purchase",
                    "image": "http://susritourtales.com/img/susri.png",
                    "order_id": data.order['id'],
                    "handler": function (response){
                        var formData=new FormData();
                        formData.append('prid',prid);
                        formData.append('razorpay_payment_id',response.razorpay_payment_id);
                        formData.append('razorpay_order_id',response.razorpay_order_id);
                        formData.append('razorpay_signature',response.razorpay_signature);
                        ajaxCall=  $.ajax({
                            type: "POST",
                            url: BASE_URL+'/twistt/enabler/checkout',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function(resp)
                            {
                                messageDisplay(resp.message);
                                element.html('Check out');
                                element.prop('disabled',false);
                                setTimeout(function(){
                                    window.location.href=BASE_URL+"/twistt/enabler/receipt/" + resp.pid;
                                },2000);
                            },
                            error: function(resp)
                            {
                                messageDisplay(resp.message, 3000);
                                element.html('Check out');
                                element.prop('disabled',false);
                                ajaxCall=null;
                            }
                        });
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
            },
            error: function(data)
            {
                messageDisplay(data.message);
                ajaxCall=null;
            }
        });
    });
});
