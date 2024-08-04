<?php
class FRMEMAILCONTROLLER_CLASS_EventHandler
{

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

    public function init()
    {
        $service = FRMEMAILCONTROLLER_BOL_Service::getInstance();
        OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_JOIN_FORM_RENDER, array($service, 'onBeforeJoinFormRender'));
        OW::getEventManager()->bind(FRMEventManager::DISTINGUISH_REQUIRED_FIELD, array($service, 'checkEmailFields'));
    }

}
