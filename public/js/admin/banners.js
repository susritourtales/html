$(document).ready(function () {
    var acceptedImageExtensions = ["jpg", "jpeg", "png", "pgm"];
    var userImageCollection = [];
    var editUserImageCollection = [];
    $("body").on("change","#place-cover",function (e) {
        var element = $(this);
        if ($(this).get(0).files.length !== 0) {
            var files = e.target.files;
            $.each(files, function (i, file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var FileType = files[i].type;
                    var filename = files[i].name;
                    var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
                    var Extension = fileExtension.toLowerCase();
                    if ($.inArray(Extension, acceptedImageExtensions) === -1) {
                        messageDisplay("Invalid File", 1500, "error");
                        return false;
                    }
                    $("#preview").removeClass("d-none").attr("src", e.target.result);
                    if ($.isEmptyObject(userImageCollection)) {
                        userImageCollection = [];
                    }
                    userImageCollection=[];
                    userImageCollection.push(file);
                };
                reader.readAsDataURL(file);
            });

        }
    }).on("click","#c2F2ZUhvd3Rv-add-banner",function () {
        var element = $(this);


        if(userImageCollection == "" || userImageCollection.length == 0){
            messageDisplay("Please Select Image",1500,"error");
            $("#banner-title").focus();
            return false;
        }


        var formData = new FormData();
        $.each(userImageCollection, function (i, file) {
            formData.append("image", file)
        });
        element.html('Please wait...');

        element.prop('disabled',true);
        ajaxData("/a_dMin/banners", formData, function (data) {
            data = parseJsonData(data);
            if (data.success) {
                element.removeAttr("disabled");
                messageDisplay(data.message, 1500, "success");
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            } else {
                messageDisplay(data.message, 1800, "error");
            }
        })
    }).on("click", ".deletebanner", function () {

        var element = $(this);
        var id = element.attr("data-id");
        $("#deleteBanner").attr("data-id",id);
    }).on("click", "#deleteBanner", function () {
        var id = $(this).attr("data-id");

        $.post(BASE_URL + "/a_dMin/delete-banner", {
            id: id,
        }, function (data) {

            if (data.success) {
                messageDisplay(data.message, 1500, "success");
                $(".banner-image[data-id='"+id+"']").remove();
                $("#deleteBannerModal").modal('toggle');
            } else {
                messageDisplay(data.message, 1800, "error");
            }
        })

    })
});