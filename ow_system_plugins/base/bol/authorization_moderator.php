<?php
/**
 * Data Transfer Object for `base_authorization_moderator` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationModerator extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;

    public function getUserId()
    {
        return $this->userId;
    }

    /**
     *
     * @param int $id
     * @return BOL_AuthorizationModerator;
     */
    public function setUserId( $id )
    {
        $this->userId = $id;

        return $this;
    }
}
