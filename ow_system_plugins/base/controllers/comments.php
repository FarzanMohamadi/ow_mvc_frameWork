<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Comments extends OW_ActionController
{
    /**
     * @var BOL_CommentService
     */
    private $commentService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $this->commentService = BOL_CommentService::getInstance();
    }

    public function addComment()
    {
        $errorMessage = false;
        $isMobile = !empty($_POST['isMobile']) && (bool) $_POST['isMobile'];
        $params = $this->getParamsObject();

        if ( empty($_POST['commentText']) && empty($_POST['attachmentInfo']) && empty($_POST['oembedInfo']) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_required_validator_message');
        }
        else if ( !OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment')  && !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized($params->getPluginKey()))
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
            $errorMessage = $status['msg'];
        }
        else if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $params->getOwnerId()) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
        }

        $checkReplyPostCommentParams = OW::getEventManager()->trigger(new OW_Event('frmcommentplus.check_reply_post_comment_request_params'));
        if(isset($checkReplyPostCommentParams->getData()['errorMessage'])){
            $errorMessage = $checkReplyPostCommentParams->getData()['errorMessage'];
        }


        if ( $errorMessage )
        {
            exit(json_encode(array('error' => $errorMessage)));
        }

        $commentText = empty($_POST['commentText']) ? '' : trim($_POST['commentText']);
        $replyId = null;
        $replyUserId = null;
        if (isset($_POST['replyId']) && (int)$_POST['replyId'] > 0)
            $replyId = $_POST['replyId'];
        if (isset($_POST['replyUserId']) && (int)$_POST['replyUserId'] > 0)
            $replyUserId = $_POST['replyUserId'];
        $attachment = null;

        if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() && !$isMobile )
        {
            if ( !empty($_POST['attachmentInfo']) )
            {
                $tempArr = json_decode($_POST['attachmentInfo'], true);
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $tempArr['url'])));
                if(isset($stringRenderer->getData()['string'])){
                    $tempArr['url'] = $stringRenderer->getData()['string'];
                }
                OW::getEventManager()->call('base.attachment_save_image', array('uid' => $tempArr['uid'], 'pluginKey' => $tempArr['pluginKey']));
                $tempArr['href'] = $tempArr['url'];
                $tempArr['type'] = 'photo';
                $attachment = json_encode($tempArr);
            }
            else if ( !empty($_POST['oembedInfo']) )
            {
                $tempArr = json_decode($_POST['oembedInfo'], true);
                // add some actions
                $attachment = json_encode($tempArr);
            }
        }

        $commentObject = $this->commentService->addComment($params->getEntityType(), $params->getEntityId(), $params->getPluginKey(), OW::getUser()->getId(), $commentText, $attachment, $replyId, $replyUserId);
        if ($commentObject == null) {
            $errorMessage = OW::getLanguage()->text('base', 'comments_add_auth_message');
            exit(json_encode(array('error' => $errorMessage)));
        }
//        BOL_AuthorizationService::getInstance()->trackAction($params->getPluginKey(), 'add_comment');
        if($params->getReplyCommentId() == null){
            $params->setIsReplyList(false);
        }
        if ( $isMobile )
        {
            $commentListCmp = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        }
        else
        {
            $commentListCmp = new BASE_CMP_CommentsList($params, $_POST['cid']);
        }

        exit(json_encode(array(
            'newAttachUid' => $this->commentService->generateAttachmentUid($params->getEntityType(), $params->getEntityId()),
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'commentList' => $commentListCmp->render(),
            'onloadScript' => OW::getDocument()->getOnloadScript(),
            'commentCount' => $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId())
                )
            )
        );
    }

    public function getCommentList()
    {
        $params = $this->getParamsObject();
        if (isset($_POST['replyCommentId']) && $_POST['replyCommentId'] > 0){
            $params->setIsReplyList(true);
            $params->setReplyCommentId($_POST['replyCommentId']);
        }
        $page = ( isset($_POST['page']) && (int) $_POST['page'] > 0) ? (int) $_POST['page'] : 1;
        $commentsList = new BASE_CMP_CommentsList($params, $_POST['cid'], $page);
        exit(json_encode(array(
            'onloadScript' => OW::getDocument()->getOnloadScript(),
            'commentList' => $commentsList->render(),
            'commentCount' => $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId())
        )));
    }

    public function getMobileCommentList()
    {
        $params = $this->getParamsObject();
        $commentsList = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        if(!json_encode($commentsList->render()))
        {
            exit(json_encode(array('onloadScript' => OW::getDocument()->getOnloadScript(), 'commentList' => $this->utf8ize($commentsList->render()))));
        }
        else {
            exit(json_encode(array('onloadScript' => OW::getDocument()->getOnloadScript(), 'commentList' => $commentsList->render())));
        }
    }

    function utf8ize($mixed)
    {
        if(is_array($mixed))
        {
            foreach ($mixed as $key=>$value) {
                $mixed[$key]=$this->utf8ize($value);
            }
        }
        else if(is_string($mixed)){
            return mb_convert_encoding($mixed,"UTF-8","UTF-8");
        }
        return $mixed;
    }

    public function deleteComment()
    {
        $commentArray = $this->getCommentInfoForDelete();
        $comment = $commentArray['comment'];
        $commentEntity = $commentArray['commentEntity'];

        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            "entityType" => 'comment',
            "entityId" => $comment->getId()
        )));

        $this->deleteAttachmentFiles($comment);
        $this->commentService->deleteComment($comment->getId());
        $commentCount = $this->commentService->findCommentCount($commentEntity->getEntityType(), $commentEntity->getEntityId());

        if ( $commentCount === 0 )
        {
            $this->commentService->deleteCommentEntity($commentEntity->getId());
        }

        $event = new OW_Event('base_delete_comment', array(
            'entityType' => $commentEntity->getEntityType(),
            'entityId' => $commentEntity->getEntityId(),
            'userId' => $comment->getUserId(),
            'commentId' => $comment->getId(),
            'comment'=> $comment,
            'pluginKey'=>$commentEntity->pluginKey
        ));

        OW::getEventManager()->trigger($event);

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->getMobileCommentList();
        }else {
            $this->getCommentList();
        }
    }

    public function deleteCommentAtatchment()
    {
        /* @var $comment BOL_Comment */
        $commentArray = $this->getCommentInfoForDelete();
        $comment = $commentArray['comment'];
        $this->deleteAttachmentFiles($comment);

        if ( !trim($comment->getMessage()) )
        {
            $this->commentService->deleteComment($comment->getId());
        }
        else
        {
            $comment->setAttachment(null);
            $this->commentService->updateComment($comment);
        }

        exit;
    }

    private function deleteAttachmentFiles( BOL_Comment $comment )
    {
        // delete attachments
        $attch = $comment->getAttachment();

        if ( $attch !== null )
        {
            $tempArr = json_decode($attch, true);

            if ( !empty($tempArr['uid']) && !empty($tempArr['pluginKey']) )
            {
                BOL_AttachmentService::getInstance()->deleteAttachmentByBundle($tempArr['pluginKey'], $tempArr['uid']);
            }
        }
    }

    private function getCommentInfoForDelete()
    {
        if ( !isset($_POST['commentId']) || (int) $_POST['commentId'] < 1 )
        {
            echo json_encode(array('error' => OW::getLanguage()->text('base', 'comment_ajax_error')));
            exit();
        }

        /* @var $comment BOL_Comment */
        $comment = $this->commentService->findComment((int) $_POST['commentId']);
        /* @var $commentEntity BOL_CommentEntity */
        $commentEntity = $this->commentService->findCommentEntityById($comment->getCommentEntityId());

        if ( $comment === null || $commentEntity === null )
        {
            echo json_encode(array('error' => OW::getLanguage()->text('base', 'comment_ajax_error')));
            exit();
        }

        $params = $this->getParamsObject();

        $isModerator = OW::getUser()->isAuthorized($params->getPluginKey());
        $isOwnerAuthorized = (OW::getUser()->isAuthenticated() && $params->getOwnerId() !== null && (int) $params->getOwnerId() === (int) OW::getUser()->getId());
        $commentOwner = ( (int) OW::getUser()->getId() === (int) $comment->getUserId() );

        if ( !$isModerator && !$isOwnerAuthorized && !$commentOwner )
        {
            echo json_encode(array('error' => OW::getLanguage()->text('base', 'auth_ajax_error')));
            exit();
        }

        return array('comment' => $comment, 'commentEntity' => $commentEntity);
    }

    private function getParamsObject()
    {
        $errorMessage = false;

        $entityType = !isset($_POST['entityType']) ? null : trim($_POST['entityType']);
        $entityId = !isset($_POST['entityId']) ? null : (int) $_POST['entityId'];
        $pluginKey = !isset($_POST['pluginKey']) ? null : trim($_POST['pluginKey']);

        if ( !$entityType || !$entityId || !$pluginKey )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_ajax_error');
        }

        $params = new BASE_CommentsParams($pluginKey, $entityType);
        $params->setEntityId($entityId);

        if ( isset($_POST['ownerId']) )
        {
            $params->setOwnerId((int) $_POST['ownerId']);
        }

        if ( isset($_POST['commentCountOnPage']) )
        {
            $params->setCommentCountOnPage((int) $_POST['commentCountOnPage']);
        }

        if ( isset($_POST['displayType']) )
        {
            $params->setDisplayType($_POST['displayType']);
        }

        if ( isset($_POST['initialCount']) )
        {
            $params->setInitialCommentsCount((int) $_POST['initialCount']);
        }

        if ( isset($_POST['loadMoreCount']) )
        {
            $params->setLoadMoreCount((int) $_POST['loadMoreCount']);
        }
        if ( isset($_POST['replyId']) )
        {
            $params->setIsReplyList(true);
            $params->setReplyCommentId((int) $_POST['replyId']);
        }

        if ( $errorMessage )
        {
            echo json_encode(array(
                'error' => $errorMessage
            ));

            exit();
        }

        return $params;
    }
}
