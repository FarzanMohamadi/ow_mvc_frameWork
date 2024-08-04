function initPublishDateField(className){
    var show = $(className)[0].classList.toggle("show");
    if(show){
        $(className)[0].style.display= "table";
    }else{
        $(className)[0].style.display= "none";
    }
}
$(document).ready(function() {
    $('#NewsTitleInput').on('input propertychange', function() {
        CharLimit(this, 520);
    });
    $('input[name ="title_frmEnglishSupport"]').on('input propertychange', function() {
        CharLimit(this, 520);
    });
});

function CharLimit(input, maxChar) {
    var len = $(input).val().length;
    if (len > maxChar) {
        $(input).val($(input).val().substring(0, maxChar));
    }
}

function removeNewsById(event, removeUrl, confirmText){

    event.preventDefault();
    var answer = $.confirm(confirmText);
    answer.buttons.ok.action = function() {
        window.location.href = removeUrl;
    };
}