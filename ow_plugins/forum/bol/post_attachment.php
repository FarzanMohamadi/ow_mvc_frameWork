<?php
/**
 * Data Transfer Object for `forum_post_attachment` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_PostAttachment extends OW_Entity
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $postId;
    /**
     * @var int
     */
    public $hash;
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $fileNameClean;
    /**
     * @var int
     */
    public $fileSize;
}