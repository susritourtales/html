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
    $("body")
        .on("change",".upload-file",function(e){
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
                    uploadFiles['attachment'][rowId]={"uploaded":false};
                    filesData.ajaxCall(2,file,rowId,function(progress,fileID,response){
                        $(".progress-bar[data-id='"+fileID+"']").css("width",'100%').text('100%');
                        if(!progress)
                        {
                            if(response.success)
                            {
                                if(uploadFiles['attachment'][fileID]!=undefined)
                                {
                                    uploadFiles['attachment'][fileID] = {"uploaded": true, 'id': response.id};
                                    if (uploadClicked) {
                                        var countryElement = $("#addPlace");
                                        countryElement.prop('disabled', false);
                                        countryElement.click();
                                    }
                                }
                            }
                        }
                    });
                    $(".upload-div[data-id='"+rowId+"']").append(`<div class="position-absolute progress" data-id="${rowId}">
                        <div class="progress-bar progress-bar-animate" role="progressbar" style="width: 1%;" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100" data-id="${rowId}">1%</div>
                    </div>`);
                    $('.file_name[data-id="'+rowId+'"]').html(filename);
                };
                reader.readAsDataURL(file);
            });
        })
        .on("change",".image-upload",function(e){
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
            setTimeout(function(){
                var height=$(".image-preview-wrapper").height();
                var parentHeight=$(".image-upload-wrapper").height();
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
        var description = $.trim($('#description').val());
        var fileDetails=[];
        stateName=$.trim(stateName);
        if(stateName=='')
        {
            messageDisplay("Please enter state name");
            return  false;
        }
        description=$.trim(description);
        let mandatorytotalLanguages=[];
        let totalLanguages=[];
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
        for(var a in uploadFiles['attachment'])
        {
            if(!uploadFiles['attachment'][a]['uploaded'])
            {
                error=true;
                break;
            }
            fileIds.push(uploadFiles['attachment'][a]['id']);
        }
        if(error)
        {
            return  false;
        }
        $(".file-uploads").each(function(){
            var rowId=$(this).data("id");
            var fileNameElement=$(".upload-file-name[data-id='"+rowId+"']");
            var fileLanguageElement=$(".file-language[data-id='"+rowId+"']");
            var tmp={};
            var editId=$(this).data("edit");
            var fileName=fileNameElement.val();
            var fileLanguage=fileLanguageElement.val();
            fileName=$.trim(fileName);
            if(fileName!="" || mediaFiles[rowId]!=undefined || fileLanguage!=''){
                if(fileName=="")
                {
                    fileNameElement.focus();
                    messageDisplay("Please Enter File Name");
                    error=true;
                    return  false;
                }
                tmp['file_name']=fileName;
                if(editId==undefined) {
                    if(mediaFiles[rowId] == undefined)
                    {
                        messageDisplay("Please upload files");
                        error=true;
                        return false;
                    }
                    tmp['file_id']=uploadFiles['attachment'][rowId]['id'];
                }else{
                    if(uploadFiles['attachment'][rowId])
                    {
                        tmp['file_id']=uploadFiles['attachment'][rowId]['id'];
                    }else{
                        tmp['file_id']='';
                    }
                }
                if(fileLanguage=="")
                {
                    fileLanguageElement.focus();
                    messageDisplay("Please select file lanaguage ");
                    error=true;
                    return false;
                }
                if(editId==undefined)
                {
                    editId=0;
                }
                if($.inArray(fileLanguage,mandatorytotalLanguages)===-1 && $.inArray(fileLanguage,mandatoryLanguages)!==-1)
                {
                    mandatorytotalLanguages.push(fileLanguage);
                }
                if($.inArray(fileLanguage,totalLanguages) === -1)
                {
                    totalLanguages.push(fileLanguage);
                }
                tmp['edit_id']=editId;
                tmp['lanaguage']=fileLanguage;
                fileDetails.push(tmp);
            }
        });
        if(error)
        {
            element.html('Submit');
            element.prop('disabled',false);
            return false;
        }
        if($(".image-preview").length==0)
        {
            messageDisplay("Please Upload Image Files");
            return false;
        }
        var formData=new FormData();
        formData.append("description",description);
        formData.append("state_name",stateName);
        formData.append("state_id",stateId);
        formData.append("file_details",JSON.stringify(fileDetails));
        formData.append("deleted_audio",JSON.stringify(deletedAudio));
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
     .on("click",".add-control",function(){
        addedRow++;
        var rowsCount= $(".file-uploads").length;
        postData('/admin/file-upload-row',{'row_number':addedRow,"rows_count":rowsCount},function(response){
            $("#file-upload-wrapper").append(response);
        });
    })
     .on("click",".remove-control",function(){
        var id=$(this).data("id");
        if(mediaFiles[id]!=undefined)
        {
            delete mediaFiles[id];
        }
        var rowElement=$(".file-uploads[data-id='"+id+"']");
        var editId=rowElement.data("edit");
        if(editId!=undefined)
        {
            deletedAudio.push(editId);
        }
        rowElement.remove();
        if(uploadFiles['attachment'][id]!=undefined)
        {
            delete uploadFiles['attachment'][id];
        }
    });
});

