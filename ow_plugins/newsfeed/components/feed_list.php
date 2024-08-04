<?php
/**
 * Feed List component
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_FeedList extends OW_Component
{
    private $feed = array();
    private $sharedData = array();
    private $displayType;

    public function __construct( $actionList, $data )
    {
        parent::__construct();

        $GLOBALS['has_newsfeed_list'] = true;
        $this->feed = $actionList;
        $this->displayType = NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTION;

        $this->sharedData['feedAutoId'] = $data['feedAutoId'];
        $this->sharedData['displayType'] = $data['displayType'];
        $this->sharedData['feedType'] = $data['feedType'];
        $this->sharedData['feedId'] = $data['feedId'];
        if (isset($data['additionalParamList'])) {
            $this->sharedData['additionalParamList'] = $data['additionalParamList'];
        }
        $this->sharedData['configs'] = OW::getConfig()->getValues('newsfeed');

        $userIds = array();
        $entityList = array();
        $entityTypeList = array();
        $entityIdList = array();
        $actionIds = array();
        $groupIds = array();
        $cachedCreatorActivities = array();
        $activityIds = array();
        $questionIds = array();
        $attachmentIdList = array();
        $usernameList = array();
        $forumTopicIds = array();
        $groupsFileIds = array();
        $eventFileIds = array();

        foreach ( $this->feed as $action )
        {
            $actionIds[] = $action->getId();
            if (isset($action->getData()['reply_to'])) {
                $actionIds[] = $action->getData()['reply_to'];
            }
            $cachedCreatorActivities[$action->getId()] = $action->getCreateActivity();
            foreach ($action->getActivityList() as $act) {
                $activityIds[] = $act->id;
            }
            /* @var $action NEWSFEED_CLASS_Action */
            $userIds[$action->getUserId()] = $action->getUserId();
            $entityList[] = array(
                'entityType' => $action->getEntity()->type,
                'entityId' => $action->getEntity()->id,
                'pluginKey' => $action->getPluginKey(),
                'userId' => $action->getUserId(),
                'countOnPage' => $this->sharedData['configs']['comments_count']
            );
            if (isset($action->getData()['question_id'])) {
                $questionIds[] = $action->getData()['question_id'];
            }
            if (isset($action->getData()['attachmentIdList'])) {
                $attachmentIdList = array_merge($action->getData()['attachmentIdList'], $attachmentIdList);
            }
            $entityTypeList[] = $action->getEntity()->type;
            $entityIdList[] = $action->getEntity()->id;
            if ($action->getEntity()->type == 'forum-topic') {
                $forumTopicIds[] = $action->getEntity()->id;
            }
            if ($action->getEntity()->type == 'groups-add-file') {
                $groupsFileIds[] = $action->getEntity()->id;
            }
            if ($action->getEntity()->type == 'event-add-file') {
                $eventFileIds[] = $action->getEntity()->id;
            }
            if (isset($action->getData()['contextFeedType']) && isset($action->getData()['contextFeedId'])) {
                $feedType = $action->getData()['contextFeedType'];
                $feedId = $action->getData()['contextFeedId'];
                if ($feedType == 'groups') {
                    if (!in_array($feedId, $groupIds)) {
                        $groupIds[] = $feedId;
                    }
                }
            }
            if (isset($action->getData()['status']) && FRMSecurityProvider::checkPluginActive('frmmention', true)) {
                $mentionService = FRMMENTION_BOL_Service::getInstance();
                $localUsernameList = $mentionService->findUsernamesFromView($action->getData()['status']);
                $usernameList = array_merge($localUsernameList, $usernameList);
            }
        }

        $groupsCacheInfo = array();
        $groupsChannelCacheInfo = array();
        $groupsManagersCacheInfo = array();
        if (FRMSecurityProvider::checkPluginActive('groups', true) && !empty($groupIds)) {
            $groups = GROUPS_BOL_GroupDao::getInstance()->findByIdList($groupIds);
            foreach ($groups as $group) {
                $groupsCacheInfo[$group->id] = $group;
            }
        }

        if (FRMSecurityProvider::checkPluginActive('forum', true) && !empty($forumTopicIds)) {
            $this->sharedData['cache']['topics_posts'] = FORUM_BOL_PostDao::getInstance()->findTopicsPostByIds($forumTopicIds);

            $topics = FORUM_BOL_TopicDao::getInstance()->findByIdList($forumTopicIds);
            $topicsData = array();
            foreach ($topics as $topic) {
                $topicsData[$topic->id] = $topic;
            }
            $this->sharedData['cache']['topics'] = $topicsData;
        }

        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true) && !empty($groupIds)) {
            $groupsChannelIds = FRMGROUPSPLUS_BOL_ChannelService::getInstance()->findChannelIds($groupIds);
            foreach ($groupIds as $groupId) {
                $channel = false;
                if (in_array($groupId, $groupsChannelIds)) {
                    $channel = true;
                }
                $groupsChannelCacheInfo[$groupId] = $channel;
            }
            $groupsManagersCacheInfo = FRMGROUPSPLUS_BOL_GroupManagersDao::getInstance()->getGroupManagersByGroupIds($groupIds);
        }

        if (FRMSecurityProvider::checkPluginActive('frmgroupsplus', true) && !empty($groupsFileIds)) {
            $groupFiles = FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->findByIdList($groupsFileIds);
            $groupFilesData = array();
            foreach ($groupFiles as $groupFile) {
                $attachmentIdList[] = $groupFile->attachmentId;
                $groupFilesData[$groupFile->id] = $groupFile;
            }

            $this->sharedData['cache']['group_files'] = $groupFilesData;
        }

        if (FRMSecurityProvider::checkPluginActive('frmeventplus', true) && !empty($eventFileIds)) {
            $eventFiles = FRMEVENTPLUS_BOL_EventFilesDao::getInstance()->findByIdList($eventFileIds);
            $eventFilesData = array();
            foreach ($eventFiles as $eventFile) {
                $attachmentIdList[] = $eventFile->attachmentId;
                $eventFilesData[$eventFile->id] = $eventFile;
            }

            $this->sharedData['cache']['event_files'] = $eventFilesData;
        }

        $cachedPinedActions = array();
        if (FRMSecurityProvider::checkPluginActive('frmnewsfeedpin', true)) {
            $pinList = FRMNEWSFEEDPIN_BOL_PinDao::getInstance()->findByEntityIdsAndEntityTypes($entityIdList, $entityTypeList);
            foreach ($pinList as $pin) {
                $cachedPinedActions[$pin->entityType . '-' . $pin->entityId] = true;
            }
            $this->sharedData['cache']['pinned_actions'] = $cachedPinedActions;
        }

        if (sizeof($questionIds) > 0 && FRMSecurityProvider::checkPluginActive('frmquestions', true)) {
            $cachedQuestionsInfo = FRMQUESTIONS_BOL_Service::getInstance()->findOptionsAnswersListByQuestionIds($questionIds);
            foreach ($cachedQuestionsInfo as $key => $value) {
                $question = $cachedQuestionsInfo[$key];
                if (isset($question['options']))
                foreach ($question['options'] as $key2 => $value2) {
                    $option = $question['options'][$key2];
                    if (isset($option['answers'])) {
                        foreach ($option['answers'] as $optionAnswerUserId) {
                            $userIds[] = $optionAnswerUserId;
                        }
                    }
                }
            }
            $this->sharedData['cache']['questions'] = $cachedQuestionsInfo;
        }

        $this->sharedData['cache']['groups'] = $groupsCacheInfo;
        $this->sharedData['cache']['groups_channel'] = $groupsChannelCacheInfo;
        $this->sharedData['cache']['groups_managers'] = $groupsManagersCacheInfo;
        $this->sharedData['cache']['activity_creator'] = $cachedCreatorActivities;

        $cachedActions = NEWSFEED_BOL_Service::getInstance()->findActionByIds($actionIds);
        $this->sharedData['cache']['actions'] = $cachedActions;

        $actionsByEntity = array();
        foreach ($cachedActions as $cachedAction) {
            $actionsByEntity[$cachedAction->entityType . '-' . $cachedAction->entityId] = $cachedAction;
        }
        $this->sharedData['cache']['actions_by_entity'] = $actionsByEntity;

        $cachedFeedFromCreatorActivity = array();
        $feedIdFromActivities = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds($activityIds);
        foreach ($feedIdFromActivities as $feedFromActivity){
            foreach ($cachedCreatorActivities as $key => $value){
                if ($cachedCreatorActivities[$key]->id == $feedFromActivity->activityId) {
                    $cachedFeedFromCreatorActivity[$feedFromActivity->activityId] = $feedFromActivity;
                }
            }
        }
        foreach ($activityIds as $id){
            if (!isset($cachedFeedFromCreatorActivity[$id])) {
                $cachedFeedFromCreatorActivity[$id] = null;
            }
        }
        $this->sharedData['cache']['feed_by_creator_activity'] = $cachedFeedFromCreatorActivity;
        $this->sharedData['commentsData'] = BOL_CommentService::getInstance()->findBatchCommentsData($entityList);
        $this->sharedData['likesData'] = BOL_VoteDao::getInstance()->findLikesByEntityList($entityList);

        $commentsEntityTypes = array();
        $commentsEntityIds = array();
        foreach ($this->sharedData['commentsData'] as $entityType => $info) {
            if ($entityType != '_static') {
                $commentsEntityTypes[] = 'frmlike-' . $entityType;
                foreach ($this->sharedData['commentsData'][$entityType] as $key => $value) {
                    foreach ($this->sharedData['commentsData'][$entityType] as $key2 => $value2) {
                        $comments = $this->sharedData['commentsData'][$entityType][$key2]['commentsList'];
                        foreach ($comments as $comment) {
                            if (!in_array($comment->userId, $userIds)) {
                                $userIds[] = (int) $comment->userId;
                            }
                            $commentsEntityIds[] = $comment->id;
                            if (FRMSecurityProvider::checkPluginActive('frmmention', true)) {
                                $mentionService = FRMMENTION_BOL_Service::getInstance();
                                $localUsernameList = $mentionService->findUsernamesFromView($comment->message);
                                $usernameList = array_merge($localUsernameList, $usernameList);
                            }
                        }
                    }
                }
            }
        }
        $usernameList = array_unique($usernameList);
        $this->sharedData['cache']['comments_votes'] = BOL_VoteDao::getInstance()->getEntityTypesVotes($commentsEntityIds, $commentsEntityTypes);

        $userIdsByUsernameList = BOL_UserDao::getInstance()->findIdsByUserNames($usernameList);
        $userIds = array_merge($userIdsByUsernameList, $userIds);
        $userIds = array_merge($userIdsByUsernameList, $userIds);
        if (OW::getUser()->isAuthenticated()) {
            $userIds[] = OW::getUser()->getId();
        }
        $userIds = array_unique($userIds);
        $userIds = array_values($userIds);
        $this->sharedData['usersIdList'] = $userIds;

        $this->sharedData['usersInfo'] = array(
            'avatars' => array(),
            'urls' => array(),
            'names' => array(),
            'roleLabels' => array()
        );
        $this->sharedData['usersInfo']['username'] = array();
        $cachedUserByUsername = array();

        $usersInfo = array();
        if ( !empty($userIds) )
        {
            $usersInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);

            foreach ( $usersInfo as $uid => $userInfo )
            {
                $this->sharedData['usersInfo']['avatars'][$uid] = $userInfo['src'];
                $this->sharedData['usersInfo']['urls'][$uid] = $userInfo['url'];
                $cachedUserByUsername[$userInfo['urlInfo']['vars']['username']] = $userInfo['title'];
                $this->sharedData['usersInfo']['names'][$uid] = $userInfo['title'];
                $this->sharedData['usersInfo']['roleLabels'][$uid] = array(
                    'label' => $userInfo['label'],
                    'labelColor' => $userInfo['labelColor']
                );
            }
        }
        $this->sharedData['cache']['users_info'] = $usersInfo;
        $this->sharedData['cache']['username'] = $cachedUserByUsername;


        if (FRMSecurityProvider::checkPluginActive('groups', true)) {
            $cachedUsersGroups = array();
            $usersRegisteredGroups = GROUPS_BOL_GroupUserDao::getInstance()->findGroupsByUserIds($userIds);
            foreach ($usersRegisteredGroups as $usersRegisteredGroup) {
                if (!isset($cachedUsersGroups[$usersRegisteredGroup->userId]) || !in_array($usersRegisteredGroup->groupId, $cachedUsersGroups[$usersRegisteredGroup->userId])) {
                    $cachedUsersGroups[$usersRegisteredGroup->userId][$usersRegisteredGroup->groupId] = $usersRegisteredGroup->groupId;
                }
            }
            $this->sharedData['cache']['users_groups'] = $cachedUsersGroups;
        }

        $attachmentsList = array();
        $attachmentIdList =  array_unique($attachmentIdList);
        if (!empty($attachmentIdList)) {
            $attachmentsList = BOL_AttachmentDao::getInstance()->findByIdList($attachmentIdList);
        }
        $attachmentDir = BOL_AttachmentService::getInstance()->getAttachmentsDir();
        $cachedAttachmentsList = array();
        $keyFiles = array();
        $secureFilePluginActive = OW::getUser()->isAuthenticated() && FRMSecurityProvider::checkPluginActive('frmsecurefileurl', true);
        foreach ($attachmentsList as $attachment) {
            $cachedAttachmentsList[$attachment->id] = $attachment;
            $filePathDir = $attachmentDir . $attachment->fileName;
            $filePath = OW::getStorage()->prepareFileUrlByPath($filePathDir);
            if ($secureFilePluginActive) {
                $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($filePath);
                if ($keyInfo['key'] != null) {
                    $keyFiles[] = $keyInfo['key'];
                }

                $thumbnailPath = UTIL_File::getCustomPath($filePathDir, 'userfiles-base-attachments-' . $attachment->fileName, 100, 100, 'min');
                $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($thumbnailPath);
                if ($keyInfo['key'] != null) {
                    $keyFiles[] = $keyInfo['key'];
                }

                $previewPath = UTIL_File::getCustomPath($filePathDir, 'userfiles-base-attachments-' . $attachment->fileName, 600, 600, 'min');
                $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($previewPath);
                if ($keyInfo['key'] != null) {
                    $keyFiles[] = $keyInfo['key'];
                }
            }
        }
        $this->sharedData['cache']['attachments'] = $cachedAttachmentsList;

        $cachedSecureFileKeyList = array();
        if ($secureFilePluginActive && sizeof($keyFiles) > 0) {
            $keyList = FRMSECUREFILEURL_BOL_Service::getInstance()->existUrlByKeyList($keyFiles);
            foreach ($keyList as $urlObject) {
                $cachedSecureFileKeyList[$urlObject->key] = $urlObject;
            }
            foreach ($keyFiles as $key) {
                if (!array_key_exists($key, $cachedSecureFileKeyList)) {
                    $cachedSecureFileKeyList[$key] = null;
                }
            }
            $this->sharedData['cache']['secure_files'] = $cachedSecureFileKeyList;
        }
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $mobileVersion=false;
        $otpForm=false;
        $otpEvent=OW_EventManager::getInstance()->trigger(new OW_Event('newsfeed.check.chat.form'));
        if( isset($otpEvent->getData()['showOtpForm']) && $otpEvent->getData()['showOtpForm']){
            $otpForm=true;
        }
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $mobileVersion=true;
        }

        if($otpForm && $mobileVersion && $this->sharedData['feedType']=='groups') {
            return;
        }
        $event = new OW_Event('newsfeed.after_status_component_addition', array('feedId' => $this->sharedData['feedId'], 'feedType' => $this->sharedData['feedType']));
        OW_EventManager::getInstance()->trigger($event);
        $data = $event->getData();
        if (isset($data) && is_array($data)) {
            if (isset($data['extra_component'])) {
                if($otpForm && $mobileVersion)
                {
                    OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('newsfeed')->getStaticCssUrl().'mobile_newsfeed.css');
                }
                $this->addComponent('extra_component', $data['extra_component']);
            }
            if (isset($data['options'])) {
                $this->assign('options', $data['options']);
            }
        }

    }


    public function setDisplayType( $type )
    {
        $this->displayType = $type;
    }

    /**
     * 
     * @param NEWSFEED_CLASS_Action $action
     * @param array $sharedData
     * @return NEWSFEED_CMP_FeedItem
     */
    protected function createItem( NEWSFEED_CLASS_Action $action, $sharedData )
    {
        return OW::getClassInstance("NEWSFEED_CMP_FeedItem", $action, $sharedData);
    }
    
    public function tplRenderItem( $params = array() )
    {
        $action = $this->feed[$params['action']];

        $cycle = array(
            'lastItem' => $params['lastItem']
        );

        $feedItem = $this->createItem($action, $this->sharedData);
        $feedItem->setDisplayType($this->displayType);

        return $feedItem->renderMarkup($cycle);
    }

    public function render()
    {
        $out = array();
        foreach ( $this->feed as $action )
        {
            $out[] = $action->getId();
        }

        $this->assign('feed', $out);
        OW_ViewRenderer::getInstance()->registerFunction('newsfeed_item', array( $this, 'tplRenderItem' ) );
        $out = parent::render();
	    OW_ViewRenderer::getInstance()->unregisterFunction('newsfeed_item');
	    return $out;
    }
}