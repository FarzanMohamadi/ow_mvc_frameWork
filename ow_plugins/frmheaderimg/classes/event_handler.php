<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmheaderimg.classes
 * @since 1.0
 */
class FRMHEADERIMG_CLASS_EventHandler
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

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onAfterRoute'));
    }

    public function onAfterRoute(OW_Event $event)
    {
    	$path = $_SERVER['REQUEST_URI'];
		$css = '';
    	//homepage
    	if(preg_match('#^(/(index(/){0,1}){0,1}){0,1}$#', $path, $matches))
    	{
    		$css = '
            	.st_slider { display: block; }';
    	}
    	elseif(preg_match('#^/forum(/[\da-z])*#', $path, $matches))
    	{
    		$css = '
            	.st_header_img
            	{
    				display: block;
    				background-image: url("' . OW::getPluginManager()->getPlugin('frmheaderimg')->getStaticUrl(). 'img/forum.png' . '");
    			}';
    	}
    	elseif(preg_match('#^/download(/)*#', $path, $matches))
    	{
    		$css = '
            	.st_header_img
            	{
    				display: block;
    				background-image: url("' . OW::getPluginManager()->getPlugin('frmheaderimg')->getStaticUrl(). 'img/download.png' . '");
    			}';
    	}
    	elseif(preg_match('#^/demo(/)*#', $path, $matches))
    	{
    		$css = '
            	.st_header_img
            	{
    				display: block;
    				background-image: url("' . OW::getPluginManager()->getPlugin('frmheaderimg')->getStaticUrl(). 'img/demo.png' . '");
    			}';
    	}
    	elseif(preg_match('#^/rules(/[\da-z])*#', $path, $matches))
    	{
    		$css = '
            	.st_header_img
            	{
    				display: block;
    				background-image: url("' . OW::getPluginManager()->getPlugin('frmheaderimg')->getStaticUrl(). 'img/rules.png' . '");
    			}';
    	}
    	elseif(preg_match('#^/news(/[\da-z])*#', $path, $matches))
    	{
    		$css = '
            	.st_header_img
            	{
    				display: block;
    				background-image: url("' . OW::getPluginManager()->getPlugin('frmheaderimg')->getStaticUrl(). 'img/news.jpg' . '");
    			}';
    	}
		if($css!='') {
			OW::getDocument()->addStyleDeclaration($css);
		}
    }
}