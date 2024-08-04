<?php
/**
 * Mailbox responder class
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.classes
 * @since 1.0
 */
class MAILBOX_CLASS_Responder
{
    public $error;
    public $notice;

    /**
     * Class constructor
     */
    public function __construct()
    {
        return $this;
    }

    public function deleteConversation( $params )
    {
        if (!OW::getUser()->isAuthenticated())
        {
            echo json_encode(array());
            exit;
        }

        $userId = OW::getUser()->getId();

        $conversationId = (int) $params['conversationId'];

        if ( !empty($conversationId) )
        {
            MAILBOX_BOL_ConversationService::getInstance()->deleteConversation(array($conversationId), $userId);

            $this->notice = OW::getLanguage()->text('mailbox', 'delete_conversation_message');
            return true;
        }
        else
        {
            $this->error = OW::getLanguage()->text('mailbox', 'conversation_id_undefined');
            return false;
        }
    }
}