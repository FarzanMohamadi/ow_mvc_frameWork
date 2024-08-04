<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsecurityessentials.controllers
 * @since 1.0
 */
class FRMSECURITYESSENTIALS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmsecurityessentials', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmsecurityessentials', 'admin_page_title'));
        $currentSectionFromParams = null;
        if(isset($params['currentSection'])){
            $currentSectionFromParams = $params['currentSection'];
        }
        $sectionsInformation = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getSections($currentSectionFromParams);
        $sections = $sectionsInformation['sections'];
        $currentSection = $sectionsInformation['currentSection'];
        $this->assign('sections',$sections);
        $this->assign('currentSection',$currentSection);
        $config = OW::getConfig();
        if ( !$config->configExists('frmsecurityessentials', 'update_all_plugins_activated') )
        {
            $config->addConfig('frmsecurityessentials', 'update_all_plugins_activated', true);
        }
        $configs = $config->getValues('frmsecurityessentials');

        if($currentSection==1) {

            $form = new Form('settings');
            $form->setAjax();
            $form->setAjaxResetOnSuccess(false);
            $form->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.admin'));
            $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("' . OW::getLanguage()->text("frmsecurityessentials", "settings_successfuly_saved") . '");}else{OW.error("Parser error");}}');

            $idleTime = new TextField('idleTime');
            $idleTime->setLabel($language->text('frmsecurityessentials','idle_time_label'));
            $idleTime->setRequired();
            $idleTime->addValidator(new IntValidator(1));
            $idleTime->setValue($configs['idleTime']);
            $form->addElement($idleTime);

            $viewUserCommentWidget = new CheckboxField('viewUserCommentWidget');
            $viewUserCommentWidget->setLabel(OW::getLanguage()->text("frmsecurityessentials", "view_user_comment_widget"));
            $viewUserCommentWidget->setValue($configs['viewUserCommentWidget']);
            $form->addElement($viewUserCommentWidget);

            $approveUserAfterEditProfile = new CheckboxField('approveUserAfterEditProfile');
            $approveUserAfterEditProfile->setLabel(OW::getLanguage()->text("frmsecurityessentials", "approve_user_after_edit_profile_label"));
            $approveUserAfterEditProfile->setValue($configs['approveUserAfterEditProfile']);
            $form->addElement($approveUserAfterEditProfile);

            $newsFeedShowDefault = new CheckboxField('newsFeedShowDefault');
            $newsFeedShowDefault->setLabel(OW::getLanguage()->text("frmsecurityessentials", "newsFeedShowDefault"));
            $newsFeedShowDefault->setValue($configs['newsFeedShowDefault']);
            $form->addElement($newsFeedShowDefault);

            $passwordRequiredProfile = new CheckboxField('passwordRequiredProfile');
            $passwordRequiredProfile->setLabel(OW::getLanguage()->text("frmsecurityessentials", "passwordRequiredProfile"));
            $passwordRequiredProfile->setValue($configs['passwordRequiredProfile']);
            $form->addElement($passwordRequiredProfile);

            $rememberMeDefaultValue = new CheckboxField('remember_me_default_value');
            $rememberMeDefaultValue->setLabel(OW::getLanguage()->text("frmsecurityessentials", "remember_me_default_value"));
            $rememberMeDefaultValue->setValue($configs['remember_me_default_value']);
            $form->addElement($rememberMeDefaultValue);

            $rememberMeDefaultValue = new CheckboxField('allow_update_all_plugins');
            $rememberMeDefaultValue->setLabel(OW::getLanguage()->text("frmsecurityessentials", "allow_update_all_plugins"));
            $rememberMeDefaultValue->setValue($configs['update_all_plugins_activated']);
            $form->addElement($rememberMeDefaultValue);

            $privacyUpdateNotification = new CheckboxField('privacyUpdateNotification');
            $privacyUpdateNotification->setLabel(OW::getLanguage()->text("frmsecurityessentials", "privacyUpdateNotification"));
            $privacyUpdateNotification->setValue($configs['privacyUpdateNotification']);
            $form->addElement($privacyUpdateNotification);

            $ieMessageEnabled = new CheckboxField('ie_message_enabled');
            $ieMessageEnabled->setLabel(OW::getLanguage()->text("frmsecurityessentials", "ie_message_enabled"));
            $ieMessageEnabled->setValue($configs['ie_message_enabled']);
            $form->addElement($ieMessageEnabled);

            $disableVerifyPeer = new CheckboxField('disable_verify_peer');
            $disableVerifyPeer->setLabel(OW::getLanguage()->text("frmsecurityessentials", "disable_verify_peer"));
            $disableVerifyPeer->setValue($configs['disable_verify_peer']);
            $form->addElement($disableVerifyPeer);

            $disableUserGetOtherSitesContent = new CheckboxField('disable_user_get_other_sites_content');
            $disableUserGetOtherSitesContent->setLabel(OW::getLanguage()->text("frmsecurityessentials", "disable_user_get_other_sites_content"));
            $disableUserGetOtherSitesContent->setValue($configs['disable_user_get_other_sites_content']);
            $form->addElement($disableUserGetOtherSitesContent);

            $userCanChangeAccountType = new CheckboxField('user_can_change_account_type');
            $userCanChangeAccountType->setLabel(OW::getLanguage()->text("frmsecurityessentials", 'user_can_change_account_type'));
            $userCanChangeAccountType->setValue($configs['user_can_change_account_type']);
            $form->addElement($userCanChangeAccountType);

            $submit = new Submit('save');
            $form->addElement($submit);

            $this->addForm($form);

            if (OW::getRequest()->isAjax()) {
                if ($form->isValid($_POST)) {
                    $viewUserCommentWidgetValue = $form->getElement('viewUserCommentWidget')->getValue();
                    $config->saveConfig('frmsecurityessentials', 'viewUserCommentWidget', $viewUserCommentWidgetValue);
                    $this->updateUserCommentWidget($viewUserCommentWidgetValue);
                    $config->saveConfig('frmsecurityessentials', 'newsFeedShowDefault', $form->getElement('newsFeedShowDefault')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'passwordRequiredProfile', $form->getElement('passwordRequiredProfile')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'remember_me_default_value', $form->getElement('remember_me_default_value')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'idleTime', $form->getElement('idleTime')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'approveUserAfterEditProfile', $form->getElement('approveUserAfterEditProfile')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'update_all_plugins_activated', $form->getElement('allow_update_all_plugins')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'privacyUpdateNotification', $form->getElement('privacyUpdateNotification')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'ie_message_enabled', $form->getElement('ie_message_enabled')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'disable_verify_peer', $form->getElement('disable_verify_peer')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'disable_user_get_other_sites_content', $form->getElement('disable_user_get_other_sites_content')->getValue());
                    $config->saveConfig('frmsecurityessentials', 'user_can_change_account_type', $form->getElement('user_can_change_account_type')->getValue());
                    exit(json_encode(array('result' => true)));
                }
            }
        }else if($currentSection==2){
            if(class_exists('PRIVACY_BOL_ActionService')) {

                $privacyForm = new Form('privacyForm');
                $privacyForm->setAjax(false);
                $privacyForm->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => $currentSection)));
                $actionSubmit = new Submit('submit');
                $actionSubmit->addAttribute('class', 'ow_button ow_ic_save');
                $privacyForm->addElement($actionSubmit);

                $actionValuesEvent= new BASE_CLASS_EventCollector( PRIVACY_BOL_ActionService::EVENT_GET_PRIVACY_LIST );
                OW::getEventManager()->trigger($actionValuesEvent);
                $data = $actionValuesEvent->getData();

                $actionValuesInfo = empty($data) ? array() : $data;
                usort($actionValuesInfo, array($this, "sortPrivacyOptions"));

                $optionsList = array();
                // -- sort action values
                foreach( $actionValuesInfo as $value )
                {
                    $optionsList[$value['key']] = $value['label'];
                }

                $resultList = array();
                $actionList = PRIVACY_BOL_ActionService::getInstance()->findAllAction();

                foreach ($actionList as $action) {

                    /* @var $action PRIVACY_CLASS_Action */
                    if ( !empty( $action->label ) )
                    {
                        $formElement = new Selectbox($action->key);
                        $formElement->setLabel($action->label);
                        $formElement->setOptions($optionsList);
                        $formElement->setRequired(true);

                        $formElement->setDescription('');
                        $privacyValue = OW::getConfig()->getValue('frmsecurityessentials',$action->key);
                        if(!isset($privacyValue)){
                            $formElement->setDescription(OW::getLanguage()->text("frmsecurityessentials", "privacy_value_empty"));
                            $formElement->setValue(null);
                            $formElement->setHasInvitation(true);
                        }else{
                            $formElement->setValue($privacyValue);
                            $formElement->setHasInvitation(false);
                        }

                        $privacyForm->addElement($formElement);

                        $resultList[$action->key] = $action->key;
                    }
                }

                $this->addForm($privacyForm);
                $this->assign('actionList', $resultList);

                if (OW::getRequest()->isPost()) {
                    if ($privacyForm->isValid($_POST)) {
                        $values = $privacyForm->getValues();
                        foreach ($actionList as $action) {
                            $value = $values[$action->key];
                            if ($value != null) {
                                OW::getConfig()->saveConfig('frmsecurityessentials', $action->key, $value);
                            }
                        }
                        OW::getFeedback()->info(OW::getLanguage()->text("frmsecurityessentials", "settings_successfuly_saved"));
                        $this->redirect();
                    }
                }
            }else{
                $this->assign('plugin_privacy_not_exist_description', OW::getLanguage()->text("frmsecurityessentials", "plugin_privacy_not_exist_description"));
            }
        }else if($currentSection==3){
            throw new Redirect404Exception();
        }else if($currentSection==4) {

            $language = OW::getLanguage();
            $changeUserPasswordForm = new Form('changeUserPasswordForm');
            $changeUserPasswordForm->setAjax(false);
            $changeUserPasswordForm->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => $currentSection)));

            $actionSubmit = new Submit('save');
            $actionSubmit->addAttribute('class', 'ow_button ow_ic_save');

            $changeUserPasswordForm->addElement($actionSubmit);

            $userNameField = new TextField('userName');
            $userNameField->setRequired(true);
            $userNameField->setLabel($language->text('base','questions_question_username_label'));
            $changeUserPasswordForm->addElement($userNameField);

            $changedPasswordField = new PasswordField('changedPassword');
            $changedPasswordField->setLabel($language->text('frmsecurityessentials','password'));
            $changedPasswordField->setRequired(true);
            $changeUserPasswordForm->addElement($changedPasswordField);

            $this->addForm($changeUserPasswordForm);

            if (OW::getRequest()->isPost()) {
                if ($changeUserPasswordForm->isValid($_POST)) {
                    $values = $changeUserPasswordForm->getValues();
                    $user = BOL_UserService::getInstance()->findByUsername($values['userName']);
                    if (isset($user)) {
                        if(strcmp($user->salt,'')==0) {
                            $salt = md5(UTIL_String::getRandomString(8, 5));
                            BOL_UserDao::getInstance()->updateSaltByUserId((int)$user->id, $salt);
                        }
                        BOL_UserDao::getInstance()->updatePassword($user->getId(), BOL_UserService::getInstance()->hashPassword($values['changedPassword'],$user->id));
                        OW::getEventManager()->trigger(new OW_Event('user.password.updated', array('user'=>$user)));
                        OW::getFeedback()->info($language->text('frmsecurityessentials', 'password_changed_successfully'));

                    } else {
                        OW::getFeedback()->error($language->text('admin', 'permissions_feedback_user_not_found'));
                    }
                }
            }
        }else if ($currentSection==5){
            $this->setPageHeading(OW::getLanguage()->text('frmsecurityessentials', 'admin_page_title'));
            $this->setPageTitle(OW::getLanguage()->text('frmsecurityessentials', 'admin_page_heading'));
            $config =  OW::getConfig();
            $language = OW::getLanguage();

            $form = new Form('form');
            $form->setAjax();
            $form->setAjaxResetOnSuccess(false);
            $form->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => $currentSection)));
            $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmsecurityessentials', 'settings_successfuly_saved').'\')  }  }');

            $validIps = new Textarea('validIps');
            $validIps->setLabel($language->text('frmsecurityessentials', 'input_settings_valid_ip_list_label'));
            $validIps->setDescription($language->text('frmsecurityessentials', 'input_settings_valid_ip_list_desc'));
            $form->addElement($validIps);

            $submit = new Submit('save');
            $form->addElement($submit);
            $this->addForm($form);

            $userIP = OW::getRequest()->getRemoteAddress();
            if ($userIP == '::1' || empty($userIP)) {
                $userIP = '127.0.0.1';
            }
            $this->assign('userIP',$userIP);

            if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
            {
                $data = $form->getValues();
                if (!empty($data['validIps']) )
                {
                    $validIpList = array_unique(preg_split('/' . PHP_EOL . '/', $data['validIps']));
                    if (!$config->configExists('frmsecurityessentials', 'valid_ips'))
                    {
                        $config->addConfig('frmsecurityessentials', 'valid_ips', json_encode(array_map('trim', $validIpList)));
                    }else {
                        $config->saveConfig('frmsecurityessentials', 'valid_ips', json_encode(array_map('trim', $validIpList)));
                    }
                }else{
                    $config->deleteConfig('frmsecurityessentials', 'valid_ips');
                }

                exit(json_encode(array('result' => true)));
            }
            if($config->configExists('frmsecurityessentials', 'valid_ips')) {
                $validIps->setValue(implode(PHP_EOL, json_decode($config->getValue('frmsecurityessentials', 'valid_ips'))));
            }
        } else if ($currentSection == 6){
            $this->setPageHeading(OW::getLanguage()->text('frmsecurityessentials', 'admin_page_title'));
            $this->setPageTitle(OW::getLanguage()->text('frmsecurityessentials', 'admin_page_heading'));
            $config =  OW::getConfig();
            $language = OW::getLanguage();

            $form = new Form('profile_privacy_form');
            $form->setAjax();
            $form->setAjaxResetOnSuccess(false);
            $form->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => $currentSection)));
            $form->bindJsFunction(Form::BIND_SUCCESS,'function( data ){ if(data && data.result){OW.info(\''.$language->text('frmsecurityessentials', 'settings_successfuly_saved').'\')  }  }');

            $allQuestions = BOL_QuestionDao::getInstance()->findAll();
            $formFieldsKey = array();
            $validQuestions = array();
            foreach ($allQuestions as $q) {
                if ($q->onView == 1) {
                    $validQuestions[] = $q;
                }
            }
            foreach ($validQuestions as $q) {
                $actionKey = $q->name;
                $formElement = new Selectbox($actionKey);
                $label = OW::getLanguage()->text('base', 'questions_question_' . $actionKey . '_label');
                $formElement->setLabel($label);

                $optionsList = array(
                    'friends_only' => OW::getLanguage()->text("frmsecurityessentials", "privacy_friends"),
                    'everybody' => OW::getLanguage()->text("frmsecurityessentials", "privacy_everybody"),
                    'only_for_me' => OW::getLanguage()->text("frmsecurityessentials", "privacy_only_for_me"),
                );
                $fieldValue = $config->getValue('frmsecurityessentials', 'privacy_profile_field_'.$actionKey);
                if (isset($fieldValue) && $fieldValue != null) {
                    $formElement->setValue($fieldValue);
                } else {
                    $formElement->setValue('friends');
                }
                $formElement->setOptions($optionsList);
                $formElement->setHasInvitation(false);
                $form->addElement($formElement);
                $formFieldsKey[] = $actionKey;
            }
            $this->assign('formFieldsKey', $formFieldsKey);

            $submit = new Submit('save');
            $form->addElement($submit);
            $this->addForm($form);

            if ( OW::getRequest()->isAjax() &&  OW::getRequest()->isPost() && $form->isValid($_POST) )
            {
                $data = $form->getValues();
                foreach ($validQuestions as $q) {
                    $actionKey = $q->name;
                    $config->saveConfig('frmsecurityessentials', 'privacy_profile_field_'.$actionKey, $data[$actionKey]);
                }
                exit(json_encode(array('result' => true)));
            }
        } else if($currentSection == 7){
            $form = new Form("update-system-code");
            $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

            $file = new FileField("file");
            $form->addElement($file);

            $submit = new Submit("save");
            $submit->setValue($language->text("admin", "plugins_manage_add_submit_label"));
            $form->addElement($submit);

            $this->addForm($form);

            $errors = array();

            if ( OW::getRequest()->isPost() )
            {
                if ( isset($_POST['file']) && $form->isValid($_POST))
                {
                    $data = $form->getValues();
                    $result = UTIL_File::checkUploadedFile($_FILES["file"]);

                    if ( !$result["result"] )
                    {
                        OW::getFeedback()->error($result["message"]);
                        $this->redirect();
                    }


                    $tempFile = OW_DIR_ROOT .UTIL_String::getRandomStringWithPrefix("update_system_code"). ".zip";
                    if ( !OW::getStorage()->moveFile($_FILES["file"]["tmp_name"], $tempFile) )
                    {
                        OW::getFeedback()->error($language->text("admin", "manage_plugin_add_move_file_error"));
                        $this->redirect();
                    }

                    $zip = new ZipArchive();

                    if ( $zip->open($tempFile) === true )
                    {
                        for($i = 0; $i < $zip->numFiles; $i++)
                        {
                            clearstatcache();

                            $filePath = $zip->getNameIndex($i);
                            $fileFullPath = OW_DIR_ROOT . $filePath;
                            $fileDirectory = pathinfo($fileFullPath, PATHINFO_DIRNAME);

                            //Check if the file exists and is writable
                            //or the file does not exists and its directory is writable
                            if(!preg_match("/(.*)\/$/", $fileFullPath)) {
                                if (file_exists($fileFullPath)) {
                                    if (!is_writable($fileFullPath)) {
                                        $errors[] = $filePath;
                                    }
                                } else if (!is_writable($fileDirectory)) {
                                        $errors[] = pathinfo($filePath, PATHINFO_DIRNAME);
                                }
                            }
                        }

                        if (count($errors) == 0) {
                            $zip->extractTo(".");
                            OW::getFeedback()->info($language->text("frmsecurityessentials", "system_code_updated_successfully"));
                        } else{
                            OW::getFeedback()->error($language->text("frmsecurityessentials", "system_code_permission_denied"));
                        }
                        $zip->close();
                    }
                    else
                    {
                        OW::getFeedback()->error($language->text("frmsecurityessentials", "extract_file_error"));
                        $this->redirect();
                    }
//
                    OW::getStorage()->removeFile($tempFile);

                }
            }
            $this->assign('errors', $errors);

            // reset services
            $form = new Form("reset-services");
            $submit = new Submit("reset");
            $submit->setValue($language->text("frmsecurityessentials", "reset_services_button"));
            $form->addElement($submit);
            $this->addForm($form);

            if ( OW::getRequest()->isPost() )
            {
                if ( $form->isValid($_POST) && isset($_POST['reset'])) {
                    OW::getEventManager()->trigger(new OW_Event('base.code.change'));
                    OW::getFeedback()->info($language->text("admin", "main_settings_updated"));
                }
            }
        }else if($currentSection == 8){
            $warningAlertform = new Form("warningAlert");
            $warningAlertform->setAjax(false);
            $warningAlertform->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.admin.currentSection', array('currentSection' => $currentSection)));
           

            $warningAlertEnable = new CheckboxField('warningAlert_enable');
            $warningAlertEnable->setLabel($language->text('frmsecurityessentials', 'warningAlert_enable_label'));
            $warningAlertEnable->setDescription($language->text('frmsecurityessentials', 'warningAlert_enable_desc'));
            $warningAlertform->addElement($warningAlertEnable);
    
            $warningAlertTypes = new RadioField('warningAlert_types');
            $warningAlertTypes->setLabel($language->text('frmsecurityessentials', 'warningAlert_types_label'));
            $warningTypesEnum=array('banner', 'modal' , 'both' );
            foreach( $warningTypesEnum as $key => $wt )
            {
              
            $warningAlertTypes->addOption($key+1,$wt);
                
            }
            $warningAlertTypes->setDescription($language->text('frmsecurityessentials', 'warningAlert_types_desc'));
            $warningAlertform->addElement($warningAlertTypes);
    
    
            $entry = new Textarea('warningAlert_text');
            $entry->setLabel($language->text('frmsecurityessentials', 'warningAlert_text_label'));
            $entry->setDescription($language->text('frmsecurityessentials', 'warningAlert_text_desc'));
            $warningAlertform->addElement($entry);

            $action = new TextField('warningAlert_action');
            $action->setLabel($language->text('frmsecurityessentials', 'warningAlert_action_label'));
            $action->setDescription($language->text('frmsecurityessentials', 'warningAlert_action_desc'));
            $warningAlertform->addElement($action);

        
        $actionSubmit = new Submit('save');
        $actionSubmit->addAttribute('class', 'ow_button ow_ic_save');
        $warningAlertform->addElement($actionSubmit);
        $this->addForm($warningAlertform);

        if (OW::getRequest()->isPost()) {
        if ( $warningAlertform->isValid($_POST) )
        {
        $data = $warningAlertform->getValues();
        $langService = BOL_LanguageService::getInstance();
        $key = $langService->findKey('admin', 'warningAlert_text_value');

        if ( $key === null )
        {
            $prefix = $langService->findPrefix('admin');
            $key = new BOL_LanguageKey();
            $key->setKey('warningAlert_text_value');
            $key->setPrefixId($prefix->getId());
            $langService->saveKey($key);
        }

        $value = $langService->findValue($langService->getCurrent()->getId(), $key->getId());

        if ( $value === null )
        {
            $value = new BOL_LanguageValue();
            $value->setKeyId($key->getId());
            $value->setLanguageId($langService->getCurrent()->getId());
        }

        $value->setValue($data['warningAlert_text']);
        $langService->saveValue($value);
        // set timeStamp
        $key = $langService->findKey('admin', 'warningAlert_timeStamp');
        if ( $key === null )
        {
            $prefix = $langService->findPrefix('admin');
            $key = new BOL_LanguageKey();
            $key->setKey('warningAlert_timeStamp');
            $key->setPrefixId($prefix->getId());
            $langService->saveKey($key);
        }

        
        $value = $langService->findValue($langService->getCurrent()->getId(), $key->getId());

        if ( $value === null )
        {
            $value = new BOL_LanguageValue();
            $value->setKeyId($key->getId());
            $value->setLanguageId($langService->getCurrent()->getId());
        }

        $value->setValue(time());
        $langService->saveValue($value);
       // set action
       $key = $langService->findKey('admin', 'warningAlert_action_value');
       if ( $key === null )
       {
           $prefix = $langService->findPrefix('admin');
           $key = new BOL_LanguageKey();
           $key->setKey('warningAlert_action_value');
           $key->setPrefixId($prefix->getId());
           $langService->saveKey($key);
       }

       
       $value = $langService->findValue($langService->getCurrent()->getId(), $key->getId());

       if ( $value === null )
       {
           $value = new BOL_LanguageValue();
           $value->setKeyId($key->getId());
           $value->setLanguageId($langService->getCurrent()->getId());
       }
       $value->setValue($data['warningAlert_action']);
       $langService->saveValue($value);
       //
        $valueWarningAlert=0;
        if($data['warningAlert_enable']){
            $valueWarningAlert=($data['warningAlert_types'])?$data['warningAlert_types']:1;
        }
        OW::getConfig()->saveConfig('base', 'warningAlert',
        $valueWarningAlert,'');

        OW::getFeedback()->info($language->text("frmsecurityessentials", "warningAlert_updated"));

    } 
    
}
     //warningAlert
     $warningAlertform->getElement('warningAlert_text')->setValue($language->text('admin', 'warningAlert_text_value'));
     $warningAlertform->getElement('warningAlert_action')->setValue($language->text('admin', 'warningAlert_action_value'));
     $getAlertValue= OW::getConfig()->getValue('base', 'warningAlert');
     $warningAlertform->getElement('warningAlert_enable')->setValue((bool)$getAlertValue);
     if( $getAlertValue > 0){
       
     $warningAlertform->getElement('warningAlert_types')->setValue($getAlertValue);

     }

    }
}
    public function updateUserCommentWidget($enable){
        $widgetService = BOL_ComponentAdminService::getInstance();
        if($enable){
            $widget = $widgetService->addWidget('BASE_CMP_ProfileWallWidget');
            $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_PROFILE);
        }else{
            BOL_ComponentAdminService::getInstance()->deleteWidget('BASE_CMP_ProfileWallWidget');
        }
    }

    public function generateRandomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()+';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    private function sortPrivacyOptions( $a, $b )
    {
        if ( $a["sortOrder"] == $b["sortOrder"]  )
        {
            return 0;
        }

        return $a["sortOrder"] < $b["sortOrder"] ? -1 : 1;
    }
}

class FRMSECURITYESSENTIALS_CustomizationForm extends Form
{

    public function __construct(  )
    {
        parent::__construct('homePageCustomizationForm');

        $language = OW::getLanguage();

        $btn = new Submit('save');
        $btn->setValue($language->text('frmsecurityessentials', 'save_customization_btn_label'));
        $this->addElement($btn);
    }

    public function process( $data, $types )
    {
        $changed = false;
        $configValue = json_decode(OW::getConfig()->getValue('frmsecurityessentials', 'disabled_home_page_action_types'), true);
        $typesToSave = array();

        foreach ( $types as $type )
        {
            $typesToSave[$type] = isset($data[$type]);
            if ( !isset($configValue[$type]) || $configValue[$type] !== $typesToSave[$type] )
            {
                $changed = true;
            }
        }

        $jsonValue = json_encode($typesToSave);
        OW::getConfig()->saveConfig('frmsecurityessentials', 'disabled_home_page_action_types', $jsonValue);

        return $changed;
    }
}
