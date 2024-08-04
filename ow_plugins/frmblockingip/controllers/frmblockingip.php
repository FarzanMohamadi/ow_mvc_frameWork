<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmblockingip.controllers
 * @since 1.0
 */
class FRMBLOCKINGIP_CTRL_Iisblockingip extends OW_ActionController
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->service = FRMBLOCKINGIP_BOL_Service::getInstance();
    }
    
    public function index( $params = NULL )
    {
        if ( !$this->service->isLocked() )
        {
            $this->redirect(OW_URL_HOME);
        }

        $userBlockedTime = $this->service->getCurrentUser()->getTime();

        OW::getDocument()->setJavaScripts(array('added' => array()));
        $this->setPageTitle(OW::getLanguage()->text("frmblockingip", "title_locked"));
        $release_time =  $userBlockedTime + (int)OW::getConfig()->getValue('frmblockingip', FRMBLOCKINGIP_BOL_Service::EXPIRE_TIME) * 60;
        $release_time =  UTIL_DateTime::formatSimpleDate($release_time,false);
        $this->assign("release_time", $release_time);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ow.js');

        $isWebservice = false;
        $mobileSupportEvent = OW::getEventManager()->trigger(new OW_Event('check.url.webservice', array()));
        if (isset($mobileSupportEvent->getData()['isWebService']) && $mobileSupportEvent->getData()['isWebService']) {
            $isWebservice = true;
        }

        if ( !OW::getRequest()->isAjax() && !$isWebservice ) {
            $masterPageFileDir = OW::getThemeManager()->getMasterPageTemplate(OW_MasterPage::TEMPLATE_BLANK);
            OW::getDocument()->getMasterPage()->setTemplate($masterPageFileDir);
        }else{
            $failRedirect = OW::getRouter()->urlForRoute('frmblockingip.authenticate_fail');
            exit(json_encode(array('failRedirect'=>$failRedirect)));
        }
        $this->setDocumentKey("frmBlockingPage");
    }
}
