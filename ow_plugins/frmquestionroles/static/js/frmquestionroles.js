function deleteQuestionRoles(url) {
    var jc = $.confirm(OW.getLanguageText('frmquestionroles', 'are_you_sure_delete'));
    jc.buttons.ok.action = function () {
        OW.info(OW.getLanguageText('frmquestionroles', 'wait'));
        $.ajax({
            url: url,
            success: function(response) {
                console.log(response);
                window.location = window.location;
            },
        });
    };
}
