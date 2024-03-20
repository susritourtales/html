$(document).ready(function(){
    $('#language').selectize({
        create: true,
        sortField: 'text'
    });
    $("body").on("click","#addLanguage",function(){
        var id=$("#language").val();
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        postData("/a_dMin/add-language",{'id':id},function(response){
            if(response)
            {
                element.prop('disabled',false);
                element.html('Submit');
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/languages";
                },2000);
            }
        });
    });
});