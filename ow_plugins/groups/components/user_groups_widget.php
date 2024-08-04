<?php
/**
 * User Group List Widget
 *
 * @package ow_plugins.groups.components
 * @since 1.0
 */
class GROUPS_CMP_UserGroupsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        /*if ( !GROUPS_BOL_Service::getInstance()->isCurrentUserCanViewList() )
        {
            $this->setVisible(false);

            return;
        }*/

        $userId = $params->additionalParamList['entityId'];
        $count = ( empty($params->customParamList['count']) ) ? 4 : (int) $params->customParamList['count'];

        // privacy check
        $viewerId = OW::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $modPermissions = OW::getUser()->isAuthorized('groups');

        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS, 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new OW_Event('privacy_check_permission', $privacyParams);

            try {
                OW::getEventManager()->trigger($event);
            }
            catch ( RedirectException $e )
            {
                $this->setVisible(false);

                return;
            }
        }

        $userName = BOL_UserService::getInstance()->findUserById($userId)->getUsername();
        if ( !$this->assignList($userId, $count) )
        {
            $this->setVisible($params->customizeMode);

            return;
        }
        $total = GROUPS_BOL_Service::getInstance()->findUserGroupListCount($userId);
        $this->setSettingValue(self::SETTING_TOOLBAR, array(
            array(
            'label' => OW::getLanguage()->text('base', 'view_all_with_count', array('count' => $total)),
            'href' => OW::getRouter()->urlForRoute('groups-user-groups', array('user' => $userName))
        )));

    }

    private function assignList( $userId, $count )
    {
        $service = GROUPS_BOL_Service::getInstance();
        $list = $service->findUserGroupList($userId, 0, $count);

        $tplList = array();
        foreach ( $list as $item )
        {
            /* @var $item GROUPS_BOL_Group */
            $groupImageURL = $service->getGroupImageUrl($item);
            $tplList[] = array(
                'image' => $groupImageURL,
                'imageInfo'=> BOL_AvatarService::getInstance()->getAvatarInfo($item->id, $groupImageURL, 'group'),
                'title' => htmlspecialchars($item->title),
                'url' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $item->id))
            );
        }

        $this->assign("list", $tplList);

        return!empty($tplList);
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('groups', 'widget_user_groups_settings_count'),
            'value' => 4
        );

        return $settingList;
    }

    public static function processSettingList( $settingList, $place, $isAdmin )
    {
        $settingList['count'] = intval($settingList['count']);

        return parent::processSettingList($settingList, $place, $isAdmin);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW_Language::getInstance()->text('groups', 'widget_user_groups_title'),
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}