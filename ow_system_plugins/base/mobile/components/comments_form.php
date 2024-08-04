<?php
/**
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
class BASE_MCMP_CommentsForm extends OW_MobileComponent
{

    public function __construct( BASE_CommentsParams $params, $id, $formName )
    {
        parent::__construct();

        $language = OW::getLanguage();
        $form = new Form($formName);
        $textArea = new Textarea('commentText');
        $textArea->setHasInvitation(true);
        $textArea->setInvitation($language->text('base', 'comment_form_element_invitation_text'));
        $form->addElement($textArea);

        $hiddenEls = array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'displayType' => $params->getDisplayType(),
            'pluginKey' => $params->getPluginKey(),
            'ownerId' => $params->getOwnerId(),
            'cid' => $id,
            'commentCountOnPage' => $params->getCommentCountOnPage(),
            'isMobile' => 1,
            'replyId' => $params->getReplyCommentId(),
            'replyUserId' => null
        );

        foreach ( $hiddenEls as $name => $value )
        {
            $el = new HiddenField($name);
            $el->setValue($value);
            $form->addElement($el);
        }

        $submit = new Submit('comment-submit');
        $submit->setValue($language->text('base', 'comment_add_submit_label'));
        $form->addElement($submit);

        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('BASE_CTRL_Comments', 'addComment'));
//        $form->bindJsFunction(Form::BIND_SUBMIT, "function(){ $('#comments-" . $id . " .comments-preloader').show();}");
//        $form->bindJsFunction(Form::BIND_SUCCESS, "function(){ $('#comments-" . $id . " .comments-preloader').hide();}");
        $this->addForm($form);
        OW::getDocument()->addOnloadScript("window.owCommentCmps['$id'].initForm('" . $textArea->getId() . "', '".$submit->getId()."');");

        $this->assign('id', $id);
        $this->assign('formName', $formName);
        $this->assign('isReplyList', $params->getIsReplyList());
        if($params->getIsReplyList()){
            $this->assign('rudnId', 'rudni'.$params->getReplyCommentId());
        }
    }
}