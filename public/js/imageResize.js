function resizeImage(file, maxWidth, maxHeight, callback) {
    const reader = new FileReader();
    reader.onload = function (event) {
        const img = new Image();
        img.onload = function () {
            let width = img.width;
            let height = img.height;

            // Maintain aspect ratio
            if (width > height) {
                if (width > maxWidth) {
                    height *= maxWidth / width;
                    width = maxWidth;
                }
            } else {
                if (height > maxHeight) {
                    width *= maxHeight / height;
                    height = maxHeight;
                }
            }

            // Create canvas and draw resized image
            const canvas = document.createElement("canvas");
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, width, height);

            // Convert canvas to Base64 (JPEG or PNG format)
        let imageType = "image/jpeg"; // Default to JPEG (change to "image/png" if needed)
        let resizedDataUrl = canvas.toDataURL(imageType, 1.0);
        
        // Convert Base64 to File object
        let resizedFile = dataURLtoFile(resizedDataUrl, file.name.replace(/\.[^/.]+$/, "") + ".jpg");
        callback(resizedFile);
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function dataURLtoFile(dataUrl, fileName) {
    let arr = dataUrl.split(",");
    let mime = arr[0].match(/:(.*?);/)[1];
    let bstr = atob(arr[1]);
    let n = bstr.length;
    let u8arr = new Uint8Array(n);
    
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }
    
    return new File([u8arr], fileName, { type: mime });
}