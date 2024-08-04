var frmmenu_body_click_set = false;
function loadFRMMenu(){
    var select_one = false;
    var select_class = 'nav-is-visible';
    var target_class = '.cd-stretchy-nav';

    if( $('.cd-stretchy-nav').length > 0 ) {
        var stretchyNavs = $(target_class);

        stretchyNavs.each(function(){
            var stretchyNav = $(this),
                stretchyNavTrigger = stretchyNav.find('.cd-nav-trigger');
            stretchyNavTrigger.unbind('click');
            stretchyNavTrigger.on('click', function(event){
                if(select_one && stretchyNav.hasClass(select_class)) {
                    stretchyNavs.removeClass(select_class);
                    select_one = false;
                    return;
                }
                $('.cd-stretchy-nav.edit-content').removeClass(select_class);

                select_one = true;
                event.preventDefault();

                stretchyNav.toggleClass(select_class);
                var openDialog = $('.cd-stretchy-nav.edit-content.nav-is-visible');
                if(typeof OWM === "undefined"){
                    $(openDialog.parent().parent().parent().parent()).css('z-index', '8');
                    $(openDialog.parent().parent().parent().parent().parent()).css('z-index', '8');
                }
            });
        });

        if(frmmenu_body_click_set) {
            return;
        }
        frmmenu_body_click_set = true;
        $(document).on('click', function(event){
            if( $(event.target).is('.cd-nav-trigger') || $(event.target).is('.cd-nav-trigger span') ){
                return;
            }
            if( $('.cd-stretchy-nav.edit-content.nav-is-visible').length == 0 ) {
                var openDialog = $('.cd-stretchy-nav.edit-content');
                $(openDialog.parent().parent().parent().parent()).css('z-index', '');
                $(openDialog.parent().parent().parent().parent().parent()).css('z-index', '');
            }
            $(target_class).removeClass(select_class);
            select_one = false;
        });
    }
}