<?php
/**
 * Data Transfer Object for `forum_group` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_Group extends OW_Entity
{
    /**
     * @var int
     */
    public $sectionId;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var int
     */
    public $order;
    /**
     * @var int
     */
    public $entityId;
    /**
     * @var boolean
     */
    public $isPrivate;
    /**
     * @var string
     */
    public $roles;
}
