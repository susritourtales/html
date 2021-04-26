$(document).ready(function(){
    var imageacceptedExtensions = ["jpg", "png","jpeg"];
    var mediaFiles=[];


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
        minDate: today,
        useCurrent: false,
        format: "YYYY-MM-DD"
    }).on("dp.change", function (e){
    });

    $("body").on("click","#addSeason",function(){
       var mobileNumberElement=$("#mobile");
       var countryCode=$("#country-code").val();
       var tourTypeElement=$("#tour_type");
       var activationDateElement=$("#start_date");
       var durationInDaysElement=$("#no_of_days");
       var placeIds=$("#places_list").val();
       var mobileNumber=mobileNumberElement.val();
       var tourType=tourTypeElement.val();
       var activationDate=activationDateElement.val();
       var duration=durationInDaysElement.val();
           mobileNumber=$.trim(mobileNumber);
       var packageId='';
            if(mobileNumber=='')
            {
                messageDisplay("Please enter mobile number",2000);
                return false;
            }
        if(tourType=='')
        {
            messageDisplay("Please select tour type",2000);
            return false;
        }
        if(tourType==5)
        {
            var packageElement=$("#packages_list");
               packageId=packageElement.val();

             placeIds=$("#packages_list option:selected").data("id");

            if(placeIds=='')
            {
                messageDisplay("Please select places",2000);
                return false;
            }

        }else if(tourType==3)
        {
             placeIds=$("#city_list option:selected").data("id");

          //  placeIds=$("#city_list").val();
            if(placeIds=='')
            {
                messageDisplay("Please select cities",2000);
                return false;
            }
        }else{

            if(placeIds=='')
            {
                messageDisplay("Please select packages",2000);
                return false;
            }
        }


        if(activationDate=='')
        {
            messageDisplay("Please select activation date",2000);
            return false;
        }
        if(activationDate=='')
        {
            messageDisplay("Please enter duration",2000);
            return false;
        }
        var formData=new FormData();
        formData.append("mobile",mobileNumber);
        formData.append("country_code",countryCode);
        formData.append("activation_date",activationDate);
        formData.append("duration_days",duration);
        formData.append("tour_type",tourType);
        formData.append("place_ids",placeIds);
        formData.append("package_id",packageId);


        $.each(mediaFiles,function(i,file){
            formData.append('image_file',file);
        });
        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
        ajaxData('/a_dMin/add-privilage-user',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                   window.location.href=BASE_URL+"/a_dMin/privilage-user-list";
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
    }).on("change","#tour_type",function(){
        let tourType=$(this).val();
        postData("/admin/admin/placesListForPrivilageUser",{'tour_type':tourType},function(response){
            $(".sol-container").remove();

            // $("#places_list").css('display','block');
            if(response.success)
            {
                var placesWrapper=$("#places_wrapper");
                var seasonalSpecialsWrapper=$("#seasonal_specials_wrapper");
                var cityWrapper=$("#city_wrapper");
                var list=response.placesList;
                var options='';

                if(tourType==5)
                {
                    seasonalSpecialsWrapper.removeClass('d-none');
                    for(let s=0;s<list.length;s++)
                    {
                        options +='<option value="'+list[s].seasonal_special_id+'" data-id="'+list[s].place_ids+'">'+list[s].seasonal_name+'</option>';

                    }
                    placesWrapper.addClass('d-none');
                    cityWrapper.addClass('d-none');

                    $('#packages_list').html(options).val('').removeAttr('class').prop("multiple",true).searchableOptionList();

                }else if(tourType==3)
                {
                    seasonalSpecialsWrapper.addClass('d-none');
                    placesWrapper.addClass('d-none');
                    cityWrapper.removeClass('d-none');
                    for(let s=0;s<list.length;s++)
                    {
                        options +='<option value="'+list[s].place_id+'" data-id="'+list[s].place_id+'">'+list[s].city_name+'</option>';
                    }
                    $('#city_list').html(options).val('').removeAttr('class').prop("multiple",true).searchableOptionList();

                }else
                {
                    seasonalSpecialsWrapper.addClass('d-none');

                    for(let s=0;s<list.length;s++)
                    {
                        options +='<option value="'+list[s].tourism_place_id+'">'+list[s].place_name+','+list[s].city_name+','+list[s].country_name+'</option>';
                    }
                    cityWrapper.addClass('d-none');
                    placesWrapper.removeClass('d-none');
                    $('#places_list').html(options).val('').removeAttr('class').prop("multiple",true).searchableOptionList();
                }

            }



         //   $("#places_list_single").addClass("d-none");


        });
    });
});