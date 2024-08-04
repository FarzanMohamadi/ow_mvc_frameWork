<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.controllers
 * @since 1.6.1
 * */
class MAILBOX_CTRL_Messages extends OW_ActionController
{
    public function index( $params )
    {
        if (!OW::getUser()->isAuthenticated())
        {
            throw new AuthenticateException();
        }
        OW::getDocument()->addOnloadScript("add_mailbox_search_content_events('".OW::getRouter()->urlForRoute('mailbox_search_content.mailbox_responder')."')");

        $language = OW::getLanguage();
        $language->addKeyForJs('mailbox', 'results_for');

        $this->setPageHeading(OW::getLanguage()->text('mailbox', 'page_heading_messages'));

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        $listParams = array();
        if (!empty($params['convId']))
        {
            $listParams['conversationId'] = $params['convId'];

            $conversation = $conversationService->getConversation($params['convId']);
            if (empty($conversation))
            {
                throw new Redirect404Exception();
            }

            /*$conversationMode = $conversationService->getConversationMode($params['convId']);
            if ($conversationMode != 'mail')
            {
                throw new Redirect404Exception();
            }*/
        }

        $listParams['activeModeList'] = $conversationService->getActiveModeList();

        //Conversation list
        $conversationList = new MAILBOX_CMP_ConversationList($listParams);
        $this->addComponent('conversationList', $conversationList);

        $conversationContainer = new MAILBOX_CMP_Conversation();
        $this->addComponent('conversationContainer', $conversationContainer);

        $activeModeList = $conversationService->getActiveModeList();
        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $this->assign('mailModeEnabled', $mailModeEnabled);


        $event = new OW_Event('mailbox.show_send_message_button', array(), false);
        OW::getEventManager()->trigger($event);

        $isAuthorizedSendMessage = true;
        $this->assign('isAuthorizedSendMessage', $isAuthorizedSendMessage);

        $chatModeEnabled = (in_array('chat', $activeModeList)) ? true : false;
        $this->assign('chatModeEnabled', $chatModeEnabled);

        $this->setDocumentKey("messages_index");
    }

    public function chatConversation( $params ){
        $this->redirect(OW::getRouter()->urlForRoute('mailbox_messages_default'));
    }

    public function conversation($params)
    {
//        pv($_REQUEST);

        exit('1');
    }

    public function conversations($params)
    {
        if (!OW::getUser()->isAuthenticated())
        {
            exit(array());
        }

        $userId = OW::getUser()->getId();
        $conversationService = MAILBOX_BOL_ConversationService::getInstance();

        if ($_SERVER['REQUEST_METHOD'] == 'GET'){
            $list = $conversationService->getConversationListByUserId($userId);
            exit(json_encode($list));
        }
        else
        {
            exit(json_encode('todo'));
        }
    }
}