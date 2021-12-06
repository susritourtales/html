
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
    $("#table").find('thead th').each(function(){
        let data=$(this).data();
        if(data.placeholder && data.input && data.type!='select'){
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
    $('table').on('click', 'tbody tr td', function(){
        let id=$(this).data("id");
        if(id)
        {
            window.location = BASE_URL+'/a_dMin/sponsors-transactions/'+id;
        }
    });

    $("#table").on("change", ".cbc", function (event) {
        var divid = '#div_' + $(this).val();
        if(this.id.startsWith('f')){
            if(confirm("Are you sure you want to apply 50% discount for " + $(divid).html() + " ?")){
                if(ajaxCall!=null)
                {
                    ajaxCall.abort();
                }
                
                var formData=new FormData();
                formData.append('sid', $(this).val().trim());
                ajaxCall=  $.ajax({
                    type: "POST",
                    url: BASE_URL+'/admin/admin/sponsor-disc50',
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
        }
        else{
            if(confirm("Are you sure you want to terminate " + $(divid).html() + " ?")){
                if(ajaxCall!=null)
                {
                    ajaxCall.abort();
                }                
                var formData=new FormData();
                formData.append('sid', $(this).val());
                ajaxCall=  $.ajax({
                    type: "POST",
                    url: BASE_URL+'/admin/admin/sponsor-terminate',
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
        }
        //event.preventDefault();
        event.stopImmediatePropagation(); 
        //return false;
    });

    function initPagination() {
        var items = parseInt($(".records").val());
        $("#listPager").html('');
        if(items>10) {
            $('.pagination').pagination({
                items: items,
                itemsOnPage: 10,
                currentPage: 1,
                onPageClick: function (pageNumber) {
                    postData("/admin/admin/load-sponsors-performance", {
                        "uid":window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1),
                        "page_number": pageNumber,
                        'type': 'pagination'
                    }, function (response) {
                        $("tbody").html(response);
                    });
                }
            });
        }
    }
    initPagination();
    $("body").on('click', '.fa-sort',  function (){
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
        var uidStr = window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1);
        filter['uid']={'text':uidStr,'order':''};
        var formData=new FormData();
        formData.append('filter',JSON.stringify(filter));
        formData.append('type','search');
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/admin/admin/load-sponsors-performance',
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