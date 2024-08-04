<?php
/**
 * Data Transfer Object for `forum_read_topic` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_ReadTopic extends OW_Entity
{
    /**
     * @var int
     */
    public $topicId;
    /**
     * @var int
     */
    public $userId;
}
