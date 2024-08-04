<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmeventplus.controllers
 * @since 1.0
 */
class FRMEVENTPLUS_CTRL_Base extends OW_ActionController
{
    /**
     * @var EVENT_BOL_EventService
     */
    private $eventPlusService;
    private $eventService;

    public function __construct()
    {
        parent::__construct();
        $this->eventPlusService = FRMEVENTPLUS_BOL_Service::getInstance();
        $this->eventService = EVENT_BOL_EventService::getInstance();
    }

    /***
     * leave event controller
     * @param $params
     * @throws Redirect404Exception
     */
    public function leave( $params )
    {
        $event = $this->getEventForParams($params);

        if ( !OW::getUser()->isAuthenticated() || ( OW::getUser()->getId() == $event->getUserId() && !OW::getUser()->isAuthorized('event') ) )
        {
            throw new Redirect404Exception();
        }
        $eventService = EVENT_BOL_EventService::getInstance();
        $eventUser = $eventService->findEventUser($event->getId(),OW::getUser()->getId());
        $this->eventPlusService->leaveEvent($event->getId(),OW::getUser()->getId());

        OW::getEventManager()->call("feed.delete_activity", array(
            'activityType' => 'event-join',
            'activityId' => $eventUser->getId(),
            'entityId' => $event->getId(),
            'userId' => OW::getUser()->getId(),
            'entityType' => 'event'
        ));

        OW::getEventManager()->call("feed.delete_activity", array(
            'activityType' => 'subscribe',
            'activityId' => $eventUser->getId(),
            'entityId' => $event->getId(),
            'userId' => OW::getUser()->getId(),
            'entityType' => 'event'
        ));


        OW::getFeedback()->info(OW::getLanguage()->text('frmeventplus', 'leave_success_message'));
        $this->redirect(OW::getRouter()->urlForRoute('event.main_menu_route'));
    }

    /***
     * Get event by params(eventId)
     * @param $params
     * @return EVENT_BOL_Event
     * @throws Redirect404Exception
     */
    private function getEventForParams( $params )
    {
        if ( empty($params['eventId']) )
        {
            throw new Redirect404Exception();
        }

        $event = EVENT_BOL_EventService::getInstance()->findEvent($params['eventId']);

        if ( $event === null )
        {
            throw new Redirect404Exception();
        }

        return $event;
    }

    public function fileList( $params )
    {

        $eventId = (int) $params['eventId'];
        $eventDto = EVENT_BOL_EventService::getInstance()->findEvent($eventId);

        if ( $eventDto === null )
        {
            throw new Redirect404Exception();
        }
        $language = OW::getLanguage();

        if ( !EVENT_BOL_EventService::getInstance()->canUserView($eventId,OW::getUser()->getId())) {

            throw new Redirect404Exception();
        }
        if ( $eventDto->whoCanView == EVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthorized('event') )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                throw new Redirect404Exception();
            }

            $eventInvite = $this->eventService->findEventInvite($eventDto->getId(), OW::getUser()->getId());
            $eventUser = $this->eventService->findEventUser($eventDto->getId(), OW::getUser()->getId());

            // check if user can view event
            if ( (int) $eventDto->getWhoCanView() === EVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && $eventUser === null && !OW::getUser()->isAuthorized('event') )
            {
                if( $eventInvite === null ) {
                    throw new Redirect404Exception();
                }else{
                    $this->redirect(OW::getRouter()->urlForRoute('event.view_event_list', array('list' => 'invited')));
                }
            }
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->eventPlusService->findFileList($eventId, $first, $count);
        $listCount = $this->eventPlusService->findFileListCount($eventId);
        $paging = new BASE_CMP_PagingMobile($page, ceil($listCount / $perPage), 2);
        $this->addComponent('paging',$paging);
        $filelist = array();
        $attachmentIds = array();
        $deleteUrls = array();
        $canEdit=false;
        if ( OW::getUser()->getId()==$eventDto->userId )
        {
            $canEdit = true;
        }
        foreach ( $dtoList as $item )
        {
            $sentenceCorrected = false;
            if ( mb_strlen($item->getOrigFileName()) > 100 )
            {
                $sentence = $item->getOrigFileName();
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 100)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected=true;
                }
                $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 100)));
                if(isset($event->getData()['correctedSentence'])){
                    $sentence = $event->getData()['correctedSentence'];
                    $sentenceCorrected=true;
                }
            }
            if($sentenceCorrected){
                $fileName = $sentence.'...';
            }
            else{
                $fileName = UTIL_String::truncate($item->getOrigFileName(), 100, '...');
            }



            $code = '';
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$item->id,'isPermanent'=>true,'activityType'=>'event_deleteFile')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
            }
            $fileNameArr = explode('.',$item->fileName);
            $fileNameExt = end($fileNameArr);
            $filelist[$item->id]['fileUrl'] = $this->getAttachmentUrl($item->fileName);
            $filelist[$item->id]['iconUrl'] = FRMEVENTPLUS_BOL_Service::getInstance()->getProperIcon(strtolower($fileNameExt));
            $filelist[$item->id]['truncatedFileName'] = $fileName;
            $filelist[$item->id]['fileName'] = $item->getOrigFileName();
            $filelist[$item->id]['createdDate'] =$item->addStamp;
            $filelist[$item->id]['userName'] =BOL_UserService::getInstance()->getDisplayName($item->getUserId());
            $filelist[$item->id]['userUrl'] = OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($item->getUserId())));
            $filelist[$item->id]['name'] =$item->id;
            if ($item->userId == OW::getUser()->getId() || $canEdit) {
                $deleteUrls[$item->id] = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmeventplus.deleteFile',
                    array('attachmentId' => $item->id, 'eventId' => $eventId)),array('code' =>$code));
            }
        }

        $this->assign("showAdd", $canEdit);
        $this->assign("fileList", $filelist);
        $this->assign("attachmentIds", $attachmentIds);
        $this->assign('deleteUrls', $deleteUrls);
        $plugin = OW::getPluginManager()->getPlugin('frmeventplus');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmeventplus.js');

        $this->assign("eventId", $eventId);
        $this->assign('backUrl',OW::getRouter()->urlForRoute('event.view' , array('eventId'=>$eventId)));
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'frmeventplus.css');
        $this->assign('deleteIconUrl', OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'images/File_Extentions/trash.svg');
        $params = array(
            "sectionKey" => "frmeventplus",
            "entityKey" => "eventFiles",
            "title" => "frmeventplus+meta_title_event_files",
            "description" => "frmeventplus+meta_desc_event_files",
            "keywords" => "frmeventplus+meta_keywords_event_files",
            "vars" => array( "event_title" => $eventDto->title )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    public function getAttachmentUrl($name)
    {
        return OW::getStorage()->getFileUrl($this->getAttachmentDir($name));
    }

    public function getAttachmentDir($name)
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS .$name ;
    }

    public function addFile($params)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }
        $eventId = (int) $params['eventId'];

        if (!isset($eventId) || $eventId<=0 )
        {
            throw new Redirect404Exception();
        }

        $form = $this->eventPlusService->getUploadFileForm($eventId);
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            if (!empty($_FILES)) {
                FRMEVENTPLUS_BOL_Service::getInstance()->manageAddFile($eventId, $_FILES['fileUpload']);
            }
            exit();
        }
    }

    public function deleteFile($params){
        if (!OW::getUser()->isAuthenticated()) {
            throw new AuthenticateException();
        }
        $eventId = $params['eventId'];
        $attachmentId = $params['attachmentId'];
        if ( !isset($eventId)  || !isset($attachmentId))
        {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'event_deleteFile')));
        }
        $eventDto = $this->eventService->findEvent($eventId);
        if(!$eventDto) {
            throw new Redirect404Exception();
        }
        $canEdit=false;
        if ($eventDto->userId==OW::getUser()->getId())
        {
            $canEdit = true;
        }

        $attachment = BOL_AttachmentDao::getInstance()->findById($attachmentId);
        if ($attachment->userId != OW::getUser()->getId() && !$canEdit) {
            throw new Redirect404Exception();
        }

        try {
            $fileId = $this->eventPlusService->findFileIdByAidAndGid($eventDto->getId(), $attachmentId);
            $this->eventPlusService->deleteFileForEvent($eventDto->getId(), $attachmentId);
            BOL_AttachmentService::getInstance()->deleteAttachmentById($attachmentId);
            OW::getEventManager()->trigger(new OW_Event("feed.delete_item", array(
                'entityType' => 'event-add-file',
                'entityId' => $fileId
            )));
            OW::getEventManager()->call('notifications.remove', array(
                'entityType' => 'event-add-file',
                'entityId' => $fileId
            ));
        }
        catch (Exception $e){

        }

        $this->redirect(OW::getRouter()->urlForRoute('frmeventplus.file-list' , array('eventId'=>$eventId)));
    }

}
