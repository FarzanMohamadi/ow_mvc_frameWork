<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceGeneral
{
    private static $classInstance;
    private $eventWebService;
    private $groupWebService;
    private $userWebService;
    private $storyWebService;
    private $passwordsecurityWebService;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->eventWebService = FRMMOBILESUPPORT_BOL_WebServiceEvent::getInstance();
        $this->groupWebService = FRMMOBILESUPPORT_BOL_WebServiceGroup::getInstance();
        $this->userWebService = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance();
        $this->notificationsWebService = FRMMOBILESUPPORT_BOL_WebServiceNotifications::getInstance();
        $this->newsWebService = FRMMOBILESUPPORT_BOL_WebServiceNews::getInstance();
        $this->friendsWebService = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance();
        $this->searchWebService = FRMMOBILESUPPORT_BOL_WebServiceSearch::getInstance();
        $this->videoWebService = FRMMOBILESUPPORT_BOL_WebServiceVideo::getInstance();
        $this->photoWebService = FRMMOBILESUPPORT_BOL_WebServicePhoto::getInstance();
        $this->newsfeedWebService = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance();
        $this->mailboxWebService = FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance();
        $this->commentWebService = FRMMOBILESUPPORT_BOL_WebServiceComment::getInstance();
        $this->forumWebService = FRMMOBILESUPPORT_BOL_WebServiceForum::getInstance();
        $this->privacyWebService = FRMMOBILESUPPORT_BOL_WebServicePrivacy::getInstance();
        $this->contactusWebService = FRMMOBILESUPPORT_BOL_WebServiceContactUs::getInstance();
        $this->blogsWebService=FRMMOBILESUPPORT_BOL_WebServiceBlogs::getInstance();
        $this->questionsWebService=FRMMOBILESUPPORT_BOL_WebServiceQuestions::getInstance();
        $this->flagWebService=FRMMOBILESUPPORT_BOL_WebServiceFlag::getInstance();
        $this->mentionsWebService=FRMMOBILESUPPORT_BOL_WebServiceMention::getInstance();
        $this->logWebService =FRMMOBILESUPPORT_BOL_WebServiceLog::getInstance();
        $this->storyWebService=FRMMOBILESUPPORT_BOL_WebServiceStory::getInstance();
        $this->highlightWebService=FRMMOBILESUPPORT_BOL_WebServiceHighlight::getInstance();
        $this->marketWebService=FRMMOBILESUPPORT_BOL_WebServiceMarket::getInstance();
        $this->broadcastWebService=FRMMOBILESUPPORT_BOL_WebServiceBroadcast::getInstance();
        $this->passwordsecurityWebService=FRMMOBILESUPPORT_BOL_WebServicePasswordsecurity::getInstance();
    }

    public function isFileClean($path) {
        if (!isset($path) || $path == null) {
            return false;
        }
        $checkFileCleanEvent = OW::getEventManager()->trigger(new OW_Event('frmclamav.is_file_clean', array('path' => $path)));
        if(isset($checkFileCleanEvent->getData()['clean'])){
            $isClean = $checkFileCleanEvent->getData()['clean'];
            if(!$isClean)
            {
                return false;
            }
        }
        return true;
    }

    public function getMobileConfig(){
        $data = array();
        $data['plugins'] = $this->getActivePlugins();
        $data['join_fields'] = $this->userWebService->getJoinFields();
        $data['account_type_labels'] = $this->userWebService->getAccountLabelTypes();
        $data['default_account_type_labels'] = $this->userWebService->getDefaultAccountLabels();
        $data['group_fields'] = $this->groupWebService->getGroupFields();
        $data['event_fields'] = $this->eventWebService->getEventFields();
        $data['social_name'] = OW::getConfig()->getValue('base', 'site_name');
        return $data;
    }

    public function correctFileName() {
        if (isset($_FILES)) {
            $fileIndex = 0;
            if (isset($_FILES['file']) && isset($_FILES['file']['name'])) {
                $_FILES['file']['name'] = urldecode($_FILES['file']['name']);
            }
            while (isset($_FILES['file' . $fileIndex]) && isset($_FILES['file' . $fileIndex]['name'])) {
                $_FILES['file' . $fileIndex]['name'] = urldecode($_FILES['file' . $fileIndex]['name']);
                $fileIndex++;
            }
        }
    }

    public function getActivePlugins(){
        $data = array();
        $plugins = BOL_PluginService::getInstance()->findActivePlugins();
        foreach ($plugins as $plugin){
            if($plugin->isSystem == 0){
                $data[] = $plugin->key;
            }
        }
        return $data;
    }

    public function isSessionUserExpired(){
        if(FRMSecurityProvider::checkPluginActive('frmuserlogin', true)){
            return FRMUSERLOGIN_BOL_ActiveDetailsDao::getInstance()->isSessionExpired(session_id());
        }
        return false;
    }

    public function manageRequestHeader($type, $actionType = 'info'){
        if(!OW::getConfig()->configExists('frmmobilesupport', 'access_web_service') || OW::getConfig()->getValue('frmmobilesupport', 'access_web_service') == false){
            exit($this->makeJson(array("Config of web service is not set.")));
        }

        $accessToken = null;
        $fcmToken = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getFcmTokenFromPost();
        $fcmTokenString = '';
        if ($fcmToken != null) {
            $fcmTokenString = $fcmToken;
        }
        $logoutProcess = false;
        if(isset($_POST['access_token'])){
            $accessToken = $_POST['access_token'];
            $id = BOL_UserService::getInstance()->findUserIdByCookie(trim($_POST['access_token']));
            if ( !empty($id) )
            {
                if (!$this->isSessionUserExpired()){
                    OW::getUser()->login($id, false);
                }else{
                    OW::getLogger()->writeLog(OW_Log::INFO, 'mobile_native_user_auto_login', array('message' => 'login_cookie_expired', 'token' => $_POST['access_token'], 'fcmToken' => $fcmTokenString));
                    $logoutProcess = true;
                }
            }else{
                OW::getLogger()->writeLog(OW_Log::INFO, 'mobile_native_user_auto_login', array('message' => 'login_cookie_not_found', 'token' => $_POST['access_token'], 'fcmToken' => $fcmTokenString));
                $logoutProcess = true;
            }
        }else{
            OW::getLogger()->writeLog(OW_Log::INFO, 'mobile_native_user_auto_login', array('message' => 'login_cookie_not_sent', 'fcmToken' => $fcmTokenString));
            $logoutProcess = true;
        }
        if ($logoutProcess) {
            FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->logoutProcess();
        }
        $this->correctFileName();
        $validType = array('config', 'forgot_password', 'check_verification_code', 'login', 'join','logout',
            'edit_profile_fields', 'edit_profile', 'send_verification_code_to_mobile');
        if(!in_array($type, $validType) && !OW::getUser()->isAuthenticated()){
            header('HTTP/1.0' . ' ' . '403 Forbidden');
            //header('Status' . ' ' . '403 Forbidden');
            $this->generateWebserviceResult($type, $actionType);
        }
        if(OW::getUser()->isAuthenticated() && $accessToken != null && $fcmToken != null){
            FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->addNativeDevice(OW::getUser()->getId(), $fcmToken, $accessToken);
        }
        $frmblockingipEvent = OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.on.login.attempt'));
        if(isset($frmblockingipEvent->getData()['lock']) && $frmblockingipEvent->getData()['lock']){
            header('HTTP/1.0' . ' ' . '403 Forbidden');
            //header('Status' . ' ' . '403 Forbidden');
            $this->generateWebserviceResult($type, $actionType);
        }

        if ($this->isMaintenanceModeEnabled()) {
            $generalWebService = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance();
            $generalWebService->generateWebserviceResult('check_login', 'info');
        }
    }

    public function getNecessaryPostedData() {
        $data = array();
        $first = 0;
        $commentPage = 0;
        $search = '';
        $data['count'] = $this->getPageSize();
        if (isset($_GET['first'])) {
            $first = $_GET['first'];
        }

        if (isset($_POST['first'])) {
            $first = $_POST['first'];
        }

        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }

        if (isset($_POST['search'])) {
            $search = $_POST['search'];
        }

        if (isset($_GET['searchValue'])) {
            $search = $_GET['searchValue'];
        }

        if (isset($_POST['searchValue'])) {
            $search = $_POST['searchValue'];
        }

        if (isset($_GET['comment_page'])) {
            $commentPage = $_GET['comment_page'];
        }

        if (isset($_POST['comment_page'])) {
            $commentPage = $_POST['comment_page'];
        }

        $data['search'] = $search;
        $data['comment_page'] = $commentPage;
        $data['first'] = $first;
        return $data;
    }

    /***
     * Return native mobile menu
     * @return array
     */
    public function getMobileMenu(){
        $menu = array();
        $menuFetch = array();
        $menuFetch = array_merge($menuFetch, $this->getMenuTypeMainItems(true));
        $menuFetch = array_merge($menuFetch, $this->getMenuTypeBottomItems(true));
        foreach($menuFetch as $menuItem){
            $menu[] = array('label' => $menuItem['label'], 'prefix' => $menuItem['prefix']);
        }
        return $menu;
    }

    /***
     * Create forms from data
     * @param $fields
     * @param $account_type
     * @return Form
     */
    public function getFormUsingDataArray($fields, $account_type = null){
        $form = new Form('sample');

        foreach ($fields as $field){
            $fieldForm = null;
            if($field['type'] == 'text' ||
                $field['type'] == 'time' ||
                $field['type'] == 'datetime'){
                $fieldForm = new TextField($field['name']);
            }else if($field['type'] == 'select' || $field['type'] == 'fselect'){
                $fieldForm = new Selectbox($field['name']);
                $newFiledValue = array();
                foreach ($field['values'] as $itemFieldValue) {
                    if (isset($itemFieldValue['label'])) {
                        $newFiledValue[$itemFieldValue['value']] = $itemFieldValue['label'];
                    } else {
                        $newFiledValue[] = $itemFieldValue;
                    }
                }
                $fieldForm->addOptions($newFiledValue);
            }else if($field['type'] == 'multiselect'){
                $fieldForm = new Multiselect($field['name']);
                $newFiledValue = array();
                foreach ($field['values'] as $itemFieldValue) {
                    if (isset($itemFieldValue['label'])) {
                        $newFiledValue[$itemFieldValue['value']] = $itemFieldValue['label'];
                        $fieldForm->addOption($itemFieldValue['value'],$itemFieldValue['label']);
                    } else {
                        $fieldForm->addOption($itemFieldValue['value'],'');
                    }
                }
            } else if($field['type'] == 'date'){
                $fieldForm = new DateField($field['name']);
            }else if($field['type'] == 'captcha' || $field['name'] == 'captcha'){
                $fieldForm = new CaptchaField($field['name']);
            }
            if($field['type'] == 'text' ||
                $field['type'] == 'select' ||
                $field['type'] == 'fselect'){
                $fieldForm->setHasInvitation(false);
            }
            if(isset($field['required']) && !$field['required']){
                $fieldForm->setRequired(false);
            }else if(isset($field['required']) && $field['required']){
                // check if for the selected account type is required (or if no account type selected then require)
                if(in_array(trim($account_type), $field['accountType']) || !isset($account_type)){
                    $fieldForm->setRequired(true);
                }
            }else if($fieldForm != null){
                $fieldForm->setRequired();
            }
            if ($fieldForm != null) {
                if (isset($field['name']) && $field['name'] == 'username') {
                    $fieldForm->addValidator(new BASE_CLASS_JoinUsernameValidator());
                }
                $form->addElement($fieldForm);
            }
        }

        return $form;
    }

    /***
     * Check data is valid
     * @param $fields
     * @param $useAuthenticated
     * @param $account_type
     * @return array
     */
    public function checkDataFormValid($fields, $useAuthenticated = true, $account_type = null){
        if($useAuthenticated && !OW::getUser()->isAuthenticated()){
            return array( 'valid' => false, 'errors' => array() );
        }

        $form = $this->getFormUsingDataArray($fields, $account_type);
        if ( $form->getElement('csrf_token') != null){
            $form->deleteElement('csrf_token');
        }
        try{
            if(isset($_POST) && $form->isValid($_POST)){
                return array( 'valid' => true );
            }
        }catch (Exception $e){
            return array( 'valid' => false, 'errors' => $form->getErrors() );
        }
        return array( 'valid' => false, 'errors' => $form->getErrors() );
    }

    /***
     * @param bool $onlyMobile
     * @return array
     */
    public function getMenuTypeMainItems($onlyMobile = false){
        $menuTypeMainItems = array();
        $items = array();
        if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE || $onlyMobile) {
            $menuTypeMainItems = BOL_NavigationService::getInstance()->getMenuItems(BOL_NavigationService::getInstance()->findMenuItems(BOL_MobileNavigationService::MENU_TYPE_TOP));
        } else {
            $menuTypeMainItems = BOL_NavigationService::getInstance()->getMenuItems(BOL_NavigationService::getInstance()->findMenuItems(BOL_NavigationService::MENU_TYPE_MAIN));
        }

        foreach($menuTypeMainItems as $menuTypeMainItem){
            $items[] = array('label' => $menuTypeMainItem->getLabel(), 'url' => $menuTypeMainItem->getUrl(), 'prefix' => $menuTypeMainItem->getPrefix());
        }

        return $items;
    }

    /***
     * @param bool $onlyMobile
     * @return array
     */
    public function getMenuTypeBottomItems($onlyMobile = false){
        $menuTypeBottomItems = array();
        $items = array();
        if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE || $onlyMobile) {
            $menuTypeBottomItems = BOL_NavigationService::getInstance()->getMenuItems(BOL_NavigationService::getInstance()->findMenuItems(BOL_MobileNavigationService::MENU_TYPE_BOTTOM));
        } else {
            $menuTypeBottomItems = BOL_NavigationService::getInstance()->getMenuItems(BOL_NavigationService::getInstance()->findMenuItems(BOL_NavigationService::MENU_TYPE_BOTTOM));
        }

        foreach($menuTypeBottomItems as $menuTypeBottomItem){
            $items[] = array('label' => $menuTypeBottomItem->getLabel(), 'url' => $menuTypeBottomItem->getUrl(), 'prefix' => $menuTypeBottomItem->getPrefix());
        }

        return $items;
    }

    public function getPageSize(){
        return 10;
    }

    public function getCachedGroupPostsSize(){
        return 300;
    }

    public function getPageNumber($first = 0){
        $count = $this->getPageSize();
        $page = (int) ($first/$count);
        if ($first % $count != 0) {
            $page += 1;
        }
        return $page + 1;
    }

    public function checkPrivacyAction($userId, $privacyAction, $module){
        if(OW::getUser()->isAdmin()){
            return true;
        }
        if(OW::getUser()->isAuthenticated()) {
            $viewerId = OW::getUser()->getId();
            $ownerMode = $userId == $viewerId;
            $modPermissions = OW::getUser()->isAuthorized($module);

            if($ownerMode){
                return true;
            }

            if (!$modPermissions) {
                $privacyParams = array('action' => $privacyAction, 'ownerId' => $userId, 'viewerId' => $viewerId);
                $event = new OW_Event('privacy_check_permission', $privacyParams);

                try {
                    OW::getEventManager()->trigger($event);
                } catch (RedirectException $e) {
                    return false;
                }
            }
        }

        $privacy = OW::getEventManager()->call('plugin.privacy.get_privacy',
            array('ownerId' => $userId, 'action' => $privacyAction)
        );

        if($privacy == 'only_for_me'){
            if(!OW::getUser()->isAuthenticated()){
                return false;
            }

            if(OW::getUser()->isAuthenticated() && $userId != OW::getUser()->getId()){
                return false;
            }
        }else if($privacy == 'friends_only'){
            if(!OW::getUser()->isAuthenticated()){
                return false;
            }

            if(OW::getUser()->isAuthenticated()){
                if(!FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->isFriend($userId, OW::getUser()->getId())){
                    return false;
                }
            }
        }

        return true;
    }

    public function checkGuestAccess(){
        $baseConfigs = OW::getConfig()->getValues('base');
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_CANT_VIEW && !OW::getUser()->isAuthenticated() )
        {
            return false;
        }

        return true;
    }

    /***
     * @param $array
     * @return null|string
     */
    public function makeJson($array){
        if($array == null){
            return null;
        }
        header('Content-Type: application/json');
        return json_encode($array);
    }

    public function getValidExtensions(){
        $list = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);
        return implode(',', $list);
    }

    public function getGeneratedEmailPattern(){
        $generatedEmailPattern = OW::getConfig()->getValue('frmmobileaccount', 'email_postfix');
        if ($generatedEmailPattern != null && !empty($generatedEmailPattern)) {
            return $generatedEmailPattern;
        }
        return '';
    }

    public function userBlockExceed() {
        return array(
            'blocked' => true,
        );
    }

    public function getFilesInfo(){
        $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
        return array(
            'max_upload_size_mb' => (int) $maxUploadSize,
        );
    }

    public function preparedFileList($group, $filesList){
        $preparedFilesList = array();
        if($group == null){
            return $preparedFilesList;
        }

        $secureFilePluginActive = OW::getUser()->isAuthenticated() && FRMSecurityProvider::checkPluginActive('frmsecurefileurl', true);
        $cachedParams = array();
        if ($secureFilePluginActive) {
            $keyFiles = array();
            foreach ($filesList as $item) {
                $filePathDir = $this->getAttachmentDir($item->fileName);
                $filePath = OW::getStorage()->prepareFileUrlByPath($filePathDir);
                if ($secureFilePluginActive) {
                    $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($filePath);
                    $keyFiles[] = $keyInfo['key'];
                }
            }
            $cachedSecureFileKeyList = array();
            if (sizeof($keyFiles) > 0) {
                $keyList = FRMSECUREFILEURL_BOL_Service::getInstance()->existUrlByKeyList($keyFiles);
                foreach ($keyList as $urlObject) {
                    $cachedSecureFileKeyList[$urlObject->key] = $urlObject;
                }
                foreach ($keyFiles as $key) {
                    if (!array_key_exists($key, $cachedSecureFileKeyList)) {
                        $cachedSecureFileKeyList[$key] = null;
                    }
                }
            }
            $cachedParams['cache']['secure_files'] = $cachedSecureFileKeyList;
        }

        foreach ( $filesList as $item )
        {
            $preparedFilesList[$item->id] = $this->prepareFileInformation($item, $cachedParams);
        }

        // send parent-folder id
        if (FRMSecurityProvider::checkPluginActive('frmfilemanager', true)) {
            $filemanagerService = FRMFILEMANAGER_BOL_Service::getInstance();
            foreach ($preparedFilesList as $k => $item) {
                $file = $filemanagerService->findByAttachmentId($k);
                if (isset($file)){
                    $preparedFilesList[$k]['parent_id'] = $filemanagerService->findByAttachmentId($k)->parent_id;
                }
            }
        }

        return $preparedFilesList;
    }

    public function prepareFileInformation($item, $params = array()){
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

        $fileName = $this->stripString($fileName);

        $fileNameArr = explode('.',$item->fileName);
        $fileNameExt = end($fileNameArr);

        $data['fileUrl'] = $this->getAttachmentUrl($item->fileName, false, $params);
        $data['iconUrl'] = FRMGROUPSPLUS_BOL_Service::getInstance()->getProperIcon(strtolower($fileNameExt));
        $data['truncatedFileName'] = $fileName;
        $data['fileName'] = $item->getOrigFileName();
        $data['createdDate'] = $item->addStamp;
        $data['canDelete'] = OW::getUser()->getId() == $item->getUserId();
        $data['userName'] = BOL_UserService::getInstance()->getDisplayName($item->getUserId());
        $data['userId'] = (int) $item->getUserId();
        $data['id'] = (int) $item->id;
        if(isset($item->parent_id)){
            $data['parent_id'] = $item->parent_id;
        }

        return $data;
    }

    public function preparedFileListByEntity($entityType, $entityId){
        $filesList = FRMFILEMANAGER_BOL_Service::getInstance()->getSubfiles($entityType, $entityId);
        $preparedFilesList = [];
        foreach ( $filesList as $item )
        {
            $preparedFilesList[$item->id] = $this->prepareFileInformation($item);
        }
        return $preparedFilesList;
    }

    public function populateInvitableUserList($idList, $key, $first, $count){
        $users = array();
        $usersObject = BOL_UserService::getInstance()->findUserListByIdList($idList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($idList);
        $usernames = BOL_UserService::getInstance()->getUserNamesForList($idList);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($idList);
        $counter = -1;
        foreach ($usersObject as $user){
            if(sizeof($users) >= $count){
                break;
            }
            $username = null;
            $displayName = null;
            if(isset($displayNames[$user->id])){
                $displayName = $displayNames[$user->id];
            }

            if(isset($usernames[$user->id])){
                $username = $usernames[$user->id];
            }

            $avatarUrl = null;
            if(isset($avatars[$user->id])){
                $avatarUrl = $avatars[$user->id];
            }
            $include = false;
            if($key == ''){
                $include = true;
            }else {
                $findChar = false;
                if(strpos($user->email, $key)!==false){
                    $findChar = true;
                } else if($username != null && strpos($username, $key)!==false){
                    $findChar = true;
                } else if($displayName != null && strpos($displayName, $key)!==false){
                    $findChar = true;
                }
                if($findChar){
                    $include = true;
                }
            }
            if($include){
                $counter++;
                if($counter < $first){
                    continue;
                }
                $users[] = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($user, $avatarUrl, $displayName);
            }
        }
        return $users;
    }

    public function getAttachmentUrl($name, $returnPath = false, $params = array())
    {
        return OW::getStorage()->getFileUrl($this->getAttachmentDir($name), $returnPath, $params);
    }

    public function getAttachmentDir($name)
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS .$name ;
    }

    public function stripString($string, $removeMultipleNewLines = true, $removeNewLine = false, $changeBrToNewLine = false){
        $string = str_replace('&nbsp;'," ", $string);

        // convert quote icon into hex
        $string = str_replace('&quot;',"\"", $string);

        // convert and icon into hex
        $string = str_replace('&amp;',"&", $string);
        $string = str_replace('<!--more-->',"", $string);

        // convert check icon into hex
        $string = str_replace('',"&#x2713;", $string);

        if ($changeBrToNewLine) {
            $string = preg_replace('#<br\s*?/?>#i', "\r\n", $string);
        }
        $string = $this->getDomTextContent($string);
        if($removeMultipleNewLines){
            //remove multiple new lines
            $string = preg_replace("/[\r\n]+/", "\r\n", $string);
            $string = preg_replace("/[\n]+/", "\n", $string);
            $string = preg_replace("/[\r]+/", "\r", $string);
        }
        $string = $this->brToNewLine($string);

        // remove additional character (used for rtl in web)
        $string = str_replace('&#8235;', '', $string);

        $string = preg_replace("'\r'","", $string);
        $string = preg_replace("'\n '","", $string);
        $string = preg_replace("'\n '","", $string);
        $string = preg_replace("' '","", $string);
        if($removeNewLine){
            $string = str_replace("\r\n"," ", $string);
            $string = str_replace("\r"," ", $string);
            $string = str_replace("\n"," ", $string);
            $string = preg_replace('/\s+/', ' ', $string);
        }
        $string = trim($string);

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $string)));
        if(isset($stringRenderer->getData()['string'])){
            $string = $stringRenderer->getData()['string'];
        }

        return $string;
    }

    public function getDomTextContent($text){
        if(strpos($text, '<') !== false) {
            //DomDocument
            $text = '<div>'.$text.'</div>';
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'));
            //$domDoc1 = preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $doc->saveHTML());

            # remove <!DOCTYPE
            $doc->removeChild($doc->doctype);
            # remove <html><body></body></html>
            /*** get the links from the HTML ***/

            $links = $doc->getElementsByTagName('a');
            $linksReplaces = $this->getLinkReplaces($links);

            $doc = $this->getCircleBulletsReplaces($doc);
            $doc = $this->getNumberBulletsReplaces($doc);

            $element = $doc->firstChild->firstChild->firstChild;
//            if (isset($element->getElementsByTagName('span')[0]) &&
//                isset($element->getElementsByTagName('span')[0]->attributes[0]) &&
//                isset($element->getElementsByTagName('span')[0]->attributes[0]->value) &&
//                $element->getElementsByTagName('span')[0]->attributes[0]->value == 'ow-message-inline-time') {
//                $foundElement = $element->getElementsByTagName('span')[0];
//                $foundElement->parentNode->removeChild($foundElement);
//            }
            $text = $element->textContent;

            foreach ($linksReplaces as $key => $value) {
                $text = str_replace($key, $value, $text);
            }
        }
        return $text;
    }

    public function getLinkReplaces($links){
        $replaces = array();

        /*** loop over the links ***/
        foreach ($links as $tag)
        {
            if(isset($tag->childNodes) && isset($tag->childNodes->item(0)->nodeValue)) {
                $innerText = $tag->childNodes->item(0)->nodeValue;
                if (strpos($innerText, '...')) {
                    $arr = parse_url($innerText);
                    if (isset($arr['scheme'])) {
                        $replaces[$tag->childNodes->item(0)->nodeValue] = $tag->getAttribute('href');
                    }
                }
            }
        }
        return $replaces;
    }

    /***
     * @param DOMDocument $doc
     * @return DOMDocument
     */
    public function getCircleBulletsReplaces($doc){
        foreach ($doc->getElementsByTagName('ul') as $bulletsTag)
        {
            if(isset($bulletsTag->childNodes)) {
                foreach ($bulletsTag->childNodes as $bullet) {
                    $checkString = $this->removeNewLine($bullet->nodeValue);
                    if ($checkString != ''){
                        $bullet->nodeValue = '&#x2022; ' . $bullet->nodeValue ;
                    }
                }
            }
        }

        return $doc;
    }

    public function removeNewLine($checkString){
        $checkString = preg_replace("'\r'","", $checkString);
        $checkString = preg_replace("'\n '","", $checkString);
        $checkString = preg_replace("'\n '","", $checkString);
        $checkString = preg_replace("' '","", $checkString);
        $checkString = trim($checkString);
        $checkString = trim($checkString, '\n');
        return $checkString;
    }

    /***
     * @param DOMDocument $doc
     * @return DOMDocument
     */
    public function getNumberBulletsReplaces($doc){
        foreach ($doc->getElementsByTagName('ol') as $bulletsTag)
        {
            if(isset($bulletsTag->childNodes)) {
                $index = 1;
                foreach ($bulletsTag->childNodes as $bullet) {
                    $checkString = $this->removeNewLine($bullet->nodeValue);
                    if ($checkString != ''){
                        $bullet->nodeValue = $index.'. ' . $bullet->nodeValue ;
                        $index++;
                    }
                }
            }
        }

        return $doc;
    }

    public function brToNewLine($string){
        $string = str_replace("<br />","\r\n", $string);
        $string = str_replace("<br/>","\r\n", $string);
        $string = str_replace("<br>","\r\n", $string);
        $string = str_replace("</br>","\r\n", $string);
        return $string;
    }

    public function userAccessUsingPrivacy($privacy, $userId, $ownerId){
        if($privacy == null){
            return true;
        }

        if($userId == $ownerId){
            return true;
        }

        if($privacy == NEWSFEED_BOL_Service::PRIVACY_EVERYBODY){
            return true;
        }

        if($privacy == NEWSFEED_BOL_Service::PRIVACY_ONLY_ME && $userId != $ownerId){
            return false;
        }

        if($privacy == NEWSFEED_BOL_Service::PRIVACY_FRIENDS){
            if ($ownerId == $userId) {
                return true;
            }
            $isFriend = FRMMOBILESUPPORT_BOL_WebServiceFriends::getInstance()->isFriend($userId, $ownerId);
            if($isFriend){
                return true;
            }
        }

        return false;
    }

    public function generateWebserviceResult($type = null, $actionType = 'info') {
        $entryData = array(
            "check_login",
            "warning_alert",
            "posted_data",
            "valid_extensions",
            "generated_email_pattern",
            "files_info"
        );
        if ($type != null) {
            $entryData[] = $type;
        }
        if ($actionType == 'info') {
            $data = $this->populateWebServiceInformationData($entryData);
        } else {
            $data = $this->populateWebServiceActionData($entryData);
        }
        exit($this->makeJson($data));
    }

    public function uploadSingleFile() {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $videoId = null;
        $fileId = null;
        $attachId = null;
        $fileData = null;
        $fileUrl = null;
        $valid = true;
        $type = 'post';
        $pluginKey = 'frmnewsfeedplus';

        if (isset($_POST['type']) && in_array($_POST['type'], array('post', 'post_video', 'single_video', 'mailbox_video'))) {
            $type = $_POST['type'];
        }

        if (isset($_POST['videoId'])) {
            $videoId = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_POST['videoId']));
        }

        if (isset($_POST['fileData'])) {
            $fileData = $_POST['fileData'];
        }

        if (isset($_POST['attachId'])) {
            // check $attachId is valid
            $attachmentList = BOL_AttachmentDao::getInstance()->findAttahcmentByBundle($pluginKey, $_POST['attachId']);
            if (!empty($attachmentList) && $attachmentList != null) {
                $attachId = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_POST['attachId']));
                foreach ($attachmentList as $attachmentItem) {
                    if ($attachmentItem->status == 1 || $attachmentItem->userId != OW::getUser()->getId()) {
                        return array('valid' => false, 'message' => 'input_error');
                    }
                }
            }
        }

        if (isset($_FILES) && isset($_FILES['file'])) {
            $isFileClean = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->isFileClean($_FILES['file']['tmp_name']);
            if ($isFileClean) {
                if ($type == 'post') {
                    $dtoObject = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->manageNewsfeedAttachment(OW::getUser()->getId(), $_FILES['file'], $attachId);
                    if (isset($dtoObject) || $dtoObject != null) {
                        $attachId = $dtoObject['bundle'];
                        if (isset($dtoObject['dto']['dto'])) {
                            $fileId = $dtoObject['dto']['dto']->id;
                        } else if (isset($dtoObject['dto'])) {
                            $fileId = $dtoObject['dto']->id;
                        }

                        if (isset($dtoObject['dto']['url'])) {
                            $fileUrl = $dtoObject['dto']['url'];
                        }
                    }
                }
            } else {
                return array('valid' => false, 'message' => 'virus_detected');
            }
        } else if ($fileData != null) {
            if ($type == 'single_video' && $videoId != null) {
                $result = FRMMOBILESUPPORT_BOL_WebServiceVideo::getInstance()->setThumbnail($videoId, $fileData);
                $valid = $result['valid'];
                $fileUrl = $result['thumbnail'];
            } else if ($type == 'post_video' && $videoId != null) {
                $result = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->setPostVideoThumbnail($videoId, $fileData);
                $valid = $result['valid'];
                $fileUrl = $result['thumbnail'];
            } else if ($type == 'mailbox_video' && $videoId != null) {
                $result = FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance()->setThumbnail($videoId, $fileData);
                $valid = $result['valid'];
                $fileUrl = $result['thumbnail'];
            }
            if (!$valid) {
                return array('valid' => $valid, 'message' => 'input_file_error');
            }
        } else {
            return array('valid' => $valid, 'message' => 'input_file_error');
        }

        return array(
            'valid' => $valid,
            'attachmentId' => $attachId,
            'fileId' => $fileId,
            'videoId' => $videoId,
            'fileUrl' => $fileUrl
        );
    }

    public function isMaintenanceModeEnabled() {
        $baseConfigs = OW::getConfig()->getValues('base');

        if ((bool) $baseConfigs['maintenance'] && !OW::getUser()->isAdmin())
        {
            return true;
        }
        return false;
    }

    public function getProfileEditHash($userId){
        return FRMSecurityProvider::getInstance()->hashSha256Data('profile_edit', $userId);
    }
    private function getWarningAlert() {
        $warningAlertShow=false;
        $warningAlertText='';
        $warningAlertIsSet=0;
        $warningAlertTimeStamp=0;
        $warningAlertAction='';

        $warningAlertIsSet=OW::getConfig()->getValue('base', 'warningAlert');

        if ($warningAlertIsSet){
            $warningAlertTimeStamp = OW::getLanguage()->text('admin', 'warningAlert_timeStamp');
            $warningAlertText = OW::getLanguage()->text('admin', 'warningAlert_text_value');
            $warningAlertAction = OW::getLanguage()->text('admin', 'warningAlert_action_value');
            $warningAlertShow=true;
           // Strip HTML Tags
            $warningAlertText = str_replace(array("\r\n", "\r", "\n"), "<br />", $warningAlertText);
            $warningAlertText = strip_tags($warningAlertText);

        }
        $warningAlertType='';
        switch ($warningAlertIsSet) {
            case '1':
                $warningAlertType='banner';
                break;
            case '2':
                $warningAlertType='modal';
                break;
            case '3':
                $warningAlertType='both';
                break;
            
            default:
            $warningAlertType='';
                break;
        }
        return array('valid' => $warningAlertShow, 'message' => $warningAlertText , 'type'=>$warningAlertType , 'timestamp'=>$warningAlertTimeStamp,'action'=>$warningAlertAction);

    }

    private function populateWebServiceInformationData($type = array()){
        $data = array();
        if(in_array("config", $type)){
            $data["config"] = $this->getMobileConfig();
        }

        /****** SECTION: Endpoints that work for not approved users */
        $notApprovedMode = false;
        if(!OW::getUser()->isAuthenticated() && !empty($_POST['userId']) && !empty($_POST['code'])){
            if($this->getProfileEditHash($_POST['userId']) === $_POST['code']){
                $notApprovedMode = true;
                OW::getUser()->login($_POST['userId']);
            }
        }

        if(in_array("check_login", $type)){
            $data["check_login"] = $this->userWebService->checkLogin();
        }

        if(in_array("edit_profile_fields", $type)){
            $data["edit_profile_fields"] = $this->userWebService->getEditProfileFields();
        }

        if($notApprovedMode){
            OW::getLogger()->writeLog(OW_Log::NOTICE, 'mobile_native_user_logout', array('message' => 'User_not_approved', 'data' => $data));
            OW::getUser()->logout();
        }
        /******  END of SECTION */

        if(in_array("groups", $type)){
            $data["groups"] = $this->groupWebService->getGroups('latest');
            $data["security"] = $this->userWebService->getSecurityInfo();
        }

        if(in_array("groups_and_chats", $type)){
            $data["groups_and_chats"] = $this->userWebService->getChatAndGroups();
            $data["security"] = $this->userWebService->getSecurityInfo();
        }

        if(in_array("groups_and_channels_and_chats", $type)){
            $data["groups_and_channels_and_chats"] = $this->userWebService->getChatsAndChannelsAndGroups();
            $data["security"] = $this->userWebService->getSecurityInfo();
        }

        if(in_array("group_invite_list", $type)){
            $data["group_invite_list"] = $this->groupWebService->getInvitableUsers();
        }

        if(in_array("group_invite_searchable_questions", $type)){
            $data["group_invite_searchable_questions"] = $this->userWebService->getInviteEntitySearchableQuestions();
        }

        if(in_array("group_invite_searchable_users", $type)){
            $data["group_invite_searchable_users"] = $this->userWebService->getSearchedUsersByQuestions();
        }

        if(in_array("newsfeed_forward_searchable_questions", $type)){
            $data["newsfeed_forward_searchable_questions"] = $this->userWebService->getInviteEntitySearchableQuestions();
        }

        if(in_array("newsfeed_forward_searchable_users", $type)){
            $data["newsfeed_forward_searchable_users"] = $this->userWebService->getSearchedUsersByQuestions();
        }

        if(in_array("event_invite_list", $type)){
            $data["event_invite_list"] = $this->eventWebService->getInvitableUsers();
        }

        if(in_array("get_group", $type)){
            $data["get_group"] = $this->groupWebService->getGroup();
            $data["security"] = $this->userWebService->getSecurityInfo();
        }

        if(in_array("events", $type)){
            $data["events"] = $this->eventWebService->getEvents('latest');
        }

        if(in_array("get_event", $type)){
            $data["get_event"] = $this->eventWebService->getEvent();
        }

        if(in_array("get_user_videos", $type)){
            $data["get_user_videos"] = $this->videoWebService->getUserVideos();
        }

        if(in_array("get_user_albums", $type)){
            $data["get_user_albums"] = $this->photoWebService->getUserAlbums();
        }

        if(in_array("get_photo", $type)){
            $data["get_photo"] = $this->photoWebService->getPhoto();
        }

        if(in_array("fetch_users_by_mobile", $type)){
            $data["fetch_users_by_mobile"] = $this->userWebService->fetchUsersByMobile();
        }

        if(in_array("get_album_photos", $type)){
            $data["get_album_photos"] = $this->photoWebService->getAlbumPhotos();
        }

        if(in_array("get_video", $type)){
            $data["get_video"] = $this->videoWebService->getVideo();
        }

        if(in_array("user_profile", $type)){
            $data["user_profile"] = $this->userWebService->getUserInformation(true);
            $data["security"] = $this->userWebService->getSecurityInfo();
//            if(FRMSecurityProvider::checkPluginActive('story', true)){
//                $data['stories'] = STORY_BOL_Service::getInstance()->findFollowingStories(true);
//            }
        }

        if(in_array("notifications", $type)){
            $data["notifications"] = $this->notificationsWebService->getNotifications();
        }

        if(in_array("notifications_setting", $type)){
            $data["notifications_setting"] = $this->notificationsWebService->getNotificationsSetting();
        }

        if(in_array("new_notifications", $type)){
            $data["new_notifications"] = $this->notificationsWebService->getNewNotifications();
        }

        if(in_array("getNews", $type)){
            $data["news"] = $this->newsWebService->getNews();
        }

        if(in_array("get_news_item", $type)){
            $data["news_item"] = $this->newsWebService->getNewsItem();
        }

        if(in_array("get_dashboard", $type)){
            $data["get_dashboard"] = $this->newsfeedWebService->getDashboard();
            $data["security"] = $this->userWebService->getSecurityInfo();
            if(FRMSecurityProvider::checkPluginActive('story', true)){
                $data['stories'] = STORY_BOL_Service::getInstance()->findFollowingStories();
            }
        }

        if(in_array("get_post", $type)){
            $data["get_post"] = $this->newsfeedWebService->getPost();
        }

        if(in_array("get_business_posts", $type)){
            $data["get_business_posts"] = $this->newsfeedWebService->getBusinessPosts();
        }

        if(in_array("get_messages", $type)){
            $data["get_messages"] = $this->mailboxWebService->getMessages();
        }

        if(in_array("get_user_message", $type)){
            $data["get_user_message"] = $this->mailboxWebService->getUserMessage();
        }

        if(in_array("get_chat_media", $type)){
            $data["get_chat_media"] = $this->mailboxWebService->getChatMedia();
        }

        if(in_array("search_chat_messages", $type)){
            $data["search_chat_messages"] = $this->mailboxWebService->searchChatMessages();
        }

        if(in_array("search", $type)){
            $data["search"] = $this->searchWebService->search();
        }

        if(in_array("posted_data", $type)){
            $data["posted_data"] = $this->getNecessaryPostedData();
        }

        if(in_array("forums", $type)){
            $data["forums"] = $this->forumWebService->getForums();
        }

        if(in_array("topics", $type)){
            $data["topics"] = $this->forumWebService->getTopics();
        }

        if(in_array("topic", $type)){
            $data["topic"] = $this->forumWebService->getTopic();
        }

        if(in_array("comments", $type)){
            $data["comments"] = $this->commentWebService->getCommentsInformationFromRequest();
        }

        if(in_array("requests", $type)){
            $data["requests"] = $this->userWebService->getRequests();
        }

        if(in_array("search_friends", $type)){
            $data["search_friends"] = $this->userWebService->searchFriends();
            $data["security"] = $this->userWebService->getSecurityInfo();
        }

        if(in_array("friend_suggestion", $type)){
            $data["friend_suggestion"] = $this->userWebService->friendSuggestion();
        }

        if(in_array("user_privacy", $type)){
            $data["user_privacy"] = $this->privacyWebService->userPrivacy();
        }

        if(in_array("user_blogs", $type)){
            $data["user_blogs"] = $this->blogsWebService->getUserblogs();
        }

        if(in_array("blog", $type)){
            $data["blog"] = $this->blogsWebService->getBlog();
        }

        if(in_array("latest_blogs", $type)){
            $data["latest_blogs"] = $this->blogsWebService->getLatestBlogs();
        }

        if(in_array("valid_extensions", $type)){
            $data["valid_extensions"] = $this->getValidExtensions();
        }

        if(in_array("generated_email_pattern", $type)){
            $data["generated_email_pattern"] = $this->getGeneratedEmailPattern();
        }

        if(in_array("files_info", $type)){
            $data["files_info"] = $this->getFilesInfo();
        }

        if(in_array("user_block_exceed", $type)){
            $data["user_block_exceed"] = $this->userBlockExceed();
        }

        if(in_array("mention_suggestion", $type)){
            $data["mention_suggestion"] = $this->mentionsWebService->getMentionSuggestion();
        }

        if(in_array("blocked_users", $type)){
            $data["blocked_users"] = $this->userWebService->getBlockedUsers();
        }

        if(in_array("following_stories", $type)){
            $data["following_stories"] = $this->storyWebService->getFollowingStories();
        }

        if(in_array("story_seen_list", $type)){
            $data["story_seen_list"] = $this->storyWebService->findStorySeens();
        }

        if(in_array("user_story_list", $type)){
            $data["user_story_list"] = $this->storyWebService->getAllUserStories();
        }

        if(in_array("liked_user_list_story", $type)){
            $data["liked_user_list_story"] = $this->storyWebService->likedUserList();
        }

        if(in_array("user_highlight_categories", $type)){
            $data["user_highlight_categories"] = $this->highlightWebService->getUserHighlightCategories();
        }

        if(in_array("user_highlights", $type)){
            $data["user_highlights"] = $this->highlightWebService->getUserHighlightsList();
        }

        if(in_array("highlights_by_category", $type)){
            $data["highlights_by_category"] = $this->highlightWebService->getHighlightsListByCategoryId();
        }

        if(in_array("get_highlight", $type)){
            $data["get_highlight"] = $this->highlightWebService->getHighlight();
        }

        if(in_array("user_following_list", $type)){
            $data["user_following_list"] = $this->newsfeedWebService->userFollowingList();
        }

        if(in_array("follower_search", $type)){
            $data["follower_search_result"] = $this->newsfeedWebService->searchFollowers();
        }

        if(in_array("user_follower_list", $type)){
            $data["user_follower_list"] = $this->newsfeedWebService->userFollowerList();
        }
        if(in_array("product_hashtag_search", $type)){
            $data["product_hashtag_search"] = $this->marketWebService->productHashtagSearch();
        }
        if(in_array("warning_alert", $type)){
            $data["warning_alert"] = $this->getWarningAlert();
        }


        if(in_array("check_password", $type)){
            $data["check_password"] = $this->passwordsecurityWebService->checkPassword();
        }

        if(in_array("is_active_password", $type)){
            $data["is_active_password"] = $this->passwordsecurityWebService->isActivePassword();
        }

        if(in_array("is_exists_password", $type)){
            $data["is_exists_password"] = $this->passwordsecurityWebService->isExistsPassword();
        }

        if(in_array("password_sections_list", $type)){
            $data["password_sections_list"] = $this->passwordsecurityWebService->passwordSectionList();
        }

        if(in_array("is_section_secure_password", $type)){
            $data["is_section_secure_password"] = $this->passwordsecurityWebService->isSectionSecure();
        }


        return $data;
    }

    public function setMentionsOnText($text, $params = array()) {
        $clearText = str_replace('‌', '', $text);
        $regex_view = '((( |^|\n|\t|>|>|\(|\))@)(\w+))';
        $clearText = str_replace('«', '', $clearText);
        $clearText = str_replace('»', '', $clearText);
        preg_match_all('/'.$regex_view.'/', $clearText, $matches);
        $replacedString = array();
        if(isset($matches[4])){
            foreach($matches[4] as $match){
                $mentionedUser = null;
                if (isset($params['cache']['users']['username'])) {
                    if (isset($params['cache']['users']['username'][$match])) {
                        $mentionedUser = $params['cache']['users']['username'][$match];
                    }
                }else {
                    $mentionedUser = BOL_UserService::getInstance()->findByUsername($match);
                }
                if($mentionedUser){
                    if (!in_array($mentionedUser, $replacedString)) {
                        $text = str_replace('@'.$match, '@'.$match.':'.$mentionedUser->getId(), $text);
                        $replacedString[] = $mentionedUser;
                    }
                }
            }
        }
        return $text;
    }

    private function populateWebServiceActionData($type = array()){
        $data = array();

        // should be placed before check_login
        if(in_array("change_password", $type)){
            $data["change_password"] = $this->userWebService->changePassword();
        }

        /****** SECTION: Endpoints that work for not approved users */
        $notApprovedMode = false;
        if(!OW::getUser()->isAuthenticated() && !empty($_POST['userId']) && !empty($_POST['code'])){
            if($this->getProfileEditHash($_POST['userId']) === $_POST['code']){
                $notApprovedMode = true;
                OW::getUser()->login($_POST['userId']);
            }
        }

        if(in_array("login", $type)){
            $data["login"] = $this->userWebService->login();
        }

        if(in_array("loginSSO", $type)){
            $data["login"] = $this->userWebService->loginSSO();
        }

        if(in_array("loginSSOServerSide", $type)){
            $data["login"] = $this->userWebService->loginSSOServerSide();
        }

        if(in_array("check_verification_code", $type)){
            $data["check_verification_code"] = $this->userWebService->checkVerificationCode();
        }

        if(in_array("check_login", $type)){
            $data["check_login"] = $this->userWebService->checkLogin();
        }

        if(in_array("edit_profile", $type)){
            $data["edit_profile"] = $this->userWebService->editProfile();
        }

        if(in_array("logEndUserCrash", $type)){
            $data["logEndUserCrash"] = $this->logWebService->logEndUserCrash();
        }

        if($notApprovedMode){
            OW::getLogger()->writeLog(OW_Log::NOTICE, 'mobile_native_user_logout', array('message' => 'User_not_approved_mode', 'data' => $data));
            OW::getUser()->logout();
        }
        /******  END of SECTION */

        if (in_array("fill_profile", $type)) {
            $data["fill_profile"] = $this->userWebService->fillProfileQuestion();
        }

        if (in_array("fill_account_type", $type)) {
            $data["fill_account_type"] = $this->userWebService->fillAccountType();
        }

        if(in_array("approve_user", $type)){
            $data["approve_user"] = $this->userWebService->approveUser();
        }

        if(in_array("request_change_from_user", $type)){
            $data["request_change_from_user"] = $this->userWebService->requestChangeFromUser();
        }

        if(in_array("mark_user_message", $type)){
            $data["mark_user_message"] = $this->mailboxWebService->markUserMessage();
        }

        if(in_array("upload_single_file", $type)){
            $data["upload_single_file"] = $this->uploadSingleFile();
        }

        if(in_array("add_news", $type)){
            $data["add_news"] = $this->newsWebService->addNews();
        }

        if(in_array("edit_news", $type)){
            $data["edit_news"] = $this->newsWebService->editNews();
        }

        if(in_array("remove_news", $type)){
            $data["remove_news"] = $this->newsWebService->removeNews();
        }

        if(in_array("create_group", $type)){
            $data["create_group"] = $this->groupWebService->processCreateGroup();
        }

        if(in_array("edit_group", $type)){
            $data["edit_group"] = $this->groupWebService->processEditGroup();
        }

        if(in_array("join_group", $type)){
            $data["join_group"] = $this->groupWebService->joinGroup();
        }

        if(in_array("activate_group", $type)){
            $data["activate_group"] = $this->groupWebService->activateGroup();
        }

        if(in_array("join_event", $type)){
            $data["join_event"] = $this->eventWebService->joinEvent();
        }

        if(in_array("remove_video", $type)){
            $data["remove_video"] = $this->videoWebService->removeVideo();
        }

        if(in_array("remove_photo", $type)){
            $data["remove_photo"] = $this->photoWebService->removePhoto();
        }

        if(in_array("terminate_session", $type)){
            $data["terminate_session"] = $this->userWebService->terminateSession();
        }

        if(in_array("terminate_all_session", $type)){
            $data["terminate_all_session"] = $this->userWebService->terminateAllSessions();
        }

        if(in_array("remove_album", $type)){
            $data["remove_album"] = $this->photoWebService->removeAlbum();
        }

        if(in_array("create_video", $type)){
            $data["create_video"] = $this->videoWebService->createVideo();
        }

        if(in_array("create_photo", $type)){
            $data["create_photo"] = $this->photoWebService->createPhoto();
        }

        if(in_array("create_album", $type)){
            $data["create_album"] = $this->photoWebService->createAlbum();
        }

        if(in_array("create_event", $type)){
            $data["create_event"] = $this->eventWebService->processCreateEvent();
        }

        if(in_array("edit_event", $type)){
            $data["edit_event"] = $this->eventWebService->processEditEvent();
        }

        if(in_array("accept_friend", $type)){
            $data["accept_friend"] = $this->friendsWebService->acceptFriendRequest();
        }

        if(in_array("friend_request", $type)){
            $data["friend_request"] = $this->friendsWebService->friendRequest();
        }

        if(in_array("cancel_request", $type)){
            $data["cancel_request"] = $this->friendsWebService->cancelRequest();
        }

        if(in_array("groups_invite_user", $type)){
            $data["groups_invite_user"] = $this->groupWebService->inviteUser();
        }

        if(in_array("groups_invite_users", $type)){
            $data["groups_invite_users"] = $this->groupWebService->inviteUsers();
        }

        if(in_array("leave_group", $type)){
            $data["leave_group"] = $this->groupWebService->leave();
        }

        if(in_array("delete_group", $type)){
            $data["delete_group"] = $this->groupWebService->deleteGroup();
        }

        if(in_array("groups_accept_invite", $type)){
            $data["groups_accept_invite"] = $this->groupWebService->acceptInvite();
        }

        if(in_array("remove_group_user", $type)){
            $data["remove_group_user"] = $this->groupWebService->removeUser();
        }

        if(in_array("add_group_manager", $type)){
            $data["add_group_manager"] = $this->groupWebService->addGroupManager();
        }

        if(in_array("remove_group_manager", $type)){
            $data["remove_group_manager"] = $this->groupWebService->removeGroupManager();
        }

        if(in_array("add_group_file", $type)){
            $data["add_group_file"] = $this->groupWebService->addFile();
        }

        if(in_array("change_group_coverphoto", $type)){
            $data["change_group_coverphoto"] = $this->groupWebService->changeCoverPhoto();
        }

        if(in_array("delete_group_file", $type)){
            $data["delete_group_file"] = $this->groupWebService->deleteFile();
        }

        if(in_array("edit_group_file", $type)){
            $data["edit_group_file"] = $this->groupWebService->editFile();
        }

        if(in_array("add_group_dir", $type)){
            $data["add_group_dir"] = $this->groupWebService->addDir();
        }

        if(in_array("edit_group_dir", $type)){
            $data["edit_group_dir"] = $this->groupWebService->editDir();
        }

        if(in_array("delete_group_dir", $type)){
            $data["delete_group_dir"] = $this->groupWebService->deleteDir();
        }

        // profile files
        if(OW::getUser()->isAuthenticated() &&
            FRMSecurityProvider::checkPluginActive('frmfilemanager', true))
        {
            if (in_array("add_profile_file", $type)) {
                $data["add_profile_file"] = $this->userWebService->addFile();
            }

            if (in_array("delete_profile_file", $type)) {
                $data["delete_profile_file"] = $this->userWebService->deleteFile();
            }

            if (in_array("edit_profile_file", $type)) {
                $data["edit_profile_file"] = $this->userWebService->editFile();
            }

            if (in_array("add_profile_dir", $type)) {
                $data["add_profile_dir"] = $this->userWebService->addDir();
            }

            if (in_array("edit_profile_dir", $type)) {
                $data["edit_profile_dir"] = $this->userWebService->editDir();
            }

            if (in_array("delete_profile_dir", $type)) {
                $data["delete_profile_dir"] = $this->userWebService->deleteDir();
            }

            if (in_array("save_file_to_profile", $type)) {
                $data["save_file_to_profile"] = $this->userWebService->saveFileToProfile();
            }
        }

        //event
        if(in_array("add_event_file", $type)){
            $data["add_event_file"] = $this->eventWebService->addFile();
        }

        if(in_array("delete_event_file", $type)){
            $data["delete_event_file"] = $this->eventWebService->deleteFile();
        }

        if(in_array("groups_cancel_invite", $type)){
            $data["groups_cancel_invite"] = $this->groupWebService->cancelInvite();
        }

        if(in_array("event_invite_user", $type)){
            $data["event_invite_user"] = $this->eventWebService->inviteUser();
        }

        if(in_array("event_accept_invite", $type)){
            $data["event_accept_invite"] = $this->eventWebService->acceptInvite();
        }

        if(in_array("event_cancel_invite", $type)){
            $data["event_cancel_invite"] = $this->eventWebService->cancelInvite();
        }

        if(in_array("event_change_status", $type)){
            $data["event_change_status"] = $this->eventWebService->changeStatus();
        }

        if(in_array("leave_event", $type)){
            $data["leave_event"] = $this->eventWebService->leave();
        }

        if(in_array("seen_notification", $type)){
            $data["seen_notification"] = $this->notificationsWebService->seenNotification();
        }

        if(in_array("hide_notification", $type)){
            $data["hide_notification"] = $this->notificationsWebService->hideNotification();
        }

        if(in_array("save_notifications_setting", $type)){
            $data["save_notifications_setting"] = $this->notificationsWebService->saveNotificationsSetting();
        }

        if(in_array("send_verification_code_to_mobile", $type)){
            $data["send_verification_code_to_mobile"] = $this->userWebService->sendVerificationCodeToMobile();
        }

        if(in_array("logout", $type)){
            OW::getLogger()->writeLog(OW_Log::NOTICE, 'mobile_native_user_logout', array('message' => 'User_not_approved', 'data' => $data));
            $data["logout"] = $this->userWebService->logout();
        }

        if(in_array("remove_friend", $type)){
            $data["remove_friend"] = $this->friendsWebService->removeFriend();
        }

        if(in_array("change_avatar", $type)){
            $data["change_avatar"] = $this->userWebService->changeAvatar();
        }

        if(in_array("remove_avatar", $type)){
            $data["remove_avatar"] = $this->userWebService->removeAvatar();
        }

        if(in_array("change_user_coverphoto", $type)){
            $data["change_user_coverphoto"] = $this->userWebService->changeCoverPhoto();
        }

        if(in_array("block_user", $type)){
            $data["block_user"] = $this->userWebService->blockUser();
        }

        if(in_array("follow_user", $type)){
            $data["follow_user"] = $this->userWebService->follow();
        }

        if(in_array("unfollow_user", $type)){
            $data["unfollow_user"] = $this->userWebService->unFollow();
        }

        if(in_array("follow_group", $type)){
            $data["follow_group"] = $this->groupWebService->follow();
        }

        if(in_array("unfollow_group", $type)){
            $data["unfollow_group"] = $this->groupWebService->unFollow();
        }

        if(in_array("posted_data", $type)){
            $data["posted_data"] = $this->getNecessaryPostedData();
        }

        if(in_array("like", $type)){
            $data["like"] = $this->newsfeedWebService->like();
        }

        if(in_array("remove_feed", $type)){
            $data["remove_feed"] = $this->newsfeedWebService->removeAction();
        }

        if(in_array("remove_feeds", $type)){
            $data["remove_feeds"] = $this->newsfeedWebService->removeActions();
        }

        if(in_array("remove_like", $type)){
            $data["remove_like"] = $this->newsfeedWebService->removeLike();
        }

        if(in_array("edit_post", $type)){
            $data["edit_post"] = $this->newsfeedWebService->editPost();
        }

        if(in_array("send_message", $type)){
            $data["send_message"] = $this->mailboxWebService->sendMessage();
        }

        if(in_array("forward_message", $type)){
            $data["forward_message"] = $this->mailboxWebService->forwardMessage();
        }

        if(in_array("mute_chat", $type)){
            $data["mute_chat"] = $this->mailboxWebService->muteChat();
        }

        if(in_array("unmute_chat", $type)){
            $data["unmute_chat"] = $this->mailboxWebService->unmuteChat();
        }

        if(in_array("multimedia_call", $type)){
            $data["multimedia_call"] = $this->mailboxWebService->multimediaCall();
        }

        if(in_array("send_post", $type)){
            $data["send_post"] = $this->newsfeedWebService->sendPost();
        }

        if(in_array("add_comment", $type)){
            $data["add_comment"] = $this->commentWebService->addComment();
        }

        if(in_array("like_comment", $type)){
            $data["like_comment"] = $this->commentWebService->likeComment();
        }

        if(in_array("unlike_comment", $type)){
            $data["unlike_comment"] = $this->commentWebService->unlikeComment();
        }

        if(in_array("remove_comment", $type)){
            $data["remove_comment"] = $this->commentWebService->removeComment();
        }

        if(in_array("change_privacy", $type)){
            $data["change_privacy"] = $this->newsfeedWebService->changePrivacy();
        }

        if(in_array("remove_message", $type)){
            $data["remove_message"] = $this->mailboxWebService->removeMessage();
        }

        if(in_array("clear_messages", $type)){
            $data["clear_messages"] = $this->mailboxWebService->clearMessages();
        }

        if(in_array("clear_chat", $type)){
            $data["clear_chat"] = $this->mailboxWebService->deleteConversation();
        }

        if(in_array("edit_message", $type)){
            $data["edit_message"] = $this->mailboxWebService->editMessage();
        }

        if(in_array("add_post_forum", $type)){
            $data["post_forum"] = $this->forumWebService->addPost();
        }

        if(in_array("add_topic_forum", $type)){
            $data["topic_forum"] = $this->forumWebService->addTopic();
        }

        if(in_array("lock_topic", $type)){
            $data["lock_topic"] = $this->forumWebService->lockTopic();
        }

        if(in_array("edit_topic", $type)){
            $data["edit_topic"] = $this->forumWebService->editTopic();
        }

        if(in_array("edit_topic_post", $type)){
            $data["edit_topic_post"] = $this->forumWebService->editTopicPost();
        }

        if(in_array("answer_option", $type)){
            $data["answer_option"] = $this->questionsWebService->addAnswer();
        }

        if(in_array("subscribe_question", $type)){
            $data["subscribe_question"] = $this->questionsWebService->subscribe();
        }

        if(in_array("add_question_option", $type)){
            $data["add_question_option"] = $this->questionsWebService->addQuestionOption();
        }

        if(in_array("remove_question_option", $type)){
            $data["remove_question_option"] = $this->questionsWebService->removeQuestionOption();
        }

        if(in_array("change_question_config", $type)){
            $data["change_question_config"] = $this->questionsWebService->changeQuestionConfig();
        }

        if(in_array("unlock_topic", $type)){
            $data["unlock_topic"] = $this->forumWebService->unlockTopic();
        }

        if(in_array("delete_topic", $type)){
            $data["delete_topic"] = $this->forumWebService->deleteTopic();
        }

        if(in_array("delete_post_forum", $type)){
            $data["delete_post_forum"] = $this->forumWebService->deletePost();
        }

        if(in_array("save_user_privacy", $type)){
            $data["user_privacy"] = $this->privacyWebService->savePrivacy();
        }

        if(in_array("send_contact_us", $type)) {
            $data["contact_us"] = $this->contactusWebService->processSendContactUsMessage();
        }

        if(in_array("add_blog", $type)){
            $data["add_blog"] = $this->blogsWebService->addBlog();
        }

        if(in_array("edit_blog", $type)){
            $data["edit_blog"] = $this->blogsWebService->editBlog();
        }

        if(in_array("remove_blog", $type)){
            $data["remove_blog"] = $this->blogsWebService->removeBlog();
        }

        if(in_array("forgot_password", $type)) {
            $data["forgot_password"] = $this->userWebService->processForgotPassword();
        }

        if(in_array("verify_reset_password_code", $type)){
            $data["verify_reset_password_code"] = $this->userWebService->verifyResetPasswordCode();
        }

        if(in_array("flagItem", $type)){
            $data["flagItem"] = $this->flagWebService->flagItem();
        }

        if(in_array("update_rate_item", $type)){
            $data["update_rate_item"] = $this->updateRate();
        }

        if(in_array("valid_extensions", $type)){
            $data["valid_extensions"] = $this->getValidExtensions();
        }

        if(in_array("generated_email_pattern", $type)){
            $data["generated_email_pattern"] = $this->getGeneratedEmailPattern();
        }

        if(in_array("files_info", $type)){
            $data["files_info"] = $this->getFilesInfo();
        }

        if(in_array("invite_user", $type)){
            $data["invite_user"] = $this->userWebService->inviteUser();
        }

        if(in_array("join", $type)){
            $data["join"] = $this->userWebService->joinAction();
        }

        if(in_array("forward", $type)){
            $data["forward"] = $this->newsfeedWebService->forwardAction();
        }

        if(in_array("forward_message", $type)){
            $data["forward_message"] = $this->newsfeedWebService->forwardMessages();
        }

        if(in_array("remove_messages", $type)){
            $data["remove_messages"] = $this->newsfeedWebService->removeMessages();
        }

        if(in_array("get_forward_list", $type)){
            $data["get_forward_list"] = $this->mailboxWebService->getForwardList();
        }

        if(in_array("conclude_topic", $type)){
            $data["conclude_topic"] = $this->forumWebService->concludeTopic();
        }

        if(in_array("create_invitation_link", $type)){
            $data["create_invitation_link"] = $this->groupWebService->createInvitationLink();
        }

        if(in_array("deactivate_invitation_link", $type)){
            $data["deactivate_invitation_link"] = $this->groupWebService->deactivateInvitationLink();
        }

        if(in_array("save_story", $type)){
            $data["save_story"] = $this->storyWebService->saveStory();
        }

        if(in_array("seen_story", $type)){
            $data["seen_story"] = $this->storyWebService->seenStory();
        }

        if(in_array("seen_stories", $type)){
            $data["seen_stories"] = $this->storyWebService->seenStories();
        }

        if(in_array("delete_story", $type)){
            $data["delete_story"] = $this->storyWebService->deleteStory();
        }

        if(in_array("like_story", $type)){
            $data["like_story"] = $this->storyWebService->likeStory();
        }

        if(in_array("unlike_story", $type)){
            $data["unlike_story"] = $this->storyWebService->unlikeStory();
        }

        if(in_array("add_category", $type)){
            $data["add_category"] = $this->highlightWebService->addNewHighlightCategory();
        }
        if(in_array("category_highlight_collection", $type)){
            $data["category_highlight_collection"] = $this->highlightWebService->addNewHighlightCollection();
        }
        if(in_array("edit_category_highlights", $type)){
            $data["edit_category_highlights"] = $this->highlightWebService->editHighlightCollection();
        }
        if(in_array("add_category_avatar", $type)){
            $data["add_category_avatar"] = $this->highlightWebService->addNewHighlightCategoryAvatar();
        }
        if(in_array("remove_category", $type)){
            $data["remove_category"] = $this->highlightWebService->removeHighlightCategory();
        }
        if(in_array("add_highlight", $type)){
            $data["add_highlight"] = $this->highlightWebService->addHighlight();
        }
        if(in_array("remove_highlight", $type)){
            $data["remove_highlight"] = $this->highlightWebService->removeHighlight();
        }
        if(in_array("promote_user", $type)){
            $data["promote_user"] = $this->marketWebService->addSeller();
        }
        if(in_array("demote_user", $type)){
            $data["demote_user"] = $this->marketWebService->removeSeller();
        }
        if(in_array("send_custom_message_to_user", $type)){
            $data["send_custom_message_to_user"] = $this->broadcastWebService->sendMessageToUserFromOutside();
        }

        if(in_array("create_password", $type)){
            $data["create_password"] = $this->passwordsecurityWebService->createPassword();
        }

        if(in_array("change_password", $type)){
            $data["change_password"] = $this->passwordsecurityWebService->changePassword();
        }

        if(in_array("active_password", $type)){
            $data["active_password"] = $this->passwordsecurityWebService->activePassword();
        }

        if(in_array("deactive_password", $type)){
            $data["deactive_password"] = $this->passwordsecurityWebService->deactivePassword();
        }

        if(in_array("update_sections", $type)){
            $data["update_sections"] = $this->passwordsecurityWebService->updateSections();
        }

        return $data;
    }

    public function getRateInfo($id, $type) {
        $info = BOL_RateService::getInstance()->findRateInfoForEntityItem($id, $type);
        $info['avg_score'] = !isset($info['avg_score']) ? 5 : round($info['avg_score'], 2);
        $info['rates_count'] = !isset($info['rates_count']) ? 0 : (int) $info['rates_count'];
        if (OW::getUser()->isAuthenticated()) {
            $userRate = BOL_RateService::getInstance()->findRate($id, $type, OW::getUser()->getId());
            if ($userRate != null) {
                $info['user_rate'] = (int) $userRate->score;
            }
        }
        return $info;
    }

    public function updateRate() {
        if ( empty($_POST['entityId']) || empty($_POST['entityType']) || empty($_POST['rate']))
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $entityId = (int) $_POST['entityId'];
        $entityType = trim($_POST['entityType']);
        $rate = (int) $_POST['rate'];

        $rateObj = BOL_RateService::getInstance()->processUpdateRate($entityId, $entityType, $rate, OW::getUser()->getId());
        if ($rateObj['valid'] == false) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $rateInfo = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->getRateInfo($entityId, $entityType);

        return array('valid' => true, 'message' => 'authorization_error', 'entityId' => $entityId, 'entityType' => $entityType, 'rateInfo' => $rateInfo);
    }

    public function getBlockUsersInfo($currentUserId, $userId) {
        $info = array();
        $blocked = false;
        $blockedBy = '';
        $permission = true;
        if ($currentUserId != 0 && $currentUserId != null && $userId != $currentUserId) {
            $blocked = BOL_UserService::getInstance()->isBlocked($currentUserId, $userId);
            if (!$blocked) {
                $blocked = BOL_UserService::getInstance()->isBlocked($userId, $currentUserId);
                if ($blocked) {
                    $blockedBy = (int) $currentUserId;
                }
            } else {
                $blockedBy = (int) $userId;
            }
            if ($blockedBy != '') {
                $permission = $blockedBy == $currentUserId;
            }
        }

        $info['isBlocked'] = $blocked;
        $info['blockedBy'] = $blockedBy;
        $info['permission'] = $permission;
        return $info;
    }
}
