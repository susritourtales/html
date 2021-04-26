$(document).ready(function(){
    $('#ed').datetimepicker({
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
        format: "DD-MM-YYYY"
    }).on('dp.show', function(){
        var date = new Date($("#sd").val());
        date.setDate(date.getDate() + 1);
        $('#ed').data("DateTimePicker").minDate(date);
    });

    var nowDate = new Date();
    var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

    $('#sd').datetimepicker({
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
        format: "DD-MM-YYYY"
    }).on("dp.change", function (e){
        let end_date=$("#ed");
        end_date.prop("disabled", false);
        var expiryDate=end_date.val();
        if(expiryDate!='')
        {
            end_date.data("DateTimePicker").minDate(e.date);
            if(expiryDate<$("#sd").val())
            {
                end_date.val('');
            }
        }
    });

    $("body").on("click","#updatePricng",function(){
        var pn=$.trim($("#pn").val());
        var p=$.trim($("#p").val());
        var sv=$.trim($("#sv").val());
        var spv=$.trim($("#spv").val());
        var sd=$.trim($("#sd").val());
        var ed=$.trim($("#ed").val());
        var frd=$.trim($("#frd").val());
        var srd=$.trim($("#srd").val());
        var cp=$.trim($("#cp").val());
        var scp=$.trim($("#scp").val());
        var md=$.trim($("#md").val());
        var tf=$.trim($("#tf").val());
        var np=$.trim($("#np").val());
        var nd=$.trim($("#nd").val());
        var gst=$.trim($("#gst").val());
        var atxt=$.trim($("#atxt").val());
        var wtxt=$.trim($("#wtxt").val());

        if(pn=='')
        {
            messageDisplay("Please enter plan name");
            return false;
        }
        if(p=='')
        {
            messageDisplay("Please enter price");
            return false;
        } 
        if($("#pricing_id").val() == "2" || $("#pricing_id").val() == "4") {
            if(sd=="")
            {
                messageDisplay("Please enter valid start date");
                return false;
            }
            if(ed=="")
            {
                messageDisplay("Please enter valid end date");
                return false;
            }
        }else{
            if(cp=="")
            {
                messageDisplay("Please enter no of complimentary passwords");
                return false;
            }
            if(scp=="")
            {
                messageDisplay("Please enter sponsor complimentary passwords");
                return false;
            }
            if(md=="")
            {
                messageDisplay("Please enter max tales");
                return false;
            }
            if(tf=="")
            {
                messageDisplay("Please enter time frame");
                return false;
            }
            if(np=="")
            {
                messageDisplay("Please enter perfromance criterion passwords");
                return false;
            }
            if(nd=="")
            {
                messageDisplay("Please enter perfromance criterion days");
                return false;
            }
            /* if(wtxt=="")
            {
                messageDisplay("Please enter web text");
                return false;
            } 
            if(atxt=="")
            {
                messageDisplay("Please enter app text");
                return false;
            }*/
            if(gst=="")
            {
                messageDisplay("Please enter GST");
                return false;
            }
        }
        var formData=new FormData();
        var pid=$("#pricing_id").val();
        formData.append("pn",pn);
        formData.append("p",p);
        formData.append("sv",sv);
        formData.append("spv",spv);
        formData.append("sd",sd);
        formData.append("ed",ed);
        formData.append("frd",frd);
        formData.append("srd",srd);
        formData.append("cp",cp);
        formData.append("scp",scp);
        formData.append("md",md);
        formData.append("tf",tf);
        formData.append("np",np);
        formData.append("nd",nd);
        formData.append("gst",gst);
        formData.append("wt",wtxt);
        formData.append("at",atxt);
        formData.append("pid",pid);
                
        var element=$(this);
        element.html('Please wait...');

        element.prop('disabled',true);
        ajaxData('/a_dMin/edit-pricing',formData,function(response){
            if(response.success)
            {
                messageDisplay(response.message);
                setTimeout(function(){
                    window.location.href=BASE_URL+"/a_dMin/pricing-list";
                },2000);

            }else{
                messageDisplay(response.message);
                element.prop('disabled',false);
                element.html('Submit');
            }
        });
    })
});