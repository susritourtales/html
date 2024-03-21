var mediaFilesacceptedExtensions = ["mp3", "mp4","wav","mpeg",'avi','mpg'];
var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var mediaFiles={};
var imageId=0;
var addedRow=1;
$(document).ready(function ()
{
    $("#places").select2({
    }).on('change', function() {
        console.log("inside ",$("#places").val());
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

    $("body").on("change","#country",function(){
        var countryId=$(this).val();
        $(".city-wrapper").removeClass("hidden");
        $('#places').html('');
        $('.tags-container').html('');
        postData('/admin/get-cities',{"country_id":countryId},function(response){
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
        });
    }).on("change","#cities",function(){
        var cityId=$(this).val();
        $(".city-wrapper").removeClass("hidden");
        postData('/admin/get-places',{"city_id":cityId},function(response){
            var options='';
            if(response.success)
            {
                var list=response.places;
                for(var s=0;s<list.length;s++)
                {
                    options +='<option value="'+list[s].id+'">'+list[s].place_name+'</option>'
                }
                $('#places').html(options);
                $("#places").select2({
                }).on('change', function() {
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
            }
        });
    }).on("click","#editbt",function(){
        var countryElement=$("#country");
        var cityElement=$("#cities");
        var error=false;
        var placeElement=$("#places");
        var tnameElement=$("#tname");
        var cityId=cityElement.val();
        var countryId=countryElement.val();
        var placeName=placeElement.val();
        var taleName = tnameElement.val();
        var taleId = $("#taleId").val();
        if(countryId=='')
        {
            messageDisplay("Please select country");
            return  false;
        }
        if(placeName==null)
        {
            placeName ="";
        }
        if(taleName==''){
            messageDisplay("Please enter Tale Name");
            return  false;
        }
        var formData=new FormData();

        formData.append("country_id",countryId);
        formData.append("city_id",cityId);
        formData.append("place_name",placeName);
        formData.append("tale_name",taleName);
        formData.append("id",taleId);
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        ajaxData('/a_dMin/edit-bunched-tour',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/bunched-tour-list";
                },2000);

            }else{
                messageDisplay(response.message,3000);

                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});

