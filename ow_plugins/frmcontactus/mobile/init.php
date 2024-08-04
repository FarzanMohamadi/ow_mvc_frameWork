<?php
OW::getRouter()->addRoute(new OW_Route('frmcontactus.index', 'contact', "FRMCONTACTUS_MCTRL_Contact", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcontactus.old.index', 'frmcontact', "FRMCONTACTUS_MCTRL_Contact", 'index'));

function frmcontactus_handler_after_install( BASE_CLASS_EventCollector $event )
{
    if ( count(FRMCONTACTUS_BOL_Service::getInstance()->getDepartmentList()) < 1 )
    {
        $url = OW::getRouter()->urlForRoute('frmcontactus.admin');
        $event->add(OW::getLanguage()->text('frmcontactus', 'after_install_notification', array('url' => $url)));
    }
}

OW::getEventManager()->bind('admin.add_admin_notification', 'frmcontactus_handler_after_install');


function frmcontactus_ads_enabled( BASE_CLASS_EventCollector $event )
{
    $event->add('frmcontactus');
}

OW::getEventManager()->bind('ads.enabled_plugins', 'frmcontactus_ads_enabled');

OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'FRMCONTACTUS_CTRL_Contact');