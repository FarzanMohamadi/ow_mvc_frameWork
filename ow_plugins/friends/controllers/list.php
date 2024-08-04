<?php
/**
 * @package ow_plugins.friends.controllers
 * @since 1.0
 */
class FRIENDS_CTRL_List extends OW_ActionController
{
    protected $params;

    /**
     * Get list of friendships
     *
     * @param array $params
     */
    public function index( $params )
    {
        $this->setDocumentKey('ow_friends_list ');

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->params = $params;

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;

        $rpp = (int) OW::getConfig()->getValue('base', 'users_count_on_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $listType = $params['list'];

        if ( $listType == 'user-friends' )
        {
            $this->setPageHeading(OW::getLanguage()->text('friends', 'user_friends_page_heading', array('user' => $params['user'])));
            $this->setPageTitle(OW::getLanguage()->text('friends', 'user_friends_page_title', array('user' => $params['user'])));
        }
        else
        {
            $this->setPageHeading(OW::getLanguage()->text('friends', 'my_friends_page_heading'));
            $this->setPageTitle(OW::getLanguage()->text('friends', 'my_friends_page_title'));
            $this->addComponent('menu', $this->getMenu());
        }

        $this->setPageHeadingIconClass('ow_ic_user');

        $this->assign('case', $listType);

        list($list, $itemCount) = $this->getInfo($first, $count, $listType);

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $rpp), 5));

        $idList = array();

        $userList = array();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $cancelCodes = array();
            $acceptCodes = array();
            $ignoreCodes = array();
        }
        foreach ( $list as $dto )
        {
            $userList[] = array(
                'dto' => $dto
            );

            $idList[] = $dto->getId();

            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$dto->getId(),'isPermanent'=>true,'activityType'=>'cancel_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $cancelCodes[$dto->getId()] = $frmSecuritymanagerEvent->getData()['code'];
            }

            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$dto->getId(),'isPermanent'=>true,'activityType'=>'accept_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $acceptCodes[$dto->getId()] = $frmSecuritymanagerEvent->getData()['code'];
            }

            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$dto->getId(),'isPermanent'=>true,'activityType'=>'ignore_friends')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $ignoreCodes[$dto->getId()] = $frmSecuritymanagerEvent->getData()['code'];
            }
        }

        if(isset($cancelCodes) && sizeof($cancelCodes)>0){
            $this->assign('cancelCodes',$cancelCodes);
        }
        if(isset($acceptCodes) && sizeof($acceptCodes)>0){
            $this->assign('acceptCodes',$acceptCodes);
        }
        if(isset($ignoreCodes) && sizeof($ignoreCodes)>0){
            $this->assign('ignoreCodes',$ignoreCodes);
        }
        $questionList = array();
        $onlineInfo = array();
        $avatarArr = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        if ( !empty($idList) )
        {
            $avatarArr = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList);
            $questionList = BOL_QuestionService::getInstance()->getQuestionData($idList, $qs);

            if ( $listType != 'online' )
            {
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
                    {
                        unset($onlineInfo[$userId]);
                    }
                }
            }
        }
        $checkOfflineChatEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ENABLE_DESKTOP_OFFLINE_CHAT, array('enOfflineChat' => true)));
        if(isset($checkOfflineChatEvent->getData()['setOfflineChat']) && $checkOfflineChatEvent->getData()['setOfflineChat']==true){
            foreach($idList as $id){
                $onlineInfo[$id]="1";
            }
        }


        $userFlatList = [];
        foreach ($userList as $userL){
            foreach($userL as $k => $v){
                $userFlatList[] = $v;
            }
        }

        $friendshipStatusEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::USER_LIST_FRIENDSHIP_STATUS, array('list' =>    $userFlatList,'desktopVersion'=>true)));
        if(isset($friendshipStatusEvent->getData()['answerValues']) && sizeof($friendshipStatusEvent->getData()['answerValues'])>0){
            $this->assign('answerValues', $friendshipStatusEvent->getData()['answerValues']);
            $this->assign('questionNameList', $friendshipStatusEvent->getData()['questionNameList']);
            $this->assign('questionNameValues', $friendshipStatusEvent->getData()['questionNameValues']);
        }
        $this->assign('questionList', $questionList);
        $this->assign('avatars', $avatarArr);
        $this->assign('onlineInfo', $onlineInfo);
        $this->assign('list', $userList);
    }

    /**
     * Get info about list of friends or friend requests
     *
     * @param integer $first
     * @param integer $count
     * @param string $listType
     * @return array( $userList, $count )
     */
    protected function getInfo( $first, $count, $listType )
    {
        $service = FRIENDS_BOL_Service::getInstance();
        $userService = BOL_UserService::getInstance();

        $userId = OW::getUser()->getId();

        switch ( $listType )
        {
            case 'friends':
                $idList = $service->findUserFriendsInList($userId, $first, $count);

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->countFriends($userId)
                );

            case 'sent-requests':

                $idList = $service->findFriendIdList($userId, $first, $count, 'sent-requests');

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->count($userId, null, FRIENDS_BOL_Service::STATUS_PENDING, FRIENDS_BOL_Service::STATUS_IGNORED)
                );

            case 'got-requests':

                $idList = $service->findFriendIdList($userId, $first, $count, 'got-requests');

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->count(null, $userId, FRIENDS_BOL_Service::STATUS_PENDING)
                );

            case 'user-friends':

                $eventParams = array(
                    'action' => 'friends_view',
                    'ownerId' => $userId,
                    'viewerId' => OW::getUser()->getId()
                );

                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);

                $user = BOL_UserService::getInstance()->findByUsername($this->params['user']);
                $userId = $user->getId();

                $idList = $service->findUserFriendsInList($userId, $first, $count);

                return array(
                    $userService->findUserListByIdList($idList),
                    $service->countFriends($userId)
                );
        }

        return array(array(), 0);
    }

    /**
     * Get submenu for friendship lists
     *
     * @return BASE_CMP_ContentMenu
     */
    protected function getMenu()
    {
        $items = array();
        $language = OW::getLanguage();
        $userId = OW::getUser()->getId();

        $count = FRIENDS_BOL_Service::getInstance()->countFriends($userId);
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('friends', 'friends_tab', array('count' => ($count > 0) ? "({$count})" : '')));
        $item->setKey('friends');
        $item->setUrl(OW::getRouter()->urlForRoute('friends_list'));
        $item->setOrder(1);
        $item->setIconClass('ow_ic_clock ow_dynamic_color_icon');
        $items[] = $item;

        $count = FRIENDS_BOL_Service::getInstance()->count($userId, null, FRIENDS_BOL_Service::STATUS_PENDING, FRIENDS_BOL_Service::STATUS_IGNORED);
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('friends', 'sent_requests_tab', array('count' => ($count > 0) ? "({$count})" : '')));
        $item->setKey('sent_requests');
        $item->setUrl(OW::getRouter()->urlForRoute('friends_lists', array('list' => 'sent-requests')));
        $item->setOrder(2);
        $item->setIconClass('ow_ic_push_pin ow_dynamic_color_icon');
        $items[] = $item;

        $count = FRIENDS_BOL_Service::getInstance()->countFriendRequests();
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('friends', 'got_requests_tab', array('count' => ($count > 0) ? "({$count})" : '')));
        $item->setKey('got_requests');
        $item->setUrl(OW::getRouter()->urlForRoute('friends_lists', array('list' => 'got-requests')));
        $item->setOrder(3);
        $item->setIconClass('ow_ic_push_pin ow_dynamic_color_icon');
        $items[] = $item;

        return new BASE_CMP_ContentMenu($items);
    }
}