<?php
/**
 *  Comment Service.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_CommentService
{
    const CONFIG_COMMENTS_ON_PAGE = 'comments_on_page';
    const CONFIG_ALLOWED_TAGS = 'allowed_tags';
    const CONFIG_ALLOWED_ATTRS = 'allowed_attrs';
    const CONFIG_MB_COMMENTS_ON_PAGE = 'mb_comments_on_page';
    const CONFIG_MB_COMMENTS_COUNT_TO_LOAD = 'mb_comments_count_to_load';

    /**
     * @var BOL_CommentDao
     */
    private $commentDao;

    /**
     * @var BOL_CommentEntityDao;
     */
    private $commentEntityDao;

    /**
     * @var array
     */
    private $configs;

    /**
     * Singleton instance.
     *
     * @var BOL_CommentService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CommentService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->commentDao = BOL_CommentDao::getInstance();
        $this->commentEntityDao = BOL_CommentEntityDao::getInstance();

        $this->configs[self::CONFIG_COMMENTS_ON_PAGE] = 10;
        $this->configs[self::CONFIG_MB_COMMENTS_ON_PAGE] = 3;
        $this->configs[self::CONFIG_MB_COMMENTS_COUNT_TO_LOAD] = 10;
        //$this->configs[self::CONFIG_ALLOWED_TAGS] = array('a', 'b', 'i', 'span', 'u', 'strong', 'br');
        //$this->configs[self::CONFIG_ALLOWED_ATTRS] = array('style', 'href');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfigValue( $name )
    {
        if ( array_key_exists($name, $this->configs) )
        {
            return $this->configs[$name];
        }

        return null;
    }

    /**
     * Returns comments list for entity item.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $page
     * @return array
     */
    public function findCommentList( $entityType, $entityId, $page = null, $count = null )
    {
        $page = ( $page === null ) ? 1 : (int) $page;
        $count = ( (int) $count === 0 ) ? $this->configs[self::CONFIG_COMMENTS_ON_PAGE] : (int) $count;
        $first = ( $page - 1 ) * $count;

        $list = $this->commentDao->findCommentList($entityType, $entityId, $first, $count);
        foreach($list as $item){
            $item->message = urldecode($item->message);
        }
        return $list;
    }

    /**
     * Returns full comments list for entity item.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return array
     */
    public function findFullCommentList( $entityType, $entityId )
    {
        $list = $this->commentDao->findFullCommentList($entityType, $entityId);
        foreach($list as $item){
            $item->message = urldecode($item->message);
        }
        return $list;
    }

    /**
     * Returns comments count for entity item.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findCommentCount( $entityType, $entityId )
    {
        return (int) $this->commentDao->findCommentCount($entityType, $entityId);
    }

    /**
     * Returns entity item comment pages count.
     *
     * @param integer $entityId
     * @param string $entityType
     * @return integer
     */
    public function findCommentPageCount( $entityType, $entityId, $count = null )
    {
        $count = ( (int) $count === 0 ) ? $this->configs[self::CONFIG_COMMENTS_ON_PAGE] : (int) $count;
        $commentCount = $this->findCommentCount($entityType, $entityId);

        if ( $commentCount === 0 )
        {
            return 1;
        }

        return ( ( $commentCount - ( $commentCount % $count ) ) / $count ) + ( ( $commentCount % $count > 0 ) ? 1 : 0 );
    }

    /**
     * Returns comment item.
     *
     * @param integer $id
     * @return BOL_Comment
     */
    public function findComment( $id )
    {
        $item = $this->commentDao->findById($id);
        if(isset($item)) {
            $item->message = urldecode($item->message);
        }
        return $item;
    }
    
    public function findCommentListByIds( array $commentIds )
    {
        $list = $this->commentDao->findByIdList($commentIds);
        foreach($list as $item){
            $item->message = urldecode($item->message);
        }
        return $list;
    }

    /**
     * @param integer $id
     * @return BOL_CommentEntity
     */
    public function findCommentEntityById( $id )
    {
        return $this->commentEntityDao->findById($id);
    }

    /**
     * @param array $ids
     * @return BOL_CommentEntity
     */
    public function findCommentEntityByIds( array $ids )
    {
        return $this->commentEntityDao->findByIdList($ids);
    }

    /**
     * @param $entityType
     * @param $entityId
     * @param $pluginKey
     * @param $userId
     * @param $message
     * @param null $attachment
     * @param null $replyId
     * @param null $replyUserId
     * @return BOL_Comment
     * @throws Redirect404Exception
     */
    public function addComment( $entityType, $entityId, $pluginKey, $userId, $message, $attachment = null, $replyId = null, $replyUserId = null )
    {
        if (in_array($entityType, array('groups-status', 'groups-join', 'group', 'groups-leave', 'groups-add-file'))) {
            if (FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
                $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
                if ($action != null && isset($action->data)) {
                    $actionData = json_decode($action->data);
                    if (isset($actionData->contextFeedType) && $actionData->contextFeedType == 'groups') {
                        $isChannel = false;
                        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load',
                            array('groupId' => $actionData->contextFeedId)));
                        if(isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel'] == true) {
                            $isChannel = true;
                        }
                        if ($isChannel) {
                            return null;
                        }
                    }
                }
            }
        }
        $commentEntity = $this->commentEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);

        if ( $commentEntity === null )
        {
            $commentEntity = new BOL_CommentEntity();
            $commentEntity->setEntityType(trim($entityType));
            $commentEntity->setEntityId((int) $entityId);
            $commentEntity->setPluginKey($pluginKey);

            $this->commentEntityDao->save($commentEntity);
        }

        if ( $attachment !== null && strlen($message) == 0 )
        {
            $message = '';
        }
        else
        {
            $message = UTIL_HtmlTag::autoLink(nl2br(htmlspecialchars($message)));
        }

        $comment = new BOL_Comment();
        $comment->setCommentEntityId($commentEntity->getId());
        $comment->setMessage(trim(urlencode($message)));
        $comment->setUserId($userId);
        $comment->setCreateStamp(time());
        $comment->setReplyId($replyId);
        $comment->setReplyUserId($replyUserId);

        if ( $attachment !== null )
        {
            $comment->setAttachment($attachment);
        }

        $this->commentDao->save($comment);

        $action = null;
        if (FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            $action = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
        }

        // trigger event comment add
        $event = new OW_Event('base_add_comment', array(
            'entityType' => $entityType,
            'entityId' => $entityId,
            'userId' => $userId,
            'action' => $action,
            'commentId' => $comment->getId(),
            'commentMessage' => $message,
            'pluginKey' => $pluginKey,
            'attachment' => json_decode($attachment, true)
        ));

        OW::getEventManager()->trigger($event);

        return $comment;
    }

    public function updateComment( BOL_Comment $comment )
    {
        $this->commentDao->save($comment);
    }

    /**
     * Deletes comment item.
     *
     * @param integer $id
     */
    public function deleteComment( $id )
    {
        $replies = $this->commentDao->findFullReplyList($id);
        $replyIds = array();
        if (!empty($replies)){
            foreach ($replies as $reply){
                $replyIds[] = $reply->getId();
            }
            $this->commentDao->deleteByIdList($replyIds);
        }
        $this->commentDao->deleteById($id);
        OW::getLogger()->writeLog(OW_Log::INFO, 'delete_comment', ['actionType'=>OW_Log::DELETE, 'enType'=>'comment', 'enId'=>$id]);
    }
    
    public function deleteCommentListByIds( $idList )
    {
        foreach ($idList as $id){
            $replies = $this->commentDao->findFullReplyList($id);
            $replyIds = array();
            if (!empty($replies)){
                foreach ($replies as $reply){
                    $replyIds[] = $reply->getId();
                }
                $this->commentDao->deleteByIdList($replyIds);
            }
        }
        $this->commentDao->deleteByIdList($idList);
    }

    public function deleteCommentEntity( $id )
    {
        $this->commentEntityDao->deleteById($id);
    }

    /**
     * Deletes entity comments.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityComments( $entityType, $entityId )
    {
        $commentEntity = $this->commentEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);

        if ( $commentEntity === null )
        {
            return;
        }
        $comments = $this->findFullCommentList($entityType, $entityId);
        if($commentEntity->pluginKey=='frmnews' || $commentEntity->pluginKey=='video' || $commentEntity->pluginKey=='photo' || $commentEntity->pluginKey=='blogs' || $commentEntity->pluginKey=='event') {
            foreach ($comments as $comment) {
                if($commentEntity->pluginKey=='frmnews'){
                    $entityTypeComment = 'news-add_comment';
                }else if($commentEntity->pluginKey=='video'){
                    $entityTypeComment = 'video-add_comment';
                }else if($commentEntity->pluginKey=='photo'){
                    $entityTypeComment = 'photo-add_comment';
                }
                else if($commentEntity->pluginKey=='blogs'){
                    $entityTypeComment = 'blogs-add_comment';
                }
                else if($commentEntity->pluginKey=='event'){
                    $entityTypeComment = 'event';
                }

                OW::getEventManager()->call('notifications.remove', array(
                    'entityType' => $entityTypeComment,
                    'entityId' => $comment->id
                ));
            }
        }
        else {
            foreach ($comments as $comment) {
                // event to remove other notifications
                $event = new OW_Event('base_delete_comment', array(
                    'entityType' => $commentEntity->getEntityType(),
                    'entityId' => $commentEntity->getEntityId(),
                    'userId' => $comment->getUserId(),
                    'commentId' => $comment->getId(),
                    'comment' => $comment,
                    'pluginKey' => $commentEntity->pluginKey
                ));
                OW::getEventManager()->trigger($event);
            }
        }

        $this->commentDao->deleteByCommentEntityId($commentEntity->getId());
        $this->commentEntityDao->delete($commentEntity);
    }

    /**
     * @param string $entityType
     * @param integer $entityId
     * @param boolean $status
     */
    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $commentEntity = $this->commentEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);

        if ( $commentEntity === null )
        {
            return;
        }

        $commentEntity->setActive(($status ? 1 : 0));
        $this->commentEntityDao->save($commentEntity);
    }

    /**
     * @param integer $entityType
     * @param array $idList
     * @return array
     */
    public function findCommentCountForEntityList( $entityType, array $idList )
    {
        $commentCountArray = $this->commentDao->findCommentCountForEntityList($entityType, $idList);

        $commentCountAssocArray = array();

        $resultArray = array();

        foreach ( $commentCountArray as $value )
        {
            $commentCountAssocArray[$value['id']] = $value['commentCount'];
        }

        foreach ( $idList as $value )
        {
            $resultArray[$value] = ( array_key_exists($value, $commentCountAssocArray) ) ? $commentCountAssocArray [$value] : 0;
        }

        return $resultArray;
    }

    /**
     * Finds most commented entities.
     *
     * @param string $entityType
     * @param integer $first
     * @param integer $count
     * @return array<BOL_CommentEntity>
     */
    public function findMostCommentedEntityList( $entityType, $first, $count )
    {
        $resultArray = $this->commentDao->findMostCommentedEntityList($entityType, $first, $count);

        $resultList = array();

        foreach ( $resultArray as $item )
        {
            $resultList[$item['id']] = $item;
        }

        return $resultList;
    }

    /**
     * Finds comments count for entity type.
     *
     * @param string $entityType
     * @return integer
     */
    public function findCommentedEntityCount( $entityType )
    {
        return $this->commentEntityDao->findCommentedEntityCount($entityType);
    }

    /**
     * Deletes all user comments.
     *
     * @param integer $userId
     */
    public function deleteUserComments( $userId )
    {
        $this->commentDao->deleteByUserId($userId);
    }

    /**
     * Deletes comments for provided entity type.
     *
     * @param string $entityType
     */
    public function deleteEntityTypeComments( $entityType )
    {
        $entityType = trim($entityType);
        $this->commentDao->deleteEntityTypeComments($entityType);
        $this->commentEntityDao->deleteByEntityType($entityType);
    }

    /**
     * Deletes all plugin entities comments.
     *
     * @param string $pluginKey
     */
    public function deletePluginComments( $pluginKey )
    {
        $pluginKey = trim($pluginKey);
        $this->commentDao->deleteByPluginKey($pluginKey);
        $this->commentEntityDao->deleteByPluginKey($pluginKey);
    }

    /**
     * Finds comment entity object for provided entity type and id.
     *
     * @param string $entityType
     * @param integer $entityId
     * @return BOL_CommentEntity
     */
    public function findCommentEntity( $entityType, $entityId )
    {
        return $this->commentEntityDao->findByEntityTypeAndEntityId($entityType, $entityId);
    }

    public function findBatchCommentsData( array $items )
    {
        if ( empty($items) )
        {
            return array();
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            $currentUserInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
        }

        $resultArray = array('_static' => array());
        $creditsParams = array();

        foreach ( $items as $item )
        {
            if ( !isset($resultArray[$item['entityType']]) )
            {
                $resultArray[$item['entityType']] = array();
            }

            $resultArray[$item['entityType']][$item['entityId']] = array('commentsCount' => 0, 'countOnPage' => $item['countOnPage'], 'commentsList' => array());
            $creditsParams[$item['pluginKey']] = array('add_comment');
        }

        if ( OW::getUser()->isAuthenticated() )
        {
            $userInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
        }

        // get comments count
        $result = $this->commentDao->findBatchCommentsCount($items);
        $entitiesForList = array();

        foreach ( $result as $item )
        {
            $resultArray[$item['entityType']][$item['entityId']]['commentsCount'] = (int) $item['count'];
            $entitiesForList[] = array('entityType' => $item['entityType'], 'entityId' => $item['entityId'], 'countOnPage' => $resultArray[$item['entityType']][$item['entityId']]['countOnPage']);
        }

        // get comments list
        $result = $this->commentDao->findBatchCommentsList($entitiesForList);

        $batchUserIdList = array();
        foreach ( $result as $item )
        {
            $item->message = urldecode($item->message);
            $resultArray[$item->entityType][$item->entityId]['commentsList'][] = $item;
            $batchUserIdList[] = $item->getUserId();
        }

        $resultArray['_static']['avatars'] = BOL_AvatarService::getInstance()->getDataForUserAvatars(array_unique($batchUserIdList));

        if ( OW::getUser()->isAuthenticated() )
        {
            $resultArray['_static']['currentUserInfo'] = $currentUserInfo[OW::getUser()->getId()];
        }

        $eventParams = array('actionList' => $creditsParams);
        $resultArray['_static']['credits'] = OW::getEventManager()->call('usercredits.batch_check_balance_for_action_list', $eventParams);

        return $resultArray;
    }

    /**
     * @param string $entityType
     * @param int $entityId
     * @return string
     */
    public function generateAttachmentUid( $entityType, $entityId )
    {
        return UTIL_HtmlTag::generateAutoId($entityType . "_" . $entityId);
    }

    /**
     * Returns replies list for comment id.
     *
     * @param string $commentId
     * @param integer $page
     * @param integer $count
     * @return array
     */
    public function findReplyList( $commentId, $page = null, $count = null )
    {
        $page = ( $page === null ) ? 1 : (int) $page;
        $count = ( (int) $count === 0 ) ? $this->configs[self::CONFIG_COMMENTS_ON_PAGE] : (int) $count;
        $first = ( $page - 1 ) * $count;
        $list = $this->commentDao->findReplyList($commentId, $first, $count);
        foreach($list as $item){
            $item->message = urldecode($item->message);
        }
        return $list;
    }

    /**
     * Returns replies count for comment id.
     *
     * @param integer $commentId
     * @return integer
     */
    public function findReplyCount( $commentId )
    {
        return (int) $this->commentDao->findreplyCount($commentId);
    }

    /**
     * Returns reply pages count.
     *
     * @param integer $commentId
     * @return integer
     */
    public function findReplyPageCount( $commentId, $count = null )
    {
        $count = ( (int) $count === 0 ) ? $this->configs[self::CONFIG_COMMENTS_ON_PAGE] : (int) $count;
        $commentCount = $this->$commentId();

        if ( $commentCount === 0 )
        {
            return 1;
        }

        return ( ( $commentCount - ( $commentCount % $count ) ) / $count ) + ( ( $commentCount % $count > 0 ) ? 1 : 0 );
    }
}
