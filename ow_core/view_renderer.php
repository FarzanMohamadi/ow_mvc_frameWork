<?php
/**
 * @package ow_core
 * @method static OW_ViewRenderer getInstance()
 * @since 1.0
 */
class OW_ViewRenderer
{
    use OW_Singleton;
    
    /**
     * @var OW_Smarty
     */
    private $smarty;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->smarty = new OW_Smarty();
    }

    /**
     * Assigns list of values to template vars by reference.
     *
     * @param array $vars
     */
    public function assignVars( $vars )
    {
        foreach ( $vars as $key => $value )
        {
            $this->smarty->assignByRef($key, $vars[$key]);
        }
    }

    /**
     * Assigns value to template var by reference.
     *
     * @param string $key
     * @param mixed $value
     */
    public function assignVar( $key, $value )
    {
        $this->smarty->assignByRef($key, $value);
    }

    /**
     * Renders template using assigned vars and returns generated markup.
     *
     * @param string $template
     * @return string
     * @throws Redirect404Exception
     */
    public function renderTemplate( $template )
    {
        try {
            return $this->smarty->fetch($template);
        } catch (Exception $e) {
            trigger_error("Can't Render url using smarty!. " . $e . "", E_USER_ERROR);
            throw new Redirect404Exception();
        }
    }

    /**
     * Returns assigned var value for provided var name.
     *
     * @param string $varName
     * @return mixed
     */
    public function getAssignedVar( $varName )
    {
        return $this->smarty->getTemplateVars($varName);
    }

    /**
     * Returns list of assigned var values.
     *
     * @return array
     */
    public function getAllAssignedVars()
    {
        return $this->smarty->getTemplateVars();
    }

    /**
     * Deletes all assigned template vars.
     */
    public function clearAssignedVars()
    {
        $this->smarty->clearAllAssign();
    }

    /**
     *
     * @param string $varName
     */
    public function clearAssignedVar( $varName )
    {
        $this->smarty->clearAssign($varName);
    }

    /**
     * Adds custom function for template.
     *
     * @param string $name
     * @param callback $callback
     */
    public function registerFunction( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['function'][$name]) )
        {
            $this->smarty->registerPlugin('function', $name, $callback);
        }
    }

    /**
     * Removes custom function.
     *
     * @param string $name
     */
    public function unregisterFunction( $name )
    {
        $this->smarty->unregisterPlugin('function', $name);
    }

    /**
     * Adds custom block function for template.
     *
     * @param string $name
     * @param callback $callback
     */
    public function registerBlock( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['block'][$name]) )
        {
            $this->smarty->registerPlugin('block', $name, $callback);
        }
    }

    /**
     * Removes block function.
     *
     * @param string $name
     */
    public function unregisterBlock( $name )
    {
        $this->smarty->unregisterPlugin('block', $name);
    }

    /**
     * Adds custom template modifier.
     * 
     * @param string $name
     * @param string $callback 
     */
    public function registerModifier( $name, $callback )
    {
        if ( empty($this->smarty->registered_plugins['modifier'][$name]) )
        {
            $this->smarty->registerPlugin('modifier', $name, $callback);
        }
    }

    /**
     * Remopves template modifier.
     * 
     * @param string $name 
     */
    public function unregisterModifier( $name )
    {
        $this->smarty->unregisterPlugin('modifier', $name);
    }

    /**
     * Clears compiled templates.
     */
    public function clearCompiledTpl()
    {
        $this->smarty->clearCompiledTemplate();
    }
}