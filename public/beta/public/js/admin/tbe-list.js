
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
    $("#extTable").find('thead th').each(function(){
        let data=$(this).data();
        if(data.placeholder && data.input){
            let place=`<div class="position-relative custom-thead">
                <input type="text" class="form-control search border-0 text-white"  placeholder="${data.placeholder}" data-id="${data.input}">
                <div class="arrows">
                    <i class="fas fa-sort" data-id="${data.input}"></i>
                    <i class="fas fa-sort-down d-none" data-id="${data.input}"></i>
                    <i class="fas fa-sort-up d-none" data-id="${data.input}"></i>
                </div>
            </div>`;
            filter[data.input]={'text':'','order':''};
            $(this).html(place);
        }
    });
    function initPagination()
    {
        $("#listPager").html('');
        var items = parseInt($(".records").val());

        if(items>10) {
            $('.pagination').pagination({
                items: items,
                itemsOnPage: 10,
                currentPage: 1,
                onPageClick: function (pageNumber) {
                    if (ajaxCall != null) {
                        ajaxCall.abort();
                    }
                    var formData = new FormData();
                    formData.append('filter', JSON.stringify(filter));
                    formData.append('type', 'pagination');
                    formData.append('page_number', pageNumber);
                    ajaxCall = $.ajax({
                        type: "POST",
                        url: BASE_URL + '/a_dMin/load-tbe-list/' + $('#hid').val(),
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            console.log(data);
                            ajaxCall = null;
                            $("tbody").html(data);
                        },
                        error: function () {
                        }
                    });
                }
            });
        }
    }

    initPagination();
    $("body").on("click",".delete-tbe",function (e) {
        var tbeId=$(this).data("id");
        $(".delete-conform-button").attr("data-id",tbeId);
        $("#deleteTBEModal").modal("show");
        e.stopPropagation();
    }) .on("click",'.delete-conform-button',function () {
        $(this).prop("disabled",true);
        var tbeId = $(this).attr("data-id");
        postData("/admin/admin/delete-tbe", {tbe_id:tbeId},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
            if(jsonRespnse.success) {
                setTimeout(function(){
                    window.location.reload();
                },2000);
            }else {
                $(".delete-conform-button").prop("disabled",true);
            }
        })
    }).on('click', 'table tbody tr', function(){
        let id=$(this).data("id");
        if(id)
        {
            window.location = BASE_URL+'/a_dMin/tbe-tourists/'+id;
        }
    }).on('click', '.fa-sort',  function (){
        $('.fa-sort').removeClass("d-none");
        $('.fa-sort-up').addClass("d-none");
        $('.fa-sort-down').addClass("d-none");
        let dataId=$(this).data("id");
        for(var keys in filter)
        {
            if(keys!=dataId)
            {
                filter[keys]['order']="";
            }else{

                filter[keys]['order']=1;
            }
        }

        $(this).addClass('d-none');
        $(this).parent(".arrows").find('.fa-sort-up').removeClass('d-none');
        filterData();

    }).on('click', '.fa-sort-up',  function(){
        $(this).addClass('d-none');
        let dataId=$(this).data("id");
        for(let keys in filter)
        {

            if(keys!=dataId)
            {
                filter[keys]['order']="";
            }else{
                filter[keys]['order']=-1;
            }
        }
        $(this).parent(".arrows").find('.fa-sort-down').removeClass('d-none');
        filterData();

    }).on('click', '.fa-sort-down',function(){
        $(this).addClass('d-none');
        let dataId=$(this).data("id");
        for(let keys in filter)
        {
            if(keys!=dataId)
            {
                filter[keys]['order']="";
            }else{
                filter[keys]['order']=1;
            }
        }
        $(this).parent(".arrows").find('.fa-sort-up').removeClass('d-none');
        filterData();
    }).on("keyup",".search",function(){
        var dataId=$(this).data("id");
        filter[dataId]['text']=$.trim($(this).val());
        filterData();
    });
    function filterData()
    {
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        var formData=new FormData();
        formData.append('filter',JSON.stringify(filter));
        formData.append('type','search');
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL + '/a_dMin/load-tbe-list/' + $('#hid').val(),
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                ajaxCall=null;
                $(".records").remove();

                $("tbody").html(data);
                initPagination()
            },
            error: function()
            {

            }
        });
    }

    $('input[type=checkbox]').change(function () {
        var divid = '#div_' + $(this).val();
        var mblid = '#mbl_' + $(this).val();
        var confStr = "Are you sure you want to disable ";
        var cbval = 0;
        if($(this).is(":checked")){
            confStr = "Are you sure you want to enable ";
            cbval = 1;
        }
        if(confirm(confStr + $(divid).html() + " ?")){
            if(ajaxCall!=null)
            {
                ajaxCall.abort();
            }                
            var formData=new FormData();
            formData.append('id', $(this).val());
            formData.append('aval', cbval);
            formData.append('mobile', $(mblid).html());
            ajaxCall=  $.ajax({
                type: "POST",
                url: BASE_URL+'/admin/admin/disable-tbe',
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
        }
        else
            $(this).prop('checked',false)
    });
});