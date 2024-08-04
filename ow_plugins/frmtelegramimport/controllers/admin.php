<?php
class FRMTELEGRAMIMPORT_CTRL_Admin extends ADMIN_CTRL_Abstract{
    public function import($params){
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        if(!$service->isAdmin()){
            throw new Redirect404Exception();
        }
        $groupIds=array();
        $action = OW::getRouter()->urlForRoute('frmtelegramimport.import');
        $importForm = new Form(FRMTELEGRAMIMPORT_BOL_Service::$CHANNEL_IMPORT_FORM_NAME);
        $importForm->setAction($action);
        $data= $service->fetchData();
        if(isset($data)){
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
                $field = new CheckboxField($channelId);
                $field->setValue(false);
                $field->setLabel($channel->name);
                $importForm->addElement($field);
            }
            $submit = new Submit('import');
            $importForm->addElement($submit);
            $groups = $service->getUserGroups();
            $n=0;
            foreach ($groups as $group){
                $groupId = $group['groupId'];
                $key = 'grp'.$groupId;
                $group['key'] = $key;
                $groups[$n] = $group;
                $n=$n+1;

                $field = new CheckboxField($key);
                $field->setValue(false);
                $importForm->addElement($field);
            }
            $this->addForm($importForm);
            if ( OW::getRequest()->isPost()){
                if($importForm->isValid($_POST)){
                    foreach ($groups as $group){
                        $key = $group['key'];
                        $element = $importForm->getElement($key);
                        if($element->getValue()){
                            $groupIds[] = $group['groupId'];
                        }
                    }
                    if(sizeof($groupIds)>0){
                        foreach ($channelsInfo as $channelInfo){
                            $channelId=$channelInfo['channelId'];
                            $element = $importForm->getElement($channelId);
                            $isChecked=$element->getValue();
                            if($isChecked){
                                $channelData = $channelInfo['channelData'];
                                $service->publishToGroup($channelData,$groupIds);
                                OW::getFeedback()->info(OW::getLanguage()->text('frmtelegramimport', 'import_successfully'));
                            }
                        }
                    }
                }
                $service->clearImportDir();
                $this->redirect(OW::getRouter()->urlForRoute('frmtelegramimport.upload'));
            }
            OW::getDocument()->addOnloadScript("
                $('#instant_search_txt_input').on('change input',function () {
                    var q = $(this).val();
                    $('.asl_groups .ow_group_list_item').each(function(i,obj){
                        if(obj.innerText.indexOf(q)>=0)
                            obj.style.display = 'inline-block'
                        else
                            obj.style.display = 'none'
                    });
                });
            ");
            $this->assign('dataIsAvailable',true);
            $this->assign('groups',$groups);
            $this->assign('channelsInfo',$channelsInfo);
        }else{
            $errorMessage = OW::getLanguage()->text('frmtelegramimport','data_is_not_available_error_message');
            $helpUrl = OW::getRouter()->urlForRoute('frmtelegramimport.admin.help');
            $helpTitle = OW::getLanguage()->text('frmtelegramimport','telegram_widget_help');

            $uploadTitle = OW::getLanguage()->text('frmtelegramimport','upload_file_submit_label');
            $uploadUrl = OW::getRouter()->urlForRoute('frmtelegramimport.upload');

            $this->assign('dataIsAvailable',false);
            $this->assign('errorMessage',$errorMessage);
            $this->assign('helpUrl',$helpUrl);
            $this->assign('helpTitle',$helpTitle);
            $this->assign('uploadTitle',$uploadTitle);
            $this->assign('uploadUrl',$uploadUrl);

        }
    }
    public function upload($params){
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        if(!$service->isAdmin()){
            throw new Redirect404Exception();
        }
        $uploadForm = $service->getChannelUploadForm(OW::getRouter()->urlForRoute('frmtelegramimport.upload'));
        $this->addForm($uploadForm);
        if ( OW::getRequest()->isPost())
        {
            if ( isset($_POST['form_name']) && $_POST['form_name'] == FRMTELEGRAMIMPORT_BOL_Service::$CHANNEL_UPLOAD_FORM_NAME && $uploadForm->isValid($_POST))
            {
                $service->extractZipFile();
                $this->redirect(OW::getRouter()->urlForRoute('frmtelegramimport.import'));
            }
        }
        $helpUrl = OW::getRouter()->urlForRoute('frmtelegramimport.admin.help');
        $helpTitle = OW::getLanguage()->text('frmtelegramimport','telegram_widget_help');
        $this->assign('helpUrl',$helpUrl);
        $this->assign('helpTitle',$helpTitle);
    }
    public function help($params){
        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        if(!$service->isAdmin()){
            throw new Redirect404Exception();
        }
        $guideline = $service->getAdminHelp();
        $this->assign('guideline',$guideline);
    }
}