<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmftpmanager.bol
 * @since 1.0
 */

class FRMFTPMANAGER_CLASS_EventHandler
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
        $service = FRMFTPMANAGER_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('base.check_ftp_not_exist', array($service, 'isFtpNotExist'));
        $eventManager->bind('base.get_ftp_attrs', array($service, 'getFtpAttrs'));
        $eventManager->bind('base.save_ftp_attr', array($service, 'saveFtpAttrs'));
        $eventManager->bind('base.on_before_ftp_handle', array($service, 'onBeforeFtp'));
        $eventManager->bind('base.before_get_ftp_connection', array($service, 'onBeforeGetFtpConnection'));
    }
}