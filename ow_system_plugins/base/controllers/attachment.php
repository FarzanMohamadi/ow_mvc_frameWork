<?php
/**
 * @package ow_core
 * @since 1.0
 */
class BASE_CTRL_Attachment extends OW_ActionController
{
    /**
     * @var BOL_AttachmentService
     */
    private $service;

    private $allowPreview = array("jpg","jpeg","png","gif","bmp", "mp4", "mp3","ogg","aac","mov");

    public function __construct()
    {
        $this->service = BOL_AttachmentService::getInstance();
    }

    public function delete( $params )
    {
        exit;
    }

    public function addLink()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $url = $_POST['url'];

        $urlInfo = parse_url($url);
        if ( empty($urlInfo['scheme']) )
        {
            $url = 'http://' . $url;
        }

        $url = str_replace("'", '%27', $url);

        $oembed = UTIL_HttpResource::getOEmbed($url);
        $event = new OW_Event('frmsecurityessentials.on.after.read.url.embed', array('stringToFix'=>$oembed['title']));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['fixedString'])){
            $oembed['title']= $event->getData()['fixedString'];
        }
        $event = new OW_Event('frmsecurityessentials.on.after.read.url.embed', array('stringToFix'=>$oembed['description']));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['fixedString'])){
            $oembed['description']= $event->getData()['fixedString'];
        }
        $oembedCmp = new BASE_CMP_AjaxOembedAttachment($oembed);

        $attacmentUniqId = $oembedCmp->initJs();

        unset($oembed['allImages']);

        $response = array(
            'content' => $this->getMarkup($oembedCmp->render()),
            'type' => 'link',
            'result' => $oembed,
            'attachment' => $attacmentUniqId
        );

        echo json_encode($response);

        exit;
    }

    private function getMarkup( $html )
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $markup = array();
        $markup['html'] = $html;

        $onloadScript = $document->getOnloadScript();
        $markup['js'] = empty($onloadScript) ? null : $onloadScript;

        $styleDeclarations = $document->getStyleDeclarations();
        $markup['css'] = empty($styleDeclarations) ? null : $styleDeclarations;

        return $markup;
    }
    /* 1.6.1 divider */

    public function addPhoto( $params )
    {
        $resultArr = array('result' => false, 'message' => 'General error');
        $bundle = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_GET['flUid']));

        if ( OW::getUser()->isAuthenticated() && !empty($_POST['flUid']) && !empty($_POST['pluginKey']) && !empty($_FILES['attachment']) )
        {
            $pluginKey = $_POST['pluginKey'];
            $item = $_FILES['attachment'];

            try
            {
                $dtoArr = $this->service->processUploadedFile($pluginKey, $item, $bundle, array('jpg', 'jpeg', 'png', 'gif'));
                $resultArr['result'] = true;
                $resultArr['url'] = $dtoArr['url'];
            }
            catch ( Exception $e )
            {
                $resultArr['message'] = $e->getMessage();
            }
        }

        $errorData = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_ERROR_RENDER, array('errorData' => $resultArr)));
        if(isset($errorData->getData()['errorData'])){
            $resultArr = $errorData->getData()['errorData'];
        }
        exit("<script>if(parent.window.owPhotoAttachment['" . $bundle . "']){parent.window.owPhotoAttachment['" . $bundle . "'].updateItem(" . json_encode($resultArr) . ");}</script>");
    }

    public function addFile()
    {
        $respondArr = array();
        $bundle = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($_GET['flUid']));
        if ( OW::getUser()->isAuthenticated() && !empty($_POST['flData']) && !empty($_POST['pluginKey']) && !empty($_FILES['ow_file_attachment']) )
        {
            $respondArr['noData'] = false;
            $respondArr['items'] = array();
            $nameArray = json_decode(urldecode($_POST['flData']), true);
            $pluginKey = $_POST['pluginKey'];
            $caption = empty($_POST['caption'])?'':$_POST['caption'];

            $finalFileArr = array();
            $DbIds=array();
            $virusNames=array();

            foreach ( $_FILES['ow_file_attachment'] as $key => $items )
            {
                foreach ( $items as $index => $item )
                {
                    if ( !isset($finalFileArr[$index]) )
                    {
                        $finalFileArr[$index] = array();
                    }

                    $finalFileArr[$index][$key] = $item;
                }
            }

            $containsVirus=array();
            foreach ( $finalFileArr as $item )
            {
                $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event('frmclamav.is_file_clean', array('path' => $item['tmp_name'])));
                if(isset($checkAnotherExtensionEvent->getData()['clean'])){
                    $isClean = $checkAnotherExtensionEvent->getData()['clean'];
                    if(!$isClean)
                    {
                        $containsVirus[]=$item['name'];
                        array_push($virusNames, $item['name']);
                        continue;
                    }
                }
                try
                {
                    $dtoArr = $this->service->processUploadedFile($pluginKey, $item, $bundle);
                    $respondArr['result'] = true;
                    $extension = ' ';
                    if (isset(pathinfo($dtoArr['dto']->getOrigFileName())['extension'])) {
                        $extension .= strtolower(pathinfo($dtoArr['dto']->getOrigFileName())['extension']);
                    }
                    if ( !in_array($dtoArr['dto']->id, $DbIds) && in_array(trim($extension),$this->allowPreview))
                    {
                        array_push($DbIds, $dtoArr['dto']->id);
                    }
                }
                catch ( Exception $e )
                {
                    $respondArr['items'][$nameArray[$item['name']]] = array('result' => false, 'message' => $e->getMessage());
                }

                if ( !array_key_exists($nameArray[$item['name']], $respondArr['items']) )
                {
                    $respondArr['items'][$nameArray[$item['name']]] = array('result' => true, 'dbId' => $dtoArr['dto']->getId());
                }
            }

            $items = $this->service->getFilesByBundleName($pluginKey, $bundle);

            $thumbnails = null;
            if (isset($_POST['thumbnail'])) {
                $thumbnails = $_POST['thumbnail'];
            }
            OW::getEventManager()->trigger(new OW_Event('base.attachment_uploaded', array('pluginKey' => $pluginKey, 'uid' => $bundle, 'files' => $items ,'caption'=>$caption, 'thumbnails' => $thumbnails )));
        }
        else
        {
            $respondArr = array('result' => false, 'message' => 'General error', 'noData' => true);
        }
        if($pluginKey=='frmnewsfeedplus') {
            $attachmentArray = array("dbIds" => $DbIds, "virusNames" => $virusNames , "script" => "<script>if(parent.window.owFileAttachments['" . $bundle . "']){parent.window.owFileAttachments['" . $bundle . "'].updateItems(" . json_encode($respondArr) . ");}</script>");
        }
        else{
            $attachmentArray = array("script" => "<script>if(parent.window.owFileAttachments['" . $bundle . "']){parent.window.owFileAttachments['" . $bundle . "'].updateItems(" . json_encode($respondArr) . ");}</script>");
        }

        if($pluginKey=='mailbox' && sizeof($containsVirus)>0)
        {
            $virusFilesListString="";
            if(sizeof($containsVirus) == 1) {
                $virusFilesListString= implode($containsVirus);
            }else{
                foreach ($containsVirus as $virus)
                {
                    $virusFilesListString= $virusFilesListString.'  -  '.$virus;
                }
            }
            $virusDetectAlert=OW::getLanguage()->text('frmclamav','virus_file_found', array('file' => $virusFilesListString));
            $respondArr = array('result' => false, 'message' => $virusDetectAlert, 'noData' => true);
            $attachmentArray = array("script" => "<script>if(parent.window.owFileAttachments['" . $bundle . "']){parent.window.owFileAttachments['" . $bundle . "'].updateItems(" . json_encode($respondArr) . ");}</script>");
        }
        exit(json_encode($attachmentArray));
    }

    public function deleteFile()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            exit;
        }

        $fileId = !empty($_POST['id']) ? (int) $_POST['id'] : -1;
        $this->service->deleteAttachment(OW::getUser()->getId(), $fileId);

        exit;
    }

}
