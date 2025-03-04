$(document).ready(function() {
    $('#photo').change(function(event) {
        var file = event.target.files[0];
        if (file && file.type.match('image.*')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#preview').hide();
        }
    });

    const form = document.getElementById('profileForm');
    form.addEventListener('submit', function(event) {
        var element=$('#submit');
        element.html('Please wait...');
        element.prop('disabled',true);
        
        const fileInput = document.querySelector('input[type="file"]');
        const maxFileSize = 2 * 1024 * 1024; // 2MB in bytes
        
        if (!form.checkValidity()) {
            event.preventDefault(); 
            event.stopPropagation(); 
        } else {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                if (file.size > maxFileSize) {
                    event.preventDefault(); 
                    alert('The selected file is too large. Please choose a file smaller than 2MB.');
                    return;
                }
            }
            event.preventDefault();
            const formData = new FormData(form);
            fetch('/twistt/enabler/edit', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (response.ok && contentType && contentType.includes('application/json')) {
                    return response.json(); 
                } else if (!response.ok) {
                    return response.json().then(json => {
                        throw new Error(json.message || 'Network response was not ok');
                    });
                } else {
                    return response.text().then(text => {
                        console.error('Unexpected response format:', text);
                        throw new Error('Unexpected response format: ' + text);
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    messageDisplay(data.message);
                    console.log('Enabler profile saved successfully!', data);
                    setTimeout(function(){
                        location.reload();
                    },2000);
                } else {
                    messageDisplay('Error submitting form: ' + data.message);
                    console.error('Error submitting form:', data.message);
                }
            })
            .catch(error => {
                messageDisplay('Error submitting form: ' + error.message);
                console.error('Error submitting form:', error);
            });
        }
        form.classList.add('was-validated');
        element.html('Submit');
        element.prop('disabled',false);
    });
});