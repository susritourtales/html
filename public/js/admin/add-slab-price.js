$(document).ready(function(){
        $("body").on("click","#addSlabPrice",function(){
                var slabPriceIt={};
                var slabPriceWt={};
            var error=false;
               $(".slab-price-it").each(function(){
                      var days=$(this).data("id");
                       var value=$(this).val();
                      if(value && value!=0)
                      {
                          slabPriceIt[days]=value;
                      }else if(value==0)
                      {
                          messageDisplay("Please enter price greater than 0 for "+days +" days in IT");
                          error=true;
                          return false;
                      }else{
                          messageDisplay("Please enter price for "+days +" days in IT");
                          error=true;
                          return false;
                      }
               });
              if(error)
              {
                  return false;
              }
            $(".slab-price-wt").each(function(){
                var days=$(this).data("id");
                var value=$(this).val();
                if(value && value!=0)
                {
                    slabPriceWt[days]=value;
                }else if(value==0)
                {
                    messageDisplay("Please enter price greater than 0 for "+days +" days in WT");
                    error=true;
                    return false;
                }else{
                    messageDisplay("Please enter price for "+days +" days in WT");
                    error=true;
                    return false;
                }
            });
            if(error)
            {
                return false;
            }
            var element=$(this);
            element.html('Please wait...');

            element.prop('disabled',true);
            postData("/a_dMin/add-slab-price",{'slab_price_it':JSON.stringify(slabPriceIt),"slab_price_wt":JSON.stringify(slabPriceWt)},function(response){
                        if(response)
                        {
                            if(response.success)
                            {
                                messageDisplay(response.message);
                                setTimeout(function(){
                                    window.location.reload();
                                },2000);

                            }else{
                                element.prop('disabled',false);
                                element.html('Submit');
                            }
                        } 
            });
        });
});