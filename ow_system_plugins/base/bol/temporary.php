<?php

/**
 * Data Transfer Object for `photo_temporary` table.
 *
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class BOL_PhotoTemporary extends OW_Entity
{
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $addDatetime;
    /**
     * @var int
     */
    public $hasFullsize;
    /**
     * @var int
     */
    public $order;
}