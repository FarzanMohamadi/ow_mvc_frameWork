<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.privacy.bol
 * @since 1.0
 */
class PRIVACY_BOL_Cron extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    
    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $value;

    /**
     * @var boolean
     */
    public $inProcess = 0;

    /**
     * @var int
     */
    public $timeStamp = 0;
}
