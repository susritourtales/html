
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
    }); function initPagination() {


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
                        url: BASE_URL + '/admin/load-executives-list',
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
    $("body").on("click", ".delete-executive", function() {
		var id = $(this).data("id");
		$(".delete-conform-button").attr("data-id", id);
        var spoid = "#spo" + id;
        $(".modal-title").html("Delete " + $(spoid).html());
		$("#deleteEntityModal").modal("show");
	}).on("click", '.delete-conform-button', function() {
		$(this).prop("disabled", true);
		var id = $(this).attr("data-id");
		postData("/a_dMin/delete-executive", {'id': id}, function(response) {
			var jsonRespnse = parseJsonData(response);
			messageDisplay(jsonRespnse.message);
			if (jsonRespnse.success) {
				setTimeout(function() {
					window.location.href = BASE_URL + "/a_dMin/executives";
				}, 3000);
			} else {
				$(".delete-conform-button").prop("disabled", true);
			}
		})
	}).on("click",".pay",function () {
        var id=$(this).data("id");
        $(".pay-confirm-button").attr("data-id",id);
        var spoid = "#spo" + id;
        $(".modal-title").html("Pay " + $(spoid).html());
        $("#payModal").modal("show");
    }) .on("click",'.pay-confirm-button',function () {
        $(this).prop("disabled",true);
        var id = $(this).attr("data-id");
        var amount = $('#tbamount').val();
        var tref = $('#tbtref').val();
        if(amount == "")
        {
            messageDisplay("Please enter amount");
            $(".pay-confirm-button").prop("disabled",false);
            return false;
        }
        if(tref == "")
        {
            messageDisplay("Please enter Transaction ref");
            $(".pay-confirm-button").prop("disabled",false);
            return false;
        }
        postData("/admin/pay-executive", {id:id, amt: amount, tr:tref},function (response){
            var jsonRespnse = parseJsonData(response);
            messageDisplay(jsonRespnse.message);
            if(jsonRespnse.success) {
                setTimeout(function(){
                    window.location.reload();
                },2000);
            }else {
                $(".pay-confirm-button").prop("disabled",true);
            }
        })
    }) .on('click', '.fa-sort',  function (){
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
            url: BASE_URL+'/admin/load-executives-list',
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
            { }
        });
    }

    $('.cbc').click(function() {
        var value = '0';
        if ($(this).is(':checked')) {
            value = '1';
        } 
        var formData=new FormData();
        formData.append('id',$(this).attr('id'));
        formData.append('val',value);
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/a_dMin/modify-executive',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                messageDisplay(data.message);
                setTimeout(function() {
					window.location.href = BASE_URL + "/a_dMin/executives";
				}, 3000);
            },
            error: function()
            { }
        });
    });

    $('.cc').click(function() {
        var url = $(this).data('url');
        window.location.href = url;
    });
});