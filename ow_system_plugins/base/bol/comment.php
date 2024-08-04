<?php
/**
 * Data Transfer Object for `base_comment` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Comment extends OW_Entity
{
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var integer
     */
    public $commentEntityId;
    /**
     * @var string
     */
    public $message;
    /**
     * @var integer
     */
    public $createStamp;
    /**
     * @var string
     */
    public $attachment;
    /**
     * @var integer
     */
    public $replyId;
    /**
     * @var integer
     */
    public $replyUserId;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId( $userId )
    {
        $this->userId = (int) $userId;
    }

    public function getCommentEntityId()
    {
        return $this->commentEntityId;
    }

    public function setCommentEntityId( $commentEntityId )
    {
        $this->commentEntityId = (int) $commentEntityId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage( $message )
    {
        $this->message = trim($message);
    }

    public function getCreateStamp()
    {
        return $this->createStamp;
    }

    public function setCreateStamp( $createStamp )
    {
        $this->createStamp = (int) $createStamp;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function setAttachment( $attachment )
    {
        $this->attachment = $attachment;
    }

    public function getReplyId()
    {
        return $this->replyId;
    }

    public function setReplyId($replyId)
    {
        $this->replyId = $replyId;
    }

    public function getReplyUserId()
    {
        return $this->replyUserId;
    }

    public function setReplyUserId($replyUserId)
    {
        $this->replyUserId = $replyUserId;
    }

}

