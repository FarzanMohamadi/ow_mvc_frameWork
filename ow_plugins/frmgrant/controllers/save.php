<?php
class FRMGRANT_CTRL_Save extends OW_ActionController
{
    private $service;

    public function __construct()
    {
        $this->service = FRMGRANT_BOL_Service::getInstance();
    }

    public function index($params = array())
    {
        if (OW::getRequest()->isAjax()) {
            exit();
        }
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        if (!OW::getUser()->isAuthorized('frmgrant', 'manage-grant') && !OW::getUser()->isAdmin()) {
            throw new Redirect404Exception();
        }

        $this->setPageHeadingIconClass('ow_ic_write');

        $id = empty($params['grantId']) ? 0 : $params['grantId'];
        $grantDao = FRMGRANT_BOL_GrantDao::getInstance();

        $isEdit = false;
        if (intval($id) > 0) {
            $grant = $grantDao->findById($id);
            if (!isset($grant)) {
                throw new Redirect404Exception();
            }
            $this->assign('grantUrl', OW::getRouter()->urlForRoute('frmgrant.view', array('grantId' => $id)));
            $isEdit = true;

        } else {
            $grant = new FRMGRANT_BOL_Grant();
        }
        $this->assign('isEdit', $isEdit);

        $form = new GrantForm($grant);

        if (OW::getRequest()->isPost() && (!empty($_POST['command']) && in_array($_POST['command'], array('save'))) && $form->isValid($_POST)) {
            $form->process($this);
        }

        $this->addForm($form);

        if (intval($id) > 0) {
            $this->setPageHeading(OW::getLanguage()->text('frmgrant', 'edit_grant_page_heading'));
            OW::getDocument()->setTitle(OW::getLanguage()->text('frmgrant', 'meta_title_edit_grant'));
            OW::getDocument()->setDescription(OW::getLanguage()->text('frmgrant', 'meta_description_edit_grant'));
        } else {
            $this->setPageHeading(OW::getLanguage()->text('frmgrant', 'save_grant_page_heading'));
            OW::getDocument()->setTitle(OW::getLanguage()->text('frmgrant', 'meta_title_new_grant'));
            OW::getDocument()->setDescription(OW::getLanguage()->text('frmgrant', 'meta_description_new_grant'));
        }
    }

    public function delete($params)
    {
        if (empty($params['grantId'])) {
            throw new Redirect404Exception();
        }

        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        $grantId = $params['grantId'];
        $grant = $this->service->findGrantById($grantId);

        if (empty($grant)) {
            throw new Redirect404Exception();
        }

        $isModerator = OW::getUser()->isAuthorized('frmgrant', 'manage-grant');

        if (!$isModerator) {
            throw new Redirect404Exception();
        }
        $this->service->deleteGrant($grantId);
        OW::getFeedback()->info(OW::getLanguage()->text('frmgrant', 'delete_grant_success_massage'));
        $this->redirect(OW::getRouter()->urlForRoute('frmgrant.index'));
    }
}
class GrantForm extends Form
{

    private $grant;
    private $service;
    private $config;

    public function __construct( FRMGRANT_BOL_Grant $grant)
    {
        parent::__construct('save');

        $language = OW::getLanguage();
        $this->service = FRMGRANT_BOL_Service::getInstance();
        $this->grant = $grant;
        $this->config =  OW::getConfig();
        $this->setMethod('post');

        $titleTextField = new TextField('title');
        $titleTextField->setLabel($language->text('frmgrant', 'grant_form_title_label'));
        $titleTextField->setRequired(true);
        $titleTextField->setValue($grant->getTitle());
        $this->addElement($titleTextField);

        $professorTextField = new TextField('professor');
        $professorTextField->setLabel($language->text('frmgrant', 'grant_form_professor_label'));
        $professorTextField->setValue($grant->getProfessor());
        $this->addElement($professorTextField);

        $collegeSelectBox= new Selectbox('college');
        $options = json_decode($this->config->getValue('frmgrant', 'collegeAndFields_list_setting'));
        $newOptions = array();
        foreach ($options as $key => $value){
            $newOptions[$value] = $value;
        }
        $collegeSelectBox->setOptions($newOptions);
        $collegeSelectBox->setLabel($language->text('frmgrant', 'grant_form_collegeAndField_label'));
        $collegeSelectBox->setValue($grant->getCollegeAndField());
        $this->addElement($collegeSelectBox);

        $laboratory = new TextField('laboratory');
        $laboratory->setLabel($language->text('frmgrant', 'grant_form_laboratory_label'));
        $laboratory->setValue($grant->getLaboratory());
        $this->addElement($laboratory);

        $startedYearTextField = new TextField('startedYear');
        $startedYearTextField->setLabel($language->text('frmgrant', 'grant_form_startedYear_label'));
        $startedYearTextField->setValue($grant->getStartedYear());
        $this->addElement($startedYearTextField);

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
            $descriptionTextArea = new MobileWysiwygTextarea('description','frmgrant');
        }
        else {
            $descriptionTextArea = new WysiwygTextarea('description','frmgrant', $buttons);
            $descriptionTextArea->setSize(WysiwygTextarea::SIZE_L);
        }
        $descriptionTextArea->setLabel(OW::getLanguage()->text('frmgrant', 'grant_form_description_label'));
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $grant->getDescription())));
        if(isset($stringRenderer->getData()['string'])){
            $descriptionTextArea->setValue($stringRenderer->getData()['string']);
        }
        $this->addElement($descriptionTextArea);

        if ( $grant->getId() != null )
        {
            $text = $language->text('frmgrant', 'update');
        }
        else
        {
            $text = $language->text('frmgrant', 'save');
        }

        $submit = new Submit('save');
        $submit->addAttribute('onclick', "$('#save_grant_command').attr('value', 'save');");
        $this->addElement($submit->setValue($text));
        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
    }

    public function process( OW_ActionController $ctrl )
    {
        $service = FRMGRANT_BOL_Service::getInstance();
        $data = $this->getValues();

        $data['title'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['title']));
        $data['professor'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['professor']));
        $data['college'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['college']));
        $data['laboratory'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['laboratory']));
        $data['startedYear'] = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['startedYear']));
        $text = UTIL_HtmlTag::sanitize($data['description']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE,array('string' => $text)));
        if(isset($stringRenderer->getData()['string'])){
            $text = $stringRenderer->getData()['string'];
        }

        $this->grant->setTitle($data['title']);
        $this->grant->setProfessor($data['professor']);
        $this->grant->setCollegeAndField($data['college']);
        $this->grant->setLaboratory($data['laboratory']);
        $this->grant->setStartedYear($data['startedYear']);
        $this->grant->setDescription($text);
        $this->grant->setTimeStamp(time());
        $isCreate = empty($this->grant->getId());

        $service->saveGrant($this->grant);

        if ($isCreate)
        {
            OW::getFeedback()->info(OW::getLanguage()->text('frmgrant', 'create_grant_success_msg'));
        }
        else
        {
            OW::getFeedback()->info(OW::getLanguage()->text('frmgrant', 'edit_grant_success_msg'));
        }

        $ctrl->redirect(OW::getRouter()->urlForRoute('frmgrant.index', array('grantId' => $this->grant->getId())));
    }

}