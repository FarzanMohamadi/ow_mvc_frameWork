<?php
/**
 * Base class for renderable elements. Allows to assign vars and compile HTML using template engine.
 *
 * @package ow_core
 * @since 1.8.3
 */
class OW_View
{
    /**
     * List of assigned vars
     *
     * @var array
     */
    protected $assignedVars = array();

    /**
     * Template path
     *
     * @var string
     */
    protected $template;

    /**
     * @var boolean
     */
    protected $visible = true;

    /**
     * @var array
     */
    protected static $devInfo = array();

    /**
     * @var boolean
     */
    private static $collectDevInfo = false;

    /**
     * Getter for renderedClasses static property
     * 
     * @return array
     */
    public static function getDevInfo()
    {
        return self::$devInfo;
    }

    /**
     * Sets developer mode
     * 
     * @param boolean $collect 
     */
    public static function setCollectDevInfo( $collect )
    {
        self::$collectDevInfo = (bool) $collect;
    }

    /**
     * Sets visibility, invisible items return empty markup on render
     *
     * @param boolean $visible
     * @return OW_View
     */
    public function setVisible( $visible )
    {
        $this->visible = (bool) $visible;
        
        return $this;
    }

    /**
     * Checks if item is visible
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return OW_View
     */
    public function setTemplate( $template )
    {
        $this->template = $template;
        
        return $this;
    }

    /**
     * Assigns variable
     *
     * @param string $name
     * @param mixed $value
     * @return OW_View
     */
    public function assign( $name, $value )
    {
        $this->assignedVars[$name] = $value;
        
        return $this;
    }

    /**
     * @param string $varName
     * @return OW_View
     */
    public function clearAssign( $varName )
    {
        if ( isset($this->assignedVars[$varName]) )
        {
            unset($this->assignedVars[$varName]);
        }

        return $this;
    }

    public function onBeforeRender()
    {
        
    }

    /**
     * Returns rendered markup
     *
     * @return string
     */
    public function render()
    {
        $this->onBeforeRender();

        if ( !$this->visible )
        {
            return "";
        }

        $className = get_class($this);

        if ( $this->template === null )
        {
            throw new LogicException("No template provided for class `{$className}`");
        }

        $viewRenderer = OW_ViewRenderer::getInstance();

        $prevVars = $viewRenderer->getAllAssignedVars();

        $this->onRender();

        $viewRenderer->assignVars($this->assignedVars);

        $renderedMarkup = $viewRenderer->renderTemplate($this->template);

        $viewRenderer->clearAssignedVars();

        $viewRenderer->assignVars($prevVars);

        if (!OW_PROFILER_ENABLE) {
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::CHECK_MASTER_PAGE_BLANK_HTML_FOR_UPLOAD_IMAGE_FORM, array('viewRenderer' => $viewRenderer, 'assignedVars' => $this->assignedVars)));
        }

        // TODO refactor - dirty data collect for dev tool
        if ( self::$collectDevInfo )
        {
            self::$devInfo[$className] = $this->template;
        }

        return $renderedMarkup;
    }

    protected function onRender()
    {
        
    }

    /**
     * Triggers event using base event class
     * 
     * @param string $name
     * @param array $params
     * @param mixed $data
     * @return mixed
     */
    protected function triggerEvent( $name, array $params = array(), $data = null )
    {
        return OW::getEventManager()->trigger(new OW_Event($name, $params, $data));
    }

    /**
     * @param OW_Event $event
     * @return mixed
     */
    protected function triggerEventForObject( OW_Event $event )
    {
        return OW::getEventManager()->trigger($event);
    }
}
