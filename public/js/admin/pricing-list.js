
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
    $("#extTable").find('thead th').each(function(){
        let data=$(this).data();
        if(data.placeholder && data.input && data.type!='select'){/* if(data.placeholder && data.input){ */
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
   $('table').on('click', 'tbody tr', function(){
        let id=$(this).data("id");
        if(id)
        {
            window.location = BASE_URL+'/a_dMin/edit-pricing/'+id;
        }
    }); 
    /*  function initPagination() {
        var items = parseInt($(".records").val());
        $("#listPager").html('');

        if(items>10) {
            $('.pagination').pagination({
                items: items,
                itemsOnPage: 10,
                currentPage: 1,
                onPageClick: function (pageNumber) {
                    postData("/admin/admin/load-pricing-list", {
                        "page_number": pageNumber,
                        'type': 'pagination'
                    }, function (response) {
                        $("tbody").html(response);
                    });
                }
            });
        }
    }
    initPagination(); */

    $("body")
    /*.on("blur",'.tpn',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, pn:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    }).on("blur",'.tp',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, p:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tsv',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, sv:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tsd',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, sd:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.ted',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, ed:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tspv',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, spv:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tfrd',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, frd:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tsrd',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, srd:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tcp',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, cp:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tscp',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, scp:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tmd',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, md:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.ttf',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, tf:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tnp',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, np:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tnd',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, nd:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    })
    .on("blur",'.tgst',function () {
        var pid = $(this).attr("data-id");
        var val = $(this).val();
        postData("/admin/admin/edit-pricing", {pid:pid, gst:val},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
        })
    }) */
    .on('click', '.fa-sort',  function (){
        $('.fa-sort').removeClass("d-none");
        $('.fa-sort-up').addClass("d-none");
        $('.fa-sort-down').addClass("d-none");
        $(this).addClass('d-none');
        $(this).parent(".arrows").find('.fa-sort-down').removeClass('d-none');
    }).on('click', '.fa-sort-up',  function (){
        $(this).addClass('d-none');
        $(this).parent(".arrows").find('.fa-sort-down').removeClass('d-none');
    }).on('click', '.fa-sort-down',  function (){
        $(this).addClass('d-none');
        $(this).parent(".arrows").find('.fa-sort-up').removeClass('d-none');
    }).on("keyup",".search",function () {

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
        console.log(dataId);
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
        /* ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/admin/admin/load-pricing-list',
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
        }); */
    }
});