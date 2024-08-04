<?php
 /**
 * @package ow_plugins.friends.components
 * @since 1.0
 */
class FRIENDS_CMP_UserWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        
        $service = FRIENDS_BOL_Service::getInstance();
        $userId = $params->additionalParamList['entityId'];
        $count = (int) $params->customParamList['count'];

        $idList = $service->findUserFriendsInList($userId, 0, $count);
        $total = $service->countFriends($userId);

        $eventParams =  array(
            'action' => 'friends_view',
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {
            $this->setVisible(false);
            return;
        }
        
        if ( empty($idList) && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        if( !empty($idList) )
        {
            $this->addComponent('userList', new BASE_CMP_AvatarUserList($idList));
        }

        $username = BOL_UserService::getInstance()->getUserName($userId);

        $toolbar = array();
        
        $toolbar[] = array('label' => OW::getLanguage()->text('friends', 'total_friends', array('total' => $total)));
        if ($userId != OW::getUser()->getId()) {
            if ( $total > $count ) {
                $toolbar[] = array('label' => OW::getLanguage()->text('base', 'view_all').' ('.$total.')', 'href' => OW::getRouter()->urlForRoute('friends_user_friends', array('user' => $username)));
            }
        } else {
            $toolbar[] = array('label' => OW::getLanguage()->text('base', 'view_all').' ('.$total.')', 'href' => OW::getRouter()->urlForRoute('friends_list'));
        }

        if ($userId == OW::getUser()->getId()) {
            $requestSent = $service->findFriendIdList($userId, 0, 1, 'got-requests');
            if ($requestSent != null && sizeof($requestSent) > 0) {
                $toolbar[] = array('label' => OW::getLanguage()->text('friends', 'request_title'), 'href' => OW::getRouter()->urlForRoute('friends_lists', array('list' => 'got-requests')));
            }
        }

        $this->assign('toolbar', $toolbar);
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('friends', 'user_widget_settings_count'),
            'value' => '9'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('friends', 'user_widget_title'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}