
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
            window.location = BASE_URL+'/a_dMin/edit-promoter-parameters/'+id;
        }
    }); 

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
        var formData=new FormData();
        formData.append('filter',JSON.stringify(filter));
        formData.append('type','search');
    }
});