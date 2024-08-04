function showAjaxFloatBoxForChangePrivacy($id, $change_privacy_label, $actionType, $feedId) {
    privacyChangeFloatBox = OW.ajaxFloatBox('FRMSECURITYESSENTIALS_CMP_PrivacyFloatBox', {
        objectId: $id,
        actionType: $actionType,
        feedId: $feedId
    }, {
        iconClass: 'ow_ic_add', title: $change_privacy_label
    });
}

function privacyChangeComplete($cmp, $id, $src, $title, $privacy, $privacy_list){
    var object = jQuery($id);
    var child = object.children();
    var image = child;
    if(child.children().length ==1){
        image = child.children();
        image[0].style["background-image"] = "url("+$src+")";
        $privacy_list.forEach(function (item, index) {
            image[0].classList.remove(item);
        });
        image[0].classList.add($privacy);
    }else if(child[0].tagName == 'DIV'){
        image[0].style["background-image"] = "url("+$src+")";
        $privacy_list.forEach(function (item, index) {
            image[0].classList.remove(item);
        });
        image[0].classList.add($privacy);
    }else{
        $src = $src.slice(0,$src.lastIndexOf('.')) + ".png";
        image.attr("src",$src);
    }
    image[0].title = $title;
    image.removeData('owTip');
    $cmp.close();
}
setInterval(function(){ var toolTips = $('div[class="ow_tip ow_tip_top"]'); for(i=0; i< toolTips.length;i++){toolTips[i].style.display = 'none';} }, 10000);
function add_warning_alert(options) {
    $('body').prepend(`
      <label for='notify-2'>
        <input id='notify-2' type='checkbox'>
        <i class='fa fa-long-arrow-down'></i>
        <div id='notification-bar'>
            <div class='container'>
            <p>` + options.text + `</p>
            <p class='btn-action' id='warningIsSeen'>` + options.btn + `</p>
            </div>
        </div>
        </label> `);


    $(document).on('click', '#warningIsSeen', function() {
        var expires = '';
        $('#notification-bar').addClass('isChecked');
        document.cookie = 'isWarningAlert' + '=' + options.timeStamp + expires + '; path=/';
    });

}