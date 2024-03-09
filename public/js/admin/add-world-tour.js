var mediaFilesacceptedExtensions = ["mp3", "mp4","wav","mpeg",'avi','mpg'];
var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var mediaFiles={};
var imageId=0;
var addedRow=1;
$(document).ready(function ()
{
    $("body").on("change","#country",function(){
        var countryId=$(this).val();


        $(".city-wrapper").removeClass("hidden");
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
        $(".city-wrapper").removeClass("hidden");
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
    }).on("change",".upload-file",function(e){
        var files = e.target.files;
        var rowId=$(this).data("id");
        var element=$(this);
        var incerement=0;
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
                    element.val("");
                    messageDisplay("Invalid File" );
                    return false;
                }
                if(mediaFiles[rowId]==undefined)
                {
                    mediaFiles[rowId]=[];
                }
                incerement++;
                mediaFiles[rowId]=[];
                mediaFiles[rowId].push(file);
                $('.file_name[data-id="'+rowId+'"]').html(filename);
                if(files.length == incerement)
                {
                    element.val("");
                }
            };
            reader.readAsDataURL(file);
        });

    }).on("change",".image-upload",function(e){
        var files = e.target.files;
        var element=$(this);

        var incerement=0;
        $.each(files, function (i, file){
            var reader = new FileReader();

            reader.onload = function (e)
            {
                var FileType = files[i].type;
                var filename = files[i].name;
                var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
                var Extension = fileExtension.toLowerCase();

                if ($.inArray(Extension, imageacceptedExtensions) === -1)
                {
                    files=[];
                    //  element.val("");
                    messageDisplay("Invalid File");
                    return false;
                }
                incerement++;
                imageId++;
                imageFiles[imageId]=[];
                imageFiles[imageId].push(file);
                console.log(files.length,incerement);
                if(files.length == incerement)
                {
                    element.val("");
                }
                $(".image-preview-wrapper").append('<div class="col-sm-3 mt-2 position-relative image-preview" data-id="'+imageId+'"><img src="'+e.target.result+'" style="width: 100%;height: 100%"><i class="fas fa-times position-absolute close-icon" data-id="'+imageId+'"></i></div>');

            };
            reader.readAsDataURL(file);
        });

        setTimeout(function(){
            /* var height=$(".image-preview-wrapper").height();
             var parentHeight=$(".image-upload-wrapper").height();
             console.log(parentHeight,height);
             if(parentHeight<height)
             {
             $(".image-upload").css("height",height);
             }
             */

        },10);

    }).on("click",".close-icon",function () {
        var id=$(this).data("id");
        $(".image-preview[data-id='"+id+"']").remove();
        if(imageFiles[id]!=undefined)
        {
            delete  imageFiles[id];
        }

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
        if(error)
        {
            return false;
        }
          if(placeName==null)
          {
              placeName ="";
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

