<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
abstract class BASE_CMP_Users extends OW_Component
{
    protected $showOnline = true, $list = array();

    public function getContextMenu($userId, $additionalInfo = array())
    {
        return null;
    }

    abstract public function getFields( $userIdList );

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline = true )
    {
        parent::__construct();

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'users.html');

        $this->list = $list;
        $this->showOnline = $showOnline;
        $friendshipStatusEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::USER_LIST_FRIENDSHIP_STATUS, array('list' => $list,'desktopVersion'=>true)));
        if(isset($friendshipStatusEvent->getData()['friendList'])){
            $this->assign('friendList', $friendshipStatusEvent->getData()['friendList']);
        }
        if(isset($friendshipStatusEvent->getData()['answerValues']) && sizeof($friendshipStatusEvent->getData()['answerValues'])>0){
            $this->assign('answerValues', $friendshipStatusEvent->getData()['answerValues']);
            $this->assign('questionNameList', $friendshipStatusEvent->getData()['questionNameList']);
            $this->assign('questionNameValues', $friendshipStatusEvent->getData()['questionNameValues']);
        }
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $isMobileVersion = false;
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion']==true) {
            $isMobileVersion = true;
        }
        $this->assign('isMobileVersion', $isMobileVersion);

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $usersOnPage), 5));
    }

    protected function process( $list, $showOnline )
    {
        $service = BOL_UserService::getInstance();

        $idList = array();
        $userList = array();

        foreach ( $list as $dto )
        {
            $userList[] = array('dto' => $dto);
            $idList[] = $dto->getId();
        }

        $avatars = array();
        $usernameList = array();
        $displayNameList = array();
        $onlineInfo = array();
        $questionList = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
            $usernameList = $service->getUserNamesForList($idList);

            foreach ( $avatars as $userId => $avatarData )
            {
                $displayNameList[$userId] = isset($avatarData['title']) ? $avatarData['title'] : '';
                if($displayNameList[$userId] == '') {
                    $displayNameList[$userId] = $usernameList[$userId];
                }
            }

            if ( $showOnline )
            {
                $onlineInfo = $service->findOnlineStatusForUserList($idList);
            }
        }
        $checkOfflineChatEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ENABLE_DESKTOP_OFFLINE_CHAT, array('enOfflineChat' => true)));
        $usersOnline = $onlineInfo;
        if(isset($checkOfflineChatEvent->getData()['setOfflineChat']) && $checkOfflineChatEvent->getData()['setOfflineChat']==true){
            foreach($idList as $id){
                $onlineInfo[$id]="1";
            }
        }
        $showPresenceList = array();

        $ownerIdList = array();

        foreach ( $onlineInfo as $userId => $isOnline )
        {
            $ownerIdList[$userId] = $userId;
        }

        $eventParams = array(
                'action' => 'base_view_my_presence_on_site',
                'ownerIdList' => $ownerIdList,
                'viewerId' => OW::getUser()->getId()
            );

        $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

        foreach ( $onlineInfo as $userId => $isOnline )
        {
            // Check privacy permissions
            if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
            {
                $showPresenceList[$userId] = false;
                continue;
            }

            $showPresenceList[$userId] = true;
        }

        $additionalInfo = array();
        $additionalInfo['cache'] = array();
        $contextMenuList = array();
        if (isset($this->groupDto) && isset($this->groupDto->id)) {
            $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$this->groupDto->id, 'all_manager_ids' => true));
            OW::getEventManager()->trigger($eventIisGroupsPlusManager);
            if(isset($eventIisGroupsPlusManager->getData()['managerIds'])){
                $additionalInfo['cache']['groups_managers'][$this->groupDto->id] = $eventIisGroupsPlusManager->getData()['managerIds'];
            }
        }
        foreach ( $idList as $uid )
        {
            $contextMenu = $this->getContextMenu($uid, $additionalInfo);
            if ( $contextMenu )
            {
                $contextMenuList[$uid] = $contextMenu->render();
            }
            else
            {
                $contextMenuList[$uid] = null;
            }
        }

        $fields = array();


        $blockedUsers = BOL_UserService::getInstance()->findBlockedListByUserIdList(OW::getUser()->getId(), $idList);
        $blockedByUsers = BOL_UserService::getInstance()->findBlockedByListByUserIdList(OW::getUser()->getId(), $idList);
        if (FRMSecurityProvider::checkPluginActive('privacy', true)) {
            // Don't remove this
            $inviteToChatUser = PRIVACY_BOL_ActionService::getInstance()->getActionValueListByUserIdList(array('mailbox_invite_to_chat'), $idList);
        }

        $this->assign('contextMenuList', $contextMenuList);

        $this->assign('fields', $this->getFields($idList));
        $this->assign('questionList', $questionList);
        $this->assign('usernameList', $usernameList);
        $this->assign('avatars', $avatars);
        $this->assign('displayNameList', $displayNameList);
        $this->assign('onlineInfo', $onlineInfo);
        $this->assign('usersOnline', $usersOnline);
        $this->assign('showPresenceList', $showPresenceList);
        $this->assign('blockedUsers', $blockedUsers);
        $this->assign('blockedByUsers', $blockedByUsers);
        $this->assign('list', $userList);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->process($this->list, $this->showOnline);
    }
}