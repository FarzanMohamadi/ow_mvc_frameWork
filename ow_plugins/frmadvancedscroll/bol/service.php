<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancedscroll.bol
 * @since 1.0
 */
class FRMADVANCEDSCROLL_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function onBeforeDocumentRenderer(OW_Event $event)
    {
        $toTopMinUrl = OW::getPluginManager()->getPlugin('frmadvancedscroll')->getStaticJsUrl() . 'jquery.ui.totop.min.js';
        $easingUrl = OW::getPluginManager()->getPlugin('frmadvancedscroll')->getStaticJsUrl() . 'easing.js';

        $document = OW::getDocument();
        $document->addScript($easingUrl, "text/javascript");
        $document->addScript($toTopMinUrl, "text/javascript");
        $document->addStyleDeclaration("#toTop {
            display: none;
            position: fixed;
            width: 35px;
            height: 35px;
            border: none;
            font-size: 0px;
            background: url(".OW::getPluginManager()->getPlugin('frmadvancedscroll')->getStaticUrl() . "img/ui.totop.png);
            background-repeat: no-repeat;
            bottom: 5px !important;
            left: 10px !important;
            right: initial !important;
            z-index: 2;
            }

            #toTop:active, #toTop:focus {
                outline:none;
            }
        ");
    }

    public function onFinalize(OW_Event $event){
        $config = OW::getConfig();

        $speed = $config->getValue('frmadvancedscroll', 'EaseSpeed');
        $type = $config->getValue('frmadvancedscroll', 'Easing');
        $indelay = $config->getValue('frmadvancedscroll', 'InDelay');
        $outdelay = $config->getValue('frmadvancedscroll', 'OutDelay');
        $bottom = $config->getValue('frmadvancedscroll', 'bottom');
        $right = $config->getValue('frmadvancedscroll', 'right');
        $left = $config->getValue('frmadvancedscroll', 'left');
        $adminAreaAllowed = $config->getValue('frmadvancedscroll', 'adminarea');//0 is false,1 is true
        $uri = OW::getRouter()->getUri();
        if ($adminAreaAllowed == 'disable' and explode('/', $uri[0] == 'admin'))
        {
            return;
        }


        $script = "$(document).ready(function() {
			   $().UItoTop({ easingType: '{$type}', scrollSpeed : $speed, inDelay: $indelay, outDelay: $outdelay});
	        });";


        $css = "#toTop{bottom: {$bottom}px; right: {$right}px;";
        if ((int) $left != 0) { $css.= "left: {$left}px;";}
        $css .= "}";

        OW::getDocument()->addStyleDeclaration($css);
        OW::getDocument()->addScriptDeclaration($script);
    }

}