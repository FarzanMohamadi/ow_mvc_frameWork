<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.bol
 * @since 1.0
 */
class FRMGROUPSINVITATIONLINK_MCLASS_EventHandler
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
        $service = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('groups_user_signed', array($service, 'checkIfUserRegisteredByLink'));
        $eventManager->bind('groups_user_left', array($service, 'removeUserJoinByLink'));
        $eventManager->bind($service::GO_TO_DEEP_LINK, array($service, 'getMobileLinkRedirectForGroup'));
        $eventManager->bind($service::GET_INVITATION_LINKS_FOR_GROUP, array($service, 'getGroupInvitationLinksEvent'));
    }

}