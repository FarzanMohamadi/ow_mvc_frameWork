$(function () {
    var lastText = '';
    var fullListLoadingStatus = 0;
    var input_selector = '#frmmainpage_friends_search';

    var filterItems = function(q){
        if (q == ''){
            //clear search
            $('.owm_user_list .owm_list_item_with_image').each(function(){
                $(this).css('display', 'block');
            });
        }
        else{
            //find items
            $('.owm_user_list .owm_list_item_with_image').each(function(){
                if( $('.owm_user_list_name > a',this).text().toLowerCase().includes(q.toLowerCase()) ){
                    $(this).css('display', 'block');
                }
                else if( $('.owm_user_list_name > a[href]',this).attr('href').toLowerCase().includes(q.toLowerCase()) ){
                    $(this).css('display', 'block');
                }
                else {
                    $(this).css('display', 'none');
                }
            });
        }

        var listCount = $('.owm_content_list_item.owm_list_item_with_image:visible').length;
        if(listCount>0)
            $('.ow_nocontent').hide();
        else
            $('.ow_nocontent').css('display', 'block');
    };

    var fullListLoaded = function(){
        fullListLoadingStatus = 2;
        var number = 0;
        $('.owm_user_list .owm_list_item_with_image').each(function(){
            $(this).attr('itemnum', number++);
        });
        //remove loading
        $('.owm_user_list_search_preloader.owm_preloader').hide();

        //refresh list
        q = $(input_selector)[0].value;
        filterItems(q);
        $('#friends_useritems_style')[0].innerHTML = '';
    };

    $(input_selector).on('change keydown paste input',function () {
        q = $(this)[0].value;
        if (lastText == q){
            return;
        }
        lastText = q;

        if (q == '')
        {
            filterItems(q);
        }
        else
        {
            if( fullListLoadingStatus == 2 ){
                filterItems(q);
            }
            else if( fullListLoadingStatus == 0 ){
                if(window.mobileUserList.process){
                    fullListLoaded();
                }
                else{
                    //show loading
                    var style = $('<style id="friends_useritems_style">.owm_user_list .owm_list_item_with_image { display: none }</style>');
                    $('html > head').append(style);
                    $('.owm_user_list_search_preloader.owm_preloader')[0].style = 'display: none; background-size: 40px; min-height: 100px; height: 100%;';
                    $('.owm_user_list_preloader.owm_preloader').hide();

                    window.mobileUserList.count = 1000;
                    window.mobileUserList.loadData();
                    fullListLoadingStatus = 1;

                    var timer = setInterval(function() {
                        if(window.mobileUserList.process)
                            return;
                        clearInterval(timer);
                        fullListLoaded();
                    }, 1000);
                }
            }
        }
    });
});

var last_mailbox_search_q = '';
function add_mailbox_search_events(ajax_url) {
    $('#frmmainpage_messages_search').on('change keydown paste input', function () {
        var q = $(this)[0].value;
        var listSelector = '#mailbox_page > .owm_list_page';
        if(q === last_mailbox_search_q){
            return;
        }
        if(last_mailbox_search_q ===''){
            $(listSelector + ' .owm_list_item_with_image').hide();
            $(listSelector + ' .ow_nocontent').hide();
            $(listSelector + ' .owm_preloader').show();
        }
        last_mailbox_search_q = q;
        if (q === '') {
            $(listSelector + ' .owm_list_item_with_image').show();
            $(listSelector + ' .owm_list_item_search_item').remove();
            $(listSelector + ' > .ow_nocontent').hide();
            $(listSelector + ' > .owm_preloader').hide();
        } else {
            $(listSelector + ' > .owm_preloader').show();
            setTimeout(function () {
                if (q !== last_mailbox_search_q)
                    return;
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        'q': q
                    },
                    url: ajax_url,
                    success:
                        function (response) {
                            var data = response;//jQuery.parseJSON(response);
                            if (data.result === 'ok') {
                                if (data.q !== last_mailbox_search_q)
                                    return;
                                $(listSelector + ' .owm_list_item').hide();
                                $(listSelector + ' .owm_list_item_search_item').remove();
                                $(listSelector + ' > .owm_preloader').hide();
                                $(listSelector + ' > .ow_nocontent').hide();
                                if (data.results.length === 0) {
                                    $(listSelector + ' > .ow_nocontent').css('display', 'block');
                                }
                                $.each(data.results, function (index, value) {
                                    if(value.imageInfo != null){
                                        var colorAvatar = 'colorful_avatar_'+value.imageInfo.digit
                                    }
                                    var html = '<div class="owm_list_item_with_image owm_list_item_search_item" onclick="location.href=\'' + value.conversationUrl + '\';">' +
                                        '<div class="owm_user_list_item"><div class="owm_avatar '+colorAvatar+'"><img src="' + value.avatarUrl + '"></div>' +
                                        '<div class="owm_user_list_name">' +
                                        '   <span id="mailboxSidebarConversationsItemDisplayName"><a href="' + value.conversationUrl + '">' + value.opponentName + '</a></span>';
                                    if (value.unreadCount) {
                                        html += '   <span class="ow_unread_count" title="Unread Count">' + value.unreadCount + '</span>';
                                    }
                                    html += '</div>' +
                                        '<div class="owm_sidebar_convers_mail_theme" id="mailboxSidebarConversationsItemSubject" onselectstart="return false" cb="set" style="user-select: none;"><a emoji="5">' + value.text + '</a></div>' +
                                        '<div class="owm_profile_online" id="mailboxSidebarConversationsItemOnlineStatus" style="display: none;"></div>' +
                                        '<div class="owm_newsfeed_date">' + value.timeString + '</div></div></div>';
                                    $(listSelector).append(html);
                                });
                            }
                        }
                })
            }, 1000);
        }
    });
}


var last_chat_group_search_q = '';
function add_chat_group_search_events(ajax_url) {
    $('#frmmainpage_chat_group_search').on('keyup', function () {
        var q = $('#frmmainpage_chat_group_search')[0].value;
        var listSelector = '#chatgroups_page';
        // $(listSelector + ' .owm_list_page .owm_list_item:not(:has(> div))').remove();

        $(listSelector + ' > #atLeastTwoChar').hide();
        if(q.length == 1){
            $(listSelector + ' > .owm_list_page .owm_list_item').remove();
            $(listSelector + ' > .ow_nocontent').hide();
            $(listSelector + ' > .owm_list_page .owm_preloader').hide();
            $(listSelector + ' > #atLeastTwoChar').show();
            return;
        }else if(q.length == 0){
            $(listSelector + ' > .owm_list_page .owm_list_item').remove();
            $(listSelector + ' > .ow_nocontent').hide();
            $(listSelector + ' > .owm_list_page .owm_preloader').show();
            $(listSelector + ' > #atLeastTwoChar').hide();

            if(window.mobileUserList !== undefined) {
                window.mobileUserList.resetExcludeList();
            }

            $.ajax({
                type: "POST",
                dataType: "json",
                data: {
                    'q': q
                },
                url: ajax_url,
                success:
                    function (response) {
                        var lastSearch_q = response['last_q'];
                        var qNew = $('#frmmainpage_chat_group_search')[0].value;

                        var data = response['tplList'];//jQuery.parseJSON(response);

                        if(window.mobileUserList !== undefined) {
                            window.mobileUserList.addDataToExcludeList(response);
                        }

                        $(listSelector + ' > .owm_list_page .owm_list_item').remove();
                        $(listSelector + ' > .owm_list_page  .owm_preloader').hide();
                        $(listSelector + ' > .ow_nocontent').hide();
                        if (data.length === 0) {
                            $(listSelector + ' > .ow_nocontent').css('display', 'block');
                        }
                        $.each(data, function (index, value) {
                            if(value.imageInfo != null){
                                var colorAvatar = 'colorful_avatar_'+value.imageInfo.digit
                            }
                            if (value.type != undefined && value.type == 'group') {
                                var partentTitle = '';
                                if(value.parentTitle !=null )
                                {
                                    partentTitle=value.parentTitle;
                                }

                                var html = '<div class="owm_list_item"><div class="ow_ipc owm_list_item_with_image owm_list_item_search_item"> <div class="ow_ipc_picture '+colorAvatar+'">' +
                                    '<img src="' + value.imageSrc + '" alt="' + value.imageTitle + '" title="' + value.imageTitle + '"> </div>' +
                                    '<div class="ow_ipc_info"> <div class="ow_ipc_header"><a href="' + value.url + '">' +
                                    value.title + '</a>'+partentTitle+'</div>	<div class="ow_ipc_content" onselectstart="return false" cb="set" style="user-select: none;">' +
                                    value.content + '</div> <div class="clearfix"> <div class="ow_ipc_toolbar ow_remark"> <span class="ow_nowrap">' +
                                    value.toolbar[0]['label'] + ' </span> </div> </div> </div> </div></div>';
                            } else {
                                var html = '<div class="owm_list_item"><div class="owm_list_item_with_image owm_list_item_search_item" onclick="location.href=\'' + value.conversationUrl + '\';">' +
                                    '<div class="owm_user_list_item"><div class="owm_avatar '+colorAvatar+'"><img src="' + value.avatarUrl + '"></div>' +
                                    '<div class="owm_user_list_name">' +
                                    '   <span id="mailboxSidebarConversationsItemDisplayName"><a href="' + value.url + '">' + value.displayName + '</a></span>';
                                if (value.unreadCount) {
                                    html += '   <span class="ow_unread_count" title="Unread Count">' + value.unreadCount + '</span>';
                                }
                                html += '</div>' +
                                    '<div class="owm_sidebar_convers_mail_theme" id="mailboxSidebarConversationsItemSubject" onselectstart="return false" cb="set" style="user-select: none;"><a emoji="5">' + value.text + '</a></div>' +
                                    '<div class="owm_profile_online" id="mailboxSidebarConversationsItemOnlineStatus" style="display: none;"></div>' +
                                    '<div class="owm_newsfeed_date">' + value.timeLabel + '</div></div></div></div>';
                            }
                            $(listSelector+ ' > .owm_list_page .owm_list_item_parent').append(html);
                        });
                    }
            });

            last_chat_group_search_q = q;
            return;
        }
        if(q === last_chat_group_search_q){
            return;
        }

        $(listSelector + ' > .owm_list_page .owm_list_item').remove();
        if(last_chat_group_search_q ===''){
            $(listSelector + ' > .ow_nocontent').hide();
            $(listSelector + ' >  .owm_list_page .owm_preloader').show();
        }
        last_chat_group_search_q = q;
        if (q === '') {
            $(listSelector + ' > .ow_nocontent').hide();
            $(listSelector + ' >  .owm_list_page .owm_preloader').hide();
        } else {
            $(listSelector + ' > .ow_nocontent').hide();
            $(listSelector + ' > #atLeastTwoChar').hide();
            $(listSelector + ' >  .owm_list_page .owm_preloader').show();
            setTimeout(function () {
                if (q !== last_chat_group_search_q)
                    return;
                if(window.mobileUserList !== undefined) {
                    window.mobileUserList.resetExcludeList();
                }
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {
                        'q': q
                    },
                    url: ajax_url,
                    success:
                        function (response) {
                            var lastSearch_q = response['last_q'];
                            var qNew = $('#frmmainpage_chat_group_search')[0].value;
                            if (qNew != lastSearch_q) {
                                return;
                            }
                            var data = response['tplList'];//jQuery.parseJSON(response);

                            if(window.mobileUserList !== undefined) {
                                window.mobileUserList.addDataToExcludeList(response);
                            }

                            $(listSelector + ' > .owm_list_page .owm_list_item').remove();
                            // $(listSelector + ' > .owm_list_page .owm_list_item_with_image').hide();
                            $(listSelector + ' >  .owm_list_page .owm_preloader').hide();
                            $(listSelector + ' > .ow_nocontent').hide();
                            if (data.length === 0) {
                                $(listSelector + ' > .ow_nocontent').css('display', 'block');
                            }
                            $.each(data, function (index, value) {
                                if(value.imageInfo != null){
                                    var colorAvatar = 'colorful_avatar_'+value.imageInfo.digit
                                }
                                if (value.type != undefined && value.type == 'group') {
                                    var partentTitle = '';
                                    if(value.parentTitle !=null )
                                    {
                                        partentTitle=value.parentTitle;
                                    }

                                    var html = '<div class="owm_list_item"><div class="ow_ipc owm_list_item_with_image owm_list_item_search_item"> <div class="ow_ipc_picture '+colorAvatar+'">' +
                                        '<img src="' + value.imageSrc + '" alt="' + value.imageTitle + '" title="' + value.imageTitle + '"> </div>' +
                                        '<div class="ow_ipc_info"> <div class="ow_ipc_header"><a href="' + value.url + '">' +
                                        value.title + '</a>'+partentTitle+'</div>	<div class="ow_ipc_content" onselectstart="return false" cb="set" style="user-select: none;">' +
                                        value.content + '</div> <div class="clearfix"> <div class="ow_ipc_toolbar ow_remark"> <span class="ow_nowrap">' +
                                        value.toolbar[0]['label'] + ' </span> </div> </div> </div> </div></div>';
                                } else {
                                    var html = '<div class="owm_list_item"><div class="owm_list_item_with_image owm_list_item_search_item" onclick="location.href=\'' + value.conversationUrl + '\';">' +
                                        '<div class="owm_user_list_item"><div class="owm_avatar '+colorAvatar+'"><img src="' + value.avatarUrl + '"></div>' +
                                        '<div class="owm_user_list_name">' +
                                        '   <span id="mailboxSidebarConversationsItemDisplayName"><a href="' + value.conversationUrl + '">' + value.opponentName + '</a></span>';
                                    if (value.unreadCount) {
                                        html += '   <span class="ow_unread_count" title="Unread Count">' + value.unreadCount + '</span>';
                                    }
                                    html += '</div>' +
                                        '<div class="owm_sidebar_convers_mail_theme" id="mailboxSidebarConversationsItemSubject" onselectstart="return false" cb="set" style="user-select: none;"><a emoji="5">' + value.text + '</a></div>' +
                                        '<div class="owm_profile_online" id="mailboxSidebarConversationsItemOnlineStatus" style="display: none;"></div>' +
                                        '<div class="owm_newsfeed_date">' + value.timeString + '</div></div></div></div>';
                                }
                                $(listSelector+ ' > .owm_list_page .owm_list_item_parent').append(html);
                            });
                        }
                })
            }, 500);
        }
    });
}


function ShowNewChatGroupList( event ) {
    OWM.FloatBox({
        "content": "<div id='new-chat-group' >"+$("#new-chat-group-list").html()+"</div>"
    });

    window.mobileNewChatUserList = new OW_UserList(event.data.userListData);

    $('body,html').css('overflow','hidden');

    $("#new-chat-group").on("remove", function () {
        $('body,html').css('overflow','initial');
    })

    $(function () {
        var lastText = '';
        var fullListLoadingStatus = 0;
        var input_selector = '#new-chat-group #frmmainpage_friends_search';

        var filterItems = function(q){
            if (q == ''){
//clear search
                $('#new-chat-group .owm_list_item_with_image').each(function(){
                    $(this).css('display', 'block');
                });
            }
            else{
//find items
                $('#new-chat-group .owm_list_item_with_image').each(function(){
                    if( $('.owm_user_list_name > a',this).text().toLowerCase().includes(q.toLowerCase()) ){
                        $(this).css('display', 'block');
                    }
                    else if( $('.owm_user_list_name > a[href]',this).attr('href').toLowerCase().includes(q.toLowerCase()) ){
                        $(this).css('display', 'block');
                    }
                    else {
                        $(this).css('display', 'none');
                    }
                });
            }

            var listCount = $('.owm_content_list_item.owm_list_item_with_image:visible').length;
            if(listCount>0)
                $('#new-chat-group .ow_nocontent').hide();
            else
                $('#new-chat-group .ow_nocontent').css('display', 'block');
        };

        var fullListLoaded = function(){
            fullListLoadingStatus = 2;
            var number = 0;
            $('.owm_user_list .owm_list_item_with_image').each(function(){
                $(this).attr('itemnum', number++);
            });
//remove loading
            $('.owm_user_list_search_preloader.owm_preloader').hide();

//refresh list
            q = $(input_selector)[0].value;
            filterItems(q);
            $('#friends_useritems_style')[0].innerHTML = '';
        };

        $(input_selector).on('change keydown paste input',function () {
            q = $(this)[0].value;
            if (lastText == q){
                return;
            }
            lastText = q;
            if (q == '')
            {
                filterItems(q);
            }
            else
            {
                if( fullListLoadingStatus == 2 ){
                    filterItems(q);
                }
                else if( fullListLoadingStatus == 0 ){
                    if(window.mobileNewChatUserList.process){
                        fullListLoaded();
                    }
                    else{
//show loading
                        var style = $('<style id="friends_useritems_style">.owm_user_list .owm_list_item_with_image { display: none }</style>');
                        $('html > head').append(style);
                        $('.owm_user_list_search_preloader.owm_preloader')[0].style = 'display: block; background-size: 40px; min-height: 100px; height: 100%;';
                        $('.owm_user_list_preloader.owm_preloader').hide();

                        window.mobileNewChatUserList.count = 1000;
                        window.mobileNewChatUserList.loadData();
                        fullListLoadingStatus = 1;

                        var timer = setInterval(function() {
                            if(window.mobileNewChatUserList.process)
                                return;
                            clearInterval(timer);
                            fullListLoaded();
                        }, 1000);
                    }
                }
            }
        });
    });

}