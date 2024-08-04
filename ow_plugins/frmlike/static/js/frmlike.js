
var FRMLIKE = function( params ){
    this.params = {};
    var self = this;
    $.extend(this.params, params);
    var $context = $('#'+params.cmpId);
    this.$totalC = $('.frmlike_total_info', $context);
    this.$likeC = $('.frmlike_like', $context);
    this.$dislikeC = $('.frmlike_dislike', $context);
    this.$likeCounter = $('.frmlike_like_count', $context);
    this.$dislikeCounter = $('.frmlike_dislike_count', $context);
    this.$likeW = this.$likeC.closest('.ow_newsfeed_btn_wrap');
    this.$dislikeW = this.$dislikeC.closest('.ow_newsfeed_btn_wrap');
    this.$cmntItem = $context.closest('.' + this.params.parentClass);
    this.$cmntItemCnt = $('.frmlike_cont', this.$cmntItem);

    if( params.total < FRMLIKEData.actionLevel ){
        this.addNegativeBehav();
    }

    if( !FRMLIKEData.displayControls ){
        this.$cmntItem.hover(
            function(){
                self.$likeW.show();
                self.$dislikeW.show();
            },
            function(){
                self.$likeW.hide();
                self.$dislikeW.hide();
            }
        );
    }
    
    if( params.currentUserId === -1 ){
        var loginMsg = function(){
            OW.error(FRMLIKEData.loginMessage);
        };

        this.$likeC.click(loginMsg);
        this.$dislikeC.click(loginMsg);
        this.initUserList();
        return;
    }
    
    this.setVote(params.userVote);
    this.updateView();
}

FRMLIKE.prototype = {
    addNegativeBehav: function(){
        var self = this;
        if( FRMLIKEData.actionFade ){
            this.$cmntItem.hover(
                function(){
                    self.$cmntItemCnt.animate({opacity:1}, 500);
                },
                function(){
                    self.$cmntItemCnt.animate({opacity:FRMLIKEData.opacityLevel}, 500);
                }
            );
        }
        
        if( FRMLIKEData.actionHide ){
            $('.frmlikehide_h', self.$cmntItem).one('click',
                function(){
                    self.$cmntItemCnt.show();
                    $(this).parent().remove();
                }
            );
        }
    },
    updateView: function(){
        var self = this;
        
        this.$totalC.removeClass('ow_green').removeClass('ow_red');
        var signString = '0';
        var activeClassString = 'active';

        if($('.owm_newsfeed_comment_list').length){
            var activeClassString = 'owm_newsfeed_control_active';
        }

        if( this.params.total > 0 ){
            signString = '+' + this.params.total;
            this.$totalC.addClass('ow_green');
        }
        else if( this.params.total < 0 ){
            signString = this.params.total;
            this.$totalC.addClass('ow_red');
        }

        this.$totalC.html(signString);
        this.$likeCounter.html(this.params.up);
        this.$dislikeCounter.html(this.params.down);

        this.$likeC.unbind('click').removeClass(activeClassString).click(
            function(){
                if( self.params.userVote == 1 ){
                    self.setVote(0);
                }
                else{
                    self.setVote(1);
                }

                self.updateView();
                self.updateVote();
            }
        );
        
        this.$dislikeC.unbind('click').removeClass(activeClassString).click(
            function(){
                if( self.params.userVote == -1 ){
                    self.setVote(0);
                }
                else{
                    self.setVote(-1);                    
                }

                self.updateView();
                self.updateVote();
            }
        );
            
        if( this.params.userVote > 0 ){
            this.$likeC.addClass(activeClassString);
        }
        
        if( this.params.userVote < 0 ){
            this.$dislikeC.addClass(activeClassString);
        }
        
        // userLists
        this.initUserList();
    },
    
    initUserList: function(){
        var self = this;
        
        if( this.params.upUserId.length > 0 ){
            this.$likeCounter.css({cursor:'pointer'}).click(function(){OW.showUsers(self.params.upUserId, FRMLIKEData.likedListLabel);});
        }
        
        if( this.params.downUserId.length > 0 ){
            this.$dislikeCounter.css({cursor:'pointer'}).click(function(){OW.showUsers(self.params.downUserId, FRMLIKEData.dislikedListLabel);});
        }
        
        if( this.params.commonUserId.length > 0 ){
            this.$totalC.css({cursor:'pointer'}).click(function(){OW.showUsers(self.params.commonUserId, FRMLIKEData.totalListLabel);});
        }
    },
    
    setVote: function( vote ){
        var currentUId = FRMLIKEData.currentUserId;
        this.removeUserFromLists();
        this.$totalC.css({cursor:'auto'}).unbind('click');
        
        // rollback prev vote
        if( this.params.userVote == 1 ){
            this.params.total--;
            this.params.count--;
            this.params.up--;
            this.$likeCounter.css({cursor:'auto'}).unbind('click');
        }

        if( this.params.userVote == -1 ){
            this.params.total++;
            this.params.count--;
            this.params.down--;
            this.$dislikeCounter.css({cursor:'auto'}).unbind('click');
        }        

        this.params.userVote = vote;

        // set new vote
        if( this.params.userVote == 1 ){
            this.params.total++;
            this.params.count++;
            this.params.up++;
            this.params.upUserId.push(currentUId);
            this.params.commonUserId.push(currentUId);
        }

        if( this.params.userVote == -1 ){
            this.params.total--;
            this.params.count++;
            this.params.down++;
            this.params.downUserId.push(currentUId);
            this.params.commonUserId.push(currentUId);
        }        
    },

    removeUserFromLists: function(){
        var lists = [this.params.upUserId, this.params.downUserId, this.params.commonUserId], index;
        
        for( var i=0; i<lists.length; i++ ){
            index = $.inArray(FRMLIKEData.currentUserId, lists[i]);
            if( index > -1 ){
                lists[i].splice(index, 1);
            }
        }
    },

    updateVote: function(){
        var self = this;
        $.ajax({
            type: 'POST',
            url: FRMLIKEData.respondUrl,
            data: {entityId:self.params.entityId, entityType: self.params.entityType, ownerId:self.params.ownerId, total:self.params.total, userVote: self.params.userVote, uri: FRMLIKEData.currentUri},
            dataType: 'json',
            success : function(data){
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    }
}