function showMorePost(postId) {
    var afterMoreElement = document.getElementById(postId);
    afterMoreElement.style.display = "block";
    var showMore = document.getElementById(postId+"showMore");
    showMore.style.display = "none";
}


$('.group_topics_filter a').click(function(e) {
    e.preventDefault();
    var a = $(this).attr('href');
    $('.group_topics_filter a.selected').removeClass('selected');
    if (a == '#all') {
        $('#group_id_all').addClass('selected');
    }
    else {
        $(a).addClass('selected');
        var b  = $(".forum_widget_container"+a.replace('#','.')).length-1;
        if( b === 0){
            $(".topic_label_group_container.forum_widget_container"+a.replace('#','.')).append('<p class="topic_label_group_container_no_content">' + OW.getLanguageText('base', 'empty_list') + '</p>')
        }
    }
    a = a.substr(1);
    $('#topic_list_widget .forum_widget_container').each(function() {
        if (!$(this).hasClass(a) && a != 'all')
            $(this).addClass('hide');
        else
            $(this).removeClass('hide');
    });
});