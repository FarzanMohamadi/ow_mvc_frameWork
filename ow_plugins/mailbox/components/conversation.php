<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.components
 * @since 1.6.1
 * */
class MAILBOX_CMP_Conversation extends OW_Component
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $defaultAvatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        $this->assign('defaultAvatarUrl', $defaultAvatarUrl);

        $userId = OW::getUser()->getId();
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($userId);

        $avatarImageInfo = BOL_AvatarService::getInstance()->getAvatarInfo( $userId, $avatarUrl);

        $this->assign('avatarImageInfo', $avatarImageInfo);

        $this->assign('avatarUrl', $avatarUrl);

        $js = "OW.Mailbox.conversationController = new MAILBOX_ConversationView();";

        OW::getDocument()->addOnloadScript($js, 3006);

        //TODO check this config
        $enableAttachments = OW::getConfig()->getValue('mailbox', 'enable_attachments');
        $this->assign('enableAttachments', $enableAttachments);

        $replyToMessageActionPromotedText = '';
        $this->assign('isAuthorizedReplyToMessage', true);

        $isAuthorizedReplyToChatMessage = true;

        $this->assign('isAuthorizedReplyToChatMessage', $isAuthorizedReplyToChatMessage);

        $this->assign('replyToMessageActionPromotedText', $replyToMessageActionPromotedText);


        $text = new WysiwygTextarea('mailbox_message','mailbox');
        $text->setId('conversationTextarea');
        $this->assign('mailbox_message', $text->renderInput());

        return parent::render();
    }
}