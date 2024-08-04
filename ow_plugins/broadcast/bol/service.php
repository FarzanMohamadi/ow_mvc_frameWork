<?php
/**
 * broadcast
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.broadcast
 * @since 1.0
 */
final class BROADCAST_BOL_Service
{

    private static $classInstance;
    /**
     * Class constructor
     *
     */
    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$classInstance) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function sendCustomMessageToUser($senderUserId, $opponentId, $text){

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $conversation = null;
        $conversationId = $conversationService->getChatConversationIdWithUserById($senderUserId, $opponentId);
        if ($conversationId == null || empty($conversationId)){
            $conversation = $conversationService->createChatConversation($senderUserId, $opponentId);
            $conversationId = $conversation->getId();
        }

        $text = str_replace('↵',"\r\n", $text);
        $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($text, false);
        $event = new OW_Event('mailbox.before_send_message', array(
            'senderId' => $senderUserId,
            'recipientId' => $opponentId,
            'conversationId' => $conversationId,
            'message' => $text
        ), array('result' => true, 'error' => '', 'message' => $text ));
        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !$data['result'] )
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $costumeFeatures = null;
        if(isset($_POST['outside_message']) && $_POST['outside_message'] == 1){
            $costumeFeatures = json_encode(array("type"=>"outside_message"));
        }

        $text = $data['message'];

        try
        {
            if($conversation == null && $conversationId != null){
                $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findById($conversationId);
            }
            if($conversation == null){
                return array('valid' => false, 'message' => 'authorization_error');
            }

            $message = $conversationService->createMessage($conversation, $senderUserId, $text, null,  false, null, false, $costumeFeatures);

        }
        catch(InvalidArgumentException $e)
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $item = $conversationService->getRawMessageInfo($message);
        if(isset($_POST['_id']) && !empty($_POST['_id']) && $_POST['_id'] != null && $_POST['_id'] != "null"){
            $item['_id'] = $_POST['_id'];
        }else{
            $item['_id'] = $message->id;
        }
        return array('valid' => true, 'message'=>$item);
    }

}