<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
abstract class BASE_MCMP_UserList extends OW_MobileComponent
{
    protected $showOnline = true, $list = array();

    public function __construct( $list, $showOnline = true )
    {
        parent::__construct();

        $this->list = $list;
        $this->showOnline = $showOnline;
        $friendshipStatusEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::USER_LIST_FRIENDSHIP_STATUS, array('list' => $list,'mobileVersion' =>true)));
        if(isset($friendshipStatusEvent->getData()['friendList'])){
            $this->assign('friendList', $friendshipStatusEvent->getData()['friendList']);
        }
        if(isset($friendshipStatusEvent->getData()['answerValues']) && sizeof($friendshipStatusEvent->getData()['answerValues'])>0){
            $this->assign('answerValues', $friendshipStatusEvent->getData()['answerValues']);
            $this->assign('qList', $friendshipStatusEvent->getData()['questionNameList']);
            $this->assign('questionNameValues', $friendshipStatusEvent->getData()['questionNameValues']);
        }
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir().'user_list.html');
    }
    
    abstract public function getFields( $userIdList );

    protected function process( $dtoList, $showOnline )
    {
        $service = BOL_UserService::getInstance();
        
        if ( empty($dtoList) )
        {
            $dtoList = array();
        }
        
        $userList = array();
        $idList = array();
        foreach ( $dtoList as $dto )
        {
            $userList[$dto->id] = array('dto' => $dto);
            $idList[] = $dto->id;
        }
        
        $avatars = array();
        $usernameList = array();
        $displayNameList = array();
        $onlineInfo = array();
        $questionList = array();

        if ( !empty($idList) )
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
            
            foreach ( $avatars as $userId => $avatarData )
            {
                $displayNameList[$userId] = isset($avatarData['title']) ? $avatarData['title'] : '';
                //$avatars[$userId]['label'] = mb_substr($avatars[$userId]['label'], 0, 1);
            }
            $usernameList = $service->getUserNamesForList($idList);

            if ( $showOnline )
            {
                $onlineInfo = $service->findOnlineStatusForUserList($idList);
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

        $contextMenuList = array();
        foreach ( $idList as $uid )
        {
            $contextMenu = $this->getContextMenu($uid);
            if ( $contextMenu )
            {
                $contextMenuList[$uid] = $contextMenu->render();
            }
            else
            {
                $contextMenuList[$uid] = null;
            }
        }

        $this->assign('contextMenuList', $contextMenuList);

        $this->assign('fields', $this->getFields($idList));
        $this->assign('questionList', $questionList);
        $this->assign('usernameList', $usernameList);
        $this->assign('avatars', $avatars);
        $this->assign('displayNameList', $displayNameList);
        $this->assign('onlineInfo', $onlineInfo);
        $this->assign('showPresenceList', $showPresenceList);
        $this->assign('list', $userList);
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
}