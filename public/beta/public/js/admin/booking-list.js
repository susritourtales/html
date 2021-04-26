
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;

    $("#bookingListtable").find('thead th').each(function(){
        let data=$(this).data();
        if(data.placeholder && data.input && data.type!='select')
        {
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
        }else if(data.type=='select')
        {
               let options=`<option value="" class="text-capitalize">${data.placeholder}</option>`;

          //  data.value=JSON.parse(data.value);

               for(let i=0;i<data.value.length ;i++)
               {
                   options+=`<option value="${data.value[i].value}">${data.value[i].text}</option>`
               }
            let place=`<div class="position-relative custom-thead">
                <select  class="form-control search border-0 text-white select font-weight-bold text-capitalize" data-id="${data.input}"></select>
                </div>`;
            filter[data.input]={'text':'','order':''};
            $(this).html(place);
            $(".select[data-id='"+data.input+"']").html(options);
            $(this).removeAttr("data-value")
        }
    });

   /* $('table tr').click(function() {
        window.location = BASE_URL+'/booking-details';
    });*/
    $('table').on('click', 'tbody tr', function(){
           let id=$(this).data("id");
           if(id)
           {
               window.location = BASE_URL+'/a_dMin/download-details/'+id;
           }

    });
    function initPagination() {


        var items = parseInt($(".records").val());
               console.log(items);
        $("#listPager").html('');

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
                        url: BASE_URL + '/admin/admin/load-download-list',
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
        var placeId=$(this).data("id");
        $(".delete-conform-button").attr("data-id",placeId);
        $("#deletePlaceModal").modal("show");
    }) .on("click",'.delete-conform-button',function () {
        $(this).prop("disabled",true);
        var cityId = $(this).attr("data-id");
        postData("/admin/admin/delete-city", {city_id:cityId},function (response){
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
            }else
            {
                filter[keys]['order']=1;
            }
        }
        $(this).parent(".arrows").find('.fa-sort-up').removeClass('d-none');
        filterData();
    }).on("keyup",".search",function(){
        var dataId=$(this).data("id");

        filter[dataId]['text']=$.trim($(this).val());
        filterData();
    }).on("change",".select",function(){
        var dataId=$(this).data("id");
            console.log(dataId);
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
            url: BASE_URL+'/admin/admin/load-download-list',
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
});