<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.controllers
 * @since 1.6.1
 * */
class MAILBOX_CTRL_Ajax extends OW_ActionController
{
    private $conversationService;

    public function init()
    {
        if (!OW::getRequest()->isAjax())
        {
            throw new Redirect404Exception();
        }

        if (!OW::getUser()->isAuthenticated())
        {
            echo json_encode('User is not authenticated');
            exit;
        }

        $this->conversationService = MAILBOX_BOL_ConversationService::getInstance();
    }

    public function getHistory()
    {
        $userId = OW::getUser()->getId();
        $conversationId = (int)$_POST['convId'];
        $beforeMessageId = (int)$_POST['messageId'];

        $data = $this->conversationService->getConversationHistory($conversationId, $beforeMessageId);

        exit(json_encode($data));
    }

    public function newMessage()
    {
        $form = OW::getClassInstance("MAILBOX_CLASS_NewMessageForm");
        /* @var $user MAILBOX_CLASS_NewMessageForm */

        if ($form->isValid($_POST))
        {
            $result = $form->process();
            exit(json_encode($result));
        }
        else
        {
            exit(json_encode(array($form->getErrors())));
        }
    }

    public function updateUserInfo()
    {
        //DDoS check
        if ( empty(OW_Session::getInstance()->get('lastUpdateRequestTimestamp')) )
        {
            OW_Session::getInstance()->set('lastUpdateRequestTimestamp', time());
        }
        else if ( (time() - (int) OW_Session::getInstance()->get('lastUpdateRequestTimestamp')) < 3 )
        {
            exit('{error: "Too much requests"}');
        }

        OW_Session::getInstance()->set('lastUpdateRequestTimestamp', time());

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        if ($errorMessage = $conversationService->checkPermissions())
        {
            exit(json_encode(array('error'=>$errorMessage)));
        }

        /* @var BOL_User $user */
        $user = null;

        if ( !empty($_POST['userId']) )
        {
            $user = BOL_UserService::getInstance()->findUserById($_POST['userId']);

            if (!$user)
            {
                $info = array(
                    'warning' => true,
                    'message' => 'User not found',
                    'type' => 'error'
                );
                exit(json_encode($info));
            }



            $eventParams = array(
                'action' => 'mailbox_invite_to_chat',
                'ownerId' => $user->getId(),
                'viewerId' => OW::getUser()->getId()
            );

            try
            {
                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $e )
            {
                //TODOS return message that has been set in a privacy value
                $info = array(
                    'warning' => true,
                    'message' => OW::getLanguage()->text('mailbox', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                    'type' => 'warning'
                );
                exit(json_encode($info));
            }

            if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $user->getId()) )
            {
                $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
                $info = array(
                    'warning' => true,
                    'message' => $errorMessage,
                    'type' => 'error'
                );
                exit(json_encode($info));
            }

            if (empty( $_POST['checkStatus'] ) || $_POST['checkStatus'] != 2)
            {
                /**
                 * commented by Mohammad Agha Abbasloo
                 * user can chat with other offline users
                 */
                $onlineStatus[$user->getId()] = BOL_UserService::getInstance()->findOnlineStatusForUserList(array($user->getId()));
                $checkOfflineChatEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ENABLE_DESKTOP_OFFLINE_CHAT, array('enOfflineChat' => true)));
                if(isset($checkOfflineChatEvent->getData()['setOfflineChat']) && $checkOfflineChatEvent->getData()['setOfflineChat']==true){
                    $onlineStatus[$user->getId()]=true;
                }
                if (!$onlineStatus[$user->getId()])
                {
                    $displayname = BOL_UserService::getInstance()->getDisplayName($user->getId());
                    $info = array(
                        'warning' => true,
                        'message' => OW::getLanguage()->text('mailbox', 'user_went_offline', array('displayname'=>$displayname)),
                        'type' => 'warning'
                    );
                    exit(json_encode($info));
                }
            }

            $info = $conversationService->getUserInfo($user->getId());
            exit(json_encode($info));
        }

        exit();
    }

    public function settings()
    {
        if (isset($_POST['soundEnabled']))
        {
            $_POST['soundEnabled'] = $_POST['soundEnabled'] === 'false' ? false : true;

            BOL_PreferenceService::getInstance()->savePreferenceValue('mailbox_user_settings_enable_sound', $_POST['soundEnabled'], OW::getUser()->getId());
        }

        if (isset($_POST['showOnlineOnly']))
        {
            $_POST['showOnlineOnly'] = $_POST['showOnlineOnly'] === 'false' ? false : true;
            BOL_PreferenceService::getInstance()->savePreferenceValue('mailbox_user_settings_show_online_only', $_POST['showOnlineOnly'], OW::getUser()->getId());

        }

        exit('true');
    }

    public function removeMessage()
    {
        if (isset($_POST['id'])) {
            $done = MAILBOX_BOL_ConversationService::getInstance()->deleteMessage($_POST['id']);
            $language = OW::getLanguage();
            if ($done)
                exit(json_encode(array('id' => $done, 'msg' => $language->text('mailbox', 'operation_successful'))));
            else
                exit(json_encode(array('error' => $language->text('mailbox', 'operation_unsuccessful'))));
        }
    }

    public function editMessage()
    {
        if (isset($_POST['messageId']) && isset($_POST['message'])) {
            $message = MAILBOX_BOL_ConversationService::getInstance()->editMessage($_POST['messageId'],$_POST['message']);
            $language = OW::getLanguage();
            if ($message)
                exit(json_encode(array('text' => MAILBOX_BOL_ConversationService::getInstance()->decodeMessage($message),'id' => $message->getId(), 'msg' => $language->text('mailbox', 'operation_successful'))));
            else
                exit(json_encode(array('error' => $language->text('mailbox', 'operation_unsuccessful'))));
        }
    }

    public function authorization(){
        $result = MAILBOX_BOL_AjaxService::getInstance()->authorizeAction($_POST);
        exit(json_encode($result));
    }

    public function ping()
    {
        $params = json_decode($_POST['request'], true);

        $event = new OW_Event('mailbox.ping', array('params'=>$params, 'command'=>'mailbox_ping'));
        OW::getEventManager()->trigger($event);

        exit( json_encode($event->getData()) );
    }

    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            echo json_encode(array());
            exit;
        }

        $kw = empty($_GET['term']) ? null : $_GET['term'];
        $idList = empty($_GET['idList']) ? null : $_GET['idList'];

        $context = empty($_GET["context"]) ? 'user' : $_GET["context"];
        $userId = OW::getUser()->getId();

        $entries = MAILBOX_BOL_AjaxService::getInstance()->getSuggestEntries($userId, $kw, $idList, $context);

        echo json_encode($entries);
        exit;
    }

    /**
     * Deprecated see AjaxService / bulkActions
     */
    public function bulkOptions()
    {
        $userId = OW::getUser()->getId();

        switch($_POST['actionName'])
        {
            case 'markUnread':
                $count = MAILBOX_BOL_ConversationService::getInstance()->markConversation($_POST['convIdList'], $userId, MAILBOX_BOL_ConversationService::MARK_TYPE_UNREAD);
                $message = OW::getLanguage()->text('mailbox', 'mark_unread_message', array('count'=>$count));
                break;
            case 'markRead':
                $count = MAILBOX_BOL_ConversationService::getInstance()->markConversation($_POST['convIdList'], $userId, MAILBOX_BOL_ConversationService::MARK_TYPE_READ);
                $message = OW::getLanguage()->text('mailbox', 'mark_read_message', array('count'=>$count));
                break;
            case 'delete':
                $count = MAILBOX_BOL_ConversationService::getInstance()->deleteConversation($_POST['convIdList'], $userId);
                $message = OW::getLanguage()->text('mailbox', 'delete_message', array('count'=>$count));
                break;
        }

        exit(json_encode(array('count'=>$count, 'message'=>$message)));
    }

    public function mailbox_responder($params)
    {
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }
        $q = empty($_POST['q']) ? array() : UTIL_HtmlTag::stripTagsAndJs($_POST['q']);
        $userId = OW::getUser()->getId();

        $result = [];
        $convIds = [];
        $messageResults = MAILBOX_BOL_ConversationService::getInstance()->searchMessagesList($userId, $q);
        $avatarService = BOL_AvatarService::getInstance();

        foreach ($messageResults as $item){
            $opponentId = $item['senderId'];
            if($opponentId == $userId){
                $opponentId = $item['recipientId'];
            }
            $convId = $item['conversationId'];
            $convIds[] = $convId;
            $item['opponentId']=$opponentId;
            $item['avatarUrl']= BOL_AvatarService::getInstance()->getAvatarUrl($opponentId);
            $item['opponentUrl']= BOL_UserService::getInstance()->getUserUrl($opponentId);
            $item['opponentName']= BOL_UserService::getInstance()->getDisplayName($opponentId);
            $item['text'] = MAILBOX_BOL_ConversationService::getInstance()->json_decode_text($item['text']);
            $item['timeString'] = UTIL_DateTime::formatDate((int)$item['timeStamp'], true);
            $item['mode'] = MAILBOX_BOL_ConversationService::getInstance()->getConversationMode((int)$convId);
            if ($item['mode'] == 'chat') {
                $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }else {
                $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$convId));
            }
            array_push($result, $item);
        }
        $titleResults = MAILBOX_BOL_ConversationService::getInstance()->searchMailTopicList($userId, $q);
        foreach ($titleResults as $obj){
            $item = [];
            $opponentId = $obj->initiatorId;
            if($opponentId == $userId){
                $opponentId = $obj->interlocutorId;
            }
            $convId = $obj->id;
            if(in_array($convId, $convIds)){
                continue;
            }
            $item['opponentId']=$opponentId;
            $item['avatarUrl']= BOL_AvatarService::getInstance()->getAvatarUrl($opponentId);
            $item['opponentUrl']= BOL_UserService::getInstance()->getUserUrl($opponentId);
            $item['opponentName']= BOL_UserService::getInstance()->getDisplayName($opponentId);
            $item['text'] = $obj->subject;
            $item['timeString'] = UTIL_DateTime::formatDate((int)$item['lastMessageTimestamp'], true);
            $item['mode'] = MAILBOX_BOL_ConversationService::getInstance()->getConversationMode((int)$convId);
            if ($item['mode'] == 'chat') {
                $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_chat_conversation', array('userId'=>$opponentId));
            }else {
                $item['conversationUrl'] = OW::getRouter()->urlForRoute('mailbox_mail_conversation', array('convId'=>$convId));
            }
            array_push($result, $item);
        }

        $list=array("result"=>"ok", "q"=>$q, "results" => $result);
        echo json_encode($list);
        exit;
    }
}