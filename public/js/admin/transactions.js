$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
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
                    var id = $('#hid').val();
                    var formData = new FormData();
                    formData.append('filter', JSON.stringify(filter));
                    formData.append('id', id);
                    formData.append('type', 'pagination');
                    formData.append('page_number', pageNumber);
                    ajaxCall = $.ajax({
                        type: "POST",
                        url: BASE_URL + '/a_dMin/load-commissions-payments-list',
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