$(document).ready(function ()
{
    $("body").on("change","#states",function(){
        var stateId=$(this).val();
        postData('/admin/get-cities',{"state_id":stateId,'tour_type':1},function(response){
            var options='<option value="">--select city--</option>';
            if(response.success)
            {
                var list=response.cities;
                for(var s=0;s<list.length;s++)
                {
                    options +='<option value="'+list[s].id+'">'+list[s].city_name+'</option>'
                }
                $('#cities').html(options);
            }
            $(".sol-container").remove();
            $("#places").html('').addClass('form-control d-block').prop("multiple",false);
        });
    }).on("change","#cities",function(){
        var cityId=$(this).val();
        postData('/admin/add-tour-get-places',{"city_id":cityId,'tour_type':1},function(response){
            var options='';
            if(response.success)
            {
                var list=response.places;
                for(var s=0;s<list.length;s++)
                {
                    options +='<option value="'+list[s].id+'">'+list[s].place_name+'</option>'
                }
                $(".sol-container").remove();
                $("#places").html(options).val('').removeAttr('class').prop("multiple",true).searchableOptionList();
            }
        });
    }).on("click","#addIndiaTour",function(){
        var stateElement=$("#states");
        var cityElement=$("#cities");
        var error=false;
        var placeElement=$("#places");
        var cityId=cityElement.val();
        var stateId=stateElement.val();
        var placeId=placeElement.val();
        var free = 0;
        if($("input[name='free']:checked").length > 0)
            free = 1;
        if(cityId=='')
        {
            messageDisplay("Please select city");
            return  false;
        }
        if(stateId=='')
        {
            messageDisplay("Please select state");
            return  false;
        }
        if(placeId==null)
        {
            messageDisplay("Please select place");
            return  false;
        }
        var formData=new FormData();
        formData.append("state_id",stateId);
        formData.append("city_id",cityId);
        formData.append("place_id",placeId);
        formData.append("free",free);
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        ajaxData('/a_dMin/add-india-tour',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/india-tour-list";
                },2000);

            }else{
                messageDisplay(response.message);

                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});

