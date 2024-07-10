
function setPlanName(){
    $('#pn').val($('#pd').val() + $('#pt').val() + $('#pp').val());
}

$(document).ready(function(){
    $("body").on("click","#addEnbPlan",function(){
        if($("#pd").val() == ""){
            messageDisplay("Please mention TWISTT Duration");
            return false;
        }
        if($("#pp").val() == ""){
            messageDisplay("Please mention MTL");
            return false;
        }
        if($("#ppinr").val() == ""){
            messageDisplay("Please mention plan price INR");
            return false;
        }
        if($("#ppusd").val() == ""){
            messageDisplay("Please mention plan price USD");
            return false;
        }
        var pn = $("#pn").val();
        var pt = $("#pt").val();
        var ppinr = $("#ppinr").val();
        var ppusd = $("#ppusd").val();
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        postData("/a_dMin/add-enabler-plan",{'pn':pn, 'pt':pt, 'ppinr':ppinr, 'ppusd':ppusd},function(response){
            if(response)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/enabler-plans";
                },2000);
            }
        });
    });
});