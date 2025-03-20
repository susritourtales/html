$(document).ready(function(){
    function initializeDateTimePicker(input, dd) {
        input.datetimepicker({
            format: 'DD-MM-YYYY',
            defaultDate: dd
        });
    }
    initializeDateTimePicker($('#sqs_sd'), sqs_sd);
    initializeDateTimePicker($('#sqs_ed'), sqs_ed);
    initializeDateTimePicker($('#sts_sd'), sts_sd);
    initializeDateTimePicker($('#sts_ed'), sts_ed);
    function updateEndDateMinValue(start, end) {
        var startDate = start.data('date');
        end.datetimepicker('minDate', startDate ? moment(startDate, 'DD-MM-YYYY').add(1, 'days') : false);
    }
    $('#sqs_sd').on('dp.change', function(e) {
        var start = $(this);
        var end = $('#sqs_ed');
        updateEndDateMinValue(start, end);
    });
    $('#sts_sd').on('dp.change', function(e) {
        var start = $(this);
        var end = $('#sts_ed');
        updateEndDateMinValue(start, end);
    });

    $("body").on("click","#updatePricng",function(){
        var pn=$.trim($("#pn").val());
        var qsp_inr=$.trim($("#qsp_inr").val());
        var qsp_usd=$.trim($("#qsp_usd").val());
        var sqsp_inr=$.trim($("#sqsp_inr").val());
        var sqsp_usd=$.trim($("#sqsp_usd").val());
        var sqs_sd=$.trim($("#sqs_sd").val());
        var sqs_ed=$.trim($("#sqs_ed").val());
        var qrp_inr=$.trim($("#qrp_inr").val());
        var qrp_usd=$.trim($("#qrp_usd").val());
        var qd=$.trim($("#qd").val());
        var td=$.trim($("#td").val());
        var mtl=$.trim($("#mtl").val());
        var adp=$.trim($("#adp").val());
        var topp_inr=$.trim($("#topp_inr").val());
        var topp_usd=$.trim($("#topp_usd").val());
        var stsp_inr=$.trim($("#stsp_inr").val());
        var stsp_usd=$.trim($("#stsp_usd").val());
        var sts_sd=$.trim($("#sts_sd").val());
        var sts_ed=$.trim($("#sts_ed").val());
        var tppp_inr=$.trim($("#tppp_inr").val());
        var tppp_usd=$.trim($("#tppp_usd").val());
        var max_pwds=$.trim($("#max_pwds").val());
        var dp_inr=$.trim($("#dp_inr").val());
        var dp_usd=$.trim($("#dp_usd").val());
        var ccp_inr=$.trim($("#ccp_inr").val());
        var ccp_usd=$.trim($("#ccp_usd").val());
        var tax=$.trim($("#tax").val());
        var cdp=$.trim($("#cdp").val());
        var qtxt=$.trim($("#qtxt").val());
        var ttxt=$.trim($("#ttxt").val());
        var wtxt=$.trim($("#wtxt").val());

        if(pn=='')
        {
            messageDisplay("Please enter plan name");
            return false;
        }
        if(qsp_inr=='')
        {
            messageDisplay("Please enter QUESTT Subscription (INR)");
            return false;
        } 
        if(qsp_usd=='')
        {
            messageDisplay("Please enter QUESTT Subscription (US$)");
            return false;
        } 
        if(sqsp_inr=='')
        {
            messageDisplay("Seasonal Price on QUESTT Subscription - INR");
            return false;
        } 
        if(sqsp_usd=='')
        {
            messageDisplay("Seasonal Price on QUESTT Subscription - US$");
            return false;
        }
        if(sqs_sd=="")
        {
            messageDisplay("Please enter valid Seasonal Price on QUESTT Subscription - Start Date");
            return false;
        }
        if(sqs_ed=="")
        {
            messageDisplay("Please enter valid Seasonal Price on QUESTT Subscription - End Date");
            return false;
        }
        if(qrp_inr=="")
        {
            messageDisplay("Please enter QUESTT RENEWAL PRICE (INR)");
            return false;
        }
        if(qrp_usd=="")
        {
            messageDisplay("Please enter QUESTT RENEWAL PRICE (US$)");
            return false;
        }
        if(qd=="")
        {
            messageDisplay("Please enter QUESTT Duration");
            return false;
        }
        if(td=="")
        {
            messageDisplay("Please enter TWISTT Duration");
            return false;
        }
        if(mtl=="")
        {
            messageDisplay("Please enter Max. Tales Limit (MTL)");
            return false;
        }
        if(adp=="")
        {
            messageDisplay("Please enter Auto-Deletion Period (ADP)");
            return false;
        }
        if(topp_inr=="")
        {
            messageDisplay("Please enter TWISTT on-line purchase price (INR)");
            return false;
        }
        if(topp_usd=="")
        {
            messageDisplay("Please enter TWISTT on-line purchase price (US$)");
            return false;
        }
        if(stsp_inr=="")
        {
            messageDisplay("Please enter Seasonal Price of TWISTT Subscription - INR");
            return false;
        }
        if(stsp_usd=="")
        {
            messageDisplay("Please enter Seasonal Price of TWISTT Subscription - USD");
            return false;
        }
        if(sts_sd=="")
        {
            messageDisplay("Please enter Seasonal Price on TWISTT Subscription - Start Date");
            return false;
        }
        if(sts_ed=="")
        {
            messageDisplay("Please enter Seasonal Price on TWISTT Subscription - End Date");
            return false;
        }
        if(tppp_inr=="")
        {
            messageDisplay("Please enter TWISTT PW Purchase price (INR)");
            return false;
        }
        if(tppp_usd=="")
        {
            messageDisplay("Please enter TWISTT PW Purchase price (USD)");
            return false;
        }
        if(max_pwds=="")
        {
            messageDisplay("Please enter Max. Number of PWs to be entered for future");
            return false;
        }
        if(dp_inr=="")
        {
            messageDisplay("Please enter Price of one discount coupon (INR)");
            return false;
        }
        if(dp_usd=="")
        {
            messageDisplay("Please enter Price of one discount coupon (US$)");
            return false;
        }
        if(ccp_inr=="")
        {
            messageDisplay("Please enter Price of one complementary coupon (INR)");
            return false;
        }
        if(ccp_usd=="")
        {
            messageDisplay("Please enter Price of one complementary coupon (USD)");
            return false;
        }
        if(cdp=="")
        {
            messageDisplay("Please enter Discount offered against Coupon on Price Amount");
            return false;
        } 
        if(tax=="")
        {
            messageDisplay("Please enter tax");
            return false;
        } 
        if(qtxt=="")
        {
            messageDisplay("Please enter questt text");
            return false;
        }
        if(ttxt=="")
        {
            messageDisplay("Please enter twistt text");
            return false;
        }
        if(wtxt=="")
        {
            messageDisplay("Please enter web text");
            return false;
        }
        
        var formData=new FormData();
        var pid=$("#plan_id").val();
        formData.append("qsp_inr",qsp_inr);
        formData.append("qsp_usd",qsp_usd);
        formData.append("sqsp_inr",sqsp_inr);
        formData.append("sqsp_usd",sqsp_usd);
        formData.append("sqs_sd",sqs_sd);
        formData.append("sqs_ed",sqs_ed);
        formData.append("qrp_inr",qrp_inr);
        formData.append("qrp_usd",qrp_usd);
        formData.append("qd",qd);
        formData.append("td",td);
        formData.append("mtl",mtl);
        formData.append("adp",adp);
        formData.append("topp_inr",topp_inr);
        formData.append("topp_usd",topp_usd);
        formData.append("stsp_inr",stsp_inr);
        formData.append("stsp_usd",stsp_usd);
        formData.append("sts_sd",sts_sd);
        formData.append("sts_ed",sts_ed);
        formData.append("tppp_inr",tppp_inr);
        formData.append("tppp_usd",tppp_usd);
        formData.append("max_pwds",max_pwds);
        formData.append("dp_inr",dp_inr);
        formData.append("dp_usd",dp_usd);
        formData.append("ccp_inr",ccp_inr);
        formData.append("ccp_usd",ccp_usd);
        formData.append("tax",tax);
        formData.append("cdp",cdp);
        formData.append("wt",wtxt);
        formData.append("qt",qtxt);
        formData.append("tt",ttxt);
        formData.append("pid",pid);
                
        var element=$(this);
        element.html('Please wait...');
        element.prop('disabled',true);
        ajaxData('/a_dMin/edit-subscription-plan',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/subscription-plans";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});