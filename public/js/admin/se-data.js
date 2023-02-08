
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
                        url: BASE_URL+'/load-se-data',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            console.log(data);
                            ajaxCall = null;
                            var contents = data.substring(data.indexOf("flex-fill\">")+11)
                            $("tbody").html(contents);
                        },
                        error: function (err) {
                            ajaxCall = null;
                            errStr = err.responseText;
                            console.log(errStr);
                            var contents = errStr.substring(errStr.indexOf("flex-fill\">")+11, errStr.indexOf("</dl>"))
                            //$("tbody").html(contents);
                            $("tbody").html("error occured");
                        }
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
            url: BASE_URL+'/load-se-data',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                ajaxCall=null;
                $(".records").remove();
                var contents = data.substring(data.indexOf("<tbody>")+7, data.indexOf("</tbody>")-8)
                $("tbody").html(contents);
                initPagination()
            },
            error: function(err)
            {
              ajaxCall = null;
              errStr = err.responseText;
              console.log(errStr);
              //var contents = errStr.substring(errStr.indexOf("flex-fill\">")+11, errStr.indexOf("</dl>"))
              //$("tbody").html(contents);
              $("tbody").html(errStr);
            }
        });
    }
});