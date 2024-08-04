window.ow_newsfeed_const = {};
window.ow_newsfeed_feed_list = {};

var NEWSFEED_Ajax = function( url, data, callback, type ) {
    $.ajax({
        type: type === "POST" ? type : "GET",
        url: url,
        data: data,
        success: callback || $.noop(),
        dataType: "json"
    });
};
var lastScrollTop = 0;
var NEWSFEED_MobileFeed = function(autoId, data)
{
    var self = this;
    this.autoId = autoId;
    this.setData(data);

    this.containerNode = $('#' + autoId).get(0);
    this.$listNode = this.$('.owm_newsfeed_list');


    this.totalItems = 0;
    this.actionsCount = 0;
    this.allowLoadMore = this.data.data.viewMore;
    this.noMore = false;

    this.actions = {};
    this.actionsById = {};

    this.$viewMore = this.$('.feed-load-more');
    if(data.data.otpForm)
    {
        $("html, body").animate({ scrollTop: $(document).height()-$(window).height() });
    }

    $(window).scroll(function( event ) {

        var st = $(this).scrollTop();
        if (st < lastScrollTop) {
            lastScrollTop = st;
            self.tryLoadMore();
            fixDirections();
        }
        lastScrollTop = st;
    });

    self.tryLoadMore();
};

NEWSFEED_MobileFeed.prototype =
    {
        setData: function(data) {
            this.data = data;
        },

        adjust: function()
        {
            if ( this.$listNode.find('.owm_newsfeed_item:not(.owm_newsfeed_nocontent)').length )
            {
                this.$listNode.find('.owm_newsfeed_nocontent').hide();
            }
            else
            {
                this.$listNode.find('.owm_newsfeed_nocontent').show();
            }
        },

        reloadItem: function( actionId )
        {
            var action = this.actionsById[actionId];

            if ( !action )
            {
                return false;
            }

            this.loadItemMarkup({actionId: actionId}, function($m){
                $(action.containerNode).replaceWith($m);
            });
        },

        loadItemMarkup: function(params, callback)
        {
            var self = this;
            params.url = location.href;
            params.feedData = this.data;
            params = JSON.stringify(params);

            NEWSFEED_Ajax(window.ow_newsfeed_const.LOAD_ITEM_RSP, {p: params}, function( markup ) {
                if ( markup.result === 'error' )
                {
                    return false;
                }

                var $m = $(markup.html);
                callback.apply(self, [$m]);

                self.processMarkup(markup);
            }, "POST");
        },

        loadNewItem: function(params, preloader, callback)
        {
            if ( typeof preloader === 'undefined' )
            {
                preloader = true;
            }

            var self = this;
            if (preloader)
            {
                var $ph = self.getPlaceholder();
                this.$listNode.prepend($ph);
            }

            this.loadItemMarkup(params, function($a) {
                if(self.data.data.otpForm)
                {
                    self.$listNode.append($a.hide());
                    window.scrollTo(0, document.body.scrollHeight);
                    $('html, body').animate({scrollTop:$(document).height()}, 'slow');
                }else {
                    this.$listNode.find('div.owm_newsfeed_nocontent').first().after($a.hide());
                }

                if ( callback )
                {
                    callback.apply(self);
                }

                self.adjust();
                if ( preloader )
                {
                    var h = $a.height();
                    $a.height($ph.height());
                    $ph.replaceWith($a.css('opacity', '0.1').show());
                    $a.animate({opacity: 1, height: h}, 'fast');
                }
                else
                {
                    $a.animate({opacity: 'show', height: 'show'}, 'fast');
                }
            });
        },

        loadList: function( callback )
        {
            this.data.url = location.href;
            var self = this, params = JSON.stringify(this.data);
            NEWSFEED_Ajax(window.ow_newsfeed_const.LOAD_ITEM_LIST_RSP, {p: params}, function( markup ) {
                if ( markup.result === 'error' )
                {
                    return false;
                }

                var $m = $(markup.html).filter('.owm_newsfeed_item');
                callback.apply(self, [$m]);

                self.processMarkup(markup);
            }, "POST");
        },

        tryLoadMore: function()
        {
            if ( !this.allowLoadMore )
                return;
            if ( $('body.owm_sidebar_right_active, body.owm_sidebar_left_active').length > 0 )
                return;

            var self = this;
            if(self.data.data.otpForm)
            {
                if (lastScrollTop!= 0 && $(window).scrollTop() < 100 && !this.noMore) {
                    self.actionsCount = 0;
                    this.loadMore();
                }
            }else {
                var diff = $(document).height() - ($(window).scrollTop() + $(window).height());

                if (diff < 100 && !this.noMore) {
                    self.actionsCount = 0;
                    this.loadMore();
                }
            }
        },

        loadMore: function(callback)
        {
            var self = this;

            this.allowLoadMore = false;
            this.$viewMore.css("display", "block");
            if(!self.data.data.otpForm) {
                window.scrollTo(0, document.body.scrollHeight);
            }

            function completed()
            {
                var moreCount = self.totalItems - self.actionsCount;
                moreCount = moreCount < 0 ? 0 : moreCount;
                self.$viewMore.find(".feed-more-count").text(moreCount);
                self.$viewMore.css("display", "none");

                self.allowLoadMore = true;

                if ( !moreCount ) {
                    self.$viewMore.hide();
                    self.allowLoadMore = false;
                }
                fixDirections();
            }

            this.loadList(function( $m )
            {
                if($m.length==0){
                    self.noMore = true;
                }
                window.setTimeout(completed);
                if(self.data.data.otpForm) {
                    const position= this.$listNode.find('div.owm_newsfeed_nocontent').first().position();
                    this.$listNode.find('div.owm_newsfeed_nocontent').first().after($m);
                    if($m.length>0) {
                        $lastElement = $m.last()[0];
                        window.scrollTo(0, $lastElement.getBoundingClientRect()["y"]);
                    }
                }else{
                    self.$listNode.append($m);
                }
                if ( callback ) {
                    callback.apply(self);
                }
            });
        },

        getPlaceholder: function()
        {
            return $('<div class="owm_newsfeed_placeholder owm_preloader"></div>');
        },

        processMarkup: function( markup )
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
        },

        /**
         * @return jQuery
         */
        $: function(selector)
        {
            return $(selector, this.containerNode);
        }
    };


var NEWSFEED_MobileFeedItem = function(autoId, feed)
{
    this.autoId = autoId;
    this.containerNode = $('#' + autoId).get(0);

    this.feed = feed;
    feed.actionsById[autoId] = this;
    feed.actionsCount++;

    feed.lastItem = this;
};

NEWSFEED_MobileFeedItem.prototype =
    {
        construct: function(data)
        {
            var self = this;

            this.entityType = data.entityType;
            this.entityId = data.entityId;
            this.id = data.id;
            this.updateStamp = data.updateStamp;

            this.likes = data.likes;

            this.comments = data.comments;
            this.displayType = data.displayType;

            this.$contextMenu = this.$('.owm_newsfeed_context_menu');
            this.$contextAction = this.$contextMenu.find(".owm_context_action");
            this.$removeBtn = this.$('.newsfeed_remove_btn');

            this.$removeBtn.click(function()
            {
                var jc = $.confirm($(this).data("confirm-msg"));
                jc.buttons.ok.action = function () {
                    self.remove();
                    self.$removeBtn.hide();

                    if ( !self.$contextAction.find(".owm_newsfeed_context_list a:visible").length ) {
                        self.$contextAction.hide();
                    }
                }
                return false;
            });
        },

        remove: function()
        {
            var self = this;

            NEWSFEED_Ajax(window.ow_newsfeed_const.DELETE_RSP, {actionId: this.id}, function( msg ) {
                if ( self.displayType === 'page' )
                {
                    if ( msg )
                    {
                        OW.info(msg);
                    }
                }
            }, "POST");

            if ( self.displayType !== 'page' )
            {
                $(this.containerNode).remove();
                self.feed.adjust();
            }
        },

        /**
         * @return jQuery
         */
        $: function(selector)
        {
            return $(selector, this.containerNode);
        }
    };

NEWSFEED_MobileFeatureLikes = function( entityType, entityId, data ) {
    this.node = $("#" + data.uniqId);
    this.entityType = entityType;
    this.entityId = entityId;
    this.likeStringUniqId = data.likeStringUniqId;

    this.btn = $(".owm_newsfeed_control_btn", this.node);
    this.like_users = $(".owm_newsfeed_control_likes", this.node);
    this.data = data;
    this.liked = data.active;
    this.userVote = data.userVote;
    this.uri = data.uri;
    this.likes = data.likescount;
    this.dislikes = data.dislikescount;
    this.ownerId = data.ownerId;
    this.currentUserId = data.currentUserId;
    this.upUserId = data.upUserId;
    this.downUserId = data.downUserId;
    this.liked_user_list_label = data.liked_user_list_label;
    this.disliked_user_list_label = data.disliked_user_list_label;
    this.total_user_list_label = data.total_user_list_label;

    this.$likeBtn = $('.newsfeed_like_btn', this.node);
    this.$likeBtnCont = $('.newsfeed_like_btn_cont', this.node);

    this.$dislikeBtn = $('.newsfeed_dislike_btn', this.node);
    this.$dislikeBtnCont = $('.newsfeed_dislike_btn_cont', this.node);

    this.totalC = $('.newsfeed_counter_total', this.node);

    this.likesInprogress = false;
    this.extraLike = false;
    this.init();
};

NEWSFEED_MobileFeatureLikes.prototype = {
    init: function() {
        var self = this;


        this.like_users.click(function () {
            self.extraLike = true;
            if(self.data.count > 0){
                NEWSFEED_Ajax(window.ow_newsfeed_const.USERS_RSP, {entityType: self.entityType, entityId: self.entityId}, function(data)
                {
                    OWM.ajaxFloatBox("BASE_MCMP_AvatarUserList", [data], {
                        width: 315,
                        title: OWM.getLanguageText('newsfeed', 'ajax_floatbox_like_users')
                    });
                }, "POST");
            }
        });


        this.$likeBtn.click(function()
        {
            if ( self.$likeBtn.hasClass('owm_newsfeed_control_active') )
            {
                self.unlike();
            }
            else
            {
                self.like();
            }

            return false;
        });

        this.$dislikeBtn.click(function()
        {
            if ( self.$dislikeBtn.hasClass('owm_newsfeed_control_active') )
            {
                self.undislike();
            }
            else
            {
                self.dislike();
            }

            return false;
        });

    },

    query: function( rsp ) {
        var self = this;

        this.btn[this.liked ? "removeClass" : "addClass"]('owm_newsfeed_control_active');
        this.likesInprogress = true;
        setTimeout(function(){
            NEWSFEED_Ajax(rsp, {entityType: self.entityType, entityId: self.entityId}, function(c)
            {
                self.likesInprogress = false;
                self.node.find(".owm_newsfeed_control_counter").text(c.count);
                self.liked = !self.liked;
                self.data.count = c.count;
                if(c.count > 0){
                    if(self.like_users.hasClass("hidden"))
                        self.like_users.removeClass("hidden");
                    if($("#" + self.likeStringUniqId).hasClass("hidden")){
                        $("#" + self.likeStringUniqId).removeClass("hidden");
                    }
                }else{
                    if(!self.like_users.hasClass("hidden"))
                        self.like_users.addClass("hidden");
                    if(!$("#" + self.likeStringUniqId).hasClass("hidden")){
                        $("#" + self.likeStringUniqId).addClass("hidden");
                    }
                }
                $("#" + self.likeStringUniqId).empty().html(c.markup);
                if(self.extraLike){
                    self.extraLike = false;
                    if(self.liked)
                        self.unlike();
                    else
                        self.like();
                }
            }, "POST");
        }, 100);
    },

    refreshCounter: function()
    {
        var $totalC= $('.newsfeed_counter_total', this.node);
        $totalC.removeClass('ow_green').removeClass('ow_red');
        var signString = '0';
        var activeClassString = 'owm_newsfeed_control_active';
        var $likes = $('.newsfeed_counter_likes', this.node);

        var $dislikes = $('.newsfeed_counter_dislikes', this.node);


        $likes.text(parseInt(this.likes));
        this.dislikes = this.dislikes > 0 ? -1 * this.dislikes : this.dislikes;
        $dislikes.text(this.dislikes);
        this.total = parseInt(this.likes) + parseInt(this.dislikes);
        $totalC.text(this.total);

        if( this.total > 0 ) {
            signString = '+' + this.total;
            $totalC.addClass('ow_green');
        }
        else if( this.total < 0 ) {
            signString = this.total;
            $totalC.addClass('ow_red');
        }
    },

    like: function()
    {
        if (this.$likeBtn.data('error'))
        {
            OW.error(this.$likeBtn.data('error'));

            return false;
        }

        if ( this.likesInprogress )
        {
            return;
        }

        var self = this;

        this.likesInprogress = true;
        NEWSFEED_Ajax(window.ow_newsfeed_const.LIKE_RSP, {entityType: self.entityType, entityId: self.entityId, ownerId: this.ownerId, total: parseInt(this.totalC.text()) + 1, userVote: 1, uri: this.uri}, function(c)
        {
            if (c.length < 1 || c == undefined){
                messageShowed = true;
                var jc = $.confirm(OW.getLanguageText('frmuserlogin', 'loggedOut'));
                jc.buttons.ok.action = function () {
                    window.location.href = "sign-in";
                };
                jc.buttons.close.action = function () {
                    userLogin = false;
                };
            }
            else {
                self.$likeBtn.addClass('newsfeed_active_button');
                self.$likeBtn.addClass('owm_newsfeed_control_active');
                self.$dislikeBtn.removeClass('owm_newsfeed_control_active');
                self.likesInprogress = false;
                self.likes = self.likes + 1;

                self.upUserId.push(self.currentUserId);
                var index = self.downUserId.indexOf(self.currentUserId);
                if (index > -1) {
                    self.downUserId.splice(index, 1);
                    self.dislikes = self.dislikes + 1;
                }
                self.refreshCounter();
            }
        }, "POST");
    },

    dislike: function()
    {
        if (this.$likeBtn.data('error'))
        {
            OW.error(this.$likeBtn.data('error'));

            return false;
        }

        if ( this.likesInprogress )
        {
            return;
        }

        var self = this;

        this.likesInprogress = true;
        NEWSFEED_Ajax(window.ow_newsfeed_const.LIKE_RSP, {entityType: self.entityType, entityId: self.entityId, ownerId: this.ownerId, total: parseInt(this.totalC.text()) - 1, userVote: -1, uri: this.uri}, function(c)
        {

            if (c.length < 1 || c == undefined){
                messageShowed = true;
                var jc = $.confirm(OW.getLanguageText('frmuserlogin', 'loggedOut'));
                jc.buttons.ok.action = function () {
                    window.location.href = "sign-in";
                };
                jc.buttons.close.action = function () {
                    userLogin = false;
                };
            }
            else {
                self.$dislikeBtn.addClass('newsfeed_active_button');
                self.$dislikeBtn.addClass('owm_newsfeed_control_active');
                self.$likeBtn.removeClass('owm_newsfeed_control_active');
                self.likesInprogress = false;
                self.dislikes = self.dislikes - 1;

                self.downUserId.push(self.currentUserId);
                var index = self.upUserId.indexOf(self.currentUserId);
                if (index > -1) {
                    self.upUserId.splice(index, 1);
                    self.likes = self.likes - 1;
                }
                self.refreshCounter();
            }
        }, "POST");
    },

    undislike: function()
    {
        if ( this.likesInprogress )
        {
            return;
        }

        var self = this;

        this.likesInprogress = true;

        this.userVote = 0;
        NEWSFEED_Ajax(window.ow_newsfeed_const.LIKE_RSP, {entityType: self.entityType, entityId: self.entityId, ownerId: this.ownerId, total: parseInt(this.totalC.text()) + 1, userVote: parseInt(this.userVote), uri: this.uri}, function(c)
        {
            if (c.length < 1 || c == undefined){
                self.$likeBtn.addClass('newsfeed_active_button');
                self.$likeBtnCont.addClass('owm_newsfeed_control_active');
                messageShowed = true;
                var jc = $.confirm(OW.getLanguageText('frmuserlogin', 'loggedOut'));
                jc.buttons.ok.action = function () {
                    window.location.href = "sign-in";
                };
                jc.buttons.close.action = function () {
                    userLogin = false;
                };
            }
            else{
                self.$dislikeBtn.removeClass('newsfeed_active_button');
                self.$dislikeBtn.removeClass('owm_newsfeed_control_active');
                self.$likeBtn.removeClass('owm_newsfeed_control_active');
                self.dislikes = self.dislikes + 1;
                var index = self.downUserId.indexOf(self.currentUserId);
                if (index > -1) {
                    self.downUserId.splice(index, 1);
                }
                self.refreshCounter();
            }
        }, "POST");
    },
    unlike: function()
    {
        if ( this.likesInprogress )
        {
            return;
        }

        var self = this;

        this.likesInprogress = true;

        this.userVote = 0;
        NEWSFEED_Ajax(window.ow_newsfeed_const.UNLIKE_RSP, {entityType: self.entityType, entityId: self.entityId, ownerId: this.ownerId, total: parseInt(this.totalC.text()) - 1, userVote: parseInt(this.userVote), uri: this.uri}, function(c)
        {
            if (c.length < 1 || c == undefined){
                self.$likeBtn.addClass('newsfeed_active_button');
                self.$likeBtnCont.addClass('owm_newsfeed_control_active');
                messageShowed = true;
                var jc = $.confirm(OW.getLanguageText('frmuserlogin', 'loggedOut'));
                jc.buttons.ok.action = function () {
                    window.location.href = "sign-in";
                };
                jc.buttons.close.action = function () {
                    userLogin = false;
                };
            }
            else{
                self.$likeBtn.removeClass('newsfeed_active_button');
                self.$likeBtn.removeClass('owm_newsfeed_control_active');
                self.$dislikeBtn.removeClass('owm_newsfeed_control_active');
                self.likesInprogress = false;
                self.likes = self.likes - 1;
                var index = self.upUserId.indexOf(self.currentUserId);
                if (index > -1) {
                    self.upUserId.splice(index, 1);
                }
                self.refreshCounter();
            }
        }, "POST");
    },



};

function removeEmojis (string) {
    var regex = /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|[\ud83c[\ude01\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|[\ud83c[\ude32\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|[\ud83c[\ude50\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/g;

    return string.replace(regex, '');
}

function checkRtl( character ) {
    var RTL = ['ا','ب','پ','ت','س','ج','چ','ح','خ','د','ذ','ر','ز','ژ','س','ش','ص','ض','ط','ظ','ع','غ','ف','ق','ک','گ','ل','م','ن','و','ه','ی'];
    return RTL.indexOf( character ) > -1;
}

function checkLtr( character ) {
    var LTR = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    return LTR.indexOf( character ) > -1;
}

function fixAlignment (divs)
{
    var regex = /:[a-zA-Z\w0-9-]+:/g;
    for ( var index = 0; index < divs.length; index++ )
    {
        if(divs[index].className.indexOf("feedLtr")>-1 || divs[index].className.indexOf("feedRtl")>-1)
        {
            continue;
        }

        if($('html').attr('lang')=='fa-IR')
        {
            var isLtr=false;
            divText = divs[index].innerText.replace(regex,'');
            for ( var indexText = 0; indexText < divText.length; indexText++ )
            {
                if( checkLtr( divText[indexText] ) )
                {
                    divs[index].className += " feedLtr";
                    isLtr =true;
                    break;
                }else if( checkRtl( divText[indexText] ) ){
                    break;
                }
            }
            if(!isLtr){
                divs[index].className += " feedRtl";
            }
        }else{
            var isRtl=false;
            divText = divs[index].innerText.replace(regex,'');
            for ( var indexText = 0; indexText < divText.length; indexText++ )
            {
                if( checkRtl( divText[indexText] ) )
                {
                    divs[index].className += " feedRtl";
                    isRtl =true;
                    break;
                }else if( checkLtr( divText[indexText] ) ){
                    break;
                }
            }
            if(!isRtl){
                divs[index].className += " feedLtr";
            }
        }
    }
}


function fixDirections(){
    fixAlignment(document.getElementsByClassName('owm_newsfeed_item_content'));
    fixAlignment(document.getElementsByClassName('ow_autolink'));
    fixAlignment(document.getElementsByClassName('owm_newsfeed_body_status'));
    $('.owm_newsfeed_item_padding').css("direction", "auto");
    fixAlignment(document.getElementsByClassName('owm_newsfeed_content'));
    fixAlignment(document.getElementsByClassName('owm_newsfeed_body_descr'));
    fixAlignment(document.getElementsByClassName('owm_newsfeed_body_title'));
    fixAlignment(document.getElementsByClassName('owm_newsfeed_body_activity_title'));
}

$(document).ready(
    function(){
        fixDirections();
        if(OW_URL_HOME) {
            $('body').on('click', '.ow_newsfeed_content_reply_to', function(){
                var action_id = $(this).data('reply-action-id');
                if (eval(action_id) > 0) {
                    document.location = OW_URL_HOME + 'newsfeed/' + action_id;
                }
            });
        }
    }
);