$(document).ready(function(){

    /* $(".sol-container").remove();
    $("#languages").removeAttr('class').prop("multiple",true).searchableOptionList({showSelectAll: true}); */

    $("body").on("click","#btnChange",function(){
        var lang=$("#languages").val();
          
        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
        postData("/admin/admin/add-language",{'lng':lang},function(response){
            if(response)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(response.message);
                window.location.reload();
            }
        });
    });
});