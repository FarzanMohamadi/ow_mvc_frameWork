<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsmtpcheck
 * @since 1.0
 */

class FRMSMTPCHECK_CLASS_EventHandler
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
    public function init()
    {
        $eventManager = OW::getEventManager();
        $service = FRMSMTPCHECK_BOL_Service::getInstance();
        $eventManager->bind('smtp.disable.tls', array($service,'SmtpDisableTLS'));
        $eventManager->bind('base_before_email_create', array($service,'beforeEmailCreate'));
    }

}