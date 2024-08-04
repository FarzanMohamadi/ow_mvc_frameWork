<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Base extends OW_ActionController
{
    public function index()
    {
        //TODO implement
    }
    
    public function turnDevModeOn()
    {
        if( OW_DEV_MODE )
        {
            OW::getConfig()->saveConfig('base', 'dev_mode', 1);
        }

        $this->redirect(OW_URL_HOME);

    }

    public function robotsTxt()
    {
        if( OW::getStorage()->fileExists(OW_DIR_ROOT.'robots.txt') )
        {
            header("Content-Type: text/plain");
            echo(OW::getStorage()->fileGetContent(OW_DIR_ROOT.'robots.txt'));
            exit;
        }

        throw new Redirect404Exception();
    }

    /**
     * Sitemap
     */
    public function sitemap()
    {
        $part = isset($_GET['part']) ? (int) $_GET['part'] : null;

        $sitemap = BOL_SeoService::getInstance()->getSitemapPath($part);

        if ( OW::getStorage()->fileExists($sitemap) )
        {
            header('Content-Type: text/xml');

            echo OW::getStorage()->fileGetContent($sitemap);
            exit;
        }

        throw new Redirect404Exception();
    }
}
