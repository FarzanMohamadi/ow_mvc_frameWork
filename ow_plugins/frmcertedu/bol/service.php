<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcertedu
 * @since 1.0
 */
final class FRMCERTEDU_BOL_Service
{
    private function __construct()
    {
    }

    /***
     * @var
     */
    private static $classInstance;

    /***
     * @return FRMCERTEDU_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function loadStaticFiles(){
        $jsFile = OW::getPluginManager()->getPlugin('frmcertedu')->getStaticJsUrl() . 'frmcertedu.js';
        OW::getDocument()->addScript($jsFile);

        $countdownJSFile = OW::getPluginManager()->getPlugin('frmcertedu')->getStaticJsUrl() . 'countdown.js';
        OW::getDocument()->addScript($countdownJSFile);

        $cssFile = OW::getPluginManager()->getPlugin('frmcertedu')->getStaticCssUrl() . 'frmcertedu.css';
        OW::getDocument()->addStyleSheet($cssFile);



        $path = $_SERVER['REQUEST_URI'];
        if(preg_match('#^(/(index(/){0,1}){0,1}){0,1}$#', $path, $matches))
        {
            $mainPageCssFile = OW::getPluginManager()->getPlugin('frmcertedu')->getStaticCssUrl() . 'certedu_mainpage.css';
            OW::getDocument()->addStyleSheet($mainPageCssFile);
        }
    }
}