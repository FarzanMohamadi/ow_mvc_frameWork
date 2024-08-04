var frmsearch_items_selector = '#div_result_search_items';
var frmsearch_last_q = '';
var last_selected_section = '';
function frmsearch_doSearch(url, id) {
    var searchValue = document.getElementById(id).value;
    var selected_section = $('#choose_plugin option:selected').val();
    if(frmsearch_last_q===searchValue && last_selected_section == selected_section)
        return;
    frmsearch_last_q = searchValue;
    last_selected_section = selected_section;
    if (searchValue.length > 1) {
        frmsearch_loadingForResults();
        setTimeout(function () {
                var searchValue2 = document.getElementById(id).value;
                if (searchValue !== searchValue2)
                    return;
                var data = {"searchValue": searchValue, "selected_section": selected_section};
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: "json",
                    data: data,
                    success: function (results) {
                        var searchedValue = results['searchedValue'];
                        if (searchedValue.trim() === document.getElementById(id).value.trim()) {
                            $(frmsearch_items_selector).fadeOut(400, function () {
                                $(frmsearch_items_selector).empty();
                                var all_count = 0;
                                $.each(results.data, function (index, value) {
                                    //console.log(index);
                                    for (var i = 0; i < value.length; i++) {
                                        var resultItem = '<div id="search_item_' + all_count + '" class="result_search_item result_search_item_' + index + '">' +
                                            '<a class="avatar" href="' + value[i]['link'] + '">';
                                        if (value[i]['imageInfo'] && value[i]['imageInfo']['empty']){
                                            resultItem = resultItem + '<span class="advanced_search_empty_image_container"' +
                                                ' style="background-image: url(' + value[i]['image'] + '); background-color: ' + value[i]['imageInfo']['color'] + '" ></span>';
                                        }
                                        else if (typeof (value[i]['image']) !== "undefined") {
                                            resultItem = resultItem + '<img src="' + value[i]['image'] + '" />';
                                        }
                                        resultItem = resultItem + '</a>' +
                                            '<a class="title" href="' + value[i]['link'] + '">' + value[i]['title'] + '</a>' +
                                            '<div class="groupName">(' + value[i]['label'] + ')</div>';
                                        if (value[i]['displayName'] != undefined)
                                            resultItem = resultItem + '<a class="label" href="' + value[i]['userUrl'] + '">' + value[i]['displayName'] + '</a></div>';
                                        else
                                            resultItem = resultItem + '</div>';
                                        $(resultItem).appendTo($(frmsearch_items_selector));
                                        all_count++;
                                    }
                                });
                                if (all_count == 0) {
                                    var resultItem = '<div id="description_not_found" class="result_search_description result_search_item">' + OW.getLanguageText('frmadvancesearch', 'no_data_found') + '</div>';
                                    $(resultItem).appendTo($(frmsearch_items_selector));
                                }
                                $(frmsearch_items_selector).fadeIn(400);
                            });
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        $(frmsearch_items_selector).empty();
                        var resultItem = '<div id="description_error" class="result_search_description result_search_item">ERROR: ' + xhr.responseText + '</div>';
                        $(resultItem).appendTo($(frmsearch_items_selector));
                        $(frmsearch_items_selector).fadeIn(400);
                    }
                });
            }
            , 1000);
    } else {
        $(frmsearch_items_selector).fadeOut(10, function () {
            $(frmsearch_items_selector).empty();
            $(frmsearch_items_selector).fadeIn(10);

            resultItem = '<div id="description_minimum_two_char" class="result_search_description result_search_item">' + OW.getLanguageText('frmadvancesearch', 'minimum_two_character') + '</div>';
            $(resultItem).appendTo($(frmsearch_items_selector));
        });
    }
}

function frmsearch_createSearchElements() {
    OW.ajaxFloatBox('FRMADVANCESEARCH_CMP_Search', {}, {width: 700, iconClass: 'ow_ic_add',  title: OW.getLanguageText('frmadvancesearch', 'search_title')});
}

function frmsearch_loadingForResults() {
    if ($('#div_result_search_spinner').length == 0) {
        $(frmsearch_items_selector).empty();
        $('<div>').attr({
            class: 'spinner',
            id: 'div_result_search_spinner'
        }).append('<div class="double-bounce1"></div><div class="double-bounce2"></div>').prependTo($(frmsearch_items_selector));
    }
}