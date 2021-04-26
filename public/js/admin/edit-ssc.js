$(document).ready(function(){
    $("body").on("click","#btnChange",function(){
         var checked=$("#ssc").val();
         var ssc = 0;
         if ($('#ssc').is(":checked"))
         {
           ssc = 1;
         }
           
        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
        postData("/a_dMin/edit-ssc",{'ssc':ssc},function(response){
            if(response)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(response.message);

            }
        });
    });
});