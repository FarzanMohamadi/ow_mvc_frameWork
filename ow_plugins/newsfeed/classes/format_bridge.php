<?php
/**
 *
 * @package ow_plugins.newsfeed.classes
 * @since 1.0
 */
class NEWSFEED_CLASS_FormatBridge
{
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_CLASS_FormatBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_CLASS_FormatBridge
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var NEWSFEED_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = NEWSFEED_BOL_Service::getInstance();
    }
    
    public function beforeRenderFormat( OW_Event $event )
    {
        
    }
    
    public function renderFormat( OW_Event $event )
    {
        
    }
    
    public function afterRenderFormat( OW_Event $event )
    {
        
    }
    
    public function collectFormats( BASE_CLASS_EventCollector $event )
    {
        
    }
    
    public function init()
    {
        OW::getEventManager()->bind("feed.collect_formats", array($this, "collectFormats"));
        OW::getEventManager()->bind("feed.before_render_format", array($this, "beforeRenderFormat"));
        OW::getEventManager()->bind("feed.render_format", array($this, "renderFormat"));
        OW::getEventManager()->bind("feed.after_render_format", array($this, "afterRenderFormat"));
    }
}