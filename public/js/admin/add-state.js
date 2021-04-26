var mediaFilesacceptedExtensions = ["mp3", "mp4","wav","mpeg",'avi','mpg'];
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
        .on("change",".upload-file",function(e){
            var files = e.target.files;
            var rowId=$(this).data("id");
            var element=$(this);
            var incerement=0;
            $.each(files, function (i, file) {

                var reader = new FileReader();
                reader.onload = function (e)
                {
                    var FileType = files[i].type;
                    var filename = files[i].name;
                    var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
                    var Extension = fileExtension.toLowerCase();

                    if ($.inArray(Extension, mediaFilesacceptedExtensions) === -1)
                    {
                        files=[];
                        element.val("");
                        messageDisplay("Invalid File");
                        return false;
                    }


                    if(mediaFiles[rowId]==undefined)
                    {
                        mediaFiles[rowId]=[];
                    }

                    incerement++;
                    totalFilesCount++;
                    mediaFiles[rowId]=[];
                    mediaFiles[rowId].push(file);
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

                    if(files.length == incerement)
                    {
                        element.val("");
                    }
                };
                reader.readAsDataURL(file);
            });

        })
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
                        //  element.val("");
                        messageDisplay("Invalid File");
                        return false;
                    }
                    incerement++;
                    imageId++;
                    imageFiles[imageId]=[];
                    imageFiles[imageId].push(file);
                    uploadFiles['images'][imageId]={"uploaded":false};


                    // circle[imageId]  = $('.circlechart').data('radialIndicator');

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
                    //  console.log( $('.file_name[data-id="'+rowId+'"]').length);
                    //  $('.file_name[data-id="'+rowId+'"]').html(filename);
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
        var description = $.trim($('#description').val());
        var fileDetails=[];

        stateName=$.trim(stateName);
        if(stateName=='')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter state name");
            return  false;
        }

        description=$.trim(description);

       /* if(description=='')
        {
            messageDisplay("Please enter description");
            return  false;
        }*/
        /*if(imageFiles.length==0)
         {
         messageDisplay("please select image files");
         error=true;
         return false;
         }*/
        let mandatorytotalLanguages=[];
        let totalLanguages=[];

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
        for(var a in uploadFiles['attachment'])
        {
            if(!uploadFiles['attachment'][a]['uploaded'])
            {
                error=true;
                break;
            }
            fileIds.push(uploadFiles['attachment'][a]['id']);
        }
        console.log(uploadFiles,error);

        if(error)
        {
            return  false;
        }

        $(".file-uploads").each(function(){
            var rowId=$(this).data("id");
            var fileNameElement=$(".upload-file-name[data-id='"+rowId+"']");
            var fileLanguageElement=$(".file-language[data-id='"+rowId+"']");
            var tmp={};
            var fileName=fileNameElement.val();
            var fileLanguage=fileLanguageElement.val();
            fileName=$.trim(fileName);
            if(fileName!="" || mediaFiles[rowId]!=undefined || fileLanguage!=''){
                if(fileName=="")
                {
                    element.html('submit');
                    element.prop('disabled',false);
                    messageDisplay("Please Enter File Name");
                    error=true;
                    return  false;
                }
                tmp['file_name']=fileName;
                if(mediaFiles[rowId] == undefined)
                {
                    element.html('submit');
                    element.prop('disabled',false);
                    messageDisplay("Please upload files");
                    error=true;
                    return false;
                }
                tmp['file_id']=uploadFiles['attachment'][rowId]['id'];
                if(fileLanguage=="")
                {
                    element.html('submit');
                    element.prop('disabled',false);
                    messageDisplay("Please select file lanaguage ");
                    error=true;

                    return false;
                }
                if($.inArray(fileLanguage,mandatorytotalLanguages)===-1 && $.inArray(fileLanguage,mandatoryLanguages)!==-1)
                {
                    mandatorytotalLanguages.push(fileLanguage);
                }
                if($.inArray(fileLanguage,totalLanguages) === -1)
                {
                    totalLanguages.push(fileLanguage);
                }

                tmp['lanaguage']=fileLanguage;
                fileDetails.push(tmp);
            }
        });


        if(error)
        {
            return false;
        }
      /*  if(mandatorytotalLanguages.length!=mandatoryLanguages.length)
        {
            messageDisplay("Please Select mandatory languages hindi and english",2000);
            return false;
        }*/
        var formData=new FormData();
        if(jQuery.isEmptyObject(imageFiles))
        {
            element.html('submit');
            element.prop('disabled',false);

            messageDisplay("Please Upload Image Files");
            return false;
        }
        formData.append("description",description);
        formData.append("state_name",stateName);
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);
        formData.append("file_details",JSON.stringify(fileDetails));
        /*if(jQuery.isEmptyObject(mediaFiles))
        {
            messageDisplay("Please Upload Audio Files");
            return false;
        }*/
        /*for(var id in mediaFiles){
            $.each(mediaFiles[id], function (i, file) {

                formData.append("mediaFiles_"+id, file);
            });
        }

        for(var id in imageFiles){
            $.each(imageFiles[id], function (i, file) {

                formData.append("images_files[]", file);
            });
        }*/
        var element=$(this);

        ajaxData('/a_dMin/add-state',formData,function(response){
            if(response.success)
            {
                messageDisplay("added successfuly");
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/state-list";
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
        if(uploadFiles['attachment'][id]!=undefined)
        {
            delete uploadFiles['attachment'][id];
        }
        $(".file-uploads[data-id='"+id+"']").remove();
    });
});

