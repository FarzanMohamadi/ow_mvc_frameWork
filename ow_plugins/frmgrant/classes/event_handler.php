<?php
class FRMGRANT_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    function onCollectAuthLabels(BASE_CLASS_EventCollector $event)
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmgrant' => array(
                    'label' => $language->text('frmgrant', 'auth_frmgrant_label'),
                    'actions' => array(
                        'manage-grant' => $language->text('frmgrant', 'auth_action_label_manage_grant')
                    )
                )
            )
        );
    }

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('admin.add_auth_labels', array($this, "onCollectAuthLabels"));
    }
}