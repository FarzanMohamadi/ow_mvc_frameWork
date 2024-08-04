<?php
/**
 * Data Transfer Object for `forum_topic` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_Topic extends OW_Entity
{
    /**
     * @var int
     */
    public $groupId;
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $title;
    /**
     * @var int
     */
    public $locked = 0;
    /**
     * @var int
     */
    public $sticky = 0;
    /**
     * @var int
     */
    public $temp = 0;
    /**
     * @var int
     */
    public $viewCount = 0;
    /**
     * @var int
     */
    public $lastPostId = 0;
    /**
     * @var string
     */
    public $status = 'approved';
    /**
     * @var int
     */
    public $closeTime;
    /**
     * @var int
     */
    public $conclusionPostId;
}
