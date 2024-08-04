<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.controllers
 * @since 1.0
 */
class FRMCFP_CTRL_Files extends OW_ActionController
{
    /**
     * @var FRMCFP_BOL_Service
     */
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = FRMCFP_BOL_Service::getInstance();
    }

    public function getAttachmentUrl($name)
    {
        return OW::getStorage()->getFileUrl($this->getAttachmentDir($name));
    }

    public function getAttachmentDir($name)
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS .$name ;
    }

    public function fileList( $params )
    {
        $this->setPageTitle(OW::getLanguage()->text('frmcfp', 'file_list'));
        $this->setPageHeading(OW::getLanguage()->text('frmcfp', 'file_list'));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmcfp', 'main_menu_item');

        $eventId = (int) $params['eventId'];
        $eventDto = FRMCFP_BOL_Service::getInstance()->findEvent($eventId);

        if ( $eventDto === null )
        {
            throw new Redirect404Exception();
        }

        if ( !FRMCFP_BOL_Service::getInstance()->canUserView($eventId, OW::getUser()->getId())) {

            throw new Redirect404Exception();
        }
        if ( $eventDto->whoCanView == FRMCFP_BOL_Service::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthorized('frmcfp') )
        {
            if ( !OW::getUser()->isAuthenticated() )
            {
                throw new Redirect404Exception();
            }

            $eventUser = $this->service->findEventUser($eventDto->getId(), OW::getUser()->getId());

            // check if user can view event
            if ( (int) $eventDto->getWhoCanView() === FRMCFP_BOL_Service::CAN_VIEW_INVITATION_ONLY && $eventUser === null && !OW::getUser()->isAuthorized('frmcfp') )
            {
                throw new Redirect404Exception();
            }
        }

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $perPage = 20;
        $first = ($page - 1) * $perPage;
        $count = $perPage;

        $dtoList = $this->service->findFileList($eventId, $first, $count);
        $listCount = $this->service->findFileListCount($eventId);
        $paging = new BASE_CMP_PagingMobile($page, ceil($listCount / $perPage), 2);
        $this->addComponent('paging',$paging);
        $filelist = array();
        $attachmentIds = array();
        $deleteUrls = array();
        $canEdit=false;
//        if ( OW::getUser()->getId()==$eventDto->userId )
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
            $filelist[$item->id]['iconUrl'] = FRMCFP_BOL_Service::getInstance()->getProperIcon(strtolower($fileNameExt));
            $filelist[$item->id]['truncatedFileName'] = $fileName;
            $filelist[$item->id]['fileName'] = $item->getOrigFileName();
            $filelist[$item->id]['createdDate'] =$item->addStamp;
            $filelist[$item->id]['userName'] =BOL_UserService::getInstance()->getDisplayName($item->getUserId());
            $filelist[$item->id]['userUrl'] = OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($item->getUserId())));
            $filelist[$item->id]['name'] =$item->id;
            if ($item->userId == OW::getUser()->getId() || $canEdit) {
                $deleteUrls[$item->id] = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmcfp.deleteFile',
                    array('attachmentId' => $item->id, 'eventId' => $eventId)),array('code' =>$code));
            }
        }

        $this->assign("showAdd", $canEdit);
        $this->assign("fileList", $filelist);
        $this->assign("attachmentIds", $attachmentIds);
        $this->assign('deleteUrls', $deleteUrls);
        $plugin = OW::getPluginManager()->getPlugin('frmcfp');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'files.js');

        $this->assign("eventId", $eventId);
        $this->assign('backUrl',OW::getRouter()->urlForRoute('frmcfp.view' , array('eventId'=>$eventId)));
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'frmcfp.css');
        $this->assign('deleteIconUrl', $plugin->getStaticUrl().'images/trash.svg');
        $params = array(
            "sectionKey" => "frmcfp",
            "entityKey" => "cfpFiles",
            "title" => "frmcfp+file_list",
            "description" => "frmcfp+meta_desc_event_files",
            "keywords" => "frmcfp+meta_keywords_event_files",
            "vars" => array( "event_title" => $eventDto->title )
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->setTemplate(OW::getPluginManager()->getPlugin('frmcfp')->getMobileCtrlViewDir() . 'files_file_list.html');
        }
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

        $form = $this->service->getUploadFileForm($eventId);
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            if (!empty($_FILES)) {
                $this->manageAddFile($eventId, $_FILES['fileUpload']);
            }
            exit(json_encode(['result'=>'ok']));
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
        $eventDto = $this->service->findEvent($eventId);
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
            $this->service->deleteFileForEvent($eventDto->getId(), $attachmentId);
            BOL_AttachmentService::getInstance()->deleteAttachmentById($attachmentId);
        }
        catch (Exception $e){
        }

        $this->redirect(OW::getRouter()->urlForRoute('frmcfp.file-list' , array('eventId'=>$eventId)));
    }

    public function manageAddFile($eventId, $item){
        $resultArr = array('result' => false, 'message' => 'General error');
        $bundle = FRMSecurityProvider::generateUniqueId();
        $pluginKey = 'frmcfp';
        if(isset($_POST['name']) && $_POST['name']!=""){
            $item['name'] = $_POST['name'].'.'.end(explode('.',$item['name'] ));
        }
        try {
            $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile($pluginKey, $item, $bundle);
            OW::getEventManager()->call('base.attachment_save_image', array('uid' => $bundle, 'pluginKey' => $pluginKey));
            $resultArr['result'] = true;
            $resultArr['url'] = $dtoArr['url'];
            $attachmentId = $dtoArr['dto']->id;
            $fileId = FRMCFP_BOL_EventFilesDao::getInstance()->addFileForEvent($eventId, $attachmentId);
            $eventDto = $this->service->findEvent($eventId);
            if(!isset($eventDto)){
                return $resultArr;
            }
        } catch (Exception $e) {
            $resultArr['message'] = $e->getMessage();
            OW::getFeedback()->error($resultArr['message']);
        }
        return $resultArr;
    }
}
