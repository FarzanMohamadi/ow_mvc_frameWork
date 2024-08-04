<?php
/**
 * Data Transfer Object for `base_user_featured` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserFeatured extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;

    public function setUserId( $id )
    {
        $this->userId = $id;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}