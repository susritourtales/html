var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var mediaFiles={};
var imageId=0;
var addedRow=1;
var totalFilesCount;
var uploadClicked=false;
var uploadFiles={'images':{},"attachment":{}};
var circle={};
$(document).ready(function ()
{
    $("body")
        .on("change",".image-upload",function(e){
            var files = e.target.files;
            var element=$(this);
            var incerement=0;
            $.each(files, function (i, file)
            {
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
                        messageDisplay("Invalid File");
                        return false;
                    }
                    incerement++;
                    imageId++;
                    imageFiles[imageId]=[];
                    imageFiles[imageId].push(file);
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

                    filesData.ajaxCall(1,file,imageId,function(progress,fileID,response)
                    {
                        console.log( circle[fileID]);
                        if(progress)
                        {
                            circle[fileID].animate((fileID*100));
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
                                        var countryElement = $("#addPlace");
                                        countryElement.prop('disabled', false);
                                        countryElement.click();
                                    }
                                }
                            }
                        }
                    });
                    if(files.length == incerement)
                    {
                        element.val("");
                    }
                };
                reader.readAsDataURL(file);
            });
            setTimeout(function(){},10);

        })
        .on("click",".close-icon",function () {
        var id=$(this).data("id");
        $(".image-preview[data-id='"+id+"']").remove();
        if(imageFiles[id]!=undefined)
        {
            delete  imageFiles[id];
        }
        if(uploadFiles['images'][id] !=undefined) {
            delete uploadFiles['images'][id]
        }
    }).on("click","#addPlace",function(){
        var stateElement=$("#state");
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        var stateName=stateElement.val();
        var fileDetails=[];
        stateName=$.trim(stateName);
        if(stateName=='')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter state name");
            return  false;
        }
        var imageFileIds=[];
        var fileIds=[];
        uploadClicked=true;
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
        console.log(uploadFiles,error);
        if(error)
        {
            return  false;
        }
        var formData=new FormData();
        if(jQuery.isEmptyObject(imageFiles))
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please Upload Image Files");
            return false;
        }
        formData.append("state_name",stateName);
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);
        formData.append("file_details",JSON.stringify(fileDetails));
        var element=$(this);
        ajaxData('/a_dMin/add-state',formData,function(response){
            if(response.success)
            {
                messageDisplay("added successfuly");
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

