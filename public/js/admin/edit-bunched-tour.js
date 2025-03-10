var imageacceptedExtensions = ["jpg", "png","jpeg"];
var imageFiles={};
var imageId=0;
var deletedImages=[];
var uploadClicked=false;
var uploadFiles={'images':{}};
var circle={};
$(document).ready(function ()
{
    imageId=$(".image-preview:last").data("id");
    $("#tales").removeAttr('class').searchableOptionList();
    
    $("body").on("change","#taletype",function(){
        var tt=$(this).val();
        $("#tales Option").each(function(){$(this).remove();});
        postData('/admin/get-tales',{"tt":tt},function(response){
            var options='';
            if(response.success)
            {
                var list=response.tales;
                for(var s=0;s<list.length;s++)
                {
                    options +='<option value="'+list[s].id+'">'+list[s].place_name+'</option>'
                }
                if(tt == '1'){ // India Tales
                    $("#state-wrapper").removeClass('d-none');
                    $("#country-wrapper").addClass('d-none');
                }else{ // World Tales
                    $("#state-wrapper").addClass('d-none');
                    $("#country-wrapper").removeClass('d-none');
                }
                $(".sol-container").remove();
                $("#tales").html(options).val('').removeAttr('class').prop("multiple",true).searchableOptionList();
            }
        });
    }).on("change","#country",function(){
        var countryId=$(this).val();
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
        });
    }).on("change","#states",function(){
        var stateId=$(this).val();
        postData('/admin/get-cities',{"state_id":stateId,"country_id":"101"},function(response){
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
        }else{
            $(".image-preview[data-id='"+id+"']").remove();
            if(imageFiles[id]!=undefined)
            {
                delete imageFiles[id];
            }
        }
            if( uploadFiles['images'][id] !=undefined )
            {
                delete uploadFiles['images'][id];
            }
    }).on("click","#editbt",function(){
        var ttElement=$("#taletype");
        var error=false;
        var countryElement=$("#country");
        var stateElement=$("#states");
        var cityElement=$("#cities");
        var talesElement=$("#tales");
        var tnameElement=$("#tname");
        var tDescElement=$("#description");
        var taletype=ttElement.val();
        var talesList=talesElement.val();
        var taleName = tnameElement.val();
        var taleDesc = tDescElement.val();
        var country = countryElement.val();
        var state = stateElement.val();
        var city = cityElement.val();
        if(taletype=='')
        {
            messageDisplay("Please select tale type");
            return  false;
        }
        if(talesList==null)
        {
            messageDisplay("Please select tales to be added to bunched tale");
            return  false;
        }
        if(taletype=='1'){
            if(state == ''){
                messageDisplay("Please select Provincial State");
                return  false;
            }
        }else{
            if(country==''){
                messageDisplay("Please select Provincial Country");
                return  false;
            }
        }
        if(city==''){
            messageDisplay("Please select Provincial City");
            return  false;
        }
        if(taleName==''){
            messageDisplay("Please enter Tale Name");
            return  false;
        }
        if(taleDesc==''){
            messageDisplay("Please enter Tale Description");
            return  false;
        }
        var imageFileIds=[];
        var fileIds=[];
        var element=$(this);
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
        var tId=$("#taleId").val();
        if($(".image-preview").length==0)
        {
            messageDisplay("Please Upload Image Files");
            return false;
        }
        var formData=new FormData();
        formData.append("tale_type",taletype);
        formData.append("tales_list",talesList);
        formData.append("tale_name",taleName);
        formData.append("id",tId);
        formData.append("tale_desc",taleDesc);
        formData.append("country",country);
        formData.append("state",state);
        formData.append("city",city);
        formData.append("deleted_images",JSON.stringify(deletedImages));
        formData.append("images",imageFileIds);
        formData.append("file_Ids",fileIds);
        ajaxData('/a_dMin/edit-bunched-tour',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/bunched-tour-list";
                },2000);
            }else{
                messageDisplay(response.message,3000);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    });
});

