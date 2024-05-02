var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var flagFile=[];
var mediaFiles={};
var imageId=0;
var addedRow=1;
var totalFilesCount;
var uploadClicked=false;
var uploadFiles={'images':{},"attachment":{}};
var circle={};
$(document).ready(function ()
{
    function initializeSelectize() {
        $('#country').selectize({
            create: true,
            sortField: 'text'
        });
    }
    initializeSelectize();
    $("body").on("change",".image-upload",function(e){
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
        setTimeout(function(){},10);
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

    })
    .on("click","#addCountry",function(){
        var countryElement=$("#country");
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        var countryName=countryElement.val();
        var fileDetails=[];

        if(countryName=='')
        {
            element.prop('disabled',false);
            element.html('Submit');
            messageDisplay("Please enter country name");
            return  false;
        }
        if(imageFiles.length==0)
         {
            messageDisplay("please select image files");
            error=true;
            return false;
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
        console.log(uploadFiles,error);

        if(error)
        {
            messageDisplay("Unable to upload image files");
            element.prop('disabled',false);
            element.html('Submit');
            return  false;
        }
        var formData=new FormData();
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);
        formData.append("country_name",countryName);
        formData.append("file_details",JSON.stringify(fileDetails));
        if(jQuery.isEmptyObject(imageFiles))
        {
            messageDisplay("Please Upload Image Files");
            return false;
        }
        ajaxData('/a_dMin/add-country',formData,function(response){
            if(response.success)
            {
                messageDisplay("added successfully");
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/countries";
                },2000);
            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});

