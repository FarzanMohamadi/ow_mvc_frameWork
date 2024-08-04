<?php
class FRMTELEGRAMIMPORT_BOL_Service
{
    private static $classInstance;
    public static $CHANNEL_IMPORT_FORM_NAME="channel_import";
    public static $CHANNEL_UPLOAD_FORM_NAME="channel_upload";

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    public function extractZipFile(){
        $this->clearImportDir();
        if(!((int)$_FILES['file']['error'] !== 0 || !is_uploaded_file($_FILES['file']['tmp_name']))) {
            if (UTIL_File::getExtension($_FILES['file']['name']) != 'zip') {
                OW::getFeedback()->error(OW::getLanguage()->text('frmtelegramimport', 'error_import_file_extension'));
            }

            $uploadPath = $_FILES['file']['tmp_name'];
            $importPath = $this->getImportDirPath();

            $zip = new ZipArchive();
            if ($zip->open($uploadPath) === true) {
                $zip->extractTo($importPath);
                $zip->close();
            } else{
                OW::getFeedback()->error(OW::getLanguage()->text('frmtelegramimport', 'uploaded_file_ix_corrupted'));
            }
        } else if ($_FILES['file']['error'] === 1){
            OW::getFeedback()->error(OW::getLanguage()->text('frmtelegramimport', 'consider_max_upload_size'));
        }
    }
    public function  fetchData(){
        $dataPath = $this->getImportDirPath().'data'.DS.'result.json';
        if (file_exists($dataPath)) {
            $file = fopen($dataPath, 'r');
            $data = fread($file, filesize($dataPath));
            fclose($file);
            $jsonObject = json_decode($data);
            return $jsonObject;
        }
        return null;
    }
    public function publishToGroup($channelData,$groupIds){
        $channel = new FRMTELEGRAMIMPORT_CLASS_Channel($channelData);
        $messages =  $channel->messages;
        $n = $messages->size();
        for($i=0;$i<$n; $i++){
            $message =  $messages->getMessage($i);
            if(isset($message)){
                $message->publishToGroups($groupIds,$channel->name);
            }
        }
    }

    public function isWidgetEnable($groupId){
        $canImport = $this->canImportChannel($groupId);
        return $canImport;
    }
    public function canImportChannel($groupId)
    {
        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return false;
        }
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if (!isset($group)) {
            return false;
        }
        $canEdit = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($group);
        if (!$canEdit) {
            return false;
        }
        return true;
    }
    public function isAdmin(){
        if (!OW::getUser()->isAuthenticated()){
            return false;
        }
        if(OW::getUser()->isAdmin()){
            return true;
        }else{
            return false;
        }
    }

    public function getChannelUploadForm($action){
        $form = new Form(self::$CHANNEL_UPLOAD_FORM_NAME);
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $file = new FileField('file');

        $form->addElement($file);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmtelegramimport', 'upload_file_submit_label'));
        $form->addElement($submit);

        return $form;
    }
    public function getFloatBoxUploadForm($groupId){
        $form = new Form(self::$CHANNEL_UPLOAD_FORM_NAME);
        $action = OW::getRouter()->urlForRoute('frmtelegramimport.uploadToGroup',array('groupId'=>$groupId));
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $file = new FileField('file');
        $label = OW::getLanguage()->text('frmtelegramimport','file_label');
        $file->setLabel($label);
        $form->addElement($file);

        $field = new HiddenField('groupId');
        $field->addAttribute("id","groupId");
        $field->setValue($groupId);
        $form->addElement($field);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('frmtelegramimport', 'upload_file_submit_label'));
        $form->addElement($submit);

        return $form;
    }
    public function getFloatBoxImportForm($groupId){
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $action = OW::getRouter()->urlForRoute('frmtelegramimport.importToGroup',array('groupId'=>$groupId));
        $importForm = new Form(FRMTELEGRAMIMPORT_BOL_Service::$CHANNEL_IMPORT_FORM_NAME);
        $importForm->setAction($action);
        $data= $service->fetchData();
        $chats = $data->chats;
        $channels = $chats->list;
        foreach ($channels as $ch) {
            $channel = new FRMTELEGRAMIMPORT_CLASS_Channel($ch);
            $channelId ='tlg'.bin2hex($channel->name);
            $field = new CheckboxField($channelId);
            $field->setValue(false);
            $field->setLabel($channel->name);
            $importForm->addElement($field);
        }
        $submit = new Submit('import');
        $importForm->addElement($submit);

        return $importForm;
    }

    public function getUserGroups(){
        $userGroups = GROUPS_BOL_Service::getInstance()->findUserGroupList(OW::getUser()->getId());
        $groups = array();
        foreach ($userGroups as $userGroup) {
            $imageUrl = GROUPS_BOL_Service::getInstance()->getGroupImageUrl($userGroup);
            $groups[] = array('groupId' => $userGroup->id, 'src' => OW::getRouter()->urlForRoute('groups-view', array('groupId' => $userGroup->id)), 'label' => $userGroup->title, 'imageUrl' => $imageUrl);
        }
        return $groups;
    }
    public function getChannelsInfo($groupId){
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $data= $service->fetchData();
        if(!isset($data)){
            return null;
        }
        $chats = $data->chats;
        $channels = $chats->list;
        $channelsInfo = array();
        foreach ($channels as $ch) {
            $channel = new FRMTELEGRAMIMPORT_CLASS_Channel($ch);
            $channelId ='tlg'.bin2hex($channel->name);
            $channelsInfo[] = array(
                'title' => $channel->name,
                'statistics' => $channel->statistic(),
                'channelId' => $channelId,
                'channelData' => $ch
            );
        }
        return $channelsInfo;
    }

    public function getImportDirPath(){
        $userId = OW::getUser()->getId();
        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $dirName = $userName;
        $pluginFilesDir = OW::getPluginManager()->getPlugin("frmtelegramimport")->getUserFilesDir().'channels'.DS.$dirName.DS;
        return $pluginFilesDir;
    }
    public function getAttachmentsDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS;
    }
    public function clearImportDir(){
        $importDir = $this->getImportDirPath();
        if ( OW::getStorage()->fileExists($importDir) )
        {
            UTIL_File::removeDir($importDir);
        }
    }

    public  function getAdminHelp(){
        $title = '<div class="ow_center"><b>' .
            OW::getLanguage()->text('frmtelegramimport','help') .
            '</b></div><br/>';

        $settingDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_telegram_setting_section') .
            '</div><br/>';
        $settingImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/setting.png" />' .
            '</div><br/>';

        $formatDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_export_data_format_section') .
            '</div><br/>';
        $formatImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/format.png" />' .
            '</div><br/>';

        $preparationDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_prepare_zip_file') .
            '</div><br/>';
        $preparationImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/preparation.png" />' .
            '</div><br/>';

        $uploadDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_upload_zip_file') .
            '</div><br/>';
        $uploadImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/upload.png" />' .
            '</div><br/>';

        $importDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_import_data') .
            '</div><br/>';
        $importImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/import.png" />' .
            '</div><br/>';

        return $title . $settingDescription . $settingImage .
            $formatDescription . $formatImage .
            $preparationDescription . $preparationImage .
            $uploadDescription . $uploadImage .
            $importDescription . $importImage;
    }
    public  function getUserHelp(){
        $title = '<div class="ow_center"><b>' .
            OW::getLanguage()->text('frmtelegramimport','help') .
            '</b></div><br/>';

        $settingDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_telegram_setting_section') .
            '</div><br/>';
        $settingImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/setting.png" />' .
            '</div><br/>';

        $formatDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_export_data_format_section') .
            '</div><br/>';
        $formatImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/format.png" />' .
            '</div><br/>';

        $preparationDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_prepare_zip_file') .
            '</div><br/>';
        $preparationImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/preparation.png" />' .
            '</div><br/>';

        $uploadDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_upload_zip_file') .
            '</div><br/>';
        $uploadImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/upload_floatbox.png" />' .
            '</div><br/>';

        $importDescription =
            '<div class="ow_center">' .
            OW::getLanguage()->text('frmtelegramimport','help_import_data_to_group') .
            '</div><br/>';
        $importImage =
            '<div class="ow_center">' .
            '<img src="' . OW::getPluginManager()->getPlugin('frmtelegramimport')->getStaticUrl(). 'img/import_floatbox.png" />' .
            '</div><br/>';

        return $title . $settingDescription . $settingImage .
            $formatDescription . $formatImage .
            $preparationDescription . $preparationImage .
            $uploadDescription . $uploadImage .
            $importDescription . $importImage;
    }
}