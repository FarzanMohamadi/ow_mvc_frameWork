<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.bol
 * @since 1.0
 */
class FRMCFP_BOL_EventFiles extends OW_Entity
{
    /**
     * @var integer
     */
    public $eventId;

    /**
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param string integer
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;
    }

   /**
     * @var integer
    */
    public $attachmentId;

    /**
     * @return int
     */
    public function getAttachmentId()
    {
        return $this->attachmentId;
    }

    /**
     * @param int $attachmentId
     */
    public function setAttachmentId($attachmentId)
    {
        $this->attachmentId = $attachmentId;
    }

}
