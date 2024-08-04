<?php
/**
 * Data Transfer Object for `base_authorization_user_role` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationUserRole extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $roleId;

}
