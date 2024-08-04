<?php
/**
 * Data Transfer Object for `base_avatar` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Avatar extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $hash;
    
    /**
     * @var string
     */
    public $status = 'active';

    /**
     *
     * @return integer
     */
    public function getUserId()
    {
        return (int) $this->userId;
    }

    /**
     *
     * @return integer
     */
    public function getHash()
    {
        return (int) $this->hash;
    }
    
    /**
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
