<?php
/**
 * frmadvancedstyles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancedstyles
 * @since 1.0
 */

class FRMADVANCEDSTYLES_CLASS_EventHandler
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

        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        $eventManager->bind('admin.css.custom.save', array($this, 'after_theme_css_save'));
    }

    public function onBeforeDocumentRender(OW_Event $event){
        $path = FRMADVANCEDSTYLES_BOL_Service::getInstance()->getScssURL();
        if (!empty($path)) {
            OW::getDocument()->addStyleSheet($path);
        }
    }

    public function after_theme_css_save(OW_Event $event){
        $params = $event->getParams();
        $form_name = $params['form_name'];
        $newStyle = $params['style'];
        $service = FRMADVANCEDSTYLES_BOL_Service::getInstance();
        if ( $form_name == 'desktop_scss') {
            $service->setDesktopCustomScss($newStyle);
            $css = $service->convertSCSStoCSS($newStyle);
            $path = $service->getScssFile();
            file_put_contents($path,$css);
        }
        if ( $form_name == 'mobile_scss') {
            FRMADVANCEDSTYLES_BOL_Service::getInstance()->setMobileCustomScss($newStyle);
            $css = $service->convertSCSStoCSS($newStyle);
            $path = $service->getScssFile(true);
            file_put_contents($path,$css);
        }
    }

}