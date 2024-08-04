<?php
/**
 *
 * @package ow_plugins.newsfeed.classes
 * @since 1.0
 */
class NEWSFEED_CLASS_FormatManager
{
    const FORMAT_EMPTY = "empty";
    
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_CLASS_FormatManager
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_CLASS_FormatManager
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $formats = array();
    
    /**
     *
     * @var OW_Plugin
     */
    private $plugin;
    
    private function __construct()
    {
        $this->plugin = OW::getPluginManager()->getPlugin("newsfeed");
    }
    
    public function getFormatNames() 
    {
        return array_keys($this->formats);
    }
    
    public function renderFormat( $name, $vars )
    {
        $beforeRenderEvent = new OW_Event("feed.before_render_format", array(
            "format" => $name,
            "vars" => $vars
        ), $vars);
        OW::getEventManager()->trigger($beforeRenderEvent);

        $renderEvent = new OW_Event("feed.render_format", array(
            "format" => $name,
            "vars" => $beforeRenderEvent->getData()
        ), null);
        OW::getEventManager()->trigger($renderEvent);
        
        $rendered = $renderEvent->getData();
        
        if ( $rendered === null )
        {
            if ( empty($this->formats[$name]) )
            {
                throw new InvalidArgumentException("Undefined Newsfeed format `$name`");
            }
            
            $formatClass = $this->formats[$name];
            
            /* @var $formatObject NEWSFEED_CLASS_Format */
            $formatObject = new $formatClass($vars, $name);
            $rendered = $formatObject->render();
        }
        
        $afterRenderEvent = new OW_Event("feed.after_render_format", array(
            "format" => $name,
            "vars" => $vars
        ), $rendered);
        OW::getEventManager()->trigger($afterRenderEvent);
        
        return $afterRenderEvent->getData();
    }

    public function addFormat($name, $className)
    {
        $this->formats[$name] = $className;
    }
    
    public function collectFormats()
    {
        $event = new BASE_CLASS_EventCollector("feed.collect_formats");
        OW::getEventManager()->trigger($event);
        
        foreach ( $event->getData() as $format )
        {
            $this->addFormat($format["name"], $format["class"]);
        }
    }
    
    public function init()
    {
        OW::getAutoloader()->addPackagePointer("NEWSFEED_FORMAT", $this->plugin->getRootDir() . "formats" . DS);
        OW::getAutoloader()->addPackagePointer("NEWSFEED_MFORMAT", $this->plugin->getMobileDir() . "formats" . DS);
        
        OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, "collectFormats"));
    }
}