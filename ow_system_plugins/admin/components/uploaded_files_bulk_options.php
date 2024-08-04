<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.admin.components
 * @since 1.7.5
 */
class ADMIN_CMP_UploadedFilesBulkOptions extends OW_Component
{

    public function __construct()
    {
        parent::__construct();

    }

    private function assignUniqidVar($name)
    {
        $showId = FRMSecurityProvider::generateUniqueId($name);
        $this->assign($name, $showId);
        return $showId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $showId = $this->assignUniqidVar('showId');
        $deleteId = $this->assignUniqidVar('deleteId');
        $backId = $this->assignUniqidVar('backId');
        $containerId = $this->assignUniqidVar('containerId');

        OW::getDocument()->addOnloadScript("
            ;function exitBulkOptions(){
                $('#{$containerId}').fadeOut(function(){
                    $('#{$showId}').parent().parent().fadeIn();
                    $(this).parents('.ow_fw_menu').find('.ow_admin_date_filter').fadeIn();
                    $('.ow_photo_context_action').show();
                    $('.ow_photo_item .ow_photo_chekbox_area').hide();
                });
            }
            $('#{$deleteId}').click(function(){
                var deleteIds = [];

                $('.ow_photo_item.ow_photo_item_checked').each(function(){
                    deleteIds.push($(this).closest('.ow_photo_item_wrap').data('photoId'));
                });
                photoContextAction.deleteImages(deleteIds);
                exitBulkOptions();
            });
            $('#{$showId}').click(function(){
                $('.ow_photo_item.ow_photo_item_checked').toggleClass('ow_photo_item_checked');
                $(this).parents('.ow_fw_menu').find('.ow_admin_date_filter').fadeOut();
                $('#{$showId}').parent().parent().fadeOut(function(){
                    $('#{$containerId}').fadeIn();
                    $('.ow_photo_context_action').hide();
                    $('.ow_photo_item .ow_photo_chekbox_area').show();
                });
            });
            $('#{$backId}').click(function(){
                exitBulkOptions();
            });
            $('.ow_photo_list').on('click', '.ow_photo_checkbox, .ow_photo_chekbox_area', function(e){
                e.stopPropagation();
                $(this).parents('.ow_photo_item').toggleClass('ow_photo_item_checked');
            });"

        );
    }
}
