function updateAmt(){
    if($('#dc').val() =="")
        $('#dc').val('0');
    if($('#cc').val() =="")
        $('#cc').val('0');
    $('#amt').html($('#dc').val() * $('#udcp').val() + $('#cc').val() * $('#uccp').val());
}

$(document).ready(function(){
    $("body").on("click", "#btnBuy", function() {
    //document.getElementById('btnBuy').onclick = function(e){
        var dc = $('#dc').val();
        var cc = $('#cc').val();
        if(dc == 0 && cc == 0){
            messageDisplay("Please enter no of coupons to purchase..", 2000);
            return;
        }
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
                ajaxCall=null;
                var options = {
                    "key": "rzp_test_dn58ZwDYwvA7U3",
                    "amount": data.order['amount'], 
                    "currency": data.order['currency'],
                    "name": "Susri Tour Tales",
                    "description": "Coupons Purchase",
                    "image": "http://susritourtales.com/img/susri.png",
                    "order_id": data.order['id'],
                    "handler": function (response){
                        var formData=new FormData();
                        formData.append('dc',dc);
                        formData.append('cc',cc);
                        formData.append('razorpay_payment_id',response.razorpay_payment_id);
                        formData.append('razorpay_order_id',response.razorpay_order_id);
                        formData.append('razorpay_signature',response.razorpay_signature);
                        ajaxCall=  $.ajax({
                            type: "POST",
                            url: BASE_URL+'/twistt/executive/checkout',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function(resp)
                            {
                                messageDisplay(resp.message);
                                setTimeout(function(){
                                    window.location.href=BASE_URL+"/twistt/executive/track-coupons";
                                },2000);
                            },
                            error: function(resp)
                            {
                                messageDisplay(data.message);
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

 /* */

    /*  */