<?php
/**
 * Abstract controller class to work with the remote storage.
 *
 * @package ow_system_plugins.admin.controllers
 * @since 1.7.7
 */
abstract class ADMIN_CTRL_StorageAbstract extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_PluginService
     */
    protected $pluginService;

    /**
     * @var BOL_StorageService
     */
    protected $storageService;

    /**
     * @var BOL_ThemeService
     */
    protected $themeService;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pluginService = BOL_PluginService::getInstance();
        $this->storageService = BOL_StorageService::getInstance();
        $this->themeService = BOL_ThemeService::getInstance();
    }

    protected function getFtpConnection()
    {
        try
        {
            $ftp = $this->storageService->getFtpConnection();
        }
        catch ( LogicException $e )
        {
            OW::getFeedback()->error($e->getMessage());
            $this->redirect(OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor("ADMIN_CTRL_Storage",
                        "ftpAttrs"),
                    array(BOL_StorageService::URI_VAR_BACK_URI => urlencode(OW::getRequest()->getRequestUri()))));
        }

        return $ftp;
    }

    protected function redirectToBackUri( $getParams )
    {
        if ( !isset($getParams[BOL_StorageService::URI_VAR_BACK_URI]) )
        {
            return;
        }

        $backUri = $getParams[BOL_StorageService::URI_VAR_BACK_URI];
        unset($getParams[BOL_StorageService::URI_VAR_BACK_URI]);

        if( isset($getParams[BOL_StorageService::URI_VAR_RETURN_RESULT]) && !$getParams[BOL_StorageService::URI_VAR_RETURN_RESULT] )
        {
            $getParams = array();
        }
        
        $this->redirect(OW::getRequest()->buildUrlQueryString(OW_URL_HOME . urldecode($backUri), $getParams));
    }

    protected function getTemDirPath()
    {
        return OW_DIR_PLUGINFILES . "ow" . DS;
    }
}
