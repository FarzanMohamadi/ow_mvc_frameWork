function graphics_on_click(self, n){
    self.disabled = true;

    url = OW_URL_HOME + 'base/graphics-mode/';
    if (n==0){
        url += 'reset_static';
    }
    else if (n==1){
        url += 'reset_template_c';
    }
    else if (n==2){
        url += 'reset_translations';
    }

    $.ajax({
        url: url,
        type: 'post',
        dataType: "json",
        success: function (results) {
            if(results['reload']) {
                location.reload();
            }
        }
    });
}
$(function() {
    $('body').prepend('<div style="position: fixed;z-index: 100;bottom: 50%;left: -198px;-webkit-transform: rotate(-90deg);-moz-transform: rotate(-90deg);-o-transform: rotate(-90deg);-ms-transform: rotate(-90deg);transform: rotate(-90deg);">' +
        '<button onclick="graphics_on_click(this,0)" style="font-family: monospace;">Reset static</button>' +
        '<button onclick="graphics_on_click(this,1)" style="font-family: monospace;">Reset template_c</button>' +
        '<button onclick="graphics_on_click(this,2)" style="font-family: monospace;">Reset translations</button>' +
        '</div>')
});