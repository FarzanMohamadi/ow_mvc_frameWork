<?php
 /**
 * @package ow_plugins.friends.components
 * @since 1.0
 */
class FRIENDS_CMP_Widget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = FRIENDS_BOL_Service::getInstance();
        if ( empty($params->additionalParamList['entityId']) )
        {
            $userId = OW::getUser()->getId();
        }
        else
            $userId = $params->additionalParamList['entityId'];

        $this->assign('count', (int) FRIENDS_BOL_Service::getInstance()->countFriends($userId));
        $this->assign('gotCount', (int) $service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING));
        $this->assign('sentCount', (int) $service->count($userId, null, FRIENDS_BOL_Service::STATUS_PENDING, FRIENDS_BOL_Service::STATUS_IGNORED));

        $this->assign('friendsUrl', OW::getRouter()->urlForRoute('friends_list'));
        $this->assign('sentRequestsUrl', OW::getRouter()->urlForRoute('friends_lists', array('list' => 'sent-requests')));
        $this->assign('gotRequestsUrl', OW::getRouter()->urlForRoute('friends_lists', array('list' => 'got-requests')));
    }

    public static function getSettingList()
    {
        $settingList = array();

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('friends', 'widget_title'),
            self::SETTING_ICON => 'ow_ic_user',
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}