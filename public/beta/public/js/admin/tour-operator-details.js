var mediaFiles={};
var mediaFilesacceptedExtensions = [ "mp4","mpeg",'avi','mpg',"jpg", "png","jpeg"];
$(document).ready(function(){

    var nowDate = new Date();
    var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
      var companydateElement=$("#company-registration-date");
      var companydatevalue=companydateElement.val();
    companydateElement.datetimepicker({
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
        maxDate: today,
        useCurrent: false,
        format: "YYYY-MM-DD"
    }).on("dp.change", function (e){
    });
    companydateElement.val(companydatevalue);
   $("body").on("click","#tour-operator-accept",function(){

             postData("/admin/admin/updateStatus",{"tour_operator_id":tourOperatorId,'status':2},function(response){
                  if(response.success)
                  {
                           messageDisplay(response.message);
                           setTimeout(function(){
                               window.location.reload();
                           },1000);
                  }else{
                      messageDisplay(response.message);
                  }
             });
   }).on("click","#tour-operator-reject",function()
   {
       postData("/admin/admin/updateStatus",{"tour_operator_id":tourOperatorId,'status':3},function(response){
           if(response.success)
           {
               messageDisplay(response.message);
               setTimeout(function(){
                   window.location.reload();
               },1000);
           }else{
               messageDisplay(response.message);
           }
       });
   }).on("click","#submit-discount",function(){
            var discount=$('input[name="discount"]');
            var companyNameElement=$('.company-name');
            var companyPanNumberElement=$('.company-pan-number');
            var llpinRefNoElement=$(".llpin-ref-no");
            var companyStateElement=$(".company-state");
            var companyRegistrationDateElement=$(".company-registration-date");
            var companyContactNameElement=$('.company-contact-name');
            var companyMobileNumberElement=$('.company-mobile-number');
            var companyUrlElement=$('.company-url');
            var companyEmailElement=$('.company-email');
            var companyDesignationElement=$('.company-designation');
            var coordinatorDesignationElement=$('.coordinator-designation');
            var coordinatorNameElement=$('.coordinator-name');
            var coordinatorMobileElement=$('.coordinator-mobile');
            var coordinatorEmailElement=$('.coordinator-email');
            var coordinatorName1Element=$('.coordinator-name-1');
            var coordinatorMobile1Element=$('.coordinator-mobile-1');
            var coordinatorEmail1Element=$('.coordinator-email-1');
            var coordinatorDesignation1Element=$('.coordinator-designation-1');

            var applyDiscount=0;
            var percentage=$("#percentage").val();
            var duration=$("#duration").val();
           var company=companyNameElement.val();
           var companyPanNumber=companyPanNumberElement.val();
           var companyRegistrationDate=companyRegistrationDateElement.val();
           var companyState=companyStateElement.val();
           var llpinRefNo=llpinRefNoElement.val();
           var companyUrl=companyUrlElement.val();
           var companyDesignation=companyDesignationElement.val();
           var coordinatorDesignation=coordinatorDesignationElement.val();
           var coordinatorDesignation1=coordinatorDesignation1Element.val();
           var companyContactName=companyContactNameElement.val();
           var companyMobileNumber=companyMobileNumberElement.val();
           var companyEmail=companyEmailElement.val();

           var coordinatorName=coordinatorNameElement.val();
           var coordinatorMobile=coordinatorMobileElement.val();
           var coordinatorEmail=coordinatorEmailElement.val();
           var coordinatorName1=coordinatorName1Element.val();
           var coordinatorMobile1=coordinatorMobile1Element.val();
           var coordinatorEmail1=coordinatorEmail1Element.val();

           if($("#apply-discount").prop('checked') === true)
            {
              applyDiscount=1;
            }

            if(applyDiscount)
            {
               if(percentage=="")
               {
                   messageDisplay("Please Enter discount Value",2000);
                   return false;
               }

               if(percentage>100)
               {
                   messageDisplay("Please Enter correct discount Value",2000);
                   return false;
               }
            }

            if(company==="")
            {
               messageDisplay("Please Enter company name",2000);
               return false;
            }
            if(companyPanNumber==="")
            {
               messageDisplay("Please Enter company pan number",2000);
               return false;
            }
            if(companyContactName==="")
            {
               messageDisplay("Please Enter company Contact Person Name",2000);
               return false;
            }
            if(companyMobileNumber==="")
            {
               messageDisplay("Please Enter company Contact Mobile Number",2000);
               return false;
            }
            if(companyEmail==="")
            {
              messageDisplay("Please Enter company Contact Email",2000);
             return false;
            }

            if(coordinatorName==="")
            {
                messageDisplay("Please Enter coordinator name",2000);
                return false;
            }

            if(coordinatorMobile==="")
            {
                 messageDisplay("Please Enter coordinator mobile",2000);
                 return false;
            }

            if(coordinatorEmail==="")
            {
                  messageDisplay("Please Enter coordinator email",2000);
                  return false;
            }
            if(coordinatorDesignation==="")
            {
                   messageDisplay("Please Enter coordinator Designation",2000);
                    return false;
            }


            var formData=new FormData();
           formData.append("apply_discount",applyDiscount);
           formData.append("percentage",percentage);
           formData.append("duration",duration);
           formData.append("tour_operator_id",tourOperatorId);
           formData.append("company_name",company);
           formData.append("company_pan_number",companyPanNumber);
           formData.append("company_contact_name",companyContactName);
           formData.append("company_email",companyEmail);
           formData.append("company_registration_date",companyRegistrationDate);
           formData.append("company_state",companyState);
           formData.append("llpin_ref_no",llpinRefNo);
           formData.append("company_url",companyUrl);
           formData.append("company_designation",companyDesignation);
           formData.append("company_mobile_number",companyMobileNumber);
           formData.append("coordinator_name",coordinatorName);
           formData.append("coordinator_mobile",coordinatorMobile);
           formData.append("coordinator_email",coordinatorEmail);
           formData.append("coordinator_name_1",coordinatorName1);
           formData.append("coordinator_mobile_1",coordinatorMobile1);
           formData.append("coordinator_email_1",coordinatorEmail1);
           formData.append("coordinator_designation",coordinatorDesignation);
           formData.append("coordinator_designation_1",coordinatorDesignation1);

           if(mediaFiles['company_registration']) {
                $.each( mediaFiles['company_registration'],function(i,file){
                     formData.append('company_registration',file);
               });
           }
       if(mediaFiles['coordinator_registration']) {
           $.each( mediaFiles['coordinator_registration'],function(i,file){
               formData.append('coordinator_registration',file);
           });
       }
       if(mediaFiles['coordinator_registration_1']) {
           $.each( mediaFiles['coordinator_registration_1'],function(i,file){
               formData.append('coordinator_registration_1',file);
           });
       }
       var element=$(this);
       element.html("Please Wait...");
       element.prop('disabled',true);
          ajaxData('/a_dMin/tour-operator-update',formData,function (response) {
              messageDisplay(response.message);

              if(response.success) {
                  setTimeout(function () {
                      window.location.reload();
                  },2000);

                        }else{
                  element.prop('disabled',false);

                  element.html("submit");
                        }
          });
   })
       .on("change",".company-registration",function (e) {
       var files = e.target.files;
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

               if ($.inArray(Extension, mediaFilesacceptedExtensions) === -1)
               {
                   files=[];
                   element.val("");
                   messageDisplay("Invalid File");
                   return false;
               }

               incerement++;

               mediaFiles["company_registration"]=[];
               mediaFiles["company_registration"].push(file);
               $(".company-registration-view-certificate").attr("href",e.target.result);

               $('.company-registration-certificate-name').html(filename);

               if(files.length == incerement){
                   element.val("");
               }

           };
           reader.readAsDataURL(file);
       });
   })
       .on("change",".coordinator-registration",function (e) {
       var files = e.target.files;
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

               if ($.inArray(Extension, mediaFilesacceptedExtensions) === -1)
               {
                   files=[];
                   element.val("");
                   messageDisplay("Invalid File");
                   return false;
               }

               incerement++;

               mediaFiles["coordinator_registration"]=[];
               mediaFiles["coordinator_registration"].push(file);
               $(".coordinator-registration-view-certificate").attr("href",e.target.result);

               $('.coordinator-registration-certificate-name').html(filename);

               if(files.length == incerement){
                   element.val("");
               }

           };
           reader.readAsDataURL(file);
       });
   }).on("change",".coordinator-registration-1",function (e) {
       var files = e.target.files;
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

               if ($.inArray(Extension, mediaFilesacceptedExtensions) === -1)
               {
                   files=[];
                   element.val("");
                   messageDisplay("Invalid File");
                   return false;
               }

               incerement++;

               mediaFiles["coordinator_registration_1"]=[];
               mediaFiles["coordinator_registration_1"].push(file);
               $(".coordinator-registration-1-view-certificate").attr("href",e.target.result);

               $('.coordinator-registration-1-certificate-name').html(filename);

               if(files.length == incerement){
                   element.val("");
               }

           };
           reader.readAsDataURL(file);
       });
   });
});