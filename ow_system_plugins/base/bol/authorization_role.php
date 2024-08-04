<?php
/**
 * Data Transfer Object for `base_authorization_role` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationRole extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $sortOrder;
    /**
     * @var int
     */
    public $displayLabel;
    /**
     * @var string
     */
    public $custom;
    
    public function getName()
    {
        return $this->name;
    }

    /**
     * 
     * @return BOL_AuthorizationRole
     */
    public function setName( $name )
    {
        $this->name = $name;

        return $this;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * 
     * @return BOL_AuthorizationRole
     */
    public function setSortOrder( $sortOrder )
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
    
    public function getDisplayLabel()
    {
        return $this->displayLabel;
    }
    
    public function setDisplayLabel( $displayLabel )
    {
        $this->displayLabel = (int) $displayLabel;
        
        return $this;
    }
    
    public function getCustom()
    {
        return $this->custom;
    }
    
    public function setCustom( $custom )
    {
        $this->custom = $custom;
        
        return $this;
    }
}
