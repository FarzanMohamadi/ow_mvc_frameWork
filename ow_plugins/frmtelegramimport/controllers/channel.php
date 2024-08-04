<?php
class FRMTELEGRAMIMPORT_CTRL_Channel extends OW_ActionController {
    public function uploadToGroup($params){
        $groupId = (int) $params['groupId'];
        $groupIds=array();
        $groupIds[] = $groupId;
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $uploadForm = $service->getFloatBoxUploadForm($groupId);
        $this->addForm($uploadForm);
        if ( OW::getRequest()->isPost()&& $uploadForm->isValid($_POST))
        {
            if ( isset($_POST['form_name']) && $_POST['form_name'] == FRMTELEGRAMIMPORT_BOL_Service::$CHANNEL_UPLOAD_FORM_NAME )
            {
                if((isset($_FILES['file']['name']) && ($_FILES['file']['name']) != ""))
                    $service->extractZipFile();
                else{
                    OW::getFeedback()->error(OW::getLanguage()->text('frmtelegramimport', 'no_file_selected'));
                    $this->redirect(OW::getRouter()->urlForRoute('groups-view', array('groupId' => $groupId)));

                }
            }

            exit();
        }
    }
    public function importToGroup($params){
        $groupId = (int) $params['groupId'];
        $groupIds=array();
        $groupIds[] = $groupId;
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $importForm = $service->getFloatBoxImportForm($groupId);
        $channelsInfo = $service->getChannelsInfo($groupId);
        if ( OW::getRequest()->isPost() && $importForm->isValid($_POST) && isset($channelsInfo)){
            foreach ($channelsInfo as $channelInfo){
                $channelId=$channelInfo['channelId'];
                $element = $importForm->getElement($channelId);
                $isChecked=$element->getValue();
                if($isChecked){
                    $channelData = $channelInfo['channelData'];
                    $service->publishToGroup($channelData,$groupIds);
                }
            }
            $service->clearImportDir();
            exit();
        }
    }
}