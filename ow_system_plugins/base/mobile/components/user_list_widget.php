<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_UserListWidget extends BASE_CMP_UserListWidget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct( $params );
        
        if ( $this->getComponent('menu') )
        {
            $params->standartParamList->capContent = $this->getComponent('menu')->render();
        }
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'user_list_widget.html');
    }

    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        return parent::getData($params);
    }

    protected function getUsersCmp( $list )
    {
        return new BASE_MCMP_AvatarUserList($list);
    }

    protected function getMenuCmp( $menuItems )
    {
        return new BASE_MCMP_WidgetMenu($menuItems);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'user_list_widget_settings_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}