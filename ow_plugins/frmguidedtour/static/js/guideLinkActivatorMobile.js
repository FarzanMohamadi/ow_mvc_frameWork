/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */

function frmgt_setUnseenAndReload() {
    frmgt_cookie_setUnseen();
    $.ajax({
            url: frmgt_ajax_unseen_url,
            type: 'post',
            success: function (data) {
                data = JSON.parse(data);
                if (data.result == 'true') {
                    //window.location.href = frmgt_home_url;
                }
            },
            error: function () {
                console.log("Data didn't get sent!!");
            }
        });
}

function frmgt_createCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

function frmgt_readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function frmgt_eraseCookie(name) {
    frmgt_createCookie(name,"",-1);
}

function frmgt_cookie_setSeen() {
    frmgt_createCookie("frmgt_seen", true);
}

function frmgt_cookie_setUnseen() {
    frmgt_eraseCookie("frmgt_seen");
}

function frmgt_cookie_isSeen() {
    var seen = frmgt_readCookie("frmgt_seen");
    if(seen)
        return true;
    else
        return false;
}

