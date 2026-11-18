document.addEventListener("DOMContentLoaded", function () {
    const slideshow = document.getElementById("boardgame-slideshow");
    const homepageImages = window.homepageImages || [];

    if (!slideshow || homepageImages.length < 2) {
        return;
    }

    let currentImage = 0;

    setInterval(function () {
        currentImage = (currentImage + 1) % homepageImages.length;
        slideshow.src = homepageImages[currentImage];
    }, 5000);
});
