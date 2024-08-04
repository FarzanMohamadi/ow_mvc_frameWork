<?php
/**
 * Data Transfer Object for `base_restricted_usernames` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_RestrictedUsernames extends OW_Entity
{
    /**
     * @var string
     */
    public $username;

    /**
     * @param string $username
     * @return BOL_RestrictedUsernames
     */
    public function setRestrictedUsername( $username )
    {
        $this->username = $username;

        return $this;
    }
}
