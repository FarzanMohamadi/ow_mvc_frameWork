<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.classes
 * @since 1.0
 */
class FRMCFP_CLASS_ContentProvider
{
    const ENTITY_TYPE = 'cfp-feed';
    
    /**
     * Singleton instance.
     * 
     * @var FRMCFP_CLASS_ContentProvider
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMCFP_CLASS_ContentProvider
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var FRMCFP_BOL_Service
     */
    private $service;
    
    private function __construct()
    {
        $this->service = FRMCFP_BOL_Service::getInstance();
    }
    
    public function onCollectTypes( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "pluginKey" => "frmcfp",
            "group" => "frmcfp",
            "entityType" => self::ENTITY_TYPE,
            
            "groupLabel" => OW::getLanguage()->text("frmcfp", "content_events_label"),
            "entityLabel" => OW::getLanguage()->text("frmcfp", "content_event_label"),
            "displayFormat" => "image_content"
        ));
    }
    
    public function onGetInfo( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != self::ENTITY_TYPE )
        {
            return;
        }
        
        if ( empty($params["entityIds"]) )
        {
            return array();
        }
        
        $events = $this->service->findByIdList($params["entityIds"]);
        $out = array();
        
        /*@var $eventDto FRMCFP_BOL_Event */
        foreach ( $events as $eventDto )
        {
            $info = array();

            $info["id"] = $eventDto->id;
            $info["userId"] = $eventDto->userId;

            $info["title"] = $eventDto->title;
            $info["description"] = $eventDto->description;
            $info["url"] = $this->service->getEventUrl($eventDto->id);
            $info["timeStamp"] = $eventDto->createTimeStamp;
            $info["startStamp"] = $eventDto->startTimeStamp;
            $info["endStamp"] = $eventDto->endTimeStamp;

            $info["image"] = array(
                "thumbnail" => $eventDto->getImage() ? $this->service->generateImageUrl($eventDto->getImage(), true) : $this->service->generateDefaultImageUrl(),
                "preview" => $eventDto->getImage() ? $this->service->generateImageUrl($eventDto->getImage(), false) : null,
            );
            
            $out[$eventDto->id] = $info;
        }
                
        $event->setData($out);
        
        return $out;
    }
    
    public function onUpdateInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( $params["entityType"] != self::ENTITY_TYPE )
        {
            return;
        }
        
        foreach ( $data as $eventId => $info )
        {
            $status = 0;
            switch ( $info['status'] )
            {
                case BOL_ContentService::STATUS_ACTIVE:
                    $status = FRMCFP_BOL_Service::MODERATION_STATUS_ACTIVE;
                    break;
                case BOL_ContentService::STATUS_APPROVAL:
                    $status = FRMCFP_BOL_Service::MODERATION_STATUS_APPROVAL;
                    break;
                case BOL_ContentService::STATUS_SUSPENDED:
                    $status = FRMCFP_BOL_Service::MODERATION_STATUS_SUSPENDED;
                    break;
            }
            
            $eventDto = FRMCFP_BOL_Service::getInstance()->findEvent($eventId);
            
            if ( !empty($eventDto) )
            {
                $eventDto->status = $status;
                
                FRMCFP_BOL_Service::getInstance()->saveEvent($eventDto);
            }
        }
    }
    
    public function onDelete( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params["entityType"] != self::ENTITY_TYPE )
        {
            return;
        }
        
        foreach ( $params["entityIds"] as $eventId )
        {
            $this->service->deleteEvent($eventId);
        }
    }
    
    public function onBeforeDelete( OW_Event $event )
    {
        $params = $event->getParams();
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            "entityType" => self::ENTITY_TYPE,
            "entityId" => $params["eventId"]
        )));
    }
    
    public function onAfterAdd( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( !empty($params["eventDto"]) )
        {
            OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_ADD, array(
                "entityType" => self::ENTITY_TYPE,
                "entityId" => $params["eventDto"]->id
            ), array(
                "string" => array("key" => "frmcfp+event_create_string")
            )));
        }
    }
    
    public function onAfterEdit( OW_Event $event )
    {
        $params = $event->getParams();
        
        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_CHANGE, array(
            "entityType" => self::ENTITY_TYPE,
            "entityId" => $params["eventId"]
        ), array(
            "string" => array("key" => "frmcfp+event_edited_string")
        )));
    }
    
    public function init()
    {
        OW::getEventManager()->bind(FRMCFP_BOL_Service::EVENT_ON_DELETE_EVENT, array($this, "onBeforeDelete"));
        OW::getEventManager()->bind(FRMCFP_BOL_Service::EVENT_AFTER_CREATE_EVENT, array($this, "onAfterAdd"), 999999999999);
        OW::getEventManager()->bind(FRMCFP_BOL_Service::EVENT_AFTER_EVENT_EDIT, array($this, "onAfterEdit"), 999999999999);
        
        OW::getEventManager()->bind(BOL_ContentService::EVENT_COLLECT_TYPES, array($this, "onCollectTypes"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_GET_INFO, array($this, "onGetInfo"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_UPDATE_INFO, array($this, "onUpdateInfo"));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_DELETE, array($this, "onDelete"));
    }
}
