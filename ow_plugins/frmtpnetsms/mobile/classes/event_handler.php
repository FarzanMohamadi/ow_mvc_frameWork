<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

class FRMTPNETSMS_MCLASS_EventHandler
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
        $service = FRMTPNETSMS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmsms.send_sms', array($service, 'sendSMS'));
        $eventManager->bind('frmsms.sms_provider_setting_is_complete', array($service, 'SMSProviderSettingIsComplete'));
        //$eventManager->bind('frmsms.get_credit', array($service, 'getCredit'));
    }

}