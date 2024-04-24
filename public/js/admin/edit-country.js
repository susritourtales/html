var mediaFilesacceptedExtensions = ["mp3", "mp4", "wav", "mpeg", 'avi', 'mpg'];
var imageacceptedExtensions = ["jpg", "png", "jpeg"];
var imageFiles = {};
var mediaFiles = {};
var flagFile = [];
var imageId = 0;
var addedRow = 1;
var deletedImages = [];
var deletedAudio = [];
var uploadClicked = false;
var uploadFiles = {
	'images': {},
	"attachment": {}
};
var circle = {};
$(document).ready(function() {
	addedRow = $(".file-uploads:last").data("id");
	imageId = $(".image-preview:last").data("id");
	var defaultCountryValue = $('#country').val();
	var $select = $('#country').selectize({
		create: true,
		sortField: 'text'
	});
	console.log(defaultCountryValue);
	$select[0].selectize.setValue(defaultCountryValue);

	$("body").on("change", ".flag-upload-file", function(e) {
			var files = e.target.files;
			var rowId = $(this).data("id");
			var element = $(this);
			var incerement = 0;
			$.each(files, function(i, file) {
				var reader = new FileReader();
				reader.onload = function(e) {
					// console.log(files);
					var FileType = files[i].type;
					var filename = files[i].name;
					var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
					var Extension = fileExtension.toLowerCase();

					if ($.inArray(Extension, imageacceptedExtensions) === -1) {
						files = [];
						element.val("");
						messageDisplay("Invalid File");
						return false;
					}

					flagFile.push(file);

					$('.flag_file_name').html(filename);
					if (files.length == incerement) {
						element.val("");
					}

				};

				reader.readAsDataURL(file);
			});

		})
		.on("change", ".image-upload", function(e) {
			var files = e.target.files;
			var element = $(this);

			var incerement = 0;
			$.each(files, function(i, file) {
				var reader = new FileReader();

				reader.onload = function(e) {
					var FileType = files[i].type;
					var filename = files[i].name;
					var fileExtension = FileType.substr((FileType.lastIndexOf('/') + 1));
					var Extension = fileExtension.toLowerCase();

					if ($.inArray(Extension, imageacceptedExtensions) === -1) {
						files = [];
						messageDisplay("Invalid File");
						return false;
					}
					incerement++;
					imageId++;
					imageFiles[imageId] = [];
					imageFiles[imageId].push(file);

					uploadFiles['images'][imageId] = {
						"uploaded": false
					};
					
					let classId = 'circlechart_img_' + imageId;
					$(".image-preview-wrapper").append('<div class="col-sm-4 mt-2 position-relative image-preview overflow-hidden" data-id="' + imageId + '"><div class="position-absolute circlechart ' + classId + '" style="width: 100%;height: 100%" data-id="' + imageId + '"></div><img src="' + e.target.result + '" style="width: 100%;height: 100%"><span class="bg-white circle close-icon" data-id="' + imageId + '"><i class="fas fa-times position-absolute " data-id="' + imageId + '" ></i></span></div>');
					circle[imageId] = radialIndicator('.' + classId, {
						radius: 50,
						barColor: '#6dd873',
						barWidth: 8,
						initValue: 0,
						barBgColor: '#e4e4e4',
						percentage: true
					});
					
					filesData.ajaxCall(1, file, imageId, function(progress, fileID, response) {
						if (progress) {
							if (circle[fileID]) {
								circle[fileID].animate((fileID * 100));
							}
						}

						if (!progress) {
							if (response.success) {
								if (uploadFiles['images'][fileID] != undefined) {
									uploadFiles['images'][fileID] = {
										"uploaded": true,
										'id': response.id
									};
									if (uploadClicked) {
										var countryElement = $("#addPlace");
										countryElement.prop('disabled', false);
										countryElement.click();
									}
								}
							}
						}
					});

					if (files.length == incerement) {
						element.val("");
					}
				};
				reader.readAsDataURL(file);
			});

			setTimeout(function() {

				var height = $(".image-preview-wrapper").height();
				var parentHeight = $(".image-upload-wrapper").height();
				console.log(parentHeight, height);
				if (parentHeight < height) {
					$(".image-upload").css("height", height);
				}
			}, 10);

		})
		.on("click", ".close-icon", function() {
			var id = $(this).data("id");
			var edit = $(this).data("edit");
			if (edit) {
				$(".image-preview[data-id='" + id + "']").remove();
				deletedImages.push(id);
			} else {
				$(".image-preview[data-id='" + id + "']").remove();
				if (imageFiles[id] != undefined) {
					delete imageFiles[id];
				}
			}
			if (uploadFiles['images'][id] != undefined) {
				delete uploadFiles['images'][id]
			}
		}).on("click", "#addPlace", function() {
			var countryElement = $("#country");
			var error = false;
			var countryName = countryElement.val();
			var fileDetails = [];
			var element = $(this);
			var countryId = $("#countryId").val();

			if (countryName == '') {
				messageDisplay("Please enter country name");
				return false;
			}

			if (imageFiles.length == 0) {
				messageDisplay("please select image files");
				error = true;
				return false;
			}
			let mandatorytotalLanguages = [];
			let totalLanguages = [];
			var imageFileIds = [];
			var fileIds = [];
			uploadClicked = true;

			element.html('Please wait...');
			element.prop('disabled', true);

			for (var k in uploadFiles['images']) {
				if (!uploadFiles['images'][k]['uploaded']) {
					error = true;
					break;
				}
				imageFileIds.push(uploadFiles['images'][k]['id']);
				fileIds.push(uploadFiles['images'][k]['id']);
			}

			if (error) {
				return false;
			}

			if ($(".image-preview").length == 0) {
				messageDisplay("Please Upload Image Files");
				return false;
			}

			var formData = new FormData();
			formData.append("country_name", countryName);
			formData.append("country_id", countryId);
			formData.append("file_details", JSON.stringify(fileDetails));
			formData.append("deleted_audio", JSON.stringify(deletedAudio));
			formData.append("deleted_images", JSON.stringify(deletedImages));
			formData.append("images", imageFileIds);
			formData.append("file_Ids", fileIds);

			if ($(".image-preview").length == 0) {
				messageDisplay("Please Upload Image Files");
				return false;
			}
			element.html('Please wait...');
			element.prop('disabled', true);
			ajaxData('/a_dMin/edit-country', formData, function(response) {
				if (response.success) {
					messageDisplay(response.message);
					setTimeout(function() {
						window.location.href = BASE_URL + "/a_dMin/countries";
					}, 2000);

				} else {
					messageDisplay(response.message);

					element.prop('disabled', false);
					element.html('Submit');
				}
			});
		})
});