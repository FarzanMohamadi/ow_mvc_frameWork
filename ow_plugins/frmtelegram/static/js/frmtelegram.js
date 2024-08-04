/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 * @since 1.0
 */
var updateFirstId = [];
var updateLastId = [];
var reachedToFirst = [];
var isOlderProcessing = [];
var isLoadMoreProcessing = [];
var isSelected = [];
var lastScrollH = [];

function loadDynamicData(loadMoreUrl, loadOlderUrl, firstId, lastId, selectorPostfix){
    updateFirstId[selectorPostfix] = firstId;
    updateLastId[selectorPostfix] = lastId;
    reachedToFirst[selectorPostfix] = false;
    isOlderProcessing[selectorPostfix] = false;
    isLoadMoreProcessing[selectorPostfix] = false;

    var items_wrapper_selector = "#items_wrapper" + selectorPostfix;
    var items_wrapper_new_items_icon_selector = "#items_wrapper_new_items_icon" + selectorPostfix;

    $(items_wrapper_new_items_icon_selector).hide();
    if ( $(items_wrapper_selector).height() == 0 ){
        isSelected[selectorPostfix] = false;
        lastScrollH[selectorPostfix] = -1;
    }else {
        isSelected[selectorPostfix] = true;
        lastScrollH[selectorPostfix] = 0;
        $(items_wrapper_selector).animate({scrollTop:   lastScrollH[selectorPostfix]}, 1000);//$(items_wrapper_selector).prop("scrollHeight") -
    }
    setInterval(function() {
        checkLoadMoreData(loadMoreUrl,selectorPostfix)
    }, 10000);
    setInterval(function() {
        checkForOlderData(loadOlderUrl,selectorPostfix);
        check_if_seen_latest(selectorPostfix);
        check_if_selected_tab(selectorPostfix);
    }, 1000);
}
function check_if_selected_tab(selectorPostfix){
    var items_wrapper_selector = "#items_wrapper"+selectorPostfix;
    if ( $(items_wrapper_selector).height() == 0 ){
        isSelected[selectorPostfix] = false;
    }else {
        if(!isSelected[selectorPostfix]){
            if(lastScrollH[selectorPostfix]==-1)
                lastScrollH[selectorPostfix] = 0;
            $(items_wrapper_selector).animate({scrollTop: lastScrollH[selectorPostfix]}, 0); //$(items_wrapper_selector).prop("scrollHeight") -
            isSelected[selectorPostfix] = true;
        }else{
            lastScrollH[selectorPostfix] = $(items_wrapper_selector).scrollTop(); //$(items_wrapper_selector).prop("scrollHeight") -
        }
    }
}
function checkLoadMoreData(url,selectorPostfix){
    if (!isSelected[selectorPostfix])
        return;
    var items_wrapper_selector = "#items_wrapper"+selectorPostfix;
    var items_wrapper_new_items_icon_selector = "#items_wrapper_new_items_icon" + selectorPostfix;
    if(isLoadMoreProcessing[selectorPostfix])
        return;
    isLoadMoreProcessing[selectorPostfix] = true;
    $.ajax({
        url: url+ updateLastId[selectorPostfix],
        success: function(response) {
            isLoadMoreProcessing[selectorPostfix] = false;
            var data = jQuery.parseJSON(response);
            updateLastId[selectorPostfix] = data.lastId;
            $.each(data.results, function( index, value ) {
                $(items_wrapper_new_items_icon_selector).show();
                $(items_wrapper_selector).prepend(value);
            });
        },
        'error' : function() {
            isLoadMoreProcessing[selectorPostfix] = false;
            //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
        }
    });
}
function checkForOlderData(url,selectorPostfix){
    if (!isSelected[selectorPostfix])
        return;
    var items_wrapper_selector = "#items_wrapper"+selectorPostfix;
    var items_wrapper_preloader_selector = "#items_wrapper_preloader" + selectorPostfix;

    if(reachedToFirst[selectorPostfix] || isOlderProcessing[selectorPostfix] )
        return;

    var sc_top = $(items_wrapper_selector).scrollTop();
    var div_hei = $(items_wrapper_selector).height();
    var max_scroll = $(items_wrapper_selector).prop("scrollHeight");
    if(sc_top+ div_hei + 20 >= max_scroll){
        //if(sc_top<20){
        $(items_wrapper_preloader_selector).show();
        isOlderProcessing[selectorPostfix] = true;
        $.ajax({
            url: url+ updateFirstId[selectorPostfix],
            success: function(response) {
                isOlderProcessing[selectorPostfix] = false;
                $(items_wrapper_preloader_selector).hide();
                var data = jQuery.parseJSON(response);
                if(updateFirstId[selectorPostfix] == data.firstId) {
                    reachedToFirst[selectorPostfix] = true;
                    return;
                }
                updateFirstId[selectorPostfix] = data.firstId;

                //var fromBottom1 = $(items_wrapper_selector).prop("scrollHeight") - $(items_wrapper_selector).scrollTop();
                $.each(data.results, function( index, value ) {
                    $(items_wrapper_selector).append(value);
                });

                /*
                 var newTopScroll = $(items_wrapper_selector).prop("scrollHeight") - fromBottom1 - 15;
                 if(newTopScroll<0){
                 newTopScroll = 0;
                 }
                 $(items_wrapper_selector).animate({ scrollTop: newTopScroll}, 0);
                 */
            },
            'error' : function() {
                //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
                isOlderProcessing[selectorPostfix] = false;
                $(items_wrapper_preloader_selector).hide();
            }
        });
    }
}
function check_if_seen_latest(selectorPostfix){
    if (!isSelected[selectorPostfix])
        return;
    var items_wrapper_selector = "#items_wrapper"+selectorPostfix;
    var items_wrapper_new_items_icon_selector = "#items_wrapper_new_items_icon"+selectorPostfix;

    var sc_top = $(items_wrapper_selector).scrollTop();
    //var div_hei = $(items_wrapper_selector).height();
    //var max_scroll = $(items_wrapper_selector).prop("scrollHeight");
    //if(sc_top+ div_hei + 20 >= max_scroll){
    if(sc_top<20){
        $(items_wrapper_new_items_icon_selector).hide();
    }
}

function item_added(data){
    if(data.result){
        $('form[name="sendTelegram"] *[name="text"]').val("")
    }
    else{
        OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
    }
}
function delete_item(url,id){
    $.ajax({
        url: url+id,
        success: function(response) {
            $("#telegram_item_"+id).hide();
        },
        'error' : function() {
            OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
        }
    });
}
function go_to_last_item(selectorPostfix){
    if (!isSelected[selectorPostfix])
        return;
    var items_wrapper_selector = "#items_wrapper"+selectorPostfix;
    var items_wrapper_new_items_icon_selector = "#items_wrapper_new_items_icon"+selectorPostfix;

    $(items_wrapper_new_items_icon_selector).hide();
    $(items_wrapper_selector).animate({ scrollTop: 0}, 1000); //$(items_wrapper_selector).prop("scrollHeight")
}