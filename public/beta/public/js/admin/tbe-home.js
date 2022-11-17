
$(document).ready(function () {
var nowDate = new Date();
var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
$('#tvl_date').datetimepicker({
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
})

$(".dropdown-item").click(function(){
  var selText = $(this).text();
  $(this).parents('.dropdown').find('.dropdown-toggle').html(selText);
});

$("#addSds").click(function(){
  var element=$(this);
  element.html('Please wait...');
  element.prop('disabled',true);

  var tName=$.trim($('#tName').val());
  var mcc=$.trim($('#tcc').val());
  var mobile=$.trim($('#tMobile').val());
  var td=$.trim($('#tvl_date').val());
  var upc=$('#ddupc').html().trim();

  if(tName == ''){
      element.html('submit');
      element.prop('disabled',false);
      messageDisplay("Please enter tourist name");
      return  false;
  }
  if(mcc == ''){
        element.html('submit');
        element.prop('disabled',false);
        messageDisplay("Please enter mobile country code");
        return  false;
    }
  if(mobile == ''){
      element.html('submit');
      element.prop('disabled',false);
      messageDisplay("Please enter tourist mobile number");
      return  false;
  }else{
    if (!(/^\d{10}$/.test(mobile))) {
        element.html('submit');
        element.prop('disabled',false);
        messageDisplay("Invalid mobile number - must be ten digits");
        return false;
    } 
  }
  if(td == ''){
      element.html('submit');
      element.prop('disabled',false);
      messageDisplay("Please enter tourist travel date");
      return  false;
  }
  if(upc == 'Select UPC'){
      element.html('submit');
      element.prop('disabled',false);
      messageDisplay("Please select UPC");
      return  false;
  }
  var formData=new FormData();

  formData.append("tbe_id",$('#htbeId').val());
  formData.append("role",$('#hrole').val());
  formData.append("name",tName);
  formData.append("mobile_country_code",mcc);
  formData.append("mobile",mobile);
  formData.append("tdate",td);
  formData.append("upc",upc);

  ajaxData('/a_dMin/add-ta-sds',formData,function(response){
      if(response.success)
      {
          messageDisplay(response.message);
          setTimeout(function(){
              window.location.href=BASE_URL+"/tbe-home";
          },2000);

      }else{
          messageDisplay(response.message);
          element.prop('disabled',false);
          element.html('Submit');
      }
  });
});


});