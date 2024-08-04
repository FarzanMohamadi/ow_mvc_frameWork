<?php
/**
 * Data Transfer Object for `forum_edit_post` table.
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_EditPost extends OW_Entity
{
    /**
     * @var int
     */
    public $postId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var int
     */
    public $editStamp;
}
