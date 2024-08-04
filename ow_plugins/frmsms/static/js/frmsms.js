let unverifiedNumber = null;

function sign_out(url){
    window.location = url;
}

function removeUnverifiedNumber(url) {
    $.ajax({
        url: url,
        type: 'post',
        dataType: "json",
        success: function (results) {
            if(results['reload']){
                location.reload();
            }else{
                var message = results['message'];
                var type = 'info';
                if(results['type'])
                {
                    type=results['type'];
                }
                if(typeof OWM === "undefined"){
                    OW.message(message, type);
                }else{
                    OWM.message(message, type);
                }
            }
        },
        error: function( jqXHR, textStatus, errorThrown )
        {
            hidePreloader(type);
        }
    });
}

function resendToken(type, url, mobile) {
    var data = {"type": type};
    unverifiedNumber = $('input[name ="field_mobile"]').val();
    if(unverifiedNumber!=null && mobile!=unverifiedNumber)
    {
        data['unverifiedNumber'] = unverifiedNumber;
    }
    if($('#oldPassword').length==1)
    {
        data['reload'] = false;
    }
    showPreloader(type);
    $.ajax({
        url: url,
        type: 'post',
        dataType: "json",
        data: data,
        success: function (results) {
            hidePreloader(type);
            if(results['reload']){
                window.location = window.location;
            }else{
                var message = results['message'];
                var type = 'info';
                if(results['type'])
                {
                    type=results['type'];
                }
                if(typeof OWM === "undefined"){
                    OW.message(message, type);
                }else{
                    OWM.message(message, type);
                }
            }
        },
        error: function( jqXHR, textStatus, errorThrown )
        {
            hidePreloader(type);
        }
    });
}

function showPreloader(type) {
    $('.user_verification .'+type+'.ow_preloader').css('display', 'inline-block');
    $('.user_verification .'+type+'.owm_preloader').css('display', 'inline-block');
}

function hidePreloader(type) {
    $('.user_verification .'+type+'.ow_preloader').css('display', 'none');
    $('.user_verification .'+type+'.owm_preloader').css('display', 'none');
}
