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
                if($('#lpd').html() == "")
                    $("#btnBuy").prop('disabled', false);
            }else{
                $("#btnBuy").prop('disabled', true);
                $('#pp').html('NA');
                $('#pad').html('NA');
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
            url: BASE_URL+'/twistt/enabler/purchase-request',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                messageDisplay(data.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/twistt/enabler/invoice/" + data.pid;
                },2000);
            },
            error: function(data)
            {
                messageDisplay(data.message);
                ajaxCall=null;
            }
        });
    });
});
