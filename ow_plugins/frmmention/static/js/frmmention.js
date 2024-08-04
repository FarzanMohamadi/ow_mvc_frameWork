/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmention
 * @since 1.0
 */

var frmmentionListCache = {};
function frmmention_wait_for_at() {
    var text_input_selector = '.ow_newsfeed_status_input, .comments_fake_autoclick';
    setInterval(function() {

        $(text_input_selector).each(function() {
            if($(this).attr('mention-loaded')!=='y'){
                $(this).attr('mention-loaded', 'y');

                var settings_data = function (q) {
                    if (q.length < 3) return [];
                    if (q in frmmentionListCache)
                        return frmmentionListCache[q];
                    var urlParam = "";
                    if(typeof groupId !== 'undefined') {
                        urlParam = "?groupId=" + groupId
                    }
                    var ret = $.getJSON(mentionLoadUsernamesUrl + q + urlParam);
                    frmmentionListCache[q] = ret;
                    return ret;
                };
                var settings_map = function (user) {
                    return {
                        value: user.username,
                        text: '<strong>' + user.username + '</strong> <small>' + user.fullname + '</small>'
                    }
                };

                $(text_input_selector).suggest_mention('@', {data: settings_data,map: settings_map,position: "bottom"});
            }
        });

    }, 1000);
}

$(function() {
    frmmention_wait_for_at();
});