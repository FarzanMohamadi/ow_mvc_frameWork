/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */

var frmhashtagListCache = {};
function frmhashtag_wait_for_at() {
    var text_input_selector = '.ow_newsfeed_status_input, .comments_fake_autoclick';
    setInterval(function() {

        $(text_input_selector).each(function() {
            if($(this).attr('hashtag-loaded')!=='y'){
                $(this).attr('hashtag-loaded', 'y');

                var settings_data = function (q) {
                    if( q.length < 3) return [];
                    if (q in frmhashtagListCache)
                        return frmhashtagListCache[q];
                    var ret = $.getJSON(frmhashtagLoadTagsUrl + q);
                    frmhashtagListCache[q] = ret;
                    return ret;
                };
                var settings_map = function (item) {
                    return {
                        value: item.tag,
                        text: '<strong>' + item.tag + '</strong> (× <small>' + item.count + '</small>)'
                    }
                };

                $(text_input_selector).suggest_hashtag('#', {data: settings_data,map: settings_map,position: "bottom"});
            }
        });

    }, 1000);
}

$(function() {
    frmhashtag_wait_for_at();
});