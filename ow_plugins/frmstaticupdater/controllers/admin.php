<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmstaticupdater.admin.controllers
 * @since 1.0
 */
class FRMSTATICUPDATER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index( array $params = array() )
    {
        $receiverFileID = rand(1,10000);
        $fileCode = FRMSecurityProvider::generateUniqueId();
        $receiverLanguageID = rand(1,10000);
        $languageCode = FRMSecurityProvider::generateUniqueId();
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$receiverFileID,'isPermanent'=>true,'activityType'=>'update_static_files')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $fileCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$receiverLanguageID,'isPermanent'=>true,'activityType'=>'update_static_languages')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $languageCode = $frmSecuritymanagerEvent->getData()['code'];
        }
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmstaticupdater', 'admin_settings_title'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmstaticupdater', 'admin_settings_title'));
        $staticUpdaterForm = new Form('setting');
        $staticUpdaterForm->setAction(OW::getRouter()->urlForRoute('update-all_static_files',array('fileCode'=>$fileCode)));
        $submitField = new Submit('submit');
        $staticUpdaterForm->addElement($submitField);
        $this->addForm($staticUpdaterForm);

        $staticLanguagesForm = new Form('languages');
        $staticLanguagesForm->setAction(OW::getRouter()->urlForRoute('update-languages',array('languageCode'=>$languageCode)));
        $submitField = new Submit('submit');
        $staticLanguagesForm->addElement($submitField);
        $this->addForm($staticLanguagesForm);

    }
}
