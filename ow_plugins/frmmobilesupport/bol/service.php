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
class FRMMOBILESUPPORT_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $deviceDao;
    private $appVersionDao;
    public $AndroidKey = 1;
    public $iOSKey = 2;
    public $nativeFcmKey = 3;
    public $webFcmKey = 4;
    public $COOKIE_SAVE_DAY = 365;
    CONST FRMMOBILESUPPORT_CATCH_REQUEST='frmmobilesupport.catch.request';

    const EVENT_SEND_NOTIFICATION_INCOMPLETE = 'frmmobilesupport.send_notification_incomplete';
    const EVENT_AFTER_SAVE_NOTIFICATIONS = 'frmmobilesupport.after_save_notifications';

    private function __construct()
    {
        $this->deviceDao = FRMMOBILESUPPORT_BOL_DeviceDao::getInstance();
        $this->appVersionDao = FRMMOBILESUPPORT_BOL_AppVersionDao::getInstance();
    }

    public function getAppInformation($type, $currentVersionCode){
        $information = array();
        $information['versionName'] = "";
        $information['versionCode'] = "";
        $information['lastVersionUrl'] = "";
        $information['isDeprecated'] = "false";
        $information['message'] = "";

        $lastVersion = $this->getLastVersions($type);
        if($lastVersion!=null){
            $information['versionName'] = $lastVersion->versionName;
            $information['versionCode'] = $lastVersion->versionCode;
            $information['lastVersionUrl'] = $lastVersion->url;
            $information['message'] = $lastVersion->message;
        }

        if($currentVersionCode!=null && $currentVersionCode != ""){
            $userCurrentVersion = $this->getVersionUsingCode($type, $currentVersionCode);
            if ($userCurrentVersion != null) {
                $information['isDeprecated'] = $userCurrentVersion->deprecated ? "true" : "false";
            }
        }

        return $information;
    }

    /***
     * @param $data
     * @param $cache
     */
    public function sendNotification($data, $cache = array()){
        $lastViewedNotification = 0;
        if (isset($cache['lastViewedNotificationsByUser'][$data['userId']])) {
            $lastViewedNotification = $cache['lastViewedNotificationsByUser'][$data['userId']];
        } else {
            $lastViewedNotification = NOTIFICATIONS_BOL_NotificationDao::getInstance()->getLastViewedNotificationId($data['userId']);
        }
        $devices = null;
        if (isset($cache['usersDevices'][$data['userId']])) {
            $devices = $cache['usersDevices'][$data['userId']];
        }
        FRMMOBILESUPPORT_BOL_Service::getInstance()->sendDataToDevice($data, $lastViewedNotification, $devices);
    }

    /***
     * @param $type
     * @return array
     */
    public function getAllVersions($type){
        return $this->appVersionDao->getAllVersions($type);
    }

    /***
     * @param $type
     * @return mixed
     */
    public function getLastVersions($type){
        return $this->appVersionDao->getLastVersions($type);
    }

    /***
     * @param $id
     */
    public function deleteVersion($id){
        $this->appVersionDao->deleteVersion($id);
    }

    /***
     * @param $id
     * @return mixed
     */
    public function deprecateVersion($id){
        return $this->appVersionDao->deprecateVersion($id);
    }

    /***
     * @param $id
     * @return mixed
     */
    public function approveVersion($id){
        return $this->appVersionDao->approveVersion($id);
    }

    /***
     * @param $type
     * @param $versionCode
     * @return mixed
     */
    public function getVersionUsingCode($type, $versionCode){
        return $this->appVersionDao->getVersionUsingCode($type, $versionCode);
    }

    /***
     * @param $type
     * @param $versionName
     * @param $versionCode
     * @return bool
     */
    public function hasVersion($type, $versionName, $versionCode){
        return $this->appVersionDao->hasVersion($type, $versionName, $versionCode);
    }

    /**
     * @param $type
     * @param $versionName
     * @param $versionCode
     * @param $url
     * @param $message
     * @return bool|FRMMOBILESUPPORT_BOL_AppVersion
     */
    public function saveVersion($type, $versionName, $versionCode, $url, $message = ''){
        return $this->appVersionDao->saveVersion($type, $versionName, $versionCode, $url, $message);
    }

    /***
     * @param $type
     * @return array
     */
    public function getArraysOfVersions($type){
        $versions = $this->getAllVersions($type);
        $lastVersion = $this->getLastVersions($type);
        $versionsArray = array();
        foreach ($versions as $value) {
            $versionInformation = array(
                'versionName' => $value->versionName,
                'versionCode' => $value->versionCode,
                'message' => $value->message,
                'isDeprecated' => $value->deprecated == true ? "0" : "1",
                'time' => UTIL_DateTime::formatSimpleDate($value->timestamp),
                'deleteUrl' => "if(confirm('".OW::getLanguage()->text('frmmobilesupport','delete_item_warning')."')){location.href='" . OW::getRouter()->urlForRoute('frmmobilesupport-admin-delete-value', array('id' => $value->id)) . "';}",
                'downloadFile'=> $value->url
            );
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$value->id,'isPermanent'=>true,'activityType'=>'delete_mobileVersion')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $versionInformation['deleteUrl'] = "if(confirm('".OW::getLanguage()->text('frmmobilesupport','delete_item_warning')."')){location.href='" .
                    OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmmobilesupport-admin-delete-value',
                        array('id' => $value->id)),array('code'=>$code)) . "';}";
            }
            if($value->deprecated){
                $versionInformation['deprecateLabel'] = OW::getLanguage()->text('frmmobilesupport', 'approve');
                $versionInformation['deprecateUrl'] = "location.href='" . OW::getRouter()->urlForRoute('frmmobilesupport-admin-approve-value', array('id' => $value->id)) . "';";
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>$value->id,'isPermanent'=>true,'activityType'=>'approve_mobileVersion')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])) {
                    $code = $frmSecuritymanagerEvent->getData()['code'];
                    $versionInformation['deprecateUrl'] = "location.href='" .
                        OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmmobilesupport-admin-approve-value',
                            array('id' => $value->id)), array('code' => $code)) . "';";
                }
            }else{
                $versionInformation['deprecateLabel'] = OW::getLanguage()->text('frmmobilesupport', 'deprecate');
                $versionInformation['deprecateUrl'] = "location.href='" . OW::getRouter()->urlForRoute('frmmobilesupport-admin-deprecate-value', array('id' => $value->id)) . "';";
                $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                    array('senderId'=>OW::getUser()->getId(),'receiverId'=>$value->id,'isPermanent'=>true,'activityType'=>'deprecate_mobileVersion')));
                if(isset($frmSecuritymanagerEvent->getData()['code'])) {
                    $code = $frmSecuritymanagerEvent->getData()['code'];
                    $versionInformation['deprecateUrl'] = "location.href='" . OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmmobilesupport-admin-deprecate-value', array('id' => $value->id)),array('code'=>$code)) . "';";
                }
            }

            $versionInformation['isLastVersion'] = false;
            if($lastVersion!=null && $lastVersion->versionCode == $value->versionCode){
                $versionInformation['isLastVersion'] = true;
            }

            $versionsArray[] = $versionInformation;
        }

        return $versionsArray;
    }

    /***
     * @param $userId
     * @return array
     */
    public function getUsersDevices($userId){
        return $this->deviceDao->getUserDevices($userId);
    }

    public function deleteInActiveDevicesOfUser($userId){
        $devices = FRMMOBILESUPPORT_BOL_Service::getInstance()->getUsersDevices($userId);
        $title = OW::getConfig()->getValue('base', 'site_name');
        $data = array(
            "userId" => $userId,
            "title" => $title,
            "description" => OW::getLanguage()->text('frmmobilesupport', 'active_devices_passed_limit'),
            "avatarUrl" => "",
            "notificationId" => "0",
        );

        $this->sendDataToDevice($data,0, $devices, [], true);
    }

    /***
     * @param $inputData
     * @param $lastViewedNotification
     * @param null $devices
     * @param $blackList
     * @param $waitForResponse
     */
    public function sendDataToDevice($inputData, $lastViewedNotification, $devices = null, $blackList = array(), $waitForResponse = false){
        if(is_array($inputData)){
            $inputData = (object)$inputData;
        }
        if($devices == null && !is_array($devices)) {
            $devices = FRMMOBILESUPPORT_BOL_Service::getInstance()->getUsersDevices($inputData->userId);
        }

        $androidDeviceTokens = array();
        $iosDeviceTokens = array();
        $nativeSignalTokens = array();
        $webTokens = array();
        $checkDescription = trim($inputData->description);
        foreach ($devices as $device) {
            if($device->type == $this->iOSKey && !in_array($this->iOSKey, $blackList)){
                if (!empty($checkDescription)) {
                    $iosDeviceTokens[] = $device->token;
                }
            }else if($device->type == $this->AndroidKey && !in_array($this->AndroidKey, $blackList)){
                $androidDeviceTokens[] = $device->token;
            }else if($device->type == $this->nativeFcmKey && !in_array($this->nativeFcmKey, $blackList)){
                if (!empty($checkDescription)) {
                    $nativeSignalTokens[] = $device->token;
                }
            }else if($device->type == $this->webFcmKey && !in_array($this->webFcmKey, $blackList)){
                if (!empty($checkDescription)) {
                    $webTokens[] = $device->token;
                }
            }
        }

        //go to notifications page
        $url = OW::getRouter()->urlForRoute('notifications-notifications');

        if(OW::getConfig()->getValue('frmmobilesupport', 'disable_notification_content')){
            $inputData->description = OW::getLanguage()->text('frmmobilesupport', 'new_notification_label');
        }else{
            if(isset($inputData->type) && $inputData->type == 'chat_direct_notification'){
            }else if(isset($inputData->description) && !empty($inputData->description)){
                $inputData->description = OW::getLanguage()->text('frmmobilesupport', 'new_notification_label') . ': '. $inputData->description;
            }
        }

        $sendData = array();

        if(!empty($androidDeviceTokens) || !empty($webTokens)){
            $sendData[] = $this->getJsonDataForSendingToAndroidDevices($inputData, $url, array_merge($androidDeviceTokens, $webTokens) , $lastViewedNotification);
        }

        if(!empty($iosDeviceTokens) || !empty($nativeSignalTokens)){
            $sendData[] = $this->getDefaultJsonDataForSendingToDevices($inputData, $url, array_merge($iosDeviceTokens, $nativeSignalTokens));
        }

        if (!empty($sendData)) {
            foreach ($sendData as $datum) {
                $this->postDataToMobile($datum, $waitForResponse);
            }
        }
    }

    public function onMailboxSendMessage(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['senderId']) && isset($params['recipientId'])) {
            $senderId = $params['senderId'];
            $recipientId = $params['recipientId'];
            $message = $event->getData();
            $isConversationMutedByUser = OW::getEventManager()->trigger(new OW_Event('mailbox.isConversationMutedByUser', array('userId' => $recipientId, 'conversationId' => $message->conversationId)));
            $isConversationMutedByUser = $isConversationMutedByUser->getData();
            if (isset($isConversationMutedByUser['muted']) && !$isConversationMutedByUser['muted']) {
            OW::getEventManager()->trigger(new OW_Event('frmmobilesupport.send_message', array('message' => $message, 'userId' => $senderId, 'opponentId' => $recipientId)));
            }
        }
    }

    public function onMarkConversation(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['conversationIdList']) && isset($params['markType'])){
            $conversationIdList = $params['conversationIdList'];
            $markType = $params['markType'];

            if(!is_array($conversationIdList)){
                return;
            }

            if($markType != 'read'){
                return;
            }

            $data = array();
            foreach ($conversationIdList as $cid){
                $conversation = null;
                if (isset($params['additionalParams']['cache']['conversations'][$cid])) {
                    $conversation = $params['additionalParams']['cache']['conversations'][$cid];
                }
                if ($conversation == null) {
                    $conversation = MAILBOX_BOL_ConversationService::getInstance()->getConversation($cid);
                }
                if($conversation != null){
                    $singleData = array();
                    $singleData['userId1'] = $conversation->initiatorId;
                    $singleData['userId2'] = $conversation->interlocutorId;
                    $singleData['conversationId'] = $cid;
                    $data[] = $singleData;
                }
            }

            foreach ($data as $singleData){
                $item['type'] = 'mark';
                $item['conversationId'] = $singleData['conversationId'];
                $additionalData = array('message' => $item);
                if (OW::getUser()->getId() != $singleData['userId1']) {
                    if (!FRMSecurityProvider::isSocketEnable()) {
                        $this->sendDataUsingFirebaseForUserId($data, $additionalData, $singleData['userId1']);
                    }
                }
                if (OW::getUser()->getId() != $singleData['userId2']) {
                    if (!FRMSecurityProvider::isSocketEnable()) {
                        $this->sendDataUsingFirebaseForUserId($data, $additionalData, $singleData['userId2']);
                    }
                }
            }
        }
    }

    public function onSendMessageAttachment(OW_Event $event){
        if (!FRMSecurityProvider::isSocketEnable()) {
            $params = $event->getParams();
            if (isset($params['messageId'])) {
                $messageId = $params['messageId'];
                $message = MAILBOX_BOL_MessageDao::getInstance()->findById($messageId);
                if ($message == null) {
                    return;
                }
                $item = MAILBOX_BOL_ConversationService::getInstance()->getMessageDataForApi($message);
                if (isset($_POST['_id'])) {
                    $item['_id'] = $_POST['_id'];
                }
                if (isset($item['text'])) {
                    $item['text'] = MAILBOX_BOL_ConversationService::getInstance()->json_decode_text($item['text']);
                }
                $data = array();
                $additionalData = array('message' => $item);
                $this->sendDataUsingFirebaseForUserId($data, $additionalData, $message->senderId);
                $this->sendDataUsingFirebaseForUserId($data, $additionalData, $message->recipientId);
            }
        }
    }

    public function onSendMessage(OW_Event $event){
        if (isset($_FILES) && sizeof($_FILES) > 0) {
            return;
        }
        $params = $event->getParams();
        if(isset($params['message']) && isset($params['userId']) && $params['opponentId']){
            $message = $params['message'];
            $userId = $params['userId'];
            $opponentId = $params['opponentId'];
            if (!FRMSecurityProvider::isSocketEnable()) {
                $item = MAILBOX_BOL_ConversationService::getInstance()->getMessageDataForApi($message);
                if (isset($_POST['_id'])) {
                    $item['_id'] = $_POST['_id'];
                }

                $data = array();
                if (isset($item['text'])) {
                    $item['text'] = MAILBOX_BOL_ConversationService::getInstance()->json_decode_text($item['text']);
                }
                $additionalData = array('message' => $item);
                $this->sendDataUsingFirebaseForUserId($data, $additionalData, $userId);
                $this->sendDataUsingFirebaseForUserId($data, $additionalData, $opponentId);
            }

            //FCM notification
            $lastViewedNotification = NOTIFICATIONS_BOL_NotificationDao::getInstance()->getLastViewedNotificationId($opponentId);
            $userDisplayName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userAvatar = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
            $title = OW::getConfig()->getValue('base', 'site_name');
            $data = (object)[
                'type'=>'chat_direct_notification',
                'description' => $userDisplayName.": ".$message->text,
                'title' => $title,
                'notification_id' => $lastViewedNotification + 1,
                'userId' => $opponentId,
                'avatarUrl' => $userAvatar,
                'type_concat' => $userId,
                'senderRealname' => $userDisplayName,
                'senderUserId' => $userId,
            ];
            $this->sendDataToDevice($data, $lastViewedNotification);
        }
    }

    /***
     * @param $data
     * @param array $additionalData
     * @param null $userId
     * @return null
     * @throws Redirect404Exception
     */
    public function sendDataUsingFirebaseForUserId($data, $additionalData = array(), $userId = null){
        if( $userId == null ){
            return null;
        }

        $devices = FRMMOBILESUPPORT_BOL_Service::getInstance()->getUsersDevices($userId);
        $nativeTokens = array();
        foreach ($devices as $device) {
            if($device->type == $this->nativeFcmKey){
                $nativeTokens[] = $device->token;
            }
        }

        if(!empty($nativeTokens)){
            $url = OW::getRouter()->urlForRoute('notifications-notifications');
            $sendData = $this->getDefaultJsonDataForSendingToDevices($data, $url, $nativeTokens, true, $additionalData);
            $this->postDataToMobile($sendData);
        }

        return null;
    }

    /***
     * @param $sendData
     * @param bool $waitForResponse
     * @return array
     */
    public function postDataToMobile($sendData, $waitForResponse = false)
    {
        $valid = FRMSecurityProvider::sendUsingRabbitMQ($sendData, 'notification');
        if (!$valid) {
            $this->sendDataToFCM($sendData, $waitForResponse);
        }
    }

    public function sendDataToFCM($sendData, $waitForResponse = false) {
        $fcmUrl = OW::getConfig()->getValue('frmmobilesupport','fcm_api_url');
        $fcmKey = OW::getConfig()->getValue('frmmobilesupport','fcm_api_key');

        if ($fcmUrl == null || $fcmUrl == '' || $fcmKey == null || $fcmKey == ''){
            return;
        }

        $params = new UTIL_HttpClientParams();
        $params->setHeader('Content-Type' ,'application/json');
        $params->setHeader('Authorization' ,'key=' . $fcmKey);
        $params->setJson($sendData);
        if(!$waitForResponse){
            $params->setTimeout(0.5);
        }
        try {
            $response = UTIL_HttpClient::post($fcmUrl, $params);
            if ($response != null) {
                $userId = null;
                if ( is_array($sendData) && isset($sendData['notification']['userId'])){
                    $userId = $sendData['notification']['userId'];
                } else if (isset($sendData->notification->userId)) {
                    $userId = $sendData->notification->userId;
                }
                if ($userId == null) {
                    return;
                }
                $devices = FRMMOBILESUPPORT_BOL_Service::getInstance()->getUsersDevices($userId);
                $results = json_decode($response->getBody())->results;

                $orderOfTokensMustBeDeleted = array();
                $count = 0;
                foreach($results as $result){
                    if(isset($result->error) && ($result->error=='InvalidRegistration' || $result->error=='NotRegistered')){
                        $orderOfTokensMustBeDeleted[] = $count;
                    }
                    $count++;
                }

                $count = 0;
                foreach($devices as $device){
                    if(in_array($count, $orderOfTokensMustBeDeleted)){
                        $this->deleteUserDevice($userId, $device->token);
                        OW::getLogger()->writeLog(OW_Log::INFO, 'fcm_delete_device', [ 'result'=>'ok', 'userId' => $userId, 'token' => $device->token]);
                    }
                    $count++;
                }
            }else{
                $results = ['connect_timeout' => true];
            }
            OW::getLogger()->writeLog(OW_Log::INFO, 'fcm_post_to_mobile', [ 'result'=>'ok', 'response' => $results]);
        } catch (Exception $e) {
            OW::getLogger()->writeLog(OW_Log::INFO, 'fcm_post_to_mobile', [ 'result'=>'http_error', 'message' => $e->getMessage()]);
        }
    }

    /***
     * @param $inputData
     * @param $url
     * @param $deviceTokens
     * @param $lastViewedNotification
     * @return mixed
     */
    public function getJsonDataForSendingToAndroidDevices($inputData, $url, $deviceTokens, $lastViewedNotification){
        $data = array();
        $title = null;
        $userId = null;
        if(isset($inputData->title) && $inputData->title != null && $inputData->title != ''){
            $title = $inputData->title;
        }
        if(isset($inputData->userId) && $inputData->userId != null && $inputData->userId != ''){
            $userId = $inputData->userId;
        }
        $description = '';
        if(isset($inputData->description) && $inputData->description != null && $inputData->description != '') {
            $description = strip_tags($inputData->description);
            $replaceTextWithEmojiImg= new OW_Event('emoji.replace_text_emoji', array('text' => $description));
            OW::getEventManager()->trigger($replaceTextWithEmojiImg);
            if(isset($replaceTextWithEmojiImg->getData()['correctedText'])) {
                $description = $replaceTextWithEmojiImg->getData()['correctedText'];
            }
        }
        $avatarUrl = null;
        if(isset($inputData->avatarUrl) && $inputData->avatarUrl != null && $inputData->avatarUrl != '') {
            $avatarUrl = $inputData->avatarUrl;
        }
        if(isset($inputData->type)){
            $data['type'] = $inputData->type;
        }

        $data['lastViewedNotification'] = $lastViewedNotification;
        $data['notificationId'] = isset($inputData->notification_id)?$inputData->notification_id:0;
        $data['title'] = $title;
        $data['description'] = $description;
        $data['avatarUrl'] = $avatarUrl;
        $data['userId'] = $userId;
        $data['url'] = $url;
        $sendData['data'] = $data;
        $sendData["registration_ids"] = $deviceTokens;
        return $sendData;
    }

    /***
     * @param $inputData
     * @param $url
     * @param $deviceTokens
     * @param bool $sendDataOnly
     * @param array $additionalData
     * @return mixed
     * @throws Redirect404Exception
     */
    public function getDefaultJsonDataForSendingToDevices($inputData, $url, $deviceTokens, $sendDataOnly = false, $additionalData = array()){
        $title = null;
        if(isset($inputData->title) && $inputData->title != null && $inputData->title != ''){
            $title = $inputData->title;
        }
        $userId = null;
        if(isset($inputData->userId) && $inputData->userId != null && $inputData->userId != ''){
            $userId = $inputData->userId;
        }
        $senderUserId = null;
        if(isset($inputData->senderUserId) && $inputData->senderUserId != null && $inputData->senderUserId != ''){
            $senderUserId = $inputData->senderUserId;
        }
        $senderRealname = null;
        if(isset($inputData->senderRealname) && $inputData->senderRealname != null && $inputData->senderRealname != ''){
            $senderRealname = $inputData->senderRealname;
        }
        $description = '';
        if(isset($inputData->description) && $inputData->description != null && $inputData->description != '') {
            $description = $inputData->description;
        }
        $avatarUrl = null;
        if(isset($inputData->avatarUrl) && $inputData->avatarUrl != null && $inputData->avatarUrl != '') {
            $avatarUrl = $inputData->avatarUrl;
        }

        $data = array();
        $data['title'] = $title;
        $data['body'] = strip_tags($description);
        $replaceTextWithEmojiImg= new OW_Event('emoji.replace_text_emoji', array('text' => $data['body']));
        OW::getEventManager()->trigger($replaceTextWithEmojiImg);
        if(isset($replaceTextWithEmojiImg->getData()['correctedText'])) {
            $data['body'] = $replaceTextWithEmojiImg->getData()['correctedText'];
        }
        $data['sound'] = "default";
        $data['avatarUrl'] = $avatarUrl;
        $data['url'] = $url;
        $data['userId'] = $userId;
        $data['senderUserId'] = $senderUserId;
        $data['senderRealname'] = $senderRealname;
        if (isset($inputData->notification_id)) {
            if (FRMSecurityProvider::checkPluginActive('notifications', true)) {
                $notification = NOTIFICATIONS_BOL_NotificationDao::getInstance()->findById($inputData->notification_id);
                if (isset($notification)) {
                    $notificationNavigatorData = FRMMOBILESUPPORT_BOL_WebServiceNotifications::getInstance()->preparedNotificationData($notification);
                    if (isset($notificationNavigatorData['pageId'])) {
                        $data['navigate'] = array(
                            'page' => $notificationNavigatorData['page'],
                            'pageId' => $notificationNavigatorData['pageId'],
                            'entityType' => $notificationNavigatorData['entityType'],
                        );
                    }
                }
            }
        }
        if (isset($inputData->type) && $inputData->type == 'chat_direct_notification') {
            $tag = $inputData->type;
            if (isset($inputData->type_concat)) {
                $tag .= $inputData->type_concat;
            }
            $data["tag"] = $tag;
        }

        $sendData = array();

        if (!isset($data['tag']) && isset($data['navigate']) && isset($data['navigate']['pageId'])) {
            $data['tag'] = $data['navigate']['entityType'] . '-' . $data['navigate']['pageId'];
        }

        if(!$sendDataOnly){
            $sendData['notification'] = $data;
            $sendData['data'] = $data;
        }else{
            $data['data'] = $additionalData;
            $sendData['data'] = $data;
        }
        $sendData["registration_ids"] = $deviceTokens;
        $sendData["priority"] = "high";

        if (!isset($sendData['data']['tag']) && isset($sendData['data']['navigate']) && isset($sendData['data']['navigate']['pageId'])) {
            $sendData['data']['tag'] = $sendData['data']['navigate']['entityType'] . '-' . $sendData['data']['navigate']['pageId'];
        }

        return $sendData;
    }

    /***
     * @param $userId
     */
    public function deleteAllDevicesOfUser($userId){
        $this->deviceDao->deleteAllDevicesOfUser($userId);
    }


    /***
     * @param $userId
     * @param $token
     * @param $type
     * @param $cookie
     */
    public function saveDevice($userId, $token, $type, $cookie){
        if(!$this->hasUserDevice($userId, $token)) {
            $allUserDevices = $this->getUsersDevices($userId);
            $canAddDevice = sizeof($allUserDevices) < OW::getConfig()->getValue('frmmobilesupport', 'constraint_user_device');
            if(!$canAddDevice){
                $this->deleteInActiveDevicesOfUser($userId);
            }
        }else{
            $canAddDevice = true;
        }

        if($canAddDevice) {
            /***
             * handles token register in cases of improper logout
             * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
             */
            $ex = new OW_Example();
            $ex->andFieldEqual('token',$token);
            $this->deviceDao->deleteByExample($ex);

            $this->deviceDao->saveDevice($userId, $token, $type, $cookie);
        }
    }

    /***
     * @param $userId
     * @param $token
     * @return array|bool
     */
    public function hasUserDevice($userId, $token){
        return $this->deviceDao->hasUserDevice($userId, $token);
    }

    /***
     * @param $token
     * @return FRMMOBILESUPPORT_BOL_Device
     */
    public function findDevice($token){
        return $this->deviceDao->findDevice($token);
    }

    /***
     * @param $userId
     * @param $token
     * @param $cookie
     * @return FRMMOBILESUPPORT_BOL_Device
     */
    public function findDeviceTokenRow($userId, $token, $cookie){
        return $this->deviceDao->findDeviceTokenRow($userId, $token, $cookie);
    }

    /***
     * @param $userId
     * @param $token
     */
    public function deleteUserDevice($userId, $token){
        $this->deviceDao->deleteUserDevice($userId, $token);
    }

    /***
     * @param $userId
     * @param $cookie
     */
    public function deleteUserDeviceByCookie($userId, $cookie){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('cookie', $cookie);
        $this->deviceDao->deleteByExample($example);
    }

    /***
     * @param $token
     */
    public function deleteDevice($token){
        $this->deviceDao->deleteDevice($token);
    }

    public function checkForUsingOnlyMobile(OW_Event $event){
        $checkUriEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::BEFORE_CHECK_URI_REQUEST));
        if(isset($checkUriEvent->getData()['ignore']) && $checkUriEvent->getData()['ignore']){
            return;
        }
        if(!$this->isUrlInWhitelist() && $this->isUserShoouldUseOnlyMobile()){
            if (OW::getRequest()->isAjax()) {
                exit();
            } else {
                OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobilesupport-use-mobile'));
            }
        }
    }

    /**
     * @return bool
     */
    public function isUrlInWhitelist()
    {
        if (OW::getRequest()->getRequestUri() == 'sign-out' ||
            strpos($_SERVER['REQUEST_URI'], '/mobile/use_mobile_only')!==false ||
            strpos($_SERVER['REQUEST_URI'], 'mobile-version')!==false ||
            strpos($_SERVER['REQUEST_URI'], 'desktop-version')!==false) {
            return true;
        }

        if(FRMSecurityProvider::checkPluginActive('frmpasswordchangeinterval', true)){
            $serviceOfPluginPasswordChangeInterval = FRMPASSWORDCHANGEINTERVAL_BOL_Service::getInstance();
            if ($serviceOfPluginPasswordChangeInterval->isUrlInWhitelist()) {
                return true;
            }
        }

        return false;
    }

    public function isUserShoouldUseOnlyMobile(){
        if(!$this->useMobile() && OW::getUser()->isAuthenticated() && !OW::getUser()->isAdmin()){
            if(!OW::getUser()->isAuthorized('frmmobilesupport', 'show-desktop-version')) {
                return true;
            }
        }

        return false;
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmmobilesupport' => array(
                    'label' => $language->text('frmmobilesupport', 'admin_settings_title'),
                    'actions' => array(
                        'show-desktop-version' => $language->text('frmmobilesupport', 'auth_action_label_show_desktop_version')
                    )
                )
            )
        );
    }

    public function useMobile(){
        return isset($_COOKIE['UsingMobileApp']);
    }

    public function useAndroidMobile(){
        return $_COOKIE['UsingMobileApp']=='android';
    }

    public function useIOSMobile(){
        return $_COOKIE['UsingMobileApp']=='ios';
    }

    public function saveDeviceToken(OW_Event $event)
    {
        $params = $event->getParams();
        if ($this->useMobile() && isset($_COOKIE['MobileTokenNotification']) && OW::getUser()->isAuthenticated()){
            $cookie = null;
            if (isset($params['cookie'])) {
                $cookie = $params['cookie'];
            }else if (isset($_COOKIE['ow_login'])){
                $cookie = $_COOKIE['ow_login'];
            }else{
                return;
            }
            $type = 1;
            if ($this->useAndroidMobile()) {
                $type = $this->AndroidKey;
            } else if ($this->useIOSMobile()) {
                $type = $this->iOSKey;
            }
            $this->saveDevice(OW::getUser()->getId(), $_COOKIE['MobileTokenNotification'], $type, $cookie);
        }
    }

    public function deleteDeviceToken(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['cookies']) && sizeof($params['cookies'])>0){
            $example = new OW_Example();
            $example->andFieldInArray('cookie', $params['cookies']);
            $this->deviceDao->deleteByExample($example);
        }
    }

    public function addMobileCss(OW_Event $event){
        if($this->useMobile()) {
            $cssUrl = OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticCssUrl() . "mobile.css";
            OW::getDocument()->addStyleSheet($cssUrl);

            $jsUrl = OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticJsUrl() . "mobile.js";
            OW::getDocument()->addScript($jsUrl);
        }
        if($this->useMobile() && $this->useAndroidMobile() && isset($_COOKIE['version_code'])) {
            $versionCode = (int) $_COOKIE['version_code'];
            if($versionCode > 33){
                OW::getDocument()->addStyleDeclaration('header#header {display: none;}');
            }
        }
    }

    public function getBrowserInformation(OW_Event $event){
        if($this->useMobile()) {
            if($this->useAndroidMobile()){
                $event->setData(array('browser_name' => OW::getLanguage()->text('frmmobilesupport','android_app_label')));
            }else if($this->useIOSMobile()){
                $event->setData(array('browser_name' => OW::getLanguage()->text('frmmobilesupport','ios_app_label')));
            }
        }
        if(isset($_SERVER['HTTP_USER_AGENT']) && strtolower($_SERVER['HTTP_USER_AGENT'])=='android native app'){
            $event->setData(array('browser_name' => 'Android native app'));
        }else if(isset($_SERVER['HTTP_USER_AGENT']) && strtolower($_SERVER['HTTP_USER_AGENT'])=='ios native app'){
            $event->setData(array('browser_name' => 'iOS native app'));
        }
    }

    public function checkNativeRequest(OW_Event $event){
        if($this->useMobile()) {
            if ($this->useAndroidMobile() || $this->useIOSMobile()) {
                $event->setData(array('is_native' => true));
            }
        }
    }

    public function userLogout(OW_Event $event){
        if($this->useMobile()) {
            $params = $event->getParams();
            if (isset($params['userId'])) {
                $deleteAllDevices = false;
                if (isset($_COOKIE['MobileTokenNotification'])) {
                    $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
                    $existUserDevice = $service->hasUserDevice($params['userId'], $_COOKIE['MobileTokenNotification']);
                    if ($existUserDevice) {
                        $service->deleteUserDevice($params['userId'], $_COOKIE['MobileTokenNotification']);
                    } else {
                        $deleteAllDevices = false;
                    }
                } else {
                    $deleteAllDevices = true;
                }

                if ($deleteAllDevices) {
                    //FRMMOBILESUPPORT_BOL_Service::getInstance()->deleteAllDevicesOfUser($params['userId']);
                }
            }
        }
    }

    /***
     * @param OW_Event $event
     * @return array|void
     */
    public function onNotificationAdd(OW_Event $event){
        $params = $event->getParams();
        $data = $event->getData();
        $cache = array();
        if (isset($data['cache'])){
            $cache = $data['cache'];
        }

        if (isset($params['mobile_notification']) && $params['mobile_notification'] == false) {
            return;
        }

        $fcmUrl = OW::getConfig()->getValue('frmmobilesupport','fcm_api_url');
        $fcmKey = OW::getConfig()->getValue('frmmobilesupport','fcm_api_key');

        if (is_string($data) || $fcmUrl == null || $fcmUrl == '' || $fcmKey == null || $fcmKey == '' || empty($data['avatar'])){
            return;
        }

        foreach ( array('string', 'conten') as $langProperty )
        {
            if ( !empty($data[$langProperty]) && is_array($data[$langProperty]) )
            {
                $key = explode('+', $data[$langProperty]['key']);
                $vars = empty($data[$langProperty]['vars']) ? array() : $data[$langProperty]['vars'];
                $data[$langProperty] = BOL_LanguageService::getInstance()->getText(BOL_LanguageService::getInstance()->getCurrent()->getId(), $key[0], $key[1], $vars);
            }
        }

        if ( empty($data['string']) )
        {
            return array();
        }

        $notification_id = isset($data['notification_id'])?$data['notification_id']:0;
        $title = OW::getConfig()->getValue('base', 'site_name');
        $description = $data['string'];
        $url = isset($data['url'])?$data['url']:null;
        $avatarUrl = null;
        $user = null;
        if(isset($params['userId'])) {
            if (isset($cache['users']) && array_key_exists($params['userId'], $cache['users'])) {
                $user = $cache['users'][$params['userId']];
            } else {
                $user = BOL_UserService::getInstance()->findUserById($params['userId']);
            }
        }
        if(isset($data['avatar']['src'])){
            $avatarUrl = $data['avatar']['src'];
        }
        if ($avatarUrl == null) {
            $avatarUrl = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        }

        if($user != null) {
            $notificationData = array();
            $notificationData['notification_id'] = $notification_id;
            $notificationData['userId'] = $user->getId();
            $notificationData['title'] = $title;
            $notificationData['description']  = $description;
            $notificationData['avatarUrl']  = $avatarUrl;
            $data['url']  = $url;
            FRMMOBILESUPPORT_BOL_Service::getInstance()->sendNotification($notificationData, $cache);
        }
    }

    public function redirectPageByVersion($version) {
        if ($version == null) {
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions'));
        }
        $currentSectionKey = null;
        if ($version != null) {
            if ($version->type == 3) {
                $currentSectionKey = 'android-native-versions';
            } else if ($version->type == 1) {
                $currentSectionKey = 'android-versions';
            } else if ($version->type == 2) {
                $currentSectionKey = 'ios-versions';
            }
        }
        if ($currentSectionKey == null) {
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions'));
        }
        $allSections = $this->getAllSections($currentSectionKey);
        $currentUrl = OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions');
        foreach ($allSections as $section) {
            if ($section['active']) {
                $currentUrl = $section['url'];
            }
        }
        OW::getApplication()->redirect($currentUrl);
    }

    public function getAllSections($sectionKey){
        $sections = array();

        $sections[] = array(
            'sectionId' => "settings",
            'active' => $sectionKey == "settings" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'settings')
        );

        $sections[] = array(
            'sectionId' => "versions",
            'active' => $sectionKey == "versions" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin-versions'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'versions')
        );

        $sections[] = array(
            'sectionId' => "android-versions",
            'active' => $sectionKey == "android-versions" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin-android-versions'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'android_versions')
        );

        $sections[] = array(
            'sectionId' => "ios-versions",
            'active' => $sectionKey == "ios-versions" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin-ios-versions'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'ios_versions')
        );

        $sections[] = array(
            'sectionId' => "android-native-versions",
            'active' => $sectionKey == "android-native-versions" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin-android-native-versions'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'native_versions')
        );

        $sections[] = array(
            'sectionId' => "web-settings",
            'active' => $sectionKey == "web-settings" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin-web-settings'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'web_settings')
        );

        $sections[] = array(
            'sectionId' => "download-show",
            'active' => $sectionKey == "download-show" ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmmobilesupport-admin-download-show'),
            'label' => OW::getLanguage()->text('frmmobilesupport', 'download_show')
        );

        return $sections;
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'FRMMOBILESUPPORT_MCTRL_Service', 'action' => 'index'));
        $event->add(array('controller' => 'FRMMOBILESUPPORT_MCTRL_Service', 'action' => 'getInformation'));
        $event->add(array('controller' => 'FRMMOBILESUPPORT_MCTRL_Service', 'action' => 'action'));
        $event->add(array('controller' => 'FRMMOBILESUPPORT_CTRL_Service', 'action' => 'downloadLatestVersion'));
    }

    public function canUseWebNotifications(){
        if(strpos(OW_URL_HOME, 'http://localhost/')===false && strpos(OW_URL_HOME, 'https://')===false){
            return false;
        }
        if(!OW::getUser()->isAuthenticated()){
            return false;
        }

        $web_config = OW::getConfig()->getValue('frmmobilesupport', 'web_config');
        $web_key = OW::getConfig()->getValue('frmmobilesupport', 'web_key');
        return (!empty($web_config) && !empty($web_key));
    }

    public function hasIOSVersion()
    {
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $iosLastVersion = $service->getLastVersions($service->iOSKey);
        if(isset($iosLastVersion))
        {
            return true;
        }
        return false;
    }

    public function afterActionAdd( OW_Event $event ) {
        $params = $event->getParams();
        $data = $event->getData();
        $action = null;
        if (!isset($data['action']) || !isset($params['feedType']) || !isset($params['feedId'])) {
            return;
        }
        $action = $data['action'];
        if ($action == null) {
            return;
        }


        if (!isset($params['activityType']) || !isset($params['activityId'])) {
            return;
        }

        if (!$this->checkDashboardActivityVisibility($params['activityType'], $params['activityId'], $action)) {
            return;
        }

        if (FRMSecurityProvider::isSocketEnable()) {
            if ($params['feedType'] == 'groups' && FRMSecurityProvider::checkPluginActive('groups', true)) {
                $groupUsers = GROUPS_BOL_GroupUserDao::getInstance()->findUserIdsByGroupId($params['feedId']);
                $actionInfo = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->preparedActionsData(array($action));
                if (sizeof($actionInfo) < 1) {
                    return;
                }
                $socketData = array();
                $socketData['type'] = 'add_post';
                $socketData['params']= array('feedId' => (int) $params['feedId'], 'feedType' => 'groups','post' => $actionInfo[0]);

                OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $groupUsers)));
            }
        }
    }

    public function afterEditPost( OW_Event $event ) {
        $params = $event->getParams();
        if (!isset($params['entityType']) || !isset($params['entityId']) || !isset($params['text'])) {
            return;
        }

        if (isset($params['oldText']) && $params['oldText'] != '' && $params['oldText'] == $params['text']) {
            return;
        }

        if (FRMSecurityProvider::isSocketEnable()) {
            $feed = FRMMOBILESUPPORT_BOL_WebServiceNewsfeed::getInstance()->findFeed($params['entityType'], $params['entityId']);
            if ($feed == null) {
                return;
            }
            if ($feed->feedType == 'groups' && FRMSecurityProvider::checkPluginActive('groups', true)) {
                $text = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->setMentionsOnText($params['text']);
                $groupUsers = GROUPS_BOL_GroupUserDao::getInstance()->findUserIdsByGroupId((int) $feed->feedId);
                $socketData = array();
                $socketData['type'] = 'edit_post';
                $socketData['params']= array('feedId' => (int) $feed->feedId, 'feedType' => 'groups', 'text' => $text, 'entityType' => $params['entityType'], 'entityId' => $params['entityId']);

                OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $groupUsers)));
            }
        }
    }

    public function hasCustomLinkVersion()
    {
        $customLimksHtml=OW::getConfig()->getValue('frmmobilesupport', 'custom_download_link_code');
        if(isset($customLimksHtml)){
            return true;
        }
        return false;
    }

    public function hasWebviewAndroidVersion()
    {
        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $androidLastVersion= $service->getLastVersions($service->AndroidKey);
        if(isset($androidLastVersion))
        {
            return true;
        }
        return false;
    }

    public function anyVersionExists()
    {
        return $this->hasIOSVersion() || $this->hasCustomLinkVersion() || $this->hasWebviewAndroidVersion();
    }
    public function showDownloadLinks()
    {
        if (OW::getConfig()->getValue('frmmobilesupport', 'custom_download_link_activation') && $this->anyVersionExists()) {
            $cssUrl = OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticCssUrl() . "frmmobilesupport.css";
            OW::getDocument()->addStyleSheet($cssUrl);
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            $appendToClassName = 'ow_mobile_app_download_links';
            if(!isset($event->getData()['isMobileVersion']) || $event->getData()['isMobileVersion']==false) {
                $this->addDownloadIconToPlace('.ow_footer_menu', $appendToClassName);
                $this->addDownloadIconToPlace('.ow_sign_up', $appendToClassName);
                $this->addDownloadIconToPlace('.mobile_account_links', $appendToClassName);
            }else{
                $this->addDownloadIconToPlace('.mobile_account_links', $appendToClassName);
            }
        }

        if($this->canUseWebNotifications()){
            $address = OW_URL_HOME . 'manifest.json';
            $head_html = '<link rel="manifest" href="'.$address.'">';
            OW::getDocument()->addCustomHeadInfo($head_html);
        }
    }

    public function addDownloadIconToPlace($placeName, $appendToClassName){
        $DownloadLinkJS = "var iDiv = document.createElement('div');
                               iDiv.className = '".$appendToClassName."';
                               if ($('".$placeName."').size()>0) {
                               $('".$placeName."').append(iDiv);
                               }";

        OW::getDocument()->addOnloadScript($DownloadLinkJS, 1000);

        $service = FRMMOBILESUPPORT_BOL_Service::getInstance();
        $androidLastVersion= $service->getLastVersions($service->AndroidKey);
        $isNewTheme = FRMSecurityProvider::themeCoreDetector() ? true : false;
        if($isNewTheme){
            $service->downloadLinkJS($androidLastVersion, 'app_download_link', 'android', 'androidNew.png', $appendToClassName);
        }else{
            $service->downloadLinkJS($androidLastVersion, 'app_download_link', 'android', 'android.png', $appendToClassName);
        }

        $iosLastVersion = $service->getLastVersions($service->iOSKey);
        if($isNewTheme){
        $service->downloadLinkJS($iosLastVersion, 'app_download_link', 'ios', 'iosNew.png', $appendToClassName);
        }else {
            $service->downloadLinkJS($iosLastVersion, 'app_download_link', 'ios', 'ios.png', $appendToClassName);
        }

        $customLimksHtml=OW::getConfig()->getValue('frmmobilesupport', 'custom_download_link_code');
        if(isset($customLimksHtml)){
            OW::getDocument()->addOnloadScript('if ($(".'.$appendToClassName.'").size()>0) {$(".'.$appendToClassName.'").append("' .UTIL_HtmlTag::escapeJs($customLimksHtml). '")}', 1001);
        }
    }

    public function downloadLinkJS($LastVersion, $className, $childClassname, $imageName, $appendToClassName){
        if(isset($LastVersion)){
            $LastVersionUrl=$LastVersion->url;
            $downloadImgCss = 'a.'.$className.'.'.$childClassname.'{
                                background-image: url("' . OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl(). 'img/' . $imageName . '");}';
            $DownloadLinkJs='if ($(\'.'.$appendToClassName.'\').size()>0) {var ia = document.createElement(\'a\');
                               ia.className = "' .$className. ' ' . $childClassname .  '";
                               ia.href="' .$LastVersionUrl. '";
                               ia.target="_blank";
                               $(\'.'.$appendToClassName.'\').append(ia);}';

            OW::getDocument()->addOnloadScript($DownloadLinkJs, 1001);
            OW::getDocument()->addStyleDeclaration($downloadImgCss);
        }
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param OW_Event $event
     */
    public function onNotificationViewed(OW_Event $event){
        $userId = $event->getParams()['userId'];

        $data = array();
        $data['notification_id'] = 0;
        $data['userId'] = $userId;
        $data['title'] = OW::getConfig()->getValue('base', 'site_name');
        $data['description']  = ' ';
        $data['avatarUrl']  = ' ';
        $data['url']  = OW_URL_HOME;
        FRMMOBILESUPPORT_BOL_Service::getInstance()->sendNotification($data);
    }

    public function checkUrlIsWebService($checkActionUrl = true, $checkInformationUrl = true){
        if(!OW::getConfig()->configExists('frmmobilesupport', 'access_web_service') || OW::getConfig()->getValue('frmmobilesupport', 'access_web_service') == false){
            return false;
        }

        if(!isset($_SERVER['REQUEST_URI'])){
            return false;
        }

        if ($checkActionUrl && strpos($_SERVER['REQUEST_URI'], '/mobile/services/action') !== false) {
            return true;
        }

        if ($checkInformationUrl && strpos($_SERVER['REQUEST_URI'], '/mobile/services/information') !== false) {
            return true;
        }
        return false;
    }

    public function checkUrlIsWebServiceEvent(OW_Event $event){
        $result= $this->checkUrlIsWebService();
        $event->setData(array('isWebService'=>$result));
    }

    public function stripStringEvent(OW_Event $event){
        $params = $event->getParams();
        $changeBrToNewLine=false;
        if (isset($params['string'])) {
            if(isset($params['changeBrToNewLine']))
            {
               $changeBrToNewLine = $params['changeBrToNewLine'];
            }
            $stripedString = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->stripString($params['string'],true,false,$changeBrToNewLine);
            $event->setData(array('string' => $stripedString));
        }
    }

    public function onBeforeUserLimitExceeded(OW_Event $event) {
        if (!OW::getUser()->isAuthenticated()) {
            return;
        }

        $generalWebService = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance();
        $generalWebService->generateWebserviceResult('user_block_exceed', 'info');
    }

    public function getConversationInfo(OW_Event $event){
        $params = $event->getParams();
        if (isset($params['conversation']) && isset($params['count']) && isset($params['returnMessages'])) {
            $conversationInfo = FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance()->preparedConversation($params['conversation'], $params['count'], $params['returnMessages']);
            $event->setData(array('conversationInfo' => $conversationInfo));
        }
    }

    public function checkReceivedMessage(OW_Event $event){
        $data = $event->getData();
        if (!isset($data)) {
            $data = array();
        }
        $params = $event->getParams();
        $requestData = $params['data'];
        if (!isset($requestData['type'])) {
            return;
        }
        $requestType = $requestData['type'];

       if ($requestType == 'mark_user_message' && OW::getUser()->isAuthenticated()) {
            if (!isset($requestData['opponentId'])) {
                return;
            }
            $convId = MAILBOX_BOL_ConversationService::getInstance()->getChatConversationIdWithUserById(OW::getUser()->getId(), $requestData['opponentId']);
            FRMMOBILESUPPORT_BOL_WebServiceMailbox::getInstance()->markMessages($convId, OW::getUser()->getId());
            $data['mark_user_message'] = array(
                'valid' => true,
                'opponentId' => (int) $requestData['opponentId'],
                'markOpponentMessages' => true,
            );
        } else if ($requestType == 'seen_group_post' && OW::getUser()->isAuthenticated()) {
            if (!isset($requestData['groupId'])) {
                return;
            }
            if (FRMSecurityProvider::checkPluginActive('groups', true)) {
                GROUPS_BOL_Service::getInstance()->updateLastSeenForGroupUser($requestData['groupId']);
            }
        } else{
            return;
        }
        $event->setData(json_encode($data));
    }

    public function onBeforeCSRFCheck(OW_Event $event){
        if ($this->checkUrlIsWebService()) {
            $event->setData(array('not_check' => true));
        }
    }

    public function onAfterMessageRemoved(OW_Event $event){
        if (!OW::getUser()->isAuthenticated()){
            return;
        }
        $params = $event->getParams();
        if (!isset($params['senderId']) || !isset($params['recipientId']) || !isset($params['id'])) {
            return;
        }
        $senderId = $params['senderId'];
        $recipientId = $params['recipientId'];
        $messageId = $params['id'];
        if (!FRMSecurityProvider::isSocketEnable()) {
            $data = array();
            $additionalData = array('removedMessageId' => (int)$messageId);
            $this->sendDataUsingFirebaseForUserId($data, $additionalData, $senderId);
            $this->sendDataUsingFirebaseForUserId($data, $additionalData, $recipientId);
        }
    }

    public function onBeforeSessionDelete(OW_Event $event){
        if ($this->checkUrlIsWebService()) {
            $event->setData(array('ignore' => true));
        }
    }

    public function currentUserApproved($checkFillQuestions = false, $questions = null)
    {
        if (OW::getUser()->isAdmin()) {
            return true;
        }
        if(OW::getUser()->isAuthenticated() && empty(trim(OW::getUser()->getUserObject()->accountType)))
        {
            return true;
        }
        if (OW::getUser()->isAuthenticated() && OW::getConfig()->getValue('base', 'mandatory_user_approve')){
            if (!BOL_UserService::getInstance()->isApproved()) {
                if ($checkFillQuestions) {
                    if ($questions == null) {
                        $questions = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId());
                    }
                    if (!empty($questions)) {
                        // User should fill empty questions before checking admin approved
                        return true;
                    }
                }
                return false;
            }
        }
        return true;
    }

    public function isUserApproved($userId)
    {
        if (!isset($userId)) {
            return false;
        }
        if (OW::getConfig()->getValue('base', 'mandatory_user_approve') &&
            !BOL_UserService::getInstance()->isApproved($userId)){
            return false;
        }
        return true;
    }

    public function onPluginsInit(){
        if(!$this->checkUrlIsWebService()){
            return;
        }

        if(OW::getUser()->isAuthenticated()) {
            $user = OW::getUser()->getUserObject();

            $accountType = BOL_QuestionService::getInstance()->findAccountTypeByName($user->accountType);

            if (empty($accountType)) {
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'FRMMOBILESUPPORT_MCTRL_Service', 'action');
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.complete_profile.account_type', 'FRMMOBILESUPPORT_MCTRL_Service', 'getInformation');
            }
        }
        if (!$this->currentUserApproved())
        {
            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'action'
            ));
            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'getInformation'
            ));
        }

        if(OW::getUser()->isAuthenticated()) {
            OW::getRequestHandler()->setCatchAllRequestsAttributes('frmpasswordchangeinterval.catch', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'action'
            ));
            OW::getRequestHandler()->setCatchAllRequestsAttributes('frmpasswordchangeinterval.catch', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'getInformation'
            ));

            $questions = BOL_QuestionService::getInstance()->getEmptyRequiredQuestionsList(OW::getUser()->getId());

            if (!empty($questions)) {
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_required_questions', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'action'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_required_questions', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'getInformation'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'action'
                ));
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile', array(
                    OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                    OW_RequestHandler::ATTRS_KEY_ACTION => 'getInformation'
                ));
            }
        }
    }

    public function onBeforeMobileValidationRedirect(OW_Event $event)
    {
        if($this->checkUrlIsWebService()){
            $event->setData(array('not_redirect' => true));
        }
    }

    public function onBeforePostRequestFailForCSRF(OW_Event $event){
        $scheme = 'http';
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        }
        $url = $scheme . '://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $passPaths = array();
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobilesupport-web-service-get-information',array('type'=>''));
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobilesupport-web-service-get-information-without-type');
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobilesupport-web-service-action',array('type'=>''));
        $passPaths[] = OW::getRouter()->urlForRoute('frmmobilesupport-web-service-action-without-type'); ;

        foreach ($passPaths as $passPath){
            if(strpos($url, $passPath)==0){
                $event->setData(array('pass' => true));
                return;
            }
        }
    }

    public function onMobileNotificationDataReceived(OW_Event $event){
        $params = $event->getParams();
        $pluginKey = $params['pluginKey'];
        $entityType = $params['entityType'];
        $data = $params['data'];
        switch ($pluginKey) {
            case 'groups':
                switch ($entityType){
                    case 'groups-add-file':
                        $event->setData(array('url' => $data['string']['vars']['groupUrl']));
                        break;
                    case 'groups-update-status':
                        //No change is needed
                        break;
                }
                break;
            case 'frmmention':
                //No change is needed
                break;
            case 'forum':
                switch ($entityType){
                    case 'forum_topic_reply':
                        //No change is needed
                        break;
                }
                break;
            case 'frmsecurityessentials':
                switch ($entityType){
                    case 'security-privacy_alert':
                        //No change is needed
                        break;
                }
                break;
            case 'newsfeed':
                switch ($entityType){
                    case 'user_status':
                        //No change is needed
                        break;
                    case 'status_comment':
                        //No change is needed
                        break;
                    case 'status_like':
                        //No change is needed
                        break;
                    case 'groups-status':
                        //No change is needed
                        break;
                }
                break;
            case 'event':
                switch ($entityType){
                    case 'event':
                        $event->setData(array('url' => $data['string']['vars']['url']));
                        break;
                    case 'event-add-file':
                        $event->setData(array('url' => $data['string']['vars']['eventUrl']));
                        break;
                }
                break;
            case 'photo':
                switch ($entityType){
                    case 'photo-add_comment':
                        //No change is needed
                        break;
                }
                break;
            case 'video':
                switch ($entityType){
                    case 'video-add_comment':
                        //No change is needed
                        break;
                }
                break;
            case 'frmnews':
                switch ($entityType) {
                    case 'news-add_comment':
                        //No change is needed
                        break;
                    case 'news-add_news':
                        //No change is needed
                        break;
                }
                break;
            case 'frmcompetition':
                switch ($entityType) {
                    case 'competition-add_competition':
                        //No change is needed
                        break;
                    case 'competition-add_user_point':
                        //No change is needed
                        break;
                    case 'competition-add_group_point':
                        //No change is needed
                        break;
                }
                break;
            case 'frmterms':
                switch ($entityType) {
                    case 'frmterms-terms':
                        $event->setData(array('url' => $data['string']['vars']['url']));
                        break;
                }
                break;
            case 'base':
                switch ($entityType) {
                    case 'base_profile_wall':
                        //No change is needed
                        break;
                }
                break;
            case 'questions':
                switch ($entityType) {
                    case 'questions-post':
                        //No change is needed
                        break;
                    case 'questions-answer':
                        //No change is needed
                        break;
                }
                break;
            case 'frmpasswordchangeinterval':
                switch ($entityType) {
                    case 'frmpasswordchangeinterval':
                        $event->setData(array('url' => OW::getRouter()->urlForRoute('frmprofilemanagement.edit')));
                }
                break;
        }
    }

    public function excludeCatchGetInformationRequest(OW_Event $event)
    {
        if(OW::getUser()->isAuthenticated()) {
            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.complete_profile', array(
                OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMMOBILESUPPORT_MCTRL_Service',
                OW_RequestHandler::ATTRS_KEY_ACTION => 'index'
            ));
        }
    }



    /*              Extracted from frmmobilesupport               */

    public function generalBeforeViewRender(OW_EVENT $event){
        $params = $event->getParams();
        if(!isset($params['targetPage'])) {
            return;
        }

        switch ($params['targetPage']){
            case 'userProfile':
                $username = $params['username'];
                $user = BOL_UserService::getInstance()->findByUsername($username);
                $user_avatar =  BOL_AvatarService::getInstance()->getAvatarUrl($user->getId(), 2);
                $js = $this->createBackMenu(BOL_UserService::getInstance()->getUserUrl($user->getId()),
                    BOL_UserService::getInstance()->getDisplayName($user->getId()),
                    $user_avatar
                   );
                OW::getDocument()->addScriptDeclaration($js);
                break;
            case 'forum':
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('forum-default'),
                    OW::getLanguage()->text('forum','forum'),
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/topics.svg');
                OW::getDocument()->addScriptDeclaration($js);
                break;
            case 'forumGroup':
                $groupId = $params['groupId'];
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('group-default', array('groupId'=>$groupId)),
                    FORUM_BOL_ForumService::getInstance()->getGroupInfo($groupId)->name,
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/topics.svg');
                OW::getDocument()->addScriptDeclaration($js);
                break;
            case 'blogs':
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('blogs'),
                    OW::getLanguage()->text('blogs','list_page_heading'),
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/news.svg');
                OW::getDocument()->addScriptDeclaration($js);
                break;
        }
    }

    public function beforeGroupViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['pageType'])){
            if($param['pageType'] == "userList" || $param['pageType'] == "edit" || $param['pageType'] == "fileList"){
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('groups-view' , array('groupId'=>$param['groupId'])),
                    GROUPS_BOL_Service::getInstance()->findGroupById($param['groupId'])->title,
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/groups.svg');
                OW::getDocument()->addScriptDeclaration($js);
            }
        }
        else{
            $url = OW::getRouter()->urlForRoute("groups-index");
            if(FRMSecurityProvider::checkPluginActive('frmmainpage', true)){
                if(!FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('chatGroups'))
                    $url = OW::getRouter()->urlForRoute('frmmainpage.chatGroups');
                else if(!FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('user-groups'))
                    $url = OW::getRouter()->urlForRoute('frmmainpage.user.groups');
            }
            if(isset($param['forceBackUrl']))
            {
                $url=$param['forceBackUrl'];
            }
            $js = $this->createBackMenu($url,
                OW::getLanguage()->text('groups', 'group_list_heading'),
                OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/groups.svg');
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    public function beforeNewsViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['pageType']) && $param['newsId'] != 0){
            if($param['pageType'] == "edit") {
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('user-entry', array('id' => $param['newsId'])),
                    EntryService::getInstance()->findById($param['newsId'])->title,
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/news.svg');
                OW::getDocument()->addScriptDeclaration($js);
            }
        }
        else{
            $js = $this->createBackMenu(OW::getRouter()->urlForRoute("frmnews"),
                OW::getLanguage()->text('frmnews', 'list_page_heading'),
                OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/news.svg');
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    public function beforeVideoViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['pageType'])){
            if($param['pageType'] == "edit") {
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('view_clip', array('id' => $param['videoId'])),
                    VIDEO_BOL_ClipService::getInstance()->findClipById($param['videoId'])->title,
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/videos.svg');
                OW::getDocument()->addScriptDeclaration($js);
            }
        }
        else{
            $url = OW::getRouter()->urlForRoute("video_list_index");
            if(FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('videos')) {
                $url = OW::getRouter()->urlForRoute('frmmainpage.videos');
            }
            $js = $this->createBackMenu($url,
                OW::getLanguage()->text('video', 'page_title_browse_video'),
                OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/videos.svg');
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    public function beforePhotoViewRender(OW_EVENT $event){
        $url = OW::getRouter()->urlForRoute("photo_list_index");
        if(FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('photos')) {
            $url = OW::getRouter()->urlForRoute('frmmainpage.photos');
        }
        $js = $this->createBackMenu($url,
            OW::getLanguage()->text('photo', 'page_title_browse_photos'),
            OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/photos.svg');
        OW::getDocument()->addScriptDeclaration($js);
    }

    public function beforeCompetitionViewRender(OW_EVENT $event){
        $js = $this->createBackMenu(OW::getRouter()->urlForRoute("frmcompetition.index"),
            OW::getLanguage()->text('frmcompetition', 'competitions'),
            OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/competition.svg');
        OW::getDocument()->addScriptDeclaration($js);
    }

    public function beforeEventViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['pageType'])){
            if($param['pageType'] == "edit" || $param['pageType'] == "fileList"){
                $js = $this->createBackMenu(OW::getRouter()->urlForRoute('event.view' , array('eventId'=>$param['eventId'])),
                    EVENT_BOL_EventService::getInstance()->findEvent($param['eventId'])->title,
                    OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/event.svg');
                OW::getDocument()->addScriptDeclaration($js);
            }
        }
        else{
            $js = $this->createBackMenu(OW::getRouter()->urlForRoute("event.main_menu_route"),
                OW::getLanguage()->text('event', 'main_menu_item'),
                OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/event.svg');
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    /***
     * @param BASE_CLASS_EventCollector $event
     */
    public function addJSForWebNotifications($event){
        if(!$this->canUseWebNotifications()){
            return;
        }

        $web_key = OW::getConfig()->getValue('frmmobilesupport', 'web_key');
        $url = OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticJsUrl() . 'web/firebase_loader.js';

        // js before the end of Body
        $js = '
            <script src="'.OW_URL_HOME.'__/firebase/6.2.0/firebase-app.js"></script>
            <script src="'.OW_URL_HOME.'__/firebase/6.2.0/firebase-auth.js"></script>
            <script src="'.OW_URL_HOME.'__/firebase/6.2.0/firebase-messaging.js"></script>
            <script src="'.OW_URL_HOME.'__/firebase/init.js"></script>
            <script src="'.$url.'"></script>
            <script> loadWebFCM("' . $web_key . '"); </script>';
        $event->add($js);
    }

    public function beforeProfilePagesViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['pageType'])){
            switch ($param['pageType']) {
                case "editProfile":
                    break;
                case "preferences":
                    $menuReplaceJs = '$("section#content").prepend($("div.owm_nav_cap"))';
                    OW::getDocument()->addScriptDeclaration($menuReplaceJs);
                    break;
            }
        }

        if(FRMSecurityProvider::checkPluginActive('frmmainpage', true) && !FRMMAINPAGE_BOL_Service::getInstance()->isDisabled('settings')){
            $js = $this->createBackMenu(OW::getRouter()->urlForRoute('frmmainpage.settings'),
                OW::getLanguage()->text('base', 'mobile_admin_settings'),
                OW::getPluginManager()->getPlugin('frmmainpage')->getStaticUrl() . 'img/'."Settings.svg");
            OW::getDocument()->addScriptDeclaration($js);
        }
        else{
            $js = $this->createBackMenu(BOL_UserService::getInstance()->getUserUrl(OW::getUser()->getId()),
                OW::getLanguage()->text('base', 'my_profile_heading'),
                BOL_AvatarService::getInstance()->getAvatarUrl(OW::getUser()->getId(), 2));
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    public function beforeGroupForumViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['groupId'])) {
            $js = $this->createBackMenu(GROUPS_BOL_Service::getInstance()->
            getGroupUrl(GROUPS_BOL_Service::getInstance()->findGroupById($param['groupId'])),
                GROUPS_BOL_Service::getInstance()->findGroupById($param['groupId'])->title,
                OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/group.svg');
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    public function beforeGroupForumTopicViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['groupId'])) {
            $js = $this->createBackMenu(OW::getRouter()->urlFor('FORUM_MCTRL_Group', 'index', array('groupId' => FORUM_BOL_ForumService::getInstance()->findGroupByEntityId('groups', $param['groupId'])->id)),
                OW::getLanguage()->text('forum','forum_subjects_list'),
                OW::getPluginManager()->getPlugin('frmmobilesupport')->getStaticUrl() . 'img/topics.svg');
            OW::getDocument()->addScriptDeclaration($js);
        }
    }

    public function onRabbitMQNotificationRelease(OW_EVENT $event) {
        $data = $event->getData();
        if (!isset($data) || !isset($data->body)) {
            return;
        }

        $data = $data->body;
        $data = (object) json_decode($data);

        if (!isset($data->itemType) || $data->itemType != 'notification') {
            return;
        }

        $this->sendDataToFCM($data, true);
    }

    public function createBackMenu($backAddress, $backTitle, $icon){
        $backSrc = OW::getThemeManager()->getCurrentTheme()->getStaticUrl() . 'mobile/images/arr_nav_next.svg';
//        $languageTag = BOL_LanguageService::getInstance()->getCurrent()->getTag();
//        if($languageTag == "fa-IR"){
        $js = '
            $("div.owm_nav_cap").append(\'<a href="'. $backAddress . '" class="mobile_back_button_title">' . $backTitle . '</a>\');
            container = document.createElement("a");
            container.classList.add("mobile_back_container");
            container.setAttribute("href", "'. $backAddress . '");
            $("div.owm_nav_cap").append(container);
            $("a.owm_nav_cap_left").remove();
            back = document.createElement("div");
            back.classList.add("mobile_back_menu_back");
            back.style.backgroundImage= "url('.$backSrc.')";
            $("a.mobile_back_container").append(back);
            icon = document.createElement("div");
            icon.classList.add("mobile_back_menu_icon");
            icon.style.backgroundImage= "url('.$icon.')";
            $("a.mobile_back_container").append(icon);
        ';
//        }
//        else if($languageTag == "en"){
//            $js = '';
//        }
        return $js;
    }

    /****************************************************/

    public function getMobileAppLastVersion($type) {
        $lastVersion = null;
        switch ($type){
            case 'android':
                $lastVersion = $this->getLastVersions($this->AndroidKey);
            break;
            case 'ios':
                $lastVersion = $this->getLastVersions($this->iOSKey);
            break;
            case 'native':
                $lastVersion = $this->getLastVersions($this->nativeFcmKey);
            break;
            case 'web':
                $lastVersion = $this->getLastVersions($this->webFcmKey);
        }
        return $lastVersion;
    }

    public function fixInviteText(OW_EVENT $event){
        $appLastVersion = $this->getMobileAppLastVersion('native');
        if (!isset($appLastVersion)) {
            return;
        }

        $text_android = OW::getLanguage()->text("frmmobilesupport","sms_text_android_part",
            array("link" => OW::getRouter()->urlForRoute('frmmobilesupport-latest-version-short', array('type'=>'native'))));
        if(isset($event->getParams()['text'])){
            $text = $event->getParams()['text'];
            $text = $text . "\n" . $text_android;
            $event->setData(['text'=>$text]);
        }
        if(isset($event->getParams()['html'])){
            $html = $event->getParams()['html'];
            $html = $html . "\n <br/>\n<p>" . $text_android . "</p><br/>\n";
            $event->setData(['html'=>$html]);
        }
    }

    public function deleteAllUsersActiveCookies(OW_Event $event)
    {
        $params = $event->getParams();
        $this->deviceDao->deleteAllDevices();
    }

    private function checkDashboardActivityVisibility($activityType, $activityId, $action) {
        $activity = NEWSFEED_BOL_Service::getInstance()->findActivityItem($activityType, $activityId, $action->id);

        $activityVisibility = new OW_Event('newsfeed.activity.visibility', array('activity' => $activity, 'action' => $action));
        OW::getEventManager()->trigger($activityVisibility);

        if (isset($activityVisibility->getData()['visibilityChanged'])) {
            $activityVisibility = $activityVisibility->getData()['visibilityChanged'];
            if (($activityVisibility & NEWSFEED_BOL_Service::VISIBILITY_AUTHOR) == 0) {
                return false;
            }
        }
        return true;
    }

    // voice and video call
    public function callActions( OW_Event $event ) {
        $params = $event->getParams()['params'];
        $userId = $event->getParams()['userId'];
        $params['userId'] = $userId;
        MULTIMEDIA_BOL_Service::getInstance()->callActionController($params);
    }

}
