var mediaFilesacceptedExtensions = ["mp3", "mp4","wav","mpeg",'avi','mpg'];
var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var mediaFiles={};
var imageId=0;
var addedRow=1;
$(document).ready(function ()
{
    $("body").on("change","#states",function(){
        var stateId=$(this).val();
        $(".city-wrapper").removeClass("hidden");
        postData('/admin/tours/get-cities',{"state_id":stateId,'tour_type':1},function(response){
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
        postData('/admin/tours/tourism-places-add-price',{"city_id":cityId,'tour_type':1},function(response){
            var options='';
            if(response.success)
            {
                var list=response.places;
                for(var s=0;s<list.length;s++)
                {
                    options +='<option value="'+list[s].tourism_place_id+'">'+list[s].place_name+'</option>'
                }
                $(".sol-container").remove();
                $("#places").html(options).val('').removeAttr('class').prop("multiple",true).searchableOptionList();
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

    }).on("click","#addIndiaTour",function(){

        var stateElement=$("#states");
        var cityElement=$("#cities");
        var error=false;
        var placeElement=$("#places");


        var cityId=cityElement.val();
        var stateId=stateElement.val();
        var placeName=placeElement.val();
        var fileDetails=[];

        if(stateId=='')
        {
            messageDisplay("Please select state");
            return  false;
        }

       /* if(cityId=='')
        {
            messageDisplay("Please select city");
            return  false;
        }

        if(placeName=='')
        {
            messageDisplay("Please select place");
            return  false;
        }*/
       
        /*if(imageFiles.length==0)
         {
         messageDisplay("please select image files");
         error=true;
         return false;
         }*/
        if(placeName==null)
        {
            placeName ="";
        }
        var formData=new FormData();

        formData.append("state_id",stateId);
        formData.append("city_id",cityId);
        formData.append("place_name",placeName);

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

