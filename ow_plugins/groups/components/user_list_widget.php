<?php
/**
 * Group user list widget
 *
 * @package ow_plugins.groups.components
 * @since 1.0
 */
class GROUPS_CMP_UserListWidget extends BASE_CLASS_Widget
{

    /**
     * GROUPS_CMP_UserListWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     * @throws Redirect404Exception
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    /***
     * @param $params
     * @return bool
     * @throws Redirect404Exception
     */
    private function assignList($params)
    {
        $groupId = $params->additionalParamList['entityId'];
        $count = ( empty($params->customParamList['count']) ) ? 9 : (int) $params->customParamList['count'];
        $list = GROUPS_BOL_Service::getInstance()->findUserList($groupId, 0, $count);

        $idlist = array();
        foreach ( $list as $item )
        {
            $idlist[] = $item->id;
        }
        $userCount = GROUPS_BOL_Service::getInstance()->findUserListCount($groupId);
        $this->assign("userCount", $userCount);
        $data = array();

        if ( !empty($idlist) )
        {
            $data = BOL_AvatarService::getInstance()->getDataForUserAvatars($idlist);
        }

        $this->assign("userIdList", $idlist);
        $this->assign("data", $data);

        //invite users button
        $service = GROUPS_BOL_Service::getInstance();
        $userId = null;
        if (OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
        }

        $groupDto = null;
        if (isset($params->additionalParamList['group']) && $params->additionalParamList['group']->id == $groupId) {
            $groupDto = $params->additionalParamList['group'];
        }

        if ($groupDto == null) {
            $groupDto = $service->findGroupById($groupId);
        }

        $isCurrentUserManager = false;
        if (isset($params->additionalParamList['currentUserIsManager']) && isset($params->additionalParamList['group']) && $params->additionalParamList['group']->id == $groupId) {
            $isCurrentUserManager = $params->additionalParamList['currentUserIsManager'];
        } else {
            $managerIds = array();
            if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
                $groupManagerIds = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupIds(array($groupId));
                $managerIds = array();
                if (isset($groupManagerIds[$groupId])) {
                    $managerIds = $groupManagerIds[$groupId];
                }
            }
            $isCurrentUserManager = in_array(OW::getUser()->getId(), $managerIds);
        }

        $isMemberOfGroup = false;
        if (isset($params->additionalParamList['currentUserIsMemberOfGroup']) && isset($params->additionalParamList['group']) && $params->additionalParamList['group']->id == $groupId) {
            $isMemberOfGroup = $params->additionalParamList['currentUserIsMemberOfGroup'];
        } else {
            $isMemberOfGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        }

        $everyParticipantCanInvite = $groupDto->whoCanInvite == GROUPS_BOL_Service::WCI_PARTICIPANT;

        if ($isCurrentUserManager || ($everyParticipantCanInvite && $isMemberOfGroup) || $service->isCurrentUserInvite($groupId, false, false, $groupDto)){
            $idList = $service->getInvitableUserIds($groupId, $userId);

            $eventIisGroupsPlusCheckCanSearchAll = new OW_Event('frmgroupsplus.check.can.invite.all',array('checkAccess'=>true));
            OW::getEventManager()->trigger($eventIisGroupsPlusCheckCanSearchAll);
            if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['directInvite']==true){
                $title = OW::getLanguage()->text('frmgroupsplus', 'add_to_group_title');
            }
            else if(isset($eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess']) && $eventIisGroupsPlusCheckCanSearchAll->getData()['hasAccess']==true){
                $title = OW::getLanguage()->text('groups', 'invite_fb_title_all_users');
            }
            else{
                $title = OW::getLanguage()->text('groups', 'invite_fb_title');
            }

            $enableQRSearch = !(boolean)OW::getConfig()->getValue('groups','enable_QRSearch');
            $options = array(
                'groupId' => $groupId,
                'userList' => $idList,
                'floatBoxTitle' => $title,
                'inviteResponder' => OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'invite'),
                'defaultSearch' => $enableQRSearch
            );
            $js = UTIL_JsGenerator::newInstance()->callFunction('GROUPS_InitInviteButton', array($options));
            OW::getDocument()->addOnloadScript($js);

            $this->assign("inviteUser", true);
        }

        if (!empty($idlist)) {
            $this->assign("groupUsersAll", OW::getRouter()->urlForRoute('groups-user-list', array('groupId' => $groupId)));
        }
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('groups', 'widget_users_settings_count'),
            'value' => 9
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('groups', 'widget_users_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}