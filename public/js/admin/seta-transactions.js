
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
  
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
                        url: BASE_URL + '/admin/admin/load-seta-transactions',
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
});