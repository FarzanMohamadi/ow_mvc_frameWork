<?php
class FRMTECHNOLOGY_CTRL_Save extends OW_ActionController
{

    private $service;
    private $isMobile;

    public function __construct()
    {
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $this->isMobile=true;
        }
        else{
            $this->isMobile=false;
        }
        $this->service = FRMTECHNOLOGY_BOL_Service::getInstance();

        if ( !OW::getRequest()->isAjax() )
        {
            if($this->isMobile)
            {
            //TODO do we need to write something here
            }
            else {
                $mainMenuItem = OW::getDocument()->getMasterPage()->getMenu(OW_Navigation::MAIN)->getElement('main_menu_item', 'frmtechnology');
                if ($mainMenuItem !== null) {
                    $mainMenuItem->setActive(true);
                }
            }
        }
    }

    public function index($params = array())
    {
        $this->assign('isMobile',$this->isMobile);
        if (OW::getRequest()->isAjax())
        {
            exit();
        }

//        if ( !OW::getUser()->isAuthenticated() )
//        {
//            throw new AuthenticateException();
//        }
        if(!$this->isMobile) {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmtechnology', 'main_menu_item');
        }

        $this->setPageHeadingIconClass('ow_ic_write');

//        if (!OW::getUser()->isAuthorized('frmtechnology') && !OW::getUser()->isAuthorized('frmtechnology', 'add_technology') && !OW::getUser()->isAdmin() )
//        {
//            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmtechnology', 'add_technology');
//            throw new AuthorizationException($status['msg']);
//        }

        $this->assign('authMsg', null);

        $id = empty($params['technologyId']) ? 0 : $params['technologyId'];
        $service = FRMTECHNOLOGY_BOL_Service::getInstance();
        $technologydao = FRMTECHNOLOGY_BOL_TechnologyDao::getInstance();

        $isEdit = false;
        if ( intval($id) > 0 )
        {
            $technology = $technologydao->findById($id);

            if(!isset($technology)){
                throw new Redirect404Exception();
            }
            if (!OW::getUser()->isAuthorized('frmtechnology','manage-technology') && !OW::getUser()->isAdmin())
            {
                throw new Redirect404Exception();
            }
            $this->assign('technologyUrl', OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $id)));
            $isEdit = true;

        }
        else
        {
            $technology = new FRMTECHNOLOGY_BOL_Technology();
            //$technology->setUserId(OW::getUser()->getId());
        }
        $this->assign('isEdit', $isEdit);

        $form = new TechnologyForm($technology);
        if($form->getElement('captcha'))
        {
            $this->assign('displayCaptcha',true);
        }
        if ($technology->getImage1() )
        {
            $this->assign('imgsrc1', $service->generateImageUrl($technology->getImage1(), true));
        }
        if ($technology->getImage2() )
        {
            $this->assign('imgsrc2', $service->generateImageUrl($technology->getImage2(), true));
        }
        if ($technology->getImage3() )
        {
            $this->assign('imgsrc3', $service->generateImageUrl($technology->getImage3(), true));
        }
        if ($technology->getImage4() )
        {
            $this->assign('imgsrc4', $service->generateImageUrl($technology->getImage4(), true));
        }
        if ($technology->getImage5() )
        {
            $this->assign('imgsrc5', $service->generateImageUrl($technology->getImage5(), true));
        }
        if ( OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('save')) ) && $form->isValid($_POST) )
        {
            $form->process($this);
        }

        $this->addForm($form);

        if (intval($id) > 0) {
            $this->setPageHeading(OW::getLanguage()->text('frmtechnology', 'edit_technology_page_heading'));
            OW::getDocument()->setTitle(OW::getLanguage()->text('frmtechnology', 'meta_title_edit_technology'));
            OW::getDocument()->setDescription(OW::getLanguage()->text('frmtechnology', 'meta_description_edit_technology'));
        }
        else{
            $this->setPageHeading(OW::getLanguage()->text('frmtechnology', 'save_technology_page_heading'));
            OW::getDocument()->setTitle(OW::getLanguage()->text('frmtechnology', 'meta_title_new_technology'));
            OW::getDocument()->setDescription(OW::getLanguage()->text('frmtechnology', 'meta_description_new_technology'));
        }


    }

    public function delete( $params )
    {
        if ( empty($params['technologyId']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        $technologyId = $params['technologyId'];
        $technology = $this->service->findTechnologyById($technologyId);

        if ( empty($technology) )
        {
            throw new Redirect404Exception();
        }

        //$isOwner = OW::getUser()->getId() == $technology->getUserId();
        $isModerator = OW::getUser()->isAuthorized('frmtechnology','manage-technology');

        if ( !$isModerator )
        {
            throw new Redirect404Exception();
        }
//        $orderCount = $this->service->findOrderCountByTechnologyId($technologyId);
//        if ($orderCount > 0){
//            OW::getFeedback()->error(OW::getLanguage()->text('frmtechnology', 'delete_technology_with_order_error'));
//            $this->redirect(OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $params['technologyId'])));
//
//        }
//        else{
            $status = $this->service->findTechnologyById($technologyId)->getStatus();
            $this->service->deleteTechnology($technologyId);
            $tagService = BOL_TagService::getInstance();
            $tagService->deleteEntityTags($technologyId, 'technology-description');
            OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'delete_technology_success_massage'));
            if($status == FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE){
                $this->redirect(OW::getRouter()->urlForRoute('frmtechnology.index'));
            }else{
                $this->redirect(OW::getRouter()->urlForRoute('frmtechnology.view-list', array( 'listType' => 'deactivate' )));
            }
//        }

    }

    public function updateActivationStatus( $params ){
        if ( !isset($params['technologyId']) ||   !isset($params['status']))
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }
        $technologyId = $params['technologyId'];
        $status=$params['status'];
        $technology = $this->service->findTechnologyById($technologyId);
        if ( empty($technology) )
        {
            throw new Redirect404Exception();
        }
        $allowDeactivate = OW::getUser()->isAuthorized('frmtechnology', 'manage-technology') || OW::getUser()->isAdmin();
        if ( $allowDeactivate ){
            $this->service->updateTechnologyStatus($technologyId,$status);
            OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', $status.'_success_message'));
            $this->redirect(OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $params['technologyId'])));
        }

    }
}

class TechnologyForm extends Form
{

    private $technology;
    private $service;
    private $config;

    public function __construct( FRMTECHNOLOGY_BOL_Technology $technology, $tags = array() )
    {
        parent::__construct('save');

        $this->service = FRMTECHNOLOGY_BOL_Service::getInstance();
        $this->technology = $technology;
        $this->config =  OW::getConfig();
        $this->setMethod('post');
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $titleTextField = new TextField('title');
        $titleTextField->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_title_label'));
        $titleTextField->setRequired(true);
        $titleTextField->setValue($technology->getTitle());
        $this->addElement($titleTextField);

        $userFullNameTextField = new TextField('userFullName');
        $userFullNameTextField->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_userFullName_label'));
        $userFullNameTextField->setRequired(true);
        $userFullNameTextField->setValue($technology->getUserFullName());
        $this->addElement($userFullNameTextField);

        $positionSelectBox= new Selectbox('position');
        $options =json_decode($this->config->getValue('frmtechnology', 'positions_list_setting'));
        $newOptions = array();
        foreach ($options as $key => $value){
            $newOptions[$value] = $value;
        }
        $positionSelectBox->setOptions($newOptions);
        $positionSelectBox->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_position_label'));
        $positionSelectBox->setRequired(true);
        $positionSelectBox->setValue($technology->getPosition());
        $this->addElement($positionSelectBox);

        $studentGradeSelectbox = new Selectbox('grade');
        $options = json_decode($this->config->getValue('frmtechnology', 'grades_list_setting'));
        $newOptions = array();
        foreach ($options as $key => $value){
            $newOptions[$value] = $value;
        }
        $studentGradeSelectbox->setOptions($newOptions);
        $studentGradeSelectbox->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_grade_label'));
        $studentGradeSelectbox->setValue($technology->getGrade());
        $this->addElement($studentGradeSelectbox);

        $snTextField = new TextField('sn');
        $snTextField->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_sn_label'));
        $snTextField->setValue($technology->getStudentNumber());
        $this->addElement($snTextField);

        $orgSelectbox = new Selectbox('org');
        $options = json_decode($this->config->getValue('frmtechnology', 'orgs_list_setting'));
        $newOptions = array();
        foreach ($options as $key => $value){
            $newOptions[$value] = $value;
        }
        $orgSelectbox->setOptions($newOptions);
        $orgSelectbox->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_org_label'));
        $orgSelectbox->setRequired(true);
        $orgSelectbox->setValue($technology->getOrganization());
        $this->addElement($orgSelectbox);

        $fieldEmail = new TextField("email");
        $fieldEmail->addValidator(new EmailValidator());
        $fieldEmail->setRequired();
        $fieldEmail->setLabel(OW_Language::getInstance()->text('frmtechnology', "technology_form_email_label"));
        $fieldEmail->setValue($technology->getEmail());
        $this->addElement($fieldEmail);

        $fieldNumber = new TextField('pn');
        $fieldNumber->setRequired();
        $fieldNumber->setLabel(OW_Language::getInstance()->text('frmtechnology', "technology_form_pn_label"));
        $fieldNumber->setValue($technology->getPhoneNumber());
        $this->addElement($fieldNumber);

        $fieldTechArea = new TextField('techArea');
        $fieldTechArea->setRequired();
        $fieldTechArea->setLabel(OW_Language::getInstance()->text('frmtechnology', "technology_form_techArea_label"));
        $fieldTechArea->setValue($technology->getArea());
        $this->addElement($fieldTechArea);

        $fieldPhoto1 = new FileField('image1');
        $fieldPhoto1->addValidator(new FileExtensionValidator(null, array('jpg','jpeg')));
        $fieldPhoto1->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_image1_label'));
        $this->addElement($fieldPhoto1);

        $fieldPhoto2 = new FileField('image2');
        $fieldPhoto2->addValidator(new FileExtensionValidator(null,array('jpg','jpeg')));
        $fieldPhoto2->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_image2_label'));
        $this->addElement($fieldPhoto2);

        $fieldPhoto3 = new FileField('image3');
        $fieldPhoto3->addValidator(new FileExtensionValidator(null,array('jpg','jpeg')));
        $fieldPhoto3->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_image3_label'));
        $this->addElement($fieldPhoto3);

        $fieldPhoto4 = new FileField('image4');
        $fieldPhoto4->addValidator(new FileExtensionValidator(null,array('jpg','jpeg')));
        $fieldPhoto4->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_image4_label'));
        $this->addElement($fieldPhoto4);

        $fieldPhoto5 = new FileField('image5');
        $fieldPhoto5->addValidator(new FileExtensionValidator(null,array('jpg','jpeg')));
        $fieldPhoto5->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_image5_label'));
        $this->addElement($fieldPhoto5);
        if ( !OW::getUser()->isAuthenticated() )
        {
            $fieldCaptcha = new CaptchaField('captcha');
            $fieldCaptcha->setLabel(OW::getLanguage()->text('frmtechnology', 'form_label_captcha'));
            $this->addElement($fieldCaptcha);
        }
        //$supportersTextField = new TextField('supporters');
        //$this->addElement($supportersTextField->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_supporters'))->setValue($technology->getSupporters()));//fix

        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
            BOL_TextFormatService::WS_BTN_HTML,
        );

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $technologyTextArea = new MobileWysiwygTextarea('techDesc','frmtechnology');
        }
        else {
            $technologyTextArea = new WysiwygTextarea('techDesc','frmtechnology', $buttons);
            $technologyTextArea->setSize(WysiwygTextarea::SIZE_L);
        }
        $technologyTextArea->setLabel(OW::getLanguage()->text('frmtechnology', 'technology_form_techDesc_label'));
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $technology->getDescription())));
        if(isset($stringRenderer->getData()['string'])){
            $technologyTextArea->setValue($stringRenderer->getData()['string']);
        }
        $technologyTextArea->setRequired(true);
        $this->addElement($technologyTextArea);

        $deleteImageField = new HiddenField('deleteImage1');
        $deleteImageField->setId('deleteImage1');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        $deleteImageField = new HiddenField('deleteImage2');
        $deleteImageField->setId('deleteImage2');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        $deleteImageField = new HiddenField('deleteImage3');
        $deleteImageField->setId('deleteImage3');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        $deleteImageField = new HiddenField('deleteImage4');
        $deleteImageField->setId('deleteImage4');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        $deleteImageField = new HiddenField('deleteImage5');
        $deleteImageField->setId('deleteImage5');
        $deleteImageField->setValue('false');
        $this->addElement($deleteImageField);

        if ( $technology->getId() != null )
        {
            $text = OW::getLanguage()->text('frmtechnology', 'update');
        }
        else
        {
            $text = OW::getLanguage()->text('frmtechnology', 'save');
        }

        $submit = new Submit('save');
        $submit->addAttribute('onclick', "$('#save_technology_command').attr('value', 'save');");

        $this->addElement($submit->setValue($text));
        $tagService = BOL_TagService::getInstance();
        $tags = array();

        if ( intval($this->technology->getId()) > 0 )
        {
            $arr = $tagService->findEntityTags($this->technology->getId(), 'technology-description');

            foreach ( (!empty($arr) ? $arr : array() ) as $dto )
            {
                $tags[] = $dto->getLabel();
            }
        }

        $tf = new TagsInputField('tf');
        $tf->setLabel(OW::getLanguage()->text('frmtechnology', 'tags_field_label'));
        $tf->setValue($tags);
        $this->addElement($tf);

//        $whoCanInviteSupporter = new RadioField('whoCanInviteSupporter');
//        $whoCanInviteSupporter->setRequired();
//        $whoCanInviteSupporter->setValue($technology->getWhoCanInviteSupporter());
//        $whoCanInviteSupporter->addOptions(
//            array(
//                FRMTECHNOLOGY_BOL_Service::WCIS_MEMBERS => $language->text('frmtechnology', 'form_who_can_invite_support_members'),
//                FRMTECHNOLOGY_BOL_Service::WCIS_CREATOR => $language->text('frmtechnology', 'form_who_can_invite_support_creator')
//            )
//        );
//        $whoCanInviteSupporter->setLabel($language->text('frmtechnology', 'form_who_can_invite_supporter_label'));
//        $this->addElement($whoCanInviteSupporter);

    }

    public function process( OW_ActionController $ctrl )
    {
        $service = FRMTECHNOLOGY_BOL_Service::getInstance();
        $data = $this->getValues();

        $data['title'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['title']));
        $data['userFullName'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['userFullName']));
        $data['position'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['position']));
        $data['grade'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['grade']));
        $data['sn'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['sn']));
        $data['org'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['org']));
        $data['email'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['email']));
        $data['pn'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['pn']));
        $data['techArea'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['techArea']));
        $text = UTIL_HtmlTag::sanitize($data['techDesc']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $text)));
        if(isset($stringRenderer->getData()['string'])){
            $text = $stringRenderer->getData()['string'];
        }

        /* @var $technology FRMTECHNOLOGY_BOL_Technology */
        $this->technology->setTitle($data['title']);
        $this->technology->setUserFullName($data['userFullName']);
        $this->technology->setPosition($data['position']);
        $this->technology->setGrade($data['grade']);
        $this->technology->setStudentNumber($data['sn']);
        $this->technology->setOrganization($data['org']);
        $this->technology->setEmail($data['email']);
        $this->technology->setPhoneNumber($data['pn']);
        $this->technology->setArea($data['techArea']);
        $this->technology->setDescription($text);
        $this->technology->setStatus(FRMTECHNOLOGY_BOL_Service::STATUS_DEACTIVATE);
        $this->technology->setTimeStamp(time());
        $this->processDeleteImage();
        $this->processAddImage();
        $isCreate = empty($this->technology->getId());

        $service->saveTechnology($this->technology);
        // notify admin(s)
        if ( !OW::getUser()->isAuthenticated() )
        {
            $managersId = $service->findUserIdByAuthorizationAction('manage-technology');
            $url = OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $this->technology->getId()));
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($managersId[0]['userId']));
            $notificationParams = array(
                'pluginKey' => 'frmtechnology',
                'action' => 'manage-technology',
                'entityType' => 'manage-technology',
                'entityId' => $this->technology->getId(),
                'userId' => null,
                'time' => time()
            );
            $notificationData = array(
                'string' => array(
                    "key" => 'frmtechnology+notif_add_new_technology',
                    "vars" => array(
                        'title' =>  UTIL_String::truncate(strip_tags($this->technology->getTitle()), 200, "...")
                    )
                ),
                'avatar' => $avatars[$managersId[0]['userId']],
                'content' => '',
                'url' => $url
            );

            // send notifications in batch to userIds
            $userIds=[];
            foreach ( $managersId as $uid ) {
                $userIds[] = $uid['userId'];
            }
            $event = new OW_Event('notifications.batch.add',
                ['userIds'=>$userIds, 'params'=>$notificationParams],
                $notificationData);
            OW::getEventManager()->trigger($event);
        }
        $tags = array();
        if ( intval($this->technology->getId()) > 0 )
        {
            $tags = $data['tf'];
            foreach ($tags as $id => $tag)
            {
                $tags[$id] = UTIL_HtmlTag::stripTags($tag);
            }
        }
        $tagService = BOL_TagService::getInstance();
        $tagService->updateEntityTags($this->technology->getId(), 'technology-description', $tags );
        $tagService->setEntityStatus('technology-description', $this->technology->getId(), true);
            if ($isCreate)
            {
                OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'create_technology_success_msg'));
            }
            else
            {
                OW::getFeedback()->info(OW::getLanguage()->text('frmtechnology', 'edit_technology_success_msg'));
            }
        if ( !OW::getUser()->isAuthorized('frmtechnology', 'manage-technology') )
        {
            $ctrl->redirect(OW::getRouter()->urlForRoute('frmtechnology.index'));
        }else{
            $ctrl->redirect(OW::getRouter()->urlForRoute('frmtechnology.view',array('technologyId'=>$this->technology->getId())));
        }
    }
    public function processDeleteImage(){
        if($_POST['deleteImage1']==1)
        {
            if( !empty($this->technology->getImage1()) )
            {
                $storage = OW::getStorage();
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage1()));
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage1(), false));
                $this->technology->setImage1(null);
            }
        }
        if($_POST['deleteImage2']==1)
        {
            if( !empty($this->technology->getImage2()) )
            {
                $storage = OW::getStorage();
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage2()));
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage2(), false));
                $this->technology->setImage2(null);
            }
        }
        if($_POST['deleteImage3']==1)
        {
            if( !empty($this->technology->getImage3()) )
            {
                $storage = OW::getStorage();
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage3()));
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage3(), false));
                $this->technology->setImage3(null);
            }
        }
        if($_POST['deleteImage4']==1)
        {
            if( !empty($this->technology->getImage4()) )
            {
                $storage = OW::getStorage();
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->$technology->getImage4()));
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->$technology->getImage4(), false));
                $this->technology->setImage4(null);
            }
        }
        if($_POST['deleteImage5']==1)
        {
            if( !empty($this->technology->getImage5()) )
            {
                $storage = OW::getStorage();
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage5()));
                $storage->removeFile(EntryService::getInstance()->generateImagePath($this->technology->getImage5(), false));
                $this->technology->setImage5(null);
            }
        }
    }
    public function processAddImage(){
        if ( !empty($_FILES['image1']['name']) )
        {
            if ( (int) $_FILES['image1']['error'] !== 0 || !is_uploaded_file($_FILES['image1']['tmp_name']) || !UTIL_File::validateImage($_FILES['image1']['name']) )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                //$this->redirect();
                OW::getApplication()->redirect();
            }
            else
            {
                $this->technology->setImage1(FRMSecurityProvider::generateUniqueId());
                $this->service->saveTechnologyImage($_FILES['image1']['tmp_name'],  $this->technology->getImage1());
            }
        }
        if ( !empty($_FILES['image2']['name']) )
        {
            if ( (int) $_FILES['image2']['error'] !== 0 || !is_uploaded_file($_FILES['image2']['tmp_name']) || !UTIL_File::validateImage($_FILES['image2']['name']) )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                //$this->redirect();
                OW::getApplication()->redirect();
            }
            else
            {
                $this->technology->setImage2(FRMSecurityProvider::generateUniqueId());
                $this->service->saveTechnologyImage($_FILES['image2']['tmp_name'],  $this->technology->getImage2());
            }
        }
        if ( !empty($_FILES['image3']['name']) )
        {
            if ( (int) $_FILES['image3']['error'] !== 0 || !is_uploaded_file($_FILES['image3']['tmp_name']) || !UTIL_File::validateImage($_FILES['image3']['name']) )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                //$this->redirect();
                OW::getApplication()->redirect();
            }
            else
            {
                $this->technology->setImage3(FRMSecurityProvider::generateUniqueId());
                $this->service->saveTechnologyImage($_FILES['image3']['tmp_name'],  $this->technology->getImage3());
            }
        }
        if ( !empty($_FILES['image4']['name']) )
        {
            if ( (int) $_FILES['image4']['error'] !== 0 || !is_uploaded_file($_FILES['image4']['tmp_name']) || !UTIL_File::validateImage($_FILES['image4']['name']) )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                //$this->redirect();
                OW::getApplication()->redirect();
            }
            else
            {
                $this->technology->setImage4(FRMSecurityProvider::generateUniqueId());
                $this->service->saveTechnologyImage($_FILES['image4']['tmp_name'],  $this->technology->getImage4());
            }
        }
        if ( !empty($_FILES['image5']['name']) )
        {
            if ( (int) $_FILES['image5']['error'] !== 0 || !is_uploaded_file($_FILES['image5']['tmp_name']) || !UTIL_File::validateImage($_FILES['image5']['name']) )
            {
                OW::getFeedback()->error(OW::getLanguage()->text('base', 'not_valid_image'));
                //$this->redirect();
                OW::getApplication()->redirect();
            }
            else
            {
                $this->technology->setImage5(FRMSecurityProvider::generateUniqueId());
                $this->service->saveTechnologyImage($_FILES['image5']['tmp_name'],  $this->technology->getImage5());
            }
        }
    }
}