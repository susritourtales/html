$('table').on('click', 'tbody tr', function(){
    let id=$(this).data("id");
    if(id)
    {
        window.location = BASE_URL+'/a_dMin/edit-subscription-plan/'+id;
    }
});