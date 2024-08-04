<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.controllers
 * @since 1.0
 */
class FRMCFP_CMP_FileUploadFloatBox extends OW_Component
{
    public function __construct($iconClass, $eventId)
    {
        $eventDto = FRMCFP_BOL_Service::getInstance()->findEvent($eventId);
        if (!isset($eventDto))
        {
            throw new Redirect404Exception();
        }
        parent::__construct();
        $form = FRMCFP_BOL_Service::getInstance()->getUploadFileForm($eventId);
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmcfp','add_file'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmcfp','add_file'));
        $this->assign('loaderIcon',$this->getIconUrl('LoaderIcon'));
        $this->addForm($form);
    }

    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmcfp')->getStaticUrl(). 'images/'.$name.'.svg';
    }
}


