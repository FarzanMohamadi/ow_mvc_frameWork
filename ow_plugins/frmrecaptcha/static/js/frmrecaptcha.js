function resetCaptcha() {
    grecaptcha.reset();
    grecaptcha.execute();
    resetLimit--;
    if(resetLimit <= 0){
        clearInterval(executeInterval);
    }
}

var resetLimit = 10;
var executeInterval;

var reCAPTCHAOnloadCallback = function() {
    grecaptcha.execute();
    executeInterval = setInterval(resetCaptcha, 90000);
};