<?php
/**
 * Data Transfer Object for `base_invite` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_InviteCode extends OW_Entity
{
    /**
     * @var string
     */
    public $code;
    /**
     * @var integer
     */
    public $expiration_stamp;
    /**
     * @var integer
     */
    public $userId;

    public function getCode()
    {
        return $this->code;
    }

    public function setCode( $code )
    {
        $this->code = $code;
    }

    public function getExpiration_stamp()
    {
        return $this->expiration_stamp;
    }

    public function setExpiration_stamp( $expiration_stamp )
    {
        $this->expiration_stamp = $expiration_stamp;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = $userId;
    }
}
