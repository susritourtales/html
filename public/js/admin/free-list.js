
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
        //console.log($("#table"));
    $("#table").find('thead th').each(function(){
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
    function initPagination() {
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
                    var ftt = $('#cftt').val();
                    var formData = new FormData();
                    formData.append('filter', JSON.stringify(filter));
                    formData.append('type', 'pagination');
                    formData.append('page_number', pageNumber);
                    formData.append('ftt',ftt);
                    ajaxCall = $.ajax({
                        type: "POST",
                        url: BASE_URL + '/admin/load-free-tour-list',
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
    $("body").on("click",".delete-place",function () {
        var taleId=$(this).data("id");
        var placeId=$(this).data("place");
        $(".delete-conform-button").attr("data-id",taleId).attr("data-place",placeId);
        $("#deleteEntityModal").modal("show");
    }).on("click",'.delete-conform-button',function(){
        $(this).prop("disabled",true);
        var taleId = $(this).attr("data-id");
        var placeId = $(this).attr("data-place");
        postData("/admin/delete-tour-tale", {tale_id:taleId,place_id:placeId},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
            if(jsonRespnse.success){
                setTimeout(function(){
                    window.location.reload();
                },2000);
            }else{
                $(".delete-conform-button").prop("disabled",true);
            }
        })
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
    }).on("click","#ftt",function(){
        var cftt = $('#cftt').val();
        var ftt = '5';
        if(cftt == '5')
            ftt = '6';
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        var formData = new FormData();
        formData.append('filter', JSON.stringify(filter));
        formData.append('type', 'search');
        //formData.append('page_number', 1);
        formData.append('ftt',ftt);
        ajaxCall = $.ajax({
            type: "POST",
            url: BASE_URL + '/admin/load-free-tour-list',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                ajaxCall=null;
                $(".records").remove();
                $("tbody").html(data);
                initPagination();
                $("#cftt").val(ftt);
                if(ftt == '5'){
                    $("#ftt").text("Free World Tales");
                    $("#ttBtn").text("Add Free Indian Tales");
                    $("#att").attr('href') = '/a_dMin/add-free-tour/' + $("#ttEncIT").val();
                }else{
                    $("#ftt").text("Free Indian Tales");
                    $("#ttBtn").text("Add Free World Tales");
                    $("#att").attr('href') = '/a_dMin/add-free-tour/' + $("#ttEncWT").val();
                }
            },
            error: function () {
            }
        });
    });
    function filterData()
    {
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        var ftt = $('#cftt').val();
        var formData=new FormData();
        formData.append('filter',JSON.stringify(filter));
        formData.append('type','search');
        formData.append('ftt',ftt);
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/admin/load-free-tour-list',
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
            {   }
        });
    }
});