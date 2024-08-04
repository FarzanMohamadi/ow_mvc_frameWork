<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.components
 * @since 1.0
 * */

class MAILBOX_CMP_ConsoleMailbox extends BASE_CMP_ConsoleDropdownClick
{
    protected $counter = array(
        'number' => 0,
        'active' => false
    );

    /**
     *
     * @var BASE_CMP_ConsoleList
     */
    protected $list;

    public function __construct()
    {
        parent::__construct( OW::getLanguage()->text('mailbox', 'messages_console_title'), 'mailbox' );

        $template = OW::getPluginManager()->getPlugin('BASE')->getCmpViewDir() . 'console_dropdown_list.html';
        $this->setTemplate($template);

        $this->list = new MAILBOX_CMP_ConsoleList($this->getKey());
        $this->addClass('ow_mailbox_items_list');

        $modeList = MAILBOX_BOL_ConversationService::getInstance()->getActiveModeList();
        $singleMode = count($modeList) == 1;
        if ($singleMode)
        {
            $this->addClass('ow_mailbox_single_mode');
        }

        $toolbar = new MAILBOX_CMP_Toolbar();
        OW::getDocument()->appendBody($toolbar->render());
    }

    protected function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->addScript('OW.Console.addItem(new OW_ConsoleDropdownList({$uniqId}, {$contentIniqId}), {$key});', array(
            'uniqId' => $this->consoleItem->getUniqId(),
            'key' => $this->getKey(),
            'contentIniqId' => $this->consoleItem->getContentUniqId()
        ));
        OW::getDocument()->addOnloadScript($js);
        $this->list->initJs();

        $js = UTIL_JsGenerator::newInstance();

        $js->addScript(
            'OW.MailboxConsole = new OW_MailboxConsole({$key}, {$params});',
            array(
                'key' => $this->getKey(),
                'params' => array(
                    'issetMails' => (boolean) MAILBOX_BOL_ConversationService::getInstance()->getInboxConversationList(OW::getUser()->getId(), 0, 1)
                )
        ));

        OW::getDocument()->addOnloadScript($js);

        return $this->consoleItem->getUniqId();
    }

    public function setCounter( $number, $active = true )
    {
        $this->counter['number'] = $number;
        $this->counter['active'] = $active;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('counter', $this->counter);
        $this->setContent($this->list->render());
    }
}