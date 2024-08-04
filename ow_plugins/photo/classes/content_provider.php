<?php
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.classes
 * @since 1.7.2
 */
class PHOTO_CLASS_ContentProvider
{
    const ENTITY_TYPE = 'photo_comments';
    const CONTENT_GROUP = 'photo';

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
        $this->service = PHOTO_BOL_PhotoService::getInstance();
    }
    
    public function onCollectTypes( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            'pluginKey' => 'photo',
            'group' => self::CONTENT_GROUP,
            'groupLabel' => OW::getLanguage()->text('photo', 'content_group_label'),
            'entityType' => self::ENTITY_TYPE,
            'entityLabel' => OW::getLanguage()->text('photo', 'content_photo_label'),
            'displayFormat' => 'image_content'
        ));
    }
    
    public function onGetInfo( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params['entityType'] != self::ENTITY_TYPE )
        {
            return;
        }

        $photoDao = PHOTO_BOL_PhotoDao::getInstance();
        $route = OW::getRouter();
        $out = array();

        foreach ( $photoDao->getPhotoListByIdList($params['entityIds']) as $photo )
        {
            $info = array();

            $info['id'] = $photo['id'];
            $info['userId'] = $photo['userId'];
            $info['description'] = $photo['description'];
            $info['url'] = $route->urlForRoute('view_photo', array('id' => $photo['id']));
            $info['timeStamp'] = $photo['addDatetime'];
            $info['image'] = array(
                'thumbnail' => PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_SMALL, $photo),
                'preview' => PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_PREVIEW, $photo),
                'view' => PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_MAIN, $photo),
                'fullsize' => PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_FULLSCREEN, $photo)
            );

            $dimension = json_decode($photo['dimension'], true);
            $info['dimension'] = array(
                'thumbnail' => $dimension['small'],
                'preview' => $dimension['preview'],
                'view' => $dimension['main']
            );

            if ( !empty($dimension['fullscreen']) )
            {
                $info['dimension']['fullsize'] = $dimension['fullscreen'];
            }
            
            $out[$photo['id']] = $info;
        }
                
        $event->setData($out);
        
        return $out;
    }
    
    public function onUpdateInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        
        if ( $params['entityType'] != self::ENTITY_TYPE )
        {
            return;
        }

        foreach ( $data as $photoId => $info )
        {
            $status = $info['status'] == BOL_ContentService::STATUS_APPROVAL ? PHOTO_BOL_PhotoDao::STATUS_APPROVAL : PHOTO_BOL_PhotoDao::STATUS_APPROVED;

            $photo = $this->service->findPhotoById($photoId);
            $photo->status = $status;

            PHOTO_BOL_PhotoDao::getInstance()->save($photo);

            OW::getEventManager()->trigger(new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_CONTENT_UPDATE, array(
                'id' => $photoId
            )));
        }
    }

    public function onDelete( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( $params['entityType'] != self::ENTITY_TYPE )
        {
            return;
        }
        
        foreach ( $params['entityIds'] as $photoId )
        {
            $this->service->deletePhoto($photoId);
        }
    }

    // Photo events

    public function onBeforePhotoDelete( OW_Event $event )
    {
        $params = $event->getParams();

        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_BEFORE_DELETE, array(
            'entityType' => self::ENTITY_TYPE,
            'entityId' => $params['id']
        )));
    }
    
    public function onAfterPhotoAdd( OW_Event $event )
    {
        foreach ( $event->getParams() as $photo )
        {
            OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_ADD, array(
                'entityType' => self::ENTITY_TYPE,
                'entityId' => $photo['photoId'],
                'silent' => !empty($photo["silent"])
            ), array(
                'string' => array('key' => 'photo+content_add_string')
            )));
        }
    }
    
    public function onAfterPhotoEdit( OW_Event $event )
    {
        $params = $event->getParams();

        OW::getEventManager()->trigger(new OW_Event(BOL_ContentService::EVENT_AFTER_CHANGE, array(
            'entityType' => self::ENTITY_TYPE,
            'entityId' => $params['id']
        ), array(
            'string' => array('key' => 'photo+content_edited_string')
        )));
    }

    public function init()
    {
        OW::getEventManager()->bind(PHOTO_CLASS_EventHandler::EVENT_BEFORE_PHOTO_DELETE, array($this, 'onBeforePhotoDelete'));
        OW::getEventManager()->bind(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_ADD, array($this, 'onAfterPhotoAdd'));
        OW::getEventManager()->bind(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_EDIT, array($this, 'onAfterPhotoEdit'));
        
        OW::getEventManager()->bind(BOL_ContentService::EVENT_COLLECT_TYPES, array($this, 'onCollectTypes'));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_GET_INFO, array($this, 'onGetInfo'));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_UPDATE_INFO, array($this, 'onUpdateInfo'));
        OW::getEventManager()->bind(BOL_ContentService::EVENT_DELETE, array($this, 'onDelete'));

    }
}
