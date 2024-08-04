<?php
class FRMTELEGRAMIMPORT_CMP_FileUploadFloatBox extends OW_Component{
    public function __construct($iconClass, $groupId)
    {
        parent::__construct();
        $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId());
        if (!$isUserInGroup )
        {
            throw new Redirect404Exception();
        }
        $form = FRMTELEGRAMIMPORT_BOL_Service::getInstance()->getFloatBoxUploadForm($groupId);
        $this->assign('loaderIcon',$this->getIconUrl('LoaderIcon'));
        $this->addForm($form);

        $helpUrl = 'javascript:showHelpWindow()';
        $helpTitle = OW::getLanguage()->text('frmtelegramimport','telegram_widget_help');

        $this->assign('helpUrl',$helpUrl);
        $this->assign('helpTitle',$helpTitle);
    }

    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmgroupsplus')->getStaticUrl(). 'images/'.$name.'.gif';
    }

}