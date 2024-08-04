<?php
/**
 * Data Transfer Object for `base_authorization_permission` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationPermission extends OW_Entity
{
    /**
     * @var integer
     */
    public $actionId;
    /**
     * @var integer
     */
    public $roleId;

}
