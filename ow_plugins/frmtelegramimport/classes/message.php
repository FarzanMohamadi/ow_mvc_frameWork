<?php
abstract class FRMTELEGRAMIMPORT_CLASS_Message{
    const TEXT_MESSAGE = "TEXT";
    const PHOTO_MESSAGE = "PHOTO";
    const VIDEO_MESSAGE = "VIDEO";
    const AUDIO_MESSAGE = "AUDIO";
    const FILE_MESSAGE = "FILE";
    const STICKER_MESSAGE = "STICKER";
    const ANIMATION_MESSAGE = "ANIMATION";

    public $id;
    public $type;
    public $date;
    public $edited;
    public $text;
    protected $attachmentIdList;
    protected $previewIdList;

    public function __construct($message){
        $this->id = $message->id;
        $this->type = $message->type;
        $this->date = $message->date;
        $this->edited = $message->edited;
        $this->text = $message->text;
        $this->attachmentIdList = array();
        $this->previewIdList = array();
    }
    protected abstract function buildAttachmentPreviewIdList();
    public abstract function typeOfMessage();
    public abstract function isMessagePublishable();

    public function publishToGroups($groupIds,$source)
    {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        if(!$this->isMessagePublishable())
            return false;

        $userId = OW::getUser()->getId();
        $groupService = GROUPS_BOL_Service::getInstance();
        $importChannelService = FRMTELEGRAMIMPORT_BOL_Service::getInstance();

        foreach($groupIds as $groupId)
        {
            if (!$importChannelService->canImportChannel($groupId)) {
                continue;
            }
            $group = $groupService->findGroupById($groupId);
            $actionData = $this->buildActionData($groupId);
            $status = strip_tags(nl2br($actionData->status));
            $status = str_replace('&#8235;', '', $status);


            $attachId = null;
            $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
            $statusVisibility = $private
                ? 14 // VISIBILITY_FOLLOW + VISIBILITY_AUTHOR + VISIBILITY_FEED
                : 15; // Visible for all (15)
            $content = '';

            /*
             * create new album and photo(s)
             */
            $feedId = $groupId;
            $_POST['feedType'] = "groups";
            $_POST['feedId'] = $feedId;
            $_POST['visibility'] = $statusVisibility;
            $_POST['status'] = $status;


            /*
             * create new attachment(s) file
             */
            if (isset($actionData->attachmentIdList)){
                $this->handleMessageAttachments($actionData,$userId);
            }

            $event = new OW_Event("feed.before_content_add", array(
                "feedType" => $_POST['feedType'],
                "feedId" => $_POST['feedId'],
                "visibility" => $_POST['visibility'],
                "userId" => $userId,
                "status" => $status,
                "type" => empty($content["type"]) ? "text" : $content["type"],
                "data" => $content
            ));

            OW::getEventManager()->trigger($event);

            $data = $event->getData();
            if (!empty($data)) {
                if (!empty($attachId)) {
                    BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("newsfeed", $attachId);
                }
                $item = empty($data["entityType"]) || empty($data["entityId"])
                    ? null
                    : array(
                        "entityType" => $data["entityType"],
                        "entityId" => $data["entityId"]
                    );

                $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.on.update.group.status', array('feedId' => $_POST['feedId'],
                    'feedType' => $_POST['feedType'], 'status' => $_POST['status'], 'statusId' => $item['entityId']));
                OW::getEventManager()->trigger($eventIisGroupsPlusManager);

            }
            $status = UTIL_HtmlTag::autoLink($status);

            $eventForward = new OW_Event('base.on.before.forward.status.create', array('actionData' => $actionData));
            OW::getEventManager()->trigger($eventForward);

            $actionAdditionalData = array(
                "content" => $content,
                "attachmentId" => $attachId,
            );
            if(isset($eventForward->getData()['data'])){
                $actionAdditionalData = array_merge($eventForward->getData()['data']);
            }

            $out = NEWSFEED_BOL_Service::getInstance()
                ->addStatus(OW::getUser()->getId(), $_POST['feedType'], $_POST['feedId'], $_POST['visibility'], $status, $actionAdditionalData);

        }
    }

    private function buildActionData($group)
    {
        $userID = OW::getUser()->getId();
        $groupId = $group->id;
        $groupTitle=$group->title;;
        $groupUrl = OW::getRouter()->urlForRoute('groups-view',array('groupId'=> $groupId));
        $this->buildAttachmentPreviewIdList();
        $actionData = array(
            'content' => array(
                'format'=>'text',
                'vars' => array()
            ),
            'attachmentId' => null,
            'statusId' => null,
            'status' => $this->text,
            'contentImage' => null,
            'view'=> array(
                'iconClass' => "ow_ic_comment"
            ),
            'data' => array(
                'userId' => $userID,
                'status' => $this->text
            ),
            'attachmentIdList' => $this->attachmentIdList,
            'previewIdList' => $this->previewIdList,
            'context' => array(
                'label' => $groupTitle,
                'url' => $groupUrl
            ),
            'contextFeedType' => 'groups',
            'contextFeedId' => $groupId
        );
        $jsonString = json_encode($actionData);

        return json_decode($jsonString);
    }

    private function handleMessageAttachments($actionData){
        $attachmentCount = 1;
        $newAttachment_feed_data = '';
        foreach ($actionData->attachmentIdList as $newAttachmentId)
        {
            if ($attachmentCount > 1) {
                $newAttachment_feed_data = $newAttachment_feed_data . '-' . $attachmentCount . ':' . $newAttachmentId;
            } else {
                $newAttachment_feed_data = $attachmentCount . ':' . $newAttachmentId;
            }
            $attachmentCount++;
        }
        $_POST['attachment_feed_data']=$newAttachment_feed_data;


        /*
         * get preview images from original post
         */
        $previewCount = 1;
        $newPreviewIdList='';
        if(sizeof($actionData->previewIdList)>0) {
            foreach ($actionData->previewIdList as $previewId) {
                if ($previewCount > 1) {
                    $newPreviewIdList = $newPreviewIdList . '-' . $previewId;
                } else {
                    $newPreviewIdList = $previewId;
                }
                $previewCount++;
            }
            $_POST['attachment_preview_data'] = $newPreviewIdList;
        }
    }
}