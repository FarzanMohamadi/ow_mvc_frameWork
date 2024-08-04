<?php
class FRMNEWSFEEDPLUS_CTRL_VideoThumbnail extends OW_ActionController
{

    public function index()
    {
        $respondArray = array();
        $attachmentId = trim($_POST['videoName']);
        $videoFile = BOL_AttachmentDao::getInstance()->findById($attachmentId);
        $thumbnail = FRMNEWSFEEDPLUS_BOL_ThumbnailDao::getInstance()->findById($attachmentId);

        if ( empty($_POST['videoName']) || empty($_POST['canvasData']) || !isset($videoFile) || !empty($thumbnail) )
        {
            $respondArray['messageType'] = 'error';
            $respondArray['message'] = '_ERROR_';
            echo json_encode($respondArray);
            exit;
        }

        $userService = OW::getUser();
        $currentUser = $userService->getUserObject();

        if( $userService != null && $userService->isAuthenticated() && $currentUser)
        {

            $imageName = UTIL_String::getRandomString(10) . '.png';
            $tmpVideoImageFile = FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getThumbnailFileDir($imageName);
            $rawData = $_POST['canvasData'];
            $filteredData = explode(',', $rawData);

            $valid = FRMSecurityProvider::createFileFromRawData($tmpVideoImageFile, $filteredData[1], 20);
            if( $valid )
            {
                $tmpVideoImageFileUtilImage = new UTIL_Image($tmpVideoImageFile, "PNG");

                $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event('frmclamav.is_file_clean', array('path' => $tmpVideoImageFile)));
                if(isset($checkAnotherExtensionEvent->getData()['clean'])) {
                    if (!$checkAnotherExtensionEvent->getData()['clean']) { // check if it's clean
                        $respondArray['messageType'] = 'error';
                        $respondArray['message'] = '_ERROR_';
                        echo json_encode($respondArray);
                        exit;
                    }
                }

                $tmpVideoImageFileUtilImage->saveImage($tmpVideoImageFile);
                FRMNEWSFEEDPLUS_BOL_ThumbnailDao::getInstance()->addThumbnail($attachmentId, $imageName, $currentUser->getId());
            }
        }
        exit;
    }

}