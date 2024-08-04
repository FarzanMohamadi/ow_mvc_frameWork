<?php
/**
 * Created by PhpStorm.
 * User: Farzan Mohammadi
 * Date: 5/7/2019
 * Time: 10:01 AM
 */

/***
 * Class FRMNEWSFEEDPLUS_CMP_RenderAttachmentNoPreview
 */
class FRMNEWSFEEDPLUS_CMP_RenderAttachmentNoPreview extends OW_Component
{
    public function __construct($attachmentsList = array(), $params = array())
    {
        parent::__construct();
        $attachmentService = BOL_AttachmentService::getInstance();
        $attachmentDir = $attachmentService->getAttachmentsDir();
        $AttachmentItemsParams = array();

        foreach ($attachmentsList as $attachment) {
            $filePath = $attachmentDir . $attachment->fileName;
            $downloadUrl = OW::getStorage()->getFileUrl($filePath, false, $params);
            $itemParams = array();
            $itemParams['downloadUrl'] = $downloadUrl;
            $itemParams['filename'] = $attachment->getOrigFileName();
            if(isset(pathinfo($attachment->getOrigFileName())['extension'])){
                $itemParams['extension'] = strtolower(pathinfo($attachment->getOrigFileName())['extension']);
            }else{
                $itemParams['extension'] = 'file';
            }
            $AttachmentItemsParams[] = $itemParams;
        }

        $this->assign('attachmentItemsParams',$AttachmentItemsParams);
        $this->assign('attachmentBox',true);
    }
}


