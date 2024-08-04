<?php
class FRMREPORT_MCLASS_EventHandler{
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

    public function init(){
        if( !FRMSecurityProvider::checkPluginActive('groups', true) ){
            return;
        }

        $eventManager = OW::getEventManager();
        $eventManager->bind('base.add_main_console_item', array($this, 'addConsoleItem'));
        $eventManager->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
        $service = FRMREPORT_BOL_Service::getInstance();
        $eventManager->bind('frm.add.group.category.filter.element',array($service,'onGroupCategoryElementAdded'));
        $eventManager->bind('frm.add.category.to.group',array($service,'onGroupCreated'));
        $eventManager->bind('frmgroupsplus.add.file.widget', array($service, 'addReportWidget'));
        $eventManager->bind('groups_group_delete_complete',array($service,'onGroupDeleted'));
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmreport' => array(
                    'label' => $language->text('frmreport', 'auth_frmreport_label')
                )
            )
        );
    }
    public function addConsoleItem( BASE_CLASS_EventCollector $event )
    {
        if(OW::getUser()->isAuthorized('frmreport')){
            $event->add(array('label' => OW::getLanguage()->text('frmreport', 'console_reports_link'), 'url' => OW_Router::getInstance()->urlForRoute('report_overall')));

        }
    }


}