<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.admin.components
 * @since 1.7.5
 */
class ADMIN_CMP_UploadedFilesFloatbox extends OW_Component
{
    public function __construct( $layout )
    {
        parent::__construct();

        $saveImageDataUrl = OW::getRouter()->urlFor('ADMIN_CTRL_Theme', 'ajaxResponder');

        $jsString = ";$('.image_save_data').click(function(e){
            e.preventDefault();
            var floatbox = $('.floatbox_container');
            var title = $('.ow_photoview_title input', floatbox).val();
            var imageId = $('.ow_photoview_id', floatbox).val();
            var data = {'entityId': imageId, 'title': title, 'ajaxFunc': 'ajaxSaveImageData'};
            $('.image_save_data').attr('disabled', 'disabled');
            $('.image_save_data').addClass('ow_inprogress');
            $.ajax({
                url: '{$saveImageDataUrl}',
                data: data,
                method: 'POST',
                success: function(data){
                    $('.image_save_data').removeAttr('disabled');
                    $('.image_save_data').removeClass('ow_inprogress');
                    photoView.unsetCache(data.imageId);
                      OW.info(OW.getLanguageText('admin', 'permission_global_privacy_settings_success_message'));

                }
            });
        });
        ";
        OW::getDocument()->addOnloadScript($jsString);
        OW::getLanguage()->addKeyForJs('admin', 'permission_global_privacy_settings_success_message');
    }
}
