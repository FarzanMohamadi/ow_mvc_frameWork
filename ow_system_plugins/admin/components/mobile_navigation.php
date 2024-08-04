<?php
/**
 *
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_MobileNavigation extends OW_Component
{
    protected $panels = array();
    protected $prefix;
    protected $sharedData = array();
    protected $responderUrl;

    public function __construct() 
    {
        parent::__construct();
        
        OW_ViewRenderer::getInstance()->registerFunction('dnd_item', array($this, 'tplItem'));
    }
    
    public function setupPanel( $panel, $settings )
    {
        $this->panels[$panel] = empty($this->panels[$panel]) ? array(
            "key" => $panel,
            "items" => array()
        ) : $this->panels[$panel];
        
        $this->panels[$panel] = array_merge($this->panels[$panel], $settings);
    }
    
    public function setResponderUrl( $url )
    {
        $this->responderUrl = $url;
    }
    
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;
    }
    
    public function setSharedData( $data )
    {
        $this->sharedData = $data;
    }
    
    public function initStatic()
    {
        $adminJsUrl = OW::getPluginManager()->getPlugin("admin")->getStaticJsUrl();
        $baseJsUrl = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        
        OW::getDocument()->addScript($baseJsUrl . "jquery-ui.min.js");
        OW::getDocument()->addScript($adminJsUrl . "mobile.js");
        OW::getLanguage()->addKeyForJs('mobile', 'are_you_sure');
        
        $settings = array();
        $settings["rsp"] = $this->responderUrl;
        $settings["prefix"] = $this->prefix;
        $settings["shared"] = $this->sharedData;
        
        $js = UTIL_JsGenerator::newInstance();
        $js->callFunction(array("MOBILE", "Navigation", "init"), array($settings));
        
        OW::getDocument()->addOnloadScript($js);
        
        OW::getLanguage()->addKeyForJs("mobile", "admin_nav_adding_message");
        OW::getLanguage()->addKeyForJs("mobile", "admin_nav_settings_fb_title");
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $this->initStatic();
        
        $this->assign("panels", $this->panels);
    }
    
    
    public function tplItem( $params )
    {
        $data = isset($params["data"]) ? $params["data"] : $params;
        
        $item = new ADMIN_CMP_MobileNavigationItem($data);
        
        return $item->render();
    }
}