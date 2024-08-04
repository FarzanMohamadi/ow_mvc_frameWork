<?php
/**
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
class BASE_MCMP_Comments extends BASE_CMP_Comments
{
    private $formName;

    /**
     * Constructor.
     *
     * @param BASE_CommentsParams $params
     */
    public function __construct( BASE_CommentsParams $params )
    {
        parent::__construct($params);
    }

    public function initForm()
    {
        $this->formName = 'comment-add-'.$this->id;
        $replyCommentId = 0;
        if ($this->params->getIsReplyList()){
            $replyCommentId = $this->params->getReplyCommentId();
        }
        OW::getDocument()->addOnloadScript(
            "window.owCommentCmps['$this->id'] = new OwMobileComments('$this->cmpContextId', '$this->formName', '$this->id' , '$replyCommentId');"
        );

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'comments.html');
        $this->params->setCommentCountOnPage(BOL_CommentService::getInstance()->getConfigValue(BOL_CommentService::CONFIG_MB_COMMENTS_ON_PAGE));
        if ( $this->isAuthorized )
        {
            $this->addComponent('form', new BASE_MCMP_CommentsForm($this->params, $this->id, $this->formName));
            $this->assign('formCmp', true);
        }

        $this->addComponent('commentList', new BASE_MCMP_CommentsList($this->params, $this->id));
    }
}
