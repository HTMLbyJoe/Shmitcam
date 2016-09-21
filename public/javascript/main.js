var cam_el = document.querySelector('.cam');
var cam_backbround_el = document.querySelector('.cam-background');

function refreshWebcam() {
    var webcam_url = SHMITCAM.url + '?' + Date.now();
    var img = new Image();
    img.src = webcam_url;

    img.addEventListener('load', function(){
        updateBackgroundImage(cam_el, webcam_url);
        updateBackgroundImage(cam_backbround_el, webcam_url);
    });
}

function updateBackgroundImage(element, url) {
    element.style.backgroundImage = 'url("' + url + '")';
}

window.setInterval(refreshWebcam, SHMITCAM.refresh_interval);

function postToTumblr() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/save/tumblr');
    xhr.send(null);

    xhr.addEventListener('readystatechange', function(){
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            window.open(response.permalink);
        }
    });
}
