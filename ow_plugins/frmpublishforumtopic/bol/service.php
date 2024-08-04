<?php
class FRMPUBLISHFORUMTOPIC_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }


    /**
     * @param $params
     * @return bool
     */
    private function checkAccessToGroup($params)
    {
        $isGroupModerator = false;
        if(FRMSecurityProvider::checkPluginActive('groups', true)) {
            if (isset($params['group'])) {
                $group = $params['group'];
            } else {
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($params['entityId']);
            }
            if (!isset($group)) {
                return $isGroupModerator;
            }
            $isGroupModerator = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        }
        return $isGroupModerator;
    }


    /**
     * @param OW_Event $event
     */
    public function onForumActionToolbarRender(OW_Event $event){
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['topicId']))
        {
            return;
        }
        if(!$this->checkDestinationAccess())
        {
            return;
        }
        if(isset($params['entityType']) && $params['entityType']=='groups') {
            if(!isset($params['entityId']))
            {
                return;
            }
            if (!$this->checkAccessToGroup($params)) {
                return;
            }
        }
        else{
             if(!$this->hasAccessToMainForumTopic($params['topicId']))
             {
                 return;
             }
        }
        /**
         * publish topic
         */
        $publishTopicCode = '';
        $frmSecuritymanagerEvent = OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId' => OW::getUser()->getId(), 'receiverId' => $params['topicId'], 'isPermanent' => true, 'activityType' => 'publish_topic')));
        if (isset($frmSecuritymanagerEvent->getData()['code'])) {
            $publishTopicCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        if(OW::getConfig()->getValue('frmpublishforumtopic','publish_destination')=='blog')
        {
            $url = OW::getRouter()->urlForRoute('post-save-new');
        }else{
            $url = OW::getRouter()->urlForRoute('entry-save-new');
        }
        $publishTopicUrl = OW::getRequest()->buildUrlQueryString($url
            , array('code' => $publishTopicCode, 'topicId' => $params['topicId'], 'entityId' => isset($params['entityId']) ? $params['entityId'] : null, 'entityType' => isset($params['entityType'])? $params['entityType'] : null));
        $data['extraToolbarActions']['publishTopic']['href'] = $publishTopicUrl;
        $data['extraToolbarActions']['publishTopic']['id'] = 'publishTopic_' . $params['topicId'];
        $data['extraToolbarActions']['publishTopic']['label'] = OW::getLanguage()->text('frmpublishforumtopic', 'publish_topic');
        $event->setData($data);
    }

    /**
     * @param OW_Event $event
     * @throws Redirect404Exception
     */
    public function onAddFormRender(OW_Event $event)
    {
        if(!isset($_GET['topicId']) )
        {
            return;
        }
        $params = $event->getParams();
        $data = $event->getData();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                return;
            }
            $userId = OW::getUser()->getId();
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => $userId,'receiverId' => $userId, 'code'=>$code,'activityType'=>'publish_topic')));
        }
        if(!isset($_GET['topicId']) )
        {
            return;
        }
        if(!$this->checkDestinationAccess())
        {
            return;
        }
        $params['topicId'] = $_GET['topicId'];
        if(isset($_GET['entityType']) && $_GET['entityType']=='groups')
        {
            if(!isset($_GET['entityId']))
            {
                return;
            }else
            {
                $params['entityId'] = $_GET['entityId'];
                $params['entityType']='groups';
                if(!$this->checkAccessToGroup($params))
                {
                    return;
                }
            }
        }else if(!$this->hasAccessToMainForumTopic( $params['topicId'])){
            return;
        }

        $topicDto = FORUM_BOL_ForumService::getInstance()->findTopicById($_GET['topicId']);
        $data['titleValue'] = $topicDto->title;
        $titleWords = explode(" ", $topicDto->title);
        foreach ($titleWords as $titleWord)
        {
            if(strlen($titleWord)>4)
            {
                $data['tagsValue'] []=$titleWord;
            }
        }
        $firstTopicPost = FORUM_BOL_ForumService::getInstance()->findTopicFirstPost($topicDto->id);
        $data['firstPostValue'] = $firstTopicPost->text;
        $data['authorUrl'] = BOL_UserService::getInstance()->getUserUrl((int)$topicDto->userId);
        $data['authorDisplayName'] = BOL_UserService::getInstance()->getDisplayName((int)$topicDto->userId);
        $data['startDate'] = UTIL_DateTime::formatSimpleDate($firstTopicPost->createStamp,true);
        if(isset($topicDto->closeTime))
        {
            $data['closeDate'] = UTIL_DateTime::formatSimpleDate($topicDto->closeTime,true);
        }

        $data['topicUrl']=OW::getRouter()->urlForRoute('topic-default', array('topicId' => $params['topicId']));
        $postCount = FORUM_BOL_ForumService::getInstance()->findTopicPostCount($params['topicId']);
        $topicPosts = FORUM_BOL_PostDao::getInstance()->findTopicPostList($params['topicId'],0,$postCount);
        $authors = array();
        foreach ($topicPosts as $post)
        {
            if ( !in_array($post->userId, $authors) )
            {
                array_push($authors, $post->userId);
                if(array_key_exists("mentions", $data)){
                    $data['mentions'] =  $data['mentions'] .' @'.BOL_UserService::getInstance()->getUserName($post->userId);
                } else{
                    $data['mentions'] =  ' @'.BOL_UserService::getInstance()->getUserName($post->userId);
                }

            }
        }


        $data['firstPartBody'] = $this->addStrongTag(OW::getLanguage()->text("frmpublishforumtopic","topic_description"));
        $data['firstPartBody'] .=$data["firstPostValue"];
        $data['firstPartBody'] = $this->addPTag($data["firstPartBody"]);

        $data['secondPartBody'] = $this->addStrongTag(OW::getLanguage()->text("frmpublishforumtopic","topic_author"),true);
        $data['secondPartBody'] .='<a href="'.$data["authorUrl"].'" target="_blank">'.$data["authorDisplayName"].'</a>';
        $data['secondPartBody'] =$this->addPTag($data["secondPartBody"]);

        $data['thirdPartBody'] = $this->addStrongTag(OW::getLanguage()->text("frmpublishforumtopic","topic_startDate"),true);
        $data['thirdPartBody'] .=$data["startDate"];
        $data['thirdPartBody'] =$this->addPTag($data["thirdPartBody"]);

        $data['fourthPartBody']='';
        if(isset($data['closeDate'])) {
            $data['fourthPartBody'] = $this->addStrongTag(OW::getLanguage()->text("frmpublishforumtopic", "topic_endDate"), true);
            $data['fourthPartBody'] .= $data["closeDate"];
            $data['fourthPartBody'] = $this->addPTag($data["fourthPartBody"]);
        }
        $data['fifthPartBody']='';
        if(isset($topicDto->conclusionPostId)) {
            $conclusionPost = FORUM_BOL_ForumService::getInstance()->findPostById($topicDto->conclusionPostId);
            $data['conclusionPost'] = $conclusionPost->text;
            $data['conclusionPostAuthorUrl'] = BOL_UserService::getInstance()->getUserUrl((int)$conclusionPost->userId);
            $data['conclusionPostAuthorDisplayName'] = BOL_UserService::getInstance()->getDisplayName((int)$conclusionPost->userId);

            $data['fifthPartBody'] = OW::getLanguage()->text("frmpublishforumtopic", "topic_conclusion_post");
            $data['fifthPartBody'] .= ' ' . OW::getLanguage()->text("frmpublishforumtopic", "topic_conclusion_post_author");
            $data['fifthPartBody'] .= ' <a href="' . $data['conclusionPostAuthorUrl'] . '" target="_blank">' . $data['conclusionPostAuthorDisplayName'] . '</a>: ';
            $data['fifthPartBody'] = $this->addStrongTag($data['fifthPartBody']);
            $data['fifthPartBody'] .= $data['conclusionPost'];
            $data['fifthPartBody'] = $this->addPTag($data["fifthPartBody"]);
        }
        $data['sixthPartBody'] = $this->addStrongTag(OW::getLanguage()->text("frmpublishforumtopic","topic_url"));
        $data['sixthPartBody'] .= '<a href="'.$data['topicUrl'].'" target="_blank"> '. OW::getLanguage()->text("frmpublishforumtopic","view_topic") .'</a>';
        $data['sixthPartBody'] =$this->addPTag($data["sixthPartBody"]);

        $data['seventhPartBody']='';
        if(isset( $data['mentions'])) {
            $data['seventhPartBody'] = $this->addStrongTag(OW::getLanguage()->text("frmpublishforumtopic", "topic_reviewers"));
            $data['seventhPartBody'] .= $data['mentions'];
            $data['seventhPartBody'] = $this->addPTag($data["seventhPartBody"]);
        }

        $data['bodyValue'] = $data['firstPartBody'] . $data['secondPartBody'].$data['thirdPartBody'].$data['fourthPartBody'].$data['fifthPartBody'].$data['sixthPartBody'].$data['seventhPartBody'];

        $event->setData(array('titleValue'=> $data['titleValue'], 'bodyValue' => $data['bodyValue'], 'tagsValue'=>  $data['tagsValue'] ));

    }


    /**
     * @param $string
     * @param bool $spaceAfter
     * @return string
     */
    private function addPTag($string,$spaceAfter=false)
    {
        if($spaceAfter)
        {
            $string = '<p>' . $string . '</p> ';
        }else {
            $string = '<p>' . $string . '</p>';
        }
        return $string;
    }


    /**
     * @param $string
     * @param bool $spaceAfter
     * @return string
     */
    private function addStrongTag($string,$spaceAfter=false)
    {
        if($spaceAfter)
        {
            $string= '<strong>'.$string.'</strong> ';
        }else{
            $string= '<strong>'.$string.'</strong>';
        }

        return $string;
    }

    /**
     * @param $topicId
     * @return bool
     */
    private function hasAccessToMainForumTopic($topicId)
    {
        $userId = OW::getUser()->getId();
        $topicDto = FORUM_BOL_ForumService::getInstance()->findTopicById($topicId);
        $isOwner = ( $topicDto->userId == $userId ) ? true : false;

        if($isOwner)
        {
            return true;
        }
        $canView = OW::getUser()->isAuthorized('forum', 'view');

        $isModerator = OW::getUser()->isAuthorized('forum');

        if(!$canView && !$isModerator)
        {
            return false;
        }
        $forumGroup = FORUM_BOL_ForumService::getInstance()->findGroupById($topicDto->groupId);

        if ( $forumGroup->isPrivate )
        {
            if ( !$userId )
            {
                return false;
            }
            else if ( !$isModerator )
            {
                if ( !FORUM_BOL_ForumService::getInstance()->isPrivateGroupAvailable($userId, json_decode($forumGroup->roles)) )
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    private function checkDestinationAccess()
    {
        $destination = OW::getConfig()->getValue('frmpublishforumtopic','publish_destination');
        $canAdd=false;
        if($destination=='blog')
        {
            if (!FRMSecurityProvider::checkPluginActive('blogs', true))
            {
                return false;
            }
            $canAdd = OW::getUser()->isAuthorized('blogs','add') || OW::getUser()->isAdmin();
        }
        else if($destination=='news')
        {
            if (!FRMSecurityProvider::checkPluginActive('frmnews', true))
            {
                return false;
            }
            $canAdd = OW::getUser()->isAuthorized('frmnews','add') || OW::getUser()->isAdmin();
        }
        return $canAdd;
    }
}