<?php
/**
 * Cover Photo Widget
 */

/**
 * Cover Photo widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class COVERPHOTO_CMP_UserCoverPhotoWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $userId = (int) $params->additionalParamList['entityId'];
        $user_cover =  COVERPHOTO_BOL_Service::getInstance()->getUserCover($userId);

        $script = "$('#cover_edit_for_select_float').click(function(){
                    coverAjaxFloatBox = OW.ajaxFloatBox('COVERPHOTO_CMP_FormsFloatBox', {reload: false} , {iconClass: 'ow_ic_add', title: '".OW::getLanguage()->text('coverphoto', 'forms_page_title')."'});
                });";

        OW::getDocument()->addOnloadScript($script);
        $this->assign("editCoverIcon", OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'edit.png');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('coverphoto')->getStaticCssUrl() . 'coverphoto.css');
        $this->assign("owner", ($userId==OW::getUser()->getId())?true:false);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('coverphoto')->getStaticJsUrl() . 'jquery.form.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('coverphoto')->getStaticJsUrl() . 'reposition.js');
        $script = "
                    $(function () {
                        $('.cover-resize-wrapper').height(Math.min($('.cover-wrapper').height(),320));

                        $('form.cover-position-form').ajaxForm({
                            url: '".OW::getRouter()->urlForRoute('coverphoto-forms-cover-crop')."',
                            dataType: 'json',
                            beforeSend: function () {
                                $('.cover-progress').html('". OW::getLanguage()->text("coverphoto", "repositioning_label") ."').fadeIn('fast').removeClass('hidden');
                            },

                            success: function (responseText) {
                                if ((responseText.status) == 200) {
                                    $('.drag-div').hide();
                                    $('.default-buttons').show();
                                    $('.cover-resize-buttons').hide();
                                    $('.cover-progress').fadeOut('fast').addClass('hidden').html('');
                                    enabled = false;
                                    location.reload();
                                }
                            }
                        });
                    });
		        ";
        OW::getDocument()->addOnloadScript($script);


        if(isset($user_cover)) {
            if($userId==OW::getUser()->getId()) {

            }
            $this->assign("user_original_cover_path", OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover->hash));
            $this->assign("user_cropped_cover_path", OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('coverphoto')->getUserFilesDir() . $user_cover->croppedHash));
        }else{
            $this->assign("empty_original_cover_path",OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'empty_original_cover.jpg');
            $this->assign("empty_cropped_cover_path",OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'empty_cropped_cover.jpg');
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('coverphoto', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_PICTURE
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}