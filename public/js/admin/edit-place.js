var mediaFilesacceptedExtensions = ["mp3", "mp4","wav","mpeg",'avi','mpg'];
var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var tnFiles={};
var mediaFiles={};
var imageId=0;
var tnImageId=0;
var addedRow=1;
var deletedImages=[];
var deletedThumbnails=[];
var deletedAudio=[];
var uploadClicked=false;
var uploadFiles={'images':{},"attachment":{},"thumbnails":{}};
var circle={};
$(document).ready(function ()
{
    addedRow=$(".file-uploads:last").data("id");
    imageId=($(".image-preview").length > 0 ) ? $(".image-preview:last").data("id") : 0;
    tnImageId=($(".tn-preview").length > 0 ) ? $(".tn-preview:last").data("id") : 0;
    $("body").on("change","#country",function(){
        var countryId=$(this).val();
        var countryText=$('#country option:selected').text();
        countryText=countryText.toLowerCase();
        var options='<option value="">--select city--</option>';
        $("#cities").html(options);
        if(countryText=='india') {
            $("#state-wrapper").removeClass('d-none');
            postData('admin/get-states', {"country_id": countryId}, function (response) {
                var options = '<option value="">--select state--</option>';
                if (response.success) {
                    var list = response.states;
                    for (var s = 0; s < list.length; s++) {
                        options += '<option value="' + list[s].id + '" data-id="' + list[s].state_id + '">' + list[s].state_name + '</option>'
                    }
                    $('#states').html(options);
                    $(".state-wrapper").removeClass("hidden");
                }
            });
        }else
        {
            $("#state-wrapper").addClass('d-none');
            postData('admin/get-cities',{"country_id":countryId},function(response){
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
        }
    }).on("change","#states",function(){
        var stateId=$(this).val();
        var countryId=$("#country").val();
        postData('admin/get-cities',{"state_id":stateId,"country_id":countryId},function(response){
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
    })
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
                        <div class="progress-bar progress-bar-animate" role="progressbar" style="width: 1%;" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100" data-id="${rowId}">1%</div></div>`);
                $('.file_name[data-id="'+rowId+'"]').html(filename);
            };
            reader.readAsDataURL(file);

        });
    })
    function resizeImage(file, maxWidth, maxHeight, callback) {
        const reader = new FileReader();
        reader.onload = function (event) {
            const img = new Image();
            img.onload = function () {
                let width = img.width;
                let height = img.height;
    
                // Maintain aspect ratio
                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }
    
                // Create canvas and draw resized image
                const canvas = document.createElement("canvas");
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                // Convert canvas to Base64 (JPEG or PNG format)
            let imageType = "image/jpeg"; // Default to JPEG (change to "image/png" if needed)
            let resizedDataUrl = canvas.toDataURL(imageType, 0.9); // 90% quality
            
            // Convert Base64 to File object
            let resizedFile = dataURLtoFile(resizedDataUrl, file.name.replace(/\.[^/.]+$/, "") + ".jpg");
            callback(resizedFile);
    
                // Convert to Blob and send to callback
                //canvas.toBlob(callback, file.type, 0.9); // 90% quality
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    function dataURLtoFile(dataUrl, fileName) {
        let arr = dataUrl.split(",");
        let mime = arr[0].match(/:(.*?);/)[1];
        let bstr = atob(arr[1]);
        let n = bstr.length;
        let u8arr = new Uint8Array(n);
        
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        
        return new File([u8arr], fileName, { type: mime });
    }
    
    $(document).on("change", ".image-upload", function(e) {
        var files = e.target.files;
        var element = $(this);
        var increment = 0;
    
        $.each(files, function(i, file) {
            resizeImage(file, 350, 250, function(resizedBlob) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var FileType = file.type;
                    var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1)).toLowerCase();
    
                    if ($.inArray(fileExtension, imageacceptedExtensions) === -1) {
                        messageDisplay("Invalid File");
                        return false;
                    }
    
                    increment++;
                    imageId++; // Ensure each image has a unique ID
                    imageFiles[imageId] = [resizedBlob]; // Use resized image
                    uploadFiles['images'][imageId] = { "uploaded": false };
    
                    let classId = 'circlechart-img-' + imageId; // Unique class for each progress bar
    
                    // Append resized image preview
                    $(".image-preview-wrapper").append(`
                        <div class="col-sm-4 mt-2 position-relative image-preview overflow-hidden" data-id="${imageId}">
                            <div class="position-absolute circlechart ${classId}" style="width: 100%; height: 100%" data-id="${imageId}"></div>
                            <img src="${e.target.result}" style="width: 100%; height: 100%">
                            <span class="bg-white circle close-icon" data-id="${imageId}">
                                <i class="fas fa-times position-absolute" data-id="${imageId}"></i>
                            </span>
                        </div>
                    `);
    
                    // Initialize radial progress bar separately for each image
                    setTimeout(() => {
                        circle[imageId] = radialIndicator('.' + classId, {
                            radius: 50,
                            barColor: '#6dd873',
                            barWidth: 8,
                            initValue: 0,
                            barBgColor: '#e4e4e4',
                            percentage: true
                        });
                    }, 50);
    
                    // Upload resized image instead of original
                    filesData.ajaxCall(1, resizedBlob, imageId, function(progress, fileID, response) {
                        if (progress && circle[fileID]) {
                            circle[fileID].animate(progress * 100);
                        }
    
                        if (!progress && response.success) {
                            uploadFiles['images'][fileID] = { "uploaded": true, 'id': response.id };
    
                            if (circle[fileID]) {
                                circle[fileID].animate(100);
                            }
    
                            if (uploadClicked) {
                                $("#addPlace").prop('disabled', false).click();
                            }
                        }
                    });
    
                    if (files.length === increment) {
                        element.val(""); // Reset file input after all files are processed
                    }
                };
    
                reader.readAsDataURL(resizedBlob);
            });
        });
    
        setTimeout(function() {
            var height = $(".image-preview-wrapper").height();
            var parentHeight = $(".image-upload-wrapper").height();
            if (parentHeight < height) {
                $(".image-upload").css("height", height);
            }
        }, 50);
    })
    .on("click",".close-icon",function () {
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
        if( uploadFiles['images'][id] !=undefined )
        {
            delete uploadFiles['images'][id];
        }
})
 
       .on("change", ".tn-upload", function (e) {
            var elements = document.getElementsByClassName("tn-close-icon");
            for (var i = 0; i < elements.length; i++) {
                elements[i].click();
            }
        
            var files = e.target.files;
            var element = $(this);
            var increment = 0;
        
            $.each(files, function (i, file) {
                resizeImage(file, 100, 150, function (resizedBlob) { // Resize image before processing
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var FileType = file.type;
                        var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1)).toLowerCase();
        
                        if ($.inArray(fileExtension, imageacceptedExtensions) === -1) {
                            files=[];
                            messageDisplay("Invalid File");
                            return false;
                        }
        
                        increment++;
                        tnImageId++;
                        tnFiles[tnImageId] = [resizedBlob]; // Use resized image
                        uploadFiles['thumbnails'][tnImageId] = { "uploaded": false };

                        console.log("Initializing radial progress for tnImageId:", tnImageId);
                        let classId = 'circlechart_img_' + tnImageId;
                        $(".tn-preview-wrapper").append(`
                            <div class="col-sm-4 mt-2 position-relative tn-preview overflow-hidden" data-id="${tnImageId}">
                                <div class="position-absolute circlechart ${classId}" style="width: 100%; height: 100%" data-id="${tnImageId}"></div>
                                <img src="${e.target.result}" style="width: 100%; height: 100%">
                                <span class="bg-white circle tn-close-icon" data-id="${tnImageId}">
                                    <i class="fas fa-times position-absolute" data-id="${tnImageId}"></i>
                                </span>
                            </div>
                        `);

                        console.log("Checking element:", document.querySelector('.' + classId));
        
                        // Initialize radial progress bar separately for each image
                        setTimeout(() => {
                            if (document.querySelector('.' + classId)) { // Ensure the element exists
                                circle[tnImageId] = radialIndicator('.' + classId, {
                                    radius: 50,
                                    barColor: '#6dd873',
                                    barWidth: 8,
                                    initValue: 0,
                                    barBgColor: '#e4e4e4',
                                    percentage: true
                                });
                            } else {
                                console.error("Radial progress element not found for tnImageId:", tnImageId);
                            }
                        }, 100); // Short delay to allow the DOM to update
                        
                        /*setTimeout(() => {
                            circle[tnImageId] = radialIndicator('.' + classId, {
                                radius: 50,
                                barColor: '#6dd873',
                                barWidth: 8,
                                initValue: 0,
                                barBgColor: '#e4e4e4',
                                percentage: true
                            });
                        }, 50);*/
        
                        // Upload resized image instead of original
                        filesData.ajaxCall(3, resizedBlob, tnImageId, function (progress, fileID, response) {
                            /*if (progress && circle[fileID]) {
                                circle[fileID].animate(progress * 100);
                            }*/
                            if (progress) {
                                if (circle[fileID] !== undefined) {
                                    circle[fileID].animate(progress * 100);
                                } else {
                                    console.error("Radial progress not found for fileID:", fileID);
                                }
                            }
                                
                            if (!progress && response.success) {
                                uploadFiles['thumbnails'][fileID] = { "uploaded": true, 'id': response.id };
        
                                if (uploadClicked) {
                                    $("#addPlace").prop('disabled', false).click();
                                }
                            }
                        });
        
                        if (files.length === increment) {
                            element.val(""); // Reset file input after all files are processed
                        }
                    };
        
                    reader.readAsDataURL(resizedBlob);
                });
            });
        
            setTimeout(function () {
                var height = $(".tn-preview-wrapper").height();
                var parentHeight = $(".image-upload-wrapper").height();
                if (parentHeight < height) {
                    $(".tn-upload").css("height", height);
                }
            }, 50);
        })
        
   .on("click",".tn-close-icon",function () {
        var id=$(this).data("id");
        var edit=$(this).data("edit");
        if(edit)
        {
            $(".tn-preview[data-id='"+id+"']").remove();
            deletedThumbnails.push(id);
        }else
        {
            $(".tn-preview[data-id='"+id+"']").remove();
            if(tnFiles[id]!=undefined)
            {
                delete  tnFiles[id];
            }
        }
            if( uploadFiles['thumbnails'][id] !=undefined )
            {
                delete uploadFiles['thumbnails'][id];
            }
    })
    .on("click","#addPlace",function(){
        var element=$(this);
        var countryElement=$("#country");
        var stateElement=$("#states");
        var cityElement=$("#cities");
        var error=false;
        var countryText=$('#country option:selected').text();
        var placeElement=$("#place-name");
        var cityId=cityElement.val();
        var stateId=stateElement.val();
        var countryId=countryElement.val();
        var description = $.trim($('#description').val());
        var placeName=placeElement.val();
        var fileDetails=[];
        if(countryId=='')
        {
            messageDisplay("Please select country");
            return  false;
        }
        if(countryText.toLowerCase()=='india') {
            if (stateId == '') {
                messageDisplay("Please select state");
                return false;
            }
        }
        if(cityId=='')
        {
            messageDisplay("Please select city");
            return  false;
        }
        if(placeName=='')
        {
            messageDisplay("Please enter place name");
            return  false;
        }
        if(description=='')
        {
            messageDisplay("Please enter description");
            return  false;
        }
        var imageFileIds=[];
        var tnFileIds=[];
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
        for(var k in uploadFiles['thumbnails'])
        {
            if(!uploadFiles['thumbnails'][k]['uploaded'])
            {
                error=true;
                break;
            }
            tnFileIds.push(uploadFiles['thumbnails'][k]['id']);
            fileIds.push(uploadFiles['thumbnails'][k]['id']);
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
            messageDisplay('files not uploaded.. please try again..');
            element.html('Submit');
            element.prop('disabled',false);
            return false;
        }
        let mandatorytotalLanguages=[];
        let totalLanguages=[];
        var placeId=$("#placeId").val();
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
                    messageDisplay("Please select file lanaguage");
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
            element.html('submit');
            element.prop('disabled',false);
            return false;
        }
        if($(".image-preview").length==0)
        {
            messageDisplay("Please Upload Image Files");
            element.html('submit');
            element.prop('disabled',false);
            return false;
        }
        if($(".tn-preview").length==0)
        {
            messageDisplay("Please Upload Thumbnail Files");
            element.html('submit');
            element.prop('disabled',false);
            return false;
        }
        if($(".file-uploads").length==0)
        {
            messageDisplay("Please Upload Audio Files");
            element.html('submit');
            element.prop('disabled',false);
            return false;
        }
        if(mandatorytotalLanguages.length!=mandatoryLanguages.length)
        {
            uploadClicked=false;
            messageDisplay("Please Select mandatory languages hindi and english",2000);
            element.html('submit');
            element.prop('disabled',false);
            return false;
        }
        var formData=new FormData();
        formData.append("country_id",countryId);
        formData.append("state_id",stateId);
        formData.append("city_id",cityId);
        formData.append("description",description);
        formData.append("place_name",placeName);
        formData.append("place_id",placeId);
        formData.append("file_details",JSON.stringify(fileDetails));
        formData.append("deleted_audio",JSON.stringify(deletedAudio));
        formData.append("deleted_images",JSON.stringify(deletedImages));
        formData.append("deleted_thumbnails",JSON.stringify(deletedThumbnails));
        formData.append("images",imageFileIds);
        formData.append("thumbnails",tnFileIds);
        formData.append("file_Ids",fileIds);
        ajaxData('/a_dMin/edit-place',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+'/a_dMin/places';
                },2000);
            }else{
                element.prop('disabled',false);
                messageDisplay(response.message);
                element.html('Submit');
            }
        });
    }).on("click",".add-control",function(){
        addedRow++;
        var rowsCount= $(".file-uploads").length;
        postData('/admin/file-upload-row',{'row_number':addedRow,"rows_count":rowsCount},function(response){
            $("#file-upload-wrapper").append(response);
        });
    }).on("click",".remove-control",function(){
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

