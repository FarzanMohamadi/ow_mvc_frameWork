<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.bol
 * @since 1.0
 */
final class FRMCFP_BOL_Service
{
    const USER_STATUS_YES = FRMCFP_BOL_EventUserDao::VALUE_STATUS_YES;
    const USER_STATUS_MAYBE = FRMCFP_BOL_EventUserDao::VALUE_STATUS_MAYBE;
    const USER_STATUS_NO = FRMCFP_BOL_EventUserDao::VALUE_STATUS_NO;

    const CAN_VIEW_ANYBODY = FRMCFP_BOL_EventDao::VALUE_WHO_CAN_VIEW_ANYBODY;
    const CAN_VIEW_INVITATION_ONLY = FRMCFP_BOL_EventDao::VALUE_WHO_CAN_VIEW_INVITATION_ONLY;

    const CONF_EVENT_USERS_COUNT = 'event_users_count';
    const CONF_EVENT_USERS_COUNT_ON_PAGE = 'event_users_count_on_page';
    const CONF_EVENTS_COUNT_ON_PAGE = 'events_count_on_page';
    const CONF_WIDGET_EVENTS_COUNT = 'events_widget_count';
    const CONF_WIDGET_EVENTS_COUNT_OPTION_LIST = 'events_widget_count_select_set';
    const CONF_DASH_WIDGET_EVENTS_COUNT = 'events_dash_widget_count';

    const EVENT_AFTER_EVENT_EDIT = 'frmcfp_after_event_edit';
    const EVENT_ON_DELETE_EVENT = 'frmcfp_on_delete_event';
    const EVENT_ON_CREATE_EVENT = 'frmcfp_on_create_event';
    const EVENT_ON_CHANGE_USER_STATUS = 'frmcfp_on_change_user_status';
    const EVENT_AFTER_CREATE_EVENT = 'frmcfp_after_create_event';

    const EVENT_BEFORE_EVENT_CREATE = 'frmcfp.before_event_create';
    const EVENT_BEFORE_EVENT_EDIT = 'frmcfp.before_event_edit';
    const EVENT_COLLECT_TOOLBAR = 'frmcfp.collect_toolbar';

    const EVENT_CLEAR_INVITATIONS_INCOMPLETE = 'frmcfp.clear_invitations_incomplete';
    const ON_BEFORE_EVENT_VIEW_RENDER = 'frm.on.before.cfp.view.render';

    const EVENT_DELETE_FILES = 'frmcfp.delete.files';
    const EVENT_ADD_FILE_WIDGET = 'frmcfp.add.file.widget';

    const MODERATION_STATUS_ACTIVE = 1;
    const MODERATION_STATUS_APPROVAL= 2;
    const MODERATION_STATUS_SUSPENDED = 3;

    /**
     * @var array
     */
    private $configs = array();
    /**
     * @var FRMCFP_BOL_EventDao
     */
    private $eventDao;
    /**
     * @var FRMCFP_BOL_EventUserDao
     */
    private $eventUserDao;
    /**
     * @var FRMCFP_BOL_EventFilesDao
     */
    private $eventFileDao;

    /**
     * Singleton instance.
     *
     * @var FRMCFP_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMCFP_BOL_Service
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
     * Constructor.
     */
    private function __construct()
    {
        $this->eventDao = FRMCFP_BOL_EventDao::getInstance();
        $this->eventUserDao = FRMCFP_BOL_EventUserDao::getInstance();
        $this->eventFileDao = FRMCFP_BOL_EventFilesDao::getInstance();

        $this->configs[self::CONF_EVENT_USERS_COUNT] = 10;
        $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE] = 15;
        $this->configs[self::CONF_DASH_WIDGET_EVENTS_COUNT] = 3;
        $this->configs[self::CONF_WIDGET_EVENTS_COUNT] = 3;
        $this->configs[self::CONF_EVENT_USERS_COUNT_ON_PAGE] = 30;
        $this->configs[self::CONF_WIDGET_EVENTS_COUNT_OPTION_LIST] = array(3 => 3, 5 => 5, 10 => 10, 15 => 15, 20 => 20);
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Saves event dto.
     *
     * @param FRMCFP_BOL_Event $event
     */
    public function saveEvent( FRMCFP_BOL_Event $event )
    {
        $this->eventDao->save($event);
    }

    public function createEvent($data){
        $language = OW::getLanguage();
        $userId = OW::getUser()->getId();
        $imgPath = $_FILES['image']['tmp_name'];
        $filePath = $_FILES['file']['tmp_name'];

        $serviceEvent = new OW_Event(FRMCFP_BOL_Service::EVENT_BEFORE_EVENT_CREATE, array(), $data);
        OW::getEventManager()->trigger($serviceEvent);
        $data = $serviceEvent->getData();

        $dateArray = explode('/', $data['start_date']);
        $startStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        if ( $data['start_time'] != 'all_day' )
        {
            $startStamp = mktime($data['start_time']['hour'], $data['start_time']['minute'], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        }

        $dateArray = explode('/', $data['end_date']);
        $endStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

        $endStamp = strtotime("+1 day", $endStamp);

        if ( $data['end_time'] != 'all_day' )
        {
            $hour = 0;
            $min = 0;

            if( $data['end_time'] != 'all_day' )
            {
                $hour = $data['end_time']['hour'];
                $min = $data['end_time']['minute'];
            }
            $dateArray = explode('/', $data['end_date']);
            $endStamp = mktime($hour, $min, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        }

        $imagePosted = false;
        if ( !empty($_FILES['image']['name']) )
        {
            if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
            {
                OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                return null;
            }
            else
            {
                $imagePosted = true;
            }
        }

        $filePosted = false;
        if ( !empty($_FILES['file']['name']) )
        {
            if ( $this->checkIfFileValid($_FILES['file']))
            {
                OW::getFeedback()->error($language->text('frmcfp', 'not_valid_file'));
                return null;
            }
            else
            {
                $filePosted = true;
            }
        }

        if ( empty($endStamp) )
        {
            $endStamp = strtotime("+1 day", $startStamp);
            $endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));
        }

        if ( !empty($endStamp) && $endStamp < $startStamp )
        {
            OW::getFeedback()->error($language->text('frmcfp', 'add_form_invalid_end_date_error_message'));
            return null;
        }

        $event = new FRMCFP_BOL_Event();
        $event->setStartTimeStamp($startStamp);
        $event->setEndTimeStamp($endStamp);
        $event->setCreateTimeStamp(time());
        $event->setTitle(UTIL_HtmlTag::stripTagsAndJs($data['title']));
        $event->setWhoCanView((int) $data['who_can_view']);
        $event->setDescription($data['desc']);
        $event->setUserId($userId);
        $event->setStartTimeDisable( $data['start_time'] == 'all_day' );
        $event->setEndTimeDisable( $data['end_time'] == 'all_day' );
        $event->setFileDisabled($data['file_disabled']);
        $event->setFileNote($data['file_note']);

        $serviceEvent = new OW_Event(FRMCFP_BOL_Service::EVENT_ON_CREATE_EVENT, array(
            'eventDto' => $event,
            "imageValid" => $imagePosted,
            "imageTmpPath" => $imgPath
        ));
        OW::getEventManager()->trigger($serviceEvent);

        if ( $imagePosted )
        {
            $event->setImage(FRMSecurityProvider::generateUniqueId());
            $this->saveEventImage($imgPath, $event->getImage());
        }
        if ( $filePosted )
        {
            $newFileId = FRMSecurityProvider::generateUniqueId('file');
            $newFileId = $newFileId . '_' . preg_replace("/[^A-Za-z0-9\.]/", '', $_FILES['file']['name']);
            $this->saveEventFile($_FILES['file']['tmp_name'], '', $newFileId);
            $event->setFile($newFileId);
        }

        $this->saveEvent($event);

        $eventUser = new FRMCFP_BOL_EventUser();
        $eventUser->setEventId($event->getId());
        $eventUser->setUserId($userId);
        $eventUser->setTimeStamp(time());
        $eventUser->setStatus(FRMCFP_BOL_Service::USER_STATUS_YES);
        $this->saveEventUser($eventUser);

        $serviceEvent = new OW_Event(FRMCFP_BOL_Service::EVENT_AFTER_CREATE_EVENT, array(
            'eventId' => $event->id,
            'eventDto' => $event
        ), array(

        ));
        OW::getEventManager()->trigger($serviceEvent);

        $event = FRMCFP_BOL_Service::getInstance()->findEvent($event->getId());

        return $event;
    }

    public function checkIfFileValid($file){
        return ( (int) $file['error'] !== 0 || !is_uploaded_file($file['tmp_name']) || !UTIL_File::validate($file['name'], ['pdf','doc','docx','jpg','png','zip','rar']) );
    }

    /**
     * Makes and saves event standard image and icon.
     *
     * @param string $imagePath
     * @param integer $imageId
     */
    public function saveEventImage( $tmpPath, $imageId )
    {
        $imagePath = $tmpPath;
        
        $storage = OW::getStorage();
        
        if ( $storage->fileExists($this->generateImagePath($imageId)) )
        {
            $storage->removeFile($this->generateImagePath($imageId));
            $storage->removeFile($this->generateImagePath($imageId, false));
        }

        $pluginfilesDir = OW::getPluginManager()->getPlugin('frmcfp')->getPluginFilesDir();

        $tmpImgPath = $pluginfilesDir . 'img_' .FRMSecurityProvider::generateUniqueId() . '.jpg';
        $tmpIconPath = $pluginfilesDir . 'icon_' . FRMSecurityProvider::generateUniqueId() . '.jpg';

        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('source' => $imagePath, 'destination' => $tmpImgPath)));
        if(isset($checkAnotherExtensionEvent->getData()['destination'])){
            $tmpImgPath = $checkAnotherExtensionEvent->getData()['destination'];
        }

        $image = new UTIL_Image($imagePath);
        $image->resizeImage(400, null)->saveImage($tmpImgPath)
            ->resizeImage(100, 100, true)->saveImage($tmpIconPath);
        
        $storage->copyFile($tmpIconPath, $this->generateImagePath($imageId));
        $storage->copyFile($tmpImgPath,$this->generateImagePath($imageId, false));

        OW::getStorage()->removeFile($imagePath);
        OW::getStorage()->removeFile($tmpImgPath);
        OW::getStorage()->removeFile($tmpIconPath);
    }

    /**
     * @param $tmpPath
     * @param $oldFileId
     * @param $newFileId
     */
    public function saveEventFile($tmpPath, $oldFileId, $newFileId )
    {
        $storage = OW::getStorage();

        $tmpFilePath = $this->generateFilePath($oldFileId);
        if ( $storage->fileExists($tmpFilePath) )
        {
            $storage->removeFile($tmpFilePath);
        }

        $tmpFilePath = $this->generateFilePath($newFileId);
        $storage->copyFile($tmpPath, $tmpFilePath);
        OW::getStorage()->removeFile($tmpPath);
    }

    /***
     * @param $fileId
     * @return string
     */
    public function generateFilePath( $fileId )
    {
        return OW::getPluginManager()->getPlugin('frmcfp')->getUserFilesDir() . 'event_' . $fileId;
    }

    /**
     * Deletes event.
     *
     * @param integer $eventId
     */
    public function deleteEvent( $eventId )
    {
        $eventDto = $this->eventDao->findById((int) $eventId);

        if ( $eventDto === null )
        {
            return;
        }
        BOL_CommentService::getInstance()->deleteEntityComments('frmcfp', $eventId);
        BOL_CommentService::getInstance()->deleteEntityComments('cfp', $eventId);
        $eventFiles = new OW_Event(FRMCFP_BOL_Service::EVENT_DELETE_FILES, array('eventId'=>$eventId));
        OW::getEventManager()->trigger($eventFiles);
        $e = new OW_Event(self::EVENT_ON_DELETE_EVENT, array('eventId' => (int) $eventId));
        OW::getEventManager()->trigger($e);

        if( !empty($eventDto->image) )
        {
            $storage = OW::getStorage();
            $storage->removeFile($this->generateImagePath($eventDto->image));
            $storage->removeFile($this->generateImagePath($eventDto->image, false));
        }

        $this->eventUserDao->deleteByEventId($eventId);
        $this->eventDao->deleteById($eventId);
        BOL_InvitationService::getInstance()->deleteInvitationByEntity('frmcfp', $eventId);
        BOL_InvitationService::getInstance()->deleteInvitationByEntity('frmcfp-invitation', $eventId);
    }

    /**
     * Returns event image and icon path.
     *
     * @param integer $imageId
     * @param boolean $icon
     * @return string
     */
    public function generateImagePath( $imageId, $icon = true )
    {
        $imagesDir = OW::getPluginManager()->getPlugin('frmcfp')->getUserFilesDir();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $imagesDir . ( $icon ? 'event_icon_' : 'event_image_' ) . $imageId)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return $imagesDir . ( $icon ? 'event_icon_' : 'event_image_' ) . $imageId . $ext;
    }

    /**
     * Returns event image and icon url.
     * 
     * @param integer $imageId
     * @param boolean $icon
     * @param $returnPath
     * @return string
     */
    public function generateImageUrl( $imageId, $icon = true, $returnPath = false )
    {
        return OW::getStorage()->getFileUrl($this->generateImagePath($imageId, $icon), $returnPath);
    }

    /**
     * Returns event image and icon url.
     *
     * @param integer $fileId
     * @param $returnPath
     * @return string
     */
    public function generateFileUrl($fileId, $returnPath = false )
    {
        return OW::getStorage()->getFileUrl($this->generateFilePath($fileId), $returnPath);
    }

    /**
     * Returns default event image url.
     */
    public function generateDefaultImageUrl()
    {
        return OW::getPluginManager()->getPlugin('frmcfp')->getStaticUrl() . 'img/default_event_image.png';
    }

    /**
     * Finds event by id.
     *
     * @param integer $id
     * @return FRMCFP_BOL_Event
     */
    public function findEvent( $id )
    {
        return $this->eventDao->findById((int) $id);
    }

    /**
     * Returns event users with provided status.
     *
     * @param integer $eventId
     * @param integer $status
     * @return array<FRMCFP_BOL_EventUser>
     */
    public function findEventUsers( $eventId, $status, $page, $usersCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $usersCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENT_USERS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventUserDao->findListByEventIdAndStatus($eventId, $status, $first, $count);
    }

    /**
     * Returns event users with provided status.
     *
     * @param integer $eventId
     * @return array<FRMCFP_BOL_EventUser>
     */
    public function findAllUsersForEvent( $eventId )
    {

        return $this->eventUserDao->findListByEventId($eventId);
    }

    /**
     * Returns users count for provided event and status.
     *
     * @param integer $eventId
     * @param integer $status
     * @return integer
     */
    public function findEventUsersCount( $eventId, $status )
    {
        return (int) $this->eventUserDao->findUsersCountByEventIdAndStatus($eventId, $status);
    }

    /**
     * Saves event user objects.
     *
     * @param FRMCFP_BOL_EventUser $eventUser
     */
    public function saveEventUser( FRMCFP_BOL_EventUser $eventUser )
    {
        $this->eventUserDao->save($eventUser);
    }

    /**
     * Saves event user objects.
     *
     * @param FRMCFP_BOL_EventUser $eventUser
     */
    public function addEventUser( $userId, $eventId, $status, $timestamp = null )
    {
        $statusList = array( FRMCFP_BOL_EventUserDao::VALUE_STATUS_YES, FRMCFP_BOL_EventUserDao::VALUE_STATUS_MAYBE, FRMCFP_BOL_EventUserDao::VALUE_STATUS_NO );

        if( (int) $userId <= 0 || $eventId <=0 || !in_array($status, $statusList) )
        {
            return null;
        }

        $event = $this->findEvent($eventId);

        if( empty($event) )
        {
            return null;
        }

        if ( !isset($timestamp) )
        {
            $timestamp = time();
        }

        $eventUser = $this->findEventUser($eventId, $userId);

        if ( empty($eventUser) )
        {
            $eventUser = new FRMCFP_BOL_EventUser();

            $eventUser->eventId = $eventId;
            $eventUser->userId = $userId;
            $eventUser->timeStamp = $timestamp;
        }

        $eventUser->status = $status;
        
        $this->eventUserDao->save($eventUser);

        return $eventUser;
    }

    /**
     * Finds event-user object.
     *
     * @param integer $eventId
     * @param integer $userId
     * @return FRMCFP_BOL_EventUser
     */
    public function findEventUser( $eventId, $userId )
    {
        return $this->eventUserDao->findObjectByEventIdAndUserId($eventId, $userId);
    }

    /**
     * Checks if user can view and join event.
     *
     * @param integer $eventId
     * @param integer $userId
     * @return boolean
     */
    public function canUserView( $eventId, $userId )
    {
        $event = $this->eventDao->findById($eventId);
        /* @var $event FRMCFP_BOL_Event */
        if ( $event === null )
        {
            return false;
        }

        $userEvent = $this->eventUserDao->findObjectByEventIdAndUserId($eventId, $userId);

        if ( $event->getWhoCanView() === self::CAN_VIEW_INVITATION_ONLY && $userEvent === null )
        {
            return false;
        }

        return true;
    }

    /**
     * Returns all latest events list ids
     *
     * @param integer $first
     * @param integer $count
     * @return array<FRMCFP_BOL_Event>
     */
    public function findAllLatestPublicEventsIds( $first, $count )
    {
        return $this->eventDao->findAllLatestPublicEventsIds($first, $count);
    }

    /**
     * Returns latest events list.
     *
     * @param integer $page
     * @return array<FRMCFP_BOL_Event>
     */
    public function findPublicEvents( $page, $eventsCount = null, $past = false )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findPublicEvents($first, $count, $past);
    }


    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param integer $page
     * @param $eventsCount
     * @param $userId
     * @param $userStatus
     * @param $past
     * @param array <eventIds>
     * @param $addUnapproved
     * @param $isPublic
     * @param $searchTitle
     * @return array<FRMCFP_BOL_Event>
     */
    public function findPublicEventsByFiltering($page, $eventsCount = null,$userId=null,$userStatus, $past ,$eventIds = array(), $addUnapproved , $isPublic, $searchTitle)
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            if($eventsCount == null){
                $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            }else{
                $count = (int) $eventsCount;
            }
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findPublicEventsByFiltering($userId,$userStatus,$first, $count, $past , $eventIds, $addUnapproved, $isPublic, $searchTitle);
    }

    public function findPublicEventsByFilteringInAdvanceSearch($first,$eventsCount = null,$userId=null,$userStatus, $past ,$eventIds = array(), $addUnapproved , $isPublic, $searchTitle)
    {
        return $this->eventDao->findPublicEventsByFiltering($userId,$userStatus,$first, $eventsCount, $past , $eventIds, $addUnapproved, $isPublic, $searchTitle);
    }
    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $userId
     * @param $userStatus
     * @param $past
     * @param array <eventIds>
     * @param $addUnapproved
     * @param $isPublic
     * @return count of events number
     */
    public function findPublicEventsByFilteringCount($userId=null,$userStatus, $past ,$eventIds = array(), $addUnapproved = false,$isPublic=true,$searchTitle)
    {
        return (int)$this->eventDao->findPublicEventsByFilteringCount($userId,$userStatus, $past , $eventIds, $addUnapproved,$isPublic,$searchTitle);
    }


    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param integer $page
     * @param $eventsCount
     * @param $userId
     * @param $userStatus
     * @param $past
     * @param array <eventIds>
     * @param $addUnapproved
     * @param $isPublic
     * @param $searchTitle
     * @return array<FRMCFP_BOL_Event>
     */
    public function findEventsForUser($page, $eventsCount = null,$userId=null,$userStatus, $past ,$eventIds = array(), $addUnapproved , $isPublic, $searchTitle)
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findUserEvents($userId,$userStatus,$first, $count, $past , $eventIds, $addUnapproved, $isPublic, $searchTitle);
    }

    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $userId
     * @param $userStatus
     * @param $past
     * @param array <eventIds>
     * @param $addUnapproved
     * @param $isPublic
     * @return count of events number
     */
    public function findEventsForUserCount($userId=null,$userStatus, $past ,$eventIds = array(), $addUnapproved = false,$isPublic=true,$searchTitle)
    {
        return (int)$this->eventDao->findUserEventsCount($userId,$userStatus, $past , $eventIds, $addUnapproved,$isPublic,$searchTitle);
    }

    /**
     * @param $eventsCount
     * @param $userId
     * @return array<EVENT_BOL_Event>
     */
    public function findUpComingEventsForUser($eventsCount = null,$userId=null)
    {
        if(!isset($eventsCount))
        {
            $eventsCount = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
        }
        return $this->eventDao->findUpComingEventsForUser($eventsCount, $userId);
    }

    /**
     * Returns latest events count.
     *
     * @return integer
     */
    public function findPublicEventsCount( $past = false )
    {
        return $this->eventDao->findPublicEventsCount($past);
    }

    /**
     * Finds events for user
     *
     * @param integer $userId
     * @return array
     */
    public function findUserEvents( $userId, $page, $eventsCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findUserCreatedEvents($userId, $first, $count);
    }

    /**
     * Returns user created events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findUsersEventsCount( $userId )
    {
        return $this->eventDao->findUserCretedEventsCount($userId);
    }

    /**
     * Returns list of user participating events.
     *
     * @param integer $userId
     * @param integer $page
     * @param integer $count
     * @return array
     */
    public function findUserParticipatedEvents( $userId, $page, $eventsCount = null, $addUnapproved = true  )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findUserEventsWithStatus($userId, self::USER_STATUS_YES, $first, $count, $addUnapproved );
    }

    /***
     * @param $ids
     * @param $page
     * @param null $eventsCount
     * @param bool $addUnapproved
     * @return array
     */
    public function findEventsWithIds( $ids, $page, $eventsCount = null, $addUnapproved = true  )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findEventsWithIds($ids, $first, $count, $addUnapproved );
    }

    /**
     * Returns user participated events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findUserParticipatedEventsCount( $userId, $addUnapproved = true )
    {
        return $this->eventDao->findUserEventsCountWithStatus($userId, self::USER_STATUS_YES, $addUnapproved);
    }

    /**
     * Returns list of user participating public events.
     *
     * @param integer $userId
     * @param integer $page
     * @param integer $count
     * @return array
     */
    public function findUserParticipatedPublicEvents( $userId, $page, $eventsCount = null )
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $eventsCount;
        }
        else
        {
            $page = ( $page === null ) ? 1 : (int) $page;
            $count = $this->configs[self::CONF_EVENTS_COUNT_ON_PAGE];
            $first = ( $page - 1 ) * $count;
        }

        return $this->eventDao->findPublicUserEventsWithStatus($userId, self::USER_STATUS_YES, $first, $count);
    }

    /**
     * Returns user participated public events count.
     *
     * @param integer $userId
     * @return integer
     */
    public function findUserParticipatedPublicEventsCount( $userId )
    {
        return $this->eventDao->findPublicUserEventsCountWithStatus($userId, self::USER_STATUS_YES);
    }

    /**
     * Prepares data for ipc listing.
     *
     * @param array<FRMCFP_BOL_Event> $events
     * @return array
     */
    public function getListingData( array $events )
    {
        $resultArray = array();

        /* @var $eventItem FRMCFP_BOL_Event */
        foreach ( $events as $eventItem )
        {
            $title = UTIL_String::truncate(strip_tags($eventItem->getTitle()), 300, "...") ;
            $sentenceCorrected = false;
            $showMore = false;
            if ( mb_strlen($eventItem->getDescription()) > 300 )
            {
                $sentence = strip_tags($eventItem->getDescription());
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected = true;
                }
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected = true;
                }
            }
            if($sentenceCorrected){
                $content = $sentence.'...';
                $showMore = true;
            }
            else{
                $content = UTIL_String::truncate(strip_tags($eventItem->getDescription()), 300, "...");
            }
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
            if (isset($stringRenderer->getData()['string'])) {
                $content = ($stringRenderer->getData()['string']);
            }

            $resultArray[$eventItem->getId()] = array(
                'content' => $content,
                'title' => $title,
                'showMore' => $showMore,
                'eventUrl' => OW::getRouter()->urlForRoute('frmcfp.view', array('eventId' => $eventItem->getId())),
                'imageSrc' => ( $eventItem->getImage() ? $this->generateImageUrl($eventItem->getImage(), true) : $this->generateDefaultImageUrl() ),
                'imageTitle' => $title
            );
        }

        return $resultArray;
    }

    public function getEventUrl( $eventId )
    {
        return OW::getRouter()->urlForRoute('frmcfp.view', array('eventId' => (int)$eventId));
    }
    
    /**
     * Prepares data for ipc listing with toolbar.
     *
     * @param array<FRMCFP_BOL_Event> $events
     * @return array
     */
    public function getListingDataWithToolbar( array $events, $toolbarList = array() )
    {
        $resultArray = $this->getListingData($events);
        $userService = BOL_UserService::getInstance();

        $idArray = array();

        /* @var $event FRMCFP_BOL_Event */
        foreach ( $events as $event )
        {
            $idArray[] = $event->getUserId();
        }

        $usernames = $userService->getDisplayNamesForList($idArray);
        $urls = $userService->getUserUrlsForList($idArray);

        $language = OW::getLanguage();
        /* @var $eventItem FRMCFP_BOL_Event */
        foreach ( $events as $eventItem )
        {
            $resultArray[$eventItem->getId()]['toolbar'][] = array('label' => $usernames[$eventItem->getUserId()], 'href' => $urls[$eventItem->getUserId()], 'class' => 'ow_icon_control ow_ic_user');
            $resultArray[$eventItem->getId()]['toolbar'][] = array('label' => UTIL_DateTime::formatSimpleDate($eventItem->getStartTimeStamp(),$eventItem->getStartTimeDisable()), 'class' => 'ow_ipc_date');

            if ( !empty($toolbarList[$eventItem->getId()]) )
            {
                $resultArray[$eventItem->getId()]['toolbar'] = array_merge($resultArray[$eventItem->getId()]['toolbar'], $toolbarList[$eventItem->getId()]);
            }
            
        }
        //printVar($resultArray);
        return $resultArray;
    }

    public function getUserListsArray()
    {
        return array(
            self::USER_STATUS_YES => 'yes',
            self::USER_STATUS_MAYBE => 'maybe',
            self::USER_STATUS_NO => 'no'
        );
    }

    /**
     * Deletes all user events.
     *
     * @param integer $userId
     */
    public function deleteUserEvents( $userId )
    {
        $events = $this->eventDao->findAllUserEvents($userId);

        /* @var $event FRMCFP_BOL_Event */
        foreach ( $events as $event )
        {
            $this->deleteEvent($event->getId());
        }
    }

    public function getContentMenu()
    {
        $menuItems = array();

        $listNames = array(
            'latest' => array('iconClass' => 'ow_ic_calendar'),
            'past' => array('iconClass' => 'ow_ic_reply'),
        );

        $i=1;
        foreach ( $listNames as $listKey => $listArr )
        {
            $menuItem = new BASE_MenuItem();
            $menuItem->setKey($listKey);
            $menuItem->setUrl(OW::getRouter()->urlForRoute('frmcfp.view_event_list', array('list' => $listKey)));
            $menuItem->setLabel(OW::getLanguage()->text('frmcfp', 'common_list_type_' . $listKey . '_label'));
            $menuItem->setOrder($i++);
            $menuItem->setIconClass($listArr['iconClass']);
            $menuItems[] = $menuItem;
        }
        
        $event = new BASE_CLASS_EventCollector('frmcfp.add_content_menu_item');
        OW::getEventManager()->getInstance()->trigger($event);
        
        $data = $event->getData();
        
        if ( !empty($data) && is_array($data) )
        {
            $menuItems = array_merge($menuItems, $data);
        }
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            return new BASE_MCMP_ContentMenu($menuItems);
        }

        return new BASE_CMP_ContentMenu($menuItems);
    }

    public function getAddForm($name, $mobile = false) {
        $form = new Form($name);
        $militaryTime = OW::getConfig()->getValue('base', 'military_time');

        $language = OW::getLanguage();

        $currentYear = date('Y', time());
        if(OW::getConfig()->getValue('frmjalali', 'dateLocale')==1){
            $currentYear=$currentYear-1;
        }
        $title = new TextField('title');
        $title->setRequired();
        $title->setLabel($language->text('frmcfp', 'add_form_title_label'));
        $form->addElement($title);

        $startDate = new DateField('start_date');
        $startDate->setMinYear($currentYear);
        $startDate->setMaxYear($currentYear + 5);
        $startDate->setRequired();
        $form->addElement($startDate);

        $startTime = new addFormTimeField('start_time');
        $startTime->setMilitaryTime($militaryTime);
        $startTime->setRequired();
        $form->addElement($startTime);

        $endDate = new DateField('end_date');
        $endDate->setMinYear($currentYear);
        $endDate->setMaxYear($currentYear + 5);
        $startDate->setRequired();
        $form->addElement($endDate);

        $endTime = new addFormTimeField('end_time');
        $endTime->setMilitaryTime($militaryTime);
        $startDate->setRequired();
        $form->addElement($endTime);

        $whoCanView = new RadioField('who_can_view');
        $whoCanView->setRequired();
        $whoCanView->addOptions(
            array(
                '1' => $language->text('frmcfp', 'add_form_who_can_view_option_anybody'),
                '2' => $language->text('frmcfp', 'add_form_who_can_view_option_creators')
            )
        );
        $whoCanView->setLabel($language->text('frmcfp', 'add_form_who_can_view_label'));
        $form->addElement($whoCanView);

        $submit = new Submit('submit');
        $submit->setValue($language->text('frmcfp', 'add_form_submit_label'));
        $form->addElement($submit);

        if($mobile) {
            $desc = new MobileWysiwygTextarea('desc', 'frmcfp');
        }else{
            $desc = new WysiwygTextarea('desc', 'frmcfp');
        }
        $desc->setLabel($language->text('frmcfp', 'add_form_desc_label'));
        $desc->setRequired();
        $form->addElement($desc);

        $imageField = new FileField('file');
        $imageField->setLabel($language->text('frmcfp', 'add_form_file_label'));
        $form->addElement($imageField);

        $imageField = new FileField('image');
        $imageField->setLabel($language->text('frmcfp', 'add_form_image_label'));
        $form->addElement($imageField);

        $fileDisabled = new CheckboxField('file_disabled');
        $fileDisabled->setLabel($language->text('frmcfp', 'add_form_file_disabled_label'));
        $form->addElement($fileDisabled);

        $fileNote = new TextField('file_note');
        $fileNote->setLabel($language->text('frmcfp', 'add_form_file_note_label'));
        $form->addElement($fileNote);

        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        return $form;
    }
    
    public function findCronExpiredEvents( $first, $count )
    {
        return $this->eventDao->findExpiredEventsForCronJobs($first, $count);
    }
    
    public function findByIdList( $idList )
    {
        return $this->eventDao->findByIdList($idList);
    }

    public function findUsersCountByEventIdListAndStatus( $eventIdList, $status ){
        return $this->eventUserDao->findUsersCountByEventIdListAndStatus($eventIdList, $status );
    }

    public function findUsersCountByEventIdList( $eventIdList ){
        return $this->eventUserDao->findUsersCountByEventIdList( $eventIdList );
    }


    /***
     *              FILE LIST
     */

    public function deleteFiles(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['eventId'])) {
            $filesDto = $this->eventFileDao->getEventFilesByEventId($params['eventId']);
            foreach ($filesDto as $file) {
                try {
                    OW::getEventManager()->trigger(new OW_Event("feed.delete_item", array(
                        'entityType' => 'frmcfp-add-file',
                        'entityId' => $file->id
                    )));
                    OW::getEventManager()->call('notifications.remove', array(
                        'entityType' => 'frmcfp-add-file',
                        'entityId' => $file->id
                    ));
                    $this->deleteFileForEventByEventId($params['eventId']);
                    BOL_AttachmentService::getInstance()->deleteAttachmentById($file->attachmentId);
                } catch (Exception $e) {

                }
            }
        }
        else if(isset($params['allFiles'])) {
            $filesDto = $this->eventFileDao->findAllFiles();
            foreach ($filesDto as $file) {
                try {
                    BOL_AttachmentService::getInstance()->deleteAttachmentById($file->attachmentId);
                    OW::getEventManager()->trigger(new OW_Event("feed.delete_item", array(
                        'entityType' => 'frmcfp-add-file',
                        'entityId' => $file->id
                    )));
                    OW::getEventManager()->call('notifications.remove', array(
                        'entityType' => 'frmcfp-add-file',
                        'entityId' => $file->id
                    ));
                } catch (Exception $e) {

                }
            }
        }
    }

    public function isModeratorForEvent($eventId){
        $event = $this->findEvent($eventId);
        return OW::getUser()->isAuthorized('frmcfp') || OW::getUser()->getId() == $event->getUserId();
    }

    public function findFileList($eventId, $first=0, $count)
    {
        $isModerator = $this->isModeratorForEvent($eventId);
        $uId = OW::getUser()->getId();
        $eventFileList = $this->eventFileDao->findFileListByEventId($eventId, $first, $count);
        $attachmentList = array();
        foreach ( $eventFileList as $eventFile )
        {
            $attachment = BOL_AttachmentDao::getInstance()->findById($eventFile->attachmentId);
            if(isset($attachment) && $attachment->getId()>0) {
                if ($isModerator || $attachment->userId == $uId) {
                    $attachmentList[] = $attachment;
                }
            }else{
                $this->deleteFileForEvent($eventId, $eventFile->attachmentId);
            }
        }
        return $attachmentList;
    }

    public function findFileListCount($eventId)
    {
        return count($this->findFileList($eventId,0,1000));
    }

    public function addFileWidget(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['controller']) && isset($params['eventId'])){
            $bcw = new BASE_CLASS_WidgetParameter();
            $bcw->additionalParamList=array('entityId'=>$params['eventId']);
            $eventController = $params['controller'];
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
                $eventController->addComponent('eventFileList', new FRMCFP_MCMP_FileListWidget($bcw));
            }else{
                $eventController->addComponent('eventFileList', new FRMCFP_CMP_FileListWidget($bcw));
            }
            $fileBoxInformation = array(
                'show_title' => true,
                'title' => OW_Language::getInstance()->text('frmcfp', 'widget_files_title'),
                'wrap_in_box' => true,
                'icon' => 'ow_ic_info',
                'type' => "",
            );
            $eventController->assign('fileBoxInformation', $fileBoxInformation);
        }
    }
    /***
     * @param $name
     * @return string
     */
    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmcfp')->getStaticUrl(). 'images/'.$name.'.svg';
    }
    /***
     * @param $ext
     * @return string
     */
    public function getProperIcon($ext){
        $videoFormats = array('mov','mkv','mp4','avi','flv','ogg','mpg','mpeg');

        $wordFormats = array('docx','doc','docm','dotx','dotm');

        $excelFormats = array('xlsx','xls','xlsm');

        $zipFormats = array('zip','rar');

        $imageFormats =array('jpg','jpeg','gif','tiff','png');

        if(in_array($ext,$videoFormats)){
            return $this->getIconUrl('avi');
        }
        else if(in_array($ext,$wordFormats)){
            return $this->getIconUrl('doc');
        }
        else if(in_array($ext,$excelFormats)){
            return $this->getIconUrl('xls');
        }
        else if(in_array($ext,$zipFormats)){
            return $this->getIconUrl('zip');
        }
        else if(in_array($ext,$imageFormats)){
            return $this->getIconUrl('jpg');
        }
        else if(strcmp($ext,'pdf')==0){
            return $this->getIconUrl('pdf');
        }
        else if(strcmp($ext,'txt')==0){
            return $this->getIconUrl('txt');
        }
        else{
            return $this->getIconUrl('file');
        }
    }

    public function addFileForEvent($eventId, $attachmentId){
        return $this->eventFileDao->addFileForEvent($eventId,$attachmentId);
    }

    public function deleteFileForEvent($eventId, $attachmentId){
        $this->eventFileDao->deleteEventFilesByAidAndEid($eventId,$attachmentId);
    }

    public function deleteFileForEventByEventId($eventId){
        $this->eventFileDao->deleteEventFilesByEventId($eventId);
    }

    public function getUploadFileForm($eventId)
    {
        $language = OW::getLanguage();

        OW::getDocument()->setHeading($language->text('frmcfp', 'file_create_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_new');
        OW::getDocument()->setTitle($language->text('frmcfp', 'file_create_page_title'));
        OW::getDocument()->setDescription($language->text('frmcfp', 'file_create_page_description'));

        $form = new FRMCSF_FileUploadForm($eventId);
        $actionRoute = OW::getRouter()->urlFor('FRMCFP_CTRL_Files', 'addFile', array('eventId' => $eventId));
        $form->setAction($actionRoute);
        return $form;
    }
}

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.forms
 * @since 1.0
 */
class addFormTimeField extends FormElement
{
    private $militaryTime;

    private $allDay = false;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
        $this->militaryTime = false;
    }

    public function setMilitaryTime( $militaryTime )
    {
        $this->militaryTime = (bool) $militaryTime;
    }

    public function setValue( $value )
    {
        if ( $value === null )
        {
            $this->value = null;
        }

        $this->allDay = false;

        if ( $value === 'all_day' )
        {
            $this->allDay = true;
            $this->value = null;
            return;
        }

        if ( is_array($value) && isset($value['hour']) && isset($value['minute']) )
        {
            $this->value = array_map('intval', $value);
        }

        if ( is_string($value) && strstr($value, ':') )
        {
            $parts = explode(':', $value);
            $this->value['hour'] = (int) $parts[0];
            $this->value['minute'] = (int) $parts[1];
        }
    }

    public function getValue()
    {
        if ( $this->allDay === true )
        {
            return 'all_day';
        }

        return $this->value;
    }

    /**
     *
     * @return string
     */
    public function getElementJs()
    {
        $jsString = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        return $jsString.$this->generateValidatorAndFilterJsCode("formElement");
    }

    private function getTimeString( $hour, $minute )
    {
        if ( $this->militaryTime )
        {
            $hour = $hour < 10 ? '0' . $hour : $hour;
            return $hour . ':' . $minute;
        }
        else
        {
            if ( $hour == 12 )
            {
                $dp = 'pm';
            }
            else if ( $hour > 12 )
            {
                $hour = $hour - 12;
                $dp = 'pm';
            }
            else
            {
                $dp = 'am';
            }

            $hour = $hour < 10 ? '0' . $hour : $hour;
            return $hour . ':' . $minute . $dp;
        }
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        for ( $hour = 0; $hour <= 23; $hour++ )
        {
            $valuesArray[$hour . ':0'] = array('label' => $this->getTimeString($hour, '00'), 'hour' => $hour, 'minute' => 0);
            $valuesArray[$hour . ':30'] = array('label' => $this->getTimeString($hour, '30'), 'hour' => $hour, 'minute' => 30);
        }

        $optionsString = UTIL_HtmlTag::generateTag('option', array('value' => ""), true, OW::getLanguage()->text('frmcfp', 'time_field_invitation_label'));

        $allDayAttrs = array( 'value' => "all_day"  );

        if ( $this->allDay )
        {
            $allDayAttrs['selected'] = 'selected';
        }

        $optionsString = UTIL_HtmlTag::generateTag('option', $allDayAttrs, true, OW::getLanguage()->text('frmcfp', 'all_day'));

        foreach ( $valuesArray as $value => $labelArr )
        {
            $attrs = array('value' => $value);

            if ( !empty($this->value) && $this->value['hour'] === $labelArr['hour'] && $this->value['minute'] === $labelArr['minute'] )
            {
                $attrs['selected'] = 'selected';
            }

            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, $labelArr['label']);
        }

        return UTIL_HtmlTag::generateTag('select', $this->attributes, true, $optionsString);
    }
}

class FRMCSF_FileUploadForm extends Form
{
    public function __construct($groupId)
    {
        parent::__construct('fileUploadForm');

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $language = OW::getLanguage();

        $nameField = new TextField('name');
        $nameField->setLabel($language->text('frmcfp', 'create_field_file_name_label'));
        $this->addElement($nameField);

        $fileField = new FileField('fileUpload');
        $fileField->setLabel($language->text('frmcfp', 'create_field_file_upload_label'));
        $this->addElement($fileField);

        $groupIdElement = new HiddenField('id');
        $groupIdElement->setValue($groupId);
        $this->addElement($groupIdElement);

        $saveField = new Submit('save');
        $saveField->setValue(OW::getLanguage()->text('frmcfp', 'add_form_submit_label'));
        $this->addElement($saveField);
    }
}

