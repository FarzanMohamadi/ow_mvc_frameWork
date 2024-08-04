<?php
class FRMTICKETING_CTRL_Ticket extends OW_ActionController
{

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function viewList()
    {
        if (!OW::getUser()->isAuthenticated())
        {
            throw new Redirect404Exception();
        }
        $language=OW::getLanguage();
        $this->setPageHeading($language->text('frmticketing', 'ticket_list_page_heading'));
        $this->setPageTitle($language->text('frmticketing', 'ticket_list_page_title'));

        $hasAccessAllTickets = OW::getUser()->isAuthorized('frmticketing', 'view_tickets') || OW::getUser()->isAdmin();

        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();

        $ticketService->setFilterParameters();

        $searchForm= $ticketService->getTicketFilterForm('searchForm');
        $this->addForm($searchForm);
        $filterFormElementsKey = array();
        foreach ($searchForm->getElements() as $element) {
            if ($element->getAttribute('type') != 'hidden') {
                $filterFormElementsKey[] = $element->getAttribute('name');
            }
        }
        $this->assign('filterFormElementsKey', $filterFormElementsKey);

        $url = OW::getRouter()->urlForRoute('frmticketing.view_tickets');
        $this->assign('url',$url);

        $userId = OW::getUser()->getId();

        $plugin = OW::getPluginManager()->getPlugin('frmticketing');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . "ticket.js");

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;
        $pageCount = FRMTICKETING_BOL_TicketService::TICKET_PER_PAGE;
        if($hasAccessAllTickets)
        {
            $ticketList = $ticketService->findAllTickets($page);
            $ticketListCount = $ticketService->findAllTicketsCount();
        }else{
            $ticketList = $ticketService->findTicketsByAuthorId($userId, $page);
            $ticketListCount = $ticketService->findTicketsByUserIdCount($userId);

            $ticketAssignedListCount = $ticketService->findAssignedTicketsByUserIdCount($userId);
            if($ticketAssignedListCount>0)
            {
                $ticketAssignedList = $ticketService->findAssignedTicketsByUserId($userId, $page);
                $ticketInfoComponent = new FRMTICKETING_CMP_TicketListInfo($ticketAssignedList,$ticketAssignedListCount,$page,$pageCount);
                $this->addComponent('assignedTicketInfoComponent',$ticketInfoComponent);
            }
        }
        $ticketInfoComponent = new FRMTICKETING_CMP_TicketListInfo($ticketList,$ticketListCount,$page,$pageCount);
        $this->addComponent('ticketInfoComponent',$ticketInfoComponent);

    }

    public function getTicketUrl($ticketId)
    {
        return OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketId));
    }

    public function view($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }

        if(!isset($params['ticketId']))
        {
            throw new Redirect404Exception();
        }

        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        $ticketDto=$ticketService->findTicketById($params['ticketId']);
        if(!isset($ticketDto))
        {
            throw new Redirect404Exception();
        }
        $isManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets')|| OW::getUser()->isAdmin();
        $isOwner=OW::getUser()->getId()==$ticketDto->userId;
        $ticketInfo=$ticketService->findTicketInfoById($params['ticketId']);
        $categories = FRMTICKETING_BOL_TicketCategoryUserDao::getInstance()->findCategoriesOfUser(OW::getUser()->getId());
        $isTicketManager = in_array($ticketInfo['categoryId'],$categories);
        $canPost= $isTicketManager || $isOwner;
        if(!$isManager && !$isTicketManager && !$isOwner)
        {
            throw new Redirect404Exception();
        }

        $this->setPageHeading($ticketInfo['title']);


        $this->assign('ticketViewsUrl',OW::getRouter()->urlForRoute('frmticketing.view_tickets'));
        $this->assign('ticketViews',OW::getLanguage()->text('frmticketing', 'ticket_list_page_title'));

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($ticketInfo['userId']), true, false);

        $avatar[$ticketInfo['userId']]['url'] = BOL_UserService::getInstance()->getUserUrl($ticketInfo['userId']);
        $userService = BOL_UserService::getInstance();

        $ticketInfoArr = array(
            'id' => $ticketInfo['id'],
            'ticketTrackingNumber' => $ticketInfo['ticketTrackingNumber'],
            'userId' => $ticketInfo['userId'],
            'displayName' => $userService->getDisplayName($ticketInfo['userId']),
            'username'  => $userService->getUserName($ticketInfo['userId']),
            'authorAvatar' =>$avatar[$ticketInfo['userId']],
            'title' =>$ticketInfo['title'],
            'locked'=>$ticketInfo['locked'],
            'description' => UTIL_HtmlTag::stripJs($ticketInfo['description']),
            'timeStamp' => UTIL_DateTime::formatDate($ticketInfo['timeStamp']),
            'ticketUrl' => $this->getTicketUrl($ticketInfo['id']),
            'categoryTitle' =>$ticketInfo['categoryTitle'],
            'orderTitle' =>$ticketInfo['orderTitle']
        );
        $this->assign('ticketInfo',$ticketInfoArr);
        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;

        $this->assign('isOwner', $isOwner);
        $this->assign('userId', OW::getUser()->getId());
        $this->assign('canPost', $canPost);
        $this->assign('isTicketManager', $isTicketManager);
        $this->assign('isManager', $isManager);


        $toolbars = array();
        $lang = OW::getLanguage();

        $langQuote = $lang->text('frmticketing', 'post_quote');

        $iteration = 0;
        $userIds = array();
        $postIds = array();

        $ticketPostList = $ticketService->findTicketPostList($ticketDto->id, $page);
        $attachmentService = FRMTICKETING_BOL_TicketAttachmentService::getInstance();
        foreach ( $ticketPostList as $post )
        {
            $post['text'] = UTIL_HtmlTag::linkify($post['text']);
            $post['permalink'] = $ticketService->getPostUrl($post['ticketId'], $post['id'], true, $page);
            $post['number'] = ($page - 1) * $ticketService::POST_PER_PAGE + $iteration + 1;

            $text = explode("<!--more-->", $post['text']);
            $isPreview = count($text) > 1;
            if ( $isPreview ){
                $post['showMore'] = true;
                $post['beforeMoreText'] = $text[0];
                $post['afterMoreText'] = $text[1];
            }
            else{
                $post['beforeMoreText'] = $post['text'];
            }
            // get list of users
            if ( !in_array($post['userId'], $userIds) )
                $userIds[$post['userId']] = $post['userId'];

            $toolbar = array();

            $label = OW::getLanguage()->text('frmticketing', 'toolbar_post_number', array("num"=> $post['number'] ));
            array_push($toolbar, array('class' => 'post_permalink', 'href' => $post['permalink'], 'label' => $label));

            if ( !$ticketDto->locked &&  $canPost )
            {
                array_push($toolbar, array('id' => $post['id'], 'class' => 'quote_post', 'href' => 'javascript://', 'label' => $langQuote));
            }

            $toolbars[$post['id']] = $toolbar;

            $iteration++;
            array_push($postIds, $post['id']);
        }

        $postAttachment = $attachmentService->findAttachmentsByEntityIdList($postIds,FRMTICKETING_BOL_TicketAttachmentDao::POST_TYPE);
        $postAttachment = $ticketService->addIconsToTicketAttachments($postAttachment);

        $this->assign('postAttachment', $postAttachment);

        $this->assign('toolbars', $toolbars);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);
        $this->assign('avatars', $avatars);

        $uid = FRMSecurityProvider::generateUniqueId();
        $addPostForm = $this->generateAddPostForm($ticketDto->id, $uid);
        $this->addForm($addPostForm);

        $addPostInputId = $addPostForm->getElement('text')->getId();
        $attachments = $attachmentService->findAttachmentsByEntityIdList(array($ticketInfo['id']),FRMTICKETING_BOL_TicketAttachmentDao::TICKET_TYPE);
        $attachments = $ticketService->addIconsToTicketAttachments($attachments);
        if( isset($attachments[$ticketInfo['id']]) ){
            $this->assign('attachments', $attachments[$ticketInfo['id']]);
        }
        else{
            $this->assign('attachments', array());
        }


        $attachmentCmp = new BASE_CLASS_FileAttachment('frmticketing', $uid);
        $this->addComponent('attachmentsCmp', $attachmentCmp);


        /**
         * deletePostCode
         */
        $postDeleteCode='';
        $frmSecurityManagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$ticketDto->id,'isPermanent'=>true,'activityType'=>'delete_ticket_post')));
        if(isset($frmSecurityManagerEvent->getData()['code'])){
            $postDeleteCode = $frmSecurityManagerEvent->getData()['code'];
        }
        $deletePostUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmticketing.delete-post',
            array('ticketId' => $ticketDto->id, 'postId' => 'postId')),array('code' =>$postDeleteCode));

        /**
         * lockTicketCode
         */
        $lockTicketCode='';
        $frmSecurityManagerEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$ticketDto->id,'isPermanent'=>true,'activityType'=>'lock_ticket')));
        if(isset($frmSecurityManagerEvent->getData()['code'])){
            $lockTicketCode = $frmSecurityManagerEvent->getData()['code'];
        }
        $lockTicketUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmticketing.lock-ticket', array('ticketId' => $ticketDto->id, 'page' => $page))
            ,array('code' =>$lockTicketCode));
        /**
         * deleteTicketCode
         */
        $deleteTicketCode='';
        $frmSecurityManagerEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$ticketDto->id,'isPermanent'=>true,'activityType'=>'delete_ticket')));
        if(isset($frmSecurityManagerEvent->getData()['code'])){
            $deleteTicketCode = $frmSecurityManagerEvent->getData()['code'];
        }
        $deleteTicketUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmticketing.delete-ticket', array('ticketId' => $ticketDto->id))
            ,array('code' =>$deleteTicketCode));

        $getPostUrl = OW::getRouter()->urlForRoute('frmticketing.get-post', array('postId' => 'postId'));
        $ticketInfoJs = json_encode(array('locked' => $ticketDto->locked));

        /**
         * editTicketCode
         */
        $editTicketCode='';
        $frmSecurityManagerEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$ticketDto->id,'isPermanent'=>true,'activityType'=>'edit_ticket')));
        if(isset($frmSecurityManagerEvent->getData()['code'])){
            $editTicketCode = $frmSecurityManagerEvent->getData()['code'];
        }

        $onloadJs = "
			Ticket.deletePostUrl = '$deletePostUrl';
			Ticket.lockTicketUrl = '$lockTicketUrl';
			Ticket.deleteTicketUrl = '$deleteTicketUrl';
			Ticket.getPostUrl = '$getPostUrl';
			Ticket.add_post_input_id = '$addPostInputId';
			Ticket.construct($ticketInfoJs);
			";

        OW::getDocument()->addOnloadScript($onloadJs);

        $plugin = OW::getPluginManager()->getPlugin('frmticketing');

        OW::getDocument()->addScript($plugin->getStaticJsUrl() . "ticket.js");
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl()."ticket.css");

        // add language keys for javascript
        $lang->addKeyForJs('frmticketing', 'sticky_topic_confirm');
        $lang->addKeyForJs('frmticketing', 'unsticky_topic_confirm');
        $lang->addKeyForJs('frmticketing', 'lock_topic_confirm');
        $lang->addKeyForJs('frmticketing', 'unlock_topic_confirm');
        $lang->addKeyForJs('frmticketing', 'delete_topic_confirm');
        $lang->addKeyForJs('frmticketing', 'delete_post_confirm');
        $lang->addKeyForJs('frmticketing', 'edit_topic_title');
        $lang->addKeyForJs('frmticketing', 'edit_post_title');
        $lang->addKeyForJs('frmticketing', 'move_topic_title');
        $lang->addKeyForJs('frmticketing', 'lock_ticket_confirm');
        $lang->addKeyForJs('frmticketing', 'delete_ticket_confirm');
        $lang->addKeyForJs('frmticketing', 'unlock_ticket_confirm');
        $lang->addKeyForJs('frmticketing', 'ticket_quote');
        $lang->addKeyForJs('frmticketing', 'ticket_quote_from');


        //posts count on page
        $count = $ticketService::POST_PER_PAGE;

        $postCount = $ticketService->findTicketPostCount($ticketDto->id);
        $pageCount = ceil($postCount / $count);

        $Paging = new BASE_CMP_Paging($page, $pageCount, $count);

        $this->assign('paging', $Paging->render());
        $this->assign('postList', $ticketPostList);
        $this->assign('page', $page);
        $this->assign('backUrl', OW::getRouter()->urlForRoute('frmticketing.view_tickets'));
    }


    /**
     * This action adds a post and after execution redirects to default action
     *
     * @param array $params
     * @throws Redirect404Exception
     * @throws AuthenticateException
     */
    public function addPost( array $params )
    {
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();

        if ( !isset($params['ticketId']) || !($ticketId = (int) $params['ticketId']) )
        {
            throw new Redirect404Exception();
        }

        $ticketDto = $ticketService->findTicketById($ticketId);

        if ( !$ticketDto )
        {
            throw new Redirect404Exception();
        }

        $isTicketManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets')|| OW::getUser()->isAdmin();
        $isOwner=OW::getUser()->getId()==$ticketDto->userId;
        if ( !$isTicketManager && !$isOwner )
        {
            throw new Redirect404Exception();
        }

        $uid = $params['uid'];

        $addPostForm = $this->generateAddPostForm($ticketId, $uid);

        if ( OW::getRequest()->isPost() && $addPostForm->isValid($_POST) )
        {
            $data = $addPostForm->getValues();

            if ( $data['ticket'] && $data['ticket'] == $ticketDto->id && !$ticketDto->locked )
            {
                if ( !OW::getUser()->getId() )
                {
                    throw new AuthenticateException();
                }

                $postDto = $ticketService->addPost($ticketDto, $data);
                if(isset($postDto))
                {
                    $this->sendNewPostNotification($postDto,$ticketDto);
                }
                $this->redirect($ticketService->getPostUrl($ticketId, $postDto->id));
            }
            else{
                $this->redirect(OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketId)));
            }
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text('frmticketing', 'error_adding_post'));
            $this->redirect(OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketId)));
        }
    }

    private function sendNewPostNotification($postDto,$ticketDto)
    {
        $proceedPermissions=array();
        $users=array();
        $authorizationGroup= BOL_AuthorizationGroupDao::getInstance()->findByName('frmticketing');
        $action = BOL_AuthorizationActionDao::getInstance()->findAction('view_tickets',$authorizationGroup->id);
        $authorizationPermissions= BOL_AuthorizationPermissionDao::getInstance()->findByActionId($action->id);
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        $postUrl = $ticketService->getPostUrl($postDto->ticketId, $postDto->id);
        $ticketUrl = OW::getRouter()->urlForRoute('frmticketing.view_ticket', array('ticketId' => $postDto->ticketId));
        $description = nl2br(UTIL_String::truncate($postDto->text, 300, '...'));
        $isOwner=$postDto->userId==$ticketDto->userId;
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($postDto->userId));
        if($isOwner)
        {
            foreach ($authorizationPermissions as $authorizationPermission) {
                if (in_array($authorizationPermission->roleId, $proceedPermissions)) {
                    continue;
                }
                $userCount = (int)BOL_UserService::getInstance()->countByRoleId($authorizationPermission->roleId);
                $users = BOL_UserService::getInstance()->findListByRoleId($authorizationPermission->roleId, 0, $userCount);
                array_push($proceedPermissions, (int)$authorizationPermission->roleId);
            }
            foreach ($users as $user) {
                if ($user->getId() == $postDto->userId) {
                    continue;
                }
                $notifService = NOTIFICATIONS_BOL_Service::getInstance();
                $notification = $notifService->findNotification('ticket-post-add', (int)$ticketDto->id, $user->getId());
                if (isset($notification)) {
                    $notification->sent = 0;
                    $notification->viewed = 0;
                    $notifService->saveNotification($notification);
                } else {
                    $event = new OW_Event('notifications.add', array(
                        'pluginKey' => 'frmticketing',
                        'entityType' => 'ticket-post-add',
                        'entityId' => (int)$postDto->id,
                        'action' => 'receive-ticket-update',
                        'userId' => $user->getId(),
                        'time' => time()
                    ), array(
                        'avatar' => $avatars[$postDto->userId],
                        'string' => array(
                            'key' => 'frmticketing+ticket_notification_post',
                            'vars' => array(
                                'userName' => $avatars[$postDto->userId]['title'],
                                'userUrl' => $avatars[$postDto->userId]['url'],
                                'postUrl' => $postUrl,
                                'ticketUrl' => $ticketUrl,
                                'title' => strip_tags($ticketDto->title)
                            )
                        ),
                        'content' => $description,
                        'url' => OW::getRouter()->urlForRoute('frmticketing.view_ticket', array('ticketId' => $ticketDto->id))
                    ));
                    OW::getEventManager()->trigger($event);
                }
            }
        }else{
            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'frmticketing',
                'entityType' => 'ticket-post-add',
                'entityId' => (int)$postDto->id,
                'action' => 'receive-ticket-update',
                'userId' => $ticketDto->userId,
                'time' => time()
            ), array(
                'avatar' => $avatars[$postDto->userId],
                'string' => array(
                    'key' => 'frmticketing+ticket_notification_post',
                    'vars' => array(
                        'userName' => $avatars[$postDto->userId]['title'],
                        'userUrl' => $avatars[$postDto->userId]['url'],
                        'postUrl' => $postUrl,
                        'ticketUrl' => $ticketUrl,
                        'title' => strip_tags($ticketDto->title)
                    )
                ),
                'content' => $description,
                'url' => OW::getRouter()->urlForRoute('frmticketing.view_ticket', array('ticketId' => $ticketDto->id))
            ));
            OW::getEventManager()->trigger($event);
        }
    }

    /**
     * This action deletes thread post
     * and after execution redirects to default action
     *
     * @param array $params
     * @throws Redirect404Exception
     */
    public function deletePost( array $params )
    {
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        if ( !isset($params['ticketId']) || !($ticketId = (int) $params['ticketId']) || !isset($params['postId']) || !($postId = (int) $params['postId']) )
        {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_ticket_post')));
        }

        $ticketDto = $ticketService->findTicketById($ticketId);
        $postDto = $ticketService->findTicketPostById($postId);

        $isTicketManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets')|| OW::getUser()->isAdmin();
        $isOwner=OW::getUser()->getId()==$postDto->userId;


        if ( $ticketDto && $postDto && ($isOwner|| $isTicketManager) )
        {
            $prevPostDto = $ticketService->findPreviousPost($ticketId, $postId);
            $ticketService->deletePost($postId);
            $postUrl =$ticketService->getPostUrl($ticketId, $prevPostDto->id, false);
        }
        else
        {
            $postUrl = $ticketService->getPostUrl($ticketId, $postId, false);
        }

        $this->redirect($postUrl);
    }


    /**
     * This action deletes the ticket
     * and after execution redirects to default action
     *
     * @param array $params
     * @throws Redirect404Exception
     */
    public function deleteTicket( array $params )
    {
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        if ( !isset($params['ticketId']) || !($ticketId = (int) $params['ticketId']) )
        {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_ticket')));
        }

        $isTicketManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets')|| OW::getUser()->isAdmin();

        $ticketDto = $ticketService->findTicketById($ticketId);

        $userId = OW::getUser()->getId();

        $redirectUrl = OW::getRouter()->urlForRoute('frmticketing.view_tickets');

        $isOwner=OW::getUser()->getId()==$ticketDto->userId;

        if ( $ticketDto )
        {

            if ( $isTicketManager || $isOwner )
            {
                $ticketService->deleteTicket($ticketId);
            }
        }

        $this->redirect($redirectUrl);
    }



    /**
     * This action locks or unlocks the ticket
     * and after execution redirects to default action
     *
     * @param array $params
     * @throws Redirect404Exception
     */
    public function lockTicket( array $params )
    {
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        if ( !isset($params['ticketId']) || !($ticketId = (int) $params['ticketId']) || !isset($params['page']) || !($page = (int) $params['page']) )
        {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'lock_ticket')));
        }
        $isTicketManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets')|| OW::getUser()->isAdmin();

        $ticketDto = $ticketService->findTicketById($ticketId);

        if ( $ticketDto && $isTicketManager)
        {
            $ticketDto->locked = ($ticketDto->locked) ? 0 : 1;
            $ticketService->saveOrUpdateTicket($ticketDto);
        }

        $ticketUrl =  OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketId));

        $this->redirect($ticketUrl . "?page=$page");
    }

    /**
     * This action gets the post called by ajax request
     *
     * @param array $params
     * @throws Redirect404Exception
     */
    public function getPost( array $params )
    {
        $ticketService = FRMTICKETING_BOL_TicketService::getInstance();
        if ( isset($params['postId']) && $postId = (int) $params['postId'] )
        {
            if ( OW::getRequest()->isAjax() )
            {
                $postDto = $ticketService->findPostById($postId);
                if (!$postDto){
                    exit();
                }

                $ticketDto = $ticketService->findTicketById($postDto->ticketId);
                if ( !$ticketDto ){
                    exit();
                }


                $isTicketManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets')|| OW::getUser()->isAdmin();

                $isOwner=OW::getUser()->getId()==$ticketDto->userId;


                if ( !$isOwner && !$isTicketManager )
                {
                    exit();
                }

                $postQuote = new FRMTICKETING_CMP_TicketPostQuote(array(
                    'quoteId' => $postId
                ));

                echo json_encode($postQuote->render());
            }
            else
            {
                throw new Redirect404Exception();
            }
        }

        exit();
    }


    /**
     * Generates add post form.
     *
     * @param int $ticketId
     * @param string $uid
     * @return Form
     */
    private function generateAddPostForm( $ticketId, $uid )
    {
        $form = new FRMTICKETING_CLASS_PostForm(
            'add-post-form',
            $uid,
            $ticketId,
            false
        );
        $form->setAction(OW::getRouter()->
        urlForRoute('frmticketing.add-post', array('ticketId' => $ticketId, 'uid' => $uid)));
        return $form;
    }
}

