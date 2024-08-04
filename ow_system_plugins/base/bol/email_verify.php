<?php
/**
 * Data Transfer Object for `base_email_verify` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_EmailVerify extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $hash;
    /**
     * @var int
     */
    public $createStamp = 0;
    /**
     * @var string
     */
    public $type;
}
