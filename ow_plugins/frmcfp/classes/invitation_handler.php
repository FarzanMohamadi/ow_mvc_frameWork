<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.classes
 * @since 1.0
 */
class FRMCFP_CLASS_InvitationHandler
{
    const  INVITATION_JOIN = 'frmcfp-join';

    /**
     * Singleton instance.
     *
     * @var FRMCFP_CLASS_InvitationHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMCFP_CLASS_InvitationHandler
     */
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

    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != self::INVITATION_JOIN )
        {
            return;
        }

        $eventId = (int) $params['entityId'];
        $data = $params['data'];

        $itemKey = $params['key'];
        
        $language = OW::getLanguage();

        $data['toolbar'] = array(
            array(
                'label' => $language->text('frmcfp', 'accept_request'),
                'id'=> 'toolbar_accept_' . $itemKey
            ),
            array(
                'label' => $language->text('frmcfp', 'ignore_request'),
                'id'=> 'toolbar_ignore_' . $itemKey
            )
        );

        $event->setData($data);

        $jsData = array(
            'eventId' => $eventId,
            'itemKey' => $itemKey
        );

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#toolbar_ignore_$itemKey", 'click',
                'OW.Invitation.send("events.ignore", e.data.eventId).removeItem(e.data.itemKey);',
        array('e'), $jsData);

        $js->jQueryEvent("#toolbar_accept_$itemKey", 'click',
                'OW.Invitation.send("events.accept", e.data.eventId);
                 $("#toolbar_ignore_" + e.data.itemKey).hide();
                 $("#toolbar_accept_" + e.data.itemKey).hide();',
        array('e'), $jsData);

        OW::getDocument()->addOnloadScript($js->generateJs());
    }

    public function onEventDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $eventId = $params['eventId'];

        OW::getEventManager()->call('invitations.remove', array(
            'entityType' => 'frmcfp',
            'entityId' => $eventId
        ));
        
        OW::getEventManager()->call('invitations.remove', array(
            'entityType' => self::INVITATION_JOIN,
            'entityId' => $eventId
        ));  
        
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'frmcfp',
            'entityId' => $eventId
        ));
    }

    public function onCommand( OW_Event $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return 'auth faild';
        }

        $params = $event->getParams();

        if ( !in_array($params['command'], array('events.accept', 'events.ignore')) )
        {
            return 'wrong command';
        }

        $eventId = $params['data'];
        $eventDto = FRMCFP_BOL_Service::getInstance()->findEvent($eventId);

        $userId = OW::getUser()->getId();
        $jsResponse = UTIL_JsGenerator::newInstance();

        if ( empty($eventDto) )
        {
            BOL_InvitationService::getInstance()->deleteInvitation(self::INVITATION_JOIN, $eventId, $userId);
            return 'empty Event Id';
        }

        $event->setData($jsResponse);
    }

    public function init()
    {
        OW::getEventManager()->bind('invitations.on_item_render', array($this, 'onItemRender'));
        OW::getEventManager()->bind(FRMCFP_BOL_Service::EVENT_ON_DELETE_EVENT, array($this, 'onEventDelete'));

        OW::getEventManager()->bind('invitations.on_command', array($this, 'onCommand'));
    }
}