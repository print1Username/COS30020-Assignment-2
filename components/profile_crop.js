document.addEventListener("DOMContentLoaded", function () {
    const uploadButton = document.getElementById("uploadPhotoButton");
    const uploadInput = document.getElementById("profileImageInput");
    const cropModal = document.getElementById("profileCropModal");
    const cropCanvas = document.getElementById("profileCropCanvas");
    const cropCancelButton = document.getElementById("cropCancelButton");
    const cropConfirmButton = document.getElementById("cropConfirmButton");
    const profileImage = document.getElementById("profileImage");

    if (!uploadButton || !uploadInput || !cropModal || !cropCanvas || !cropCancelButton || !cropConfirmButton) {
        console.error("Profile crop: required elements were not found.");
        return;
    }

    const ctx = cropCanvas.getContext("2d");

    let image = new Image();
    let imageLoaded = false;
    let scale = 1;
    let minScale = 1;
    let imageX = 0;
    let imageY = 0;
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let startImageX = 0;
    let startImageY = 0;

    uploadButton.addEventListener("click", function () {
        uploadInput.click();
    });

    uploadInput.addEventListener("change", function () {
        const file = uploadInput.files[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith("image/")) {
            alert("Please select an image file.");
            uploadInput.value = "";
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            image = new Image();

            image.onload = function () {
                imageLoaded = true;
                openCropWindow();
            };

            image.onerror = function () {
                alert("The selected image could not be loaded.");
                uploadInput.value = "";
            };

            image.src = event.target.result;
        };

        reader.onerror = function () {
            alert("The image could not be read.");
            uploadInput.value = "";
        };

        reader.readAsDataURL(file);
    });

    function openCropWindow() {
        cropModal.style.display = "flex";
        cropModal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
        resizeCanvas();
        setInitialImagePosition();
        drawCanvas();
    }

    function resizeCanvas() {
        const container = cropCanvas.parentElement;
        let size = Math.min(container.clientWidth, container.clientHeight);

        if (!size || size < 100) {
            size = 300;
        }

        cropCanvas.width = size;
        cropCanvas.height = size;

        if (imageLoaded) {
            drawCanvas();
        }
    }

    function setInitialImagePosition() {
        const canvasWidth = cropCanvas.width;
        const canvasHeight = cropCanvas.height;

        minScale = Math.max(
            canvasWidth / image.width,
            canvasHeight / image.height
        );

        scale = minScale;

        const imageWidth = image.width * scale;
        const imageHeight = image.height * scale;

        imageX = (canvasWidth - imageWidth) / 2;
        imageY = (canvasHeight - imageHeight) / 2;
    }

    function drawCanvas() {
        if (!imageLoaded) {
            return;
        }

        const canvasWidth = cropCanvas.width;
        const canvasHeight = cropCanvas.height;
        const imageWidth = image.width * scale;
        const imageHeight = image.height * scale;

        ctx.clearRect(0, 0, canvasWidth, canvasHeight);

        ctx.drawImage(
            image,
            imageX,
            imageY,
            imageWidth,
            imageHeight
        );

        ctx.save();
        ctx.strokeStyle = "rgba(255, 255, 255, 0.9)";
        ctx.lineWidth = 2;
        ctx.strokeRect(
            1,
            1,
            canvasWidth - 2,
            canvasHeight - 2
        );
        ctx.restore();
    }

    cropCanvas.addEventListener("pointerdown", function (event) {
        if (!imageLoaded) {
            return;
        }

        isDragging = true;
        dragStartX = event.clientX;
        dragStartY = event.clientY;
        startImageX = imageX;
        startImageY = imageY;

        cropCanvas.setPointerCapture(event.pointerId);
    });

    cropCanvas.addEventListener("pointermove", function (event) {
        if (!isDragging) {
            return;
        }

        const deltaX = event.clientX - dragStartX;
        const deltaY = event.clientY - dragStartY;

        imageX = startImageX + deltaX;
        imageY = startImageY + deltaY;

        constrainImage();
        drawCanvas();
    });

    cropCanvas.addEventListener("pointerup", function () {
        isDragging = false;
    });

    cropCanvas.addEventListener("pointercancel", function () {
        isDragging = false;
    });

    cropCanvas.addEventListener("wheel", function (event) {
        if (!imageLoaded) {
            return;
        }

        event.preventDefault();

        const zoomFactor = event.deltaY < 0 ? 1.05 : 0.95;
        const oldScale = scale;

        scale = Math.max(
            minScale,
            scale * zoomFactor
        );

        const rect = cropCanvas.getBoundingClientRect();
        const mouseX = event.clientX - rect.left;
        const mouseY = event.clientY - rect.top;

        const imagePointX = (mouseX - imageX) / oldScale;
        const imagePointY = (mouseY - imageY) / oldScale;

        imageX = mouseX - imagePointX * scale;
        imageY = mouseY - imagePointY * scale;

        constrainImage();
        drawCanvas();
    }, { passive: false });

    function constrainImage() {
        const canvasWidth = cropCanvas.width;
        const canvasHeight = cropCanvas.height;
        const imageWidth = image.width * scale;
        const imageHeight = image.height * scale;

        if (imageWidth <= canvasWidth) {
            imageX = (canvasWidth - imageWidth) / 2;
        } else {
            const minimumX = canvasWidth - imageWidth;
            imageX = Math.max(
                minimumX,
                Math.min(0, imageX)
            );
        }

        if (imageHeight <= canvasHeight) {
            imageY = (canvasHeight - imageHeight) / 2;
        } else {
            const minimumY = canvasHeight - imageHeight;
            imageY = Math.max(
                minimumY,
                Math.min(0, imageY)
            );
        }
    }

    cropCancelButton.addEventListener("click", function () {
        cropModal.style.display = "none";
        cropModal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
        uploadInput.value = "";
        imageLoaded = false;
    });

    cropConfirmButton.addEventListener("click", function () {
        if (!imageLoaded) {
            return;
        }

        const outputCanvas = document.createElement("canvas");
        outputCanvas.width = 600;
        outputCanvas.height = 600;

        const outputContext = outputCanvas.getContext("2d");

        const sourceX = -imageX / scale;
        const sourceY = -imageY / scale;
        const sourceSize = cropCanvas.width / scale;

        outputContext.drawImage(
            image,
            sourceX,
            sourceY,
            sourceSize,
            sourceSize,
            0,
            0,
            600,
            600
        );

        const croppedImage = outputCanvas.toDataURL(
            "image/jpeg",
            0.9
        );

        /*
         * Update profile picture preview
         */
        if (profileImage) {
            if (profileImage.tagName.toLowerCase() === "img") {
                profileImage.src = croppedImage;
            } else {
                const newProfileImage = document.createElement("img");

                newProfileImage.src = croppedImage;
                newProfileImage.alt = "Profile picture";
                newProfileImage.className = "profile-picture";
                newProfileImage.id = "profileImage";

                profileImage.replaceWith(newProfileImage);
            }
        }

        /*
         * Convert cropped image to file
         */
        outputCanvas.toBlob(function (blob) {
            if (!blob) {
                alert("Unable to create cropped image.");
                return;
            }

            const croppedFile = new File(
                [blob],
                "profile_image.jpg",
                {
                    type: "image/jpeg"
                }
            );

            const dataTransfer = new DataTransfer();

            dataTransfer.items.add(croppedFile);
            uploadInput.files = dataTransfer.files;

            /*
             * Close crop window
             */
            cropModal.style.display = "none";
            cropModal.setAttribute("aria-hidden", "true");
            document.body.style.overflow = "";
            imageLoaded = false;
        }, "image/jpeg", 0.9);
    });

    window.addEventListener("resize", function () {
        if (
            imageLoaded &&
            cropModal.style.display === "flex"
        ) {
            resizeCanvas();
        }
    });
});