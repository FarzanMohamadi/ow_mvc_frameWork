function startTimer(duration, display) {
    var timer = duration, minutes, seconds;
    setInterval(function () {
        minutes = parseInt(timer / 60, 10);
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            timer = duration;
        }
    }, 1000);
}

function changeDemoTheme(url){
    themeValue = document.getElementById('demo_themes_items').value;
    var data = {"themeValue":themeValue};
    $.ajax({
        url: url,
        type: 'post',
        dataType : "json",
        data: data,
        success: function(result){
            window.location = window.location;
        }
    });
}