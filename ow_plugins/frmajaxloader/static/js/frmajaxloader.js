/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmajaxloader
 * @since 1.0
 */

var ajax_updateLastTS = [];
var ajax_isLoadMoreProcessing = [];
var ajax_itemsWrapperSelector = [];
var ajax_newItemsIconSelector = [];
var ajax_newItemsIconText = [];

var ajax_latestData = [];
var ajax_latestCount = [];

function ajax_loadNewly(loadMoreUrl, lastId, _items_wrapper_selector, selectorPostfix, btn_label, user_id){
    ajax_updateLastTS[selectorPostfix] = lastId;
    ajax_isLoadMoreProcessing[selectorPostfix] = false;
    ajax_itemsWrapperSelector[selectorPostfix] = _items_wrapper_selector;
    ajax_newItemsIconSelector[selectorPostfix] = "#ajax_newly_button_"+selectorPostfix;
    if($(ajax_itemsWrapperSelector[selectorPostfix]).length==0)
        return;

    //add and set newly btn
    var btn_html =
        '<div id="ajax_newly_button_'+selectorPostfix+'" class="ow_ajax_newly_button">'
        +'<span class="ow_button ajax_ok_part"><span class="ow_newsfeed_view_more"><input type="button" value="'+btn_label+'" class="ow_newsfeed_view_more ow_ic_add"></span></span>'
        +'<div class="ow_button ow_ic_close"></div>'
        +'</div>';
    if (typeof OWM != 'undefined'){
        btn_html = '<div id="ajax_newly_button_'+selectorPostfix+'" class="owm_ajax_newly_button">'
            +'<input type="button" class="ajax_ok_part" value="'+btn_label+'">'
            +'<div class="owm_close_btn"></div>'
            +'</div>';
    }
    $('body').append(btn_html);
    ajax_newItemsIconText[selectorPostfix] = $(ajax_newItemsIconSelector[selectorPostfix]+' input').prop('value');

    $(ajax_newItemsIconSelector[selectorPostfix]).hide();
    $(ajax_newItemsIconSelector[selectorPostfix]+' .ajax_ok_part').click(function(){
        ajax_goToLastItem(selectorPostfix);
        //if(selectorPostfix == 'myfeed' || selectorPostfix == 'sitefeed')
        {
            $(ajax_itemsWrapperSelector[selectorPostfix]+' .newsfeed_nocontent').remove();
            $(ajax_itemsWrapperSelector[selectorPostfix]+' .owm_newsfeed_nocontent').remove();

            $.each(ajax_latestData[selectorPostfix], function( index, data ) {
                $.each(data.idList, function( index2, item_id ) {
                    $(ajax_itemsWrapperSelector[selectorPostfix] + ' #action-feed1-' + item_id).remove();
                });
                if (typeof OWM != 'undefined'){
                    if($(ajax_itemsWrapperSelector[selectorPostfix]+' .owm_content_menu_wrap').length===0){ //check if frmnewsfeedpin is disabled
                        $(ajax_itemsWrapperSelector[selectorPostfix]).prepend(data.content);
                    }else{
                        data.content = data.content.replace('owm_content_menu_wrap','ow_content_menu_wrap2');
                        $(ajax_itemsWrapperSelector[selectorPostfix]+' .owm_content_menu_wrap').after(data.content);
                        $(ajax_itemsWrapperSelector[selectorPostfix]+' .ow_content_menu_wrap2').remove();
                    }
                }else{
                    if($(ajax_itemsWrapperSelector[selectorPostfix]+' .ow_content_menu_wrap').length===0){ //check if frmnewsfeedpin is disabled
                        $(ajax_itemsWrapperSelector[selectorPostfix]).prepend(data.content);
                    }else{
                        data.content = data.content.replace('ow_content_menu_wrap','ow_content_menu_wrap2');
                        $(ajax_itemsWrapperSelector[selectorPostfix]+' .ow_content_menu_wrap').after(data.content);
                        $(ajax_itemsWrapperSelector[selectorPostfix]+' .ow_content_menu_wrap2').remove();
                    }
                }

                $.each(data.idList, function( index2, item_id ) {
                    $(ajax_itemsWrapperSelector[selectorPostfix] + ' #action-feed1-' + item_id).css('display', 'none').fadeIn(1500);
                    $(ajax_itemsWrapperSelector[selectorPostfix] + ' #action-feed1-' + item_id).addClass("ow_ajax_newly_feed").delay(4000).queue(function(next){
                        $(this).removeClass("ow_ajax_newly_feed");
                        next();
                    });
                });
                $(ajax_itemsWrapperSelector[selectorPostfix]+' >li .ow_newsfeed_delimiter').css('display','block');
                ajax_processMarkup(data);
            });

            ajax_latestData[selectorPostfix] = [];
            ajax_latestCount[selectorPostfix] = 0;
        }
    });
    $(ajax_newItemsIconSelector[selectorPostfix]+' div').click(function(){
        $(ajax_newItemsIconSelector[selectorPostfix]).hide();
    });

    setInterval(function() {
        ajax_checkLoadMoreData(loadMoreUrl, selectorPostfix, user_id)
    }, 30000);
}

function ajax_checkLoadMoreData(url, selectorPostfix, userId){
    if( ajax_isLoadMoreProcessing[selectorPostfix])
        return;
    ajax_isLoadMoreProcessing[selectorPostfix] = true;
    if(typeof socket != "undefined" && socket.readyState === socket.OPEN){
        socket.send(JSON.stringify({'type':'feedLoader', 'auth': socket_auth, 'selectorPostfix':selectorPostfix,
            'lastTS':ajax_updateLastTS[selectorPostfix], 'userId':userId}));
        ajax_isLoadMoreProcessing[selectorPostfix] = false;
    }else{
        dataFetching(url, selectorPostfix);
    }
}

function dataFetching(url, selectorPostfix) {
    $.ajax({
        url: url+ ajax_updateLastTS[selectorPostfix],
        success: function(response) {
            ajax_isLoadMoreProcessing[selectorPostfix] = false;
            var data = jQuery.parseJSON(response);
            if(typeof data.count != "undefined" && data.count > 0) {
                ajax_updateLastTS[selectorPostfix] = data.lastTS;
                if(typeof ajax_latestCount[selectorPostfix] == "undefined") {
                    ajax_latestCount[selectorPostfix] = 0;
                    ajax_latestData[selectorPostfix] = [];
                }

                //new data exists
                ajax_latestCount[selectorPostfix] += parseInt(data.count);
                ajax_latestData[selectorPostfix].push(data);

                $(ajax_newItemsIconSelector[selectorPostfix]+' input').prop('value',
                    ajax_latestCount[selectorPostfix] + " " + ajax_newItemsIconText[selectorPostfix]);
                $(ajax_newItemsIconSelector[selectorPostfix]).show();
            }
        },
        'error' : function() {
            ajax_isLoadMoreProcessing[selectorPostfix] = false;
        }
    });
}

function ajax_goToLastItem(selectorPostfix){
    var sc_top = $(ajax_itemsWrapperSelector[selectorPostfix]).offset().top - 80;
    if(sc_top<0)
        sc_top = 0;
    $(ajax_newItemsIconSelector[selectorPostfix]).fadeOut(500);
    $('body,html').animate({ scrollTop: sc_top}, 300);
}

function ajax_processMarkup( markup )
{
    if (markup.styleSheets)
    {
        $.each(markup.styleSheets, function(i, o)
        {
            OW.addCssFile(o);
        });
    }

    if (markup.styleDeclarations)
    {
        OW.addCss(markup.styleDeclarations);
    }

    if (markup.beforeIncludes)
    {
        OW.addScript(markup.beforeIncludes);
    }

    if (markup.scriptFiles)
    {

        OW.addScriptFiles(markup.scriptFiles, function()
        {
            if (markup.onloadScript)
            {
                OW.addScript(markup.onloadScript);
            }
        });
    }
    else
    {
        if (markup.onloadScript)
        {
            OW.addScript(markup.onloadScript);
        }
    }
}

OW.bind("base.socket_prepare_message", function (data) {
    if (data.type == "feedLoader") {
        dataFetching(data.url,  data.selectorPostfix)
    }
});
