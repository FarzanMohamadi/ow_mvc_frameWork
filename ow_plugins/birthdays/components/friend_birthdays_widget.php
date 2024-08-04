<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BIRTHDAYS_CMP_FriendBirthdaysWidget extends BASE_CMP_UsersWidget
{
    public function getData( BASE_CLASS_WidgetParameter $params )
    {
        if( !OW::getUser()->isAuthenticated() || !OW::getEventManager()->call('plugin.friends') )
        {
            $this->setVisible(false);
            return array();
        }
        
        $count = (int)$params->customParamList['count'];

        $language = OW::getLanguage();
        $service = BIRTHDAYS_BOL_Service::getInstance();

        $friendsIdList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => OW::getUser()->getId()));
        $users = $service->findListByBirthdayPeriod(date('Y-m-d'), date('Y-m-d', strtotime('+7 day')), 0, $count, $friendsIdList, array('everybody','friends_only'));
        
        if ( (!$params->customizeMode && empty($users) ) )
        {
            $this->setVisible(false);
        }        

        return array(
            'birthdays_this_week' => array(
                'menu-label' => "",//$language->text('birthdays', 'user_list_menu_item_birthdays'),
                'userIds' => array( 'key' => 'birthdays_this_week', 'list' => $this->getIdList($users) ),
                'toolbar' => false, //TODO complete
                'menu_active' => true
            )
        );
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => 'Count',
            'value' => '9'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('birthdays', 'friends_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
    
    protected function getUsersCmp( $list )
    {
        $key = !empty($list['key']) ? $list['key'] : null;
        $idList = !empty($list['list']) ? $list['list'] : array();
        
        return new BIRTHDAYS_CMP_AvatarUserList($idList, $key);
    }
}