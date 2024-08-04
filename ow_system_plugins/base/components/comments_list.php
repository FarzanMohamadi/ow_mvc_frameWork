<?php
/**
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_CommentsList extends OW_Component
{
    /**
     * @var BASE_CommentsParams
     */
    protected $params;
    protected $batchData;
    protected $staticData;
    protected $id;
    protected $commentCount;
    protected $cmpContextId;

    /**
     * @var BOL_CommentService
     */
    protected $commentService;
    protected $avatarService;
    protected $page;
    protected $isModerator;
    protected $actionArr = array('comments' => array(), 'users' => array());
    protected $commentIdList = array();
    protected $userIdList = array();
    protected $tmp = array();
    protected $isReplyList = true;

    /**
     * Constructor.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $page
     * @param string $displayType
     */
    public function __construct( BASE_CommentsParams $params, $id, $page = 1 )
    {
        parent::__construct();
        $batchData = $params->getBatchData();
        $this->staticData = empty($batchData['_static']) ? array() : $batchData['_static'];
        $batchData = isset($batchData[$params->getEntityType()][$params->getEntityId()]) ? $batchData[$params->getEntityType()][$params->getEntityId()] : array();
        $this->params = $params;
        $this->batchData = $batchData;
        $this->id = $id;
        $this->page = $page;
        $this->isModerator = OW::getUser()->isAuthorized($params->getPluginKey());
        $this->isOwnerAuthorized = (OW::getUser()->isAuthenticated() && $this->params->getOwnerId() !== null && (int) $this->params->getOwnerId() === (int) OW::getUser()->getId());
        $this->isBaseModerator = OW::getUser()->isAuthorized('base');

        $this->commentService = BOL_CommentService::getInstance();
        $this->avatarService = BOL_AvatarService::getInstance();
        $this->cmpContextId = "comments-list-$id";
        $this->assign('cmpContext', $this->cmpContextId);
        if (!$this->params->getIsReplyList()){
            $this->isReplyList = false;
        }
        if ($this->isReplyList){
            $this->commentCount = $this->commentService->findReplyCount($this->params->getReplyCommentId());
            $this->assign('replyListId', 'replyList-'.$this->params->getReplyCommentId());
        }
        else{
            $this->commentCount = isset($batchData['commentsCount']) ? $batchData['commentsCount'] : $this->commentService->findCommentCount($params->getEntityType(), $params->getEntityId());
        }
        $this->init();
    }

    protected function processList( $commentList )
    {
        $arrayToAssign = array();

        /* @var $value BOL_Comment */
        foreach ( $commentList as $value )
        {
            $this->userIdList[] = $value->getUserId();
            $this->commentIdList[] = $value->getId();
        }

        $userAvatarArrayList = empty($this->staticData['avatars']) ? $this->avatarService->getDataForUserAvatars($this->userIdList) : $this->staticData['avatars'];
        $userAvatarArrayList = $this->avatarService->getDataForUserAvatars($this->userIdList);
        $this->params->setIsReplyList(true);

        /* @var $value BOL_Comment */
        foreach ( $commentList as $value )
        {
            $cmItemArray = array(
                'displayName' => $userAvatarArrayList[$value->getUserId()]['title'],
                'avatarUrl' => $userAvatarArrayList[$value->getUserId()]['src'],
                'profileUrl' => $userAvatarArrayList[$value->getUserId()]['url'],
                'content' => $value->getMessage(),
                'date' => UTIL_DateTime::formatDate($value->getCreateStamp()),
                'userId' => $value->getUserId(),
                'commentId' => $value->getId(),
                'avatar' => $userAvatarArrayList[$value->getUserId()],
                'replyList' => !$this->isReplyList,
                'contentId' => 'content-'.$value->getId(),
            );
            if (!$this->isReplyList){
//                $this->params->setReplyCommentId($value->getId().$this->id);//change
//                $cmItemArray['replyListParams'] = $this->params;
                $cmItemArray['replyComponent'] = $this->getReplyCommentComponentKey($value);
                $this->params->setReplyCommentId($value->getId());
                $this->addReplyCommentComponent($value);
            }
            if( $value->getReplyUserId() != null){
                $cmItemArray['userReplyDisplayName'] = OW::getLanguage()->text('base', 'user_reply_display_name', array('displayName' => BOL_UserService::getInstance()->getDisplayName($value->getReplyUserId())));
                $cmItemArray['userReplyProfileUrl'] = BOL_UserService::getInstance()->getUserUrl($value->getReplyUserId());
            }

            $contentAdd = '';

            if ( $value->getAttachment() !== null )
            {
                $tempCmp = new BASE_CMP_OembedAttachment((array) json_decode($value->getAttachment()), $this->isOwnerAuthorized);
                if (sizeof($tempCmp->getOembed()) != 0)
                    $contentAdd .= '<div class="ow_attachment ow_small" id="att' . $value->getId() . '">' . $tempCmp->render() . '</div>';
            }

            $commentlikeEvent= OW::getEventManager()->trigger(new OW_Event('add.newsfeed.comment.like.component',array('value'=>$value,'cmItemArray'=>$cmItemArray,'commentIdList'=>  $this->commentIdList,'entityType'=> $this->params->getEntityType(), 'params' => $this->params)));
            if(isset($commentlikeEvent->getData()['cmItemArray']))
            {
                $cmItemArray=$commentlikeEvent->getData()['cmItemArray'];
            }
            $cmItemArray['content_add'] = $contentAdd;

            $event = new BASE_CLASS_EventProcessCommentItem('base.comment_item_process', $value, $cmItemArray, $this->params->cachedParams);
            OW::getEventManager()->trigger($event);

            //fix a tag split in view
            $dataArr = $event->getDataArr();
            if(isset($dataArr['previewMaxChar'])) {
                $content = $dataArr['content'];
                $oldMaxChar = $dataArr['previewMaxChar'];
                $contentPart1 = substr($content, 0, $oldMaxChar);
                if(strpos($content,'<a')!==false){
                    if(strrpos($contentPart1,'<')!==strrpos($contentPart1,'</a>')){
                        $lastStarter = strrpos($contentPart1,'<');
                        $lastEnder = strpos($content,'</a>',$lastStarter);
                        $dataArr['previewMaxChar'] = $lastEnder + 4;
                    }
                }
            }
            $arrayToAssign[] = $dataArr;
        }

        OW::getEventManager()->unbind('base.comment_item_process', array($this, 'itemHandler'));
        if (!$this->isReplyList){
            $this->params->setReplyCommentId(null);
        }
        return $arrayToAssign;
    }

    public function itemHandler( BASE_CLASS_EventProcessCommentItem $e )
    {
        $language = OW::getLanguage();

        $deleteButton = false;
        $cAction = null;
        /* @var $value BOL_Comment */
        $value = $e->getItem();

        if ( $this->isOwnerAuthorized || $this->isModerator || (int) OW::getUser()->getId() === (int) $value->getUserId() )
        {
            $deleteButton = true;
        }

        $parentAction = new BASE_ContextAction();
        $parentAction->setKey('parent');
        $parentAction->setClass('ow_comments_context');

        $flagButton = $value->getUserId() != OW::getUser()->getId();
        $replyButton = OW::getEventManager()->trigger(new OW_Event('frmcommentplus.add_reply_post_comment_button', array('value' => $value, 'isReplyList' => $this->isReplyList, 'replyCommentId' => $this->params->getReplyCommentId(), 'parentAction' => $parentAction)));
        $replyButton = $replyButton->getData();

        if ( $this->isBaseModerator || $deleteButton || $flagButton || isset($replyButton['replyId']) )
        {
            $cAction = new BASE_CMP_ContextAction();

            $cAction->addAction($parentAction);

            if ( $deleteButton )
            {
                $flagAction = new BASE_ContextAction();
                $flagAction->setLabel($language->text('base', 'contex_action_comment_delete_label'));
                $flagAction->setKey('udel');
                $flagAction->setParentKey($parentAction->getKey());
                $delId = 'del-' . $value->getId();
                $flagAction->setId($delId);
                $this->actionArr['comments'][$delId] = $value->getId();
                $cAction->addAction($flagAction);

            }

            if (isset($replyButton['replyId'])) {
                $actionParams = $replyButton['actionParams'];
                $flagAction = $replyButton['flagAction'];
                $replyId = $replyButton['replyId'];
                $this->actionArr['comments'][$replyId] = $actionParams;
                $cAction->addAction($flagAction);
            }
            //because the suspension of users was removed from the original post this feature(suspend user) of the comments was deleted .
//            if ( $this->isBaseModerator && $value->getUserId() != OW::getUser()->getId() )
//            {
                /* $suspendCode='';
                $unSuspendCode='';
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>$value->getUserId(),'isPermanent'=>true,'activityType'=>'userSuspend_core')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])){
                    $suspendCode = $frmSecuritymanagerEvent->getData()['code'];
                }
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>$value->getUserId(),'isPermanent'=>true,'activityType'=>'userUnSuspend_core')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])){
                    $unSuspendCode = $frmSecuritymanagerEvent->getData()['code'];
                }

                $toogleText = null;
                $toggleCommand = null;
                $toggleClass = null;

                $suspended = BOL_UserService::getInstance()->isSuspended($value->getUserId());

                $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
                $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

                $toggleText = $suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

                $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $suspended ? "unsuspend" : "suspend";

                $command = !$suspended ? "suspend" : "unsuspend";

                $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS] = $suspended ? "ow_mild_green" : "ow_mild_red";

                $toggleClass = $suspended ? "ow_mild_green" : "ow_mild_red";

                $modAction = new BASE_ContextAction();
                $modAction->setLabel($toggleText);
                $modAction->setKey('cdel');
//                $modAction->setClass($toggleClass);
                $modAction->setParentKey($parentAction->getKey());
                $delId = 'udel-' . $value->getId();
                $modAction->setId($delId);
                $this->actionArr['users'][$delId] = array(
                    'display_name' => 'test',
                    'command' => $command,
                    'uid' => $value->getUserId(),
                    'suspend_code' => $suspendCode,
                    'un_suspend_code' => $unSuspendCode,
                    'un_suspend_text' => OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl'),
                    'suspend_text' => OW::getLanguage()->text('base', 'user_suspend_btn_lbl'),
                    'comment_list_id'=>$this->id
                );
                $cAction->addAction($modAction);*/
//            }

            if ( $flagButton )
            {
                $flagAction = new BASE_ContextAction();
                $flagAction->setLabel($language->text('base', 'flag'));
                $flagAction->setKey('cflag');
                $flagAction->setParentKey($parentAction->getKey());
                $flagAction->addAttribute("onclick", "var d = $(this).data(); OW.flagContent(d.etype, d.eid);");
                $flagAction->addAttribute("data-etype", "comment");
                $flagAction->addAttribute("data-eid", $value->id);
                $flagAction->addAttribute("class","comment_delete");

                $cAction->addAction($flagAction);
            }
        }

        if ( $this->params->getCommentPreviewMaxCharCount() > 0 && mb_strlen($value->getMessage()) > $this->params->getCommentPreviewMaxCharCount() )
        {
            $e->setDataProp('previewMaxChar', $this->params->getCommentPreviewMaxCharCount());
        }

        $e->setDataProp('cnxAction', empty($cAction) ? '' : $cAction->render());
    }

    protected function init()
    {
        if ( $this->commentCount === 0 && $this->params->getShowEmptyList() && !$this->isReplyList )
        {
            $this->assign('noComments', true);
        }
        if ( $this->commentCount != 0 && $this->isReplyList )
        {
            $this->assign('hasReply', true);
        }


        $countToLoad = 0;

        if ( $this->commentCount === 0 )
        {
            $commentList = array();
        }
        else if($this->isReplyList){
            $getReplyCount = 3;
            if (isset($_POST['replyCommentId']) && $_POST['replyCommentId'] > 0) {
                $getReplyCount = $this->params->getInitialCommentsCount();
            }
            $commentList = $this->commentService->findReplyList($this->params->getReplyCommentId(), 1, $getReplyCount);
            $repliesCountToLoad = $this->commentService->findReplyCount($this->params->getReplyCommentId()) - $getReplyCount;
            $this->assign('repliesCountToLoad', $repliesCountToLoad);
        }
        else if ( in_array($this->params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST, BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI)) )
        {
            $commentList = empty($this->batchData['commentsList']) ? $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), 1, $this->params->getInitialCommentsCount()) : $this->batchData['commentsList'];
            $commentList = array_reverse($commentList);
            $countToLoad = $this->commentCount - $this->params->getInitialCommentsCount();
            $this->assign('countToLoad', $countToLoad);
        }
        else
        {
            $commentList = $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), $this->page, $this->params->getCommentCountOnPage());
        }

        OW::getEventManager()->trigger(new OW_Event('base.comment_list_prepare_data', array('list' => $commentList, 'entityType' => $this->params->getEntityType(), 'entityId' => $this->params->getEntityId())));
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'itemHandler'));
        $this->assign('comments', $this->processList($commentList));
        $pages = false;

        if ( $this->params->getDisplayType() === BASE_CommentsParams::DISPLAY_TYPE_WITH_PAGING )
        {
            $pagesCount = $this->commentService->findCommentPageCount($this->params->getEntityType(), $this->params->getEntityId(), $this->params->getCommentCountOnPage());

            if ( $pagesCount > 1 )
            {
                $pages = $this->getPages($this->page, $pagesCount, 8);
                $this->assign('pages', $pages);
            }
        }
        else
        {
            $pagesCount = 0;
        }

        $this->assign('loadMoreLabel', OW::getLanguage()->text('base', 'comment_load_more_label'));

        static $dataInit = false;

        if ( !$dataInit )
        {
            $staticDataArray = array(
                'respondUrl' => OW::getRouter()->urlFor('BASE_CTRL_Comments', 'getCommentList'),
                'delUrl' => OW::getRouter()->urlFor('BASE_CTRL_Comments', 'deleteComment'),
                'delAtchUrl' => OW::getRouter()->urlFor('BASE_CTRL_Comments', 'deleteCommentAtatchment'),
                'delConfirmMsg' => OW::getLanguage()->text('base', 'comment_delete_confirm_message'),
                'preloaderImgUrl' => OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'ajax_preloader_button.gif'
            );
            OW::getDocument()->addOnloadScript("window.owCommentListCmps.staticData=" . json_encode($staticDataArray) . ";");
            $dataInit = true;
        }

        $jsParams = json_encode(
            array(
                'totalCount' => $this->commentCount,
                'contextId' => $this->cmpContextId,
                'displayType' => $this->params->getDisplayType(),
                'entityType' => $this->params->getEntityType(),
                'entityId' => $this->params->getEntityId(),
                'pagesCount' => $pagesCount,
                'initialCount' => $this->params->getInitialCommentsCount(),
                'loadMoreCount' => $this->params->getLoadMoreCount(),
                'commentIds' => $this->commentIdList,
                'pages' => $pages,
                'pluginKey' => $this->params->getPluginKey(),
                'ownerId' => $this->params->getOwnerId(),
                'commentCountOnPage' => $this->params->getCommentCountOnPage(),
                'cid' => $this->id,
                'actionArray' => $this->actionArr,
                'countToLoad' => $countToLoad,
                'replyCommentId' => $this->params->getReplyCommentId(),
                'repliesCountToLoad' => isset($repliesCountToLoad) ? $repliesCountToLoad : 0
            )
        );

        OW::getDocument()->addOnloadScript(
            "window.owCommentListCmps.items['$this->id'] = new OwCommentsList($jsParams);
            window.owCommentListCmps.items['$this->id'].init();"
        );
    }

    protected function getPages( $currentPage, $pagesCount, $displayPagesCount )
    {
        $first = false;
        $last = false;

        $prev = ( $currentPage > 1 );
        $next = ( $currentPage < $pagesCount );

        if ( $pagesCount <= $displayPagesCount )
        {
            $start = 1;
            $displayPagesCount = $pagesCount;
        }
        else
        {
            $start = $currentPage - (int) floor($displayPagesCount / 2);

            if ( $start <= 1 )
            {
                $start = 1;
            }
            else
            {
                $first = true;
            }

            if ( ($start + $displayPagesCount - 1) < $pagesCount )
            {
                $last = true;
            }
            else
            {
                $start = $pagesCount - $displayPagesCount + 1;
            }
        }

        $pageArray = array();

        if ( $first )
        {
            $pageArray[] = array('label' => OW::getLanguage()->text('base', 'paging_label_first'), 'pageIndex' => 1);
        }

        if ( $prev )
        {
            $pageArray[] = array('label' => OW::getLanguage()->text('base', 'paging_label_prev'), 'pageIndex' => ($currentPage - 1));
        }

        if ( $first )
        {
            $pageArray[] = array('label' => '...');
        }

        for ( $i = (int) $start; $i <= ($start + $displayPagesCount - 1); $i++ )
        {
            $pageArray[] = array('label' => $i, 'pageIndex' => $i, 'active' => ( $i === (int) $currentPage ));
        }

        if ( $last )
        {
            $pageArray[] = array('label' => '...');
        }

        if ( $next )
        {
            $pageArray[] = array('label' => OW::getLanguage()->text('base', 'paging_label_next'), 'pageIndex' => ( $currentPage + 1 ));
        }

        if ( $last )
        {
            $pageArray[] = array('label' => OW::getLanguage()->text('base', 'paging_label_last'), 'pageIndex' => $pagesCount);
        }

        return $pageArray;
    }

    /**
     * @param $value BOL_Comment
     */
    protected function addReplyCommentComponent($value) {
        $this->addComponent($this->getReplyCommentComponentKey($value), new BASE_CMP_Comments($this->params));
    }

    /**
     * @param $value BOL_Comment
     * @return string
     */
    protected function getReplyCommentComponentKey($value) {
        return 'replyComponent-' . $value->getId();
    }
}
