<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */
class FRMGUIDEDTOUR_BOL_UserGuide extends OW_Entity
{
    /**
     * @var string
     */
    public $userId;
    /**
     * @var boolean
     */
    public $seenStatus;

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function getSeenStatus()
    {
        return $this->seenStatus;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param bool $seen
     */
    public function setSeen($seen)
    {
        $this->seenStatus = $seen;
    }
}