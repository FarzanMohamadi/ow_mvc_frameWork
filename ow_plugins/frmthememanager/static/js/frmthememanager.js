/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */

function themeActionController( $themeKey, $token, $action, $controller) {
    if ($action === 'click' )
    {
        $('html body .ow_admin_choose_theme .selected_theme_info').css('display','none');
        $('.selected_theme_info.frm_theme_manager_themes').css('display','block');
        $('.themes_select .theme_item').removeClass('theme_clicked');
        $(this).parent().addClass('theme_clicked');
    }
    var data = {'themeKey': $themeKey , 'action': $action, 'token': $token};
    $.ajax({
        type: 'POST',
        url: $controller ,
        data: data,
        dataType: 'json',
        success: function(respondArray)
        {
            if(respondArray.edit){
                window.location = respondArray.editUrl;
            }else if(respondArray.click){
                viewThemeInfo(respondArray)
                loadInProgress( false );
            }else if(respondArray.export){
                window.location = respondArray.downloadUrl;
                loadInProgress( false );
            }else{
                OW.info(respondArray.message);
                window.location = window.location;
            }
        }
    });
    loadInProgress( true ,'.frmthememanager_page');
    if($action === 'click'){
        loadInProgress( true ,'.frm_theme_manager_themes');
    }

}

function parentThemeSelectAjax($this, $controller) {
    var data = {'themeKey': $this[0].value , 'action': 'themeColorAjax'};
    $.ajax({
        type: 'POST',
        url: $controller ,
        data: data,
        dataType: 'json',
        success: function(respondArray)
        {
            if(respondArray.colors){
                let indexes = Object.keys(respondArray.colors);
                for (i=0;i<indexes.length;i++){
                    $('input[name="'+indexes[i]+'"]')[0].value = respondArray.colors[indexes[i]];
                }
                loadInProgress( false );
            }else{
                OW.error(respondArray.message);
                loadInProgress( false );
            }
        }
    });
    loadInProgress( true );
}

function colorPickerConfigChange( $status, $controller ) {
    var data = { 'action': 'colorPicker', 'status': $status };
    $.ajax({
        type: 'POST',
        url: $controller ,
        data: data,
        dataType: 'json',
        success: function(respondArray)
        {
            if(respondArray.success){
                OW.info(respondArray.message);
                loadInProgress( false );
            }else{
                OW.error(respondArray.message);
                loadInProgress( false );
            }
        }
    });
    loadInProgress( true ,'.frmthememanager_page');

}

function loadInProgress( $status, $place = '.frmthememanager_page table.ow_table_1.ow_form tbody' ) {
    if ($status){
        $('.iithememanager_overlay').css('display','block');
        $('.iithememanager_overlay').css('height',   $($place).css('height') );
        $('.iithememanager_overlay').css('width',   $($place).css('width') );
        $('.iithememanager_overlay').css('margin-bottom',  - parseInt($($place).css('height')) );
    }else{
        $('.iithememanager_overlay').css('display','none');
    }
}

function removeFile($fileId, $undoRemove = false ) {
    let List = $('#file_remove_list').val();
    if ( $('#file_remove_list').val().length < 1 ) {
        List = $fileId;
    }else{
        List = JSON.parse(List);
        if(!Array.isArray(List)){
            List = Array(List);
        }
        if (!List.includes($fileId)){
            List.push($fileId);
        }
    }
    $('#'+$fileId).css('display','none');
    if (List.includes($fileId) && $undoRemove){
        List.pop($fileId);
        $('a#'+$fileId).css('display','inline-block');
    }
    $('#file_remove_list').val(JSON.stringify(List));
    $('.theme_file_preview.'+$fileId).css('display','none');
}

function viewThemeInfo( $respondArray ){
    $('.themeIconViewer').css('background-image', 'url(' + $respondArray.pluginUrl + $respondArray.clickData.urls.mainLogo + ')');
    $('.themeName .theme_info_value').text($respondArray.clickData.themeName);
    $('.themeKey .theme_info_value').text($respondArray.clickData.themeKey);
    $('.parentTheme .theme_info_value').text($respondArray.clickData.parentTheme);
    $.each( $respondArray.clickData.themeColors, function( key, value ) {
        $('#'+key).css('background-color',value);
    });
    $('.theme_info_value .edit').attr("onclick","themeActionController('"+$respondArray.clickData.themeKey+"','"+$respondArray.clickData.csrf_token+"','edit','"+$respondArray.themeActionController+"')");
    $('.theme_info_value .remove').attr("onclick","var result =$.confirm('"+OW.getLanguageText('frmthememanager', 'delete_theme_confirm')+"');result.buttons.ok.action = function () { themeActionController('"+$respondArray.clickData.themeKey+"','"+$respondArray.clickData.csrf_token+"','remove','"+$respondArray.themeActionController+"')}");
    $('.theme_info_value .export').attr("onclick","themeActionController('"+$respondArray.clickData.themeKey+"','"+$respondArray.clickData.csrf_token+"','export','"+$respondArray.themeActionController+"')");
    $('.theme_info_value .activate').attr("onclick","themeActionController('"+$respondArray.clickData.themeKey+"','"+$respondArray.clickData.csrf_token+"','activate','"+$respondArray.themeActionController+"')");
    $('.theme_info_value .update_all_themes').attr("onclick","themeActionController('"+$respondArray.clickData.themeKey+"','"+$respondArray.clickData.csrf_token+"','updateAllThemesList','"+$respondArray.themeActionController+"')");
    $('.theme_info_value .deactivateAll').attr("onclick","themeActionController('"+$respondArray.clickData.themeKey+"','"+$respondArray.clickData.csrf_token+"','deactivateAll','"+$respondArray.themeActionController+"')");
    if($respondArray.clickData.themeKey === $respondArray.activeTheme ){
        $('.theme_info_value .activate').css('display','none');
        $('.theme_info_value .frmthememanager_active_theme').css('display','inline-block');
        $('.theme_info_value .deactivateAll').css('display','inline-block');
    }else{
        $('.theme_info_value .activate').css('display','inline-block');
        $('.theme_info_value .deactivateAll').css('display','none');
        $('.theme_info_value .frmthememanager_active_theme').css('display','none');
    }
    if($respondArray.debugMode === true){
        $('.theme_info_value .update_all_themes').css('display','inline-block');
    }else{
        $('.theme_info_value .update_all_themes').css('display','none');
    }
}
