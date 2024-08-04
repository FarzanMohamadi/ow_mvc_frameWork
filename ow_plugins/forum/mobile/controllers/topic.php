<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.forum.mobile.controllers
 * @since 1.6.0
 */
class FORUM_MCTRL_Topic extends FORUM_MCTRL_AbstractForum
{
    /**
     * Topic index
     * 
     * @param array $params
     */
    public function index( array $params )
    {
        // get topic info
        if ( !isset($params['topicId']) 
                || ($topicDto = $this->forumService->findTopicById($params['topicId'])) === null )
        {
            throw new Redirect404Exception();
        }
        /**
         * lockTopicCode
         */
        $lockTopicCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$params['topicId'],'isPermanent'=>true,'activityType'=>'lock_topic')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $lockTopicCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $lockTopicUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('lock-topic', array('topicId' => $params['topicId']))
            ,array('code' =>$lockTopicCode));
        $this->assign('lockTopicUrl',$lockTopicUrl);


        /**
         * stickyTopicCode
         */
        $stickyTopicCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$params['topicId'],'isPermanent'=>true,'activityType'=>'sticky_topic')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $stickyTopicCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $stickyTopicUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('sticky-topic', array('topicId' => $params['topicId']))
            ,array('code' =>$stickyTopicCode));
        $this->assign('stickyTopicUrl',$stickyTopicUrl);

        /**
         * subscribeTopicCode
         */
        $subscribeTopicCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$params['topicId'],'isPermanent'=>true,'activityType'=>'subscribe_topic')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $subscribeTopicCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $subscribeTopicUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('subscribe-topic', array('topicId' => $params['topicId']))
            ,array('code' =>$subscribeTopicCode));
        $this->assign('subscribeTopicUrl',$subscribeTopicUrl);

        if (FRMSecurityProvider::checkPluginActive('groups', true)) {
            $this->assign("frmmenu_active", true);
        }

        /**
         * deleteTopicCode
         */
        $deleteTopicCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$params['topicId'],'isPermanent'=>true,'activityType'=>'delete_topic')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $deleteTopicCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $deleteTopicUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('delete-topic', array('topicId' => $params['topicId']))
            ,array('code' =>$deleteTopicCode));
        $this->assign('deleteTopicUrl',$deleteTopicUrl);

        $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
        $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);
        $isOwner = ( $topicDto->userId == OW::getUser()->getId() ) ? true : false;
        // users cannot see topics in hidden sections
        if ( !$forumSection || $forumSection->isHidden )
        {
            $event = new OW_Event('forum.can_view', array(
                'entity' => $forumSection->entity,
                'entityId' => $forumGroup->entityId
            ), true);
            $this->assign('hideSearchComponent', true);
            OW::getEventManager()->trigger($event);

            $canView = $event->getData();

            $isModerator = OW::getUser()->isAuthorized($forumSection->entity);
            $params = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
            $event = new OW_Event('forum.check_permissions', $params);
            OW::getEventManager()->trigger($event);
            $canEdit = $event->getData();

            $params = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'delete_topic');
            $event = new OW_Event('forum.check_permissions', $params);
            OW::getEventManager()->trigger($event);
            $canDelete = $event->getData();

            $params = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'add_topic');
            $event = new OW_Event('forum.check_permissions', $params);
            OW::getEventManager()->trigger($event);
            $canPost = $event->getData();
            $this->assign('forumGroupBackUrl', OW::getRouter()->urlForRoute('group-default', array('groupId' => $forumGroup->id)));
            $heading = $topicDto->title;
            OW::getDocument()->setHeading($heading);
            $headingSet=true;
        }
        else
        {
            $isModerator = OW::getUser()->isAuthorized('forum');
            $canView = OW::getUser()->isAuthorized('forum', 'view');
            $canEdit = OW::getUser()->isAuthorized('forum', 'edit') || $isModerator ? true : false;
            $canDelete = OW::getUser()->isAuthorized('forum', 'delete') || $isModerator ? true : false;
            $canPost = OW::getUser()->isAuthorized('forum', 'edit') || $isModerator ;
        }

        $isModerator = OW::getUser()->isAuthorized('forum');
        if(FRMSecurityProvider::checkPluginActive('groups', true)) {
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($forumGroup->entityId);
            if (isset($groupDto) && GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto)) {
                $isModerator = true;
            }
        }
        if ( !$canView && !$isModerator )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $userId = OW::getUser()->getId();
        $isOwner = ( $topicDto->userId == $userId ) ? true : false;

        // check the permission for private topic
        if ( $forumGroup->isPrivate )
        {
            if ( !$userId )
            {
                throw new AuthorizationException();
            }
            else if ( !$isModerator )
            {
                if ( !$this->forumService->isPrivateGroupAvailable($userId, json_decode($forumGroup->roles)) )
                {
                    throw new AuthorizationException();
                }
            }
        }

        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',
            array('groupId'=>$forumGroup->entityId)));
        $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
        if ( isset($isChannelParticipant) && $isChannelParticipant){
            $canPost = false;
        }
        //update topic's view count
        $topicDto->viewCount += 1;
        $this->forumService->saveOrUpdateTopic($topicDto);

        //update user read info
        $this->forumService->setTopicRead($topicDto->id, $userId);

        $topicInfo = $this->forumService->getTopicInfo($topicDto->id);
        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;


        // include js translations
        OW::getLanguage()->addKeyForJs('forum', 'post_attachment');
        OW::getLanguage()->addKeyForJs('forum', 'attached_files');
        OW::getLanguage()->addKeyForJs('forum', 'confirm_delete_all_attachments');
        OW::getLanguage()->addKeyForJs('forum', 'confirm_delete_attachment');
        OW::getLanguage()->addKeyForJs('forum', 'move');

        // assign view variables
        $firstPost = $this->forumService->findTopicFirstPost($topicDto->id);
        $this->assign('firstTopicPost', $firstPost);
        $this->assign('userId', $userId);
        $this->assign('topicInfo', $topicInfo);
        $this->assign('page', $page);
        $this->assign('isOwner', $isOwner);
        $this->assign('isModerator', $isModerator);
        $this->assign('canEdit', $canEdit);
        $this->assign('canDelete', $canDelete);
        $this->assign('canMove', $canEdit);
        $this->assign('canPost', $canPost);
        $this->assign('canLock', $isModerator);
        $this->assign('canSticky', $isModerator);
        $this->assign('canSubscribe', OW::getUser()->isAuthorized('forum', 'subscribe'));
        $this->assign('isSubscribed', $userId 
                && FORUM_BOL_SubscriptionService::getInstance()->isUserSubscribed($userId, $topicDto->id));
        
        // remember the last forum page
        OW::getSession()->set('last_forum_page', OW_URL_HOME . OW::getRequest()->getRequestUri());

        // set current page settings
//        OW::getDocument()->setDescription(OW::getLanguage()->text('forum', 'meta_description_forums'));
        if(!isset($headingSet)) {
            OW::getDocument()->setHeading($topicDto->title);
        }
//        OW::getDocument()->setTitle(OW::getLanguage()->text('forum', 'forum_topic'));

        $params = array(
            "sectionKey" => "forum",
            "entityKey" => "topic",
            "title" => "forum+meta_title_topic",
            "description" => "forum+meta_desc_topic",
            "keywords" => "forum+meta_keywords_topic",
            "vars" => array( "topic_name" => $topicInfo['title'], "topic_description" => $firstPost->text )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
        $paramsForumPost = array('page' => $page,
            'topicInfo' => $topicInfo,
            'canEdit' => $canEdit,
            'canDelete'=> $canDelete,
            'canPost' => $canPost,
            'userId' => $userId,
            'isModerator' => $isModerator);
        $forumPostComponent = new FORUM_MCMP_ForumPost($paramsForumPost);
        $this->addComponent('forum_Post_Component',$forumPostComponent);
        $forumPostComponent->assign('firstTopicPost', $firstPost);
        $forumPostComponent->assign('userId', $userId);
        $forumPostComponent->assign('isModerator', $isModerator);
        //check paging is rendered
        //assign page to this
        if(isset($forumPostComponent->assignedVars['paging'])){
            $this->assign('paging' , $forumPostComponent->assignedVars['paging']);
        }

        OW::getEventManager()->trigger(new OW_Event('on.load.post.list.in.forum'));
        foreach ( $forumPostComponent->assignedVars['postList'] as &$post ) {
            $frmformplusEvent = OW::getEventManager()->trigger(new OW_Event('on.handle.more.in.forum', array('post' => $post)));
            if (isset($frmformplusEvent->getData()['post'])) {
                $post = $frmformplusEvent->getData()['post'];
            }
        }

        if(isset($forumGroup) && isset($forumGroup->entityId)){
            OW::getEventManager()->trigger(new OW_Event('frm.on.before.group.forum.topic.view.render', array('groupId' => $forumGroup->entityId)));
        }else {
            OW::getEventManager()->trigger(new OW_Event('frmwidgetplus.general.before.view.render', array('targetPage' => 'forumGroup', 'groupId' => $forumGroup->id)));
        }
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('forum')->getStaticJsUrl() .'forum.js');
    }

    
    /**
     * Delete forum post
     */
    public function ajaxDeletePost( array $params )
    {
        $result  = false;
        $postUrl = null;
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_post')));
        }
        $topicId = !empty($params['topicId']) ? (int) $params['topicId'] : null;
        $postId = !empty($params['postId']) ? (int) $params['postId'] : null;

        if ( OW::getRequest()->isPost() && $topicId && $postId ) 
        {
            $topicDto = $this->forumService->findTopicById($topicId);
            $postDto = $this->forumService->findPostById($postId);

            if ( $topicDto && $postDto )
            {
                $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
                $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);
                $userId = OW::getUser()->getId();
                $isModerator = OW::getUser()->isAuthorized('forum');
                if ( $forumSection->isHidden )
                {
                    $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
                    $event = new OW_Event('forum.check_permissions', $eParams);
                    OW::getEventManager()->trigger($event);

                    if ( $event->getData() )
                    {
                        $isModerator = true;
                    }
                }

                if ($topicDto->conclusionPostId == $params['postId']){
                    $topicDto->conclusionPostId = null;
                }

                //if ( !$forumSection->isHidden && ($postDto->userId == $userId || $isModerator) )
                if ( $postDto->userId == $userId || $isModerator)
                {
                    $prevPostDto = $this->forumService->findPreviousPost($topicId, $postId);

                    if ( $prevPostDto ) 
                    {
                        $topicDto->lastPostId = $prevPostDto->id;
                        $this->forumService->saveOrUpdateTopic($topicDto);

                        $this->forumService->deletePost($postId);
                        $postUrl = $this->forumService->getPostUrl($topicId, $prevPostDto->id, false);
                        $result = true;
                    }
                }
            }
        }

        die(json_encode(array(
            'result' => $result,
            'url' => $postUrl
        )));
    }

    /**
     * Delete attachment
     */
    public function ajaxDeleteAttachment()
    {
        $result  = false;
        $attachmentIds = !empty($_POST['id']) ? $_POST['id'] : null;

        if (OW::getRequest()->isAjax() && $attachmentIds )
        {
            if (!is_array($attachmentIds)) {
                $attachmentIds = array($attachmentIds);
            }

            $attachmentService = FORUM_BOL_PostAttachmentService::getInstance();
            $forumService = FORUM_BOL_ForumService::getInstance();
            $userId = OW::getUser()->getId();
            $isAuthorized = OW::getUser()->isAuthorized('forum');

            foreach ($attachmentIds as $attachmentId)
            {                
                $attachment = $attachmentService->findPostAttachmentById($attachmentId);

                if ( $attachment ) 
                {                    
                    $post = $forumService->findPostById($attachment->postId);

                    if ( $post )
                    {
                        // check the ownership
                        if ( $isAuthorized || $post->userId == $userId )
                        {
                            $attachmentService->deleteAttachment($attachment->id);
                            $result = true;
                            continue;
                        }
                    }
                }

                $result = false;
            }
        }

        die(json_encode(array(
            'result' => $result 
        )));
    }

    /**
     * This action deletes the topic
     *
     * @param array $params
     */
    public function ajaxDeleteTopic( array $params )
    {
        $result  = false;
        $topicId = !empty($params['topicId']) ? (int) $params['topicId'] : -1;
        $userId = OW::getUser()->getId();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_topic')));
        }
        if ( OW::getRequest()->isPost() ) 
        {
            $topicDto = $this->forumService->findTopicById($topicId);

            if ( $topicDto )
            {
                $isModerator = OW::getUser()->isAuthorized('forum');
                $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
                $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);
                if ( $forumSection->isHidden )
                {
                    $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'delete_topic');
                    $event = new OW_Event('forum.check_permissions', $eParams);
                    OW::getEventManager()->trigger($event);

                    if ( $event->getData() )
                    {
                        $isModerator = true;
                    }
                }
               /* if ( !$forumSection->isHidden
                        && ($isModerator || $userId == $topicDto->userId))*/
               if($isModerator || ($userId == $topicDto->userId && OW::getUser()->isAuthorized('forum', 'delete')) )
                {
                    $this->forumService->deleteTopic($topicId);
                    $result = true;
                }
            }
        }

        die(json_encode(array(
            'result' => $result 
        )));
    }

    /**
     * This action subscribe or unsubscribe the topic
     *
     * @param array $params
     */
    public function ajaxSubscribeTopic( array $params )
    {
        $result  = false;
        $topicId = !empty($params['topicId']) ? (int) $params['topicId'] : -1;
        $userId = OW::getUser()->getId();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'subscribe_topic')));
        }
        if ( OW::getRequest()->isPost() ) 
        {
            $subscribeService = FORUM_BOL_SubscriptionService::getInstance();
            $topicDto = $this->forumService->findTopicById($topicId);

            if ( $topicDto )
            {
                if ( OW::getUser()->isAuthorized('forum', 'subscribe') )
                {
                    if ( !$subscribeService->isUserSubscribed($userId, $topicId) )
                    {
                        $subscription = new FORUM_BOL_Subscription;
                        $subscription->userId = $userId;
                        $subscription->topicId = $topicId;

                        $subscribeService->addSubscription($subscription);
                    }
                    else
                    {
                        $subscribeService->deleteSubscription($userId, $topicId);
                    }

                    $result = true;
                }
            }
        }

        die(json_encode(array(
            'result' => $result 
        )));
    }

    /**
     * This action locks or unlocks the topic
     *
     * @param array $params
     */
    public function ajaxLockTopic( array $params )
    {
        $result  = false;
        $topicId = !empty($params['topicId']) ? (int) $params['topicId'] : -1;
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'lock_topic')));
        }
        if ( OW::getRequest()->isPost() ) 
        {
            $isModerator = OW::getUser()->isAuthorized('forum');
            $topicDto = $this->forumService->findTopicById($topicId);
            $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
            $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);
            if ( $forumSection->isHidden )
            {
                $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
                $event = new OW_Event('forum.check_permissions', $eParams);
                OW::getEventManager()->trigger($event);

                if ( $event->getData() )
                {
                    $isModerator = true;
                }
            }
            if ( $topicDto )
            {
                if ( $isModerator )
                {
                    $topicDto->locked = ($topicDto->locked) ? 0 : 1;
                    $topicDto->closeTime = time();
                    $this->forumService->saveOrUpdateTopic($topicDto);
                    $result = true;
                }
            }
        }

        die(json_encode(array(
            'result' => $result 
        )));
    }

    /**
     * This action sticky or unsticky the topic
     *
     * @param array $params
     */
    public function ajaxStickyTopic( array $params )
    {
        $result  = false;
        $topicId = !empty($params['topicId']) ? (int) $params['topicId'] : -1;
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['code'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['code'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'sticky_topic')));
        }
        if ( OW::getRequest()->isPost() ) 
        {
            $isModerator = OW::getUser()->isAuthorized('forum');
            $topicDto = $this->forumService->findTopicById($topicId);
            $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
            $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);
            if ( $forumSection->isHidden )
            {
                $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
                $event = new OW_Event('forum.check_permissions', $eParams);
                OW::getEventManager()->trigger($event);

                if ( $event->getData() )
                {
                    $isModerator = true;
                }
            }
            if ( $topicDto )
            {
                if ( $isModerator )
                {
                    $topicDto->sticky = ($topicDto->sticky) ? 0 : 1;
                    $this->forumService->saveOrUpdateTopic($topicDto);
                    $result = true;
                }
            }
        }

        die(json_encode(array(
            'result' => $result 
        )));
    }
    /**
     * This action sets/unsets the topic conclusion post
     *
     * @param array $params
     */
    public function setTopicConclusionPost( array $params ) {
        $topicDto = $this->forumService->findTopicById($params['topicId']);
        if ($topicDto->conclusionPostId == null || $topicDto->conclusionPostId != $params['postId'])
            $topicDto->conclusionPostId = $params['postId'];
        else
            $topicDto->conclusionPostId = null;
        $this->forumService->saveOrUpdateTopic($topicDto);

        $topicUrl = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $params['topicId']));
        $this->redirect($topicUrl . "?page=1");
    }

}