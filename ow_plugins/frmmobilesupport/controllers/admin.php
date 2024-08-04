<?php
class FRMMOBILESUPPORT_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        if ( OW::getRequest()->isAjax() )
        {
            return;
        }

        $lang = OW::getLanguage();

        $this->setPageHeading($lang->text('frmmobilesupport', 'admin_settings_title'));
        $this->setPageTitle($lang->text('frmmobilesupport', 'admin_settings_title'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    public function versions(){
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();

        $this->assign("sections", $service->getAllSections("versions"));

        $versionForm = new Form('versionsForm');
        $lang = OW::getLanguage();

        $versionNameField = new TextField('version_name');
        $versionNameField->setLabel($lang->text('frmmobilesupport','version_name'));
        $versionNameField->setRequired(true);
        $versionForm->addElement($versionNameField);

        $versionCodeField = new TextField('version_code');
        $versionCodeField->setLabel($lang->text('frmmobilesupport','version_code'));
        $versionCodeField->addValidator(new IntValidator());
        $versionCodeField->setRequired(true);
        $versionForm->addElement($versionCodeField);

        $typeField = new Selectbox('type');
        $options = array();
        $options[$service::getInstance()->AndroidKey] = 'Android';
        $options[$service::getInstance()->iOSKey] = 'iOS';
        $options[$service::getInstance()->nativeFcmKey] = 'Native';
        $typeField->setOptions($options);
        $typeField->setHasInvitation(false);
        $typeField->setRequired(true);
        $typeField->setLabel($lang->text('frmmobilesupport','type'));
        $versionForm->addElement($typeField);

        $urlField = new TextField('url');
        $urlField->setLabel($lang->text('frmmobilesupport','url'));
        $versionForm->addElement($urlField);

        $messageField = new TextField('message');
        $messageField->setLabel($lang->text('frmmobilesupport','message'));
        $versionForm->addElement($messageField);

        $file = new FileField('file');
        $file->setLabel($lang->text('frmmobilesupport','upload_file'));
        $versionForm->addElement($file);

        $element = new Submit('saveSettings');
        $element->setValue($lang->text('frmmobilesupport', 'versions'));
        $versionForm->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($versionForm->isValid($_POST)) {
                $values = $versionForm->getValues();
                if(!empty($values['url']))
                    $service->saveVersion($values['type'], $values['version_name'], $values['version_code'], $values['url'], $values['message']);
                elseif (!empty($_FILES['file']["tmp_name"])) {
                    $bundle = FRMSecurityProvider::generateUniqueId();
                    $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);
                    array_push($validFileExtensions , 'apk');
                    $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmmobilesupport', $_FILES['file'], $bundle ,$validFileExtensions);
                    BOL_AttachmentService::getInstance()->updateStatusForBundle('frmmobilesupport',$bundle,1);
                    $fileName = explode('.', $_FILES['file']["name"]);
                    $renamedFile = null;
                    $length = count($fileName);
                    for( $i=0; $i< $length; $i++)
                    {
                        if($i<($length-2))
                            $renamedFile = $renamedFile.$fileName[$i].'.';
                        elseif ($i<($length - 1))
                            $renamedFile = $renamedFile.$fileName[$i].'_'.FRMSecurityProvider::generateUniqueId().'.';
                        else
                            $renamedFile=$renamedFile.$fileName[$i];
                    }

                    $userfilesDir = OW::getPluginManager()->getPlugin('frmmobilesupport')->getUserFilesDir();
                    $userfilesPath = $userfilesDir . $renamedFile;
                    rename($dtoArr['path'], $userfilesPath);
                    $fileUrl = OW::getStorage()->getFileUrl($userfilesPath);
                    $service->saveVersion($values['type'], $values['version_name'], $values['version_code'], $fileUrl, $values['message']);
                }
                elseif (isset($_FILES["file"])){
                    $result = UTIL_File::checkUploadedFile($_FILES["file"]);
                    if ( !$result["result"] )
                    {
                        OW::getFeedback()->error($result["message"]);
                        $this->redirect(OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions'));
                    }
                }
                else {
                    OW::getFeedback()->error($lang->text('frmmobilesupport', 'file_fileUrl_require'));
                    $this->redirect(OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions'));
                }
                OW::getFeedback()->info($lang->text('frmmobilesupport', 'save_success'));
                $this->redirect(OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions'));
            }
        }
        $versionForm->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($versionForm);

    }

    public function androidVersions(){
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $this->assign("sections", $service->getAllSections("android-versions"));
        $this->assign('values', $service->getArraysOfVersions($service->AndroidKey));
    }

    public function iosVersions(){
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $this->assign("sections", $service->getAllSections("ios-versions"));
        $this->assign('values', $service->getArraysOfVersions($service->iOSKey));
    }

    public function androidNativeVersions(){
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $this->assign("sections", $service->getAllSections("android-native-versions"));
        $this->assign('values', $service->getArraysOfVersions($service->nativeFcmKey));
    }

    public function downloadShow(){

        $config = OW::getConfig();
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $this->assign("sections", $service->getAllSections("download-show"));

        $form = new Form('customHTML');

        $customHtmlRecord = new Textarea('custom_download_link');
        $customHtmlRecord->setLabel(OW::getLanguage()->text('frmmobilesupport', 'custom_download_link_label'));
        $customHtmlRecord->setDescription(OW::getLanguage()->text('frmmobilesupport', 'custom_download_link_desc'));
        if(OW::getConfig()->getValue('frmmobilesupport', 'custom_download_link_code')){
            $customHtmlRecord->setValue(OW::getConfig()->getValue('frmmobilesupport', 'custom_download_link_code'));
        }
        $form->addElement($customHtmlRecord);

        $linkActtivation = new CheckboxField('download_activation');
        $linkActtivation->setValue(OW::getConfig()->getValue('frmmobilesupport','custom_download_link_activation'));
        $form->addElement($linkActtivation);

        $submit = new Submit('save');
        $form->addElement($submit);
        $form->getElement('custom_download_link')->addAttribute(Form::SAFE_RENDERING,true);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) && OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin())
        {
            $configValue=$form->getElement('custom_download_link')->getValue();
            $configActivate=$form->getElement('download_activation')->getValue();
            $config->saveConfig('frmmobilesupport', 'custom_download_link_code', $configValue);
            $config->saveConfig('frmmobilesupport', 'custom_download_link_activation', $configActivate);

        }
    }

    public function useMobile(){

    }

    public function deprecateVersion($params){

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'deprecate_mobileVersion')));
        }
        $version = null;
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        if(!isset($params['id'])){
            throw new Redirect404Exception();
        }else{
            $version = FRMMOBILESUPPORT_BOL_AppVersionDao::getInstance()->findById($params['id']);
            $service->deprecateVersion($params['id']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmmobilesupport', 'deprecate_version_success'));
        }
        $service->redirectPageByVersion($version);
    }

    public function approveVersion($params){
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'approve_mobileVersion')));
        }
        $version = null;
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        if(!isset($params['id'])){
            throw new Redirect404Exception();
        }else{
            $version = FRMMOBILESUPPORT_BOL_AppVersionDao::getInstance()->findById($params['id']);
            $service->approveVersion($params['id']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmmobilesupport', 'approve_version_success'));
        }

        $service->redirectPageByVersion($version);
    }

    public function deleteVersion($params){
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                OW::getFeedback()->info(OW::getLanguage()->text('frmmobilesupport', 'delete_version_fail'));
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_mobileVersion')));
        }
        $version = null;
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        if(!isset($params['id'])){
            OW::getFeedback()->info(OW::getLanguage()->text('frmmobilesupport', 'delete_version_fail'));
        }else{
            $version = FRMMOBILESUPPORT_BOL_AppVersionDao::getInstance()->findById($params['id']);
            $service->deleteVersion($params['id']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmmobilesupport', 'delete_version_success'));
        }
        $service->redirectPageByVersion($version);
    }

    public function settings()
    {

        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();

        $this->assign("sections", $service->getAllSections("settings"));

        $adminForm = new Form('adminForm');      

        $lang = OW::getLanguage();
        $config = OW::getConfig();

        $field = new TextField('fcm_api_key');
        $field->setLabel($lang->text('frmmobilesupport','fcm_api_key_label'));
        $field->setValue($config->getValue('frmmobilesupport', 'fcm_api_key'));
        $adminForm->addElement($field);

        $field = new TextField('fcm_api_url');
        $field->setLabel($lang->text('frmmobilesupport','fcm_api_url_label'));
        $field->setValue($config->getValue('frmmobilesupport', 'fcm_api_url'));
        $adminForm->addElement($field);

        $field = new TextField('constraint_user_device');
        $field->setRequired();
        $validator = new IntValidator();
        $validator->setMinValue(2);
        $validator->setMaxValue(999);
        $field->addValidator($validator);
        $field->setLabel($lang->text('frmmobilesupport','constraint_user_device_label'));
        $field->setValue($config->getValue('frmmobilesupport', 'constraint_user_device'));
        $adminForm->addElement($field);

        $field = new CheckboxField('disable_notification_content');
        $field->setLabel($lang->text('frmmobilesupport','disable_notification_content'));
        $field->setValue($config->getValue('frmmobilesupport', 'disable_notification_content'));
        $adminForm->addElement($field);
        
        $element = new Submit('saveSettings');
        $element->setValue($lang->text('frmmobilesupport', 'admin_save_settings'));
        $adminForm->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($adminForm->isValid($_POST)) {
                $config = OW::getConfig();
                $values = $adminForm->getValues();
                $config->saveConfig('frmmobilesupport', 'disable_notification_content', $values['disable_notification_content']);
                $config->saveConfig('frmmobilesupport', 'fcm_api_key', $values['fcm_api_key']);
                $config->saveConfig('frmmobilesupport', 'constraint_user_device', $values['constraint_user_device']);
                $config->saveConfig('frmmobilesupport', 'fcm_api_url', $values['fcm_api_url']);
                OW::getFeedback()->info($lang->text('frmmobilesupport', 'user_save_success'));
            }
        }

       $this->addForm($adminForm);
    }

    public function webSettings()
    {
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();

        $this->assign("sections", $service->getAllSections("web-settings"));

        $adminForm = new Form('adminForm');

        $lang = OW::getLanguage();
        $config = OW::getConfig();

        $field = new Textarea('config');
        $field->setLabel($lang->text('frmmobilesupport','web_config'));
        $value = !empty($config->getValue('frmmobilesupport', 'web_config'))?$config->getValue('frmmobilesupport', 'web_config'):"var firebaseConfig = {...}";
        $field->setValue($value);
        $field->addAttribute('style','direction:ltr');
        $field->setDescription($lang->text('frmmobilesupport', 'web_config_desc'));
        $adminForm->addElement($field);

        $field = new TextField('key');
        $field->setLabel($lang->text('frmmobilesupport','web_key'));
        $field->setValue($config->getValue('frmmobilesupport', 'web_key'));
        $field->setDescription($lang->text('frmmobilesupport', 'web_key_desc'));
        $adminForm->addElement($field);

        $element = new Submit('saveSettings');
        $element->setValue($lang->text('frmmobilesupport', 'admin_save_settings'));
        $adminForm->addElement($element);

        if ( OW::getRequest()->isPost() ) {
            if ($adminForm->isValid($_POST)) {
                $config = OW::getConfig();
                $values = $adminForm->getValues();
                $config->saveConfig('frmmobilesupport', 'web_config', $values['config']);
                $config->saveConfig('frmmobilesupport', 'web_key', $values['key']);
                if($service->canUseWebNotifications()){
                    $baseDir = OW::getPluginManager()->getPlugin("frmmobilesupport")->getStaticJsDir() . 'web' . DS;
                    // add firebase-messaging-sw.js
                    OW::getStorage()->copyFile($baseDir . 'firebase-messaging-sw.js',OW_DIR_ROOT . 'firebase-messaging-sw.js');
                    // add manifest.json
                    OW::getStorage()->copyFile($baseDir . 'manifest.json',OW_DIR_ROOT . 'manifest.json');
                    // add folder __
                    OW::getStorage()->copyDir($baseDir . '__' . DS,OW_DIR_ROOT . '__' . DS);
                    // edit init.js from user config
                    $str=file_get_contents(OW_DIR_ROOT . '__' . DS . 'firebase' . DS . 'init.js');
                    $str=str_replace('$firebaseConfigPlaceHolder', $values['config'], $str);
                    file_put_contents(OW_DIR_ROOT . '__' . DS . 'firebase' . DS . 'init.js', $str);
                }else{
                    // delete firebase-messaging-sw.js
                    @OW::getStorage()->removeFile(OW_DIR_ROOT . 'firebase-messaging-sw.js');
                    // delete manifest.json
                    @OW::getStorage()->removeFile(OW_DIR_ROOT . 'manifest.json');
                    // delete folder __
                    @UTIL_File::removeDir(OW_DIR_ROOT . '__');
                }
                OW::getFeedback()->info($lang->text('frmmobilesupport', 'user_save_success'));
            }
        }

        $this->addForm($adminForm);
    }
}
