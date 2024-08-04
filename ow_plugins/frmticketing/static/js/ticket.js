var Ticket = {
	construct: function(ticketInfo)
	{
		var self = this;
		this.$add_post_input = $('#'+this.add_post_input_id);
		
		$(".lock_ticket").bind("click", function() {
			var key = ( ticketInfo.locked===1 ) ? 'unlock_ticket_confirm' : 'lock_ticket_confirm';
			self.confirmAction(self.lockTicketUrl, OW.getLanguageText('frmticketing', key));
		});
		
		$(".delete_ticket").bind("click", function() {
			self.confirmAction(self.deleteTicketUrl, OW.getLanguageText('frmticketing', 'delete_ticket_confirm'));
		});

		$(":submit").bind("click", function(e) {
			e.preventDefault();
			var result = $.confirm(OW.getLanguageText('base', 'are_you_sure'));
			result.buttons.ok.action = function () {
				let $form = document.getElementsByName("add-post-form")[0];
				$form.submit();
			};
		});

		$(".quote_post a").bind("click", function() {
			var postId = $(this).attr("id");
			self.quotePost(postId);
		});
	},
	
	confirmAction: function(url, confirmText, postId)
	{
		var result = window.confirm(confirmText);
		if ( result ) {
			url = (postId) ? url.replace('postId', postId) : url;
			window.location.href = url;
		}		
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
	
	prepareEditTicketForm: function()
	{
		this.editTicketForm = window.owForms['edit-ticket-form'];
		this.editTicketFormFields = {
				$title: $(this.editTicketForm.elements['title'].input)
		};		
		
		this.editTicketForm.bind('success', function(result){
			if (result) {
				window.location.reload();
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
						if ( callback !== undefined ) {
							callback(result, self);	
						}
						if ( result !== undefined )
						{
							new Function(result.script)();
						}
					}
				});
	}
};

function searchTicket(url) {
    var searchTitle = $('#searchTitle')[0].value;
    var searchCategory = $('#searchCategory')[0].value;
    var searchLock = $('#searchLock')[0].value;
    var filter = "?searchTitle="+searchTitle+"&searchCategory="+searchCategory+"&searchLock="+searchLock;
	var searchOrder = $('#searchOrder')[0].value;
	filter= filter + "&searchOrder=" + searchOrder;
    url = url + filter;
    window.location = url;
}

function autoCompleteUser(url, username){
	if(username.length >= 3)
	{
		var data = {"username":username};
		$.ajax({
			url: url,
			type: 'post',
			dataType : "json",
			data: data,
			success: function(result){
				console.log(result);
			}
		});
	}
}

$(".ow_ticket_attachment").hover(
	function(){
		$(this).find("a.ticket_delete_attachment").show();
	},
	function(){
		$(this).find("a.ticket_delete_attachment").hide();
	}
);