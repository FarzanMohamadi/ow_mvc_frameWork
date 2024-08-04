<?php
class FRMRSS_MCLASS_EventHandler
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

    private function __construct() { }


    public function init()
    {
        $service = FRMRSS_BOL_Service::getInstance();
        OW::getEventManager()->bind(FRMRSS_BOL_Service::ADD_RSS_COMPONENT, array($service, "addRssComponent"));
    }
}