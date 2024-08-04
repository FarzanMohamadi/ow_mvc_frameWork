<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmterms.classes
 * @since 1.0
 */
class FRMTERMS_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMTERMS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTERMS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var FRMTERMS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = FRMTERMS_BOL_Service::getInstance();
    }
    
    public function genericInit()
    {
        $service = FRMTERMS_BOL_Service::getInstance();
        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'on_notify_actions'));
        OW::getEventManager()->bind(FRMEventManager::ON_RENDER_JOIN_FORM, array($service, 'on_render_join_form'));
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        /*
         * to show terms in join form when subscription can be done only by invitation
         */
        OW::getEventManager()->bind('base.members_only_exceptions', array($this, 'onAddMembersOnlyException'));
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_MCTRL_AjaxLoader', 'action' => 'component'));
    }
    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();
        if ( $params['pluginKey'] != 'frmterms' || $params['entityType'] != 'frmterms-terms' )
        {
            return;
        }

        if(!isset($params['data']['string']['vars']['value1']) || !isset($params['data']['string']['vars']['value2']) ||
        !isset($params['data']['url']))
        {
            return;
        }
        else{
            $title =$params['data']['string']['vars']['value1'];
            $size = $params['data']['string']['vars']['value2'];
        }
        $langVars = array(
            'value1' => $title,
            'url' => $params['data']['url'],
            'value2' => $size
        );

        $data['string'] = array('key' => 'frmterms+mobile_notification_content', 'vars' => $langVars);

        //Notification on click logic is set here
        $event = new OW_Event('mobile.notification.data.received', array('pluginKey' => $params['pluginKey'],
            'entityType' => $params['entityType'],
            'data' => $data));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['url'])){
            $data['url']=$event->getData()['url'];
        }

        $e->setData($data);



    }

}