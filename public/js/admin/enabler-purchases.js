
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
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
        var items = parseInt($(".records").val());
        $("#listPager").html('');

        if(items>10) {
            $('.pagination').pagination({
				items: items,
				itemsOnPage: 10,
				currentPage: 1,
				onPageClick: function(pageNumber) {
					if (ajaxCall != null) {
						ajaxCall.abort();
					}
					var formData = new FormData();
					formData.append('filter', JSON.stringify(filter));
					formData.append('type', 'pagination');
					formData.append('page_number', pageNumber);
					ajaxCall = $.ajax({
						type: "POST",
						url: BASE_URL + '/a_dMin/load-purchases-list/'+$('#eid').val(),
						data: formData,
						cache: false,
						contentType: false,
						processData: false,
						success: function(data) {
							console.log(data);
							ajaxCall = null;
							$("tbody").html(data);
						},
						error: function() {}
					});
				}
			});
        }
    }
    initPagination();
    $("body").on('change', '.sd',  function (){
        var csd = $(this).val();
        var id = $(this).attr('id');
        postData("/a_dMin/edit-enabler",{'id':id, 'csd':csd},function(response){
            if(response)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/purchases-list/"+$('#eid').val();
                },2000);
            }
        });
    }).on('click', '.fa-sort',  function (){
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
            url: BASE_URL+'/a_dMin/load-purchases-list/'+$('#eid').val(),
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
            {}
        });
    }
});