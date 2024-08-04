function removeBlogById(event, removeUrl, confirmText){

    event.preventDefault();
    var answer = $.confirm(confirmText);
    answer.buttons.ok.action = function() {
        window.location.href = removeUrl;
    };
}