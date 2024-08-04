<?php
/**
 * Data Transfer Object for `login_cookie` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LoginCookie extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var string
     */
    public $cookie;

    /**
     * @var string
     */
    public $timestamp;

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param integer $userId
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param string $cookie
     */
    public function setCookie( $cookie )
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
}