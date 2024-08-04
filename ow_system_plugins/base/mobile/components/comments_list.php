<?php
/**
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_CommentsList extends BASE_CMP_CommentsList
{
    /**
     * Constructor.
     *
     * @param string $entityType
     * @param integer $entityId
     * @param integer $page
     * @param string $displayType
     */
    public function __construct( BASE_CommentsParams $params, $id )
    {
        parent::__construct($params, $id);
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'comments_list.html');
    }

    protected function init()
    {
        if ($this->isReplyList) {
            $commentList = $this->commentService->findReplyList($this->params->getReplyCommentId(), 1, $this->params->getInitialCommentsCount());
        }else {
            $commentList = $this->commentService->findCommentList($this->params->getEntityType(), $this->params->getEntityId(), null, $this->params->getInitialCommentsCount());
        }
        $commentList = array_reverse($commentList);
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'itemHandler'));
        $commentList = $this->processList($commentList);
        $this->assign('comments', $commentList);
        $countToLoad = $this->commentCount - $this->params->getInitialCommentsCount();
        $this->assign('countToLoad', $countToLoad);


        static $dataInit = false;

        if ( !$dataInit )
        {
            $staticDataArray = array(
                'respondUrl' => OW::getRouter()->urlFor('BASE_CTRL_Comments', 'getMobileCommentList'),
                'delUrl' => OW::getRouter()->urlFor('BASE_CTRL_Comments', 'deleteComment'),
                'delAtchUrl' => OW::getRouter()->urlFor('BASE_CTRL_Comments', 'deleteCommentAtatchment'),
                'delConfirmMsg' => OW::getLanguage()->text('base', 'comment_delete_confirm_message'),
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
                    'initialCount' => $this->params->getInitialCommentsCount(),
                    'loadMoreCount' => $this->params->getLoadMoreCount(),
                    'commentIds' => $this->commentIdList,
                    'pluginKey' => $this->params->getPluginKey(),
                    'ownerId' => $this->params->getOwnerId(),
                    'commentCountOnPage' => $this->params->getCommentCountOnPage(),
                    'actionArray' => $this->actionArr,
                    'cid' => $this->id,
                    'loadCount' => $this->commentService->getConfigValue(BOL_CommentService::CONFIG_MB_COMMENTS_COUNT_TO_LOAD),
                    'replyCommentId' => $this->params->getReplyCommentId()
                )
        );

        OW::getDocument()->addOnloadScript(
            "window.owCommentListCmps.items['$this->id'] = new OwMobileCommentsList($jsParams);
            window.owCommentListCmps.items['$this->id'].init();"
        );
    }

    public function itemHandler( BASE_CLASS_EventProcessCommentItem $e )
    {
        $deleteButton = false;
        $cAction = null;
        $value = $e->getItem();

        if ( $this->isOwnerAuthorized || $this->isModerator || (int) OW::getUser()->getId() === (int) $value->getUserId() )
        {
            $deleteButton = true;
        }

        $flagButton = $value->getUserId() != OW::getUser()->getId();
        $replyButton = OW::getEventManager()->trigger(new OW_Event('frmcommentplus.add_reply_post_comment_button', array('value' => $value, 'isReplyList' => $this->isReplyList, 'replyCommentId' => $this->params->getReplyCommentId())));
        $replyButton = $replyButton->getData();

        if ( $this->isBaseModerator || $deleteButton || $flagButton || isset($replyButton['replyId']) )
        {
            $items = array();
            if ( $deleteButton )
            {
                $delId = 'del-' . $value->getId();
                array_unshift($items,  array(
                    'key' => 'udel',
                    'label' => OW::getLanguage()->text('base', 'contex_action_comment_delete_label'),
                    'order' => 1,
                    'class' => null,
                    'id' => $delId,
                    'attributes' => array(
                    ),
                ));
                $this->actionArr['comments'][$delId] = $value->getId();
            }

            if ( isset($replyButton['replyId']) )
            {
                $replyId = $replyButton['replyId'];
                $actionParams = $replyButton['actionParams'];
                $replyArray = $replyButton['replyArray'];
                $this->actionArr['comments'][$replyId] = $actionParams;
                array_unshift($items,  $replyArray);
            }

            if ( $flagButton )
            {
                array_unshift($items, array(
                    'key' => 'cflag',
                    'label' => OW::getLanguage()->text('base', 'flag'),
                    'order' => 3,
                    'class' => null,
                    'id' => $value->getId(),
                    'attributes' => array(
                        'onclick' => 'var d = $(this).data(); OW.flagContent(d.etype, d.eid);',
                        'data-etype' => 'comment',
                        'data-eid' =>$value->id

                    )
                ));
            }
        }
        $cAction = new BASE_MCMP_ContextAction($items);
        if ( $this->params->getCommentPreviewMaxCharCount() > 0 && mb_strlen($value->getMessage()) > $this->params->getCommentPreviewMaxCharCount() )
        {
            $e->setDataProp('previewMaxChar', $this->params->getCommentPreviewMaxCharCount());
        }

        $e->setDataProp('cnxAction', empty($cAction) ? '' : $cAction->render());
    }

    /**
     * @param $value BOL_Comment
     */
    protected function addReplyCommentComponent($value) {
        $this->addComponent($this->getReplyCommentComponentKey($value), new BASE_MCMP_Comments($this->params));
    }
}
