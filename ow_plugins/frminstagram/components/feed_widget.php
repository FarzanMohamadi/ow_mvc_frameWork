<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
class FRMINSTAGRAM_CMP_FeedWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $username = OW::getConfig()->getValue('frminstagram', 'default_page');;
        if ( isset($params->customParamList['username']) )
            $username = $params->customParamList['username'];
        $this->initializeWidgetData($username);
    }

    private function initializeWidgetData($username)
    {
        //--add ajax to load
        $jsDir = OW::getPluginManager()->getPlugin("frminstagram")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "frminstagram.js");
        $loadDataUrl = OW::getRouter()->urlForRoute('frminstagram.widget_load', array('username' => ''));
        $loadMoreUrl = OW::getRouter()->urlForRoute('frminstagram.widget_load_more', array('username' => ''));
        OW::getDocument()->addOnloadScript(';ig_loadWidgetData("'.$username.'","'.$loadDataUrl.'","'.$loadMoreUrl.'");');

        $cssFile = OW::getPluginManager()->getPlugin('frminstagram')->getStaticCssUrl() . 'frminstagram.css';
        OW::getDocument()->addStyleSheet($cssFile);
        $css = '.igw_comments { background: url("' . OW::getPluginManager()->getPlugin('frminstagram')->getStaticCssUrl() . 'chat-0.svg' . '") left center no-repeat; }';
        $css .= '.igw_item .igw_video div { background: url("' . OW::getPluginManager()->getPlugin('frminstagram')->getStaticCssUrl() . 'ic_video.svg' . '") right top no-repeat; }';
        OW::getDocument()->addStyleDeclaration($css);

        $this->assign('preloader_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'ajax_preloader_content.gif');
        $this->assign('new_items_img_url' , OW::getThemeManager()->getThemeImagesUrl() . 'photo_view_context.png');
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['username'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => 'username',
            'value' => OW::getConfig()->getValue('frminstagram', 'default_page')
        );
        return $settingList;
    }
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frminstagram', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

}