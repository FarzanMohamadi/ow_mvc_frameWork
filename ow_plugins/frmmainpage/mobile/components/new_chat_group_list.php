<?php
/**
 * frmmainpage
 */
class FRMMAINPAGE_MCMP_NewChatGroupList extends OW_MobileComponent
{
    protected $showOnline = true, $list = array();
    protected $listKey;

    public function __construct($listKey,$list, $showOnline)
    {
        parent::__construct();

        $this->list = $list;
        $this->showOnline = $showOnline;

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmmainpage')->getMobileCmpViewDir().'new_chat_group_list.html');
    }


    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->process($this->list, $this->showOnline);
    }

    public function getContextMenu()
    {
        return null;
    }

    protected function process( $idList, $showOnline )
    {
        $userService = BOL_UserService::getInstance();

        if ( empty($idList) )
        {
            $idList = array();
        }

        $userList = array();

        $dtoList = BOL_UserService::getInstance()->findUserListByIdList($idList);
        $tmpUserList = array();
        foreach ( $dtoList as $dto )
        {
            $tmpUserList[$dto->id] = array('dto' => $dto,
                'chatUrl' => $this->getChatUrl($dto->id));
        }

        foreach ( $idList as $id )
        {
            $userList[$id] = $tmpUserList[$id];
        }

        $avatars = array();
        $usernameList = array();
        $displayNameList = array();
        $onlineInfo = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);

            foreach ( $avatars as $userId => $avatarData )
            {
                $displayNameList[$userId] = isset($avatarData['title']) ? $avatarData['title'] : '';
            }
            $usernameList = $userService->getUserNamesForList($idList);
            if ( $showOnline )
            {
                $onlineInfo = $userService->findOnlineStatusForUserList($idList);
            }

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
                if ( isset($permissions[$userId]['blocked']) && $permissions[$userId]['blocked'] == true )
                    $onlineInfo[$userId] = false;
            }
        }

        $this->assign('onlineInfo', $onlineInfo);
        $this->assign('usernameList', $usernameList);
        $this->assign('avatars', $avatars);
        $this->assign('displayNameList', $displayNameList);
        $this->assign('friendList', $userList);
    }
    public function getChatUrl($userId)
    {
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)) {
            return null;
        }
        $chatUrl = "";
        $activeModes = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();

        if (in_array('chat', $activeModes))
        {
            $allowChat = OW::getEventManager()->call('base.online_now_click', array('userId' => OW::getUser()->getId(), 'onlineUserId' => $userId));
            if ($allowChat) {
                $chatUrl = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId' => $userId));
            }
        }
        return $chatUrl;
    }
}