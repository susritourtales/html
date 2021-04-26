$(document).ready(function(){
    $("body").on("click","#addSlabDays",function(){
         var slabDays=$(".slab-days").val();
                 slabDays=$.trim(slabDays);

           if(slabDays=="" || slabDays==undefined)
           {
               messageDisplay("Please Enter Slab Days",2000);
               return false;
           }

        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
        postData("/a_dMin/add-city-slab-days",{'days':slabDays},function(response){
            if(response)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(response.message);

            }
        });
    });
});