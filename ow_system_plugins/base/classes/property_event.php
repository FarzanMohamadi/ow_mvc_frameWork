<?php
/**
 * Database log writer.
 *
 * @package ow_system_plugins.base.class
 * @since 1.0
 */
class BASE_CLASS_PropertyEvent extends OW_Event
{
    protected $props;

    /**
     * Constructor.
     */
    public function __construct( $name, array $properties, array $params = array() )
    {
        parent::__construct($name, $params);
        $this->props = $properties;
    }

    public function getProperties()
    {
        return $this->props;
    }

    public function getProperty( $name )
    {
        return array_key_exists($name, $this->props) ? $this->props[$name] : null;
    }

    public function setProperty( $name, $val )
    {
        $this->props[$name] = $val;
    }
}
