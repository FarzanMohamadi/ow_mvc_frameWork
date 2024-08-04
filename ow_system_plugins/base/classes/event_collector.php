<?php
/**
 * Collector event
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BASE_CLASS_EventCollector extends OW_Event
{
    public function __construct( $name, $params = array() )
    {
        parent::__construct($name, $params);

        $this->data = array();
    }

    public function add( $item )
    {
        $this->data[] = $item;
    }

    public function setData( $data )
    {
        throw new LogicException("Can't set data in collector event `" . $this->getName() . "`!");
    }
}