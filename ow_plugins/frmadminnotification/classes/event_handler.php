<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadminnotification.bol
 * @since 1.0
 */
class FRMADMINNOTIFICATION_CLASS_EventHandler
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
        $service = FRMADMINNOTIFICATION_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($service, 'onUserRegistered'));
        $eventManager->bind('forum.topic_add', array($service, 'onTopicForumAdd'));
        $eventManager->bind('forum.add_post', array($service, 'onPostTopicForumAdd'));
        $eventManager->bind('base_add_comment', array($service, 'onCommentAdd'));
    }

}