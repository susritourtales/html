
$(document).ready(function () {
    var filter={};
    var ajaxCall=null;
    $('#end_date').datetimepicker({
        icons: {
            time: 'fa fa-clock',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'glyphicon glyphicon-screenshot',
            clear: 'fa fa-trash',
            close: 'glyphicon glyphicon-remove'
        },
        useCurrent: true,
        format: "YYYY-MM-DD"
    }).on('dp.show', function(){
        $("#export").addClass('d-none');
        var date = new Date($("#start_date").val());
        date.setDate(date.getDate() + 1);
        $('#end_date').data("DateTimePicker").minDate(date);
        $('#end_date').data("DateTimePicker").maxDate(today);
    });

    var nowDate = new Date();
    var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

    $('#start_date').datetimepicker({
        icons: {
            time: 'fa fa-clock',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'glyphicon glyphicon-screenshot',
            clear: 'fa fa-trash',
            close: 'glyphicon glyphicon-remove'
        },
        /* minDate: today, */
        useCurrent: false,
        format: "YYYY-MM-DD"
    }).on("dp.change", function (e){
        $("#export").addClass('d-none');
          let end_date=$("#end_date");
        end_date.prop("disabled", false);
        var expiryDate=end_date.val();
        if(expiryDate!='')
        {
            end_date.data("DateTimePicker").minDate(e.date);
            if(expiryDate<$("#start_date").val())
            {
                end_date.val('');
            }
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
                    var formData = new FormData();
                    formData.append('type', 'pagination');
                    formData.append('page_number', pageNumber);
                    ajaxCall = $.ajax({
                        type: "POST",
                        url: BASE_URL + '/admin/admin/load-financial-statements',
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

    $('#export').click(function(){  
        /* var excel_data = $('#expTable').html();  
        var exdata = encodeURIComponent(excel_data);
        var page = $('#baseurl').val()+"/excel.php?data=" + exdata;  
        window.location = page;  */ 
        $("#exp").val("1");
        $("#from").val($("#start_date").val());
        $("#to").val($("#end_date").val());
        $("#frmExp").submit();
    }); 

    $("body").on("click","#getFS",function(){
        if(ajaxCall!=null)
        {
            ajaxCall.abort();
        }
        var error=false;
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);

        /* var month=$.trim($('#month').val());
        var year=$.trim($('#year').val()); */
        var startDateElement=$("#start_date");
        var endDateElement=$("#end_date");
        var startDate=startDateElement.val();
        var endDate=endDateElement.val();
        var optType=$('input[name="optType"]:checked').val();
        var formData=new FormData();
        /* formData.append('m',month);
        formData.append('y',year); */
        formData.append("start_date",startDate);
        formData.append("end_date",endDate);
        formData.append("optType",optType);
        
        ajaxCall=  $.ajax({
            type: "POST",
            url: BASE_URL+'/admin/admin/load-financial-statements',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(data)
            {
                ajaxCall=null;
                $(".records").remove();
                $("tbody").html(data);
                initPagination();
                element.prop('disabled',false);
                element.html('Submit');
                $("#export").removeClass('d-none');
            },
            error: function()
            {
                 messageDisplay(response.message);
                 element.prop('disabled',false);
                 element.html('Submit');
            }
        });
    })
});