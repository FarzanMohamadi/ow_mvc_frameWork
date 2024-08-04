<?php
/**
 * Admin page
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcontactus.controllers
 * @since 1.0
 */
class FRMCONTACTUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function dept($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcontactus', 'admin_contactus_settings_heading'));
       $service = $this->getService();
       $sectionId = 1;
        if(isset($params['sectionId'])){
            $sectionId = $params['sectionId'];
        }
        $this->setPageTitle(OW::getLanguage()->text('frmcontactus', 'admin_dept_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmcontactus', 'admin_dept_heading'));
        if($sectionId==2) {
            $this->assign('sectionId', 2);
            $contactEmails = array();
            $editUrls = array();
            $deleteUrls = array();
            $contacts = FRMCONTACTUS_BOL_Service::getInstance()->getDepartmentList();
            foreach ($contacts as $contact) {
                /* @var $contact FRMCONTACTUS_BOL_Department */
                $contactEmails[$contact->id]['name'] = $contact->id;
                $contactEmails[$contact->id]['email'] = $contact->email;
                $contactEmails[$contact->id]['label'] = $contact->label;
                $deleteUrls[$contact->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $contact->id));
                $editUrls[$contact->id] = "OW.ajaxFloatBox('FRMCONTACTUS_CMP_EditItemFloatBox', {id: ".$contact->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmcontactus', 'edit_item_page_title')."'})";
            }
            $this->assign('contacts', $contactEmails);
            $this->assign('deleteUrls', $deleteUrls);
            $this->assign('editUrls', $editUrls);

            $form = new Form('add_dept');
            $this->addForm($form);

            $fieldEmail = new TextField('email');
            $fieldEmail->setRequired();
            $fieldEmail->addValidator(new EmailValidator());
            $fieldEmail->setInvitation(OW::getLanguage()->text('frmcontactus', 'label_invitation_email'));
            $fieldEmail->setHasInvitation(true);
            $form->addElement($fieldEmail);


            $fieldLabel = new TextField('label');
            $fieldLabel->setRequired();
            $fieldLabel->setInvitation(OW::getLanguage()->text('frmcontactus', 'label_invitation_label'));
            $fieldLabel->setHasInvitation(true);
            $validator = new FRMCONTACTUS_CLASS_LabelValidator();
            $language = OW::getLanguage();
            $validator->setErrorMessage($language->text('frmcontactus', 'label_error_already_exist'));
            $fieldLabel->addValidator($validator);
            $form->addElement($fieldLabel);

            $submit = new Submit('add');
            $submit->setValue(OW::getLanguage()->text('frmcontactus', 'form_add_dept_submit'));
            $form->addElement($submit);
            $this->assign('sections', $service->getAdminSections($sectionId));
            if (OW::getRequest()->isPost()) {
                if ($form->isValid($_POST)) {
                    $data = $form->getValues();
                        FRMCONTACTUS_BOL_Service::getInstance()->addDepartment($data['email'], $data['label']);
                        $this->redirect();
                }
            }
      }
        else if($sectionId==1)
        {
            $this->assign('sectionId', 1);
            $formSettings = new Form('settings');
            $formSettings->setAjax();
            $formSettings->setAjaxResetOnSuccess(false);
            $formSettings->setAction(OW::getRouter()->urlForRoute('frmcontactus.admin'));
            $formSettings->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("Settings successfuly saved");}else{OW.error("Parser error");}}');
            $formData = new Form('formData');
            $formData->setAction(OW::getRouter()->urlForRoute('frmcontactus.admin.data'));
            $config = OW::getConfig();
            $configs = $config->getValues('frmcontactus');
            $contacts = FRMCONTACTUS_BOL_Service::getInstance()->getDepartmentList();
            $optionsDepartments = array();
            foreach ($contacts as $contact) {
                $optionsDepartments[$contact->label] =  $contact->label;
            }

            $departments = new Selectbox('departments');
            $departments->setHasInvitation(false);
            $departments->setOptions($optionsDepartments);
            $departments->setRequired();
            if(isset($configs['departments'])) {
                $departments->setValue($configs['departments']);
            }
            $formData->addElement($departments);
            $submitFormData = new Submit('showFormData');
            $submitFormData->setValue(OW::getLanguage()->text("frmcontactus", "showFormData"));
            $formData->addElement($submitFormData);

            $this->addForm($formData);
            $this->assign('sections', $service->getAdminSections($sectionId));
            if ( OW::getRequest()->isAjax() )
            {
                if ( $formData->isValid($_POST) )
                {
                    $data = $formData->getValues();
                    FRMCONTACTUS_BOL_Service::getInstance()->addDepartment($data['email'], $data['label']);
                    $this->redirect();
                }
            }
        }
        else if($sectionId=='new')
        {
            $form = new Form('add_adminComment');
            $this->addForm($form);
            $config = OW::getConfig();
            $configs = $config->getValues('frmcontactus');
            $buttons = array(
                BOL_TextFormatService::WS_BTN_BOLD,
                BOL_TextFormatService::WS_BTN_ITALIC,
                BOL_TextFormatService::WS_BTN_UNDERLINE,
                BOL_TextFormatService::WS_BTN_IMAGE,
                BOL_TextFormatService::WS_BTN_LINK,
                BOL_TextFormatService::WS_BTN_ORDERED_LIST,
                BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
                BOL_TextFormatService::WS_BTN_MORE,
                BOL_TextFormatService::WS_BTN_SWITCH_HTML,
                BOL_TextFormatService::WS_BTN_HTML,
                BOL_TextFormatService::WS_BTN_VIDEO
            );
            $this->assign('sectionId', 'new');
            $commentTextArea = new WysiwygTextarea('comment','frmcontactus', $buttons);
            $commentTextArea->setSize(WysiwygTextarea::SIZE_L);
            $commentTextArea->setLabel(OW::getLanguage()->text('frmcontactus', 'save_form_lbl_entry'));
            $commentTextArea->setValue($configs['adminComment']);
            $form->addElement($commentTextArea);

            $submitFormData = new Submit('add');
            $submitFormData->setValue(OW::getLanguage()->text("frmcontactus", "addAdminComment"));
            $form->addElement($submitFormData);

            $this->addForm($form);
            $this->assign('sections', $service->getAdminSections($sectionId));
            if (OW::getRequest()->isPost()&& $form->isValid($_POST))
            {
                $data = $form->getValues();
                $eventForEnglishFieldSupport = new OW_Event('frmmultilingualsupport.store.multilingual.data', array('entityId' => 1,'entityType'=>'frmcontactus'));
                OW::getEventManager()->trigger($eventForEnglishFieldSupport);
                $text = UTIL_HtmlTag::sanitize($data['comment']);
                if($config->configExists('frmcontactus','adminComment'))  {
                    $config->saveConfig('frmcontactus', 'adminComment', $text);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('frmcontactus', 'modified_successfully'));
            }
        }
    }

    public function data($params)
    {
        if(!isset($_POST['departments']) && !isset($_GET["departments"])){
            $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.admin'));
        }else {
            $url = OW::getRouter()->urlForRoute('frmcontactus.admin.data');
            $page = (!empty($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
            $count = 10;
            $first = ($page - 1) * $count;
            if (isset($_POST['departments']))
                $department = $_POST["departments"];
            else if (isset($_GET["departments"]))
                $department = $_GET["departments"];
            $informationCount = FRMCONTACTUS_BOL_Service::getInstance()->getCountByDepartment($department);
            $this->assign('informationCount',$informationCount);
            $information = $this->getDepartmentsData($department, $first, $count);
            $extraParams['departments'] = $department;
            $url = OW::getRequest()->buildUrlQueryString($url, $extraParams);
            $paging = new BASE_CMP_Paging($page, ceil($informationCount / $count), 5, "", $url);
            $this->addComponent('paging', $paging);
            $this->assign('tableData', $information['data']);
            $this->assign('returnToSetting', OW::getRouter()->urlForRoute('frmcontactus.admin'));
        }
    }

    /**
     * @param $department
     * @param $numberOfData
     * @return array
     */
    public function getDepartmentsData($department, $first=0 ,$count=10)
    {
        $data = FRMCONTACTUS_BOL_Service::getInstance()->getUserInformationListByLabel($department,$first,$count);
        return $data;
    }
    public function getService(){
        return FRMCONTACTUS_BOL_Service::getInstance();
    }


    public function delete( $params )
    {
        if ( isset($params['id']) )
        {
            $department = FRMCONTACTUS_BOL_Service::getInstance()->getDepartmentByID((int) $params['id']);
            FRMCONTACTUS_BOL_Service::getInstance()->deleteUserInformationBylabel(trim($department->label));
            FRMCONTACTUS_BOL_Service::getInstance()->deleteDepartment((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.admin'));
    }

    public function editItem()
    {
        $form = $this->getService()->getDepartmentEditForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
            $this->getService()->editDepartment($form->getElement('id')->getValue(), $form->getElement('email')->getValue(), $form->getElement('label')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmcontactus', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.admin'));
        }else{
            if($form->getErrors()['label'][0]!=null) {
                OW::getFeedback()->error($form->getErrors()['label'][0]);
            }
            $this->redirect(OW::getRouter()->urlForRoute('frmcontactus.admin'));
        }
    }
}
