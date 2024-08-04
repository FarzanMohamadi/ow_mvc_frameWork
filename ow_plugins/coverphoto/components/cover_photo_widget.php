<?php
/**
 * General Cover Photo widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class COVERPHOTO_CMP_CoverPhotoWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $entityType = $params->additionalParamList['entity'];
        $entityId = (int) $params->additionalParamList['entityId'];

        $this->assign("user_original_cover_path", COVERPHOTO_BOL_Service::getInstance()->getCoverURL($entityType, $entityId, true));
        $this->assign("user_cropped_cover_path", COVERPHOTO_BOL_Service::getInstance()->getCoverURL($entityType, $entityId, false));

        $user_cover =  COVERPHOTO_BOL_Service::getInstance()->getSelectedCover($entityType, $entityId);
        if(isset($user_cover)) {
            $coverId = $user_cover->id;
            $this->assign('allow_reposition', true);
        }else{
            $coverId = 0;
            $this->assign('allow_reposition', false);
        }

        // scripts
        $script = "$('#cover_edit_for_select_float').click(function(){
                    coverAjaxFloatBox = OW.ajaxFloatBox('COVERPHOTO_CMP_FormsFloatBox', {reload: false, entityType: '$entityType', entityId: $entityId} , {iconClass: 'ow_ic_add', title: '".OW::getLanguage()->text('coverphoto', 'forms_page_title')."'});
                });";
        OW::getDocument()->addOnloadScript($script);

        $script = "
            $(function () {
                $('.cover-resize-wrapper').height(Math.min($('.cover-wrapper').height(),150));

                $('form.cover-position-form').ajaxForm({
                    url: '".OW::getRouter()->urlForRoute('coverphoto-forms-cover-crop', ['id'=>$coverId])."',
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

        $this->assign("editCoverIcon", OW::getPluginManager()->getPlugin('coverphoto')->getStaticUrl().'img/' . 'edit.png');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('coverphoto')->getStaticCssUrl() . 'coverphoto.css');
        $this->assign("owner", COVERPHOTO_BOL_Service::getInstance()->isOwner($entityType, $entityId));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('coverphoto')->getStaticJsUrl() . 'reposition.js');
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