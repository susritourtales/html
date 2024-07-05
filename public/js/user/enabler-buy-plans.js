function updateAmt(){
    var pid = $('#selPlan option:selected').val();
    var cc = $('#cc').val();
    if(pid != 0){
        var formData=new FormData();
        formData.append('pid',pid);
        formData.append('cc',cc);
        ajaxData('/twistt/enabler/get-plan-price',formData,function(response){
            if(response.success){
                $('#pp').html(response.pp);
                $('#pad').html(response.pad);
                $("#btnBuy").prop('disabled', false);
            }else{
                $("#btnBuy").prop('disabled', true);
                messageDisplay(response.message,3000);
            }
        });
    }
}

$(document).ready(function(){
    $("body").on("change", "#selPlan", function() {
        updateAmt();
    }).on("click", "#btnBuy", function() {
        var cc = $('#cc').val();
        var pp = $('#pp').html();
        var pad = $('#pad').html();
        var pid = $('#selPlan option:selected').val();
        
        if($('#selPlan').val() == '0'){
            messageDisplay("Please select a plan to purchase", 2000);
            return;
        }
        var dname = $('#selName option:selected').val();
        var formData=new FormData();
        formData.append('cc',cc);
        formData.append('pp',pp);
        formData.append('pad',pad);
        formData.append('pid',pid);
        formData.append('dname',dname);
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/twistt/enabler/pay',
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
                            url: BASE_URL+'/twistt/enabler/checkout',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function(resp)
                            {
                                messageDisplay(resp.message);
                                setTimeout(function(){
                                    window.location.href=BASE_URL+"/twistt/enabler/track-purchases";
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
