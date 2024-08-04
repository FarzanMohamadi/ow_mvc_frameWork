<?php

require_once OW_DIR_LIB . 'rss' . DS . 'rss.php';

/**
 * Class FRMGROUPSRSS_BOL_Service
 */
class FRMGROUPSRSS_BOL_Service
{
    const SET_RSS_FOR_GROUP_ON_CREATE = 'frmgroupsrss.set.rss.for.group.on.create';
    const SET_RSS_FOR_GROUP_ON_EDIT = 'frmgroupsrss.set.rss.for.group.on.edit';
    const GET_RSS_LINKS_FOR_GROUP = 'frmgroupsrss.get.rss.links.for.group';

    const GROUP_RSS_COUNT_FOR_EACH_INTERVAL = 50;
    const MAXIMUM_RSS_COUNT_FOR_EACH_GROUP = 3;

    private $groupRssDao;
    private $groupDto;

    private static $classInstance;
    public static function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance=new self();
        }
        return self::$classInstance;
    }

    private function __construct()
    {
        $this->groupRssDao = FRMGROUPSRSS_BOL_GroupRssDao::getInstance();
    }

    /**
     * @param $prefix
     * @param $key
     * @param array|null $vars
     * @return mixed|string
     */
    private function text($prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }

    /**
     * @param OW_Event $event
     */
    public function addGroupSettingElements(OW_Event $event)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsrss', 'add')) {
            return;
        }
        $params = $event->getParams();
        $data = $event->getData();

        if (!isset($params['form'])) {
            return;
        }

        $form = $params['form'];

        $rssLinkField = new TagsInputField('rssLinks');
        $rssLinkField->setLabel($this->text('frmgroupsrss', 'rss_link_field_label'));
        $rssLinkField->setInvitation($this->text('frmgroupsrss', 'rss_link_field_invitataion'));
        $rssLinkField->addValidator(new FRMGROUPSRSS_TagInputValidator(self::MAXIMUM_RSS_COUNT_FOR_EACH_GROUP));
        if (isset($params['groupId'])) {
            // editing group
            $rssLinks = $this->getGroupRssLinks($params['groupId']);
            $rssLinkField->setValue($rssLinks);
        }

        $form->addElement($rssLinkField);

        $data['rssLinks'] = true;
        $data['form'] = $form;
        $event->setData($data);
    }

    /**
     * @param OW_Event $event
     */
    public function setRssForGroupOnCreate(OW_Event $event)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsrss', 'add')) {
            return;
        }
        $params = $event->getParams();

        if(!isset($params['groupId'])
            || !isset($params['rssLinks'])
            || !isset($params['creatorId'])
            || empty($params['rssLinks']))
        {
            return;
        }

        $this->saveGroupRssLinks($params['rssLinks'], $params['groupId'],$params['creatorId']);
    }

    /**
     * @param OW_Event $event
     */
    public function setRssForGroupOnEdit(OW_Event $event)
    {
        $params = $event->getParams();

        if(!isset($params['groupId'])
            || !isset($params['rssLinks'])
            || !isset($params['creatorId']))
        {
            return;
        }
        
        if(empty($params['rssLinks']))
        {
            $params['rssLinks'] = array();
        }

        $this->editGroupRssLinks($params['rssLinks'], $params['groupId'], $params['creatorId']);
    }

    /**
     * @param OW_Event $event
     */
    public function updateNotifierIdCronRss(OW_Event $event)
    {
        $params = $event->getParams();

        if(!isset($params['group']))
        {
            return;
        }

        if (defined('OW_CRON') && !OW::getUser()->isAuthenticated()) {
            $event->setData(array('userId'=>$params['group']->userId));
        }
    }

    /**
     * @param array $rssLinks
     * @param $groupId
     * @param $creatorId
     */
    public function saveGroupRssLinks(array $rssLinks, $groupId, $creatorId)
    {
        $this->groupRssDao->saveGroupRssLinks($rssLinks, $groupId, $creatorId);
    }

    /**
     * @param array $rssLinks
     * @param $groupId
     * @param $creatorId
     */
    public function editGroupRssLinks(array $rssLinks, $groupId, $creatorId)
    {
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsrss', 'add')) {
            return;
        }
        $groupRssRecordsBeforeEdit =  $this->groupRssDao->findLinksByGroupId($groupId);
        foreach ($groupRssRecordsBeforeEdit as $groupRssRecordBeforeEdit)
        {
            if(!in_array($groupRssRecordBeforeEdit->rssLink, $rssLinks)){
                $this->groupRssDao->deleteById($groupRssRecordBeforeEdit->id);
            }
        }

        foreach ($rssLinks as $rssLink)
        {
            if(!empty($rssLink) && !$this->groupRssDao->linkExistsForGroup($rssLink, $groupId)){
                $this->groupRssDao->saveGroupRssLink($rssLink, $groupId, $creatorId);
            }
        }
    }

    /**
     * @param $groupId
     * @return array
     */
    public function getGroupRssLinks($groupId)
    {
        $groupRssRecords = $this->groupRssDao->findLinksByGroupId($groupId);
        $rssLinks = array();
        foreach ($groupRssRecords as $groupRssRecord){
            $rssLinks[] = $groupRssRecord->rssLink;
        }
        return $rssLinks;
    }

    /**
     * @param OW_Event $event
     */
    public function getGroupRssLinksEvent(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();

        if(!isset($params['groupId']))
        {
            return;
        }
        if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('frmgroupsrss', 'add')) {
            return;
        }
        $rssLinks = $this->getGroupRssLinks($params['groupId']);
        $data['rssLinks'] = $rssLinks;
        $event->setData($data);
    }

    /**
     * @return bool
     */
    public function canManageRssGroups()
    {
        $canManageRss = false;
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmgroupsrss', 'add')) {
            $canManageRss = true;
        }
        return $canManageRss;
    }

    public function sendRssFeedsToGroupsCronJob()
    {
        $records = $this->groupRssDao->getGroupRssForInterval(self::GROUP_RSS_COUNT_FOR_EACH_INTERVAL);
        foreach ($records as $record)
        {
            $groupId = $record->groupId;
            $creatorId = $record->creatorId;
            $rssLink = $record->rssLink;
            $rssFeedsDetails = $this->getRssFeedsDetails($rssLink);
            $previousLastFeedDate = $record->lastRssFeedDate;

            foreach ($rssFeedsDetails as $rssFeedsDetail)
            {
                if(strtotime($rssFeedsDetail['date'])!=false) {
                    $currentFeedDate = strtotime($rssFeedsDetail['date']);
                }else{
                    $currentFeedDate = $rssFeedsDetail['date'];
                }

                if(!isset($previousLastFeedDate) || $currentFeedDate > $previousLastFeedDate)
                {
                    $this->createFeedForRss($rssFeedsDetail, $creatorId, $groupId);
                }

                if(!isset($lastFeedDate) || $currentFeedDate > $lastFeedDate)
                {
                    $lastFeedDate = $currentFeedDate;
                }
            }

            if(isset($lastFeedDate))
                $this->groupRssDao->updateLastFeedDate($record->id, $lastFeedDate);

            $this->groupRssDao->updateLastUpdateDate($record->id, time());
        }
    }

    public function removeDeletedGroupsRssDataCronJob(){
        if(FRMSecurityProvider::checkPluginActive('groups', true)) {
            FRMGROUPSRSS_BOL_GroupRssDao::getInstance()->removeDeletedGroupsRssLink();
        }
    }

    /**
     * @param $rssLink
     * @return array|void
     * @throws Exception
     */
    public function getRssFeedsDetails($rssLink)
    {
        $config = OW::getConfig();

        if ( !$config->configExists('frmgroupsrss', 'feeds_count') )
        {
            return;
        }

        $maxFeedCountForEachRSS = $config->getValue('frmgroupsrss', 'feeds_count');

        $rss = array();
        try {
            $rssIterator = RssParcer::getIterator($rssLink, $maxFeedCountForEachRSS);
            foreach ($rssIterator as $item) {
                $rss[] = (array)$item;
            }
        }catch (Exception $e){

        }
        return $rss;
    }

    public function removeRssForDeletedGroup($groupId)
    {
        if(FRMSecurityProvider::checkPluginActive('groups', true)) {
            FRMGROUPSRSS_BOL_GroupRssDao::getInstance()->removeRssForDeletedGroup($groupId);
        }
    }

    /**
     * @param $rssDetails
     * @param $userId
     * @param $groupId
     */
    public function createFeedForRss($rssDetails, $userId, $groupId)
    {

        if(!isset($this->groupDto) || $this->groupDto->id != $groupId)
        {
            $this->groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }

        if(!isset( $this->groupDto ))
        {
            $this->removeRssForDeletedGroup($groupId);
            return;
        }
        $private = $this->groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
        $statusVisibility = $private
            ? 14 // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
            : 15; // Visible for all (15)
        $status = strip_tags(nl2br($rssDetails['title'] . PHP_EOL . $rssDetails['description'] . PHP_EOL . $rssDetails['link']));
        $status = str_replace('&#8235;', '', $status);
        $status = UTIL_HtmlTag::autoLink($status);

        $_POST['feedType'] = 'groups';
        $_POST['feedId'] = $groupId;
        $_POST['visibility'] = $statusVisibility;
        $_POST['status'] = $status;

        $content = '';

        $event = new OW_Event("feed.before_content_add", array(
            "feedType" => $_POST['feedType'],
            "feedId" => $_POST['feedId'],
            "visibility" => $_POST['visibility'],
            "userId" => $userId,
            "status" => $_POST['status'],
            "type" => empty($content["type"]) ? "text" : $content["type"],
            "data" => $content
        ));

        OW::getEventManager()->trigger($event);

        // upload and add image if rss feed has image
        BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);
        if(!empty($rssDetails['image'])){
            $result = OW::getStorage()->fileGetContent($rssDetails['image']);
        }

        if(isset($result) && !empty($result)){
            $fileName = time() . '_' . $groupId . '.jpg';
            $filePath = BOL_AttachmentService::getInstance()->getAttachmentsDir(). $fileName;
            if (OW::getStorage()->fileExists($filePath)) {
                OW::getStorage()->removeFile($filePath);
            }
            OW::getStorage()->fileSetContent($filePath, $result);

            $item = array();
            $item['name'] = $fileName;
            $item['type'] = 'image/'.'jpg';
            $item['error'] = 0;
            $item['size'] = UTIL_File::getFileSize($filePath,false);
            $pluginKey = 'frmgroupsrss';
            $bundle = FRMSecurityProvider::generateUniqueId('rss-groups' . "feed1");
            $tempFileId = BOL_FileTemporaryService::getInstance()->addTemporaryFile($filePath,$fileName,$userId);
            $item['tmp_name']= BOL_FileTemporaryService::getInstance()->getTemporaryFilePath($tempFileId);
            $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile($pluginKey, $item, $bundle);
            $newAttachmentIds[] = $dtoArr['dto']->id;
            $previewIdList[]= $dtoArr['dto']->id;
            $attachmentCount = 1;
            foreach ($newAttachmentIds as $newAttachmentId)
            {
                if ($attachmentCount > 1) {
                    $newAttachment_feed_data = $newAttachment_feed_data . '-' . $attachmentCount . ':' . $newAttachmentId;
                } else {
                    $newAttachment_feed_data = $attachmentCount . ':' . $newAttachmentId;
                }
                $attachmentCount++;
            }
            $_POST['attachment_feed_data'] = $newAttachment_feed_data;


            /*
             * get preview images from original post
             */
            $previewCount = 1;
            $newPreviewIdList='';
            if(sizeof($previewIdList)>0) {
                foreach ($previewIdList as $previewId) {
                    if ($previewCount > 1) {
                        $newPreviewIdList = $newPreviewIdList . '-' . $previewId;
                    } else {
                        $newPreviewIdList = $previewId;
                    }
                    $previewCount++;
                }
                $_POST['attachment_preview_data'] = $newPreviewIdList;
            }
        }else{
            $_POST['attachment_preview_data'] = null;
            $_POST['attachment_feed_data'] = null;
        }

        NEWSFEED_BOL_Service::getInstance()->addStatus($userId,$_POST['feedType'],  $_POST['feedId'], $_POST['visibility'],  $status);
    }
}

class FRMGROUPSRSS_TagInputValidator extends OW_Validator
{
    private $maxCount;

    public function __construct( $maxCount = 3 )
    {
        $this->maxCount = $maxCount;
    }

    public function isValid( $value )
    {
        // removeEmptyValues
        $value = array_filter($value);

        foreach($value as $url){
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->setErrorMessage(OW::getLanguage()->text('frmgroupsrss', 'invalid_link'));
                return false;
            }
        }

        if ( count($value) < $this->maxCount+1 )
        {
            return true;
        }

        $this->setErrorMessage(OW::getLanguage()->text('frmgroupsrss', 'maximum_rss_count_exceeds',  array('max_count'=> $this->maxCount)));
        return false;
    }
}
