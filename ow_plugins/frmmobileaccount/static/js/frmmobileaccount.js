function resendMobileCodeAccount(url) {
    $('#account_status').html(OW.getLanguageText('frmmobileaccount', 'resending_token'));
    $('.account_status_box').css('display', 'inline-block');
    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        success: function(data)
        {
            $('.account_status_box').css('display', 'none');
            if( data !=undefined) {
                if(data.failRedirect)
                {
                    window.location = data.failRedirect;
                }
                if (data.valid != undefined && data.valid == true) {
                    if(data.message != undefined)
                    {
                        OW.info(data.message);
                    }
                    OW.info(OW.getLanguageText('frmmobileaccount', 'resend_token_successfully'));
                } else if (data.valid != undefined && data.valid == false && data.message) {
                    OW.error(data.message);
                } else if (data.valid != undefined && data.valid == false) {
                    OW.warning(OW.getLanguageText('frmmobileaccount', 'try_again'));
                }
            }
        },
        error: function(data){
            $('.account_status_box').css('display', 'none');
                OW.error(OW.getLanguageText('frmmobileaccount', 'try_again'));
        },
    });
}

//disable this part to change the UI design of login page with phone number
// $(document).ready(function(){
//     $("#login_frame").steps({
//         headerTag: "h3",
//         bodyTag: "section",
//         transitionEffect: "slideLeft",
//         enableFinishButton: false,
//         enablePagination: false,
//         enableAllSteps: true,
//         titleTemplate: "#title#",
//         cssClass: "tabcontrol"
//     });
// });

function showLoginWithUsernameTab(){
    $('#login_frame li a')[1].click();
}

$("#loginBySMSTab").click( function () {
        if($("#loginBySMSTab").closest("li").hasClass("active")){
            return false;
        }
        $("#loginBySMSTab").closest("li").addClass("active");
        $("#loginBySmsDIV").css("display","block");
        $("#loginByPasswordTab").closest("li").removeClass("active");
        $("#loginByPasswordDIV").css("display","none");
    }
);

$("#loginByPasswordTab").click( function () {
        if($("#loginByPasswordTab").closest("li").hasClass("active")){
            return false;
        }
        $("#loginBySmsDIV").css("display","none");
        $("#loginBySMSTab").closest("li").removeClass("active");
        $("#loginByPasswordDIV").css("display","block");
        $("#loginByPasswordTab").closest("li").addClass("active");
    }
);

var inputNames = ["username", "password", "mobile_number", "mobile_code"];

$(document).ready(function () {
    setTimeout(function () {
        inputNames.forEach(updateLabels);
    },500);
});

$( ".account_field input" ).keyup(function() {
    inputNames.forEach(updateLabels);
});

function updateLabels(item, index) {

    if($("input[name='"+item+"']").val() != ""){
        $("input[name='"+item+"']").next().addClass("filled");
    } else {
        $("input[name='"+item+"']").next().removeClass("filled");
    }
}
