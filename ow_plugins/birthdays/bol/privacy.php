<?php
/**
 * Data Transfer Object for `birthday_privacy` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.birthdays.bol
 * @since 1.0
 */
class BIRTHDAYS_BOL_Privacy extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var staring
     */
    public $privacy;
}