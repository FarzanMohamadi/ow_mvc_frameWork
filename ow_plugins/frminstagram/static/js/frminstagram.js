/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */

var ig_username;
var ig_widget_preloader,ig_widget_items,ig_widget_empty,ig_widget_not_set,ig_widget_cant_load,ig_widget_items_wrapper;
var ig_data;
var ig_reachedToFirst = false, ig_isOlderProcessing = false, ig_maxId = false;
var id,first,after,profile_pic;
function imageExists(url, callback) {
    var img = new Image();
    img.onload = function() { callback(true); };
    img.onerror = function() { callback(false); };
    img.src = url;
}
function ig_loadWidgetData(_username,_loadDataUrl,_loadMoreUrl){
    this.ig_username = _username;

    ig_widget_preloader = "#igw_preloader";
    $(ig_widget_preloader).hide();
    ig_widget_items = "#igw_items";
    $(ig_widget_items).hide();
    ig_widget_empty = "#igw_empty";
    $(ig_widget_empty).hide();
    ig_widget_not_set = "#igw_not_set";
    $(ig_widget_not_set).hide();
    ig_widget_cant_load = "#igw_cant_load";
    $(ig_widget_cant_load).hide();

    if(this.ig_username=="")
    {
        $(ig_widget_not_set).show();
        return;
    }

    //initial loading
    $(ig_widget_preloader).show();
    ig_isOlderProcessing = true;
    ig_reachedToFirst = false;

    $.ajax({
        type: "GET",
        url: _loadDataUrl+this.ig_username,
        success: function(response) {
            $(ig_widget_preloader).hide();
            try {
                ig_data = jQuery.parseJSON(response);
                id = ig_data.user.id;
                after = ig_data.after;
                first = ig_data.first;
                profile_pic = ig_data.user.profile_picture;
                if (ig_data.status == "error") {
                    ig_isOlderProcessing = false;
                    ig_reachedToFirst = true;
                    $(ig_widget_empty).show();
                    //OW.error(ig_data.error_msg);
                    return;
                }
                if (ig_data.items.length == 0 || ig_data.status == "empty") {
                    $(ig_widget_empty).show();
                    ig_reachedToFirst = true;
                    return;
                }

                $(ig_widget_items).show();
                imageExists(profile_pic, function(exists) {
                    if(!exists) {
                        console.log('RESULT: url=' + profile_pic + ', exists=' + exists);
                        $(ig_widget_items).hide();
                        $(ig_widget_cant_load).show();
                    }
                });
                var user_html_code =
                    "<div id='igw_header'>" +
                    "<a href='" + ig_data.user.profile_url + "' target='_blank'>" +
                    "<img id='igw_usericon' src='" + ig_data.user.profile_picture + "'>" +
                    "<div id='igw_username'>" + ig_data.user.username + "</div>" +
                    "</a>" +
                    "</div>";
                $(ig_widget_items).append(user_html_code);

                var html_code = "<div id='igw_data_wrapper'></div>";
                if ($(ig_widget_items).width() > 320)
                    html_code = "<div id='igw_data_wrapper' class='wide'></div>";
                $(ig_widget_items).append(html_code);

                ig_process_json(ig_data);
            }catch (err){
                ig_isOlderProcessing = false;
                $(ig_widget_preloader).hide();
                $(ig_widget_empty).show();
                //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
            }
        },
        'error' : function() {
            ig_isOlderProcessing = false;
            $(ig_widget_preloader).hide();
            $(ig_widget_empty).show();
            //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
        }
    });

    // setInterval(function() {
    //     if(ig_reachedToFirst || ig_isOlderProcessing )
    //         return;
    //     checkForInstagramOlderData(_loadMoreUrl);
    // }, 2000);
}

function checkForInstagramOlderData(url){
    if(ig_reachedToFirst || ig_isOlderProcessing || ig_widget_items_wrapper=="")
        return;
    var sc_full_h = $(ig_widget_items_wrapper).prop("scrollHeight");
    var sc_bottom_h = $(ig_widget_items_wrapper).scrollTop() + $(ig_widget_items_wrapper).height();
    if(sc_full_h - sc_bottom_h < 20){
        url = url+this.ig_username;
        // if(ig_maxId!=false)
        //     url = url+'/'+ig_maxId;
        $(ig_widget_preloader).show();
        ig_isOlderProcessing = true;
        $.ajax({
            method: "POST",
            url: url,
            data: {
                'first':first,
                'after':after,
                'id': id,
                'profile_pic': profile_pic
            },
            success: function(response) {
                $(ig_widget_preloader).hide();
                try{
                    ig_data = jQuery.parseJSON(response);
                    ig_process_json(ig_data);
                }catch (err) {
                    ig_isOlderProcessing = false;
                    $(ig_widget_preloader).hide();
                    //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
                }
            },
            'error' : function(e) {
                ig_isOlderProcessing = false;
                $(ig_widget_preloader).hide();
                //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
            }
        });
    }
}
function ig_process_json(ig_data){
    ig_isOlderProcessing = false;
    id = ig_data.user.id;
    after = ig_data.after;
    first = ig_data.first;
    profile_pic = ig_data.user.profile_picture;
    if(ig_data.status =="error")
    {
        ig_reachedToFirst = true;
        $(ig_widget_empty).show();
        OW.error(ig_data.error_msg);
        return;
    }
    if(ig_data.items.length==0 || ig_data.status =="empty"){
        ig_reachedToFirst = true;
        return;
    }
    if(!ig_data.more_available)
        ig_reachedToFirst = true;
    ig_widget_items_wrapper = '#igw_data_wrapper';

    var size = "m";
    if($(ig_widget_items).width()>900) {
        size = "l";
    }

    $.each(ig_data.items, function( index, value ) {
        ig_maxId = value.id;
        //html
        var html_code=
            "    <div class='igw_item'>" +
            "        <a href='"+value.link+"' target='_blank'>" +
            "          <div class='igw_item_header'>" +
            "          </div>"+
            "          <div class='igw_"+value.type+"'>" +
            "             <div></div>"+
            "          </div>"+
            "          <div class='igw_item_content'>" +
            "             <img src='"+value.image+"'/>" +
            "          </div>" +
            "          <div class='igw_item_toolbar'>" +
            "             <span class='igw_date'>"+value.created_time+"</span>" +
            "             <span class='igw_likes'>❤️"+value.likes+"</span>" +
            "             <span class='igw_comments'>"+value.comments+"</span>" +
            "          </div>"+
            "        </a>" +
            "    </div>";
        $(ig_widget_items_wrapper).append(html_code);
    });
}