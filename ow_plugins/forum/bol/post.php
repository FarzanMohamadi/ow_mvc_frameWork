<?php
/**
 * Data Transfer Object for `forum_post` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_Post extends OW_Entity
{
    /**
     * @var int
     */
    public $topicId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $text;
    /**
     * @var int
     */
    public $createStamp;
}