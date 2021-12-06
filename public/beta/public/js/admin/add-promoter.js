$(document).ready(function ()
{
    var ajaxCall=null;
    $("#languages").select2({
    }).on('change', function() {
                     console.log("inside ",$("#languages").val());
        var $selected = $(this).find('option:selected');
        var $container = $('.tags-container');
        console.log($container.length);
        var $list = $('<ul>');
        $selected.each(function(k, v) {
            var $li = $('<li class="tag-selected">' + $(v).text() + '<a class="destroy-tag-selected">×</a></li>');
            $li.children('a.destroy-tag-selected')
                .off('click.select2-copy')
                .on('click.select2-copy', function(e) {
                    var $opt = $(this).data('select2-opt');
                    $opt.prop('selected', false);
                    $opt.parents('select').trigger('change');
                }).data('select2-opt', $(v));
            $list.append($li);
        });
        $container.html('').append($list);
    }).trigger('change');
    
    $("body").on("blur","#mobile",function(){
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        
        var formData=new FormData();
        formData.append('mobile', $(this).val().trim());
        formData.append('action', 'find');
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/admin/admin/add-promoter',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(response)
            {
                if(response.success)
                {
                    $('#name').val(response.name);
                    $('#addPromoter').prop('disabled', false);
                }else{
                    $('#name').val('');
                    $('#addPromoter').prop('disabled', true);
                    messageDisplay(response.message);
                }
            },
            error: function(){}
        });
    }).on("click","#addPromoter",function(){
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        
        var formData=new FormData();
        formData.append('mobile', $('#mobile').val().trim());
        formData.append('action', 'add');
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/admin/admin/add-promoter',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(response)
            {
                if(response.success)
                {
                    messageDisplay(response.message);
                    setTimeout(function(){
                        window.location.reload();
                    },2000);

                }else{
                    messageDisplay(response.message);
                }
            },
            error: function(){}
        });
    });
});