$(document).ready(function ()
{
    $("#languages").select2({
    }).on('change', function() {
                     console.log("inside ",$("#languages").val());
        var $selected = $(this).find('option:selected');
        var $container = $('.tags-container');
        console.log($container.length);
        var $list = $('<ul>');
        $selected.each(function(k, v) {
            var $li = $('<li class="tag-selected">' + $(v).text() + '<a class="destroy-tag-selected">×</a></li>');
            $li.children('a.destroy-tag-selected')
                .off('click.select2-copy')
                .on('click.select2-copy', function(e) {
                    var $opt = $(this).data('select2-opt');
                    $opt.prop('selected', false);
                    $opt.parents('select').trigger('change');
                }).data('select2-opt', $(v));
            $list.append($li);
        });
        $container.html('').append($list);
    }).trigger('change');

    $("body").on("click","#editUpcoming",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var month=$.trim($('#month').val());
        var year=$.trim($('#year').val());
        var countryName=$.trim($('#country').val());
        var stateName=$.trim($('#state').val());
        var cityName=$.trim($('#city').val());
        var placeName=$.trim($('#place').val());
        var languages = $.trim($('#languages').val());
        var tourType=$('#tourtype option:selected').val();

        if(month == "0"){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please select month");
            return  false;
        }
        if(year == "0"){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please select year");
            return  false;
        }
        if(tourType == 0){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please select tour type");
            return  false;
        }
        if(countryName=='')
        {
            if(tourType == 2 || tourType == 3 || tourType == 5){
                element.html('submit');
                element.prop('disabled',false);
                messageDisplay("Please select country");
                return  false;
            }
        }
        if (stateName == '')
        {
            if(tourType == 1 || tourType == 4){
                element.html('submit');
                element.prop('disabled',false);
                messageDisplay("Please select state");
                return false;
            }
        }
        if(cityName=='')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter city name");
            return  false;
        }
        if(placeName=='')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter place name");
            return  false;
        }
        if(languages=='')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter languages");
            return  false;
        }        


        var formData=new FormData();
        var upid=$('#huid').val();
        formData.append("languages",languages);
        formData.append("country_name",countryName);
        formData.append("state_name",stateName);
        formData.append("city_name",cityName);
        formData.append("place_name",placeName);
        formData.append("tour_type",tourType);
        formData.append("month",month);
        formData.append("year",year);

        ajaxData('/a_dMin/edit-upcoming/' + upid,formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/upcoming-list";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});