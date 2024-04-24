var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var mediaFiles={};
var imageId=0;
var addedRow=1;
var deletedImages=[];
var deletedAudio=[];
var uploadClicked=false;
var uploadFiles={'images':{},"attachment":{}};
var circle={}
$(document).ready(function ()
{
    addedRow=$(".file-uploads:last").data("id");
    imageId=$(".image-preview:last").data("id");

    $("body").on("change","#country",function(){
        var countryId=$(this).val();
        var countryText=$('#country option:selected').text();
        countryText=countryText.toLowerCase();
        if(countryText=='india') {
            $("#state-wrapper").removeClass('d-none');
            postData('/admin/get-states',{"country_id":countryId},function(response){
                var options='<option value="">--select state--</option>';
                if(response.success){
                    var list=response.states;
                    for(var s=0;s<list.length;s++)
                    {
                        options +='<option value="'+list[s].id+'" data-id="'+list[s].state_id+'">'+list[s].state_name+'</option>'
                    }
                    $('#states').html(options);
                }
            });
        }else
        {
            $("#state-wrapper").addClass('d-none');
        }
    }).on("change",".image-upload",function(e){
        let text = "Existing thumbnail will be replaced. Do you want to proceed?";
        if (confirm(text) == false) {
            return false;
        }
        var elements = document.getElementsByClassName("close-icon");
        for (var i = 0; i < elements.length; i++) {
            elements[i].click();
        }
        var ifile = e.target.files[0];
        var element=$(this);
        var incerement=0;
        var reader = new FileReader();
        reader.onload = function (e)
        {
            var FileType = ifile.type;
            var filename = ifile.name;
            var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
            var Extension = fileExtension.toLowerCase();
            if ($.inArray(Extension, imageacceptedExtensions) === -1)
            {
                ifile=null;
                messageDisplay("Invalid File");
                return false;
            }
            incerement++;
            imageId++;
            imageFiles[imageId]=[];
            imageFiles[imageId].push(ifile);
            uploadFiles['images'][imageId]={"uploaded":false};
            
            let classId='circlechart_img_'+imageId;
            $(".image-preview-wrapper").append('<div class="col-sm-4 mt-2 position-relative image-preview overflow-hidden" data-id="'+imageId+'"><div class="position-absolute circlechart '+classId+'" style="width: 100%;height: 100%" data-id="'+imageId+'"></div><img src="'+e.target.result+'" style="width: 100%;height: 100%"><span class="bg-white circle close-icon" data-id="'+imageId+'"><i class="fas fa-times position-absolute " data-id="'+imageId+'" ></i></span></div>');
            circle[imageId] = radialIndicator('.'+classId,{
                radius: 50,
                barColor : '#6dd873',
                barWidth : 8,
                initValue : 0,
                barBgColor: '#e4e4e4',
                percentage: true
            });
            
            filesData.ajaxCall(1,ifile,imageId,function(progress,fileID,response)
            {
                if(progress)
                {
                    if(circle[fileID]) {
                        circle[fileID].animate((fileID * 100));
                    }
                }
                if(!progress)
                {
                    if(response.success)
                    {
                        if(uploadFiles['images'][fileID]!=undefined)
                        {
                            uploadFiles['images'][fileID] = {
                                "uploaded": true,
                                'id': response.id
                            };
                            if(circle[fileID]) {
                                circle[fileID].animate(100);
                            }
                            if (uploadClicked) {
                                var countryElement = $("#editbt");
                                countryElement.prop('disabled', false);
                                countryElement.click();
                            }
                        }
                    }
                }
            });
            element.val("");
        };
        reader.readAsDataURL(ifile);
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
        var edit=$(this).data("edit");
        if(edit)
        {
            $(".image-preview[data-id='"+id+"']").remove();
            deletedImages.push(id);
        }else
        {
            $(".image-preview[data-id='"+id+"']").remove();
            if(imageFiles[id]!=undefined)
            {
                delete  imageFiles[id];
            }
        }
        if(uploadFiles['images'][id] !=undefined) {
            delete uploadFiles['images'][id]
        }
    }).on("click","#addPlace",function(){
        var countryElement=$("#country");
        var stateElement=$("#states");
        var cityElement=$("#city");
        var error=false;
        var element=$(this);
        var countryName=countryElement.val();
        var stateName=stateElement.val();
        var cityName=cityElement.val();
        var fileDetails=[];
        if(countryName=='')
        {
            messageDisplay("Please select country");
            return  false;
        }
        var countryText=$('#country option:selected').text();

        if(countryText.toLowerCase()=='india') {
            if (stateName == '') {
                messageDisplay("Please select state");
                return false;
            }
        }
        cityName=$.trim(cityName);
        if(cityName=='')
        {
            messageDisplay("Please enter city name");
            return  false;
        }
        var cityId=$("#cityId").val();
        var imageFileIds=[];
        var fileIds=[];
        uploadClicked=true;
        element.html('Please wait...');
        element.prop('disabled',true);
        for(var k in uploadFiles['images'])
        {
            if(!uploadFiles['images'][k]['uploaded'])
            {
                error=true;
                break;
            }
            imageFileIds.push(uploadFiles['images'][k]['id']);
            fileIds.push(uploadFiles['images'][k]['id']);
        }
        if(error)
        {
            return  false;
        }
        
    if($(".image-preview").length==0)
    {
        messageDisplay("Please Upload Image Files");
        return false;
    }
        var formData=new FormData();
        formData.append("country_name",countryName);
        formData.append("state_name",stateName);
        formData.append("city_name",cityName);
        formData.append("city_id",cityId);
        formData.append("file_details",JSON.stringify(fileDetails));
        formData.append("deleted_images",JSON.stringify(deletedImages));
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);
        element.html('Please wait...');
        element.prop('disabled',true);
        ajaxData('/a_dMin/edit-city',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/cities";
                },2000);

            }else{
                messageDisplay(response.message);

                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});

