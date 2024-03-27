$(document).ready(function ()
{
    $("body").on("change","#country",function(){
        var countryId=$(this).val();
        postData('/admin/get-cities',{"country_id":countryId,'tour_type':2},function(response){
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
        postData('/admin/add-tour-get-places',{"city_id":cityId,'tour_type':2},function(response){
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
    }).on("click","#addWorldTour",function(){

        var countryElement=$("#country");
        var cityElement=$("#cities");
        var placeNameElement=$("#places");
        var error=false;
        var countryName=countryElement.val();
        var cityName=cityElement.val();
        var placeName=placeNameElement.val();
        var free = 0;
        if($("input[name='free']:checked"))
            free = 1;

        if(countryName=='')
        {
            messageDisplay("Please select country");
            return  false;
        }
        if(cityName=='')
        {
            messageDisplay("Please select city");
            return  false;
        }
        if(placeName==null)
        {
            messageDisplay("Please select place");
            return  false;
        }
        var formData=new FormData();
        formData.append("country_id",countryName);
        formData.append("city_id",cityName);
        formData.append("place_id",placeName);
        formData.append("free",free);
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        ajaxData('/a_dMin/add-world-tour',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message,2000);
                setTimeout(function(){
                   window.location.href=BASE_URL+"/a_dMin/world-tour-list";
                },2000);

            }else
            {
                messageDisplay(response.message);

                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});

