<?php
class MAILBOX_CMP_ConsoleList extends OW_Component
{
    protected $viewAll = null, $itemKey, $listRsp;


    public function __construct( $consoleItemKey )
    {
        parent::__construct();

        $this->itemKey = $consoleItemKey;
        $this->listRsp = OW::getRouter()->urlFor('BASE_CTRL_Console', 'listRsp');
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::composeJsString('$.extend(OW.Console.getItem({$key}), OW_ConsoleList).construct({$params});', array(
            'key' => $this->itemKey,
            'params' => array(
                'rsp' => $this->listRsp,
                'key' => $this->itemKey
            )
        ));

        OW::getDocument()->addOnloadScript($js);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $viewAllUrl = OW::getRouter()->urlForRoute('mailbox_messages_default');
        $this->assign('viewAllUrl', $viewAllUrl);

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $activeModeList = $conversationService->getActiveModeList();
        $mailModeEnabled = (in_array('mail', $activeModeList)) ? true : false;
        $this->assign('mailModeEnabled', $mailModeEnabled);


        $event = new OW_Event('mailbox.show_send_message_button', array(), false);
        OW::getEventManager()->trigger($event);

        $isAuthorizedSendMessage = true;
        $this->assign('isAuthorizedSendMessage', $isAuthorizedSendMessage);
    }
}