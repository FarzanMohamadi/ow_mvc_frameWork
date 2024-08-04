var ForumCustomize = {
	request: [],
	
	construct: function()
	{
		var self = this;
		
		this.setSectionSortable();
		this.setGroupSortable();
		
		this.prepareAddForum();
		this.prepareEditSection();
		this.prepareEditGroup();
		
		$('.forum_group:visible:odd').addClass('ow_alt2');
		$('.forum_group:visible:even').addClass('ow_alt1');
		
		$('#finish_customizing').bind('click', function(){
			window.location.href = self.forumIndexUrl;
		});
		
		$('#add_forum_btn').bind('click', function(){

            var $form_content = $("#add_forum_form");

            window.add_forum_floatbox = new OW_FloatBox({
                $title: OW.getLanguageText('forum', 'add_new_forum_title'),
                $contents: $form_content,
                icon_class: 'ow_ic_add',
                width: 543
            });
            
            var $row = $("tr.private_forum_roles", self.addGroupForm.form);
            
            $(self.addGroupForm.elements['is-private'].input).change(function(){
				if ( $(this).attr("checked") ) {
					$row.show();
				}
				else {
					$row.hide();
				}
			});
		});
		
		$('.forum_section').each(function(){
			var $node = $(this);
			var sectionId = $node.attr('id').substr(8);
			
			$node.find('.section_delete').bind('click', function(){
				self.deleteSection($node, sectionId);
			});
			$node.find('.section_edit').bind('click', function(){
				self.editSection(sectionId);
			});
		});
		
		$('.forum_group').each(function(){
			var $node = $(this);
			
			var id = $node.attr("id");
			if ( id != undefined )
			{
				var groupId = id.substr(6);
			
				$node.find('.group_delete').bind('click', function(){
					self.deleteGroup($node, groupId);
				});
				$node.find('.group_edit').bind('click', function(){
					self.editGroup(groupId);
				});
			}
		});
				
	},
	
	setSectionSortable: function()
	{
		var self = this;
		
		$('.forum_sections').sortable({
			items: '.forum_section',
			handle: '.forum_section_tr',
			cursor: 'crosshair',
			helper: 'clone',
			placeholder: 'forum_placeholder',
			forcePlaceholderSize: true,
			start: function(event, ui){
				$(ui.placeholder).append('<tr><td colspan="4"></td></tr>');
                                $('.forum_sections').sortable( 'refreshPositions' );
			},			
			update: function(){
				var section = $('.forum_sections').sortable('serialize');
				self.ajaxCall(self.sortSectionOrderUrl, {data: section});
			}
		});		
	},
	
	setGroupSortable: function()
	{
		var self = this;
		
		$('.forum_section').sortable({
			items: '.forum_group',
			cancel: '.no_forum_group',
			cursor: 'crosshair',
			helper: 'clone',
			placeholder: 'forum_placeholder',
			forcePlaceholderSize: true,
			connectWith: '.forum_section',
			stop: function(event, ui){
				if ( self.request.length ) {
					self.ajaxCall(self.sortGroupOrderUrl, {data: JSON.stringify( self.request )});
				}
				self.request = [];
				
				// alt2
				var cycle = $(ui.item).hasClass('ow_alt2');
				
				$('.forum_group:visible').removeClass('ow_alt2');
				
				$('.forum_group:visible:odd').addClass('ow_alt2');
				
				if ( cycle != $(ui.item).hasClass('ow_alt2') ) {
					$('.forum_group').toggleClass('ow_alt2');
				}
				
				// alt1
				var cycle = $(ui.item).hasClass('ow_alt1');
				
				$('.forum_group:visible').removeClass('ow_alt1');
				
				$('.forum_group:visible:even').addClass('ow_alt1');
				
				if ( cycle != $(ui.item).hasClass('ow_alt1') ) {
					$('.forum_group').toggleClass('ow_alt1');
				}
			},
			start: function(event, ui){
				$(ui.placeholder).append('<td colspan="4"></td>');
			},			
			update: function(e, ui){
				if (!ui.sender) {
					self.request = [];

					self.request.push({
						sectionId: $(this).attr('id').substr(8),
						order: $(this).sortable('serialize')
					});
				}
				else {
					self.request = [];
					self.request.push({
						sectionId: $(this).attr('id').substr(8),
						order: $(this).sortable('serialize')
					});
					
					self.request.push({
						sectionId: $(ui.sender).attr('id').substr(8),
						order: $(ui.sender).sortable('serialize')
					});
				}
			},
			receive: function(event, ui){
				var sender_length = $(ui.sender).find('tr').length;

				if ( sender_length==2 ) {
					$(ui.sender).find('.no_forum_group').show();
				}
				else {
					$(ui.item).parent().find('.no_forum_group').hide();
				}
				var receiver_length = $(this).find('tr').length;

				if ( receiver_length==2 ) {
					$(this).find('.no_forum_group').show();
				}
				else {
					$(this).find('.no_forum_group').hide();
				}
			}
		});		
	},
	
	deleteSection: function($node, sectionId)
	{
		var self = this;
		var result = $.confirm(OW.getLanguageText('forum', 'delete_section_confirm'));

        result.buttons.ok.action = function () {
            self.ajaxCall(self.deleteSectionUrl, {sectionId:sectionId}, function(data){
                if ( data ) {
                    $node.remove();
                }
            });
		}
		return false;
	},
	
	editSection: function(sectionId)
	{
		var self = this;
				
		this.ajaxCall(this.getSectionUrl, {sectionId:sectionId}, function(section){
			if ( section ) {
				self.editSectionFormFields.$sectionName.val(section.name);
				self.editSectionFormFields.$sectionId.val(section.id);

                var $form_content = $("#edit_section_form");

                window.edit_section_floatbox = new OW_FloatBox({
                    $title: OW.getLanguageText('forum', 'edit_section_title'),
                    $contents: $form_content,
                    icon_class: 'ow_ic_edit',
                    width: 550
                });
			}
		});		
	},

	deleteGroup: function($node, groupId)
    {
        var result = $.confirm(OW.getLanguageText('forum', 'delete_group_confirm'));
        var thisVar = this;
        result.buttons.ok.action = function () {
        	thisVar.ajaxCall(thisVar.deleteGroupUrl, {groupId:groupId}, function(data){
        		if ( data ) {
                    $node.remove();
                }
            });
        };
        return false;
    },

	editGroup: function(groupId)
	{
		var self = this;
		this.ajaxCall(this.getGroupUrl, {groupId:groupId}, function(group){
			if ( group ) {

				var $row = $("tr.private_forum_roles", self.editGroupForm.form);
				
				$("input[type=checkbox]", $row).attr("checked", false);
				
                if ( group.isPrivate == 1 ) {
                	if ( group.roles != null ) {
                		for ( i=0; i<group.roles.length; i++ ) { 
                			$("input[type=checkbox][value=" + group.roles[i] + "]", $row).attr("checked", "checked");
                		}
                	}
                	$row.show();
                }
                else {
                	$row.hide();
                }
				
                var $form_content = $("#edit_group_form");
                
                window.edit_group_floatbox = new OW_FloatBox({
                    $title: OW.getLanguageText('forum', 'edit_group_title'),
                    $contents: $form_content,
                    icon_class: 'ow_ic_edit',
                    width: 545
                });
				
				self.editGroupFormFields.$groupId.val(group.id);
				self.editGroupFormFields.$description.val(group.description);
				self.editGroupFormFields.$groupName.val(group.name);
				self.editGroupFormFields.$isPrivate.attr("checked", group.isPrivate == 1 ? "checked" : false);
				
				self.editGroupFormFields.$isPrivate.change(function(){
					if ( $(this).attr("checked") ) {
						$row.show();
					}
					else {
						$row.hide();
					}
				});
			}
		});	
	},
	
	prepareAddForum: function()
	{
		var self = this;
		this.addGroupForm = window.owForms['add-forum-form'];
				
		this.addGroupForm.bind('success', function(result){
			if (result) {
				window.location.reload();
			}
		});		
	},
	
	prepareEditSection: function()
	{
		this.editSectionForm = window.owForms['edit-section-form'];
		this.editSectionFormFields = {
				$sectionId: $(this.editSectionForm.elements['section-id'].input),
				
				$sectionName: $(this.editSectionForm.elements['section-name'].input)
		};		
		
		this.editSectionForm.bind('success', function(result){
			if (result) {
				window.location.reload();
			}
		});		
	},
	
	prepareEditGroup: function()
	{
		this.editGroupForm = window.owForms['edit-group-form'];
		this.editGroupFormFields = {
				$groupId: $(this.editGroupForm.elements['group-id'].input),
				$groupName: $(this.editGroupForm.elements['group-name'].input),
				$description: $(this.editGroupForm.elements['description'].input),
				$isPrivate: $(this.editGroupForm.elements['is-private'].input)
		};		
		
		this.editGroupForm.bind('success', function(result){
			if (result) {
				window.location.reload();
			}
		});		
	},	
	
	selectSection: function(sectionName)
	{	
		ForumCustomize.addGroupFormFields.$section.val( $(sectionName).text() ).focus();
		ForumCustomize.addGroupFormFields.$sectionId.val(sectionName.extra[0]);
	},
		
	ajaxCall: function(url, params, callback) 
	{
		var self = this;
		$.ajax({
				url: url,
				type: 'post',
				dataType: 'json',
				data: params,
				success: function(result){
					if ( callback != undefined ) {
						callback(result, self);	
					}
					if ( result != undefined )
					{
						new Function(result.script)();
					}
				}
			});
	}	
};

var ForumTopic = {
	construct: function(topicInfo)
	{
		var self = this;
		this.$add_post_input = $('#'+this.add_post_input_id);
		
		$(".sticky_topic").bind("click", function() {
			var key = ( topicInfo.sticky==1 ) ? 'unsticky_topic_confirm' : 'sticky_topic_confirm';
            var jc = $.confirm(OW.getLanguageText('forum', key));
            jc.buttons.ok.action = function () {
                self.actionAfterConfirm(self.stickyTopicUrl, null);
            }
		});
		
		$(".lock_topic").bind("click", function() {
			var key = ( topicInfo.locked==1 ) ? 'unlock_topic_confirm' : 'lock_topic_confirm';
            var jc = $.confirm(OW.getLanguageText('forum', key));
            jc.buttons.ok.action = function () {
                self.actionAfterConfirm(self.lockTopicUrl, null);
            }
		});
		
		$(".delete_topic").bind("click", function() {
            var jc = $.confirm(OW.getLanguageText('forum', 'delete_topic_confirm'));
            jc.buttons.ok.action = function () {
                self.actionAfterConfirm(self.deleteTopicUrl, null);
            }
		});		
	
		$(".delete_post a").bind("click", function() {
			var postId = $(this).attr("id");
            var jc = $.confirm(OW.getLanguageText('forum', 'delete_post_confirm'));
            jc.buttons.ok.action = function () {
                self.actionAfterConfirm(self.deletePostUrl, postId);
            }
        });
		
		$(".quote_post a").bind("click", function() {
			var postId = $(this).attr("id");
			self.quotePost(postId);
		});
		
		$("#cb-subscribe").bind("change", function() {
			if ( $(this).attr("checked") )
			{
				var url = self.subscribeTopicUrl;
			}
			else
			{
				var url = self.unsubscribeTopicUrl;
			}
			
			self.ajaxCall(url, function(post) {
				if ( post.error )
				{
					OW.error(post.erro);
				}
				else {
					var subscribe_link = $(".subscribe_to_topic");
                    if (subscribe_link.hasClass("subscribe")) {
                        subscribe_link.addClass("unsubscribe").removeClass("subscribe").html( OW.getLanguageText('forum', 'unsubscribeNow') );
                    } else {
                        subscribe_link.addClass("subscribe").removeClass("unsubscribe").html( OW.getLanguageText('forum', 'subscribe') );
                    }
                    if ( post.msg ){
                        OW.info(post.msg);
                    }
                }
			});
		});

		$(".subscribe_to_topic").bind("click", function() {
            $("#cb-subscribe").click().change();
		});
				
        if (topicInfo.ishidden == 0)
        {
            $(".move_topic").bind("click", function() {
                self.moveTopic();
            });

            this.prepareMoveTopicForm();

        }
	},
	
	confirmAction: function(url, confirmText, postId)
	{
		var self = this;
		var result = window.confirm(confirmText);
		if ( result ) {
            self.actionAfterConfirm(url, postId);
		}		
	},

	actionAfterConfirm: function(url, postId)
	{
		url = (postId) ? url.replace('postId', postId) : url;
		window.location.href = url;
	},

	quotePost: function(postId)
	{
		var self = this;
		
		if ( document.getSelection )
		{
			var selText = document.getSelection();
		}
		else if ( document.selection )
		{
			var selText = document.selection.createRange().text;
		}
				
		var textarea = self.$add_post_input.get(0);
		textarea.htmlarea();
		textarea.htmlareaFocus();
		
		var url = this.getPostUrl.replace('postId', postId);
		this.ajaxCall(url, function(quote) {
			var areaObj = self.$add_post_input.get(0).jhtmlareaObject;
			areaObj.pasteHTML("<br />");
			areaObj.pasteHTML(quote);
			if($('#cke_1_contents').length>0) {
                var iframe = $('#cke_1_contents')[0].getElementsByTagName("iframe")[0];
                iframe.contentDocument.body.innerHTML=iframe.contentDocument.body.innerHTML+"<p>&nbsp;</p>";
            }else if($('.input_ws_cont').length>0)
			{
                var iframe = $('.input_ws_cont')[0].getElementsByTagName("iframe")[0];
                iframe.contentDocument.body.innerHTML=iframe.contentDocument.body.innerHTML+"<p>&nbsp;</p>";
			}

		});		
	},
		
	moveTopic: function()
	{
		var $form_content = $("#move_topic_form").children();
		
		 window.move_topic_floatbox = new OW_FloatBox({
             $title: OW.getLanguageText('forum', 'move_topic_title'),
             $contents: $form_content,
             icon_class: 'ow_ic_move',
             width: 400
         });
	},		
	
	prepareEditPostForm: function()
	{
		this.editPostForm = window.owForms['edit-post-form'];
		this.editPostFormFields = {
				$postId: $(this.editPostForm.elements['post-id'].input),
				$text: $(this.editPostForm.elements['text'].input)
		};		
		
		this.editPostForm.bind('success', function(result){
			if (result) {
				window.location.reload();
			}
		});
	},
	
	prepareEditTopicForm: function()
	{
		this.editTopicForm = window.owForms['edit-topic-form'];
		this.editTopicFormFields = {
				$title: $(this.editTopicForm.elements['title'].input)
		};		
		
		this.editTopicForm.bind('success', function(result){
			if (result) {
				window.location.reload();
			}
		});
	},
	
	prepareMoveTopicForm: function()
	{
		this.moveTopicForm = window.owForms['move-topic-form'];
		this.moveTopicFormFields = {
				$groupId: $(this.moveTopicForm.elements['group-id'].input)
		};		
		
		this.moveTopicForm.bind('success', function(url){
			if (url) {
				window.location.href = url;
			}
		});
	},	
		
	ajaxCall: function(url, callback) 
	{
		var self = this;
		$.ajax({
				url: url,
				type: "get",
				dataType: "json",
					success: function(result){
						if ( callback != undefined ) {
							callback(result, self);	
						}
						if ( result != undefined )
						{
							new Function(result.script)();
						}
					}
				});
	}
};

$(document).ready(function(){
    $(document).on("change", '#change_forum_topic_posts_show_order', function(e) {
        var url = document.location.href;
        var reverse_forum_posts_show = false;
        if ($(e.target).find(":selected").hasClass("descending_sort"))
            reverse_forum_posts_show = true;
        var split_url_by_sharp_sign = url.split("#");
        url = split_url_by_sharp_sign[0];
        var url_after_sharp_sign = split_url_by_sharp_sign.slice(1);
        if(url.includes('?')) {
            if(!document.location.href.includes('reverse_sort')) {
                url = url + "&reverse_sort=" + reverse_forum_posts_show;
            } else{
                url = url.replace("reverse_sort=true", "reverse_sort=" + reverse_forum_posts_show)
                    .replace("reverse_sort=false", "reverse_sort=" + reverse_forum_posts_show);
            }
        }else {
            url = document.location.href+"?reverse_sort=" + reverse_forum_posts_show;
        }
        for (var i = 0; i < url_after_sharp_sign.length; i++)
            url = url + "#" + url_after_sharp_sign[i];
        document.location = url;
    });
});