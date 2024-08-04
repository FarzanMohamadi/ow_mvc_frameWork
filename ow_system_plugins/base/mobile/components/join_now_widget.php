<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_JoinNowWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObject )
    {
        parent::__construct();

        $this->assign('url', OW::getRouter()->urlForRoute('base_join'));
        $this->assign('label', !empty($paramObject->customParamList['buttonLabel']) ? $paramObject->customParamList['buttonLabel'] : OW::getLanguage()->text('base', 'join_index_join_button'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir().'join_now_widget.html');
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'join_index_join_button'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => self::ICON_INFO
        );
    }

    public static function getSettingList()
    {
        $lang = OW::getLanguage();
        $settingList = array();
        
        $settingList['buttonLabel'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('base', 'join_index_join_button'),
            'value' => ''
        );
        
        return $settingList;
    }
    
    public static function getAccess()
    {
        return self::ACCESS_GUEST;
    }
}