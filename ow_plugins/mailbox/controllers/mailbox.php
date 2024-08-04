<?php
/**
 * Mailbox controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugin.mailbox.controllers
 * @since 1.0
 */
class MAILBOX_CTRL_Mailbox extends OW_ActionController
{
    /**
     * @var string
     */
    public $responderUrl;

    /**
     * @see OW_ActionController::init()
     *
     */
    public function init()
    {
        parent::init();

        $language = OW::getLanguage();

        $this->setPageHeading($language->text('mailbox', 'mailbox'));
        $this->setPageHeadingIconClass('ow_ic_mail');
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->responderUrl = OW::getRouter()->urlFor("MAILBOX_CTRL_Mailbox", "responder");
    }

    /**
     * Action for mailbox ajax responder
     */
    public function responder()
    {
        if ( empty($_POST["function_"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $function = (string) $_POST["function_"];

        $responder = new MAILBOX_CLASS_Responder();
        $result = call_user_func(array($responder, $function), $_POST);

        echo json_encode(array('result' => $result, 'error' => $responder->error, 'notice' => $responder->notice));
        exit();
    }

    public function users( $params )
    {
        if(!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }

        header('Content-Type: text/plain');

        if (!OW::getUser()->isAuthenticated())
        {
            exit( json_encode(array()) );
        }

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $data = $conversationService->getUserList(OW::getUser()->getId());

        exit( base64_encode(json_encode($data['list'])) );
    }

    public function convs( $params )
    {
        if(!OW::getRequest()->isAjax()){
            throw new Redirect404Exception();
        }

        header('Content-Type: text/plain');

        if (!OW::getUser()->isAuthenticated())
        {
            exit( json_encode(array()) );
        }

        $conversationService = MAILBOX_BOL_ConversationService::getInstance();
        $data = $conversationService->getConversationListByUserId(OW::getUser()->getId());

        exit( base64_encode(json_encode($data)) );
    }

    public function testapi($params)
    {
        $commands = array(
            array(
                'name'=>'mailbox_api_ping',
                'params'=>array(
                    'lastRequestTimestamp'=>0
                )
            )
        );

        $commandsResult = array();
        foreach ($commands as $command)
        {
//            pv($command);
            $event = new OW_Event('base.ping' . '.' . trim($command["name"]), $command["params"]);
            OW::getEventManager()->trigger($event);

            $event = new OW_Event('base.ping', array(
                "command" => $command["name"],
                "params" => $command["params"]
            ), $event->getData());
            OW::getEventManager()->trigger($event);

            $commandsResult[] = array(
                'name' => $command["name"],
                'data' => $event->getData()
            );
        }

//        pv($commandsResult);

        exit('end');
    }
}