<?php
/**
 * Conversation Service Class
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.bol
 * @since 1.0
 */
final class MAILBOX_BOL_ConversationService
{
    const EVENT_MARK_CONVERSATION = 'mailbox.mark_conversation';
    const EVENT_DELETE_CONVERSATION = 'mailbox.delete_conversation';
    const EVENT_DELETE_ATTACHMENT_FILES_INCOMPLETE = 'mailbox.delete_attachment_files_incomplete';
    const EVENT_AFTER_ADD_MESSAGE = 'mailbox.after_add_message';

    const MARK_TYPE_READ = 'read';
    const MARK_TYPE_UNREAD = 'unread';

    const CHAT_CONVERSATION_SUBJECT = 'mailbox_chat_conversation';

    /**
     * @var MAILBOX_BOL_ConversationDao
     */
    private $conversationDao;
    /**
     * @var MAILBOX_BOL_LastMessageDao
     */
    private $lastMessageDao;
    /**
     * @var MAILBOX_BOL_MessageDao
     */
    private $messageDao;
    /**
     * @var MAILBOX_BOL_AttachmentDao
     */
    private $attachmentDao;
    /**
     * @var MAILBOX_BOL_UserLastDataDao
     */
    private $userLastDataDao;
    /**
     * @var array
     */
    private static $allowedExtensions =
        array(
            'txt', 'doc', 'docx', 'sql', 'csv', 'xls', 'ppt',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'psd', 'ai', 'pdf',
            'avi', 'wmv', 'mp3', '3gp', 'flv', 'mkv', 'mpeg', 'mpg', 'swf',
            'zip', 'gz', '.tgz', 'gzip', '7z', 'bzip2', 'rar'
        );
    /**
     * Class instance
     *
     * @var MAILBOX_BOL_ConversationService
     */
    private static $classInstance;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->conversationDao = MAILBOX_BOL_ConversationDao::getInstance();
        $this->lastMessageDao = MAILBOX_BOL_LastMessageDao::getInstance();
        $this->messageDao = MAILBOX_BOL_MessageDao::getInstance();
        $this->attachmentDao = MAILBOX_BOL_AttachmentDao::getInstance();
        $this->userLastDataDao = MAILBOX_BOL_UserLastDataDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return MAILBOX_BOL_ConversationService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getUnreadMessageListForConsole( $userId, $first, $count, $lastPingTime, $ignoreList = array() )
    {
        if ( empty($userId) || !isset($first) || !isset($count) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        return $this->conversationDao->getUnreadMessageListForConsole($userId, $first, $count, $lastPingTime, $ignoreList);
    }

    /**
     * Marks conversation as Read or Unread
     *
     * @param array $conversationsId
     * @param $userId
     * @param string $markType
     * @param null $lastRequestTimestamp
     * @param $additionalParams
     * @return int
     */
    public function markConversation( array $conversationsId, $userId, $markType = self::MARK_TYPE_READ, $lastRequestTimestamp = null, $additionalParams = array())
    {
        if ( empty($userId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'not_numeric_params_with_user_error', array('$userId' => $userId));
            throw new InvalidArgumentException($errorMessage);
        }

        if ( empty($conversationsId) || !is_array($conversationsId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'wrong_parameter_conversationsId_error', array('$conversationsId' => $conversationsId));
            throw new InvalidArgumentException($errorMessage);
        }

        $userId = (int) $userId;
        $conversations = array();
        $remainConversationIds = array();
        foreach ($conversationsId as $conversationId) {
            if (isset($additionalParams['cache']['conversations'][$conversationId])) {
                $conversations[$additionalParams['cache']['conversations'][$conversationId]->id] = $additionalParams['cache']['conversations'][$conversationId];
            } else {
                $remainConversationIds[] = $conversationId;
            }
        }

        if (sizeof($remainConversationIds) > 0) {
            $remainConversations = $this->conversationDao->findByIdList($remainConversationIds);
            foreach ($remainConversations as $conv) {
                $conversations[$conv->id] = $conv;
            }
        }

        $count = 0;

        foreach ( $conversations as $key => $value )
        {
            $conversation = &$conversations[$key];

            $lastMessages = $this->lastMessageDao->findByConversationId($conversation->id);
            if (!empty($lastMessages))
            {
                $readBy = MAILBOX_BOL_ConversationDao::READ_NONE;
                $isOpponentLastMessage = false;

                switch ( $userId )
                {
                    case $conversation->initiatorId :

                        if ( $lastMessages->initiatorMessageId < $lastMessages->interlocutorMessageId )
                        {
                            $isOpponentLastMessage = true;
                            $conversation->notificationSent = 1;
                        }

                        $readBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;

                        break;

                    case $conversation->interlocutorId :

                        if ( $lastMessages->initiatorMessageId > $lastMessages->interlocutorMessageId )
                        {
                            $isOpponentLastMessage = true;
                            $conversation->notificationSent = 1;
                        }

                        $readBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;

                        break;
                }

//                if ( !$isOpponentLastMessage )
//                {
//                    continue;
//                }

                switch ( $markType )
                {
                    case self::MARK_TYPE_READ :
                        //mark all new messages
                        $ex = new OW_Example();
                        $ex->andFieldLike('conversationId', $conversation->id);
                        $ex->andFieldLike('recipientId', $userId);
                        $ex->andFieldLike('recipientRead', false);
                        if( isset($lastRequestTimestamp) ){
                            $ex->andFieldLessThan('timeStamp',$lastRequestTimestamp-8);
                        }
                        $idList = $this->messageDao->findIdListByExample($ex);
                        $this->markMessageIdListReadByUser($idList, $userId);

                        $conversation->read = (int) $conversation->read | $readBy;
                        break;

                    case self::MARK_TYPE_UNREAD :
                        $conversation->read = (int) $conversation->read & (~$readBy);
                        break;
                }

                $this->conversationDao->save($conversation);

                if ( $this->conversationDao->getAffectedRows() > 0 )
                {
                    $count++;
                }
            }
        }

        $paramList = array(
            'conversationIdList' => $conversationsId,
            'userId' => $userId,
            'additionalParams' => $additionalParams,
            'markType' => $markType);

        $event = new OW_Event(self::EVENT_MARK_CONVERSATION, $paramList);
        OW::getEventManager()->trigger($event);

        $this->resetUserLastData($userId);

        return $count;
    }

    /**
     * Marks conversation as Read
     * @param array $conversationsId
     * @param $userId
     * @param null $lastRequestTimestamp
     * @param $additionalParams
     * @return int
     */
    public function markRead( array $conversationsId, $userId, $lastRequestTimestamp = null, $additionalParams = array())
    {
        return $this->markConversation($conversationsId, $userId, self::MARK_TYPE_READ, $lastRequestTimestamp, $additionalParams);
    }

    /**
     * Marks message as read by recipient
     *
     * @param $messageId
     * @return bool
     */
    public function markMessageRead( $messageId )
    {
        $message = $this->messageDao->findById($messageId);

        if ( !$message )
        {
            return false;
        }

        $message->recipientRead = 1;
        $this->messageDao->save($message);

        return true;
    }

    public function markMessageAuthorizedToRead( $messageId )
    {
        /**
         * @var MAILBOX_BOL_Message $message
         */
        $message = $this->messageDao->findById($messageId);

        if ( !$message )
        {
            return false;
        }

        $message->wasAuthorized = 1;
        $this->messageDao->save($message);

        return $message;
    }

    public function markMessageAsSystem( $messageId )
    {
        /**
         * @var MAILBOX_BOL_Message $message
         */
        $message = $this->messageDao->findById($messageId);

        if ( !$message )
        {
            return false;
        }

        $message->isSystem = 1;
        $this->messageDao->save($message);

        return true;
    }

    /**
     * Marks conversation as Unread
     *
     * @param array $conversationsId
     * @param int $userId
     *
     * retunn int
     */
    public function markUnread( array $conversationsId, $userId )
    {
        return $this->markConversation($conversationsId, $userId, self::MARK_TYPE_UNREAD);
    }

    /**
     * Deletes conversation
     *
     * @param array $conversationsId
     * @param int $userId
     * @throws InvalidArgumentException
     *
     * @return int
     */
    public function deleteConversation( array $conversationsId, $userId )
    {
        if ( empty($userId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'not_numeric_params_with_user_error', array('$userId' => $userId));
            throw new InvalidArgumentException($errorMessage);
        }

        if ( empty($conversationsId) || !is_array($conversationsId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'wrong_parameter_conversationsId_error', array('$conversationsId' => $conversationsId));
            throw new InvalidArgumentException($errorMessage);
        }

        $userId = (int) $userId;
        $conversations = $this->conversationDao->findByIdList($conversationsId);

        $count = 0;

        foreach ( $conversations as $key => $value )
        {
            /**
             * @var MAILBOX_BOL_Conversation $conversation
             */
            $conversation = &$conversations[$key];

            $deletedBy = MAILBOX_BOL_ConversationDao::DELETED_NONE;

            switch ( $userId )
            {
                case $conversation->initiatorId :
                    $deletedBy = MAILBOX_BOL_ConversationDao::DELETED_INITIATOR;
                    $conversation->initiatorDeletedTimestamp = time();
                    break;

                case $conversation->interlocutorId :
                    $deletedBy = MAILBOX_BOL_ConversationDao::DELETED_INTERLOCUTOR;
                    $conversation->interlocutorDeletedTimestamp = time();
                    break;
            }

            $conversation->deleted = (int) $conversation->deleted | $deletedBy;

            if ( $conversation->deleted == MAILBOX_BOL_ConversationDao::DELETED_ALL )
            {
                $this->messageDao->deleteByConversationId($conversation->id);
                $this->lastMessageDao->deleteByConversationId($conversation->id);
                $this->conversationDao->deleteById($conversation->id);
                $this->deleteAttachmentsByConversationList(array($conversation->id));

                $event = new OW_Event(self::EVENT_DELETE_CONVERSATION, array('conversationDto' => $conversation));
                OW::getEventManager()->trigger($event);
            }
            else
            {
                $this->conversationDao->save($conversation);

                // clear query cache
                switch ( $userId )
                {
                    case $conversation->initiatorId :
                        OW::getCacheManager()->clean(array(MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $conversation->initiatorId));
                        break;

                    case $conversation->interlocutorId :
                        OW::getCacheManager()->clean(array(MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $conversation->interlocutorId));
                        break;
                }
            }

            if ( $this->conversationDao->getAffectedRows() > 0 )
            {
                $count++;

                OW::getCacheManager()->clean(array(MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $userId));
            }
        }

        $this->resetUserLastData($userId);

        return $count;
    }

    /**
     * Creates new conversation
     *
     * @param int $initiatorId
     * @param int $interlocutorId
     * @param string $subject
     * @param string $text
     * @throws InvalidArgumentException
     *
     * @return MAILBOX_BOL_Conversation
     */
    public function createConversation( $initiatorId, $interlocutorId, $subject, $text = '' )
    {
        if ( empty($initiatorId) || empty($interlocutorId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        $initiatorId = (int) $initiatorId;
        $interlocutorId = (int) $interlocutorId;
        $subject = trim(strip_tags($subject));

        if ( empty($subject) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        // create conversation
        $conversation = new MAILBOX_BOL_Conversation();
        $conversation->initiatorId = $initiatorId;
        $conversation->interlocutorId = $interlocutorId;
        $conversation->subject = $subject;
        $conversation->createStamp = time();
        $conversation->viewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

        $this->conversationDao->save($conversation);

        $text = trim($text);
        if (!empty($text))
        {
            $this->createMessage($conversation, $initiatorId, $text);
        }

        return $conversation;
    }

    public function createChatConversation( $initiatorId, $interlocutorId )
    {
        if ( empty($initiatorId) || empty($interlocutorId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        $initiatorId = (int) $initiatorId;
        $interlocutorId = (int) $interlocutorId;

        // create chat conversation
        $conversation = new MAILBOX_BOL_Conversation();
        $conversation->initiatorId = $initiatorId;
        $conversation->interlocutorId = $interlocutorId;
        $conversation->subject = self::CHAT_CONVERSATION_SUBJECT;
        $conversation->createStamp = time();
        $conversation->viewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

        $this->conversationDao->save($conversation);

        return $conversation;
    }

    /**
     * @param $conversationId
     * @param $first
     * @param $count
     * @param array $additionalParams
     * @return array
     */
    public function getConversationMessagesList( $conversationId, $first, $count, $additionalParams = array())
    {
        if ( empty($conversationId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId, $additionalParams);

        $dtoList = $this->messageDao->findListByConversationId($conversationId, $count, $deletedTimestamp);
        $messageIdList = array();
        foreach($dtoList as $message)
        {
            $messageIdList[] = $message->id;
        }

        $attachmentsByMessageList = $this->findAttachmentsByMessageIdList($messageIdList);

        $list = array();
        foreach($dtoList as $message)
        {
            $list[] = $this->getMessageData($message, $attachmentsByMessageList, $additionalParams);
        }

        return $list;
    }

    /**
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageData( $message, $attachmentsByMessageList = null, $cachedParams = array())
    {
        $item = array();

        $item['convId'] = (int)$message->conversationId;
        $item['mode'] = $this->getConversationMode((int)$message->conversationId, $cachedParams);
        $item['id'] = (int)$message->id;
        $item['replyId'] = $message->replyId;
        $item['replyMessage'] = null;
        if (isset($message->replyId)) {
            $replyMessage = MAILBOX_BOL_MessageDao::getInstance()->findById($message->replyId);
            if (isset($replyMessage)) {
                $senderName = BOL_UserService::getInstance()->getDisplayName($replyMessage->senderId);
                $item['reply_sender'] = $senderName;
                $item['reply_sender_url'] = BOL_UserService::getInstance()->getUserUrl($replyMessage->senderId);
                $text = $this->json_decode_text($replyMessage->text);
                $item['replyMessage'] = $text;
                if($item['replyMessage'] == OW::getLanguage()->text('mailbox', 'attachment')){
                    $replyAttachments = $this->attachmentDao->findAttachmentsByMessageId($message->replyId);
                    if (!empty($replyAttachments))
                    {
                        foreach($replyAttachments as $attachment)
                        {
                            $ext = UTIL_File::getExtension($attachment->fileName);
                            $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);
                            $attItem = array();
                            $attItem['id'] = $attachment->id;
                            $attItem['messageId'] = $attachment->messageId;
                            $attItem['downloadUrl'] = OW::getStorage()->getFileUrl($attachmentPath);
                            $attItem['fileName'] = $attachment->fileName;
                            $attItem['fileSize'] = $attachment->fileSize;
                            $attItem['type'] = $this->getAttachmentType($attachment);

                            $item['replyAttachments'][] = $attItem;
                        }
                    }
                }
            }
        }
        $conversationItem = $this->getConversationItem($item['mode'], $message->conversationId, null, $cachedParams);
        $item['opponentId'] = $conversationItem['opponentId'];
        $senderName = BOL_UserService::getInstance()->getDisplayName($message->senderId);
        $item['senderName'] = $senderName;
        $senderUrl = BOL_UserService::getInstance()->getUserUrl($message->senderId);
        $item['senderUrl'] = $senderUrl;

        $senderAvatar = null;
        if (isset($cachedParams['cache']['users_info'][$message->senderId]['src'])) {
            $senderAvatar = $cachedParams['cache']['users_info'][$message->senderId]['src'];
        }
        if ($senderAvatar == null) {
            $senderAvatar = BOL_AvatarService::getInstance()->getAvatarUrl($message->senderId);
        }

        $item['senderAvatar'] = $senderAvatar;
        $item['date'] = date('Y-m-d', (int)$message->timeStamp);
        $item['dateLabel'] = UTIL_DateTime::formatDate((int)$message->timeStamp, true);
        $item['timeStamp'] = (int)$message->timeStamp;

        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');
        $item['timeLabel'] = $militaryTime ? strftime("%H:%M", (int)$message->timeStamp) : strftime("%I:%M%p", (int)$message->timeStamp);
        $item['recipientId'] = (int)$message->recipientId;
        $item['senderId'] = (int)$message->senderId;
        $item['isAuthor'] = (bool)((int)$message->senderId == OW::getUser()->getId());
        $item['recipientRead'] = (int)$message->recipientRead;
        $item['isSystem'] = (int)$message->isSystem;
        $item['byCreditsMessage'] = false;
        $item['promotedMessage'] = false;
        $item['authErrorMessages'] = false;
        $item['changed'] = $message->changed;
        $item['attachments'] = array();

        $conversation = null;
        if (isset($cachedParams['cache']['conversations'][$message->conversationId])) {
            $conversation = $cachedParams['cache']['conversations'][$message->conversationId];
        }
        if ($conversation == null) {
            $conversation = $this->getConversation($message->conversationId);
        }
        if ( (int)$conversation->initiatorId == OW::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
        }

        if ( (int)$conversation->interlocutorId == OW::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
        }

        $item['readMessageAuthorized'] = true;

        if ($message->isSystem) {
            $eventParams = json_decode($message->text, true);
            $eventParams['params']['messageId'] = (int)$message->id;

            $event = new OW_Event($eventParams['entityType'] . '.' . $eventParams['eventName'], $eventParams['params']);
            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if (!empty($data)) {
                $text = $data;
            } else {
                $text = '<div class="ow_dialog_item odd">' . OW::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType' => $eventParams['entityType'])) . '</div>';
            }
        } else {
            $text = $message->text;
        }

        if ($attachmentsByMessageList === null) {
            $attachments = $this->attachmentDao->findAttachmentsByMessageId($message->id);
        } else {
            $attachments = array_key_exists($message->id, $attachmentsByMessageList) ? $attachmentsByMessageList[$message->id] : array();
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $ext = UTIL_File::getExtension($attachment->fileName);
                $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                $attItem = array();
                $attItem['id'] = (int)$attachment->id;
                $attItem['messageId'] = (int)$attachment->messageId;
                $attItem['downloadUrl'] = OW::getStorage()->getFileUrl($attachmentPath);
                $attItem['fileName'] = $attachment->fileName;
                $attItem['fileSize'] = $attachment->fileSize;
                $attItem['type'] = $this->getAttachmentType($attachment);

                $item['attachments'][] = $attItem;
            }
        }

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $text)));
        if (isset($stringRenderer->getData()['string'])) {
            $text = ($stringRenderer->getData()['string']);
        }
        $item['text'] = $text;

        return $item;
    }

    /**
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageDataForList( $messageList, $attachmentsByMessageList = null )
    {
        $list = array();
        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');


        $conversationIds = array();
        foreach ($messageList as $message) {
            $conversationIds[] = $message->conversationId;
        }
        $cachedConversation = $this->conversationDao->findByConversationIds($conversationIds);
        $params['cache']['conversations_items'] = $this->conversationDao->getConversationsItem($conversationIds);

        foreach ($messageList as $message)
        {
            $conversation = null;
            if (isset($cachedConversation[$message->conversationId])) {
                $conversation = $cachedConversation[$message->conversationId];
            } else {
                $conversation = $this->getConversation($message->conversationId);
            }

            $item = array();

            $item['convId'] = (int)$message->conversationId;

            $item['mode'] = ($conversation->subject == self::CHAT_CONVERSATION_SUBJECT) ? 'chat' : 'mail';
            $item['replyId'] = $message->replyId;
            $senderName = BOL_UserService::getInstance()->getDisplayName($message->senderId);
            $recipientName = BOL_UserService::getInstance()->getDisplayName($message->recipientId);
            $item['sender_name'] = $senderName;
            $item['recipient_name'] = $recipientName;
            $item['replyMessage'] = null;
            if (isset($message->replyId)) {
                $replyMessage = MAILBOX_BOL_MessageDao::getInstance()->findById($message->replyId);
                if (isset($replyMessage)) {
                    $senderName = BOL_UserService::getInstance()->getDisplayName($replyMessage->senderId);
                    $item['reply_sender'] = $senderName;
                    $text = $this->json_decode_text($replyMessage->text);
                    $item['replyMessage'] = $text;
                }
            }
            $conversationItem = $this->getConversationItem($item['mode'], $message->conversationId, null, $params);
            $item['opponentId'] = $conversationItem['opponentId'];
            $senderName = BOL_UserService::getInstance()->getDisplayName($message->senderId);
            $item['senderName'] = $senderName;
            $senderUrl = BOL_UserService::getInstance()->getUserUrl($message->senderId);
            $item['senderUrl'] = $senderUrl;
            $senderAvatar = BOL_AvatarService::getInstance()->getAvatarUrl($message->senderId);
            $item['senderAvatar'] = $senderAvatar;
            $item['id'] = (int)$message->id;
            $item['date'] = date('Y-m-d', (int)$message->timeStamp);
            $item['dateLabel'] = UTIL_DateTime::formatDate((int)$message->timeStamp, true);
            $item['timeStamp'] = (int)$message->timeStamp;

            $item['timeLabel'] = $militaryTime ? strftime("%H:%M", (int)$message->timeStamp) : strftime("%I:%M%p", (int)$message->timeStamp);
            $item['recipientId'] = (int)$message->recipientId;
            $item['senderId'] = (int)$message->senderId;
            $item['isAuthor'] = (bool)((int)$message->senderId == OW::getUser()->getId());
            $item['recipientRead'] = (int)$message->recipientRead;
            $item['isSystem'] = (int)$message->isSystem;
            $item['attachments'] = array();

            if ( (int)$conversation->initiatorId == OW::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
            }

            if ( (int)$conversation->interlocutorId == OW::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
            }

            $item['readMessageAuthorized'] = true;

            if ($message->isSystem)
            {
                $eventParams = json_decode($message->text, true);
                $eventParams['params']['messageId'] = (int)$message->id;

                $event = new OW_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                if (!empty($data))
                {
                    $text = $data;
                }
                else
                {
                    $text = '<div class="ow_dialog_item odd">'.OW::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType'])).'</div>';
                }

            }
            else
            {
                $text = $message->text;
            }

            if ($attachmentsByMessageList === null)
            {
                $attachments = $this->attachmentDao->findAttachmentsByMessageId($message->id);
            }
            else
            {
                $attachments = array_key_exists($message->id, $attachmentsByMessageList) ? $attachmentsByMessageList[$message->id] : array();
            }

            if (!empty($attachments))
            {
                foreach($attachments as $attachment)
                {
                    $ext = UTIL_File::getExtension($attachment->fileName);
                    $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                    $attItem = array();
                    $attItem['id'] = (int) $attachment->id;
                    $attItem['messageId'] = (int) $attachment->messageId;
                    $attItem['downloadUrl'] = OW::getStorage()->getFileUrl($attachmentPath);
                    $attItem['fileName'] = $attachment->fileName;
                    $attItem['fileSize'] = $attachment->fileSize;
                    $attItem['type'] = $this->getAttachmentType($attachment);

                    $item['attachments'][] = $attItem;
                }
            }

            $item['text'] = $text;

            $list[] = $item;
        }

        return $list;
    }

    /**
     * Returns conversation info
     *
     * @param int $conversationId
     * @throws InvalidArgumentException
     * @return MAILBOX_BOL_Conversation
     */
    public function getConversation( $conversationId )
    {
        if ( empty($conversationId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        return $this->conversationDao->findById($conversationId);
    }

    /**
     * Creates New Message
     *
     * @param MAILBOX_BOL_Conversation $conversation
     * @param int $senderId
     * @param string $text
     * @param null $replyId
     * @param boolean $isSystem
     * @return MAILBOX_BOL_Message
     */
    public function createMessage( MAILBOX_BOL_Conversation $conversation, $senderId, $text, $replyId = null, $isSystem = false, $tmpMessageUid=null, $isForwarded = false, $costumeFeatures = null )
    {
        if ( empty($senderId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        if ( $conversation === null )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'conversation_doesnt_exist_error');
            throw new InvalidArgumentException($errorMessage);
        }

        if ( empty($conversation->id) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'conversationId_doesnt_exist_error', array('$conversationId' => $conversation->id));
            throw new InvalidArgumentException($errorMessage);
        }

        if ( !in_array($senderId, array($conversation->initiatorId, $conversation->interlocutorId)) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'wrong_senderId_error');
            throw new InvalidArgumentException($errorMessage);
        }

        if ( BOL_UserService::getInstance()->isBlocked($conversation->initiatorId, $conversation->interlocutorId) ||
             BOL_UserService::getInstance()->isBlocked($conversation->interlocutorId, $conversation->initiatorId)) {
            $errorMessage = OW::getLanguage()->text('mailbox', 'you_blocked_opponnet_in_chat');
            throw new InvalidArgumentException($errorMessage);
        }

        $senderId = (int) $senderId;
        $recipientId = ($senderId == $conversation->initiatorId) ? $conversation->interlocutorId : $conversation->initiatorId;

        $message = $this->addMessage($conversation, $senderId, $text, $replyId, $isSystem, $isForwarded, $costumeFeatures);

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $text)));
        if (isset($stringRenderer->getData()['string'])) {
            $text = ($stringRenderer->getData()['string']);
        }

        $event = new OW_Event('mailbox.send_message', array(
            'senderId' => $senderId,
            'recipientId' => $recipientId,
            'conversationId' => $conversation->id,
            'isSystem' => $isSystem,
            'message' => $text,
            'isForwarded' => $isForwarded,
            'tmpMessageUid' => $tmpMessageUid
        ), $message);
        OW::getEventManager()->trigger($event);

        $this->resetUserLastData($senderId);
        $this->resetUserLastData($recipientId);

        return $message;
    }

    public function forwardMessage( MAILBOX_BOL_Conversation $conversation, $senderId, $text, $replyId = null, $isSystem = false, $tmpMessageUid=null, $isForwarded = false )
    {
        return $this->createMessage(  $conversation, $senderId, $text, $replyId , $isSystem , $tmpMessageUid, $isForwarded );
    }

    /**
     * @param $conversationId
     * @return MAILBOX_BOL_Message
     */
    public function getLastMessage( $conversationId )
    {
        return $this->messageDao->findLastMessage($conversationId);
    }

    public function getFirstMessage( $conversationId )
    {
        return $this->messageDao->findFirstMessage($conversationId);
    }

    public function deleteConverstionByUserId( $userId )
    {
        $count = 1000;
        $first = 0;

        if ( !empty($userId) )
        {
            $conversationList = array();

            do
            {
                $conversationList = $this->conversationDao->getConversationListByUserId($userId, $first, $count);

                $conversationIdList = array();

                foreach ( $conversationList as $conversation )
                {
                    $conversationIdList[$conversation['id']] = $conversation['id'];
                }

                if ( !empty($conversationIdList) )
                {
                    $this->conversationDao->deleteByIdList($conversationIdList);
                    $this->deleteAttachmentsByConversationList($conversationIdList);
                }

                foreach ( $conversationList as $conversation )
                {
                    $conversationIdList[$conversation['id']] = $conversation['id'];

                    $dto = new MAILBOX_BOL_Conversation();
                    $dto->id = $conversation['id'];
                    $dto->initiatorId = $conversation['initiatorId'];
                    $dto->interlocutorId = $conversation['interlocutorId'];
                    $dto->subject = $conversation['subject'];
                    $dto->read = $conversation['read'];
                    $dto->deleted = $conversation['deleted'];
                    $dto->createStamp = $conversation['createStamp'];

                    $paramList = array(
                        'conversationDto' => $dto
                    );

                    $event = new OW_Event(self::EVENT_DELETE_CONVERSATION, $paramList);
                    OW::getEventManager()->trigger($event);
                }

                $first += $count;
            }
            while ( !empty($conversationList) );
        }
    }

    public function onSendMessageAttachmentWebSocket(OW_Event $event) {
        if (FRMSecurityProvider::isSocketEnable()) {
            $params = $event->getParams();
            if (isset($params['messageId'])) {
                $messageId = $params['messageId'];
                $message = MAILBOX_BOL_MessageDao::getInstance()->findById($messageId);
                if (isset($message)) {
                    $this->sendNewMessageToWebSocket($message);
                }
            }
        }
    }

    public function onSendMessageWebSocket(OW_Event $event) {

        if (FRMSecurityProvider::isSocketEnable()) {
            $params = $event->getParams();
            $tmpMessageUid = null;
            if(isset($params['tmpMessageUid'])) {
                $tmpMessageUid = $params['tmpMessageUid'];
            }
            if (isset($params['senderId']) && isset($params['recipientId'])) {
                if (isset($_FILES) && sizeof($_FILES) > 0) {
                    return;
                }
                $message = $event->getData();
                if (isset($message)) {
                    $this->sendNewMessageToWebSocket($message, $tmpMessageUid);
                }
            }
        }
    }

    public function onAfterMessageRemoved(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['senderId']) || !isset($params['conversationId']) || !isset($params['recipientId']) || !isset($params['id'])) {
            return;
        }
        $this->sendRemovedMessageToWebSocket($params['conversationId'], $params['senderId'], $params['recipientId'], $params['id']);
    }

    public function onAfterMessageEdited(OW_Event $event){
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }
        $message = $event->getData();
        $params = $event->getParams();
        if (!isset($params['senderId']) || !isset($params['conversationId']) || !isset($params['recipientId']) || !isset($params['id'])) {
            return;
        }

        $editedText = $this->json_decode_text($message->text);
        $stripedStringEvent = OW::getEventManager()->trigger(new OW_Event('base.strip_raw_string', array('string' => $editedText)));
        if (isset($stripedStringEvent->getData()['string'])) {
            $editedText = $stripedStringEvent->getData()['string'];
        }
        $editedText = trim($editedText);
        $editedText = trim($editedText, '"');
        $this->sendEditedMessageToWebSocket($params['conversationId'], $params['senderId'], $params['recipientId'], $params['id'], $editedText);
    }
    /***
     * @param $message
     */
    public function sendNewMessageToWebSocket($message, $tmpMessageUid=null) {
        if ($message == null || !isset($message->senderId) || !isset($message->recipientId)) {
            return;
        }

        if (!FRMSecurityProvider::isSocketEnable()) {
            return;
        }

        // web socket send data
        if (isset($message->conversationId)) {
            $messageData = $this->getRawMessageInfo($message);

            $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $messageData['text'])));
            if (isset($stringRenderer->getData()['string'])) {
                $messageData['text'] = ($stringRenderer->getData()['string']);
            }

            $conv = $this->getConversationItem('chat', $message->conversationId, $message->senderId);
            if ($conv != null) {
                $messageInfo = array();
                $preparedConversationEvent = OW::getEventManager()->trigger(new OW_Event('mailbox.get_conversation_info', array('conversation' => $conv, 'count' => 0, 'returnMessages' => false)));
                if (isset($preparedConversationEvent->getData()['conversationInfo'])) {
                    $messageInfo = $preparedConversationEvent->getData()['conversationInfo'];
                }
                if($tmpMessageUid != null)
                    $messageInfo['tmpMessageUid'] = $tmpMessageUid;
                $messageInfo['messageData'] = $messageData;
                $messageInfo['type'] = 'new_message';
                $messageInfo['opponentId'] = (int) $message->recipientId;
                $messageInfo['userId'] = (int) $message->senderId;

                $unread_conversations_count = $this->getUnreadConversationsCount((int) $message->senderId);
                $messageInfo['unread_conversations_count'] = (int) $unread_conversations_count;
                OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageInfo, 'userId' => (int) $message->senderId)));
            }

            $conv = $this->getConversationItem('chat', $message->conversationId, $message->recipientId);
            if ($conv != null) {
                $messageInfo = array();
                $preparedConversationEvent = OW::getEventManager()->trigger(new OW_Event('mailbox.get_conversation_info', array('conversation' => $conv, 'count' => 0, 'returnMessages' => false)));
                if (isset($preparedConversationEvent->getData()['conversationInfo'])) {
                    $messageInfo = $preparedConversationEvent->getData()['conversationInfo'];
                }
                $messageInfo['messageData'] = $messageData;
                $messageInfo['type'] = 'new_message';
                $messageInfo['opponentId'] = (int) $message->senderId;
                $messageInfo['userId'] = (int) $message->recipientId;

                $unread_conversations_count = $this->getUnreadConversationsCount((int) $message->recipientId);
                $messageInfo['unread_conversations_count'] = (int) $unread_conversations_count;

                OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageInfo, 'userId' => (int) $message->recipientId)));
            }
        }
    }

    public function getUnreadConversationsCount($userId = null) {
        if ($userId == null && OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
        }
        $unreadConversations = 0;
        if($userId == null){
            return $unreadConversations;
        }
        return MAILBOX_BOL_ConversationService::getInstance()->getMarkedUnreadConversationCount( $userId );
    }

    public function onMarkConversationWebSocket(OW_Event $event){

        if (!FRMSecurityProvider::isSocketEnable()) {
            return;
        }
        $params = $event->getParams();
        if(isset($params['conversationIdList']) && isset($params['markType'])){
            $conversationIdList = $params['conversationIdList'];
            $markType = $params['markType'];

            if(!is_array($conversationIdList)){
                return;
            }

            if($markType != 'read'){
                return;
            }

            $data = array();
            foreach ($conversationIdList as $cid){
                $conversation = $this->getConversation($cid);
                if($conversation != null){
                    $singleData = array();
                    $singleData['userId1'] = $conversation->initiatorId;
                    $singleData['userId2'] = $conversation->interlocutorId;
                    $singleData['conversationId'] = $cid;
                    $data[] = $singleData;
                }
            }

            foreach ($data as $singleData){
                if (OW::getUser()->getId() != $singleData['userId1']) {
                    $this->sendMarkMessageToWebSocket($singleData['conversationId'], $singleData['userId2'], $singleData['userId1'], $singleData['userId1']);
                }
                if (OW::getUser()->getId() != $singleData['userId2']) {
                    $this->sendMarkMessageToWebSocket($singleData['conversationId'], $singleData['userId1'], $singleData['userId2'], $singleData['userId2']);
                }
            }
        }
    }

    /***
     * @param $conversationId
     * @param $userId1
     * @param $userId2
     * @param $sendToUserId
     */
    public function sendMarkMessageToWebSocket($conversationId, $userId1, $userId2, $sendToUserId) {
        if ($conversationId == null) {
            return;
        }

        if (!FRMSecurityProvider::isSocketEnable()) {
            return;
        }

        // web socket send data
        $conv = $this->getConversationItem('chat', $conversationId, $sendToUserId);
        if ($conv != null) {
            $messageData = array();
            $preparedConversationEvent = OW::getEventManager()->trigger(new OW_Event('mailbox.get_conversation_info', array('conversation' => $conv, 'count' => 1, 'returnMessages' => true)));
            if (isset($preparedConversationEvent->getData()['conversationInfo'])) {
                $messageData = $preparedConversationEvent->getData()['conversationInfo'];
            }
            $messageData['opponentId'] = (int) $userId1;
            $messageData['userId'] = (int) $userId2;
            $messageData['markedMessages'] = true;
            $messageData['type'] = 'mark_message';
            if (isset($messageData['messages']) && sizeof($messageData['messages']) > 0) {
                $messageData['lastMessageId'] = $messageData['messages'][0]['id'];
            } else {
                $messageData['lastMessageId'] = null;
            }

            $unread_conversations_count = $this->getUnreadConversationsCount((int) $sendToUserId);
            $messageData['unread_conversations_count'] = (int) $unread_conversations_count;
            OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageData, 'userId' => (int) $sendToUserId)));
        }
    }

    public function sendRemovedMessageToWebSocket($conversationId, $senderId, $recipientId, $removedMessageId) {
        if (!FRMSecurityProvider::isSocketEnable()) {
            return;
        }

        // web socket send data
        $conv = MAILBOX_BOL_ConversationService::getInstance()->getConversationItem('chat', $conversationId);
        if ($conv != null) {
            $messageData = array();
            $preparedConversationEvent = OW::getEventManager()->trigger(new OW_Event('mailbox.get_conversation_info', array('conversation' => $conv, 'count' => 0, 'returnMessages' => false)));
            if (isset($preparedConversationEvent->getData()['conversationInfo'])) {
                $messageData = $preparedConversationEvent->getData()['conversationInfo'];
            }
            $messageData['opponentId'] = $senderId;
            $messageData['userId'] = $recipientId;
            $messageData['removedMessageId'] = $removedMessageId;
            $messageData['type'] = 'removed_message';
            OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageData, 'userId' => (int) $senderId)));
            OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageData, 'userId' => (int) $recipientId)));
        }
    }

    public function sendEditedMessageToWebSocket($conversationId, $senderId, $recipientId, $editedMessageId, $editedMessageText) {
    if (!FRMSecurityProvider::isSocketEnable()) {
        return;
    }

    // web socket send data
    $conv = MAILBOX_BOL_ConversationService::getInstance()->getConversationItem('chat', $conversationId);
    if ($conv != null) {
        $messageData = array();
        $preparedConversationEvent = OW::getEventManager()->trigger(new OW_Event('mailbox.get_conversation_info', array('conversation' => $conv, 'count' => 0, 'returnMessages' => false)));
        if (isset($preparedConversationEvent->getData()['conversationInfo'])) {
            $messageData = $preparedConversationEvent->getData()['conversationInfo'];
        }
        $messageData['opponentId'] = $senderId;
        $messageData['userId'] = $recipientId;
        $messageData['editedMessageId'] = (int) $editedMessageId;
        $messageData['editedMessageText'] = $editedMessageText;
        $messageData['type'] = 'edited_message';
        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageData, 'userId' => (int) $senderId)));
        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $messageData, 'userId' => (int) $recipientId)));
    }
}


    public function deleteUserContent( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $this->deleteConverstionByUserId($userId);
        }
    }

    public function getConversationUrl( $conversationId, $redirectTo = null )
    {
        $params = array();
        $params['convId'] = $conversationId;

        if ( $redirectTo !== null )
        {
            $params['redirectTo'] = $redirectTo;
        }

        return OW::getRouter()->urlForRoute('mailbox_conversation', $params);
    }

    /**
     * @param int $initiatorId
     * @param int $interlocutorId
     * @throws InvalidArgumentException
     * @return array<MAILBOX_BOL_Conversation>
     */
    public function findConversationList( $initiatorId, $interlocutorId )
    {
        if ( empty($initiatorId) || !isset($interlocutorId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        return $this->conversationDao->findConversationList($initiatorId, $interlocutorId);
    }

    /**
     * @param MAILBOX_BOL_Conversation $conversationd
     */
    public function saveConversation( MAILBOX_BOL_Conversation $conversation )
    {
        $this->conversationDao->save($conversation);
    }

    /**
     * @param $ids
     * @return array
     */
    public function findConversationListByIds( $ids )
    {
        return $this->conversationDao->findConversationListByIds($ids);
    }

    /**
     * Add message to conversation
     *
     * @param MAILBOX_BOL_Conversation $conversation
     * @param int $senderId
     * @param string $text
     * @param null $replyId
     * @param boolean $isSystem
     * @return MAILBOX_BOL_Message
     */
    public function addMessage( MAILBOX_BOL_Conversation $conversation, $senderId, $text, $replyId = null, $isSystem = false, $isForwarded = false, $costumeFeatures = null )
    {
        if ( empty($senderId) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        if ( $conversation === null )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'conversation_doesnt_exist_error');
            throw new InvalidArgumentException($errorMessage);
        }

        if ( empty($conversation->id) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'conversationId_doesnt_exist_error', array('$conversationId' => $conversation->id));
            throw new InvalidArgumentException($errorMessage);
        }

        if ( !in_array($senderId, array($conversation->initiatorId, $conversation->interlocutorId)) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'wrong_senderId_error');
            throw new InvalidArgumentException($errorMessage);
        }

        $senderId = (int) $senderId;
        $recipientId = ($senderId == $conversation->initiatorId) ? $conversation->interlocutorId : $conversation->initiatorId;

        $text = trim($text);

        if ( !isset($text) || $text == '' )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        // create message
        $message = new MAILBOX_BOL_Message();
        $message->conversationId = $conversation->id;
        $message->senderId = $senderId;
        $message->recipientId = $recipientId;
        $message->text = $text;
        $message->timeStamp = time();
        $message->isSystem = $isSystem;
        $message->isForwarded = $isForwarded;
        $message->costumeFeatures = $costumeFeatures;

        $replyMessage = MAILBOX_BOL_MessageDao::getInstance()->findById($replyId);
        if(isset($replyMessage) && $replyMessage->conversationId == $conversation->getId())
            $message->replyId = $replyId;

        $message->text = $this->json_encode_text($message->text);
        $this->messageDao->save($message);
        $message->text = $text;

        // insert record into LastMessage table
        $lastMessage = $this->lastMessageDao->findByConversationId($conversation->id);

        if ( $lastMessage === null )
        {
            $lastMessage = new MAILBOX_BOL_LastMessage();
            $lastMessage->conversationId = $conversation->id;
        }

        switch ( $senderId )
        {
            case $conversation->initiatorId :

                $unReadBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;
                $readBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
                $unDeletedBy = MAILBOX_BOL_ConversationDao::DELETED_INTERLOCUTOR;
                $lastMessage->initiatorMessageId = $message->id;
                $consoleViewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

                break;

            case $conversation->interlocutorId :
                if($lastMessage->initiatorMessageId == null){
                    $lastMessage->initiatorMessageId = $conversation->initiatorId;
                }
                $unReadBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
                $readBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;
                $unDeletedBy = MAILBOX_BOL_ConversationDao::DELETED_INITIATOR;
                $lastMessage->interlocutorMessageId = $message->id;
                $consoleViewed = MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR;

                break;
        }

        $conversation->deleted = (int) $conversation->deleted & ($unDeletedBy);
        $conversation->read = ( (int) $conversation->read & (~$unReadBy) ) | $readBy;
        $conversation->viewed = $consoleViewed;
        $conversation->notificationSent = 0;

        $conversation->lastMessageId = $message->id;
        if($isForwarded){
            $conversation->lastMessageTimestamp = $message->timeStamp;
        }else{
            $conversation->lastMessageTimestamp = time();
        }

        $this->conversationDao->save($conversation);

        $this->lastMessageDao->save($lastMessage);

        OW::getEventManager()->trigger(new OW_Event(self::EVENT_AFTER_ADD_MESSAGE));

        return $message;
    }

    public function saveMessage($message)
    {
        $this->messageDao->save($message);

        return $message;
    }

    /**
     * Add Attachment files to message
     *
     * @param int $messageId
     * @param array $filesList
     */
    public function addMessageAttachments( $messageId, $fileList )
    {
        $configs = OW::getConfig()->getValues('mailbox');

        if ( empty($configs['enable_attachments']) )
        {
            return;
        }

        foreach($fileList as $file)
        {
            $dto = $file['dto'];
            $fileName = htmlspecialchars( $dto->origFileName );
            $attachmentDto = new MAILBOX_BOL_Attachment();
            $attachmentDto->messageId = $messageId;
            $attachmentDto->fileName = htmlspecialchars( $fileName );
            $attachmentDto->fileSize = $dto->size;
            $attachmentDto->hash = FRMSecurityProvider::generateUniqueId();

            $this->addAttachment($attachmentDto, $file['path']);
        }

        $event = new OW_Event('mailbox.send_message_attachment', array('messageId' => $messageId));
        OW::getEventManager()->trigger($event);
    }

    public function forwardMessageAttachments( $messageId, $fileList )
    {
        $configs = OW::getConfig()->getValues('mailbox');

        if ( empty($configs['enable_attachments']) )
        {
            return;
        }

        foreach($fileList as $file)
        {
            $fileName = htmlspecialchars( $file->fileName );
            $attachmentDto = new MAILBOX_BOL_Attachment();
            $attachmentDto->messageId = $messageId;
            $attachmentDto->fileName = htmlspecialchars( $fileName );
            $attachmentDto->fileSize = $file->fileSize;
            $attachmentDto->hash = FRMSecurityProvider::generateUniqueId();
            $filePath =  MAILBOX_BOL_ConversationService::getInstance()->getAttachmentFilePath($file->id, $file->hash, UTIL_File::getExtension($file->fileName), $file->fileName);

            $this->attachmentDao->save($attachmentDto);

            $attId = $attachmentDto->id;
            $ext = UTIL_File::getExtension($attachmentDto->fileName);

            $attachmentPath = $this->getAttachmentFilePath($attId, $attachmentDto->hash, $ext, $attachmentDto->fileName);

            $storage = OW::getStorage();
            if ( $storage->fileExists($filePath) )
            {
                $storage->copyFile($filePath, $attachmentPath);
            }
        }

        $event = new OW_Event('mailbox.send_message_attachment', array('messageId' => $messageId));
        OW::getEventManager()->trigger($event);
    }

    /***
     * @param $AttachmentId
     * @param $messageId
     * @return void
     */
    public function forwardAttachmentIdToChat( $AttachmentId,$messageId )
    {
        $configs = OW::getConfig()->getValues('mailbox');

        if ( empty($configs['enable_attachments']) )
        {
            return;
        }
        $file = BOL_AttachmentDao::getInstance()->findById($AttachmentId);

        $dto = $file;
        $fileName = htmlspecialchars( $dto->origFileName );
        $attachmentDto = new MAILBOX_BOL_Attachment();
        $attachmentDto->messageId = $messageId;
        $attachmentDto->fileName = htmlspecialchars( $fileName );
        $attachmentDto->fileSize = $dto->size;
        $attachmentDto->hash = FRMSecurityProvider::generateUniqueId();

        $path =  BOL_AttachmentService::getInstance()->getAttachmentsDir(). $file->fileName;
        $this->addAttachment($attachmentDto, $path);

        $event = new OW_Event('mailbox.send_message_attachment', array('messageId' => $messageId));
        OW::getEventManager()->trigger($event);
    }

    /**
     * Add attachment
     *
     * @param MAILBOX_BOL_Attachment $attachmentDto
     * @param string $filePath
     * @param boolean
     */
    public function addAttachment( $attachmentDto, $filePath )
    {
        $this->attachmentDao->save($attachmentDto);

        $attId = $attachmentDto->id;
        $ext = UTIL_File::getExtension($attachmentDto->fileName);

        $attachmentPath = $this->getAttachmentFilePath($attId, $attachmentDto->hash, $ext, $attachmentDto->fileName);
        $pluginFilesPath = OW::getPluginManager()->getPlugin('mailbox')->getPluginFilesDir() . FRMSecurityProvider::generateUniqueId('attach');

        $storage = OW::getStorage();
        if ( $storage->fileExists($filePath) )
        {
            $storage->renameFile($filePath, $attachmentPath);
            OW::getStorage()->removeFile($pluginFilesPath, true);
            OW::getStorage()->removeFile($filePath, true);

            return true;
        }
        else
        {
            $this->attachmentDao->deleteById($attId);
            return false;
        }
    }

    public function getAttachmentType(MAILBOX_BOL_Attachment $attachment)
    {
        $type = 'doc';

        if (UTIL_File::validateImage($attachment->fileName))
        {
            $type = 'image';
        }

        return $type;
    }

    public function getAttachmentFilePath( $attId, $hash, $ext, $name = null )
    {
        return $this->getAttachmentDir() . $this->getAttachmentFileName($attId, $hash, $ext, $name);
    }

    public function getAttachmentDir()
    {
        return OW::getPluginManager()->getPlugin('mailbox')->getUserFilesDir() . 'attachments' . DS;
    }

    public function getAttachmentUrl()
    {
        return OW::getPluginManager()->getPlugin('mailbox')->getUserFilesUrl() . 'attachments/';
    }

    public function getAttachmentFileName( $attId, $hash, $ext, $name )
    {
        $lastAttId = 0;
        if (OW::getConfig()->configExists('mailbox', 'last_attachment_id'))
        {
            $lastAttId = (int)OW::getConfig()->getValue('mailbox', 'last_attachment_id');
        }

        if ($attId <= $lastAttId)
        {
            return 'attachment_' . $attId . '_' . $hash . (strlen($ext) ? '.' . $ext : '');
        }

        return 'attachment_' . $attId . '_' . $hash . (mb_strlen($name) ? '_' . $name : (strlen($ext) ? '.' . $ext : ''));
    }

    public function fileExtensionIsAllowed( $ext )
    {
        if ( !strlen($ext) )
        {
            return false;
        }

        return in_array($ext, self::$allowedExtensions);
    }

    /**
     *
     * @param array $messageIdList
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function findAttachmentsByMessageIdList( array $messageIdList )
    {
        $result = array();
        $list = $this->attachmentDao->findAttachmentsByMessageIdList($messageIdList);
        foreach ($list as $attachment)
        {
            $result[$attachment->messageId][] = $attachment;
        }

        return $result;
    }

    /**
     * @param $userId
     * @param $opponentsId
     * @return array
     */
    public function findUserOpponentsUnreadMessages($userId, $opponentsId)
    {
        return $this->messageDao->findUserOpponentsUnreadMessages($userId, $opponentsId);
    }

    /**
     *
     * @param array $conversationIdList
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function getAttachmentsCountByConversationList( array $conversationIdList )
    {
        return $this->attachmentDao->getAttachmentsCountByConversationList($conversationIdList);
    }

    /**
     *
     * @param array $conversationIdList
     * @return array<MAILBOX_BOL_Attachment>
     */
    public function deleteAttachmentsByConversationList( array $conversationIdList )
    {
        $attachmentList = $this->attachmentDao->findAttachmentstByConversationList($conversationIdList);

        foreach ( $attachmentList as $attachment )
        {/* @var $attachment MAILBOX_BOL_Attachment */
            $ext = UTIL_File::getExtension($attachment->fileName);
            $path = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);
            if ( OW::getStorage()->fileExists($path) )
            {
                $attachment->fileName = ('deleted_' . FRMSecurityProvider::generateUniqueId() . '_' . $attachment->fileName);
                $this->attachmentDao->save($attachment);
                $newPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                OW::getStorage()->renameFile($path, $newPath);
//              OW::getStorage()->removeFile($path);
            }

            $this->attachmentDao->deleteById($attachment->id);
        }

        return $attachmentList;
    }

    /**
     *
     * @param array $conversationIdList
     * @return array<MAILBOX_BOL_Conversation>
     */
    public function getConversationListByIdList( $idList )
    {
        return $this->conversationDao->findByIdList($idList);
    }

    public function setConversationViewedInConsole( $idList, $userId )
    {
        $conversationList = $this->getConversationListByIdList($idList);
        /* @var $conversation MAILBOX_BOL_Conversation  */
        foreach ( $conversationList as $conversation )
        {
            $pre_value = $conversation->viewed;
            if ( $conversation->initiatorId == $userId )
            {
                $conversation->viewed = $conversation->viewed | MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;
            }

            if ( $conversation->interlocutorId == $userId )
            {
                $conversation->viewed = $conversation->viewed | MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR;
            }

            if($conversation->viewed != $pre_value) {
                $this->saveConversation($conversation);
            }
        }

        $this->resetUserLastData($userId);
    }

    public function getConversationListForConsoleNotificationMailer( $userIdList )
    {
        return $this->conversationDao->getNewConversationListForConsoleNotificationMailer($userIdList);
    }

    public function getConversationPreviewTextById($conversationId, $params = array())
    {
        if(empty($conversationId) || $conversationId == 0){
            return '';
        }

        $conversationId = (int)$conversationId;

        $conversation = null;
        if (isset($params['cache']['conversations_items'][$conversationId])) {
            $conversation = $params['cache']['conversations_items'][$conversationId];
        }
        if ($conversation == null) {
            $conversation = $this->conversationDao->getConversationItem($conversationId);
        }

        $conversationRead = 0;
        $userId = OW::getUser()->getId();

        switch ( $userId )
        {
            case $conversation['initiatorId']:

                $conversationOpponentId = $conversation['interlocutorId'];

                if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                {
                    $conversationRead = 1;
                }

                break;

            case $conversation['interlocutorId']:

                $conversationOpponentId = $conversation['initiatorId'];

                if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                {
                    $conversationRead = 1;
                }

                break;
        }
        $conversation['conversationRead'] = $conversationRead;
        $conversation['opponentId'] = $conversationOpponentId;
        $conversation['mode'] = 'chat';

        if(isset($conversation)){
            $string = $this->getConversationPreviewText($conversation);
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $string)));
            if (isset($stringRenderer->getData()['string'])) {
                $string = ($stringRenderer->getData()['string']);
            }
            return $string;
        }
        return '';
    }

    public function getConversationPreviewText($conversation)
    {
        $convPreview = '';

        switch($conversation['mode'])
        {
            case 'mail':

                $convPreview = $conversation['subject'];

                break;

            case 'chat':

                if ($conversation['isSystem']) {
                    $eventParams = json_decode($conversation['text'], true);
                    $eventParams['params']['messageId'] = (int)$conversation['lastMessageId'];
                    $eventParams['params']['getPreview'] = true;

                    $mobileSupportEvent = OW::getEventManager()->trigger(new OW_Event('check.url.webservice', array()));
                    if (isset($mobileSupportEvent->getData()['isWebService']) && $mobileSupportEvent->getData()['isWebService']) {
                        $eventParams['params']['getMessage'] = true;
                    }

                    $event = new OW_Event($eventParams['entityType'] . '.' . $eventParams['eventName'], $eventParams['params']);
                    OW::getEventManager()->trigger($event);

                    $data = $event->getData();

                    if (!empty($data)) {
                        $convPreview = $data;
                    } else {
                        $convPreview = OW::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType' => $eventParams['entityType']));
                    }
                } else {
                    $short = mb_strlen($conversation['text']) > 50 ? mb_substr($conversation['text'], 0, 50) . '...' : $conversation['text'];
//                        $short = UTIL_HtmlTag::autoLink($short);

                    $event = new OW_Event('mailbox.message_render', array(
                        'conversationId' => $conversation['id'],
                        'messageId' => $conversation['lastMessageId'],
                        'senderId' => $conversation['lastMessageSenderId'],
                        'recipientId' => $conversation['lastMessageRecipientId'],
                    ), array('short' => $short, 'full' => $conversation['text']));

                    OW::getEventManager()->trigger($event);

                    $eventData = $event->getData();

                    $convPreview = $eventData['short'];
                }

                break;
        }

        return $convPreview;
    }


    public function getConversationPreviewTextForApi($conversation)
    {
        if ($conversation['isSystem'])
        {
            $eventParams = json_decode($conversation['text'], true);
            $eventParams['params']['messageId'] = (int)$conversation['lastMessageId'];
            $eventParams['params']['getPreview'] = true;

            $mobileSupportEvent= OW::getEventManager()->trigger(new OW_Event('check.url.webservice',array()));
            if(isset($mobileSupportEvent->getData()['isWebService']) && $mobileSupportEvent->getData()['isWebService'])
            {
                $eventParams['params']['getMessage'] = true;
            }

            $event = new OW_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if (!empty($data))
            {
                $convPreview = $data;
            }
            else
            {
                $convPreview = OW::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType']));
            }
        }
        else
        {
            $short = mb_strlen($conversation['text']) > 200 ? mb_substr($conversation['text'], 0, 200) . '...' : $conversation['text'];
//                        $short = UTIL_HtmlTag::autoLink($short);

            $event = new OW_Event('mailbox.message_render', array(
                'conversationId' => $conversation['id'],
                'messageId' => $conversation['lastMessageId'],
                'senderId' => $conversation['lastMessageSenderId'],
                'recipientId' => $conversation['lastMessageRecipientId'],
            ), array( 'short' => $short, 'full' => $conversation['text'] ));

            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();
            //TODO check if native needs <br> to be stripped or not
            if(!isset($eventParams['params']['getMessage']))
            {
                $eventData['short'] = preg_replace("/<br\W*?\/>/", " ", $eventData['short']);
                $convPreview = strip_tags($eventData['short'],"<br>");
            }
            else {
                $convPreview = strip_tags($eventData['short']);
            }
        }

        return $convPreview;
    }

    public function prepareConversationItem($conversation, $convId, $userId, $mode, $params = array()) {
        $conversationRead = 0;
        $conversationHasReply = false;

        switch ( $userId )
        {
            case $conversation['initiatorId']:

                $conversationOpponentId = $conversation['interlocutorId'];

                if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                {
                    $conversationRead = 1;
                }

                break;

            case $conversation['interlocutorId']:

                $conversationOpponentId = $conversation['initiatorId'];

                if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                {
                    $conversationRead = 1;
                }

                break;
        }

        switch($userId)
        {
            case $conversation['lastMessageSenderId']:
                $conversationHasReply = false;
                break;

            case $conversation['lastMessageRecipientId']:
                $conversationHasReply = true;
                break;
        }

        $conversation['opponentId'] = $conversationOpponentId;
        $conversation['conversationRead'] = $conversationRead;
        $conversation['mode'] = $mode;

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName($conversationOpponentId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName($conversationOpponentId) : $profileDisplayname;
        $profileUrl = BOL_UserService::getInstance()->getUserUrl($conversationOpponentId);
        $avatarUrl = null;
        if (isset($params['cache']['users_info'][$conversationOpponentId]['src'])) {
            $avatarUrl = $params['cache']['users_info'][$conversationOpponentId]['src'];
        }
        if ($avatarUrl == null) {
            $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($conversationOpponentId);
        }
        $profileAvatarUrl =$avatarUrl;
        $convDate = empty($conversation['timeStamp']) ? '' : UTIL_DateTime::formatDate((int)$conversation['timeStamp'], true);

        $convPreview = $this->getConversationPreviewText($conversation);

        $item = array();

        $item['conversationId'] = (int)$convId;
        $item['opponentId'] = (int)$conversationOpponentId;
        $item['mode'] = $mode;
        $item['conversationRead'] = (int)$conversationRead;
        $item['profileUrl'] = $profileUrl;
        $item['avatarUrl'] = $profileAvatarUrl;

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($conversationOpponentId));

        $item['avatarLabel'] = !empty($avatarData[$conversationOpponentId]) ? mb_substr($avatarData[$conversationOpponentId]['label'], 0, 1) : ' ';

        $item['displayName'] = $profileDisplayname;
        $item['dateLabel'] = $convDate;
        $item['previewText'] = $convPreview;
        $item['lastMessageTimestamp'] = (int)$conversation['timeStamp'];
        $item['reply'] = $conversationHasReply;

        $newMessageCount = 0;
        if (isset($params['cache']['unread_conversation_count'][$convId])) {
            $newMessageCount = $params['cache']['unread_conversation_count'][$convId];
        } else {
            $newMessageCount = $this->countUnreadMessagesForConversation($convId, $userId);
        }
        $item['newMessageCount'] = $newMessageCount;

        if ( (int)$conversation['initiatorId'] == OW::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
        }

        if ( (int)$conversation['interlocutorId'] == OW::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
        }

        if ($mode == 'chat')
        {
            $item['url'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$conversationOpponentId));
        }

        if ($mode == 'mail')
        {
            $item['url'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$convId));
        }

        return $item;
    }

    public function getConversationsItem($conversationsList, $mode = 'chat') {
        $conversations = MAILBOX_BOL_ConversationDao::getInstance()->getConversationsItem($conversationsList);

        $userId = OW::getUser()->getId();
        $conversationsInfo = array();
        foreach ($conversations as $conversation) {
            $convId = $conversation['id'];
            $conversationsInfo[] = $this->prepareConversationItem($conversation, $convId, $userId, $mode);
        }
        return $conversationsInfo;
    }

    public function getConversationItem($mode, $convId, $userId = null, $params = array())
    {
        if ($userId == null) {
            $userId = OW::getUser()->getId();
        }

        $conversation = null;
        if (isset($params['cache']['conversations_items'][$convId])) {
            $conversation = $params['cache']['conversations_items'][$convId];
        }
        if ($conversation == null) {
            $conversation = $this->conversationDao->getConversationItem($convId);
        }
        return $this->prepareConversationItem($conversation, $convId, $userId, $mode, $params);
    }

    public function getConversationItemByConversationIdList($conversationItemList)
    {
        $userId = OW::getUser()->getId();
        $convInfoList = array();

        $userIdList = array();
        $conversationIdList = array();
        foreach($conversationItemList as $conversation)
        {
            $conversationIdList[] = (int)$conversation['id'];

            if ($conversation['interlocutorId'] == $userId)
            {
                $opponentId = $conversation['initiatorId'];
            }
            else
            {
                $opponentId = $conversation['interlocutorId'];
            }

            if (!in_array($opponentId, $userIdList))
            {
                $userIdList[] = $opponentId;
            }
        }

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $unreadMessagesCountByConversationIdList = $this->countUnreadMessagesForConversationList($conversationIdList, $userId);
        $conversationsWithAttachments = $this->getConversationsWithAttachmentFromConversationList($conversationIdList);

        foreach($conversationItemList as $conversation)
        {
            $conversationId = (int)$conversation['id'];
            $mode = $conversation['subject'] == self::CHAT_CONVERSATION_SUBJECT ? 'chat' : 'mail';

            $conversationRead = 0;
            $conversationHasReply = false;

            switch ( $userId )
            {
                case $conversation['initiatorId']:

                    $opponentId = $conversation['interlocutorId'];
//                    $conversationHasReply = $conversation['interlocutorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                    {
                        $conversationRead = 1;
                    }

                    break;

                case $conversation['interlocutorId']:

                    $opponentId = $conversation['initiatorId'];
//                    $conversationHasReply = $conversation['initiatorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                    {
                        $conversationRead = 1;
                    }

                    break;
            }

//            pv($conversation);

            switch($userId)
            {
                case $conversation['lastMessageSenderId']:
                    $conversationHasReply = false;
                    break;

                case $conversation['lastMessageRecipientId']:
                    $conversationHasReply = true;
                    break;
            }

            $conversation['opponentId'] = $opponentId;
            $conversation['conversationRead'] = $conversationRead;
            $conversation['mode'] = $mode;

            $profileDisplayname = empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'];
            $profileUrl = $avatarData[$opponentId]['url'];
            $avatarUrl = $avatarData[$opponentId]['src'];
            $convDate = empty($conversation['timeStamp']) ? '' : UTIL_DateTime::formatDate((int)$conversation['timeStamp'], true);
            $convPreview = $this->getConversationPreviewText($conversation);

            $item = array();

            $item['conversationId'] = $conversationId;
            $item['opponentId'] = (int)$opponentId;
            $item['mode'] = $mode;
            $item['conversationRead'] = (int)$conversationRead;
            $item['profileUrl'] = $profileUrl;
            $item['avatarUrl'] = $avatarUrl;
            $item['avatarLabel'] = !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null;
            $item['displayName'] = $profileDisplayname;
            $item['dateLabel'] = $convDate;
            $item['previewText'] = $convPreview;
            $item['subject'] = $conversation['subject'];
            $item['lastMessageTimestamp'] = (int)$conversation['timeStamp'];
            $item['reply'] = $conversationHasReply;
            $item['newMessageCount'] = array_key_exists($conversationId, $unreadMessagesCountByConversationIdList) ? $unreadMessagesCountByConversationIdList[$conversationId] : 0;
            $item['hasAttachment'] = $conversationsWithAttachments[$conversationId];

            $shortUserData = $this->getFields(array($opponentId));
            $item['shortUserData'] = $shortUserData[$opponentId];

            if ( (int)$conversation['initiatorId'] == OW::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
            }

            if ( (int)$conversation['interlocutorId'] == OW::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
            }

            if ($mode == 'chat')
            {
                $item['url'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }

            if ($mode == 'mail')
            {
                $item['url'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$conversationId));
            }

            $convInfoList[] = $item;
        }


        return $convInfoList;
    }

    public function getConversationItemByConversationIdListForApi($conversationItemList)
    {
        $userId = OW::getUser()->getId();
        $convInfoList = array();

        $userIdList = array();
        $conversationIdList = array();
        foreach($conversationItemList as $conversation)
        {
            $conversationIdList[] = (int)$conversation['id'];

            if ($conversation['interlocutorId'] == $userId)
            {
                $opponentId = $conversation['initiatorId'];
            }
            else
            {
                $opponentId = $conversation['interlocutorId'];
            }

            if (!in_array($opponentId, $userIdList))
            {
                $userIdList[] = $opponentId;
            }
        }

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, false, true, true);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $unreadMessagesCountByConversationIdList = $this->countUnreadMessagesForConversationList($conversationIdList, $userId);
        $conversationsWithAttachments = $this->getConversationsWithAttachmentFromConversationList($conversationIdList);
        $onlineMap = BOL_UserService::getInstance()->findOnlineStatusForUserList($userIdList);

        $conversationIds = array();
        foreach($conversationItemList as $conversation) {
            $conversationIds[] = (int)$conversation['id'];
        }
        $cachedUnreadMessages = MAILBOX_BOL_MessageDao::getInstance()->findUnreadMessagesForConversations($conversationIds, $userId);
        $cachedConversationCount = $this->countUnreadMessagesForConversationByIds($conversationIds, $userId);

        foreach($conversationItemList as $conversation)
        {
            $conversationId = (int)$conversation['id'];
            $mode = $conversation['subject'] == self::CHAT_CONVERSATION_SUBJECT ? 'chat' : 'mail';

            $conversationRead = 0;
            $conversationHasReply = false;

            switch ( $userId )
            {
                case $conversation['initiatorId']:

                    $opponentId = $conversation['interlocutorId'];
//                    $conversationHasReply = $conversation['interlocutorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                    {
                        $conversationRead = 1;
                    }

                    break;

                case $conversation['interlocutorId']:

                    $opponentId = $conversation['initiatorId'];
//                    $conversationHasReply = $conversation['initiatorMessageId'] != 0 ? true : false;

                    if ( (int) $conversation['read'] & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                    {
                        $conversationRead = 1;
                    }

                    break;
            }

            $unreadMessages = array();
            if (isset($cachedUnreadMessages[$conversationId])) {
                $unreadMessages = $cachedUnreadMessages[$conversationId];
            } else {
                $unreadMessages = MAILBOX_BOL_MessageDao::getInstance()->findUnreadMessagesForConversation($conversationId, OW::getUser()->getId());
            }
            if ($unreadMessages == null || sizeof($unreadMessages)==0){
                $conversationRead = 1;
            }

//            pv($conversation);

            switch($userId)
            {
                case $conversation['lastMessageSenderId']:
                    $conversationHasReply = false;
                    break;

                case $conversation['lastMessageRecipientId']:
                    $conversationHasReply = true;
                    break;
            }

            $conversation['opponentId'] = $opponentId;
            $conversation['conversationRead'] = $conversationRead;
            $conversation['mode'] = $mode;

            $profileDisplayname = empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'];
//            $profileUrl = $avatarData[$opponentId]['url'];
            $avatarUrl = $avatarData[$opponentId]['src'];
            $convDate = empty($conversation['timeStamp']) ? '' : UTIL_DateTime::formatDate((int)$conversation['timeStamp'], true);
            $convPreview = $this->getConversationPreviewTextForApi($conversation);
            $originalPreview = $convPreview;
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $convPreview)));
            if (isset($stringRenderer->getData()['string'])) {
                $convPreview = ($stringRenderer->getData()['string']);
            }

            $item = array();

            $item['userId'] = (int)$opponentId; // Backward compatibility
            $item['conversationId'] = $conversationId;
            $item['opponentId'] = (int)$opponentId;
            $item['mode'] = $mode;
            $item['conversationRead'] = (int)$conversationRead;
//            $item['profileUrl'] = $profileUrl;
            $item['avatarUrl'] = $avatarUrl;
            $item['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo((int)$opponentId, $avatarUrl);
            $item['avatarLabel'] = !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null;
            $item['displayName'] = $profileDisplayname;
            $item['dateLabel'] = $convDate;
            $item['previewText'] = $convPreview;
            $item['text'] = $convPreview;
            $item['originalPreviewText'] = $originalPreview;
            $item['subject'] = $conversation['subject'];
            $item['lastMessageTimestamp'] = (int)$conversation['timeStamp'];
            $item['recipientRead'] = $conversation['recipientRead'];
            $item['lastMessageRecipientId'] = (int)$conversation['lastMessageRecipientId'];
            $item['reply'] = $conversationHasReply;
            $item['newMessageCount'] = array_key_exists($conversationId, $unreadMessagesCountByConversationIdList) ? $unreadMessagesCountByConversationIdList[$conversationId] : 0;
            $item['hasAttachment'] = $conversationsWithAttachments[$conversationId];

            $unreadMessagesCount = 0;
            if (isset($cachedConversationCount[$conversationId])) {
                $unreadMessagesCount = $cachedConversationCount[$conversationId];
            } else {
                $unreadMessagesCount = $this->countUnreadMessagesForConversation($conversationId, $userId);
            }
            $item['unreadCount'] = $unreadMessagesCount;

            $item['timeLabel'] = $conversation["timeStamp"] > 0 ? UTIL_DateTime::formatDate($conversation["timeStamp"]) : "";
            $item['onlineStatus'] = $onlineMap[$opponentId];

            $shortUserData = $this->getFields(array($opponentId));
            $item['shortUserData'] = $shortUserData[$opponentId];

            if ( (int)$conversation['initiatorId'] == OW::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
            }

            if ( (int)$conversation['interlocutorId'] == OW::getUser()->getId() )
            {
                $item['conversationViewed'] = (bool)((int)$conversation['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
            }

            if ($mode == 'chat')
            {
                $item['url'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }

            if ($mode == 'mail')
            {
                $item['url'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$conversationId));
            }
                $convInfoList[] = $item;
        }


        return $convInfoList;
    }

    /**
     * @param $messageId
     * @return MAILBOX_BOL_Message
     */
    public function getMessage($messageId)
    {
        return $this->messageDao->findById($messageId);
    }

    /**
     * @param int $userId
     * @param int $opponentId
     * @return MAILBOX_BOL_Conversation
     */
    public function findChatConversationWithUserById($userId, $opponentId) {
        return $this->conversationDao->findChatConversationWithUserById($userId, $opponentId);
    }

    public function getChatConversationIdWithUserById($userId, $opponentId)
    {
        return $this->conversationDao->findChatConversationIdWithUserById($userId, $opponentId);
    }

    public function getChatConversationIdWithUserByIdList($userId, $userIdList)
    {
        $result = array();

        $conversationIdList = $this->conversationDao->findChatConversationIdWithUserByIdList($userId, $userIdList);

        foreach($conversationIdList as $conversationInfo)
        {
            $result[$conversationInfo['opponentId']] = $conversationInfo['id'];
        }

        return $result;
    }

    public function getConversationMode($conversationId, $cachedParams = array())
    {
        $mode = 'mail';

        $conversation = null;
        if (isset($cachedParams['cache']['conversations'][$conversationId])) {
            $conversation = $cachedParams['cache']['conversations'][$conversationId];
        }
        if ($conversation == null) {
            $conversation = $this->getConversation($conversationId);
        }

        if ($conversation->subject == self::CHAT_CONVERSATION_SUBJECT)
        {
            $mode = 'chat';
        }

        return $mode;
    }

    public function getUserStatus($userId)
    {
        $userIdList = array($userId);

        $onlineInfo = $this->getUserStatusForUserIdList($userIdList);

        return $onlineInfo[$userId];
    }

    public function getUserStatusForUserIdList($userIdList)
    {
        $onlineInfo = array();
        $list = BOL_UserService::getInstance()->findOnlineStatusForUserList($userIdList);
        $privacyForUserIdList = $this->getViewPresenceOnSitePrivacySettingsForUserIdList( OW::getUser()->getId(), $userIdList );

        foreach($list as $userId => $status)
        {
            $viewPresenceOnSiteAllowed = $privacyForUserIdList[$userId];

            if ($viewPresenceOnSiteAllowed && $status > 0)
            {
                switch($status)
                {
                    case BOL_UserOnlineDao::CONTEXT_VAL_DESKTOP:
                        $onlineInfo[$userId] = 'status_online';
                        break;
                    case BOL_UserOnlineDao::CONTEXT_VAL_MOBILE:
                        $onlineInfo[$userId] = 'status_mobile';
                        break;
                    default:
                        $onlineInfo[$userId] = 'status_online';
                        break;
                }
            }
            else
            {
                $onlineInfo[$userId] = 'offline';
            }
        }

        return $onlineInfo;
    }

    public function getConversationHistory($conversationId, $beforeMessageId)
    {
        $count = 10;
        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);
        $dtoList = $this->messageDao->findHistory($conversationId, $beforeMessageId, $count, $deletedTimestamp);
        $list = array();

        $cachedParams['cache']['conversations'] = MAILBOX_BOL_ConversationDao::getInstance()->findByConversationIds(array($conversationId));
        $cachedParams['cache']['conversations_items'] = $this->conversationDao->getConversationsItem(array($conversationId));

        foreach($dtoList as $message)
        {
            $list[] = $this->getMessageData($message, null, $cachedParams);
        }

        $data = array(
            'log' => $list
        );

        return $data;
    }

    public function getConversationHistoryForApi($conversationId, $beforeMessageId)
    {
        $count = 10;
        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);
        $dtoList = $this->messageDao->findHistory($conversationId, $beforeMessageId, $count, $deletedTimestamp);
        $list = array();
        foreach($dtoList as $message)
        {
            $list[] = $this->getMessageDataForApi($message);
        }

        $data = array(
            'log' => $list
        );

        return $data;
    }

    public function getConversationDataAndLog($conversationId, $first = 0, $count = 16, $additionalParams = array())
    {
        $userId = OW::getUser()->getId();
        $conversation = null;
        if (isset($additionalParams['cache']['conversations'][$conversationId])) {
            $conversation = $additionalParams['cache']['conversations'][$conversationId];
        }
        if ($conversation == null) {
            $conversation = $this->getConversation($conversationId);
        }
        if (empty($conversation))
        {
            return array();
        }

        if ($conversation->initiatorId != $userId && $conversation->interlocutorId != $userId)
        {
            return array('close_dialog'=>true);
        }

        $list = $this->getConversationMessagesList($conversationId, $first, $count, $additionalParams);
        $language = OW::getLanguage();

        switch ( $userId )
        {
            case $conversation->initiatorId:

                $conversationOpponentId = (int)$conversation->interlocutorId;

                break;

            case $conversation->interlocutorId:

                $conversationOpponentId = (int)$conversation->initiatorId;

                break;
        }

        $data = array();
        $data['conversationId'] = $conversationId;
        $data['opponentId'] = $conversationOpponentId;
        $data['mode'] = $this->getConversationMode($conversationId, $additionalParams);
        $data['subject'] = $conversation->subject;

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName($conversationOpponentId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName($conversationOpponentId) : $profileDisplayname;
        $data['displayName'] = $profileDisplayname;
        $data['profileUrl'] = BOL_UserService::getInstance()->getUserUrl($conversationOpponentId);

        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($conversationOpponentId);
        $data['avatarUrl'] = $avatarUrl;

        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($conversationOpponentId));
        $data['avatarLabel'] = !empty($avatarData[$conversationOpponentId]) ? mb_substr($avatarData[$conversationOpponentId]['label'], 0, 1) : null;

        $data['status'] = $this->getUserStatus($conversationOpponentId);
        $data['log'] = $list;
        $data['logLength'] = $this->getConversationLength($conversationId, $additionalParams);
        $shortUserData = $this->getFields(array($conversationOpponentId));
        $data['shortUserData'] = $shortUserData[$conversationOpponentId];

        $checkResult = $this->checkUser($userId, $conversationOpponentId);

        $data['isSuspended'] = $checkResult['isSuspended'];
        if ($data['isSuspended'])
        {
            $data['suspendReasonMessage'] = $checkResult['suspendReasonMessage'];
        }

        return $data;
    }

    /**
     * @param $userId
     * @return MAILBOX_BOL_Message
     */
    public function getLastSentMessage($userId)
    {
        return $this->messageDao->findLastSentMessage($userId);
    }

    public function findUnreadMessages( $userId, $ignoreList, $timeStamp = null )
    {
        $list = array();

        $messages = $this->messageDao->findUnreadMessages($userId, $ignoreList, $timeStamp, $this->getActiveModeList());
        $list = $this->getMessageDataForList($messages);

        return $list;
    }

    public function markMessageIdListRead($messageIdList, $lastRequestTimestamp = null)
    {
        $this->markMessageIdListReadByUser($messageIdList, OW::getUser()->getId(), $lastRequestTimestamp);
    }

    public function markMessageIdListReadByUser($messageIdList, $userId, $lastRequestTimestamp = null)
    {
        $conversationIds = array();
        foreach($messageIdList as $messageId)
        {
            $message = $this->getMessage($messageId);
            if(!is_null($message) && $message->senderId != $userId) {
                if (!in_array($message->conversationId, $conversationIds)) {
                    $conversationIds[] = $message->conversationId;
                }

                $this->markMessageRead($messageId);
            }
        }

        if(!empty($conversationIds)){
            $this->markRead($conversationIds, $userId, $lastRequestTimestamp);
        }

    }

    private function getUserIdListAlt($userId)
    {
        $friendsEnabled = (bool)OW::getEventManager()->call('plugin.friends');
        if ($friendsEnabled)
        {
            $friendIdList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        }
        else
        {
            $friendIdList = array();
        }

        $userIdList = array();

        $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();

        $queryParts = BOL_UserDao::getInstance()->getUserQueryFilter("u", "id", array(
            "method" => "BOL_UserDao::findList"
        ));

        $correspondenceCondition = "";
        $friendsCondition = "";
        if (!empty($userWithCorrespondenceIdList))
        {
            $friendsCondition = "";
            if (!empty($friendIdList))
            {
                $correspondenceCondition = " AND ( `u`.`id` IN ( ".OW::getDbo()->mergeInClause($userWithCorrespondenceIdList)." ) ";
                $friendsCondition = " OR `u`.`id` IN ( ".OW::getDbo()->mergeInClause($friendIdList)." ) )";
            }
            else
            {
                $correspondenceCondition = " AND `u`.`id` IN ( ".OW::getDbo()->mergeInClause($userWithCorrespondenceIdList)." ) ";
            }
        }
        else
        {
            if (!empty($friendIdList))
            {
                $friendsCondition = " AND `u`.`id` IN ( ".OW::getDbo()->mergeInClause($friendIdList)." )";
            }
            else
            {
                return array(
                    'userIdList' => $userIdList,
                    'userWithCorrespondenceIdList' => $userWithCorrespondenceIdList,
                    'friendIdList' => $friendIdList
                );
            }
        }

        $query = "SELECT `u`.`id`
            FROM `".BOL_UserDao::getInstance()->getTableName()."` as `u`
            {$queryParts["join"]}

            WHERE {$queryParts["where"]} ".$correspondenceCondition." ".$friendsCondition;

        $tmpUserIdList = OW::getDbo()->queryForColumnList($query);

        foreach($tmpUserIdList as $id)
        {
            if ($id == $userId) continue;

            if (!in_array($id, $userIdList))
            {
                $userIdList[] = $id;
            }
        }

        return array(
            'userIdList' => $userIdList,
            'userWithCorrespondenceIdList' => $userWithCorrespondenceIdList,
            'friendIdList' => $friendIdList
        );
    }

    private function getUserIdList($userId)
    {
        return $this->getUserIdListAlt($userId);
    }

    public function getUserList($userId, $data = array())
    {
        if (empty($data))
        {
            $data = $this->getUserIdList($userId);
        }
        $list = $this->getUserInfoForUserIdList($data['userIdList'], $data['userWithCorrespondenceIdList'], $data['friendIdList']);

        $onlineCount = 0;
        $result = array();

        foreach($list as $userData)
        {
            $result[] = $userData;
            if ($userData['status'] != 'offline' && empty($userData['wasBlocked']))
            {
                $onlineCount++;
            }
        }

        return array('onlineCount'=>$onlineCount, 'list'=>$result);
    }

    public function getUserOnlineList($userId)
    {
        $data = $this->getUserIdList($userId);

        $list = $this->getUserOnlineInfoForUserIdList($data['userIdList'], $data['userWithCorrespondenceIdList'], $data['friendIdList']);

        $onlineCount = 0;
        $result = array();
        foreach($list as $userData)
        {
            $result[] = $userData;
            if ($userData['status'] != 'offline' && empty($userData['wasBlocked']))
            {
                $onlineCount++;
            }
        }

        return array('onlineCount'=>$onlineCount, 'list'=>$result, 'userIdList'=>$data);
    }

    public function resetUserLastData($userId)
    {
        $userLastData = $this->userLastDataDao->findUserLastDataFor($userId);

        if ($userLastData)
        {
            $userLastData->data = '';

            $this->userLastDataDao->save($userLastData);
        }
    }

    /***
     * @param OW_EVENT $event
     */
    public function onRabbitMQNotificationRelease(OW_EVENT $event) {
        $data = $event->getData();
        if (!isset($data) || !isset($data->body)) {
            return;
        }

        if (isset($params['itemType'])){
            $params = $data->body;
            $params = (array) json_decode($params);
            if($params['itemType'] == 'resetAllUsersLastData') {
                $this->_resetAllUsersLastData();
            }
        }
    }

    public function resetAllUsersLastData()
    {
        $valid = FRMSecurityProvider::sendUsingRabbitMQ([], 'resetAllUsersLastData');

        if (!$valid) {
            $this->_resetAllUsersLastData();
        }
    }

    private function _resetAllUsersLastData()
    {
        $example = new OW_Example();
        $example->andFieldNotEqual('userId', 0);
        $this->userLastDataDao->deleteByExample($example);
    }

    public function getLastDataAlt($params)
    {
        $socketEnabled = FRMSecurityProvider::isSocketEnable(true);
        $result = array();
        $userId = OW::getUser()->getId();

        $userLastData = $this->userLastDataDao->findUserLastDataFor($userId);

        if (empty($userLastData))
        {
            $userLastData = new MAILBOX_BOL_UserLastData();
            $userLastData->userId = $userId;
        }

        if ($userLastData->data == '')
        {
            $userData = array();
            $userService = BOL_UserService::getInstance();

            $userOnlineListData = $this->getUserOnlineList($userId);

            $userListData = $this->getUserList($userId, $userOnlineListData['userIdList']);

            $userData['userOnlineCount'] = $userListData['onlineCount'];
            $userData['userList'] = $userListData['list'];

//            $messageList = $this->findUnreadMessages($userId, $params['unreadMessageList'], $params['lastMessageTimestamp']);
//            if (!empty($messageList))
//            {
//                $conversations = array();
//                $notViewedConversations = 0;
//                foreach($messageList as $message)
//                {
//                    if (!in_array($message['convId'], $conversations))
//                    {
//                        $conversations[] = $message['convId'];
//                        if (!$message['conversationViewed'])
//                        {
//                            $notViewedConversations++;
//                        }
//                    }
//                }
//                $userData['messageList'] = $messageList;
//                $userData['newMessageCount'] = array('all'=>count($conversations), 'new'=>(int)$notViewedConversations);
//            }
//            else
//            {
//                $userData['messageList'] = '';
//                $userData['newMessageCount'] = array('all'=>0, 'new'=>0);
//            }


            $userData['conversationsCount'] = $this->countConversationListByUserId($userId);
            $limit = !empty($params['getAllConversations'])
                ? $userData['conversationsCount']
                : 10;

            $userData['convList'] = $this->getConversationListByUserId(OW::getUser()->getId(), 0, $limit);

            $userLastData->data = json_encode($userData);

            $this->userLastDataDao->save($userLastData);
        }

        $messageList = $this->findUnreadMessages($userId, $params['unreadMessageList'], $params['lastMessageTimestamp']);

        foreach ($messageList as $id => $message) {
            $text = $messageList[$id]['text'];
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $text)));
            if (isset($stringRenderer->getData()['string'])) {
                $messageList[$id]['text'] = ($stringRenderer->getData()['string']);
            }
        }

        if (!empty($messageList))
        {
            $conversations = array();
            $notViewedConversations = 0;
            if (!$socketEnabled) {
                foreach ($messageList as $message) {
                    if (!in_array($message['convId'], $conversations)) {
                        $conversations[] = $message['convId'];
                        if (!$message['conversationViewed']) {
                            $notViewedConversations++;
                        }
                    }
                }
                $result['messageList'] = $messageList;
            }
            $result['newMessageCount'] = array('all'=>count($conversations), 'new'=>(int)$notViewedConversations);
        }
//        else
//        {
//            $result['messageList'] = '';
//            $result['newMessageCount'] = array('all'=>0, 'new'=>0);
//        }

        $data = json_decode($userLastData->data, true);


        if ($params['userOnlineCount'] === 0 || $data['userOnlineCount'] != $params['userOnlineCount'])
        {
            $result['userOnlineCount'] = $data['userOnlineCount'];
            $result['userList'] = $data['userList'];
        }

        if ($data['conversationsCount'] != $params['conversationsCount'])
        {
            $result['conversationsCount'] = $data['conversationsCount'];
            $result['convList'] = $data['convList'];
        }


        if (!$socketEnabled && !empty($data['messageList']))
        {
            foreach($data['messageList'] as $id => $message)
            {
                if (in_array($message['id'], $params['unreadMessageList']))
                {
                    unset($data['messageList'][$id]);
                    continue;
                }
            }
            $result['messageList'] = $data['messageList'];
        }

        //--  remove content from blocked users --//
        $blockedUsers = $this->findBlockedByMeUserIdList();

        if ( !empty($result['userList']) )
        {
            foreach ( $result['userList'] as $index => &$user )
            {
                if ( in_array($user['opponentId'], $blockedUsers) )
                {
                    $user['canInvite'] = true;
                }
            }
        }

        // -- sort userList
        if ( !empty($result['userList']) )
        {
            $userList = $result['userList'];
            $opponentIds = array();
            foreach ($userList as $key => $userItem)
            {
                $opponentIds[] = $userItem['opponentId'];
            }
            $cachedConversation = MAILBOX_BOL_ConversationDao::getInstance()->findChatConversationIdWithUserByOpponentIds($userId, $opponentIds);
            foreach ($userList as $key => $userItem) {
                if (array_key_exists($userItem['opponentId'], $cachedConversation)) {
                    $conversation = $cachedConversation[$userItem['opponentId']];
                    if ($conversation != null) {
                        $userList[$key]['lastMessageTimestamp'] = $conversation->lastMessageTimestamp;
                    }
                } else {
                    $conversationId = MAILBOX_BOL_ConversationDao::getInstance()->findChatConversationIdWithUserById($userId,$userItem['opponentId']);
                    if ((int) $conversationId > 0) {
                        $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findById($conversationId);
                        if(isset($conversation)) {
                            $userList[$key]['lastMessageTimestamp'] = $conversation->lastMessageTimestamp;
                        }
                    }
                }
            }
            usort($userList, function($a, $b) {
                if($b['lastMessageTimestamp'] != $a['lastMessageTimestamp'])
                    return $b['lastMessageTimestamp'] - $a['lastMessageTimestamp'];
                else
                    return $b['displayName'] < $a['displayName'];
            });
            $result['userList'] = $userList;
        }
        // -- sort convList
        if ( !empty($result['convList']) )
        {
            $convList = $result['convList'];
            usort($convList, function($a, $b) {
                if($b['lastMessageTimestamp'] != $a['lastMessageTimestamp'])
                    return $b['lastMessageTimestamp'] - $a['lastMessageTimestamp'];
                else
                    return $b['displayName'] < $a['displayName'];
            });
            $result['convList'] = $convList;
        }
        // --

        return $result;
    }

    public function getActiveModeList()
    {
        $event = new OW_Event('plugin.mailbox.get_active_modes');
        OW::getEventManager()->trigger($event);

        $activeModes = $event->getData();

        if (empty($activeModes))
        {
            $activeModes = json_decode( OW::getConfig()->getValue('mailbox', 'active_modes') );
        }

        return $activeModes;
    }

    public function getLastMessageTimestamp( $conversationId )
    {
        $message = $this->messageDao->findLastMessage($conversationId);

        return (!empty($message)) ? (int)$message->timeStamp : 0;
    }

    /***
     * @param $userId
     * @param $userIdList
     * @return array
     */
    public function findLastOpponentReadMessageByConversationIdListAndUserIdList($userId, $userIdList){
        return $this->messageDao->findLastOpponentReadMessageByConversationIdListAndUserIdList( $userId, $userIdList );
    }

    public function getLastMessageTimestampByUserIdList( $userIdList )
    {
        $result = array();
        $userId = OW::getUser()->getId();

        $messageList = $this->messageDao->findLastMessageByConversationIdListAndUserIdList( $userId, $userIdList );

        foreach($messageList as $message)
        {
            if ($message['recipientId'] == $userId)
            {
                $opponentId = $message['senderId'];
            }

            if ($message['senderId'] == $userId)
            {
                $opponentId = $message['recipientId'];
            }

            if (isset($result[$opponentId]))
            {
                if ( $result[$opponentId] < (int)$message['timeStamp'] )
                {
                    $result[$opponentId] = (int)$message['timeStamp'];
                }
            }
            else
            {
                $result[$opponentId] = (int)$message['timeStamp'];
            }
        }

        return $result;
    }

    public function getUserSettingsForm()
    {
        $form = new Form('im_user_settings_form');

        $findContact = new MAILBOX_CLASS_SearchField('im_find_contact');
        $findContact->setHasInvitation(true);
        $findContact->setInvitation(OW::getLanguage()->text('mailbox', 'find_contact'));
        $form->addElement($findContact);

        $userIdHidden = new HiddenField('user_id');
        $form->addElement($userIdHidden);


        return $form;
    }

    public function checkPermissions()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return "You need to sign in";
        }

        if ( !OW::getRequest()->isAjax() )
        {
            return "Ajax request required";
        }

        return false;
    }

    public function getUserInfo( $opponentId, $userWithCorrespondenceIdList = null, $friendIdList = null )
    {
        $userId = OW::getUser()->getId();


        $profileUrl = BOL_UserService::getInstance()->getUserUrl($opponentId);
        $avatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl($opponentId);

        $isFriend = false;
        if ($friendIdList === null)
        {
            $friendIdList = array();
            $friendship = OW::getEventManager()->call('plugin.friends.check_friendship', array('userId' => $userId, 'friendId' => $opponentId));
            if ( !empty($friendship) && $friendship->getStatus() == 'active' )
            {
                $friendIdList[] = $opponentId;
            }
        }

        if (in_array($opponentId, $friendIdList))
        {
            $isFriend = true;
        }

        $wasCorrespondence = false;
        if ($userWithCorrespondenceIdList === null)
        {
            $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
            $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
        }
        else
        {
            $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
        }

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName($opponentId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName($opponentId) : $profileDisplayname;
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($opponentId));
        $shortUserDataByUserIdList = $this->getFields(array($opponentId));

        $info = array(
            'opponentId' => (int)$opponentId,
            'displayName' => $profileDisplayname,
            'avatarUrl' => $avatarUrl,
            'avatarLabel' => !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null,
            'profileUrl' => $profileUrl,
            'isFriend' => $isFriend,
            'status' => $conversationService->getUserStatus($opponentId),
            'lastMessageTimestamp' => $this->getLastMessageTimestamp($conversationId),
            'convId' => $conversationId, //here it is a chat conversation id
            'wasCorrespondence' => $wasCorrespondence,
            'displayText' => $this->getConversationPreviewTextById($conversationId),
            'shortUserData' => !empty($shortUserDataByUserIdList[$opponentId]) ? $shortUserDataByUserIdList[$opponentId] : $profileDisplayname
        );

        $activeModes = $this->getActiveModeList();
        if (in_array('chat', $activeModes))
        {
            $url = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            $info['url'] = $url;
            $info['canInvite'] = $this->getInviteToChatPrivacySettings($userId, $opponentId);

            if ( !$info['canInvite'] )
            {
                $info['wasBlocked'] = true;
            }
        }
        else
        {
            $url = OW::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$opponentId));
            $info['url'] = $url;
        }

        if ( BOL_UserService::getInstance()->isBlocked($opponentId) )
        {
            $info['wasBlocked'] = true;
        }

        return $info;
    }

    private function isBlockedByUserIdList($userId, $userIdList)
    {
        $userIdListString = OW::getDbo()->mergeInClause($userIdList);
        $sql = "SELECT `userId` FROM `".BOL_UserBlockDao::getInstance()->getTableName()."` WHERE `blockedUserId` = :userId AND `userId` IN ( {$userIdListString} )";

        return OW::getDbo()->queryForList($sql, array('userId'=>$userId));
    }


    private function findBlockedByMeUserIdList()
    {
        $sql = "SELECT `blockedUserId` FROM `".BOL_UserBlockDao::getInstance()->getTableName()."` WHERE `userId` = :userId";

        return OW::getDbo()->queryForColumnList($sql, array('userId'=>OW::getUser()->getId()));
    }

    public function getFields( $userIdList )
    {
        $fields = array();

        foreach($userIdList as $userId)
        {
            $fields[$userId] = '';
        }

        $qs = array();

        $qs[] = 'username';

        $questionName = OW::getConfig()->getValue('base', 'display_name_question');
        $qs[] = $questionName;
/* We nedd to show just username or realname of users
        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
        {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
        {
            $qs[] = 'sex';
        }

        $qLocation = BOL_QuestionService::getInstance()->findQuestionByName('googlemap_location');
        if ($qLocation)
        {
            if ( $qLocation->onView )
            {
                $qs[] = 'googlemap_location';
            }
        }
*/
        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach($questionList as $userId => $question)
        {
            $userFields = array();

            $fields[$userId] = isset($question[$questionName]) ? "<b>".$question[$questionName]."</b>" : "<b>".$question['username']."</b>";

            $sexValue = '';
            if ( !empty($question['sex']) )
            {
                $sex = $question['sex'];

                for ( $i = 0; $i < 64; $i++ )
                {
                    $val = $i+1;
                    if ( (int) $sex == $val )
                    {
                        $sexValue .= BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $val) . ', ';
                    }
                }

                if ( !empty($sexValue) )
                {
                    $userFields['sex'] = substr($sexValue, 0, -2);
                    $fields[$userId] .= "<br/>".$userFields['sex'];
                }
            }

            if ( !empty($question['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($question['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $userFields['age'] = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                $fields[$userId] .= "<br/>".$userFields['age'];
            }


            if (!empty($question['googlemap_location']))
            {
                $userFields['googlemap_location'] = !empty($question['googlemap_location']['address'])
                    ? $question['googlemap_location']['address']
                    : '';
                $fields[$userId] .= "<br/>".$userFields['googlemap_location'];
            }
        }

        return $fields;
    }

    public function getUserInfoForUserIdList( $userIdList, $userWithCorrespondenceIdList = array(), $friendIdList = array() )
    {
        if (empty($userIdList))
        {
            return array();
        }
        $activeModes = $this->getActiveModeList();

        $userInfoList = array();
        $userId = OW::getUser()->getId();


        $blockedByUserIdList = $this->isBlockedByUserIdList($userId, $userIdList);
        $onlineStatusByUserIdList = $this->getUserStatusForUserIdList($userIdList);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList);
        $conversationIdByUserIdList = $this->getChatConversationIdWithUserByIdList($userId, $userIdList);
        $friendIdList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        $shortUserDataByUserIdList = $this->getFields($userIdList);

        if (empty($friendIdList))
        {
            $friendIdList = array();
        }

        $lastMessageTimestampByUserIdList = $this->getLastMessageTimestampByUserIdList($userIdList);

        if (in_array('chat', $activeModes))
        {
            $canInviteByUserIdList = $this->getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList);
        }
        else
        {
            $canInviteByUserIdList = array();
        }

        $params = array();
        $conversationIds = array();
        foreach ($userIdList as $opponentId) {
            if (array_key_exists($opponentId, $conversationIdByUserIdList)) {
                $conversationIds[] = $conversationIdByUserIdList[$opponentId];
            }
        }
        $params['cache']['conversations_items'] = $this->conversationDao->getConversationsItem($conversationIds);

        foreach ($userIdList as $opponentId)
        {
            $wasCorrespondence = false;
            if ($userWithCorrespondenceIdList === null)
            {
                $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }
            else
            {
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }

            $conversationId = array_key_exists($opponentId, $conversationIdByUserIdList) ? $conversationIdByUserIdList[$opponentId] : 0;
            $displayText = $this->getConversationPreviewTextById($conversationId, $params);

            $info = array(
                'opponentId' => (int)$opponentId,
                'displayName' => empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'],
                'userName'=>$userNameByUserIdList[$opponentId],
                'avatarUrl' => $avatarData[$opponentId]['src'],
                'avatarLabel' => !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null,
                'profileUrl' => $avatarData[$opponentId]['url'],
                'isFriend' => in_array($opponentId, $friendIdList),
                'status' => $onlineStatusByUserIdList[$opponentId],
                'lastMessageTimestamp' => array_key_exists($opponentId, $lastMessageTimestampByUserIdList) ? $lastMessageTimestampByUserIdList[$opponentId] : 0,
                'convId' => (int)$conversationId, //here it is a chat conversation id
                'wasCorrespondence' => $wasCorrespondence,
                'displayText' => $displayText,
                'shortUserData' => $shortUserDataByUserIdList[$opponentId]
            );

            if (in_array('chat', $activeModes))
            {
                $url = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
                $info['url'] = $url;
                $info['canInvite'] = $canInviteByUserIdList[$opponentId];

                if ( !$info['canInvite'] )
                {
                    $info['wasBlocked'] = true;
                }
            }
            else
            {
                $url = OW::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$opponentId));
                $info['url'] = $url;
            }

            $userInfoList[$opponentId] = $info;

            $userInfoList[$opponentId]['wasBlocked'] = in_array($opponentId, $blockedByUserIdList) ? true : false;
        }

        return $userInfoList;
    }

    public function getUserInfoForUserIdListForApi( $userIdList, $userWithCorrespondenceIdList = array(), $friendIdList = array() )
    {
        if (empty($userIdList))
        {
            return array();
        }
        $activeModes = $this->getActiveModeList();

        $userInfoList = array();
        $userId = OW::getUser()->getId();

        $blockedByUserIdList = $this->isBlockedByUserIdList($userId, $userIdList);
        $onlineStatusByUserIdList = $this->getUserStatusForUserIdList($userIdList);
        $userNameByUserIdList = BOL_UserService::getInstance()->getUserNamesForList($userIdList);
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIdList, true, false, true, true);
        $conversationIdByUserIdList = $this->getChatConversationIdWithUserByIdList($userId, $userIdList);
        $friendIdList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId'=>$userId));
        $shortUserDataByUserIdList = $this->getFields($userIdList);

        if (empty($friendIdList))
        {
            $friendIdList = array();
        }

        $lastMessageTimestampByUserIdList = $this->getLastMessageTimestampByUserIdList($userIdList);

        if (in_array('chat', $activeModes))
        {
            $canInviteByUserIdList = $this->getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList);
        }
        else
        {
            $canInviteByUserIdList = array();
        }

        $params = array();
        $conversationIds = array();
        foreach ($userIdList as $opponentId) {
            if (array_key_exists($opponentId, $conversationIdByUserIdList)) {
                $conversationIds[] = $conversationIdByUserIdList[$opponentId];
            }
        }
        $params['cache']['conversations_items'] = $this->conversationDao->getConversationsItem($conversationIds);

        foreach ($userIdList as $opponentId)
        {
            $wasCorrespondence = false;
            if ($userWithCorrespondenceIdList === null)
            {
                $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }
            else
            {
                $wasCorrespondence = in_array($opponentId, $userWithCorrespondenceIdList);
            }

            $conversationId = array_key_exists($opponentId, $conversationIdByUserIdList) ? $conversationIdByUserIdList[$opponentId] : 0;

            $info = array(
                'opponentId' => (int)$opponentId,
                'displayName' => empty($avatarData[$opponentId]['title']) ? $userNameByUserIdList[$opponentId] : $avatarData[$opponentId]['title'],
                'avatarUrl' => $avatarData[$opponentId]['src'],
                'avatarLabel' => !empty($avatarData[$opponentId]) ? mb_substr($avatarData[$opponentId]['label'], 0, 1) : null,
                'profileUrl' => '',
                'isFriend' => in_array($opponentId, $friendIdList),
                'status' => $onlineStatusByUserIdList[$opponentId],
                'lastMessageTimestamp' => array_key_exists($opponentId, $lastMessageTimestampByUserIdList) ? $lastMessageTimestampByUserIdList[$opponentId] : 0,
                'convId' => (int)$conversationId, //here it is a chat conversation id
                'wasCorrespondence' => $wasCorrespondence,
                'displayText' => $this->getConversationPreviewTextById($conversationId, $params),
                'shortUserData' => $shortUserDataByUserIdList[$opponentId]
            );

            if (in_array('chat', $activeModes))
            {
//                $url = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
//                $info['url'] = $url;
                $info['canInvite'] = $canInviteByUserIdList[$opponentId];

                if ( !$info['canInvite'] )
                {
                    $info['wasBlocked'] = true;
                }
            }
//            else
//            {
//                $url = OW::getRouter()->urlForRoute('mailbox_compose_mail_conversation', array('opponentId'=>$opponentId));
//                $info['url'] = $url;
//            }

            $userInfoList[$opponentId] = $info;

            $userInfoList[$opponentId]['wasBlocked'] = in_array($opponentId, $blockedByUserIdList) ? true : false;
        }

        return $userInfoList;
    }

    public function getUserOnlineInfoForUserIdList( $userIdList, $userWithCorrespondenceIdList = null, $friendIdList = null )
    {
        if (empty($userIdList))
        {
            return array();
        }

        $activeModes = $this->getActiveModeList();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $userInfoList = array();
        $userId = OW::getUser()->getId();

        $blockedByUserIdList = $this->isBlockedByUserIdList($userId, $userIdList);
        $onlineStatusByUserIdList = $this->getUserStatusForUserIdList($userIdList);
        if (in_array('chat', $activeModes))
        {
            $canInviteByUserIdList = $this->getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList);
        }
        else
        {
            $canInviteByUserIdList = array();
        }

        foreach ($userIdList as $opponentId)
        {
            $info = array(
                'status' => $onlineStatusByUserIdList[$opponentId],
            );

            if (in_array('chat', $activeModes))
            {
                $info['canInvite'] = $canInviteByUserIdList[$opponentId];

                if ( !$info['canInvite'] )
                {
                    $info['wasBlocked'] = true;
                }
            }

            $userInfoList[$opponentId] = $info;

            $userInfoList[$opponentId]['wasBlocked'] = in_array($opponentId, $blockedByUserIdList) ? true : false;
        }

        return $userInfoList;
    }

    public function getInviteToChatPrivacySettings($userId, $opponentId)
    {
        $eventParams = array(
            'action' => 'mailbox_invite_to_chat',
            'ownerId' => $opponentId,
            'viewerId' => $userId
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            return false;
        }

        return true;
    }

    public function getViewPresenceOnSitePrivacySettings($userId, $opponentId)
    {
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerId' => $opponentId,
            'viewerId' => $userId
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {

            return false;
        }

        return true;
    }

    private function getPrivacySettingsForUserIdList( $actionName, $userId, $userIdList)
    {
        $eventParams = array(
            'action' => $actionName,
            'ownerIdList' => $userIdList,
            'viewerId' => $userId
        );

        $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);

        $result = array();

        foreach($userIdList as $opponentId)
        {
            if ( isset($permissions[$opponentId]['blocked']) && $permissions[$opponentId]['blocked'] == true )
            {
                $result[$opponentId] = false;
            }
            else
            {
                $result[$opponentId] = true;
            }
        }

        return $result;
    }

    public function getInviteToChatPrivacySettingsForUserIdList($userId, $userIdList)
    {
        return $this->getPrivacySettingsForUserIdList('mailbox_invite_to_chat', $userId, $userIdList);
    }

    public function getViewPresenceOnSitePrivacySettingsForUserIdList($userId, $userIdList)
    {
        return $this->getPrivacySettingsForUserIdList('base_view_my_presence_on_site', $userId, $userIdList);
    }

    public function getUserListWithCorrespondence()
    {
        $userId = OW::getUser()->getId();

        $userIdList = $this->messageDao->findUserListWithCorrespondence($userId);

        return $userIdList;
    }

    public function getUserListWithCorrespondenceAlt($friendIdList)
    {
        $userId = OW::getUser()->getId();

        $userIdList = $this->messageDao->findUserListWithCorrespondenceAlt($userId, $friendIdList);

        return $userIdList;
    }

    public function countConversationListByUserId($userId)
    {
        $activeModes = $this->getActiveModeList();

        return (int)$this->conversationDao->countConversationListByUserId($userId, $activeModes);
    }

    public function getConversationListByUserId($userId, $from = 0, $count = 50, $convId = null, $search = ""){
        $data = array();

        $activeModes = $this->getActiveModeList();
        $conversationItemList = $this->conversationDao->findConversationItemListByUserId($userId, $activeModes, $from, $count, $convId, $search);

        foreach($conversationItemList as $i => $conversation)
        {
                $conversationItemList[$i]['timeStamp'] = (int)$conversation['initiatorMessageTimestamp'];
                $conversationItemList[$i]['lastMessageSenderId'] = $conversation['initiatorMessageSenderId'];
                $conversationItemList[$i]['isSystem'] = $conversation['initiatorMessageIsSystem'];
                $conversationItemList[$i]['text'] = $conversation['initiatorText'];

                $conversationItemList[$i]['lastMessageId'] = $conversation['initiatorLastMessageId'];
                $conversationItemList[$i]['recipientRead'] = $conversation['initiatorRecipientRead'];
                $conversationItemList[$i]['lastMessageRecipientId'] = $conversation['initiatorMessageRecipientId'];
                $conversationItemList[$i]['lastMessageWasAuthorized'] = 1;
        }

        $data = $this->getConversationItemByConversationIdListForApi( $conversationItemList );

        return $data;
    }

//    public function sortConversationList($a, $b)
//    {
//        return $a['timeStamp'] < $b['timeStamp'] ? 1 : -1;
//    }

    public function getConversationDeletedTimestamp($conversationId, $additionalParams = array())
    {
        $deletedTimestamp = 0;
        $conversation = null;
        if (isset($additionalParams['cache']['conversations'][$conversationId])) {
            $conversation = $additionalParams['cache']['conversations'][$conversationId];
        }
        if ($conversation == null) {
            $conversation = $this->getConversation($conversationId);
        }
        if ($conversation->initiatorId == OW::getUser()->getId())
        {
            $deletedTimestamp = $conversation->initiatorDeletedTimestamp;
        }
        else
        {
            $deletedTimestamp = $conversation->interlocutorDeletedTimestamp;
        }

        return $deletedTimestamp;
    }

    public function getNewConsoleConversationCount( $userId, $messageList )
    {
        $convList = array();
        foreach ($messageList as $messageData)
        {
            if (!in_array($messageData['convId'], $convList))
            {
                $convList[] = $messageData['convId'];
            }
        }
        return $this->conversationDao->getNewConversationCountForConsole($userId, $convList);
    }

    public function getViewedConversationCountForConsole( $userId, $messageList )
    {
        $convList = array();
        foreach ($messageList as $messageData)
        {
            if (!in_array($messageData['convId'], $convList))
            {
                $convList[] = $messageData['convId'];
            }
        }

        return $this->conversationDao->getViewedConversationCountForConsole($userId, $convList);
    }

    public function getConsoleConversationList( $userId, $first, $count, $lastPingTime, $ignoreList = array() )
    {
        if ( empty($userId) || !isset($first) || !isset($count) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        $activeModes = $this->getActiveModeList();

        return $this->conversationDao->getConsoleConversationList($activeModes, $userId, $first, $count, $lastPingTime, $ignoreList);
    }

    public function getMarkedUnreadConversationList( $userId, $ignoreList = array() )
    {
        $list = $this->conversationDao->getMarkedUnreadConversationList( $userId, $ignoreList, $this->getActiveModeList() );

        $convIds = array();
        foreach($list as $id => $value) {
            $convIds[] = (int)$value;
        }
        $cachedConversationCount = $this->countUnreadMessagesForConversationByIds($convIds, $userId);

        foreach($list as $id => $value)
        {
            $list[$id] = (int)$value;
            $unreadMessagesCount = 0;
            if (isset($cachedConversationCount[$value])) {
                $unreadMessagesCount = $cachedConversationCount[$value];
            } else {
                $unreadMessagesCount = $this->countUnreadMessagesForConversation($value, $userId);
            }
            if($unreadMessagesCount==0) {
                unset($list[$id]);
            }
        }

        return $list;
    }

    public function getMarkedUnreadConversationCount( $userId, $ignoreList = array() )
    {
        $conversationIds = $this->conversationDao->getMarkedUnreadConversationList( $userId, $ignoreList, $this->getActiveModeList() );

        $unreadConversations= array();
        if (sizeof($conversationIds) > 0) {
            $unreadConversations = $this->countUnreadMessagesForConversationList($conversationIds, $userId);
        }
        foreach($unreadConversations as $id => $value)
        {
            if($value == 0) {
                unset($unreadConversations[$id]);
            }
        }

        return sizeof($unreadConversations);
    }


    public function getInboxConversationList( $userId, $first, $count )
    {
        if ( empty($userId) || !isset($first) || !isset($count) )
        {
            $errorMessage = OW::getLanguage()->text('mailbox', 'empty_string_params_error');
            throw new InvalidArgumentException($errorMessage);
        }

        return $this->conversationDao->getInboxConversationList($userId, $first, $count);
    }

    public function countUnreadMessagesForConversation($convId, $userId)
    {
        return (int)$this->messageDao->countUnreadMessagesForConversation($convId, $userId);
    }

    public function countUnreadMessagesForConversationByIds($convIds, $userId)
    {
        return $this->messageDao->countUnreadMessagesForConversationByIds($convIds, $userId);
    }

    public function countUnreadMessagesForConversationList($conversationIdList, $userId)
    {
        if (count($conversationIdList) == 0)
        {
            return array();
        }

        $list = $this->messageDao->countUnreadMessagesForConversationList($conversationIdList, $userId);

        $result = array();
        foreach($list as $item)
        {
            $result[$item['conversationId']] = $item['count'];
        }

        return $result;
    }

    public function checkUser($userId, $conversationOpponentId)
    {
        $language = OW::getLanguage();
        $user = BOL_UserService::getInstance()->findUserById($conversationOpponentId);
        $result = array();

        if (empty($user))
        {
            $result['isSuspended'] = true;
            $result['suspendReasonMessage'] = $language->text('mailbox', 'user_is_deleted');//TODO add lang
        }
        else
        {
            $suspendReason = '';
            $isDeleted = false;

            $isSuspended = BOL_UserService::getInstance()->isSuspended($conversationOpponentId);
            if ($isSuspended)
            {
                $suspendReasonMessage = $language->text('mailbox', 'user_is_suspended');
            }

            $isApproved = true;
            if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') )
            {
                $isApproved = BOL_UserService::getInstance()->isApproved($conversationOpponentId);
            }
            if (!$isApproved)
            {
                $suspendReasonMessage = $language->text('mailbox', 'user_is_not_approved');
            }

            $emailVerified = true;
            if ( OW::getConfig()->getValue('base', 'confirm_email') )
            {
                $emailVerified = $user->emailVerify;
            }
            if (!$emailVerified)
            {
                $suspendReasonMessage = $language->text('mailbox', 'user_is_not_verified');
            }

            $isAuthorizedReadMessage = true;

            $isBlocked = BOL_UserService::getInstance()->isBlocked($userId, $conversationOpponentId);
            if ($isBlocked)
            {
                $suspendReasonMessage = $language->text('base', 'user_cant_chat_with_this_user');
                $suspendReason = 'isBlocked';
            }

            $userIdBlockedOpponentId = BOL_UserService::getInstance()->isBlocked($conversationOpponentId, $userId);
            if ($userIdBlockedOpponentId) {
                $suspendReasonMessage = $language->text('mailbox', 'you_blocked_opponnet_in_chat');
                $suspendReason = 'isBlocked';
            }

            $result['isSuspended'] = $isSuspended || !$isApproved || !$emailVerified || !$isAuthorizedReadMessage || $isBlocked || $userIdBlockedOpponentId;

            if ($result['isSuspended'])
            {
                $result['suspendReasonMessage'] = $suspendReasonMessage;
                $result['suspendReason'] = $suspendReason;
            }
        }

        return $result;
    }

    public function getConversationLength($conversationId, $additionalParams = array())
    {
        $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId, $additionalParams);

        return (int)$this->messageDao->getConversationLength($conversationId, $deletedTimestamp);
    }

    /**
     * Application event methods
     */
    public function getUnreadMessageCount( $userId, $ignoreList = array(), $time = null, $activeModes = array() )
    {
        $ignoreList = empty($ignoreList) ? array() : (array)$ignoreList;
        $time = $time == null ? time() : (int)$time;
        $activeModes = empty($activeModes) ? $this->getActiveModeList() : $activeModes;
        $messageList = $this->messageDao->findUnreadMessages($userId, $ignoreList, $time, $activeModes);

        return count($messageList);
    }

    public function getConversationRead(MAILBOX_BOL_Conversation $conversation, $userId)
    {
        $conversationRead = 0;
        switch ( $userId )
        {
            case $conversation->initiatorId:
                if ( (int) $conversation->read & MAILBOX_BOL_ConversationDao::READ_INITIATOR )
                {
                    $conversationRead = 1;
                }

                break;

            case $conversation->interlocutorId:

                if ( (int) $conversation->read & MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR )
                {
                    $conversationRead = 1;
                }

                break;
        }

        return $conversationRead;
    }

    public function getShortUserInfo( $opponentId )
    {
        $conversationId = $this->getChatConversationIdWithUserById(OW::getUser()->getId(), $opponentId);
        if (!empty($conversationId))
        {
            $conversation = $this->getConversation($conversationId);

            $conversationRead = $this->getConversationRead($conversation, OW::getUser()->getId());
            $lastMessageTimestamp = $this->getLastMessageTimestamp($conversationId);
        }
        else
        {
            $conversationRead = 1;
            $lastMessageTimestamp = 0;
        }

        return array(
            'userId'=>$opponentId,
            'conversationRead'=>$conversationRead,
            'timeStamp'=>$lastMessageTimestamp
        );
    }

    public function getChatUserList( $userId, $from = 0, $count = 10 )
    {
        $conversationList = $this->getConversationListByUserId(OW::getUser()->getId(), $from, $count);

        return $conversationList;

//        $data = array();
//        $list = array();
//
//        $userWithCorrespondenceIdList = $this->getUserListWithCorrespondence();
//
//
//        if (empty($userWithCorrespondenceIdList))
//        {
//            return array();
//        }
//        foreach($userWithCorrespondenceIdList as $id)
//        {
//            $data[$id] = $this->getShortUserInfo($id);
//        }
//
//        $idList = array();
//        $viewedMap = array();
//        $timeMap = array();
//        $timeStamps = array();
//        foreach ( $data as $item )
//        {
//            $idList[] = $item["userId"];
//            $viewedMap[$item["userId"]] = $item["conversationRead"];
//            $timeMap[$item["userId"]] = $item["timeStamp"] > 0 ? UTIL_DateTime::formatDate($item["timeStamp"]) : "";
//            $timeStamps[$item["userId"]] = $item["timeStamp"] > 0 ? $item["timeStamp"] : 0;
//        }
//
//        $userService = BOL_UserService::getInstance();
//        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, false);
//        $onlineMap = BOL_UserService::getInstance()->findOnlineStatusForUserList($idList);
//
//        foreach ( $avatarList as $opponentId => $user )
//        {
//
//            $list[] = array(
//                "userId" => $opponentId,
//                "displayName" => !empty($user["title"]) ? $user["title"] : $userService->getUserName($opponentId),
//                "avatarUrl" => $user["src"],
//                "viewed" => $viewedMap[$opponentId],
//                "online" => $onlineMap[$opponentId],
//                "time" => $timeMap[$opponentId],
//                "lastMessageTimestamp" => $timeStamps[$opponentId],
//            );
//        }
//
//        return $list;
    }

    public function getChatNewMessages($userId, $opponentId, $lastMessageTimestamp)
    {
        $conversationId = $this->getChatConversationIdWithUserById($userId, $opponentId);

        if (!empty($conversationId))
        {
            $dtoList = $this->messageDao->findConversationMessagesByLastMessageTimestamp($conversationId, $lastMessageTimestamp);
            $list = array();
            foreach($dtoList as $dto)
            {
                $list[] = $this->getMessageDataForApi($dto);
            }
        }
        else
        {
            $list = array();
        }

        return $list;
    }


    public function getNewMessagesForConversation( $conversationId, $lastMessageTimestamp = null )
    {
        if ( ($conversation = $this->getConversation($conversationId)) === null )
        {
            return array();
        }

        if ( empty($lastMessageTimestamp) )
        {
            $lastMessageTimestamp = time();
        }

        $result = array();
        $messageList = $this->messageDao->findConversationMessagesByLastMessageTimestamp($conversation->id, $lastMessageTimestamp);

        foreach ( $messageList as $message )
        {
            $result[] = $this->getMessageDataForApi($message);
        }

        return $result;
    }

    /**
     * @param MAILBOX_BOL_Message $message
     * @return array
     */
    public function getMessageDataForApi( $message )
    {
        $item = array();
        $item['convId'] = (int)$message->conversationId;
        $item['mode'] = $this->getConversationMode((int)$message->conversationId);
        $item['id'] = (int)$message->id;
        $item['date'] = date('Y-m-d', (int)$message->timeStamp);
        $item['replyId'] = $message->replyId;
        $text = '';
        $item['changed'] = $message->changed;
        $senderName = BOL_UserService::getInstance()->getDisplayName($message->senderId);
        $recipientName = BOL_UserService::getInstance()->getDisplayName($message->recipientId);
        $item['sender_name'] = $senderName;
        $item['recipient_name'] = $recipientName;
        $item['replyMessage'] = null;
        if (isset($message->replyId)) {
            $replyMessage = MAILBOX_BOL_MessageDao::getInstance()->findById($message->replyId);
            if (isset($replyMessage)) {
                $senderName = BOL_UserService::getInstance()->getDisplayName($replyMessage->senderId);
                $item['reply_sender'] = $senderName;
                $text = $this->json_decode_text($replyMessage->text);
                $item['replyMessage'] = $text;
            } else{
                $item['reply_sender'] = OW::getLanguage()->text('mailbox', 'deleted_message');
                $item['replyId'] = -1;
                $item['replyMessage'] = OW::getLanguage()->text('mailbox', 'deleted_message');
            }
        }
        $conversationItem = $this->getConversationItem($item['mode'], $message->conversationId);
        $item['opponentId'] = $conversationItem['opponentId'];
        $senderName = BOL_UserService::getInstance()->getDisplayName($message->senderId);
        $item['senderName'] = $senderName;
        $senderUrl = BOL_UserService::getInstance()->getUserUrl($message->senderId);
        $item['senderUrl'] = $senderUrl;
        $senderAvatar = BOL_AvatarService::getInstance()->getAvatarUrl($message->senderId);
        $item['senderAvatar'] = $senderAvatar;
        $item['dateLabel'] = UTIL_DateTime::formatDate((int)$message->timeStamp, true);
        $item['timeStamp'] = (int)$message->timeStamp;

        $militaryTime = (bool) OW::getConfig()->getValue('base', 'military_time');
        $item['timeLabel'] = $militaryTime ? strftime("%H:%M", (int)$message->timeStamp) : strftime("%I:%M%p", (int)$message->timeStamp);
        $item['recipientId'] = (int)$message->recipientId;
        $item['senderId'] = (int)$message->senderId;

        $profileDisplayname = BOL_UserService::getInstance()->getDisplayName((int)$message->senderId);
        $profileDisplayname = empty($profileDisplayname) ? BOL_UserService::getInstance()->getUserName((int)$message->senderId) : $profileDisplayname;
        $item['displayName'] = $profileDisplayname;


        $profileAvatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl((int)$message->senderId);
        $item['senderAvatarUrl'] = $profileAvatarUrl;

        $profileAvatarUrl = BOL_AvatarService::getInstance()->getAvatarUrl((int)$message->recipientId);
        $item['recipientAvatarUrl'] = $profileAvatarUrl;

        $item['isAuthor'] = (bool)((int)$message->senderId == OW::getUser()->getId());
        $item['recipientRead'] = (int)$message->recipientRead;
        $item['isSystem'] = (int)$message->isSystem;
        $item['isForwarded'] = $message->isForwarded == 1 || $message->isForwarded == true;
        $item['attachments'] = array();

        $conversation = $this->getConversation($message->conversationId);
        if ( (int)$conversation->initiatorId == OW::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR);
        }

        if ( (int)$conversation->interlocutorId == OW::getUser()->getId() )
        {
            $item['conversationViewed'] = (bool)((int)$conversation->viewed & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR);
        }

        $item['readMessageAuthorized'] = true;

        if ($message->isSystem)
        {
            $eventParams = json_decode($message->text, true);
            $eventParams['params']['messageId'] = (int)$message->id;

            $mobileSupportEvent= OW::getEventManager()->trigger(new OW_Event('check.url.webservice',array()));
            if(isset($mobileSupportEvent->getData()['isWebService']) && $mobileSupportEvent->getData()['isWebService'])
            {
                $eventParams['params']['getMessage'] = true;
            }

            $event = new OW_Event($eventParams['entityType'].'.'.$eventParams['eventName'], $eventParams['params']);
            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            if (!empty($data))
            {
                $text = $data;
            }
            else
            {
                $text = array(
                    'eventName' => $eventParams['eventName'],
                    'text' => OW::getLanguage()->text('mailbox', 'can_not_display_entitytype_message', array('entityType'=>$eventParams['entityType']))
                );
            }
        }
        else
        {
            $text = $message->text;
        }

        $attachments = $this->attachmentDao->findAttachmentsByMessageId($message->id);
        if (!empty($attachments))
        {
            /** @var MAILBOX_BOL_Attachment $attachment */
            foreach($attachments as $attachment)
            {
                $ext = UTIL_File::getExtension($attachment->fileName);
                $attachmentPath = $this->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);
                $thumbnailPath = !empty($attachment->thumbName) ? MAILBOX_BOL_ConversationService::getInstance()->getAttachmentDir() . $attachment->thumbName : '';
                $fileUrl = OW::getStorage()->getFileUrl($attachmentPath);
                $filePathUrl = OW::getStorage()->getFileUrl($attachmentPath, true);
                $thumbnailUrl = OW::getStorage()->getFileUrl($thumbnailPath, true);
                $isImage = false;

                if($this->isImageUrl($filePathUrl)){
                    $isImage = true;
                }

                $attItem = array();
                $attItem['id'] = (int) $attachment->id;
                $attItem['messageId'] = (int) $attachment->messageId;
                $attItem['downloadUrl'] = $fileUrl;
                $attItem['fileName'] = $attachment->fileName;
                $attItem['thumbnail'] = $thumbnailUrl;
                $attItem['fileSize'] = $attachment->fileSize;
                $attItem['type'] = $this->getAttachmentType($attachment);

                $item['attachments'][] = $attItem;

                if($isImage){
                    $item['image'] = $fileUrl;
                }else if($text == '' && !empty($attachment->fileName)){
                   $text = $attachment->fileName;
                }
            }
        }

        /***
         * Data for api chat
         */

        $item['createdAt'] = (int)$message->timeStamp;
        $item['_id'] = (int)$message->id;
        $senderDisplayName = BOL_UserService::getInstance()->getDisplayName((int)$message->senderId);
        $item['user'] = array(
            "id" => (int)$message->senderId,
            "_id" => (int)$message->senderId,
            "name" => $senderDisplayName,
            "avatar" => $item['senderAvatarUrl'],
        );
        $item['sent'] = true;
        $item['received'] = (int)$message->recipientRead;

        /***
         *
         */

        $item['text'] = $text;

        return $item;
    }

    public function getRawMessageInfo($message, $userId = null, $stripString = true){
        if ($userId == null && OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
        }
        $messageInfo = $this->getMessageDataForApi($message);
        $text = $messageInfo['text'];
        $text = $this->json_decode_text($text);
        $text = $this->removePrefetchFromText($text);
        $editable = false;
        if(OW::getUser()->isAuthenticated()) {
            $editable = $messageInfo['senderId'] == $userId;
        }
        $messageInfo['editable'] = $editable;
        $messageInfo['removable'] = $editable;
        $messageInfo['text'] = $text;
        if ($stripString) {
            $stripedString = OW::getEventManager()->trigger(new OW_Event('base.strip_raw_string', array('string' => $messageInfo['text'],'changeBrToNewLine'=>true)));
            if (isset($stripedString->getData()['string'])) {
                $messageInfo['text'] = $stripedString->getData()['string'];
            }
        }
        $messageInfo['text'] = trim($messageInfo['text']);
        $messageInfo['text'] = trim($messageInfo['text'], '"');
        if ($messageInfo['received'] == 1) {
            $messageInfo['received'] = true;
        } else {
            $messageInfo['received'] = false;
        }
        $messageInfo['changed'] = (int) $messageInfo['changed'];
        $messageInfo['opponentId'] = (int) $messageInfo['opponentId'];
        $messageInfo['opponentId'] = (int) $messageInfo['opponentId'];

        if(isset($_POST['_id'])){
            $messageInfo['_id'] = $_POST['_id'];
        }
        return $messageInfo;
    }

    public function removePrefetchFromText($text){
        if(strpos($text, '<') !== false) {
            $text = '<div>'.$text.'</div>';
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
            //$domDoc1 = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $doc->saveHTML());

            # remove <!DOCTYPE
            $doc->removeChild($doc->doctype);
            # remove <html><body></body></html>
            $domDoc2 = "";
            $element = $doc->firstChild->firstChild->firstChild;
            $element = $this->removeAdditionalHtmlTag($element);
            $children  = $element->childNodes;
            foreach ($children as $child)
            {
                $domDoc2 .= $element->ownerDocument->saveHTML($child);
            }
            $text = $domDoc2;
        }

        return $text;
    }

    /***
     * @param domElement $element
     * @return mixed
     */
    private function removeAdditionalHtmlTag($element){
        if($element->nodeType != XML_ELEMENT_NODE)
            return $element;

        if($element->hasAttribute('class')){
            $class = $element->getAttribute('class');
            $attrList = ['ow_oembed_attachment_preview'];

            foreach($attrList as $attr){
                if(strpos($class, $attr) !== false){
                    return false;
                }
            }
        }

        $children  = $element->childNodes;
        for ($i=$children->length-1;$i>=0;$i--)
        {
            $child = $children->item($i);
            $newChild = $this->removeAdditionalHtmlTag($child);
            if(isset($newChild) && $newChild!=false) {
                $element->replaceChild($newChild, $child);
            }
            else {
                $element->removeChild($child);
            }
        }

        return $element;
    }

    private function isImageUrl( $url )
    {
        $urlInfo = parse_url($url);

        if ( empty($urlInfo['path']) )
        {
            return false;
        }

        $foo = explode('.', $urlInfo['path']);
        $ext = end($foo);
        $ext = strtolower($ext);

        switch ( trim($ext) )
        {
            case 'jpeg':
            case 'jpg':
            case 'png':
                return true;

            default :
                return false;
        }
    }

    public function getMessagesForApi($userId, $conversationId)
    {
        $list = array();
        $length = 0;

        if (!empty($conversationId))
        {
            $count = 16;
            $deletedTimestamp = $this->getConversationDeletedTimestamp($conversationId);

            $dtoList = $this->messageDao->findListByConversationId($conversationId, $count, $deletedTimestamp);

            foreach($dtoList as $message)
            {
                $list[] = $this->getMessageDataForApi($message);
            }

            $length = $this->getConversationLength($conversationId);
        }

        return array('list'=>$list, 'length'=>$length);
    }

    public function findUnreadMessagesForApi( $userId, $ignoreList, $timeStamp = null )
    {
        $list = array();

        $messages = $this->messageDao->findUnreadMessages($userId, $ignoreList, $timeStamp, $this->getActiveModeList());

        foreach($messages as $id=>$message)
        {
            $list[] = $this->getMessageDataForApi($message);
        }

        return $list;
    }

    /***
     * @param $userId
     * @param $q
     * @return array<MAILBOX_BOL_Conversation>
     */
    public function searchMailTopicList( $userId, $q )
    {
        $convList = $this->conversationDao->findConversationListByUserId($userId, array('mail'));
        if(empty($convList))
        {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldLike('subject', "%$q%");
        $ex->andFieldNotEqual('subject', self::CHAT_CONVERSATION_SUBJECT);
        $ex->andFieldInArray('id', $convList);
        return $this->conversationDao->findListByExample($ex);
    }

    /**
     * @param $userId
     * @param $q
     * @param null $first
     * @param null $count
     * @return array
     */
    public function searchMessagesList( $userId, $q,$first=null,$count=null )
    {

        $limitClause = "";
        if(isset($first) && isset($count))
        {
            $limitClause=" LIMIT ".$first.",".$count;
        }
        $query = "
            SELECT * FROM " . OW_DB_PREFIX . "mailbox_message as t0
            INNER JOIN
                (
                    SELECT id, conversationId FROM " . OW_DB_PREFIX . "mailbox_message WHERE (`senderId` = :uId OR `recipientId` = :uId) AND `isSystem`=false AND `text` LIKE :q
                UNION
                    SELECT id, conversationId FROM " . OW_DB_PREFIX . "mailbox_message WHERE `senderId` = :uId AND `isSystem`=FALSE 
                    AND `recipientId` IN ( SELECT id FROM " . OW_DB_PREFIX . "base_user WHERE username LIKE :q UNION SELECT `userId` FROM `" . OW_DB_PREFIX . "base_question_data` WHERE `questionName`='realname' AND textValue LIKE :q)
                UNION 
                    SELECT id, conversationId FROM " . OW_DB_PREFIX . "mailbox_message WHERE `recipientId` = :uId AND `isSystem`=FALSE 
                    AND `senderId` IN ( SELECT id FROM " . OW_DB_PREFIX . "base_user WHERE username LIKE :q UNION SELECT `userId` FROM `" . OW_DB_PREFIX . "base_question_data` WHERE `questionName`='realname' AND textValue LIKE :q)
                ) as t2 on t0.id = t2.id
                INNER JOIN  " . OW_DB_PREFIX . "mailbox_conversation AS t3 ON t2.conversationId = t3.id AND ((t3.initiatorId = :uId && t3.initiatorDeletedTimestamp < t0.timeStamp )  || (t3.interlocutorId = :uId && t3.interlocutorDeletedTimestamp < t0.timeStamp ) ||
	(t3.initiatorId = :uId && t3.initiatorDeletedTimestamp < t0.timeStamp )  || (t3.interlocutorId = :uId && t3.interlocutorDeletedTimestamp < t0.timeStamp ))
            ORDER BY `timeStamp` DESC ".$limitClause.";";

        $list = OW::getDbo()->queryForList($query, array('q' => "%$q%", 'uId' => $userId));
        return $list;
    }

    public function searchMessagesListQuery($userId, $q = null) {
        $query = "
            SELECT t0.id, t0.timestamp as lastActivityTimeStamp, 'chat' as type FROM " . OW_DB_PREFIX . "mailbox_message as t0
            INNER JOIN
                (
                SELECT conversationId, MAX(id) as id FROM 
                    (
                        SELECT id, conversationId FROM " . OW_DB_PREFIX . "mailbox_message WHERE (`senderId` = :uId OR `recipientId` = :uId) AND `isSystem`=false AND `text` LIKE :q
                    UNION
                        SELECT id, conversationId FROM " . OW_DB_PREFIX . "mailbox_message WHERE `senderId` = :uId AND `isSystem`=FALSE 
                        AND `recipientId` IN ( SELECT id FROM " . OW_DB_PREFIX . "base_user WHERE username LIKE :q UNION SELECT `userId` FROM `" . OW_DB_PREFIX . "base_question_data` WHERE `questionName`='realname' AND textValue LIKE :q)
                    UNION 
                        SELECT id, conversationId FROM " . OW_DB_PREFIX . "mailbox_message WHERE `recipientId` = :uId AND `isSystem`=FALSE 
                        AND `senderId` IN ( SELECT id FROM " . OW_DB_PREFIX . "base_user WHERE username LIKE :q UNION SELECT `userId` FROM `" . OW_DB_PREFIX . "base_question_data` WHERE `questionName`='realname' AND textValue LIKE :q)
                    ) as t1 
                GROUP BY `conversationId`
                ) as t2 on t0.id = t2.id
            ";

        $params = array('q' => "%$q%", 'uId' => $userId);
        $result = [
            "query" => $query,
            "params" => $params
        ];

        return $result;
    }

    /***
     * @param $conversationIdList
     * @return array
     */
    public function getConversationsWithAttachmentFromConversationList($conversationIdList)
    {
        if (empty($conversationIdList))
        {
            return array();
        }

        $list = $this->attachmentDao->findConversationsWithAttachmentFromConversationList($conversationIdList);

        $result = array();
        foreach($conversationIdList as $conversationId)
        {
            if (in_array($conversationId, $list))
            {
                $result[$conversationId] = true;
            }
            else
            {
                $result[$conversationId] = false;
            }
        }

        return $result;
    }

    public function checkUserSendMessageInterval($userId)
    {
        $send_message_interval = (int)OW::getConfig()->getValue('mailbox', 'send_message_interval');
        $conversation = $this->conversationDao->findUserLastConversation($userId);
        if ($conversation != null)
        {
            if (time()-$conversation->createStamp < $send_message_interval)
            {
                return false;
            }
        }

        return true;
    }
    
    public function deleteAttachmentFiles()  // this method has calling from cron
    {
        $limit = 100;
        $attachDtoList = $this->attachmentDao->getAttachmentForDelete($limit);
     
        foreach ($attachDtoList as $attachDto)
        {   /* @var $attachDto MAILBOX_BOL_Attachment */
            $ext = UTIL_File::getExtension($attachDto->fileName);
            $path = $this->getAttachmentFilePath($attachDto->id, $attachDto->hash, $ext, $attachDto->fileName);
            if ( OW::getStorage()->fileExists($path) )
            {
                $attachDto->fileName = ('deleted_' . FRMSecurityProvider::generateUniqueId() . '_' . $attachDto->fileName);
                $this->attachmentDao->save($attachDto);
                $newPath = $this->getAttachmentDir().$attachDto->fileName;

                OW::getStorage()->renameFile($path, $newPath);
//              OW::getStorage()->removeFile($path);
            }
            $this->attachmentDao->deleteById($attachDto->id);
        }

        if (count($attachDtoList) == $limit) {
            OW::getEventManager()->trigger(new OW_Event(self::EVENT_DELETE_ATTACHMENT_FILES_INCOMPLETE));
        }
    }

    /***
     * @deprecated no need to use this since we change database encoding
     * @param $string
     * @return string|null
     */
    public function json_encode_text($string){
        return $string;
    }

    /***
     * @deprecated no need to use this since we change database encoding
     * @param $string
     * @return $string
     */
    public function json_decode_text($string){
        return $string;
    }

    public function decodeMessage($message){
        $messageText = $message->text;
        if(!isset($messageText))
            return null;

        $obj = json_decode($messageText);
        if(isset($obj->params)){
            $params = $obj->params;
            return $params->message;
        }
        return $messageText;
    }

    public function encodeMessage($message, $messageText){
        $oldMessageText = $message->text;
        if(!isset($oldMessageText) && isset($messageText))
            return $messageText;

        $obj = json_decode($oldMessageText);
        if(isset($obj->params)) {
            $params = $obj->params;
            $href = $params->href;
            if (strpos($messageText, $href) !== false) {
                $params->message = $messageText;
                return json_encode($obj);
            }
        }
        return $messageText;
    }

    public function IsSystem($message, $messageText){
        $oldMessageText = $message->text;
        if(!isset($oldMessageText) && isset($messageText))
            return 0;

        $obj = json_decode($oldMessageText);
        if(isset($obj->params)) {
            $params = $obj->params;
            $href = $params->href;
            if (strpos($messageText, $href) !== false) {
                return 1;
            }
        }
        return 0;
    }
    public function deleteMessage($id){
        /** @var MAILBOX_BOL_Message $message */
        $message = MAILBOX_BOL_MessageDao::getInstance()->findById($id);
        if(!OW::getUser()->isAuthenticated())
            return false;
        if (isset($message) && $message->senderId == OW::getUser()->getId()) {
            $lastMessageEntity = $this->getLastMessage($message->conversationId);
            MAILBOX_BOL_MessageDao::getInstance()->deleteById($id);
//            MAILBOX_BOL_MessageDao::getInstance()->clearReplies($id);
            $this->resetUserLastData($message->senderId);
            $this->resetUserLastData($message->recipientId);
            $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findById($message->conversationId);
            $lastMessage = $this->lastMessageDao->findByConversationId($message->conversationId);
            if($lastMessage->initiatorMessageId == $message->getId() || $lastMessage->interlocutorMessageId == $message->getId() ) {
                $newSenderLastMessage = MAILBOX_BOL_MessageDao::getInstance()->findConversationLastMessage($message->conversationId,$message->id,$message->senderId);
                $newReceiverLastMessage = MAILBOX_BOL_MessageDao::getInstance()->findConversationLastMessage($message->conversationId,$message->id,$message->recipientId);
                if(!isset($newSenderLastMessage))
                {
                    $newLastMessage=$newReceiverLastMessage;
                }
                else if(!isset($newReceiverLastMessage))
                {
                    $newLastMessage = $newSenderLastMessage;
                }
                else{
                    if($newSenderLastMessage->timeStamp>$newReceiverLastMessage->timeStamp)
                    {
                        $newLastMessage = $newSenderLastMessage;
                    }else{
                        $newLastMessage=$newReceiverLastMessage;
                    }
                }
                if(isset($newLastMessage)) {
                    if (!isset($lastMessage)) {
                        $lastMessage = new MAILBOX_BOL_LastMessage();
                        $lastMessage->conversationId = $conversation->id;
                    }
                    switch ($newLastMessage->senderId) {
                        case $conversation->initiatorId :

                            $unReadBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;
                            $readBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
                            $unDeletedBy = MAILBOX_BOL_ConversationDao::DELETED_INTERLOCUTOR;
                            $lastMessage->initiatorMessageId = $newLastMessage->id;
                            $consoleViewed = MAILBOX_BOL_ConversationDao::VIEW_INITIATOR;

                            break;

                        case $conversation->interlocutorId :
                            if ($lastMessage->initiatorMessageId == null) {
                                $lastMessage->initiatorMessageId = $conversation->initiatorId;
                            }
                            $unReadBy = MAILBOX_BOL_ConversationDao::READ_INITIATOR;
                            $readBy = MAILBOX_BOL_ConversationDao::READ_INTERLOCUTOR;
                            $unDeletedBy = MAILBOX_BOL_ConversationDao::DELETED_INITIATOR;
                            $lastMessage->interlocutorMessageId = $newLastMessage->id;
                            $consoleViewed = MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR;

                            break;
                    }
                    $conversation->deleted = (int)$conversation->deleted & ($unDeletedBy);
                    $conversation->read = ((int)$conversation->read & (~$unReadBy)) | $readBy;
                    $conversation->viewed = $consoleViewed;
                    $conversation->notificationSent = 0;
                    $conversation->lastMessageId = $newLastMessage->id;
                    $conversation->lastMessageTimestamp = $newLastMessage->timeStamp;

                    $this->conversationDao->save($conversation);

                    $this->lastMessageDao->save($lastMessage);
                }
            }
            $deletedMessage = new MAILBOX_BOL_DeletedMessage();
            $deletedMessage->conversationId = $message->conversationId;
            $deletedMessage->deletedId = $message->id;
            $deletedMessage->time = time();
            MAILBOX_BOL_DeletedMessageDao::getInstance()->save($deletedMessage);
            OW::getEventManager()->trigger(new OW_Event('mailbox.after_message_removed', array('id' => $id, 'senderId' => $message->senderId, 'recipientId' => $message->recipientId, 'conversationId' => $message->conversationId)));
            return $message->id;
        }
        return false;
    }

    public function editMessage($id,$messageText){
        if(!OW::getUser()->isAuthenticated()) {
            return false;
        }
        $messageText = UTIL_HtmlTag::stripTags($messageText);
        $messageText = preg_replace("/\r\n|\r|\n/", '<br />', $messageText);
        if(!isset($messageText) || empty($messageText))
        {
            return false;
        }
        $message = MAILBOX_BOL_MessageDao::getInstance()->findById($id);

        if (isset($message) && $message->senderId == OW::getUser()->getId()) {
            $encodedBody = $this->encodeMessage($message,$messageText);
            $isSystem = $this->IsSystem($message,$messageText);
            if(isset($encodedBody)){
                $message->text = $encodedBody;
                $message->changed = 1;
                $message->isSystem = $isSystem;
                MAILBOX_BOL_MessageDao::getInstance()->save($message);
                OW::getEventManager()->trigger(new OW_Event('mailbox.after_message_edited', array('id' => $id, 'senderId' => $message->senderId, 'recipientId' => $message->recipientId, 'conversationId' => $message->conversationId), $message));

                $this->resetUserLastData($message->senderId);
                $this->resetUserLastData($message->recipientId);
                return $message;
            }
        }
        return false;
    }

    /**
     * @param $convId
     * @return array
     */
    public function getConversationChanges($convId){
        $list = MAILBOX_BOL_MessageDao::getInstance()->getChangedData($convId);
        $deletedList = MAILBOX_BOL_DeletedMessageDao::getInstance()->getDeletedMessages($convId);
        return array('changed' => $list, 'deleted' => $deletedList);
    }

    public function checkIfReplyIdValid($conversationId,$userId)
    {
        return $this->conversationDao->checkIfReplyIdValid($conversationId,$userId);
    }

    /**
     * @param int $conversationId
     * @param int $first
     * @param int $count
     * @param int $deletedTimestamp
     * @return array
     */
    public function findMessageListHaveAttachmentByConversationId($conversationId, $first, $count, $deletedTimestamp = 0) {
        $sql = "SELECT `msg`.* FROM `" . OW_DB_PREFIX . "mailbox_message` AS `msg` 
                INNER JOIN `" . OW_DB_PREFIX . "mailbox_attachment` AS `att` 
                ON `msg`.`id` = `att`.`messageId`
                WHERE `msg`.`conversationId` = :conversationId AND `msg`.`timeStamp` > :deletedTimestamp 
                ORDER BY `msg`.`timeStamp` DESC
                LIMIT :first, :count;";
        $result = OW::getDbo()->queryForObjectList($sql, MAILBOX_BOL_MessageDao::getInstance()->getDtoClassName(), array('conversationId' => $conversationId, 'first' => $first, 'count' => $count, 'deletedTimestamp' => $deletedTimestamp));
        foreach ($result as $message) {
            $message->text = MAILBOX_BOL_ConversationService::getInstance()->json_decode_text($message->text);
        }
        return $result;
    }

    /**
     * @param int $userId
     * @param MAILBOX_BOL_Conversation $conversation
     * @return int $userStatus
     */
    public function isUserInitiatorOrInterlocutorForMuteConversation($userId, $conversation) {
        $userState = MAILBOX_BOL_ConversationDao::MUTE_NONE;
        if ($userId == $conversation->initiatorId) {
            $userState = MAILBOX_BOL_ConversationDao::MUTE_INITIATOR;
        } else if ($userId == $conversation->interlocutorId) {
            $userState = MAILBOX_BOL_ConversationDao::MUTE_INTERLOCUTOR;
        }
        return $userState;
    }

    public function isConversationMutedByUserEvent(OW_Event $event) {
        $params = $event->getParams();
        if (isset($params['conversationId']) && isset($params['userId'])) {
            $conversationId = $params['conversationId'];
            $userId  = $params['userId'];
            $conversation = MAILBOX_BOL_ConversationService::getInstance()->getConversation($conversationId);
            if ($conversation == null || empty($conversation)) {
                return;
            }

            $userState = $this->isUserInitiatorOrinterlocutorForMuteConversation($userId, $conversation);

            $muted = ($conversation->muted & $userState);
            $muted = ($muted > 0);

            $event->setData(array('muted' => $muted));
        }
    }

    /**
     * @param int $convId
     * @param string $q
     * @param int|null $first
     * @param int|null $count
     * @return array
     */
    public function searchMessagesListCountInConversation($convId, $userId, $q) {
        $query = "
            SELECT COUNT(*) FROM " . OW_DB_PREFIX . "mailbox_message as t0
                INNER JOIN  " . OW_DB_PREFIX . "mailbox_conversation AS t3 ON t0.conversationId = t3.id AND ((t3.initiatorId = :uId && t3.initiatorDeletedTimestamp < t0.timeStamp )  || (t3.interlocutorId = :uId && t3.interlocutorDeletedTimestamp < t0.timeStamp ) ||
	(t3.initiatorId = :uId && t3.initiatorDeletedTimestamp < t0.timeStamp )  || (t3.interlocutorId = :uId && t3.interlocutorDeletedTimestamp < t0.timeStamp ))
	        WHERE t0.`conversationId` = :convId AND t0.`isSystem`=false AND t0.`text` LIKE BINARY :q
            ORDER BY `timeStamp` DESC ;";

        $list = OW::getDbo()->queryForColumn($query, array('q' => "%$q%", 'convId' => $convId, 'uId' => $userId));
        return $list;
    }

    public function addMessageAttachmentsThumbnails($messages, $canvasDataList) {
        if (empty($messages) || empty($canvasDataList)) {
            return;
        }

        $attachments = MAILBOX_BOL_AttachmentDao::getInstance()->findAttachmentsByMessageIdList(array_column($messages, 'id'));

        if (empty($attachments) || count($canvasDataList) != count($attachments)) {
            return;
        }

        /** @var BOL_Attachment $attachment */
        foreach ($attachments as $key => &$attachment) {
            /** @var MAILBOX_BOL_Message $message */
            $message = $messages[$key];
            if (!OW::getUser()->isAuthenticated() || ($message->senderId != OW::getUser()->getId() && $message->recipientId != OW::getUser()->getId())) {
                continue;
            }

            $imageName = $this->generateThumbnailFileName($attachment);

            $tmpVideoImageFile = OW::getPluginManager()->getPlugin('mailbox')->getPluginFilesDir() . $imageName;

            $filteredData = explode(',', $canvasDataList[$key]);
            if (!isset($filteredData[1])) {
                continue;
            }

            $valid = FRMSecurityProvider::createFileFromRawData($tmpVideoImageFile, $filteredData[1]);
            if (!$valid) {
                continue;
            }

            $imageFile = MAILBOX_BOL_ConversationService::getInstance()->getAttachmentDir() . $imageName;

            try {
                OW::getStorage()->copyFile($tmpVideoImageFile, $imageFile);
                $attachment->thumbName = $imageName;
                OW::getStorage()->removeFile($tmpVideoImageFile);
            } catch (Exception $e) {
                OW::getLogger()->writeLog(OW_Log::ERROR, 'mailbox_save_video_thumbnail', ['actionType' => OW_Log::UPDATE, 'entityType' => 'video', 'entityId' => $attachment->id]);
            }
        }
        MAILBOX_BOL_AttachmentDao::getInstance()->batchSave($attachments);
    }

    /**
     * @param BOL_Attachment $attachment
     * @return string
     */
    private function generateThumbnailFileName($attachment) {
        $videoNameParts = explode('.', $attachment->fileName);
        $imageName = "";
        foreach ($videoNameParts as $videoNamePart) {
            if ($videoNamePart != end($videoNameParts)) {
                $imageName = $imageName . $videoNamePart;
            }
        }

        $imageName = "attachment_" . $attachment->id . "_" . FRMSecurityProvider::generateUniqueId() . "_" . $imageName . '.png';
        return $imageName;
    }


}