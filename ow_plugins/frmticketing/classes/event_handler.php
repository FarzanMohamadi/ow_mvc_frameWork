<?php
class FRMTICKETING_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    public function init()
    {
        $service = FRMTICKETING_BOL_TicketService::getInstance();
        $eventManager=OW::getEventManager();
        $eventManager->bind('admin.add_auth_labels', array($this, 'onCollectAuthLabels'));
        $eventManager->bind('base.add_main_console_item', array($this, 'addConsoleItem'));
        $eventManager->bind('ticket.validate.category.data',array($service,'validateCategoryData'));
        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmticketing' => array(
                    'label' => $language->text('frmticketing', 'auth_group_label'),
                    'actions' => array(
                        'view_tickets' => $language->text('frmticketing', 'auth_action_label_view_tickets')
                    )
                )
            )
        );
    }

    function addConsoleItem( BASE_CLASS_EventCollector $event )
    {

        $isTicketmanager = OW::getUser()->isAuthorized('frmticketing','view_tickets');
        if($isTicketmanager || OW::getUser()->isAuthenticated())
        {
            $label=OW::getLanguage()->text('frmticketing','my_tickets_label');
            if($isTicketmanager)
            {
                $label=OW::getLanguage()->text('frmticketing', 'view_tickets_label');
            }
            $event->add(array('label' =>$label , 'url' => OW_Router::getInstance()->urlForRoute('frmticketing.view_tickets')));
        }
    }


    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'frmticketing',
            'action' => 'receive-ticket-update',
            'sectionIcon' => 'ow_ic_calendar',
            'sectionLabel' => OW::getLanguage()->text('frmticketing', 'notifications_section_label'),
            'description' => OW::getLanguage()->text('frmticketing', 'notifications_new_message'),
            'selected' => true
        ));
    }

}