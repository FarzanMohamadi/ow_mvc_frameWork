<?php
class FRMGROUPSPLUS_CMP_FileListWidget extends BASE_CLASS_Widget
{

    /***
     * FRMGROUPSPLUS_CMP_FileListWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $groupId = $params->additionalParamList['entityId'];
        $groupDto = null;
        if (isset($params->additionalParamList['group'])) {
            $groupDto = $params->additionalParamList['group'];
        }
        if ($groupDto == null) {
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }

        $canEdit = false;
        if (isset($params->additionalParamList['currentUserIsManager']) && $params->additionalParamList['entityId'] == $groupDto->id) {
            $isCurrentUserManager = $params->additionalParamList['currentUserIsManager'];
            $canEdit = $isCurrentUserManager || GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto, false);
        } else {
            $canEdit = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
        }

        $this->assign('canEdit',$canEdit);
        $count = ( empty($params->customParamList['count']) ) ? 10 : (int) $params->customParamList['count'];
        $this->assign('view_all_files', OW::getRouter()->urlForRoute('frmgroupsplus.file-list', array('groupId' => $groupId)));
        $list = FRMGROUPSPLUS_BOL_Service::getInstance()->findFileList($groupId, 0, $count);

        $filelist = array();
        $attachmentIds = array();
        $deleteUrls = array();

        $secureFilePluginActive = OW::getUser()->isAuthenticated() && FRMSecurityProvider::checkPluginActive('frmsecurefileurl', true);
        $cachedParams = array();
        if ($secureFilePluginActive) {
            $keyFiles = array();
            foreach ($list as $item) {
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

        foreach ( $list as $item )
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

            $fileNameArr = explode('.',$item->fileName);
            $fileNameExt = end($fileNameArr);
            $filelist[$item->id]['fileUrl'] = $this->getAttachmentUrl($item->fileName, $cachedParams);

            $filelist[$item->id]['iconUrl'] = FRMGROUPSPLUS_BOL_Service::getInstance()->getProperIcon(strtolower($fileNameExt));
            $filelist[$item->id]['truncatedFileName'] = $fileName;
            $filelist[$item->id]['fileName'] = $item->getOrigFileName();
            $filelist[$item->id]['name'] =$item->id;
//            if($item->userId==OW::getUser()->getId() || $canEdit) {
//                $deleteUrls[$item->id] = OW::getRouter()->urlForRoute('frmgroupsplus.deleteFile', array('attachmentId' => $item->id, 'groupId' => $groupId));
//            }
        }

        $showAdd=true;
        $isChannel=false;
        if ($groupDto == null) {
            $groupDto= GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }

        if(OW::getUser()->isAuthenticated()){
            $isUserInGroup = false;
            if (isset($params->additionalParamList['currentUserIsMemberOfGroup']) && $params->additionalParamList['entityId'] == $groupId) {
                $isUserInGroup = $params->additionalParamList['currentUserIsMemberOfGroup'];
            } else {
                $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
            }

            if(!$isUserInGroup) {
                $showAdd=false;
            }

            if (isset($params->additionalParamList['isChannel'])) {
                $isChannel = $params->additionalParamList['isChannel'];
            } else {
                $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget', array('groupId'=>$groupId, 'group' => $groupDto)));
                if (isset($channelEvent->getData()['channelParticipant'])) {
                    $isChannelParticipant = $channelEvent->getData()['channelParticipant'];
                    if( $isUserInGroup && isset($isChannelParticipant) && $isChannelParticipant ){
                        $isChannel=true;
                    }
                }
            }
            $isManager = false;
            if (isset($params->additionalParamList['currentUserIsManager'])) {
                $isManager = $params->additionalParamList['currentUserIsManager'];
            }
            if ((($groupDto != null && $groupDto->userId == OW::getUser()->getId()) || $isManager) && $isChannel) {
                $isChannel = false;
            }
        }

        $isAuthorizedUpload=true;
        $groupSettingEvent = OW::getEventManager()->trigger(new OW_Event('can.upload.in.file.widget', array('groupId'=>$groupId, 'additionalInfo' => $params->additionalParamList)));
        if(isset($groupSettingEvent->getData()['accessUploadFile'])) {
            $isAuthorizedUpload = $groupSettingEvent->getData()['accessUploadFile'];
        }

        $canEdit = false;
        if (isset($params->additionalParamList['currentUserIsManager']) && $params->additionalParamList['entityId'] == $groupDto->id) {
            $isCurrentUserManager = $params->additionalParamList['currentUserIsManager'];
            $canEdit = $isCurrentUserManager || GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto, false);
        } else {
            $canEdit = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
        }

        if(!$canEdit) {
            if (!$isAuthorizedUpload) {
                $showAdd = false;
            }
            else if ($isAuthorizedUpload && $isChannel) {
                $showAdd = false;
            }
        }
        if(!OW::getUser()->isAuthenticated())
            $showAdd = false;
        $this->assign("showAdd", $showAdd);

        $this->assign("fileList", $filelist);
        $this->assign("attachmentIds", $attachmentIds);
        $this->assign('deleteUrls', $deleteUrls);
        $plugin = OW::getPluginManager()->getPlugin('frmgroupsplus');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'frmgroupsplus.js');
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl() . 'frmgroupsplus.css');
        $this->assign('deleteIconUrl', $plugin->getStaticUrl().'images/trash.svg');
        $this->assign("filesCount", FRMGROUPSPLUS_BOL_Service::getInstance()->findFileListCount($groupId));
        $this->assign("groupId", $groupId);
        return !empty($filelist);
    }


    public function getAttachmentUrl($name, $params = array())
    {
        return OW::getStorage()->getFileUrl($this->getAttachmentDir($name), false, $params);
    }

    public function getAttachmentDir($name)
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS .$name ;
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('frmgroupsplus', 'widget_files_settings_count'),
            'value' => 10
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmgroupsplus', 'widget_files_title'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}