var mediaFilesacceptedExtensions = ["mp3", "mp4","wav","mpeg",'avi','mpg'];
var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var mediaFiles={};
var imageId=0;
var addedRow=1;
var deletedImages=[];
var deletedAudio=[];
var uploadClicked=false;
var uploadFiles={'images':{},"attachment":{}};
var circle={};
$(document).ready(function ()
{
    addedRow=$(".file-uploads:last").data("id");
    imageId=$(".image-preview:last").data("id");
    $("body").on("change",".image-upload",function(e){
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
        resizeImage(ifile, 100, 150, function (resizedBlob) {
            var reader = new FileReader();
            reader.onload = function (e)
            {
                var FileType = ifile.type;
                var filename = ifile.name;
                var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
                var Extension = fileExtension.toLowerCase();
                if ($.inArray(Extension, imageacceptedExtensions) === -1)
                {
                    // ifile=null;
                    messageDisplay("Invalid File");
                    return false;
                }
                incerement++;
                imageId++;
                imageFiles[imageId] = [resizedBlob];
                /* imageFiles[imageId]=[];
                imageFiles[imageId].push(ifile); */
                uploadFiles['images'][imageId]={"uploaded":false};
                
                let classId='circlechart_img_'+imageId;
                $(".image-preview-wrapper").append('<div class="col-sm-4 mt-2 position-relative image-preview overflow-hidden" data-id="'+imageId+'"><div class="position-absolute circlechart '+classId+'" style="width: 100%;height: 100%" data-id="'+imageId+'"></div><img src="'+e.target.result+'" style="width: 100%;height: 100%"><span class="bg-white circle close-icon" data-id="'+imageId+'"><i class="fas fa-times position-absolute " data-id="'+imageId+'" ></i></span></div>');
                setTimeout(() => {
                    if (document.querySelector('.' + classId)) { // Ensure the element exists
                        circle[imageId] = radialIndicator('.'+classId,{
                            radius: 50,
                            barColor : '#6dd873',
                            barWidth : 8,
                            initValue : 0,
                            barBgColor: '#e4e4e4',
                            percentage: true
                        });
                    } else {
                        console.error("Radial progress element not found for imageId:", imageId);
                    }
                }, 50); 
                
                filesData.ajaxCall(1,resizedBlob,imageId,function(progress,fileID,response)
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
            reader.readAsDataURL(resizedBlob);
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
    }).on("click",".close-icon",function(){
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
        var stateElement=$("#state");
        var error=false;
        var stateId=$("#stateId").val();
        var element=$(this);
        var stateName=stateElement.val();
        var fileDetails=[];
        stateName=$.trim(stateName);
        if(stateName=='')
        {
            messageDisplay("Please enter state name");
            return  false;
        }
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
        formData.append("state_name",stateName);
        formData.append("state_id",stateId);
        formData.append("file_details",JSON.stringify(fileDetails));
        formData.append("deleted_images",JSON.stringify(deletedImages));
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);
        element.html('Please wait...');
        element.prop('disabled',true);
        ajaxData('/a_dMin/edit-state',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/states";
                },2000);
            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});

