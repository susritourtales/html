$(document).ready(function(){
    var imageacceptedExtensions = ["jpg", "png","jpeg"];
    var mediaFiles=[];
    $('#places_list').searchableOptionList();
    /* $('#season_end_date').datetimepicker({
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
        useCurrent: false,
        format: "YYYY-MM-DD"
    }).on('dp.show', function(){
        var date = new Date($("#season_start_date").val());
        date.setDate(date.getDate() + 1);
        $('#season_end_date').data("DateTimePicker").minDate(date);
    });

    var nowDate = new Date();
    var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

    $('#season_start_date').datetimepicker({
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
        minDate: today,
        useCurrent: false,
        format: "YYYY-MM-DD"
    }).on("dp.change", function (e){
          let season_end_date=$("#season_end_date");
        season_end_date.prop("disabled", false);
        var expiryDate=season_end_date.val();
        if(expiryDate!='')
        {
            season_end_date.data("DateTimePicker").minDate(e.date);
            if(expiryDate<$("#season_start_date").val())
            {
                season_end_date.val('');
            }
        }
    }); */

    $("body").on("click","#addSeason",function(){
         var seasonNameElement=$("#season_name");
         var descriptionElement=$("#description");
         /* var priceElement=$("#price");
         var discountedPriceElement=$("#discounted_price");
         var seasonStartDateElement=$("#season_start_date");
         var seasonEndDateElement=$("#season_end_date");
         var noOfDaysElement=$("#no_of_days"); */

         var seasonName=seasonNameElement.val();
         var description=descriptionElement.val();
         /* var price=priceElement.val();
         var startDate=seasonStartDateElement.val();
         var endDate=seasonEndDateElement.val();
         var noOfDays=noOfDaysElement.val(); */
        var placeIds=[];
       /* $('input[name="places"]:checked').each(function() {
            console.log(this.value);

            placeIds.push(this.value);
        });*/
       placeIds=$("#places_list").val();

         var tourType=$('input[name="tourtype"]:checked').val();
         //var discountedPrice=discountedPriceElement.val();
                 seasonName=$.trim(seasonName);
          if(seasonName=='')
          {
              messageDisplay("Please enter season name");
              return false;
          }
         /* if(startDate=='')
          {
              messageDisplay("Please enter start date");
              return false;
          }*/
        /* startDate=$.trim(startDate);
         if(startDate!="")
         {

             if(endDate=='')
             {
                 messageDisplay("Please enter end date");
                 return false;
             }

             if(noOfDays=="")
             {
                 messageDisplay("Please enter no of days");
                 return false;
             }
             description=$.trim(description);
             if(description=='')
             {
                 messageDisplay("Please enter description");
                 return false;
             }
             if(price=="")
             {
                 messageDisplay("Please enter price");
                 return false;
             }
             if(discountedPrice=="")
             {
                 messageDisplay("Please enter discount price");
                 return false;
             }
            if(price<discountedPrice)
            {
                messageDisplay("Please enter price greater than discounted price");
                return false;
            }

             if(mediaFiles.length==0)
             {
                 messageDisplay("Please upload file");
                 return false;
             }

             if(placeIds==null || placeIds.length==0)
             {
                 messageDisplay("Please select places");
                 return false;
             }
         } */
         description=$.trim(description);
             if(description=='')
             {
                 messageDisplay("Please enter description");
                 return false;
             }
        if(mediaFiles.length==0)
             {
                 messageDisplay("Please upload file");
                 return false;
             }
        if(placeIds==null || placeIds.length==0)
        {
            messageDisplay("Please select places");
            return false;
        }

        var formData=new FormData();
            formData.append("season_name",seasonName);
            formData.append("description",description);
            /* formData.append("price",price); */
            formData.append("tour_type",tourType);
            formData.append("place_ids",placeIds);
            /* formData.append("start_date",startDate);
            formData.append("end_date",endDate);
            formData.append("discounted_price",discountedPrice);
            formData.append("no_of_days",noOfDays); */

            $.each(mediaFiles,function(i,file){
                  formData.append('image_file',file);
            });
            var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
            ajaxData('/a_dMin/add-seasonal-specials',formData,function(response){
                if(response.success)
                {
                    messageDisplay("added successfully");
                    setTimeout(function(){
                        window.location.href=BASE_URL+"/a_dMin/seasonal-specials-list";
                    },2000);

                }else{
                    messageDisplay(response.message);
                    element.prop('disabled',false);
                    element.html('Submit');
                }
            });
   }).on("change",".upload-file",function(e){
       var files = e.target.files;
       var rowId=$(this).data("id");
       var element=$(this);
       var incerement=0;
       $.each(files, function (i, file) {

           var reader = new FileReader();
           reader.onload = function (e)
           {
               var FileType = files[i].type;
               var filename = files[i].name;
               var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
               var Extension = fileExtension.toLowerCase();

               if ($.inArray(Extension, imageacceptedExtensions) === -1)
               {
                   files=[];
                   element.val("");
                   messageDisplay("Invalid File" );
                   return false;
               }

                   mediaFiles=[];

               mediaFiles.push(file);
               $('.file_name').html(filename);

                   element.val("");

           };
           reader.readAsDataURL(file);
       });
   }).on("change","input[name='tourtype']",function(){
         let tourType=$(this).val();
       postData("/admin/admin/placesListForSeasonalSpecials",{'tour_type':tourType},function(response){
           $(".sol-container").remove();
          // $("#places_list").css('display','block');
           if(response.success)
           {

               var list=response.placesList;
               var options='';
               for(var s=0;s<list.length;s++)
               {
                   options +='<option value="'+list[s].tourism_place_id+'">'+list[s].place_name+','+list[s].city_name+','+list[s].country_name+'</option>';
               }

               $('#places_list').html(options);
           }
            $("#places_list").prop("multiple",true);

               $('#places_list').searchableOptionList();


       });
    });
});