<?php
/**
 * Base controller class for all admin pages.
 * All admin controllers should be extended from this class.
 *
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
abstract class ADMIN_CTRL_Abstract extends OW_ActionController
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( OW::getApplication()->getContext() != OW_Application::CONTEXT_DESKTOP )
        {
            throw new InterceptException(array(OW_RequestHandler::ATTRS_KEY_CTRL => 'BASE_MCTRL_BaseDocument', OW_RequestHandler::ATTRS_KEY_ACTION => 'notAvailable'));
        }

        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticateException();
        }

        if ( !OW::getRequest()->isAjax() )
        {
            $document = OW::getDocument();
            $document->setMasterPage(new ADMIN_CLASS_MasterPage());
            $document->setTitle(OW::getLanguage()->text('admin', 'page_default_title'), true);
        }
    }

    public function setPageTitle( $title )
    {
        OW::getDocument()->setTitle($title);
    }
}
