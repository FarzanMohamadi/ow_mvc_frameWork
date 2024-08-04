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

var NEWSFEED_Feed = function(autoId, data)
{
	var self = this;
	this.autoId = autoId;
	this.setData(data);

	this.containerNode = $('#' + autoId).get(0);
	this.$listNode = this.$('.ow_newsfeed');

	this.totalItems = 0;
	this.actionsCount = 0;

	this.actions = {};
	this.actionsById = {};


	this.$viewMore = this.$('.ow_newsfeed_view_more_c');

	this.$viewMore.find('input.ow_newsfeed_view_more').click(function(){
		var btn = this;
		OW.inProgressNode(this);
		this.classList.add('ow_inprogress');
		self.loadMore(function(){
			OW.activateNode(btn);
			if ( self.totalItems > self.actionsCount)
			{
				self.$viewMore.show();
                fixDirections();
			}else if(self.data && self.data.driver && self.data.driver.params && self.data.driver.params.length && self.data.driver.params.length > self.actionsCount){
				self.$viewMore.show();
                fixDirections();
			}
		});
	});

	OW.bind('base.comments_list_init', function(p)
        {
		if ( self.actions[p.entityType + '.' + p.entityId] && p.replyCommentId == null)
		{
			self.actions[p.entityType + '.' + p.entityId].comments = this.totalCount;
			self.actions[p.entityType + '.' + p.entityId].refreshCounter();
		}
	});
};

NEWSFEED_Feed.prototype =
{
                setData: function(data) {
                    this.data = data;
                },

		adjust: function()
		{
                    if ( this.$listNode.find('.ow_newsfeed_item:not(.newsfeed_nocontent)').length )
                    {
                        this.$listNode.find('.newsfeed_nocontent').hide();
                    }
                    else
                    {
                        this.$listNode.find('.newsfeed_nocontent').show();
                    }

                    this.$listNode.find('.ow_newsfeed_item:last-child .newsfeed-item-delim').hide();
		},

		reloadItem: function( actionId )
		{
			var action = this.actionsById[actionId];

			if ( !action )
			{
				return false;
			}

			this.loadItemMarkup({actionId: actionId,  cycle: action.cycle}, function($m){
				$(action.containerNode).replaceWith($m);
			});
		},

		loadItemMarkup: function(params, callback)
		{
			var self = this;
            params.url = location.href;
			params.feedData = this.data;
			params.cycle = params.cycle || {lastItem: false};
            try {
                if (params.feedData.data.additionalParamList.group.description !== undefined) {
                    delete params.feedData.data.additionalParamList.group.description;
                }
                if (params.feedData.driver.params.additionalParamList.group.description !== undefined) {
                    delete params.feedData.driver.params.additionalParamList.group.description;
                }
            } catch (e) {}

			params = JSON.stringify(params);

			NEWSFEED_Ajax(window.ow_newsfeed_const.LOAD_ITEM_RSP, {p: params}, function( markup ) {

				if ( markup.result == 'error' )
				{
					return false;
				}

				var $m = $(markup.html);
				callback.apply(self, [$m]);
				OW.bindAutoClicks($m);

				self.processMarkup(markup);
			}, "POST");
		},

		loadNewItem: function(params, preloader, callback)
		{
			if ( typeof preloader == 'undefined' )
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
				this.$listNode.find('li.ow_newsfeed_item').first().after($a.hide());

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
            try {
                if (this.data.data.additionalParamList.group.description !== undefined) {
                    delete this.data.data.additionalParamList.group.description;
                }
                if (this.data.driver.params.additionalParamList.group.description !== undefined) {
                    delete this.data.driver.params.additionalParamList.group.description;
                }
            } catch (e) {}

            var self = this, params = JSON.stringify(this.data);
			NEWSFEED_Ajax(window.ow_newsfeed_const.LOAD_ITEM_LIST_RSP, {p: params}, function( markup ) {

				if ( markup.result == 'error' )
				{
					return false;
				}

				var $m = $(markup.html).filter('li');
				callback.apply(self, [$m]);
				OW.bindAutoClicks($m);
				self.processMarkup(markup);
			}, "POST");
		},

		loadMore: function(callback)
		{
			var self = this;
			var li = this.lastItem;

			this.loadList(function( $m )
			{
				var w = $('<li class="newsfeed_item_tmp_wrapper"></li>').append($m).hide();
				self.$viewMore.hide();
				li.$delim.show();

				self.$listNode.append(w);

				w.slideDown('normal', function() {
					w.before(w.children()).remove();
					if ( callback )
					{
                                            callback.apply(self);
					}
				});
			})
		},

		getPlaceholder: function()
		{
			return $('<li class="ow_newsfeed_placeholder ow_preloader"></li>');
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
}


var NEWSFEED_FeedItem = function(autoId, feed)
{
	this.autoId = autoId;
	this.containerNode = $('#' + autoId).get(0);

	this.feed = feed;
	feed.actionsById[autoId] = this;
	feed.actionsCount++;
	feed.lastItem = this;
};

NEWSFEED_FeedItem.prototype =
{
		construct: function(data)
		{
			var self = this;

			this.entityType = data.entityType;
			this.entityId = data.entityId;
			this.id = data.id;
			this.updateStamp = data.updateStamp;

			this.userVote = data.userVote;
            this.uri = data.uri;
			this.likes = data.likes;
			this.dislikes = data.dislikes;
			this.ownerId = data.ownerId;
			this.currentUserId = data.currentUserId;
            this.upUserId = data.upUserId;
            this.downUserId = data.downUserId;

            this.liked_user_list_label = data.liked_user_list_label;
            this.disliked_user_list_label = data.disliked_user_list_label;
            this.total_user_list_label = data.total_user_list_label;

			this.comments = data.comments;
                        this.displayType = data.displayType;

			this.cycle = data.cycle || {lastItem: false};

            this.$contextMenu = this.$('.ow_newsfeed_context_menu');
            this.$contextAction = this.$contextMenu.find(".ow_context_action_block");

            self.$contextMenu.show().css({opacity: "1"});
            $('a.cd-nav-trigger', this.$contextMenu).show().css({opacity: "0.4", "transition-duration": "0.5s"});
            $('.ow_newsfeed_context_menu_wrap, .ow_newsfeed_line').hover(function(){
                $('a.cd-nav-trigger', this).show().css({opacity: "1"});
            }, function(){
                $('a.cd-nav-trigger', this).show().css({opacity: "0.4"});
            }).on('click',function(){
                //$('.ow_newsfeed_context_tooltip', this).hide();
            });

			this.$commentBtn = this.$('.newsfeed_comment_btn');
                        this.$commentBtnCont = this.$('.newsfeed_comment_btn_cont');
                        this.$commentsCont = this.$('.ow_tooltip.ow_newsfeed_tooltip.ow_add_comments_form.ow_small');

			this.$likeBtn = this.$('.newsfeed_like_btn');
                        this.$likeBtnCont = this.$('.newsfeed_like_btn_cont');
                        this.likesInprogress = false;

            this.$dislikeBtn = this.$('.newsfeed_dislike_btn');
            this.$dislikeBtnCont = this.$('.newsfeed_dislike_btn_cont');

            this.totalC = this.$('.newsfeed_counter_total');
			this.$removeBtn = this.$('.newsfeed_remove_btn');
			this.$delim = this.$('.newsfeed-item-delim');

            self.refreshCounter();

                        this.$attachment = this.$('.newsfeed_attachment');
                        this.hasAttachment = this.$attachment.length;
                        

                        this.$attachment.find('.newsfeed_attachment_remove').click(function(){
                            self.$attachment.animate({opacity: 'hide', height: 'hide'}, 'fast', function() {
                                self.$attachment.remove();
                            });

                            NEWSFEED_Ajax(window.ow_newsfeed_const.REMOVE_ATTACHMENT, {actionId: self.id}, '', "POST");

                            return false;
                        });

			this.$commentBtn.click(function()
                        {
                            if ( self.$commentBtn.hasClass('newsfeed_active_button') )
                            {
                                self.hideComments();
                            }
                            else
                            {
                                self.showComments();
                            }

                            return false;
			});

			this.$likeBtn.click(function()
                        {
                            if ( self.$likeBtnCont.hasClass('active') )
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
                if ( self.$dislikeBtn.hasClass('active') )
                {
                    self.undislike();
                }
                else
                {
                    self.dislike();
                }

                return false;
            });

            this.$removeBtn.click(function () {
                var jc = $.confirm($(this).data("confirm-msg"));
                jc.buttons.ok.action = function () {
                    self.remove();
                    self.$removeBtn.hide();

                    if (!self.$contextAction.find("a:visible").length) {
                        self.$contextAction.hide();
                    }
                }

                return false;
            });
		},

		refreshCounter: function()
                {
            var $totalC= this.$('.newsfeed_counter_total');
            $totalC.removeClass('ow_green').removeClass('ow_red');
            var signString = '0';
            var activeClassString = 'active';
			var $likes = this.$('.newsfeed_counter_likes'),
                            $comments = this.$('.newsfeed_counter_comments');

            var $dislikes = this.$('.newsfeed_counter_dislikes');

            
            $likes.text(parseInt(this.likes));
                        $comments.text(parseInt(this.comments));
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

            var self = this;
            if( this.upUserId.length > 0 ){
                $likes.css({cursor:'pointer'}).click(function(){OW.showUsers(self.upUserId, self.liked_user_list_label)});
            }

            if( this.downUserId.length > 0 ){
                $dislikes.css({cursor:'pointer'}).click(function(){OW.showUsers(self.downUserId, self.disliked_user_list_label)});
            }

            if( this.upUserId.length > 0 || this.downUserId.length > 0 ){
                $totalC.css({cursor:'pointer'}).click(function(){OW.showUsers(self.upUserId.concat(self.downUserId), self.total_user_list_label)});
            }
		},

		showComments: function()
		{
                    var $c = this.$commentsCont.slideDown('fast');
                    this.$commentBtn.addClass('newsfeed_active_button');
                    this.$commentBtnCont.addClass('active');

                    $c.show().find('.ow_newsfeed_comments').show().find('textarea').focus();
		},

                hideComments: function()
		{
                    this.$commentsCont.slideUp('fast');
                    this.$commentBtn.removeClass('newsfeed_active_button');
                    this.$commentBtnCont.removeClass('active');
                    this.$commentsCont.slideUp('fast');
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
                    self.$likeBtnCont.addClass('active');
                    self.$dislikeBtn.removeClass('active');
                    self.likesInprogress = false;
                    self.likes = self.likes + 1;

                    self.upUserId.push(self.currentUserId);
                    var index = self.downUserId.indexOf(self.currentUserId);
                    if (index > -1) {
                        self.downUserId.splice(index, 1);
                        self.dislikes = self.dislikes + 1;
                    }
                    self.showLikes(c.markup);
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
                self.$dislikeBtn.addClass('active');
                self.$likeBtnCont.removeClass('active');
                self.likesInprogress = false;
                self.dislikes = self.dislikes - 1;

                self.downUserId.push(self.currentUserId);
                var index = self.upUserId.indexOf(self.currentUserId);
                if (index > -1) {
                    self.upUserId.splice(index, 1);
                    self.likes = self.likes - 1;
                }
                self.showLikes(c.markup);
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
                self.$likeBtnCont.addClass('active');
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
                self.$dislikeBtn.removeClass('active');
                self.$likeBtnCont.removeClass('active');
                self.dislikes = self.dislikes + 1;
                var index = self.downUserId.indexOf(self.currentUserId);
                if (index > -1) {
                    self.downUserId.splice(index, 1);
                }
                self.showLikes(c.markup);
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
                    self.$likeBtnCont.addClass('active');
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
                    self.$likeBtnCont.removeClass('active');
                    self.$dislikeBtn.removeClass('active');
                    self.likesInprogress = false;
                    self.likes = self.likes - 1;
                    var index = self.upUserId.indexOf(self.currentUserId);
                    if (index > -1) {
                        self.upUserId.splice(index, 1);
                    }
                    self.showLikes(c.markup);
                    self.refreshCounter();
                }
            }, "POST");
        },

		showLikes: function( likesHtml )
		{
                    var $likes = this.$('.newsfeed_likes_string');
                    $likes.empty().html(likesHtml);

                    if ( this.likes > 0 )
                    {
                        $likes.show();
                    }
		},

		remove: function()
		{
			var self = this;

                        NEWSFEED_Ajax(window.ow_newsfeed_const.DELETE_RSP, {actionId: this.id}, function(result)
                        {
                            if ( self.displayType == 'page' )
                            {
                                if ( result )
                                {
                                    if(result['msg']) {
                                        OW.info(result['msg']);
                                    }
                                    if(result['url']){
                                        window.location.href = result['url'];
                                        return;
                                    }

                                }
                            }
                        }, "POST");

                        if ( self.displayType != 'page' )
                        {
                            $(this.containerNode).animate({opacity: 'hide', height: 'hide'}, 'fast', function()
                            {
                                $(this).remove();

                                self.feed.adjust();
                            });
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
    var regex_emoji = /:[a-zA-Z\w0-9-]+:/g;
    var regex_mention = /[@](\w|[\u0620-\u064A\u067B\u067E\u0686\u068C\u0698\u06A9\u06AF\u06BE\u06CC\u202B\u200C]|-|_)+/g;
    var regex_hashtag = /[#](\w|[\u0620-\u064A\u067B\u067E\u0686\u068C\u0698\u06A9\u06AF\u06BE\u06CC\u202B\u200C]|-|_)+/g;
    for ( var index = 0; index < divs.length; index++ )
    {

        if($('html').attr('lang')=='fa-IR')
        {
            var isLtr=false;
            divText = $(divs[index]).text().replace(regex_emoji,'');
            divText = divText.replace(regex_mention,'');
            divText = divText.replace(regex_hashtag,'');
            for ( var indexText = 0; indexText < divText.length; indexText++ )
            {
                if( checkLtr( divText[indexText] ) )
                {
                    divs[index].className += " feedLtr";
                    isLtr =true;
                    if( divs[index].className.includes("ow_newsfeed_item_content ow_newsfeed_item_image_content") ||
                        divs[index].className.includes("attachment_right"))
                    {
                        if(divs[index].previousElementSibling.className.includes("ow_newsfeed_item_picture") ||
                            divs[index].previousElementSibling.className.includes("attachment_left"))
                        {
                            divs[index].previousElementSibling.className += " feedLtr";
                        }
                        if (divs[index].parentElement.className.includes("link two_column has_thumbnail"))
                        {
                            divs[index].parentElement.className += " feedLtr";
                        }
                    }
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
            divText = $(divs[index]).text().replace(regex_emoji,'');
            divText = divText.replace(regex_mention,'');
            divText = divText.replace(regex_hashtag,'');
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
    fixAlignment(document.getElementsByClassName('ow_newsfeed_item_content'));
    fixAlignment(document.getElementsByClassName('ow_autolink'));
    fixAlignment(document.getElementsByClassName('ow_newsfeed_body_status'));
    fixAlignment(document.getElementsByClassName('ow_newsfeed_content'));
    fixAlignment(document.getElementsByClassName('ow_newsfeed_item_title'));
    fixAlignment(document.getElementsByClassName('ow_remark ow_smallmargin'));
    fixAlignment(document.getElementsByClassName('ow_newsfeed_activity_title'));
    fixAlignment(document.getElementsByClassName('ow_mini_ipc_header'));
    fixAlignment(document.getElementsByClassName('ow_mini_ipc_content'));
    fixAlignment(document.getElementsByClassName('attachment_right'));
    fixAlignment(document.getElementsByClassName('ow_comments_content'));
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
