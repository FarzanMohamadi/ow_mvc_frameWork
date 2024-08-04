<?php
class FRMMASSMAILING_CLASS_EventHandler
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
    
    private $service;
    
    private function __construct()
    {
        $this->service = FRMMASSMAILING_BOL_Service::getInstance();
    }
    
    public function init()
    {
        $service = FRMMASSMAILING_BOL_Service::getInstance();
        OW::getEventManager()->bind(FRMMASSMAILING_BOL_Service::ON_SEND_MASS_MAIL, array( $service, "onSendMassMail"));
    }
}