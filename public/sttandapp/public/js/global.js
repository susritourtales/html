var mandatoryLanguages=["1",'42'];
function postData(url, parameters, callback)
{
    url = BASE_URL + url;
    //  console.log(parameters);
    try
    {
        $.post(url, parameters, function (data) {

            if (typeof callback == "function")
            {
                callback(data);
            }
        });
    }catch (error)
    {
        console.log(error);
    }

}
function parseJsonData(data) {
    return $.parseJSON(JSON.stringify(data));
}
function messageDisplay(message, timeOut)
{
    if ($.trim(timeOut) == "") {
        timeOut = 2500;
    }
    var element = $("#feedbackSection");
    if(message)
    {
        $("div#feedbackSection span").html(message);
    }else{
        $("div#feedbackSection span").html("Something Went Worng Try Agian");
    }

    if(timeOut==0)
    {
        element.hide();
        return false;
    }
    element.animate({
        height: 70
    }, 300).show();
    if (timeOut != -1) 
    {
        setTimeout(function (){
            element.animate({
                height: 0
            }, 300, function () {
                element.hide();
            });
        }, timeOut);
    }
}
function ajaxData(url, parameters, callback)
{
    try
    {
        url = BASE_URL + url;
        $.ajax({
            type: "POST",
            url: url,
            data: parameters,
            dataType: "json",
            cache: false,
            contentType: false,
            processData: false,
            xhr: function(){
                var xhr = new window.XMLHttpRequest();
                
                //Upload progress
                
                xhr.upload.addEventListener("progress", function(evt)
                {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        //Do something with upload progress
                        console.log(percentComplete);
                    }
                }, false);
                
                //Download progress
                
                xhr.addEventListener("progress", function(evt){
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        //Do something with download progress
                        console.log(percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function(data)
            {
                if (typeof callback == "function")
                {
                    callback(data);
                }
            },
            error: function()
            {
                if (typeof callback == "function") {
                    callback('{"success": false,"message": "Oops..! Something went wrong try again later"}');
                }
            }
        });
    }catch (error){
        console.log(error);
    }
}
var ajaxCallFilesCount=1;
function AjaxCallFiles(){

    this.totalFiles=1;
}

AjaxCallFiles.prototype.ajaxCall=function(fileType,file,fileID,callback)
{
    //console.log(this.fileId);
    this.totalFiles++;
    var formData=new FormData();
    if(fileType==1)
    {

        formData.append("image_files",file);
    } if(fileType==2)
{

    formData.append("attachments",file);
}
    try
    {

        $.ajax({
            type: "POST",
            url: BASE_URL+'/a_dMin/upload-files',
            data: formData,
            dataType: "json",
            cache: false,
            contentType: false,
            processData: false,
            xhr: function(){
                var xhr = new window.XMLHttpRequest();

                //Upload progress

                xhr.upload.addEventListener("progress", function(evt)
                {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        //Do something with upload progress

                        callback(true,percentComplete,fileID);
                    }
                }, false);

                //Download progress

                xhr.addEventListener("progress", function(evt){
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        //Do something with download progress
                        console.log(percentComplete,this.totalFiles);
                        callback(true,fileID,percentComplete);
                    }
                }, false);
                return xhr;
            },
            success: function(data)
            {
                if (typeof callback == "function")
                {
                    callback(false,fileID,data);
                }
            },
            error: function()
            {
                if (typeof callback == "function") {
                    callback(false,fileID,'{"success": false,"message": "Oops..! Something went wrong try again later"}');
                }
            }
        });
    }catch (error){
        console.log(error);
    }
};
var filesData=new AjaxCallFiles();
$(document).ready(function(){
   $('.custom-collapse').on('click', function(){
       $(this).toggleClass('custom-collapse-open');
   });
    $(".mobilenumberValidations").keydown(function (e){
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57 || e.keyCode==190 )) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
     $("body").on("keydown", ".price",function (e){
            console.log(e.keyCode);
         if(e.keyCode == 110 || e.keyCode == 190){
             var val = $(this).val();
             val=val.trim();
             if (val.indexOf('.') > -1) {
                 e.preventDefault();
             }
             if(val.length==0)
             {
                 e.preventDefault();
             }else{
                 return;
             }
         }
         if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
             (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
             (e.keyCode >= 35 && e.keyCode <= 40)) {
             return;
         }
         if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57  )) && (e.keyCode < 96 || e.keyCode > 105) && e.keyCode != 110) {
             e.preventDefault();
         }
     }).on("keydown", ".number", function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57  )) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});