<?php
/**
 * Data Transfer Object for `forum_section` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_Section extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $order;
    /**
     * @var string
     */
    public $entity;
    /**
     * @var boolean
     */
    public $isHidden = 0;
}
