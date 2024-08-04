<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmeventplus.bol
 * @since 1.0
 */
class FRMEVENTPLUS_CLASS_EventHandler
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
        if( !FRMSecurityProvider::checkPluginActive('event', true)){
            return;
        }
        $service = FRMEVENTPLUS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ADD_LIST_TYPE_TO_EVENT, array($service, 'addListTypeToEvent'));
        $eventManager->bind(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_EVENT, array($service, 'getResultForListItemEvent'));
        $eventManager->bind(FRMEventManager::SET_TITLE_HEADER_LIST_ITEM_EVENT, array($service, 'setTitleHeaderListItemEvent'));
        $eventManager->bind(FRMEventManager::ADD_EVENT_FILTER_FORM, array($service, 'addEventFilterForm'));
        $eventManager->bind(FRMEventManager::ADD_LEAVE_BUTTON, array($service, 'addLeaveButton'));
        $eventManager->bind(FRMEventManager::ADD_CATEGORY_FILTER_ELEMENT, array($service, 'addCategoryFilterElement'));
        $eventManager->bind(FRMEventManager::GET_EVENT_SELECTED_CATEGORY_ID, array($service, 'getEventSelectedCategoryId'));
        $eventManager->bind(FRMEventManager::ADD_CATEGORY_TO_EVENT, array($service, 'addCategoryToEvent'));
        $eventManager->bind(FRMEventManager::GET_EVENT_SELECTED_CATEGORY_LABEL, array($service, 'getEventSelectedCategoryLabel'));
        $eventManager->bind(FRMEVENTPLUS_BOL_Service::ADD_FILTER_PARAMETERS_TO_PAGING, array($service, "addFilterParametersToPaging"));
        $eventManager->bind(FRMEVENTPLUS_BOL_Service::CHECK_IF_EVENTPLUS_IS_ACTIVE , array($service, "checkIfEventPlusIsActive"));
        $eventManager->bind(FRMEVENTPLUS_BOL_Service::DELETE_FILES, array($service, 'deleteFiles'));
        $eventManager->bind('notifications.collect_actions', array($service, 'onCollectNotificationActions'));

        $eventManager->bind('event.invite_user',array($service,'onInviteUser'));
        $eventManager->bind('notifications.on_item_render', array($service, 'onNotificationRender'));
        OW::getEventManager()->bind('feed.on_item_render', array($service,'feedOnItemRender'));

    }
}