<?php
/**
 * Data Transfer Object for `base_tag` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Tag extends OW_Entity
{
    public $label;

    /**
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return BOL_Tag
     */
    public function setLabel( $label )
    {
        $this->label = $label;
        return $this;
    }
}