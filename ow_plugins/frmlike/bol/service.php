<?php
final class FRMLIKE_BOL_Service
{
    const FRMLIKE_DISPLAY_CTRL = "display_controls";
    const FRMLIKE_NEGATIVE_ACTION = "negative_action";
    const FRMLIKE_ACTION_LEVEL = "action_level";
    const FRMLIKE_OPACITY_LEVEL = "opacity_level";
    const FRMLIKE_FADE_TIME = "fade_time";
    const FRMLIKE_POSITIVE_USER_LIST = "allow_positive_user_list";
    const FRMLIKE_NEGATIVE_USER_LIST = "allow_negative_user_list";
    const FRMLIKE_COMMMON_USER_LIST = "allow_common_user_list";
    const FRMLIKE_USE_LIKE_DISLIKE_FOR = "use_like_dislike_for";
    const VAL_NEGATIVE_ACTION_FADE = 1;
    const VAL_NEGATIVE_ACTION_HIDE = 2;
    const VAL_ENTITY_TYPE_COMMENT = "frmlike_comment";
    const VAL_ENTITY_TYPE_USER = "frmlike_user";
    const VALID_ENTITY_TYPE = array('user-status','groups-status','blog-post','news-entry','story');
    private $likeInfo;
    private $likeLimit;
    private $fade;
    private $hide;
    private $opacity;
    private $clickMsg;
    private $userLikeInfo;
    private $entityType;

    /**
     * @var FRMLIKE_BOL_Service
     */
    private $voteService;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->voteService = BOL_VoteService::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var FRMLIKE_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMLIKE_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getLikeInfoForList( $idList, $entityType, $params = array())
    {
        $this->entityType = $entityType;

        $tempData = $this->getInfoForList($idList, $entityType, $params);
        $this->likeInfo = array();

        if ( OW::getUser()->isAuthenticated() )
        {
            $this->userLikeInfo = array();
        }

        /* @var $item BOL_Vote */
        foreach ( $tempData as $item )
        {
            if ( !isset($this->likeInfo[$item->getEntityId()]) )
            {
                $this->likeInfo[$item->getEntityId()] = array('id' => $item->getEntityId(), 'sum' => 0, 'count' => 0, 'up' => 0, 'upUserId' => array(), 'down' => 0, 'downUserId' => array());
            }

            $this->likeInfo[$item->getEntityId()]['sum'] += $item->getVote();
            $this->likeInfo[$item->getEntityId()]['count'] ++;

            if ( $item->getVote() > 0 )
            {
                $this->likeInfo[$item->getEntityId()]['up'] ++;
                $this->likeInfo[$item->getEntityId()]['upUserId'][] = $item->getUserId();
            }
            else
            {
                $this->likeInfo[$item->getEntityId()]['down'] ++;
                $this->likeInfo[$item->getEntityId()]['downUserId'][] = $item->getUserId();
            }

            if ( OW::getUser()->isAuthenticated() && OW::getUser()->getId() == $item->getUserId() )
            {
                $this->userLikeInfo[$item->getEntityId()] = $item;
            }
        }
        return(array($this->likeInfo,$this->userLikeInfo));
    }

    /**
     * @param $idList
     * @param $entityType
     * @param array $params
     * @return array
     */
    private function getInfoForList( $idList, $entityType, $params = array())
    {
        $voteDao = BOL_VoteDao::getInstance();

        if (isset($params['params']->cachedParams['comments_votes'])) {
            $commentVotes = $params['params']->cachedParams['comments_votes'];
            $votes = array();
            foreach ($commentVotes as $commentVote) {
                if ($commentVote->entityType == $entityType && in_array($commentVote->entityId, $idList)) {
                    $votes[] = $commentVote;
                }
            }
            return $votes;
        } else if ( method_exists($voteDao, "getEntityTypeVotes") )
        {
            return $voteDao->getEntityTypeVotes($idList, $entityType);
        }

        if ( empty($idList) || empty($entityType) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual(BOL_VoteDao::ENTITY_TYPE, $entityType);
        $example->andFieldInArray(BOL_VoteDao::ENTITY_ID, $idList);
        $example->andFieldEqual(BOL_VoteDao::ACTIVE, 1);
        return $voteDao->findListByExample($example);
    }

    public function addNewsfeedCommentLikeComponent(OW_Event $event)
    {
        $params=$event->getParams();
        if(isset($params['value']) && isset($params['cmItemArray']) && isset($params['commentIdList']) && isset($params['entityType'])) {
            $value=$params['value'];
            $cmItemArray=$params['cmItemArray'];
            $commentIdList = $params['commentIdList'];
            $entityType = $params['entityType'];
            if(!in_array($entityType,self::VALID_ENTITY_TYPE))
            {
                return;
            }
            list($votesInfo, $userVoteInfo) = FRMLIKE_BOL_Service::getInstance()->getLikeInfoForList($commentIdList, 'frmlike-' . $entityType, $params);
            $totalVotes = isset($votesInfo[$value->getId()]) ? $votesInfo[$value->getId()] : array();
            $myVote = ($userVoteInfo === null ? null : (isset($userVoteInfo[$value->getId()]) ? $userVoteInfo[$value->getId()] : 0));

            $parentClass = "ow_comments_item";
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
            if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
                $parentClass = "owm_newsfeed_comment_item";
            }
            $voteCmp = new FRMLIKE_CMP_Action($value->getId(), 'frmlike-' . $entityType, $totalVotes, $myVote, $value->getUserId(), $parentClass);
        } else if(isset($params['entityType']) && isset($params['entityId'])) {

            $entityType = $params['entityType'];
            $entityId = $params['entityId'];
            /** @var NEWSFEED_CLASS_Action $value */
            $value = $params['value'];

            list($votesInfo, $userVoteInfo) = FRMLIKE_BOL_Service::getInstance()->getLikeInfoForList([$entityId], $entityType, $params);
            $totalVotes = isset($votesInfo[$entityId]) ? $votesInfo[$entityId] : array();
            $myVote = ($userVoteInfo === null ? null : (isset($userVoteInfo[$entityId]) ? $userVoteInfo[$entityId] : 0));

            $parentClass = "ow_comments_item";
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
            if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
                $parentClass = "owm_newsfeed_comment_item";
            }

            $voteCmp = new FRMLIKE_CMP_Action($entityId, $entityType, $totalVotes, $myVote, $value->getUserId(), $parentClass);
        }

        if (isset($voteCmp)) {
            $cmItemArray['voteCmp'] = $voteCmp->render();
            $event->setData(array('cmItemArray'=>$cmItemArray));
        }
    }

    public function setLike($commentId,$entityType,$userId)
    {

        if(!in_array($entityType,self::VALID_ENTITY_TYPE))
        {
            return json_encode(array('result'=>null, 'error'=>true, 'allowCommentLike'=>false));
        }
        $entityType = 'frmlike-'.$entityType;
        $voteService = BOL_VoteService::getInstance();
        $voteDto = $voteService->findUserVote($commentId, $entityType, $userId);
        if(!isset($voteDto))
        {
            $voteDto = new BOL_Vote();
            $voteDto->setUserId($userId);
            $voteDto->setEntityType($entityType);
            $voteDto->setEntityId($commentId);
            $voteDto->setVote(1);
            $voteDto->setTimeStamp(time());
            $voteService->saveVote($voteDto);
            $this->sendNotification($commentId,$entityType,$userId,1);
            return json_encode(array('result'=>$voteDto));
        }
        return json_encode(array('result'=>false));
    }

    public function removeLike($commentId,$entityType,$userId)
    {
        $entityType = 'frmlike-'.$entityType;
        $voteService = BOL_VoteService::getInstance();
        $voteDto = $voteService->findUserVote($commentId, $entityType, $userId);
        if(isset($voteDto))
        {
            $voteService->delete($voteDto);
            OW::getEventManager()->call('notifications.remove', array(
                'entityType' => $entityType,
                'entityId' => $commentId
            ));
            return json_encode(array('result'=>true));
        }
        return json_encode(array('result'=>false));
    }

    public function findUserLike($commentId,$entityType,$userId)
    {
        $entityType = 'frmlike-'.$entityType;
        $voteService = BOL_VoteService::getInstance();
        $voteDto = $voteService->findUserVote($commentId, $entityType, $userId);
        return $voteDto;
    }

    public function sendNotification($entityId,$entityType,$userId,$userVote)
    {
        $action = "frmlike-comment";
        $comment = BOL_CommentService::getInstance()->findComment($entityId);
        $ownerId=$comment->userId;
        $commentEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
        $entityData = explode('frmlike-', $entityType);
        $originalEntityType = $entityData[1];
        if($userId==$comment->userId)
        {
            return;
        }
        switch ($originalEntityType)
        {
            case 'user-status':
            case 'groups-status':
                if (FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                    $newsfeedAction = NEWSFEED_BOL_Service::getInstance()->findAction($originalEntityType, $commentEntity->entityId);
                    OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array('actionId' => $newsfeedAction->getId(), 'feedId' => null)));
                    $commentUrl = OW::getRouter()->urlForRoute('newsfeed_view_item', array('actionId' => $newsfeedAction->getId(), 'feedId' => null));
                }
                break;
            case 'blog-post':
                if (FRMSecurityProvider::checkPluginActive('blogs', true)) {
                    $post = PostService::getInstance()->findById($commentEntity->entityId);

                    if ($post === null) {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }
                    if ($post->isDraft() && $post->authorId != OW::getUser()->getId()) {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }
                    if ( !OW::getUser()->isAuthorized('blogs', 'view') )
                    {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }

                    if ( ( OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $post->getAuthorId() ) && !OW::getUser()->isAuthorized('blogs', 'view') )
                    {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }

                    /* Check privacy permissions */
                    if ( $post->authorId != OW::getUser()->getId() && !OW::getUser()->isAuthorized('blogs') )
                    {
                        $eventParams = array(
                            'action' => 'blogs_view_blog_posts',
                            'ownerId' => $post->authorId,
                            'viewerId' => OW::getUser()->getId()
                        );

                        OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                    }
                    /* */
                    $commentUrl = $commentUrl = OW::getRouter()->urlForRoute('post', array('id' => $commentEntity->entityId));
                }
                break;
            case 'news-entry':
                if (FRMSecurityProvider::checkPluginActive('frmnews', true)) {
                    $entry = EntryService::getInstance()->findById($commentEntity->entityId);
                    if ( $entry === null )
                    {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }
                    if ($entry->isDraft() && $entry->authorId != OW::getUser()->getId())
                    {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }
                    if ( !OW::getUser()->isAuthorized('frmnews', 'view')  && !OW::getUser()->isAdmin())
                    {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }
                    if ( ( OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $entry->getAuthorId() ) && !OW::getUser()->isAuthorized('frmnews', 'view') )
                    {
                        exit(json_encode(array("result" => false,"error"=>"404 Exception", "message" => "Error!")));
                    }
                    $commentUrl = $commentUrl = OW::getRouter()->urlForRoute('entry', array('id' => $commentEntity->entityId));
                }
                break;
            default:
                break;
        }
        $langKey = $userVote > 0 ? "notification_comment_like_string" : "notification_comment_dislike_string";
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $event = new OW_Event("notifications.add", array(
            "pluginKey" => 'frmlike',
            "entityType" => $entityType,
            "entityId" => $entityId,
            "action" => $action,
            "userId" => $ownerId,
            "time" => time()
        ), array(
            "avatar" => $avatars[$userId],
            "string" => array(
                "key" => "frmlike+" . $langKey,
                "vars" => array(
                    "displayName" => BOL_UserService::getInstance()->getDisplayName($userId),
                    "userUrl" => BOL_UserService::getInstance()->getUserUrl($userId),
                    "commentUrl" => isset($commentUrl) ? $commentUrl : OW_URL_HOME
                )
            ),
            "url" => isset($commentUrl) ? $commentUrl : OW_URL_HOME
        ));

        OW::getEventManager()->trigger($event);

    }

    public function getValidEntityTypes() {
        return self::VALID_ENTITY_TYPE;
    }



    public function getNewsfeedLikeTableName() {
        return OW_DB_PREFIX . 'newsfeed_like';
    }

    /**
     * get likes with pagination
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLikesFromNewsfeedLike($limit, $offset) {
        $query = "SELECT * FROM ". $this->getNewsfeedLikeTableName() ." LIMIT :offset, :limit;";
        $result = OW::getDbo()->queryForList($query, ['offset' => $offset, 'limit' => $limit]);
        return $result;
    }

    public function dropNewsfeedLikeTable() {
        $query = "DROP TABLE " . $this->getNewsfeedLikeTableName() ."; ";
        $result = OW::getDbo()->query($query);
        return $result;
    }

    /**
     * Deletes list of entities by id list. Returns affected rows
     *
     * @param array $idList
     * @return int
     */
    public function deleteByIdListFromNewsfeedLike( array $idList ) {
        $dbo = OW::getDbo();
        if ( $idList === null || count($idList) === 0 ) {
            return;
        }
        $sql = 'DELETE FROM ' . $this->getNewsfeedLikeTableName() . ' WHERE `id` IN(' . $dbo->mergeInClause($idList) . ')';

        return $dbo->delete($sql);
    }

    /**
     * Returns count of all rows
     *
     * @return array
     */
    public function countNewsfeedLikeTable() {
        $sql = 'SELECT COUNT(*) FROM ' . $this->getNewsfeedLikeTableName();

        return OW::getDbo()->queryForColumn($sql);
    }
}
