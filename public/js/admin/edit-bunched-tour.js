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
        postData('/admin/admin/get-cities',{"country_id":countryId},function(response){
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
        postData('/admin/tours/tourism-places',{"city_id":cityId},function(response){
            var options='';
            if(response.success)
            {
                var list=response.places;
                for(var s=0;s<list.length;s++)
                {
                    options +='<option value="'+list[s].tourism_place_id+'">'+list[s].place_name+'</option>'
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
    }).on("change",".upload-file",function(e){
        var files = e.target.files;
        var rowId=$(this).data("id");
        $.each(files, function (i, file) {

            var reader = new FileReader();
            reader.onload = function (e)
            {
                console.log(files);

                var FileType = files[i].type;
                var filename = files[i].name;
                var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
                var Extension = fileExtension.toLowerCase();

                if ($.inArray(Extension, mediaFilesacceptedExtensions) === -1)
                {
                    files=[];
                    messageDisplay("Invalid File" );
                    return false;
                }
                if(mediaFiles[rowId]==undefined)
                {
                    mediaFiles[rowId]=[];
                }
                mediaFiles[rowId]=[];
                mediaFiles[rowId].push(file);
                console.log( $('.file_name[data-id="'+rowId+'"]').length);
                $('.file_name[data-id="'+rowId+'"]').html(filename);
            };
            reader.readAsDataURL(file);

        });
    }).on("change",".image-upload",function(e){
        var files = e.target.files;
        var element=$(this);
        console.log("image");

        $.each(files, function (i, file){
            var reader = new FileReader();

            reader.onload = function (e)
            {
                var FileType = files[i].type;
                var filename = files[i].name;
                var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
                var Extension = fileExtension.toLowerCase();
                console.log(files);
                if ($.inArray(Extension, imageacceptedExtensions) === -1)
                {
                    files=[];
                    messageDisplay("Invalid File");
                    return false;
                }
                imageId++;
                imageFiles[imageId]=[];
                imageFiles[imageId].push(file);
                //  console.log( $('.file_name[data-id="'+rowId+'"]').length);
                //  $('.file_name[data-id="'+rowId+'"]').html(filename);
                $(".image-preview-wrapper").append('<div class="col-sm-3 mt-2 position-relative image-preview" data-id="'+imageId+'"><img src="'+e.target.result+'" style="width: 100%;height: 100%"><i class="fas fa-times position-absolute close-icon" data-id="'+imageId+'"></i></div>');
            };
            reader.readAsDataURL(file);
        });

        setTimeout(function(){

            var height=$(".image-preview-wrapper").height();
            var parentHeight=$(".image-upload-wrapper").height();
            console.log(parentHeight,height);
            if(parentHeight<height)
            {
                $(".image-upload").css("height",height);
            }


        },10);

    }).on("click",".close-icon",function () {
        var id=$(this).data("id");
        $(".image-preview[data-id='"+id+"']").remove();
        if(imageFiles[id]!=undefined)
        {
            delete  imageFiles[id];
        }

    }).on("click","#editCityTour",function(){

        var countryElement=$("#country");
        var cityElement=$("#cities");
        var error=false;
        var placeElement=$("#places");
        //var priceElement=$("#price");


        var cityId=cityElement.val();
        var countryId=countryElement.val();
        var placeName=placeElement.val();
        //var price=priceElement.val();
        var fileDetails=[];

        if(countryId=='')
        {
            messageDisplay("Please select country");
            return  false;
        }
        /*  if(cityId=='')
          {
              messageDisplay("Please select city");
              return  false;
          }*/
        /*  if(price=='')
          {
              messageDisplay("Please enter price");
              return  false;
          }*/


        /*if(imageFiles.length==0)
         {
         messageDisplay("please select image files");
         error=true;
         return false;
         }*/

        var formData=new FormData();
        //let priceId=$(".priceId").val();
        formData.append("country_id",countryId);
        formData.append("city_id",cityId);
        //formData.append("price",price);
        formData.append("place_name",placeName);
        //formData.append("price_id",priceId);

        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);

        ajaxData('/a_dMin/edit-city-tour',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/city-tour-list";
                },2000);

            }else{
                messageDisplay(response.message,3000);

                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    }).on("click",".add-control",function(){
        addedRow++;
        var rowsCount= $(".file-uploads").length;
        postData('/admin/admin/file-upload-row',{'row_number':addedRow,"rows_count":rowsCount},function(response){

            $("#file-upload-wrapper").append(response);
        });
    }).on("click",".remove-control",function(){
        var id=$(this).data("id");

        if(mediaFiles[id]!=undefined)
        {
            delete mediaFiles[id];
        }
        $(".file-uploads[data-id='"+id+"']").remove();
    });
});

