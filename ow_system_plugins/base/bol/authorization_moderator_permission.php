<?php
/**
 * Data Transfer Object for `base_authorization_moderator_permission` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationModeratorPermission extends OW_Entity
{
    /**
     * @var integer
     */
    public $moderatorId;
    /**
     * @var integer
     */
    public $groupId;

    /**
     *
     * @return BOL_AuthorizationModeratorPermission
     */
    public function setModeratorId( $moderatorId )
    {
        $this->moderatorId = $moderatorId;

        return $this;
    }

    public function getModeratorId()
    {
        return $this->moderatorId;
    }

    /**
     *
     * @return BOL_AuthorizationModeratorPermission
     */
    public function setGroupId( $groupId )
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }
}
