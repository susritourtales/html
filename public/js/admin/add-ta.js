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
    $("body").on("click","#addTa",function(){
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var fileDetails=[];
        var taName=$.trim($('#taName').val());
        var taMobile=$.trim($('#taMobile').val());
        var taEmail=$.trim($('#taEmail').val());
        var aeName=$.trim($('#aeName').val());
        var aeMobile=$.trim($('#aeMobile').val());
        var aeEmail=$.trim($('#aeEmail').val());
        var cMobile=$.trim($('#cMobile').val());
        var cName=$.trim($('#cName').val());
        var tac=$.trim($('#tac').val());
        

        if(taName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter TA Name");
            return  false;
        }
        if(taMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter TA Mobile");
            return  false;
        }
        if(taEmail == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter TA Email");
            return  false;
        }
        if(aeName == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter AE Name");
            return  false;
        }
        if(aeMobile == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter AE Mobile");
            return  false;
        }
        if(aeEmail == ''){
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter AE Email");
            return  false;
        }
        if(cName == ''){
            cMobile = '';
        }
        if($(".image-preview").length==0)
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please Upload Logo");
            return false;
        }
        if(tac == '')
        {
            element.html('submit');
            element.prop('disabled',false);
            messageDisplay("Please enter TAC");
            return  false;
        }

        uploadClicked=true;
            var imageFileIds=[];
            var fileIds=[];
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
           
        var formData=new FormData();

        formData.append("ta_name",taName);
        formData.append("ta_mobile",taMobile);
        formData.append("ta_email",taEmail);
        formData.append("ae_name",aeName);
        formData.append("ae_mobile",aeMobile);
        formData.append("ae_email",aeEmail);
        formData.append("cons_mobile",cMobile);
        formData.append("tac",tac);
        formData.append("file_details",JSON.stringify(fileDetails));
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);

        ajaxData('/a_dMin/add-ta',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/ta-list";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
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
        },10);

    }).on("click",".close-icon",function () {
        var id=$(this).data("id");
        $(".image-preview[data-id='"+id+"']").remove();
        if(imageFiles[id]!=undefined)
        {
            delete  imageFiles[id];
        }
            if(uploadFiles['images'][id] !=undefined) {
                delete uploadFiles['images'][id]
            }

    });
    $('#cMobile').blur(function(){
        $('#cName').val('');
        var cMobile=$.trim($('#cMobile').val());
        if(cMobile != ''){
            var formData=new FormData();
            formData.append("cons_mobile",cMobile);

            ajaxData('/a_dMin/get-se-name',formData,function(response){
                if(response.success)
                {
                    $('#cName').val(response.message);
    
                }else{
                    messageDisplay(response.message);
                    element.prop('disabled',false);
                    element.html('Submit');
                }
            });
        }
    });
});