<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceMailbox
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getMessages(){
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array();
        }

        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        $userId = OW::getUser()->getId();
        return $this->processGetMessages($userId, $count);
    }

    public function processGetMessages($userId, $count, $search = ""){
        $data = array();
        $first = 0;
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }
        $convList = MAILBOX_BOL_ConversationService::getInstance()->getConversationListByUserId($userId, $first, $count, null, $search);
        foreach ($convList as $conv){
            $data[] = $this->preparedConversation($conv, $count, false);
        }

        return $data;
    }

    public function getUserMessage(){
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array();
        }

        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $opponentId = null;
        if(isset($_GET['opponentId'])){
            $opponentId = $_GET['opponentId'];
        }

        if($opponentId == null){
            return array();
        }

        $userId = OW::getUser()->getId();
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if(isset($_GET['first'])){
            $first = (int) $_GET['first'];
        }

        return $this->processGetUserMessages($userId, $opponentId, $first, $count);
    }

    public function markUserMessage(){
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false);
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false);
        }

        $opponentId = null;
        if(isset($_GET['opponentId'])){
            $opponentId = $_GET['opponentId'];
        }

        if($opponentId == null){
            return array('valid' => false);
        }

        $userId = OW::getUser()->getId();

        $convId = MAILBOX_BOL_ConversationService::getInstance()->getChatConversationIdWithUserById($userId, $opponentId);
        $this->markMessages($convId, $userId);
        return array('valid' => true);
    }

    public function markMessages($convId, $userId) {
        if ($convId == null || $userId == null) {
            return;
        }
        $unreadMessages = MAILBOX_BOL_MessageDao::getInstance()->findUnreadMessagesForConversation($convId, $userId);
        $unreadMessagesId = array();
        foreach ($unreadMessages as $unreadMessage) {
            $unreadMessagesId[] = $unreadMessage->id;
        }
        if(!empty($unreadMessagesId)){
            MAILBOX_BOL_ConversationService::getInstance()->markMessageIdListRead($unreadMessagesId);
        }
    }

    public function processGetUserMessages($userId, $opponentId, $first, $count){
        $convId = MAILBOX_BOL_ConversationService::getInstance()->getChatConversationIdWithUserById($userId, $opponentId);
        $data = array();

        if($convId != null) {
            $convList = MAILBOX_BOL_ConversationService::getInstance()->getConversationListByUserId($userId, $first, $count, $convId);
            if($convList != null){
                $data = $this->preparedConversation($convList[0], $count);
                $this->markMessages($convId, $userId);
            }
        }
        return $data;
    }

    public function sendMessage(){
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['entityId']) && isset($_POST['entityType']) && isset($_POST['storyId'])){
            return array('valid' => false, 'message' => 'input_error');
        }

        if( (isset($_POST['entityId']) && !isset($_POST['entityType']))
            || (isset($_POST['entityType']) && !isset($_POST['entityId'])) ){
            return array('valid' => false, 'message' => 'input_error');
        }

        $opponentId = null;
        if(isset($_POST['opponentId'])){
            $opponentId = $_POST['opponentId'];
        }

        $text = null;
        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }

        $isForwarded = false;
        if(isset($_POST['isForwarded'])){
            if ($_POST['isForwarded'] == true || $_POST['isForwarded'] ==1){
                $isForwarded = true;
            }
        }

        $costumeFeatures = null;
        if(isset($_POST['entityId']) && isset($_POST['entityType'])){
            $text = '(Post)';
            $costumeFeatures = json_encode(array("type"=>"post","entityId"=>(int)$_POST['entityId'], "entityType"=>$_POST['entityType']));
        }

        if(isset($_POST['storyId'])){
            
            $text = $text ? $text :'(Story)';
            $costumeFeatures = json_encode(array("type"=>"story","id"=>(int)$_POST['storyId']));
        }

        $userId = OW::getUser()->getId();
        if($userId == $opponentId || !isset($text) || $text == '' || $opponentId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!is_numeric($opponentId)){
            return array('valid' => false, 'message' => 'input_error');
        }

        $conversation = null;
        $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentId);
        if ($conversationId == null || empty($conversationId)){
            $conversation = $conversationService->createChatConversation($userId, $opponentId);
            $conversationId = $conversation->getId();
        }

        $text = str_replace('↵',"\r\n", $text);
        $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($text, false);
        $event = new OW_Event('mailbox.before_send_message', array(
            'senderId' => $userId,
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

        $text = $data['message'];

        try
        {
            if($conversation == null && $conversationId != null){
                $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findById($conversationId);
            }
            if($conversation == null){
                return array('valid' => false, 'message' => 'authorization_error');
            }
            $validFile = false;
            if(isset($_FILES) && isset($_FILES['file'])){
                $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
                if ($isFileClean) {
                    $validFile = true;
                    $bundle = FRMSecurityProvider::generateUniqueId();
                    $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
                    $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);
                    BOL_AttachmentService::getInstance()->processUploadedFile('mailbox', $_FILES['file'], $bundle, $validFileExtensions, $maxUploadSize);
                    $items = BOL_AttachmentService::getInstance()->getFilesByBundleName('mailbox', $bundle);
                } else {
                    return array('valid' => false, 'message' => 'virus_detected');
                }
            }
            $replyId = null;
            if (isset($_POST['replyId'])) {
                $replyId = $_POST['replyId'];
            }
            $message = $conversationService->createMessage($conversation, $userId, $text, $replyId,  false, null, $isForwarded, $costumeFeatures);
            if($validFile){
                MAILBOX_BOL_ConversationService::getInstance()->addMessageAttachments($message->id, $items);
            }
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

    public function forwardMessage(){
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }


        if(empty($_POST['opponentIds']) || !isset($_POST['messageIds'])){
            return array('valid' => false, 'message' => 'input_error');
        }


        $opponentIds = (array) explode(',', $_POST['opponentIds'] );
        $opponentUsers = BOL_UserDao::getInstance()->findByIdList($opponentIds);

        $messageIds = (array) explode(',', $_POST['messageIds'] );
        $messages = MAILBOX_BOL_MessageDao::getInstance()->findByIdList($messageIds);

        $items = array();
        /* @var $message MAILBOX_BOL_Message*/
        foreach ($messages as $message) {
            if ($message->recipientId != OW::getUser()->getId() && $message->senderId != OW::getUser()->getId()) {
                return array('valid' => false, 'message' => 'unauthorized_action_error');
            }
            $attachments = $conversationService->findAttachmentsByMessageIdList([$message->id]);
            $attachments = array_key_exists($message->id, $attachments) ? $attachments[$message->id] : array();
            $text = $message->text;
            /* @var $opponentUser BOL_User */
            foreach ($opponentUsers as $opponentUser) {
                $opponentUserId = $opponentUser->id;
                $userId = OW::getUser()->getId();
                if ($userId == $opponentUserId || !isset($text) || $text == '' || $opponentUserId == null) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }

                $conversation = null;
                $conversationId = $conversationService->getChatConversationIdWithUserById($userId, $opponentUserId);
                if ($conversationId == null || empty($conversationId)) {
                    $conversation = $conversationService->createChatConversation($userId, $opponentUserId);
                    $conversationId = $conversation->getId();
                }

                $text = str_replace('↵', "\r\n", $text);
                $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($text, false);
                $event = new OW_Event('mailbox.before_send_message', array(
                    'senderId' => $userId,
                    'recipientId' => $opponentUserId,
                    'conversationId' => $conversationId,
                    'message' => $text
                ), array('result' => true, 'error' => '', 'message' => $text));
                OW::getEventManager()->trigger($event);

                $data = $event->getData();

                if (!$data['result']) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }

                $text = $data['message'];
                if (empty(trim($text)) && !empty($attachments)) {
                    $text = OW::getLanguage()->text('mailbox', 'attachment');
                }
                try {
                    if ($conversation == null && $conversationId != null) {
                        $conversation = MAILBOX_BOL_ConversationDao::getInstance()->findById($conversationId);
                    }
                    if ($conversation == null) {
                        return array('valid' => false, 'message' => 'authorization_error');
                    }
                    $replyId = null;
                    $newMessage = $conversationService->forwardMessage($conversation, $userId, $text, $replyId, false, null, true);
                    if (!empty($attachments)) {
                        BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);
                        foreach ($attachments as $attachment) {
                            $ext = UTIL_File::getExtension($attachment->fileName);
                            $attachmentPath = $conversationService->getAttachmentFilePath($attachment->id, $attachment->hash, $ext, $attachment->fileName);
                            $fileExt = UTIL_File::getExtension($attachment->fileName);
                            $newBundle = FRMSecurityProvider::generateUniqueId('mailbox_dialog_' . $conversationId . '_' . time() . '_' . $opponentUserId);
                            $newAttachmentFileName = urldecode($attachment->fileName);
                            $item = array();
                            $item['name'] = $newAttachmentFileName;
                            $item['type'] = 'image/' . $fileExt;
                            $item['error'] = 0;
                            $item['size'] = UTIL_File::getFileSize($attachmentPath, false);
                            $pluginKey = 'mailbox';
                            $tempFileId = BOL_FileTemporaryService::getInstance()->addTemporaryFile($attachmentPath, $newAttachmentFileName, $userId);
                            $item['tmp_name'] = BOL_FileTemporaryService::getInstance()->getTemporaryFilePath($tempFileId);
                            $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile($pluginKey, $item, $newBundle);
                            $items = BOL_AttachmentService::getInstance()->getFilesByBundleName('mailbox', $newBundle);
                            $conversationService->addMessageAttachments($newMessage->id, $items);
                        }
                    }
                } catch (InvalidArgumentException $e) {
                    return array('valid' => false, 'message' => 'authorization_error');
                }
            }
            $item = $conversationService->getRawMessageInfo($newMessage);
            if (isset($_POST['_id']) && !empty($_POST['_id']) && $_POST['_id'] != null && $_POST['_id'] != "null") {
                $item['_id'] = $_POST['_id'];
            } else {
                $item['_id'] = $newMessage->id;
            }
            $items[] = $item;
        }
        return array('valid' => true, 'messages'=>$items);
    }

    public function preparedConversation($conversationObj, $count, $returnMessages = true){
        $data = array();

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $data['conversation_info'] = array();

        $convInfo = array();
        if($conversationObj == null){
            return $data;
        }

        if (isset($conversationObj['opponentId']) && !isset($conversationObj['userId'])) {
            $conversationObj['userId'] = $conversationObj['opponentId'];
        }

        $convInfo['conversationId'] = (int) $conversationObj['conversationId'];
        $convInfo['user'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUserInformationById($conversationObj['userId']);
        if (isset($convInfo['user'])) {
            $convInfo['user']['online'] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->isUserOnline($conversationObj['userId']);
        }
        $convInfo['preview_text'] = $conversationObj['previewText'];
        if (isset($conversationObj['originalPreviewText'])) {
            $convInfo['preview_text'] = $conversationObj['originalPreviewText'];
        }
        $convInfo['preview_text'] = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($convInfo['preview_text'],true,false,true);

        //check if contains emoji html
        if(strpos($convInfo['preview_text'], "<img class='emj'") !== false){
            preg_match_all(
                '/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u',
                $convInfo['preview_text'],
                $emojis
            );
            $convInfo['preview_text'] = $emojis[0][0];
        }

        $convInfo['last_time'] = $conversationObj['lastMessageTimestamp'];
        $convInfo['new_count'] = $conversationObj['newMessageCount'];
        if (isset($conversationObj['lastMessageRecipientId'])) {
            $convInfo['lastMessageRecipientId'] = (int) $conversationObj['lastMessageRecipientId'];
        }
        if (isset($conversationObj['recipientRead'])) {
            $convInfo['recipientRead'] = (bool) $conversationObj['recipientRead'];
        }
        $convInfo['mode'] = $conversationObj['mode'];

        $userId1 = (int) $conversationObj['userId'];
        $userId2 = (int) OW::getUser()->getId();
        if ($userId1 == $userId2 && isset($conversationObj['opponentId']) && $conversationObj['opponentId'] != $userId1) {
            $userId1 = (int) $conversationObj['opponentId'];
        }

        $blockInfo = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getBlockUsersInfo($userId2, $userId1);
        $convInfo['isBlocked'] = $blockInfo['isBlocked'];
        $convInfo['blockedBy'] = $blockInfo['blockedBy'];

        $convInfo['muted'] = false;
        $isConversationMutedByUser = OW::getEventManager()->trigger(new OW_Event('mailbox.isConversationMutedByUser', array('userId' => OW::getUser()->getId(),  'conversationId' => $conversationObj['conversationId'])));
        $isConversationMutedByUser = $isConversationMutedByUser->getData();
        if (isset($isConversationMutedByUser['muted'])) {
            $convInfo['muted'] = $isConversationMutedByUser['muted'];
        }

        $data['conversation_info'] = $convInfo;
        if($returnMessages) {
            $data['messages'] = $this->getMessagesOfConversation($conversationObj['conversationId'], $count);
        }

        return $data;
    }

    private function getMessagesOfConversation($conversationId, $count){
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $deletedTimestamp = $conversationService->getConversationDeletedTimestamp($conversationId);

        $dtoList = array();
        $list = array();
        if(isset($_GET['last_id'])){
            $dtoList = MAILBOX_BOL_MessageDao::getInstance()->findHistory($conversationId, $_GET['last_id'], $count, $deletedTimestamp);
            $dtoList = array_reverse($dtoList);
        }else{
//            $unreadMessages = MAILBOX_BOL_MessageDao::getInstance()->findUnreadMessagesForConversation($conversationId, OW::getUser()->getId(), $deletedTimestamp);
//            $minIdUnreadMessage = null;
//            if (is_array($unreadMessages)) {
//                foreach ($unreadMessages as $unreadMessage) {
//                    if ($minIdUnreadMessage == null || $minIdUnreadMessage > $unreadMessage->id) {
//                        $minIdUnreadMessage = $unreadMessage->id;
//                    }
//                }
//            }
//            if ($minIdUnreadMessage != null) {
//                $dtoList = MAILBOX_BOL_MessageDao::getInstance()->findMessagesAfterMessageId($conversationId, $minIdUnreadMessage, $deletedTimestamp);
//            } else {
                $dtoList = MAILBOX_BOL_MessageDao::getInstance()->findListByConversationId($conversationId, $count, $deletedTimestamp);
                $dtoList = array_reverse($dtoList);
//            }
        }
        foreach($dtoList as $message)
        {
            $messageInfo = $conversationService->getRawMessageInfo($message);
            if(isset($message->costumeFeatures)){
                $features = (array) json_decode( $message->costumeFeatures );
                $customType = $features['type'];
                if($customType == 'post'){
                    $actions = NEWSFEED_BOL_Service::getInstance()->findActionListByEntityIdsAndEntityType((array)$features['entityId'], $features['entityType']);
                    $actionIds = array_column( $actions,'id');
                    $actionList = NEWSFEED_BOL_Service::getInstance()->findActionByIds($actionIds);
                    $data =FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->preparedActionsData($actionList, array('comments'));
                    $messageInfo["postData"] = $data[0];
                }
                if($customType == 'story'){

                    $stories = STORY_BOL_StoryDao::getInstance()->findStoriesById((array)$features['id']);
                    $newAllStories = FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance()->appendStoryUrl($stories);
                    $messageInfo["storyData"] = $newAllStories[0];
                    
                    $isActiveStory =     STORY_BOL_StoryDao::getInstance()->isActiveStory($messageInfo["storyData"]->storyId) ? true : false;
                    $messageInfo["storyData"]->isActiveStory = $isActiveStory;
    
                    $isFollower = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->isUserFollower($messageInfo["storyData"]->userId);
                    $messageInfo["storyData"]->isFollower = $isFollower;

                }
            }
            $list[] = $messageInfo;
        }

        return $list;
    }

    public function removeMessage() {
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $messageId = null;

        if(isset($_POST['id'])){
            $messageId = $_POST['id'];
        }

        if($messageId == null || !OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $done = MAILBOX_BOL_ConversationService::getInstance()->deleteMessage($messageId);
        if ($done){
            return array('valid' => true, 'id' => (int) $messageId);
        }

        return array('valid' => false, 'message' => 'authorization_error');
    }

    public function clearMessages() {
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $opponentId = null;

        if(isset($_POST['opponentId'])){
            $opponentId = $_POST['opponentId'];
        }

        if($opponentId == null || !OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();

        $conversationId = MAILBOX_BOL_ConversationService::getInstance()->getChatConversationIdWithUserById($userId, $opponentId);

        if (!isset($conversationId)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        MAILBOX_BOL_ConversationService::getInstance()->deleteConversation(array($conversationId), $userId);

        return array('valid' => true, 'opponentId' => (int) $opponentId, 'conversationId' => (int) $conversationId);
    }

    public function deleteConversation(){

        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(isset($_POST['conversationId'])){
            $conversationId = $_POST['conversationId'];
        }else{
            return array('valid' => false, 'message' => 'input_error');
        }

        $userId = OW::getUser()->getId();

        $conversationObject = MAILBOX_BOL_ConversationDao::getInstance()->findConversationObjectById($conversationId);
        if(empty($conversationObject)){
            return array('valid' => false, 'message' => 'chat_not_found');
        }
        if($userId == $conversationObject->initiatorId || $userId == $conversationObject->interlocutorId){
            MAILBOX_BOL_MessageDao::getInstance()->deleteByConversationId($conversationId);
            MAILBOX_BOL_LastMessageDao::getInstance()->deleteByConversationId($conversationId);
            MAILBOX_BOL_ConversationDao::getInstance()->deleteById($conversationId);
            MAILBOX_BOL_ConversationService::getInstance()->deleteAttachmentsByConversationList(array($conversationId));
            return array('valid' => true, 'message' => "removed_successfully");
        }else{
            return array('valid' => false, 'message' => 'authorization_error');
        }
    }

    public function editMessage() {
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $messageId = null;
        $text = null;

        if(isset($_POST['id'])){
            $messageId = $_POST['id'];
        }

        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }

        if($text == null || $messageId == null || !OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $text = str_replace('↵',"\r\n", $text);
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $message = $conversationService->editMessage($messageId, $text);
        if ($message){
            if (isset($message->text)) {
                $message->text = $conversationService->json_decode_text($message->text);
            }
            $info = $conversationService->getRawMessageInfo($message);
            return array('valid' => true, 'message' => $info);
        }

        return array('valid' => false, 'message' => 'authorization_error');
    }

    public function getForwardList(){
        $list = array();
        if(!FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array();
        }

        if(!OW::getUser()->isAuthenticated()){
            return array();
        }

        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        $userId = OW::getUser()->getId();

        $search = '';
        if(isset($_GET['search'])){
            $search = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_GET['search'], true, true);
        }

        $first = 0;
        if(isset($_GET['first'])){
            $first = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($_GET['first'], true, true);
            $first = (int) $first;
        }

        $param = array(
            'search' => $search,
            'userId' => $userId,
            'count' => $count,
            'first' => $first
        );

        $event = OW::getEventManager()->trigger(new OW_Event('plugin.friends.get_friend_list_by_display_name', $param));
        $friendsIds = $event->getData();
        $first = (!empty($_GET['first']) && intval($_GET['first']) > 0 ) ? intval($_GET['first']) : 1;
        $followingUsers = NEWSFEED_BOL_FollowDao::getInstance()->findUserFollowingListWithPaginate($userId, $first, 100000000);
        $followerUsers = NEWSFEED_BOL_FollowDao::getInstance()->findUserFollowerListWithPaginate($userId, $first, 100000000);
        $followerUserIds = array_column( $followerUsers, "userId");
        $followingUserIds = array_column( $followingUsers, "feedId");
        $followUserIds = array_merge($followingUserIds, $followerUserIds);
        $userIds = array_unique( array_merge($followUserIds, $friendsIds) );

        $friends = array();
        if(!empty($userIds)) {
            $usersObject = BOL_UserService::getInstance()->findUserListByIdList($userIds);
            $usernames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($userIds, 2);
            foreach ($usersObject as $userObject) {
                $username = null;
                if (isset($usernames[$userObject->id])) {
                    $username = $usernames[$userObject->id];
                }

                $avatarUrl = null;
                if (isset($avatars[$userObject->id])) {
                    $avatarUrl = $avatars[$userObject->id];
                }
                $friendss[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatarUrl, $username, false, true);
            }
        }
        $conversations = $this->processGetMessages($userId, $count, $search);
        $conversationsUsers = array_column( array_column( $conversations, 'conversation_info'), 'user');
//        $friends = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->getUserFriends($userId);
        $list['conversationsUsers'] = $conversationsUsers;
        $list['friends'] = $friends;
        return $list;
    }

    public function getChatMedia() {
        if (!FRMSecurityProvider::checkPluginActive('mailbox', true)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $opponentId = null;
        if (isset($_GET['opponentId'])) {
            $opponentId = $_GET['opponentId'];
        }

        if ($opponentId == null) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        $first = 0;
        $count = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getPageSize();
        if (isset($_GET['first'])) {
            $first = (int)$_GET['first'];
        }

        $convId = MAILBOX_BOL_ConversationService::getInstance()->getChatConversationIdWithUserById($userId, $opponentId);

        if ($convId == null) {
            return array('valid' => false, 'message' => 'authorization_error');
        }


        $deletedTimestamp = MAILBOX_BOL_ConversationService::getInstance()->getConversationDeletedTimestamp($convId);
        $messages = MAILBOX_BOL_ConversationService::getInstance()->findMessageListHaveAttachmentByConversationId($convId, $first, $count, $deletedTimestamp);

        $result = array();
        foreach ($messages as $message) {
            $messageInfo = MAILBOX_BOL_ConversationService::getInstance()->getRawMessageInfo($message);

            $date = explode(" ", $messageInfo['dateLabel']);
            if (count($date) ==  3) {
                $date = $date[1] . " " . $date[2]; // example: "تیر 1400"
            } else {
                $date = $messageInfo['dateLabel']; // example: "امروز", "دیروز"
            }

            if (!array_key_exists($date, $result)) {
                $result[$date]['photo'] = array();
                $result[$date]['video'] = array();
            }

            if (!empty($messageInfo['attachments'])) {
                $attachment = $messageInfo['attachments'][0];
                $filename = $attachment['fileName'];
                if (UTIL_FILE::validateImage($filename)) {
                    $result[$date]['photo'][] = $messageInfo;
                } else if (UTIL_FILE::validateVideo($filename)) {
                    $result[$date]['video'][] = $messageInfo;
                }
            }
        }

        return $result;
    }

    public function muteChat() {
        if (!FRMSecurityProvider::checkPluginActive('mailbox', true)) {
            array('valid' => false, 'message' => 'authorization_error');
        }

        if (!OW::getUser()->isAuthenticated()) {
            array('valid' => false, 'message' => 'authorization_error');
        }

        $opponentId = null;
        if (isset($_POST['opponentId'])) {
            $opponentId = $_POST['opponentId'];
        }

        if (!is_numeric($opponentId)) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $userId = OW::getUser()->getId();

        $conversation = MAILBOX_BOL_ConversationService::getInstance()->findChatConversationWithUserById($userId, $opponentId);
        if ($conversation == null || empty($conversation)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userState = MAILBOX_BOL_ConversationService::getInstance()->isUserInitiatorOrInterlocutorForMuteConversation($userId, $conversation);
        $conversation->muted = $conversation->muted | $userState;

        MAILBOX_BOL_ConversationService::getInstance()->saveConversation($conversation);

        return array("valid" => true, "message" => "muted_successfully");
    }

    public function unmuteChat() {
        if (!FRMSecurityProvider::checkPluginActive('mailbox', true)) {
            array('valid' => false, 'message' => 'authorization_error');
        }

        if (!OW::getUser()->isAuthenticated()) {
            array('valid' => false, 'message' => 'authorization_error');
        }

        $opponentId = null;
        if (isset($_POST['opponentId'])) {
            $opponentId = $_POST['opponentId'];
        }

        if (!is_numeric($opponentId)) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $userId = OW::getUser()->getId();

        $conversation = MAILBOX_BOL_ConversationService::getInstance()->findChatConversationWithUserById($userId, $opponentId);
        if ($conversation == null || empty($conversation)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userState = MAILBOX_BOL_ConversationService::getInstance()->isUserInitiatorOrInterlocutorForMuteConversation($userId, $conversation);
        $conversation->muted = $conversation->muted & (~$userState);

        MAILBOX_BOL_ConversationService::getInstance()->saveConversation($conversation);

        return array("valid" => true, "message" => "unmuted_successfully");
    }

    private function getSearchChatPageSize() {
        return 100;
    }

    public function multimediaCall(){
        if (!isset($_POST['subType'])) {
            return;
        }
        $params = array(
            'opponentIds' => explode(",",$_POST['opponentIds']),
            'candidate' => $_POST['candidate'],
            'offer' => $_POST['offer'],
            'subType' => $_POST['subType'],
            'callMode' => $_POST['callMode'],
            'callId' => $_POST['callId'],
            'userId' => OW::getUser()->getId(),
        );

        $result = MULTIMEDIA_BOL_Service::getInstance()->callActionController($params);
        return array('valid' => true, 'call_info' => $result);
    }
    public function searchChatMessages() {
        if (!FRMSecurityProvider::checkPluginActive('mailbox', true)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $opponentId = null;
        if (isset($_GET['opponentId'])) {
            $opponentId = $_GET['opponentId'];
        }

        if (!is_numeric($opponentId)) {
            return array('valid' => false, 'message' => 'input_error');
        }

        $userId = OW::getUser()->getId();

        $conversation = MAILBOX_BOL_ConversationService::getInstance()->findChatConversationWithUserById($userId, $opponentId);
        if ($conversation == null || empty($conversation)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $searchValue = '';
        if (isset($_GET['searchValue'])) {
            $searchValue = UTIL_HtmlTag::stripTagsAndJs($_GET['searchValue']);
        }

        $count = FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance()->getSearchChatPageSize();

        $searchValueCount = MAILBOX_BOL_ConversationService::getInstance()->searchMessagesListCountInConversation($conversation->id, $userId, $searchValue);
        if ($searchValueCount == 0) {
            return array('valid' => true, 'message' => 'not_found');
        }

        $data = array();
        $data['search_info'] = array('total' => (int)$searchValueCount, 'in_this_request' => 0);
        $data['messages'] = $this->getMessagesOfConversation($conversation->id, $count);

        foreach ($data['messages'] as &$item) {
            $item['search_value_exist'] = false;
            if (strpos($item['text'], $searchValue) !== false) {
                $item['search_value_exist'] = true;
                $data['search_info']['in_this_request']++;
            }
        }

        return $data;
    }

    public function setThumbnail($attachmentId, $fileData) {
        if($attachmentId  == null || $fileData == null || !FRMSecurityProvider::checkPluginActive('mailbox', true)){
            return array('valid' => false, 'thumbnail' => '');
        }

        /** @var MAILBOX_BOL_Attachment $mailboxAttachment */
        $mailboxAttachment = MAILBOX_BOL_AttachmentDao::getInstance()->findById($attachmentId);

        /** @var MAILBOX_BOL_Message $message */
        $message = MAILBOX_BOL_MessageDao::getInstance()->findById($mailboxAttachment->messageId);

        if ($mailboxAttachment === null || $message === null || ($message->senderId != OW::getUser()->getId() && $message->recipientId != OW::getUser()->getId())) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $videoNameParts = explode('.', $mailboxAttachment->fileName);
        $imageName = "";
        foreach ($videoNameParts as $videoNamePart) {
            if ($videoNamePart != end($videoNameParts)) {
                $imageName = $imageName . $videoNamePart;
            }
        }
        $imageName = "attachment_" . $attachmentId . "_" . FRMSecurityProvider::generateUniqueId() . "_" . $imageName . '.png';

        $tmpVideoImageFile = OW::getPluginManager()->getPlugin('mailbox')->getPluginFilesDir() . $imageName;

        $filteredData = explode(',', $fileData);
        if (!isset($filteredData[1])) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $valid = FRMSecurityProvider::createFileFromRawData($tmpVideoImageFile, $filteredData[1]);
        if (!$valid) {
            return array('valid' => false, 'thumbnail' => '');
        }

        $imageFile = MAILBOX_BOL_ConversationService::getInstance()->getAttachmentDir() . $imageName;

        try {
            OW::getStorage()->copyFile($tmpVideoImageFile, $imageFile);
            $mailboxAttachment->thumbName = $imageName;
            MAILBOX_BOL_AttachmentDao::getInstance()->save($mailboxAttachment);
        } catch (Exception $e) {
            return array('valid' => false, 'thumbnail' => '');
        }
        OW::getStorage()->removeFile($tmpVideoImageFile);

        $thumbnail = OW::getStorage()->getFileUrl($imageFile, false);
        return array('valid' => true, 'thumbnail' => $thumbnail);
    }
}