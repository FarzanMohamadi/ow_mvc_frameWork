/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */
function frmadvancesearch_search_users(base_url,selector,count,load_empty) {
    var input_selector = selector+" input";
    var results_selector = selector+" .frmadvancesearch_results";
    var empty_selector = selector + " .ow_nocontent";

    var frmadvancesearch_last_text = '';
    var frmadvancesearch_ListCache = {};
    var frmas_more_available_list = {};
    var frmas_next_start = {};
    if(count == null)
        count = 12;
    if (load_empty === undefined) {
        load_empty = false;
    }

    $(selector).append('<div id="load-more"></div>');

    var get_items = function (q) {
        $(selector + ' #load-more').html('<a href="javascript://" id="notifications-load-more" class="owm_sidebar_load_more owm_sidebar_load_more_with_text owm_sidebar_load_more_preloader">'+OW.getLanguageText('base', 'more')+'</a>');
        $.ajax({
            url: base_url + q,
            data: {"start": frmas_next_start[q], "count": count},
            success: function (response) {
                var result = jQuery.parseJSON(response);
                if(result.is_appending)
                    frmadvancesearch_ListCache[q] = frmadvancesearch_ListCache[q].concat(result.items);
                else
                    frmadvancesearch_ListCache[q] = result.items;
                frmas_more_available_list[q] = result.more_available;

                if (result.q.trim() === $(input_selector)[0].value.trim()) {
                    $(selector + ' #load-more').html('');
                    frmas_next_start[q] = result.next_start;
                    frmadvancesearch_load_data(result.items, results_selector, empty_selector, result.is_appending);
                    if (frmas_more_available_list[q]) {
                        $(selector + ' #load-more').html('<a href="javascript://" id="notifications-load-more" class="owm_sidebar_load_more owm_sidebar_load_more_with_text ">'+OW.getLanguageText('base', 'more')+'</a>');
                        $(selector + ' #notifications-load-more').on('click', function () {
                            q = $(input_selector)[0].value;
                            get_items(q);
                        });
                    }
                }
            },
            'error': function () {
                //OW.error(OW.getLanguageText('base', 'comment_add_post_error'));
            }
        });
    };

    if(load_empty){
        frmas_next_start[''] = 0;
        get_items('');
    }

    $(input_selector).on('change keydown paste input',function () {
        q = $(input_selector)[0].value;
        if (frmadvancesearch_last_text == q)
            return;
        if (q == "" && !load_empty) {
            $(results_selector).html('');
            $(selector + ' #load-more').html('');
            $(empty_selector).css('display', 'none');
            return;
        }
        next_start = 0;
        frmadvancesearch_last_text = q;
        if (q in frmadvancesearch_ListCache) {
            frmadvancesearch_load_data(frmadvancesearch_ListCache[q], results_selector, empty_selector, false);
            $(selector + ' #load-more').html('');
            if (frmas_more_available_list[q]) {
                $(selector + ' #load-more').html('<a href="javascript://" id="notifications-load-more" class="owm_sidebar_load_more owm_sidebar_load_more_with_text ">'+OW.getLanguageText('base', 'more')+'</a>');
                $(selector + ' #notifications-load-more').on('click', function () {
                    var q2 = $(input_selector)[0].value;
                    get_items(q2);
                });
            }
        }
        else {
            $(results_selector).html('');
            frmas_next_start[q] = 0;
            get_items(q);
        }
    });
}
function frmadvancesearch_load_data(items, results_selector,empty_selector,is_appending) {
    if(!is_appending) {
        $(results_selector).html('');
        $(empty_selector).css('display', 'block');
    }
    $.each(items, function( index, value ) {
        var extraClass = value.imageInfo.empty ? 'colorful_avatar_' + value.imageInfo.digit : '';
        var html = '<div class="owm_avatar ' + extraClass + '"><a href="'+value.url+'">' +
            '<div class="owm_align_center"><img alt="" title="'+value.title+'+" src="'+value.src+'" /></div>' +
            '<div class="owm_align_center"><span class="owm_avatar_title">'+value.title+'</span></div>' +
            '</a></div>';
        $(results_selector).append(html);
        $(empty_selector).css('display','none');
    });
}