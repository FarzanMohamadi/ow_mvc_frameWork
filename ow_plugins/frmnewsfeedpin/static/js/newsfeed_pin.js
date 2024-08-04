var FRMNEWSFEEDPIN_PinItem = function (isPinned, autoId) {
    this.isPinned = isPinned;
    this.autoId = autoId;
};

FRMNEWSFEEDPIN_PinItem.prototype =
    {
        construct: function (data) {
            this.$removePinAjaxURL = data.removeURL;
            this.$addPinAjaxURL = data.addURL;
            this.$entityId = data.entityId;
            this.$entityType = data.entityType;
            this.isPinnedPage = data.isPinnedPage;

            if (this.isPinned) {
                this.$pinBtn = $('#' + this.autoId + ' .frmnewsfeedpin_remove_pin');
                this.$pinBtn.click({self: this}, this.remove);
            } else {
                this.$pinBtn = $('#' + this.autoId + ' .frmnewsfeedpin_add_pin');
                this.$pinBtn.click({self: this}, this.pin);
            }
        },

        remove: function (event) {
            var self = event.data.self;
            var ajax_settings = {
                url: self.$removePinAjaxURL,
                type: 'POST',
                data: {className: $(this).attr('class')},
                dataType: 'json',
                success: function (data) {
                    if (data['error']) {
                        OW.error(data['msg']);
                    } else {
                        OW.info(data['msg']);
                        ow_frmnewsfeedpin_feed_list[self.autoId].isPinned = false;
                        var query = '#' + self.autoId;
                        $(query).removeClass('frmnewsfeedpin_pined_class');
                        if (self.isPinnedPage) {
                            $(query).hide();
                            window.hasPinned = false;
                            for (var index in window.ow_frmnewsfeedpin_feed_list) {
                                window.hasPinned = window.hasPinned || ow_frmnewsfeedpin_feed_list[index].isPinned;
                            }
                            if (!window.hasPinned) {
                                $('.ow_nocontent').show();
                                $('.owm_nocontent').show();
                            }
                        }
                        self.$pinBtn.removeClass('frmnewsfeedpin_remove_pin');
                        $(query).removeClass('frmnewsfeedpin_pined_class');
                        self.$pinBtn.addClass('frmnewsfeedpin_add_pin');
                        self.$pinBtn.html(data['button_value']);
                        self.$pinBtn.unbind("click");
                        self.$pinBtn.click({self: self}, self.pin);
                        $(query).find('.frmnewsfeedpin_pined_icon').remove();
                    }
                }
            };
            var jc = $.confirm($(this).data("confirm-msg"));

            jc.buttons.ok.action = function () {
                $.ajax(ajax_settings);
            }
        },
        pin: function (event) {
            var self = event.data.self;
            var ajax_settings = {
                url: self.$addPinAjaxURL,
                type: 'POST',
                data: {entityId: self.$entityId, entityType: self.$entityType},
                dataType: 'json',
                success: function (data) {
                    if (data['error']) {
                        OW.error(data['msg']);
                    } else {
                        OW.info(data['msg']);
                        $('#' + self.autoId).addClass('last_activity_description');
                        $('#' + self.autoId).addClass('frmnewsfeedpin_pined_class');
                        self.$pinBtn.removeClass('frmnewsfeedpin_add_pin');
                        self.$pinBtn.addClass('frmnewsfeedpin_remove_pin');
                        self.$pinBtn.html(data['button_value']);
                        self.$pinBtn.unbind("click");
                        self.$pinBtn.click({self: self}, self.remove);
                        self.$isPinned = true;
                        var  insertBefore = $('#' + self.autoId).find('.last_activity_description');
                        if(insertBefore.length > 0){
                            $('<div class="frmnewsfeedpin_pined_icon"></div>').insertBefore(insertBefore);
                        }else{
                            var insertAfter = $('#' + self.autoId).find('.ow_newsfeed_context_menu_wrap .ow_newsfeed_context_menu');
                            if(insertAfter.length > 0){
                                $('<div class="frmnewsfeedpin_pined_icon"></div>').insertAfter(insertAfter);
                            }
                        }
                    }
                }
            };
            var jc = $.confirm($(this).data("confirm-msg"));
            jc.buttons.ok.action = function () {
                $.ajax(ajax_settings);
            }
        }
    };
window.ow_frmnewsfeedpin_feed_list = [];

var FRMNEWSFEEDPIN_PinButton = function () {
    window.pinned = false;
    var pinId = '#FRMNEWSFEEDPIN_Pin';
    $(pinId).click(
        function () {
            if (window.pinned === false) {
                $(pinId).removeClass("frmnewsfeedpin_pin");
                $(pinId).addClass("frmnewsfeedpin_unpin");
                window.pinned = true;
                $("#pin").val(true);
            } else {
                clear_pin();
            }
        }
    );
    var clear_pin = function () {
        $(pinId).removeClass("frmnewsfeedpin_unpin");
        $(pinId).addClass("frmnewsfeedpin_pin");
        window.pinned = false;
        $("#pin").val(false);
    };
    $('form[name = "newsfeed_update_status"]').submit(
        function (e) {
            clear_pin();
        }
    );
}