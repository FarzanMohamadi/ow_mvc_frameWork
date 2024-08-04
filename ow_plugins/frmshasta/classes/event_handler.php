<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmshasta.bol
 * @since 1.0
 */
class FRMSHASTA_CLASS_EventHandler
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
        $service = FRMSHASTA_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_BEFORE_JOIN_FORM_RENDER, array($service, 'onBeforeJoinFormRender'));
        $eventManager->bind('on_before_complete_profile_form_render', array($service, 'onBeforeCompleteProfileFormRender'));
        $eventManager->bind('before.get.question.values', array($service, 'beforeGetQuestionValues'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'onBeforeDocumentRenderer'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegister'));
        $eventManager->bind('base.questions_save_data', array($service, 'onUserRegister'), 1);
        $eventManager->bind('notifications.collect_actions', array($service, 'notificationActions'));
        $eventManager->bind('admin.add_auth_labels', array($service, 'onCollectAuthLabels'));
    }
}