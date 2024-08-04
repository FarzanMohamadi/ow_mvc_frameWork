<?php
/**
 * Data Transfer Object for `base_authorization_group` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationGroup extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var boolean
     */
    public $moderated = true;

    public function getName()
    {
        return $this->name;
    }

    public function setName( $name )
    {
        $this->name = $name;
    }

    public function isModerated()
    {
        return (boolean) $this->moderated;
    }

    public function setModerated( $moderated )
    {
        $this->moderated = (boolean) $moderated;
    }
}
