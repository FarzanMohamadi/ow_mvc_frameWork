<?php
class FRMGROUPSRSS_CLASS_EventHandler
{
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMJCSE_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    public function genericInit()
    {
        $service = FRMGROUPSRSS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('add.group.setting.elements', array($service, 'addGroupSettingElements'));
        $eventManager->bind($service::SET_RSS_FOR_GROUP_ON_CREATE, array($service, 'setRssForGroupOnCreate'));
        $eventManager->bind($service::SET_RSS_FOR_GROUP_ON_EDIT, array($service, 'setRssForGroupOnEdit'));
        $eventManager->bind($service::GET_RSS_LINKS_FOR_GROUP, array($service, 'getGroupRssLinksEvent'));
        $eventManager->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
        $eventManager->bind('update.notifierId.group.status.notification', array($service, "updateNotifierIdCronRss"));
    }


    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmgroupsrss' => array(
                    'label' => $language->text('frmgroupsrss', 'auth_frmgroupsrss_label'),
                    'actions' => array(
                        'add' => $language->text('frmgroupsrss', 'auth_action_label_add')
                    )
                )
            )
        );
    }
}
