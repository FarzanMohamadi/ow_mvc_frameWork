<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmclamav
 * @since 1.0
 */

class FRMCLAMAV_CLASS_EventHandler
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
        $service = FRMCLAMAV_BOL_Service::getInstance();
        $eventManager->bind('frmclamav.is_file_clean', array($service, 'isFileClean'));
        $eventManager->bind(FRMEventManager::ON_AFTER_UPDATE_STATUS_FORM_RENDERER, array($service, 'addClamavStaticFiles'));
        $eventManager->bind('frmclamav.add.file.upload.validator', array($service, 'addFileUploadValidator'));
        $eventManager->bind('admin.add_auth_labels', array($service, 'onCollectAuthLabels'));
    }

}