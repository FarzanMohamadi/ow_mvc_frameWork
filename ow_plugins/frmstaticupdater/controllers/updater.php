<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmstaticupdater.controllers
 * @since 1.0
 */

class FRMSTATICUPDATER_CTRL_Updater extends OW_ActionController
{

    public function updateStaticFiles($params){
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()){
            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                $code =$params['fileCode'];
                if(!isset($code)){
                    throw new Redirect404Exception();
                }
                OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                    array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'update_static_files')));
            }
            FRMSecurityProvider::updateStaticFiles(true);
            OW::getFeedback()->info(OW::getLanguage()->text('frmstaticupdater', 'successfully_update'));
            $this->redirect(OW::getRouter()->urlForRoute('frmstaticupdater.admin'));
        }

        throw new Redirect404Exception();
    }

    public function updateLanguages($params){
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()){
            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                $code =$params['languageCode'];
                if(!isset($code)){
                    throw new Redirect404Exception();
                }
                OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                    array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'update_static_languages')));
            }
            try
            {
                FRMSecurityProvider::updateLanguages(true);
                OW::getFeedback()->info(OW::getLanguage()->text('frmstaticupdater', 'update_language_successfully'));
                $this->redirect(OW::getRouter()->urlForRoute('frmstaticupdater.admin'));
            }
            catch (Exception $ex)
            {
                throw new Redirect404Exception();
            }
        }

        throw new Redirect404Exception();
    }
}