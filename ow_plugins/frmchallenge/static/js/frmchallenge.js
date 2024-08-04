var challengeOpponentId;
function create_challenge(){
    challengeOpponentId = null;

    var formFrame = $("#create_challenge");
    formFrame.children("div").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        labels: {
            next: OW.getLanguageText('frmchallenge', 'next_label'),
            previous: OW.getLanguageText('frmchallenge', 'previous_label'),
            finish: OW.getLanguageText('frmchallenge', 'finish_label'),
        },
        onStepChanging: function (event, currentIndex, newIndex)
        {
            return manageStepInfo();
        },
        onFinished: function (event, currentIndex)
        {
            formFrame.submit();
        }
    });

    $("#all_challenge").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        enableFinishButton: false,
        enablePagination: false,
        enableAllSteps: true,
        titleTemplate: "#title#",
        cssClass: "tabcontrol"
    });

    configPopups();

    showAllElement();
}

function showAllElement() {
    $('#all_challenge').css('display', 'block');
    $('.challenge_create_button').css('display', 'block');
    $('.challenge.ow_preloader').css('display', 'none');
}

function manageStepInfo() {
    var value = $("input:radio[name ='challenge_type']:checked").val();
    if(value == 1){
        $('.solitary_information').fadeIn("fast");
        $('.groups_information').fadeOut("fast");
        $('.universal_information').fadeOut("fast");
    }else if(value == 2){
        $('.groups_information').fadeIn("fast");
        $('.solitary_information').fadeOut("fast");
        $('.universal_information').fadeOut("fast");
    }else if(value == 3){
        $('.universal_information').fadeIn("fast");
        $('.groups_information').fadeOut("fast");
        $('.solitary_information').fadeOut("fast");
    }else{
        return false;
    }

    return true;
}

function configPopups(){
    $(function () {
        $('[data-popup-open]').on('click', function (e) {
            var targeted_popup_class = jQuery(this).attr('data-popup-open');
            $('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);
            e.preventDefault();
        });
        $('[data-popup-close]').on('click', function (e) {
            var targeted_popup_class = jQuery(this).attr('data-popup-close');
            $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
            e.preventDefault();
        });
    });
}

function calculateFinishAnsweringTime($seconds, url) {
    var counter = $seconds;
    $('#challenge_question_counter').html(counter);
    var interval = setInterval(function() {
        counter--;
        $('#challenge_question_counter').html(counter);
        if (counter == 0) {
            clearInterval(interval);
            $.ajax({
                url: url,
                type: 'POST',
                data: {},
                dataType: 'json',
                success: function (data) {
                    window.location = data.location;
                }
            });
        }
    }, 1000);
}