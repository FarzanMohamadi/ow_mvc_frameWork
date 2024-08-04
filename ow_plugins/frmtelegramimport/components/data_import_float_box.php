<?php
class FRMTELEGRAMIMPORT_CMP_DataImportFloatBox extends OW_Component{
    public function __construct($iconClass, $groupId)
    {
        parent::__construct();
        $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId());
        if (!$isUserInGroup )
        {
            throw new Redirect404Exception();
        }
        $form = FRMTELEGRAMIMPORT_BOL_Service::getInstance()->getFloatBoxImportForm($groupId);
        $this->addForm($form);

        $service = FRMTELEGRAMIMPORT_BOL_Service::getInstance();
        $channelsInfo = $service->getChannelsInfo($groupId);
        $this->assign('loaderIcon',$this->getIconUrl('LoaderIcon'));
        if(isset($channelsInfo)){
            $this->assign('dataIsAvailable',true);
            $this->assign('channelsInfo',$channelsInfo);
        }else{
            $errorMessage = OW::getLanguage()->text('frmtelegramimport','data_is_not_available_error_message');
            $helpUrl = 'javascript:showHelpWindow()';
            $helpTitle = OW::getLanguage()->text('frmtelegramimport','telegram_widget_help');

            $this->assign('dataIsAvailable',false);
            $this->assign('errorMessage',$errorMessage);
            $this->assign('helpUrl',$helpUrl);
            $this->assign('helpTitle',$helpTitle);
        }
    }
    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmgroupsplus')->getStaticUrl(). 'images/'.$name.'.gif';
    }
}